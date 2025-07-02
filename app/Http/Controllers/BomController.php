<?php

namespace App\Http\Controllers;

// Import model dan request yang diperlukan
use App\Models\{Bom, BomDetail, Barang, JenisMaterial, UnitSatuan, Company}; // Tambahkan Company
use App\Http\Requests\Boms\{StoreBomRequest, UpdateBomRequest}; // Pastikan request ini ada
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request; // Gunakan Request standar jika perlu validasi manual tambahan
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BomController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:bom view', only: ['index', 'show']),
            new Middleware('permission:bom create', only: ['create', 'store']),
            new Middleware('permission:bom edit', only: ['edit', 'update']),
            new Middleware('permission:bom delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $companyId = session('sessionCompany'); // Ambil company ID
            $boms = DB::table('bom')
                ->leftJoin('barang', 'bom.barang_id', '=', 'barang.id')
                ->select(
                    'bom.id', // Pastikan ID BOM ada
                    'bom.deskripsi',
                    'bom.created_at',
                    'bom.updated_at',
                    'barang.kode_barang',
                    'barang.nama_barang'
                )
                // Filter berdasarkan company_id dari session
                ->where('bom.company_id', $companyId); // Filter company

            return DataTables::of($boms)
                ->addColumn('nama_barang', function ($row) {
                    return $row->nama_barang ?? '-';
                })
                ->addColumn('kode_barang', function ($row) {
                    return $row->kode_barang ?? '-';
                })
                ->addColumn('created_at_formatted', function ($row) {
                    // Format tanggal agar lebih mudah dibaca
                    return Carbon::parse($row->created_at)->isoFormat('D MMM YYYY, HH:mm');
                })
                ->addColumn('deskripsi', function ($row) {
                    return Str::limit($row->deskripsi, 100);
                })
                ->addColumn('action', 'bom.include.action') // Menggunakan view action
                ->toJson();
        }

        return view('bom.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companyId = session('sessionCompany');


        $produkJadi = Barang::where('company_id', $companyId)
            ->where('tipe_barang', 'Barang Jadi')
            ->orderBy('nama_barang')
            ->get();

        $barangMaterials = Barang::with('unitSatuan')
            ->where('company_id', $companyId)
            ->orderBy('nama_barang')->get();

        $barangKemasan = Barang::with('unitSatuan')
            ->where('company_id', $companyId)
            ->whereHas('jenisMaterial', function ($query) {
                $query->where('nama_jenis_material', 'MATERIAL KEMASAN');
            })
            ->orderBy('nama_barang')->get();


        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan')
            ->pluck('nama_unit_satuan', 'id');

        return view('bom.create', compact('produkJadi', 'barangMaterials', 'barangKemasan', 'unitSatuans'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBomRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $companyId = session('sessionCompany'); // Ambil company_id

        // Validasi tambahan: pastikan ada minimal 1 material
        if (empty($validated['materials'])) {
            throw ValidationException::withMessages(['materials' => 'Minimal harus ada 1 material/komponen yang ditambahkan.']);
        }

        // Validasi tambahan: Pastikan produk jadi berasal dari company yang sama
        $produkJadi = Barang::where('id', $validated['barang_id'])
            ->where('company_id', $companyId)
            ->first();
        if (!$produkJadi) {
            throw ValidationException::withMessages(['barang_id' => 'Produk jadi yang dipilih tidak valid atau tidak sesuai dengan perusahaan Anda.']);
        }

        DB::beginTransaction();
        try {
            // 1. Simpan data BoM utama
            $bom = Bom::create([
                'company_id' => $companyId, // Tambahkan company_id
                'barang_id' => $validated['barang_id'],
                'deskripsi' => $validated['deskripsi'],
            ]);

            // 2. Siapkan dan simpan data detail material
            $materialsToInsert = [];
            $materialIds = array_column($validated['materials'], 'barang_id');
            $unitSatuanIds = array_column($validated['materials'], 'unit_satuan_id');

            // Cek company_id semua material & unit satuan sekaligus
            $validMaterials = Barang::whereIn('id', $materialIds)
                ->where('company_id', $companyId)
                ->pluck('id')->toArray();
            $validUnitSatuans = UnitSatuan::whereIn('id', $unitSatuanIds)
                ->where('company_id', $companyId)
                ->pluck('id')->toArray();

            foreach ($validated['materials'] as $materialData) {
                $materialId = $materialData['barang_id'];
                $unitSatuanId = $materialData['unit_satuan_id'];

                // Pastikan data lengkap dan material & unit dari company yang benar
                if (
                    isset($materialData['jumlah']) &&
                    in_array($materialId, $validMaterials) &&
                    in_array($unitSatuanId, $validUnitSatuans)
                ) {
                    $materialsToInsert[] = [
                        'bom_id' => $bom->id,
                        'barang_id' => $materialId,
                        'jumlah' => $materialData['jumlah'],
                        'unit_satuan_id' => $unitSatuanId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Jika material atau unit tidak valid/sesuai company, batalkan
                    DB::rollBack();
                    Log::warning('Data material/unit tidak valid/sesuai company saat store BOM: ', $materialData);

                    $errorMessage = 'Kesalahan pada data material.';
                    if (!in_array($materialId, $validMaterials)) {
                        $invalidMaterial = Barang::find($materialId);
                        $errorMessage = "Material '" . ($invalidMaterial->kode_barang ?? $materialId) . "' tidak valid/sesuai.";
                    } elseif (!in_array($unitSatuanId, $validUnitSatuans)) {
                        $invalidUnit = UnitSatuan::find($unitSatuanId);
                        $errorMessage = "Unit Satuan '" . ($invalidUnit->nama_unit_satuan ?? $unitSatuanId) . "' tidak valid/sesuai.";
                    }

                    throw ValidationException::withMessages(['materials' => $errorMessage]);
                }
            }

            if (empty($materialsToInsert)) {
                DB::rollBack();
                throw ValidationException::withMessages(['materials' => 'Tidak ada data material valid yang bisa disimpan.']);
            }

            if (!empty($validated['kemasan'])) {
                foreach ($validated['kemasan'] as $item) {
                    if (!empty($item['barang_id']) && !empty($item['jumlah'])) {
                        $bom->kemasan()->create([
                            'barang_id' => $item['barang_id'],
                            'jumlah' => $item['jumlah'],
                            'unit_satuan_id' => $item['unit_satuan_id'],
                        ]);
                    }
                }
            }

            BomDetail::insert($materialsToInsert);


            DB::commit();
            return to_route('bom.index')->with('success', __('BoM berhasil dibuat.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing BOM: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($e instanceof ValidationException) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', __('Gagal menyimpan BoM: Terjadi kesalahan server.'))->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Bom $bom): View
    {
        $companyId = session('sessionCompany');
        // Validasi company
        if ($bom->company_id != $companyId) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relasi yang dibutuhkan
        $bom->load([
            'company', // Load data company
            'barang.unitSatuan', // Produk jadi dan unit defaultnya
            'details.material.unitSatuan', // Detail->material->unit defaultnya
            'details.unitSatuan' // Detail->unit pilihan user
        ]);

        return view('bom.show', compact('bom'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bom $bom): View
    {
        $companyId = session('sessionCompany');
        if ($bom->company_id != $companyId) {
            abort(403, 'Unauthorized action.');
        }

        $bom->load([
            'barang',
            'details.material.unitSatuan',
            'details.unitSatuan'
        ]);


        $produkJadi = Barang::where('company_id', $companyId)
            ->where('tipe_barang', 'Barang Jadi')
            ->orderBy('nama_barang')
            ->get();

        $barangMaterials = Barang::with('unitSatuan')
            ->where('company_id', $companyId)
            ->orderBy('nama_barang')->get();

        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan')
            ->pluck('nama_unit_satuan', 'id');

        return view('bom.edit', compact('bom', 'produkJadi', 'barangMaterials', 'unitSatuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBomRequest $request, Bom $bom): RedirectResponse
    {
        $companyId = session('sessionCompany');
        // Validasi company
        if ($bom->company_id != $companyId) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        // Validasi tambahan: pastikan ada minimal 1 material setelah update
        if (empty($validated['materials'])) {
            throw ValidationException::withMessages(['materials' => 'Minimal harus ada 1 material/komponen.']);
        }

        // Validasi tambahan: Pastikan produk jadi berasal dari company yang sama
        $produkJadi = Barang::where('id', $validated['barang_id'])
            ->where('company_id', $companyId)
            ->first();
        if (!$produkJadi) {
            throw ValidationException::withMessages(['barang_id' => 'Produk jadi yang dipilih tidak valid atau tidak sesuai dengan perusahaan Anda.']);
        }


        DB::beginTransaction();
        try {
            // 1. Update data BoM utama
            // Pastikan company_id tidak ikut terupdate jika tidak seharusnya
            $bomData = [
                'barang_id' => $validated['barang_id'],
                'deskripsi' => $validated['deskripsi'],
                // 'company_id' => $companyId, // Tidak perlu diupdate biasanya
            ];
            $bom->update($bomData);


            // 2. Proses detail material
            $existingDetailIds = $bom->details->pluck('id')->toArray();
            $submittedDetailIds = [];
            $materialsToInsert = [];
            $materialsToUpdate = []; // [id => data]

            // --- Kumpulkan ID Material & Unit Satuan dari request ---
            $materialIds = [];
            $unitSatuanIds = [];
            foreach ($validated['materials'] as $materialData) {
                if (isset($materialData['barang_id'])) $materialIds[] = $materialData['barang_id'];
                if (isset($materialData['unit_satuan_id'])) $unitSatuanIds[] = $materialData['unit_satuan_id'];
            }
            $materialIds = array_unique($materialIds);
            $unitSatuanIds = array_unique($unitSatuanIds);

            // --- Cek validitas Material & Unit Satuan di company ini ---
            $validMaterials = Barang::whereIn('id', $materialIds)
                ->where('company_id', $companyId)
                ->pluck('id')->toArray();
            $validUnitSatuans = UnitSatuan::whereIn('id', $unitSatuanIds)
                ->where('company_id', $companyId)
                ->pluck('id')->toArray();
            // --- End Cek Validitas ---


            foreach ($validated['materials'] as $materialData) {
                // Pastikan data lengkap
                if (!isset($materialData['barang_id'], $materialData['jumlah'], $materialData['unit_satuan_id'])) {
                    Log::warning('Data material tidak lengkap saat update BOM: ', $materialData);
                    continue; // Lewati item ini
                }

                $materialId = $materialData['barang_id'];
                $unitSatuanId = $materialData['unit_satuan_id'];
                $detailId = $materialData['detail_id'] ?? null; // Ambil ID detail jika ada (untuk update)

                // --- Validasi Material & Unit Satuan per item ---
                if (!in_array($materialId, $validMaterials)) {
                    DB::rollBack();
                    $invalidMaterial = Barang::find($materialId);
                    throw ValidationException::withMessages(['materials' => "Material '" . ($invalidMaterial->kode_barang ?? $materialId) . "' tidak valid/sesuai."]);
                }
                if (!in_array($unitSatuanId, $validUnitSatuans)) {
                    DB::rollBack();
                    $invalidUnit = UnitSatuan::find($unitSatuanId);
                    throw ValidationException::withMessages(['materials' => "Unit Satuan '" . ($invalidUnit->nama_unit_satuan ?? $unitSatuanId) . "' tidak valid/sesuai."]);
                }
                // --- End Validasi per Item ---


                $dataPayload = [
                    // 'bom_id' => $bom->id, // Tidak perlu untuk update, sudah di where clause
                    'barang_id' => $materialId,
                    'jumlah' => $materialData['jumlah'],
                    'unit_satuan_id' => $unitSatuanId,
                    'updated_at' => now(),
                ];

                if ($detailId && in_array($detailId, $existingDetailIds)) {
                    // --- Antrikan Update ---
                    $materialsToUpdate[$detailId] = $dataPayload;
                    $submittedDetailIds[] = (int)$detailId; // Simpan ID yang di-submit untuk proses delete nanti
                } else {
                    // --- Antrikan Insert (detail baru) ---
                    $dataPayload['bom_id'] = $bom->id; // Perlu bom_id untuk insert
                    $dataPayload['created_at'] = now();
                    $materialsToInsert[] = $dataPayload;
                }
            }

            // --- Lakukan Update ---
            if (!empty($materialsToUpdate)) {
                foreach ($materialsToUpdate as $id => $data) {
                    // Update hanya jika detail ID tersebut memang milik BoM ini
                    BomDetail::where('id', $id)
                        ->where('bom_id', $bom->id) // Keamanan tambahan
                        ->update($data);
                }
            }

            // --- Lakukan Insert ---
            if (!empty($materialsToInsert)) {
                BomDetail::insert($materialsToInsert);
            }

            // --- Delete Details Not Submitted ---
            // Bandingkan ID detail yang ada sebelumnya dengan ID detail yang di-submit (untuk update)
            $existingDetailIdsInt = array_map('intval', $existingDetailIds);
            $submittedDetailIdsInt = array_map('intval', $submittedDetailIds);
            $detailsToDelete = array_diff($existingDetailIdsInt, $submittedDetailIdsInt);

            if (!empty($detailsToDelete)) {
                BomDetail::where('bom_id', $bom->id) // Pastikan hanya hapus dari BoM ini
                    ->whereIn('id', $detailsToDelete)
                    ->delete();
            }

            // Validasi ulang: Pastikan masih ada detail setelah proses
            // Refresh relasi details setelah modifikasi
            $bom->refresh();
            if ($bom->details->isEmpty()) {
                DB::rollBack();
                throw ValidationException::withMessages(['materials' => 'BoM harus memiliki setidaknya 1 material/komponen setelah diperbarui.']);
            }

            $bom->kemasan()->delete(); // Cara sederhana: hapus semua dan buat baru
            if (!empty($validated['kemasan'])) {
                foreach ($validated['kemasan'] as $item) {
                    if (!empty($item['barang_id']) && !empty($item['jumlah'])) {
                        $bom->kemasan()->create([
                            'barang_id' => $item['barang_id'],
                            'jumlah' => $item['jumlah'],
                            'unit_satuan_id' => $item['unit_satuan_id'],
                        ]);
                    }
                }
            }

            DB::commit();
            return to_route('bom.index')->with('success', __('BoM berhasil diperbarui.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating BOM ID ' . $bom->id . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($e instanceof ValidationException) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', __('Gagal memperbarui BoM: Terjadi kesalahan server.'))->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bom $bom): RedirectResponse
    {
        $companyId = session('sessionCompany');
        // Validasi company
        if ($bom->company_id != $companyId) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Hapus detail terlebih dahulu (menggunakan relasi sudah aman karena $bom unik)
            $bom->details()->delete();

            // Hapus BoM utama
            $bom->delete();

            DB::commit();
            return to_route('bom.index')->with('success', __('BoM berhasil dihapus.'));
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorCode = $e->errorInfo[1] ?? null;
            if ($errorCode == 1451) {
                return to_route('bom.index')->with('error', __("BoM tidak bisa dihapus karena mungkin masih terhubung dengan data lain (misal: digunakan dalam produksi?)."));
            }
            Log::error("Error deleting BoM ID {$bom->id}: " . $e->getMessage());
            return to_route('bom.index')->with('error', __("Gagal menghapus BoM: Terjadi kesalahan database."));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting BoM ID {$bom->id}: " . $e->getMessage());
            return to_route('bom.index')->with('error', __("Gagal menghapus BoM atau detail terkait."));
        }
    }
}
