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
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BarangExport;


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
            new Middleware('permission:barang export excel', only: ['exportExcel']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse // Tambahkan Request $request
    {
        if ($request->ajax()) { // Gunakan $request
            $companyId = session('sessionCompany');
            $query = DB::table('barang')
                ->leftJoin('jenis_material', function ($join) use ($companyId) { // Tambahkan use $companyId
                    $join->on('barang.jenis_material_id', '=', 'jenis_material.id')
                        ->where('jenis_material.company_id', '=', $companyId); // Filter join
                })
                ->leftJoin('unit_satuan', function ($join) use ($companyId) { // Tambahkan use $companyId
                    $join->on('barang.unit_satuan_id', '=', 'unit_satuan.id')
                        ->where('unit_satuan.company_id', '=', $companyId); // Filter join
                })
                ->select(
                    'barang.*',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                )
                ->where('barang.company_id', $companyId);

            // --- TERAPKAN FILTER TIPE BARANG ---
            if ($request->filled('tipe_barang')) {
                $query->where('barang.tipe_barang', $request->input('tipe_barang'));
            }
            // --- AKHIR FILTER ---

            return DataTables::of($query)
                ->addColumn('tipe_barang', function ($row) {
                    if ($row->tipe_barang == 'Barang Jadi') return '<span class="badge bg-light-primary">Barang Jadi</span>';
                    if ($row->tipe_barang == 'Bahan Baku') return '<span class="badge bg-light-secondary">Bahan Baku</span>';
                    return $row->tipe_barang ?? '-';
                })
                ->addColumn('deskripsi_barang', function ($row) {
                    return Str::limit($row->deskripsi_barang, 50);
                })
                ->addColumn('jenis_material', function ($row) {
                    return $row->nama_jenis_material ?? '-';
                })
                ->addColumn('unit_satuan', function ($row) {
                    return $row->nama_unit_satuan ?? '-';
                })
                ->addColumn('stock_barang', function ($row) {
                    return formatAngkaRibuan($row->stock_barang);
                })
                ->addColumn('harga', function ($row) {
                    return $row->harga !== null ? formatRupiah($row->harga) : '-';
                })
                ->addColumn('total_harga', function ($row) {
                    $total = ($row->harga ?? 0) * ($row->stock_barang ?? 0);
                    return formatRupiah($total);
                })
                ->addColumn('photo_barang', function ($row) {
                    return $row->photo_barang ?: null;
                })
                ->addColumn('action', 'barang.include.action')
                ->rawColumns(['tipe_barang', 'photo_barang', 'action'])
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

        // Set harga to null if not Bahan Baku
        if ($validated['tipe_barang'] !== 'Bahan Baku') {
            $validated['harga'] = null;
        }

        // Tambahkan company_id dari session
        $validated['company_id'] = $companyId;

        // Inisialisasi stock_barang jika belum ada
        $validated['stock_barang'] = $validated['stock_barang'] ?? 0;

        Barang::create($validated);

        return to_route('barang.index')->with('success', __('The barang was created successfully.'));
    }

    public function show($id): View
    {
        $companyId = session('sessionCompany');

        $barang = DB::table('barang')
            ->leftJoin('jenis_material', function ($join) use ($companyId) {
                $join->on('barang.jenis_material_id', '=', 'jenis_material.id')
                    ->where('jenis_material.company_id', '=', $companyId);
            })
            ->leftJoin('unit_satuan', function ($join) use ($companyId) {
                $join->on('barang.unit_satuan_id', '=', 'unit_satuan.id')
                    ->where('unit_satuan.company_id', '=', $companyId);
            })
            ->where('barang.id', $id)
            ->where('barang.company_id', $companyId)
            ->select(
                'barang.*',
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan'
            )
            ->first();

        if (!$barang) {
            abort(403, 'Unauthorized action or data not found.');
        }

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

        // Set harga to null if not Bahan Baku
        if ($validated['tipe_barang'] !== 'Bahan Baku') {
            $validated['harga'] = null;
        }

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
    public function exportPdf(Request $request): RedirectResponse|StreamedResponse
    {
        $companyId = session('sessionCompany');
        if (!$companyId) return redirect()->route('barang.index')->with('error', '...');
        $activeCompany = Company::find($companyId);
        if (!$activeCompany) return redirect()->route('barang.index')->with('error', '...');
        $namaPerusahaanCetak = $activeCompany->nama_perusahaan;

        // Query dasar
        $query = DB::table('barang')
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
                'barang.tipe_barang',
                'barang.harga',
                'barang.deskripsi_barang',
                'barang.stock_barang',
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan'
            );

        // Terapkan filter tipe barang jika ada
        if ($request->filled('tipe_barang')) {
            $query->where('barang.tipe_barang', $request->input('tipe_barang'));
        }

        $barangs = $query->orderBy('barang.kode_barang')->get();

        // Persiapan data untuk PDF
        $logoUrl = get_company_logo_base64($activeCompany);
        $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
        $namaPembuat = auth()->user()?->name ?? 'N/A';
        $data = compact('barangs', 'activeCompany', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaanCetak');

        // Generate PDF dengan format nama file baru
        $pdf = Pdf::loadView('barang.export-pdf', $data)->setPaper('a4', 'portrait')->setOption('isRemoteEnabled', true);

        // Format nama file baru: tanggal dan jam di awal dengan format Y-m-d_H-i
        $filename = date('Y-m-d_H-i') . '-Data-Barang-' . str_replace(' ', '-', strtoupper($namaPerusahaanCetak));
        if ($request->filled('tipe_barang')) {
            $filename .= '-' . Str::slug($request->input('tipe_barang'));
        }
        $filename .= '.pdf';

        $pdfOutput = $pdf->output();
        return response()->stream(
            function () use ($pdfOutput) {
                echo $pdfOutput;
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    /**
     * Export data barang to Excel.
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->user()->can('barang export excel')) {
            abort(403, 'Anda tidak punya izin untuk ekspor Excel data barang.');
        }

        try {
            $companyId = session('sessionCompany');
            $activeCompany = Company::find($companyId);
            $namaPerusahaan = $activeCompany ? str_replace(' ', '-', strtoupper($activeCompany->nama_perusahaan)) : 'DATA';


            // Ambil filter tipe barang dari request
            $tipeBarangFilter = $request->input('tipe_barang');

            // Format tanggal dan jam dengan pemisah dan hanya jam:menit
            $filename = date('Y-m-d_H-i') . '-Data-Barang-' . $namaPerusahaan;
            if ($tipeBarangFilter) {
                $filename .= '-' . Str::slug($tipeBarangFilter);
            }
            $filename .= '.xlsx';

            // Panggil export class, lewatkan filter
            return Excel::download(new BarangExport($tipeBarangFilter), $filename);
        } catch (\Exception $e) {
            return redirect()->route('barang.index')->with('error', 'Gagal mengekspor data barang ke Excel.');
        }
    }
}
