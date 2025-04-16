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
use Illuminate\Support\Facades\DB;

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
            $barangs = DB::table('barang')
                ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
                ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
                ->select(
                    'barang.*',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                );

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
                        return 'https://dummyimage.com/150x100/cccccc/000000&text=No+Image';
                    }

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
        return view('barang.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBarangRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['photo_barang'] = $this->imageService->upload(name: 'photo_barang', path: $this->photoBarangPath);

        Barang::create($validated);

        return to_route('barang.index')->with('success', __('The barang was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang): View
    {
        $barang->load(['jenis_material:id', 'unit_satuan:id']);

        return view('barang.show', compact('barang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang): View
    {
        $barang->load(['jenis_material:id', 'unit_satuan:id']);

        return view('barang.edit', compact('barang'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBarangRequest $request, Barang $barang): RedirectResponse
    {
        $validated = $request->validated();

        $validated['photo_barang'] = $this->imageService->upload(name: 'photo_barang', path: $this->photoBarangPath, defaultImage: $barang?->photo_barang);

        $barang->update($validated);

        return to_route('barang.index')->with('success', __('The barang was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang): RedirectResponse
    {
        try {
            $photoBarang = $barang->photo_barang;

            $barang->delete();

            $this->imageService->delete(image: $this->photoBarangPath . $photoBarang);

            return to_route('barang.index')->with('success', __('The barang was deleted successfully.'));
        } catch (\Exception $e) {
            return to_route('barang.index')->with('error', __("The barang can't be deleted because it's related to another table."));
        }
    }

    public function listDataBarang()
    {
        $barang = DB::table('barang')
            ->join('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
            ->join('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
            ->select(
                'barang.id',
                'barang.kode_barang',
                'barang.stock_barang as stock',
                'jenis_material.nama_jenis_material as jenis_material',
                'unit_satuan.nama_unit_satuan as unit_satuan'
            )
            ->get();
        return response()->json($barang);
    }

    /**
     * Export data barang to PDF.
     */
    public function exportPdf()
    {
        \Log::info('Memanggil metode exportPdf di BarangController');
        try {
            // 1. Ambil data Barang
            $barangs = DB::table('barang')
                ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
                ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
                ->select(
                    'barang.kode_barang',
                    'barang.deskripsi_barang',
                    'barang.stock_barang',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                )
                ->orderBy('barang.kode_barang') // Urutkan berdasarkan kode barang
                ->get();

            // 2. Ambil Setting Aplikasi untuk Header
            $setting = SettingAplikasi::first(); // Ambil setting pertama
            $logoPath = null;
            $logoUrl = null;

            if ($setting && $setting->logo_perusahaan) {
                // Gunakan path dari SettingAplikasiController
                $dbLogoPath = storage_path('app/public/uploads/logo-perusahaans/' . $setting->logo_perusahaan);
                if (file_exists($dbLogoPath)) {
                    $logoPath = $dbLogoPath;
                } else {
                    Log::warning('File logo perusahaan tidak ditemukan di path: ' . $dbLogoPath);
                }
            } else {
                Log::warning('Setting aplikasi atau logo perusahaan tidak ditemukan.');
            }

            // Encode logo ke base64 jika path valid
            if ($logoPath) {
                try {
                    $logoMimeType = mime_content_type($logoPath); // Dapatkan tipe mime
                    if (str_starts_with($logoMimeType, 'image/')) { // Pastikan itu gambar
                        $logoUrl = 'data:' . $logoMimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    } else {
                        Log::warning('File logo bukan gambar: ' . $logoPath);
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal membaca atau encode file logo: ' . $logoPath . ' - Error: ' . $e->getMessage());
                    $logoUrl = null; // Set null jika gagal baca file
                }
            }

            // 3. Data tambahan untuk view PDF
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i'); // Format tanggal Indonesia
            $namaPembuat = auth()->user()->name ?? 'N/A'; // Nama pengguna yang mencetak

            // 4. Siapkan data untuk view
            $data = [
                'barangs' => $barangs,
                'setting' => $setting,
                'logoUrl' => $logoUrl,
                'tanggalCetak' => $tanggalCetak,
                'namaPembuat' => $namaPembuat,
            ];

            // 5. Generate PDF
            $pdf = Pdf::loadView('barang.export-pdf', $data); // Nama view PDF baru
            $pdf->setPaper('a4', 'portrait'); // Atur ukuran kertas (portrait atau landscape)
            $filename = 'Data-Barang-' . date('YmdHis') . '.pdf';
            return $pdf->stream($filename); // Tampilkan di browser

        } catch (\Exception $e) {
            \Log::error('Error generating Barang PDF: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('barang.index')->with('error', 'Gagal membuat PDF data barang. Silakan coba lagi atau hubungi administrator.');
        }
    }
}
