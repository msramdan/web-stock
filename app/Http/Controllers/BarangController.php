<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Http\Requests\Barangs\{StoreBarangRequest, UpdateBarangRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use App\Models\JenisMaterial;
use App\Models\UnitSatuan;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;


class BarangController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $photoBarangPath = '')
    {
        $this->photoBarangPath = storage_path('app/public/uploads/photo-barangs/');
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:barang view', only: ['index', 'show']),
            new Middleware('permission:barang create', only: ['create', 'store']),
            new Middleware('permission:barang edit', only: ['edit', 'update']),
            new Middleware('permission:barang delete', only: ['destroy']),
            new Middleware('permission:barang export pdf', only: ['exportPdf']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $companyId = session('sessionCompany'); // Ambil company ID
            $barangs = DB::table('barang')
                ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
                ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
                ->select(
                    'barang.*',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                )
                // Filter berdasarkan company_id dari session
                ->where('barang.company_id', $companyId); // Terapkan filter

            return DataTables::of($barangs)
                ->addColumn('tipe_barang', function ($row) { // Tambahkan render untuk tipe barang jika perlu styling
                    if ($row->tipe_barang == 'Barang Jadi') {
                        return '<span class="badge bg-light-primary">Barang Jadi</span>';
                    } elseif ($row->tipe_barang == 'Bahan Baku') {
                        return '<span class="badge bg-light-secondary">Bahan Baku</span>';
                    }
                    return $row->tipe_barang ?? '-';
                })
                ->addColumn('deskripsi_barang', function ($row) {
                    return str($row->deskripsi_barang)->limit(100);
                })
                ->addColumn('jenis_material', function ($row) {
                    return $row->nama_jenis_material ?? '';
                })
                ->addColumn('unit_satuan', function ($row) {
                    return $row->nama_unit_satuan ?? '';
                })
                ->addColumn('stock_barang', function ($row) {
                    return rtrim(rtrim(number_format((float)$row->stock_barang, 4, '.', ''), '0'), '.');
                })
                ->addColumn('photo_barang', function ($row) {
                    if (!$row->photo_barang) {
                        // Jika perlu, gunakan URL default dari .env atau config
                        return asset('assets/static/images/faces/2.jpg'); // Placeholder
                        // return 'https://dummyimage.com/150x100/cccccc/000000&text=No+Image';
                    }
                    // Pastikan path storage sesuai
                    return asset('storage/uploads/photo-barangs/' . $row->photo_barang);
                })
                ->addColumn('action', 'barang.include.action')
                ->rawColumns(['tipe_barang', 'action'])
                ->toJson();
        }

        return view('barang.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companyId = session('sessionCompany');

        $jenisMaterials = JenisMaterial::where('company_id', $companyId)
            ->orderBy('nama_jenis_material')
            ->get(['id', 'nama_jenis_material']);

        $unitSatuans = UnitSatuan::where('company_id', $companyId) // <- Filter penting
            ->orderBy('nama_unit_satuan')
            ->get(['id', 'nama_unit_satuan']); // <- Gunakan get()

        return view('barang.create', compact('jenisMaterials', 'unitSatuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBarangRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $companyId = session('sessionCompany');

        // --- Validasi tambahan: Pastikan JenisMaterial & UnitSatuan dari company yang sama ---
        $jenisMaterialValid = JenisMaterial::where('id', $validated['jenis_material_id'])
            ->where('company_id', $companyId)
            ->exists();
        $unitSatuanValid = UnitSatuan::where('id', $validated['unit_satuan_id'])
            ->where('company_id', $companyId)
            ->exists();

        if (!$jenisMaterialValid || !$unitSatuanValid) {
            return back()->withErrors([
                'jenis_material_id' => !$jenisMaterialValid ? 'Jenis material tidak valid untuk perusahaan ini.' : null,
                'unit_satuan_id' => !$unitSatuanValid ? 'Unit satuan tidak valid untuk perusahaan ini.' : null,
            ])->withInput()->with('error', 'Data relasi tidak sesuai dengan perusahaan Anda.');
        }
        // --- End Validasi Tambahan ---

        $validated['photo_barang'] = $this->imageService->upload(name: 'photo_barang', path: $this->photoBarangPath);

        // Tambahkan company_id dari session
        $validated['company_id'] = $companyId;

        // Inisialisasi stock_barang jika belum ada
        $validated['stock_barang'] = $validated['stock_barang'] ?? 0;

        Barang::create($validated);

        return to_route('barang.index')->with('success', __('The barang was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang): View
    {
        // Optional: Validasi company
        if ($barang->company_id != session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }

        $barang->load(['jenisMaterial', 'unitSatuan', 'company']); // Load relasi termasuk company

        return view('barang.show', compact('barang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang): View
    {
        // Optional: Validasi company
        if ($barang->company_id != session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }

        $barang->load(['jenisMaterial', 'unitSatuan']); // Load relasi

        // Ambil data relasi HANYA dari company yang aktif untuk dropdown
        $companyId = session('sessionCompany');

        // Ambil collection object, bukan hasil pluck
        $jenisMaterials = JenisMaterial::where('company_id', $companyId)
            ->orderBy('nama_jenis_material')
            ->get(['id', 'nama_jenis_material']); // Ambil kolom yang dibutuhkan

        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan')
            ->get(['id', 'nama_unit_satuan']); // Ambil kolom yang dibutuhkan


        return view('barang.edit', compact('barang', 'jenisMaterials', 'unitSatuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBarangRequest $request, Barang $barang): RedirectResponse
    {
        // Optional: Validasi company
        if ($barang->company_id != session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();
        $companyId = session('sessionCompany');

        // --- Validasi tambahan: Pastikan JenisMaterial & UnitSatuan dari company yang sama ---
        $jenisMaterialValid = JenisMaterial::where('id', $validated['jenis_material_id'])
            ->where('company_id', $companyId)
            ->exists();
        $unitSatuanValid = UnitSatuan::where('id', $validated['unit_satuan_id'])
            ->where('company_id', $companyId)
            ->exists();

        if (!$jenisMaterialValid || !$unitSatuanValid) {
            return back()->withErrors([
                'jenis_material_id' => !$jenisMaterialValid ? 'Jenis material tidak valid untuk perusahaan ini.' : null,
                'unit_satuan_id' => !$unitSatuanValid ? 'Unit satuan tidak valid untuk perusahaan ini.' : null,
            ])->withInput()->with('error', 'Data relasi tidak sesuai dengan perusahaan Anda.');
        }
        // --- End Validasi Tambahan ---


        $validated['photo_barang'] = $this->imageService->upload(name: 'photo_barang', path: $this->photoBarangPath, defaultImage: $barang?->photo_barang);

        // Pastikan company_id tidak ikut terupdate jika tidak diinginkan
        // unset($validated['company_id']);

        // Inisialisasi stock_barang jika belum ada (meskipun biasanya sudah ada saat update)
        $validated['stock_barang'] = $validated['stock_barang'] ?? $barang->stock_barang;


        $barang->update($validated);

        return to_route('barang.index')->with('success', __('The barang was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang): RedirectResponse
    {
        // Optional: Validasi company
        if ($barang->company_id != session('sessionCompany')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Hapus gambar terkait sebelum menghapus record
            $photoBarang = $barang->photo_barang;
            if ($photoBarang) {
                $this->imageService->delete(image: $this->photoBarangPath . $photoBarang);
            }

            $barang->delete();

            return to_route('barang.index')->with('success', __('The barang was deleted successfully.'));
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error constraint violation secara spesifik
            $errorCode = $e->errorInfo[1] ?? null;
            if ($errorCode == 1451) { // Kode error MySQL untuk constraint violation
                return to_route('barang.index')->with('error', __("Barang tidak bisa dihapus karena terhubung dengan data lain (misal: Transaksi, BoM)."));
            }

            return to_route('barang.index')->with('error', __("Gagal menghapus barang: Terjadi kesalahan database."));
        } catch (\Exception $e) {

            return to_route('barang.index')->with('error', __("Gagal menghapus barang: Terjadi kesalahan tidak dikenal."));
        }
    }

    /**
     * Menyediakan data barang untuk AJAX (misal: form transaksi).
     */
    public function listDataBarang()
    {
        $companyId = session('sessionCompany'); // Ambil company ID
        $barang = DB::table('barang')
            ->join('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
            ->join('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
            ->select(
                'barang.id',
                'barang.kode_barang',
                'barang.nama_barang', // Tambahkan nama barang jika perlu
                'barang.stock_barang as stock',
                'jenis_material.nama_jenis_material as jenis_material',
                'unit_satuan.nama_unit_satuan as unit_satuan'
            )
            // Filter berdasarkan company_id
            ->where('barang.company_id', $companyId)
            ->orderBy('barang.nama_barang') // Urutkan agar mudah dicari
            ->get();
        return response()->json($barang);
    }

    /**
     * Export data barang to PDF.
     */
    public function exportPdf(): RedirectResponse|StreamedResponse
    {

        try {
            $companyId = session('sessionCompany');
            if (!$companyId) {
                return redirect()->route('barang.index')->with('error', 'Gagal export PDF: Silakan pilih perusahaan.');
            }

            $activeCompany = Company::find($companyId);
            if (!$activeCompany) {
                return redirect()->route('barang.index')->with('error', 'Gagal export PDF: Perusahaan tidak ditemukan.');
            }
            $namaPerusahaanCetak = $activeCompany->nama_perusahaan;

            // Ambil Data Barang (filter companyId)
            $barangs = DB::table('barang')
                ->leftJoin('jenis_material', function ($join) use ($companyId) {
                    $join->on('barang.jenis_material_id', '=', 'jenis_material.id')
                        ->where('jenis_material.company_id', '=', $companyId);
                })
                ->leftJoin('unit_satuan', function ($join) use ($companyId) {
                    $join->on('barang.unit_satuan_id', '=', 'unit_satuan.id')
                        ->where('unit_satuan.company_id', '=', $companyId);
                })
                ->where('barang.company_id', $companyId)
                ->select(
                    'barang.kode_barang',
                    'barang.nama_barang',
                    'barang.deskripsi_barang',
                    'barang.stock_barang',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                )
                ->orderBy('barang.kode_barang')
                ->get();

            // Panggil helper HANYA dengan $activeCompany
            $logoUrl = function_exists('get_company_logo_base64') ? get_company_logo_base64($activeCompany) : null;

            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()?->name ?? 'N/A';

            // Kirim $activeCompany ke view, HAPUS $setting
            $data = compact('barangs', 'activeCompany', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaanCetak'); // Pastikan 'activeCompany' ada

            $pdf = Pdf::loadView('barang.export-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true);

            $filename = 'Data-Barang-' . Str::slug($namaPerusahaanCetak) . '-' . date('YmdHis') . '.pdf';

            // === PENDEKATAN MANUAL STREAM (Sudah Benar Sebelumnya) ===
            try {
                $pdfOutput = $pdf->output();
                return response()->streamDownload(
                    function () use ($pdfOutput) {
                        echo $pdfOutput;
                    },
                    $filename,
                    ['Content-Type' => 'application/pdf']
                );
            } catch (\Throwable $renderOrOutputError) {
                Log::error('Gagal saat render/output PDF Barang: ' . $renderOrOutputError->getMessage(), [
                    'company_id' => session('sessionCompany'),
                    'user_id' => auth()->id(),
                    'file' => $renderOrOutputError->getFile(),
                    'line' => $renderOrOutputError->getLine()
                ]);
                return redirect()->route('barang.index')->with('error', 'Gagal saat generate output PDF. (' . $renderOrOutputError->getMessage() . ')');
            }
            // === AKHIR PENDEKATAN MANUAL STREAM ===

        } catch (\Throwable $th) {
            Log::error('Gagal membuat PDF Barang (Outer Catch): ' . $th->getMessage(), [
                'company_id' => session('sessionCompany'),
                'user_id' => auth()->id(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);
            // Perbaiki pesan error agar lebih informatif
            return redirect()->route('barang.index')->with('error', 'Gagal memproses PDF data barang. (' . $th->getMessage() . ')');
        }
    }
}
