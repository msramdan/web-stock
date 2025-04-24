<?php

namespace App\Http\Controllers;

// Import model dan request yang diperlukan
use App\Models\{Bom, BomDetail, Barang, UnitSatuan};
use App\Http\Requests\Boms\{StoreBomRequest, UpdateBomRequest}; // Pastikan request ini ada
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
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
            $boms = DB::table('bom')
                ->leftJoin('barang', 'bom.barang_id', '=', 'barang.id')
                ->select(
                    'bom.id', // Pastikan ID BOM ada
                    'bom.deskripsi',
                    'bom.created_at',
                    'bom.updated_at',
                    'barang.kode_barang',
                    'barang.nama_barang'
                );

            return DataTables::of($boms)
                ->addColumn('nama_barang', function ($row) {
                    return $row->nama_barang ?? '-';
                })
                ->addColumn('kode_barang', function ($row) {
                    return $row->kode_barang ?? '-';
                })
                ->addColumn('deskripsi', function ($row) {
                    return Str::limit($row->deskripsi, 100);
                })
                ->addColumn('action', 'bom.include.action') // Menggunakan view action yang Anda unggah
                ->toJson();
        }

        return view('bom.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Ambil data barang yang BISA menjadi material (dengan unit satuannya)
        $barangMaterials = Barang::with('unitSatuan')->orderBy('nama_barang')->get();

        // Ambil data barang yang BISA menjadi produk jadi
        $produkJadi = Barang::orderBy('nama_barang')->get();

        return view('bom.create', compact('barangMaterials', 'produkJadi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Validasi tambahan: pastikan ada minimal 1 material
        if (empty($validated['materials'])) {
            throw ValidationException::withMessages(['materials' => 'Minimal harus ada 1 material/komponen yang ditambahkan.']);
        }

        DB::beginTransaction();
        try {
            // 1. Simpan data BoM utama
            $bom = Bom::create([
                'barang_id' => $validated['barang_id'],
                'deskripsi' => $validated['deskripsi'],
            ]);

            // 2. Siapkan dan simpan data detail material
            $materialsToInsert = [];
            foreach ($validated['materials'] as $materialData) {
                if (isset($materialData['barang_id'], $materialData['jumlah'], $materialData['unit_satuan_id'])) {
                    $materialsToInsert[] = [
                        'bom_id' => $bom->id,
                        'barang_id' => $materialData['barang_id'],
                        'jumlah' => $materialData['jumlah'],
                        'unit_satuan_id' => $materialData['unit_satuan_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    Log::warning('Data material tidak lengkap saat store BOM: ', $materialData);
                }
            }

            if (!empty($materialsToInsert)) {
                BomDetail::insert($materialsToInsert);
            } else {
                DB::rollBack();
                throw ValidationException::withMessages(['materials' => 'Tidak ada data material valid yang bisa disimpan.']);
            }

            DB::commit();
            return to_route('bom.index')->with('success', __('BoM berhasil dibuat.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing BOM: ' . $e->getMessage());
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
        // Eager load relasi yang dibutuhkan untuk view detail
        $bom->load([
            'barang.unitSatuan', // Produk jadi dan unitnya
            'details.material.unitSatuan', // Detail, materialnya, dan unit materialnya
            'details.unitSatuan' // Detail dan unit satuan yang tersimpan di detail
        ]);

        return view('bom.show', compact('bom')); // Menggunakan view show.blade.php yang Anda unggah
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bom $bom): View
    {
        // Eager load relasi yang dibutuhkan untuk form edit
        $bom->load([
            'barang', // Produk jadi
            'details.material.unitSatuan', // Detail, materialnya, dan unit materialnya
            'details.unitSatuan' // Detail dan unit satuan yang tersimpan di detail
        ]);

        // Ambil data barang untuk pilihan dropdown
        $barangMaterials = Barang::with('unitSatuan')->orderBy('nama_barang')->get();
        $produkJadi = Barang::orderBy('nama_barang')->get();

        return view('bom.edit', compact('bom', 'barangMaterials', 'produkJadi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBomRequest $request, Bom $bom): RedirectResponse
    {
        $validated = $request->validated();

        // Validasi tambahan: pastikan ada minimal 1 material setelah update
        if (empty($validated['materials'])) {
            throw ValidationException::withMessages(['materials' => 'Minimal harus ada 1 material/komponen.']);
        }

        DB::beginTransaction();
        try {
            // 1. Update data BoM utama
            $bom->update([
                'barang_id' => $validated['barang_id'],
                'deskripsi' => $validated['deskripsi'],
            ]);

            // 2. Proses detail material
            $existingDetailIds = $bom->details->pluck('id')->toArray();
            $submittedDetailIds = [];
            $materialsToInsert = [];
            $materialsToUpdate = [];

            foreach ($validated['materials'] as $materialData) {
                if (!isset($materialData['barang_id'], $materialData['jumlah'], $materialData['unit_satuan_id'])) {
                    Log::warning('Data material tidak lengkap saat update BOM: ', $materialData);
                    continue;
                }

                $detailId = $materialData['detail_id'] ?? null;

                $dataPayload = [
                    'bom_id' => $bom->id,
                    'barang_id' => $materialData['barang_id'],
                    'jumlah' => $materialData['jumlah'],
                    'unit_satuan_id' => $materialData['unit_satuan_id'],
                    'updated_at' => now(), // Set updated_at
                ];

                if ($detailId && in_array($detailId, $existingDetailIds)) {
                    // --- Antrikan Update ---
                    $materialsToUpdate[$detailId] = $dataPayload;
                    $submittedDetailIds[] = (int)$detailId;
                } else {
                    // --- Antrikan Insert ---
                    $dataPayload['created_at'] = now();
                    $materialsToInsert[] = $dataPayload;
                }
            }

            // --- Lakukan Update ---
            if (!empty($materialsToUpdate)) {
                foreach ($materialsToUpdate as $id => $data) {
                    // Hapus bom_id dari payload data update karena kita sudah filter berdasarkan bom_id di where
                    unset($data['bom_id']);
                    BomDetail::where('id', $id)->where('bom_id', $bom->id)->update($data);
                }
            }

            // --- Lakukan Insert ---
            if (!empty($materialsToInsert)) {
                BomDetail::insert($materialsToInsert);
            }

            // --- Delete Details Not Submitted ---
            $existingDetailIdsInt = array_map('intval', $existingDetailIds);
            $submittedDetailIdsInt = array_map('intval', $submittedDetailIds);
            $detailsToDelete = array_diff($existingDetailIdsInt, $submittedDetailIdsInt);

            if (!empty($detailsToDelete)) {
                BomDetail::where('bom_id', $bom->id)->whereIn('id', $detailsToDelete)->delete();
            }

            // Validasi ulang: Pastikan masih ada detail setelah proses
            $remainingDetailsCount = BomDetail::where('bom_id', $bom->id)->count();
            if ($remainingDetailsCount === 0) {
                DB::rollBack();
                throw ValidationException::withMessages(['materials' => 'BoM harus memiliki setidaknya 1 material/komponen setelah diperbarui.']);
            }

            DB::commit();
            return to_route('bom.index')->with('success', __('BoM berhasil diperbarui.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating BOM: ' . $e->getMessage());
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
        DB::beginTransaction();
        try {
            // Hapus detail terlebih dahulu (menggunakan relasi)
            $bom->details()->delete();

            // Hapus BoM utama
            $bom->delete();

            DB::commit();
            return to_route('bom.index')->with('success', __('BoM berhasil dihapus.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting BOM: ' . $e->getMessage());
            // Cek constraint violation
            if (str_contains($e->getMessage(), 'constraint violation')) {
                return to_route('bom.index')->with('error', __("BoM tidak bisa dihapus karena mungkin masih terhubung dengan data lain."));
            }
            return to_route('bom.index')->with('error', __("Gagal menghapus BoM atau detail terkait."));
        }
    }
}
