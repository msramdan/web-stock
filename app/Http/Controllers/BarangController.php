<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Http\Requests\Barangs\{StoreBarangRequest, UpdateBarangRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use App\Models\SettingAplikasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB; // Pastikan DB di-import
use App\Models\JenisMaterial; // Import model relasi
use App\Models\UnitSatuan; // Import model relasi

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
                ->addColumn('deskripsi_barang', function ($row) {
                    return str($row->deskripsi_barang)->limit(100);
                })
                ->addColumn('jenis_material', function ($row) {
                    return $row->nama_jenis_material ?? '';
                })
                ->addColumn('unit_satuan', function ($row) {
                    return $row->nama_unit_satuan ?? '';
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
        \Log::info('BarangController::create() - ID Company dari Session: ' . $companyId); // Optional log

        $jenisMaterials = JenisMaterial::where('company_id', $companyId)
            ->orderBy('nama_jenis_material')
            ->get(['id', 'nama_jenis_material']);

        $unitSatuans = UnitSatuan::where('company_id', $companyId) // <- Filter penting
            ->orderBy('nama_unit_satuan')
            ->get(['id', 'nama_unit_satuan']); // <- Gunakan get()

        \Log::info('BarangController::create() - Jumlah Unit Satuan ditemukan: ' . $unitSatuans->count()); // Optional log


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
        if ($barang->company_id !== session('sessionCompany')) {
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
        if ($barang->company_id !== session('sessionCompany')) {
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
        if ($barang->company_id !== session('sessionCompany')) {
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
        if ($barang->company_id !== session('sessionCompany')) {
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
            Log::error("Error deleting Barang ID {$barang->id}: " . $e->getMessage());
            return to_route('barang.index')->with('error', __("Gagal menghapus barang: Terjadi kesalahan database."));
        } catch (\Exception $e) {
            Log::error("Error deleting Barang ID {$barang->id}: " . $e->getMessage());
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
    public function exportPdf()
    {
        Log::info('Memanggil metode exportPdf di BarangController');
        try {
            $companyId = session('sessionCompany'); // Ambil company ID

            // 1. Ambil data Barang sesuai company
            $barangs = DB::table('barang')
                ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
                ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
                ->select(
                    'barang.kode_barang',
                    'barang.nama_barang', // Tambahkan nama barang
                    'barang.deskripsi_barang',
                    'barang.stock_barang',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                )
                ->where('barang.company_id', $companyId) // Filter company
                ->orderBy('barang.kode_barang')
                ->get();

            // 2. Ambil Setting Aplikasi (asumsi setting global, bukan per company)
            // Jika setting per company, perlu penyesuaian
            $setting = SettingAplikasi::first();
            $logoPath = null;
            $logoUrl = null;

            // Ambil logo dari company yang aktif jika ada, fallback ke setting global
            $activeCompany = \App\Models\Company::find($companyId);
            $logoFilename = $activeCompany?->logo_perusahaan ?? $setting?->logo_perusahaan;

            if ($logoFilename) {
                // Cek path logo company dulu
                $companyLogoPath = storage_path('app/public/uploads/logo-perusahaans/' . $activeCompany->logo_perusahaan);
                // Jika tidak ada, cek path logo setting global
                $globalLogoPath = $setting?->logo_perusahaan ? storage_path('app/public/uploads/logo-perusahaans/' . $setting->logo_perusahaan) : null;

                if (file_exists($companyLogoPath)) {
                    $logoPath = $companyLogoPath;
                } elseif ($globalLogoPath && file_exists($globalLogoPath)) {
                    $logoPath = $globalLogoPath;
                } else {
                    Log::warning('File logo perusahaan (' . ($activeCompany->logo_perusahaan ?? 'N/A') . ') atau setting (' . ($setting?->logo_perusahaan ?? 'N/A') . ') tidak ditemukan.');
                }
            } else {
                Log::warning('Nama file logo perusahaan atau setting aplikasi tidak ditemukan.');
            }

            // Encode logo ke base64 jika path valid
            if ($logoPath) {
                try {
                    $logoMimeType = mime_content_type($logoPath);
                    if (str_starts_with($logoMimeType, 'image/')) {
                        $logoUrl = 'data:' . $logoMimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    } else {
                        Log::warning('File logo bukan gambar: ' . $logoPath);
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal membaca atau encode file logo: ' . $logoPath . ' - Error: ' . $e->getMessage());
                    $logoUrl = null;
                }
            }

            // 3. Data tambahan untuk view PDF
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()->name ?? 'N/A';
            $namaPerusahaanCetak = $activeCompany?->nama_perusahaan ?? $setting?->nama_perusahaan ?? 'Nama Perusahaan Tidak Ditemukan';

            // 4. Siapkan data untuk view
            $data = [
                'barangs' => $barangs,
                'setting' => $setting, // Kirim setting global jika masih diperlukan di view
                'logoUrl' => $logoUrl,
                'tanggalCetak' => $tanggalCetak,
                'namaPembuat' => $namaPembuat,
                'namaPerusahaan' => $namaPerusahaanCetak, // Kirim nama perusahaan aktif
            ];

            // 5. Generate PDF
            $pdf = Pdf::loadView('barang.export-pdf', $data);
            $pdf->setPaper('a4', 'portrait');
            $filename = 'Data-Barang-' . Str::slug($namaPerusahaanCetak) . '-' . date('YmdHis') . '.pdf';
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating Barang PDF: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('barang.index')->with('error', 'Gagal membuat PDF data barang. Silakan coba lagi atau hubungi administrator.');
        }
    }
}
