<?php

namespace App\Http\Controllers;

// use App\Models\Transaksi; // Tidak digunakan jika pakai Query Builder
// use App\Http\Requests\Transaksis\{StoreTransaksiRequest, UpdateTransaksiRequest}; // Tidak digunakan
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService; // Tetap dipakai jika ada upload lain
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // Gunakan Request standar
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SettingAplikasi; // Import SettingAplikasi
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF
use Carbon\Carbon; // Import Carbon
use Illuminate\Support\Facades\Log; // Import Log
use Illuminate\Support\Str; // Import Str

class TransaksiStockOutController extends Controller implements HasMiddleware
{
    // Constructor tidak perlu ImageService jika hanya untuk attachment
    public function __construct(/*public ImageService $imageService,*/public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/'); // Path lengkap
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:transaksi stock out view', only: ['index', 'show']),
            new Middleware('permission:transaksi stock out create', only: ['create', 'store']),
            new Middleware('permission:transaksi stock out edit', only: ['edit', 'update']), // Edit/Update belum diimplementasi
            new Middleware('permission:transaksi stock out delete', only: ['destroy']),
            new Middleware('permission:transaksi stock out export pdf', only: ['exportPdf', 'exportItemPdf']), // Tambahkan exportItemPdf
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $companyId = session('sessionCompany'); // Ambil company ID
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'Out') // Filter type Out
                // Filter berdasarkan company_id dari session
                ->where('transaksi.company_id', $companyId)
                ->orderByDesc('transaksi.tanggal');

            return DataTables::of($transaksi)
                ->addColumn('keterangan', function ($row) {
                    return str($row->keterangan)->limit(100);
                })
                ->addColumn('user', function ($row) {
                    return $row->user_name ?? '-';
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)->isoFormat('D MMMM YYYY, HH:mm');
                })
                ->addColumn('attachment', function ($row) {
                    if (!$row->attachment) {
                        return '<span class="text-muted">-</span>';
                    }
                    // Gunakan nama file yang disimpan, dari folder company
                    $url = Storage::url('uploads/attachments/' . $row->company_id . '/' . $row->attachment);
                    $icon = 'bi-file-earmark-arrow-down';
                    $extension = pathinfo($row->attachment, PATHINFO_EXTENSION);
                    if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'bi-file-earmark-image';
                    elseif (strtolower($extension) === 'pdf') $icon = 'bi-file-earmark-pdf';
                    elseif (in_array(strtolower($extension), ['doc', 'docx'])) $icon = 'bi-file-earmark-word';

                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary" title="Download ' . e($row->attachment) . '">
                                <i class="bi ' . $icon . '"></i>
                            </a>';
                })
                ->addColumn('action', 'transaksi-stock-out.include.action')
                ->rawColumns(['attachment', 'action'])
                ->toJson();
        }

        return view('transaksi-stock-out.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Tidak perlu mengirim data barang ke view create jika menggunakan AJAX search
        return view('transaksi-stock-out.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = session('sessionCompany'); // Ambil company ID
        $userId = Auth::id(); // Ambil user ID

        // Validate the request data
        $validator = Validator::make($request->all(), [
            // Unique per company
            'no_surat' => 'required|string|max:255|unique:transaksi,no_surat,NULL,id,company_id,' . $companyId,
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048', // Max 10MB
            'cart_items' => 'required|json',
            // Cek barang exists di company ini
            'cart_items.*.id' => 'required|integer|exists:barang,id,company_id,' . $companyId,
            'cart_items.*.qty' => 'required|integer|min:1',
        ], [
            'no_surat.unique' => 'No. Surat sudah pernah digunakan di perusahaan ini.',
            'cart_items.*.id.exists' => 'Salah satu barang yang dipilih tidak valid atau bukan milik perusahaan ini.',
            'cart_items.*.qty.min' => 'Jumlah barang minimal 1.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cartItems = json_decode($request->cart_items, true);
        if (empty($cartItems)) {
            return redirect()->back()
                ->withErrors(['cart_items' => 'Keranjang tidak boleh kosong.'])
                ->withInput();
        }


        // --- Validasi Stok Sebelum Transaksi ---
        $barangIds = array_column($cartItems, 'id');
        $barangStocks = DB::table('barang')
            ->whereIn('id', $barangIds)
            ->where('company_id', $companyId)
            ->pluck('stock_barang', 'id');

        $stockValidationErrors = [];
        foreach ($cartItems as $item) {
            $barangId = $item['id'];
            $requestedQty = $item['qty'];
            $availableStock = $barangStocks[$barangId] ?? 0; // Ambil stok dari hasil query

            if ($availableStock < $requestedQty) {
                $barangInfo = DB::table('barang')->where('id', $barangId)->value('kode_barang') ?? "ID:{$barangId}";
                $stockValidationErrors[] = "Stok '{$barangInfo}' tidak mencukupi (tersedia: {$availableStock}, diminta: {$requestedQty}).";
            }
        }

        if (!empty($stockValidationErrors)) {
            return redirect()->back()
                ->withErrors(['cart_items' => $stockValidationErrors]) // Kirim error spesifik
                ->withInput()
                ->with('error', 'Gagal membuat transaksi stock out karena stok tidak mencukupi.');
        }
        // --- Akhir Validasi Stok ---

        DB::beginTransaction();

        try {
            // Handle file upload (sama seperti Stock In)
            $attachmentName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $attachmentName = $companyId . '_' . time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/uploads/attachments/' . $companyId, $attachmentName);
            }

            // Create transaction using Query Builder
            $transaksiId = DB::table('transaksi')->insertGetId([
                'company_id' => $companyId, // Simpan company_id
                'no_surat' => $request->no_surat,
                'tanggal' => $request->tanggal,
                'type' => 'Out', // Tipe Out
                'keterangan' => $request->keterangan,
                'attachment' => $attachmentName, // Simpan nama file
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Process cart items
            $transaksiDetails = [];
            $stockUpdates = []; // Array untuk menampung [barang_id => qty]

            foreach ($cartItems as $item) {
                // Validasi sudah dilakukan di awal, cukup siapkan data
                $transaksiDetails[] = [
                    'barang_id' => $item['id'],
                    'qty' => $item['qty'],
                    'transaksi_id' => $transaksiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (isset($stockUpdates[$item['id']])) {
                    $stockUpdates[$item['id']] += $item['qty'];
                } else {
                    $stockUpdates[$item['id']] = $item['qty'];
                }
            }

            // Bulk insert transaction details
            if (!empty($transaksiDetails)) {
                DB::table('transaksi_detail')->insert($transaksiDetails);
            } else {
                throw new \Exception('Tidak ada item detail transaksi yang valid.');
            }


            // Bulk update stock (DECREMENT stock_barang)
            if (!empty($stockUpdates)) {
                foreach ($stockUpdates as $barangId => $totalQty) {
                    DB::table('barang')
                        ->where('id', $barangId)
                        ->where('company_id', $companyId)
                        ->decrement('stock_barang', $totalQty, ['updated_at' => now()]); // <-- DECREMENT
                }
            }

            DB::commit();

            return redirect()->route('transaksi-stock-out.index') // Arahkan ke index Stock Out
                ->with('success', 'Transaksi stock out berhasil dibuat.'); // Pesan sukses Stock Out
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus file yang mungkin sudah terupload jika transaksi gagal
            if ($attachmentName && $companyId) {
                Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
            }
            Log::error('Error storing stock out transaction: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Gagal membuat transaksi stock out: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $companyId = session('sessionCompany'); // Ambil company ID

        // Get transaction data
        $transaksi = DB::table('transaksi')
            ->select('transaksi.*', 'users.name as user_name')
            ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
            ->where('transaksi.id', $id)
            ->where('transaksi.company_id', $companyId) // Filter company
            ->where('transaksi.type', 'Out') // Filter type Out
            ->first();

        if (!$transaksi) {
            abort(404, 'Transaksi tidak ditemukan atau tidak sesuai.');
        }

        // Get transaction details with item information
        $details = DB::table('transaksi_detail')
            ->select(
                'transaksi_detail.qty',
                'barang.kode_barang',
                'barang.nama_barang', // Tambah nama barang
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan'
            )
            ->join('barang', 'transaksi_detail.barang_id', '=', 'barang.id')
            ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
            ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
            ->where('transaksi_detail.transaksi_id', $id)
            ->get();

        // Siapkan URL attachment jika ada
        $attachmentUrl = null;
        if ($transaksi->attachment) {
            $attachmentUrl = Storage::url('uploads/attachments/' . $transaksi->company_id . '/' . $transaksi->attachment);
        }

        return view('transaksi-stock-out.show', [ // View stock-out
            'transaksi' => $transaksi,
            'details' => $details,
            'attachmentUrl' => $attachmentUrl,
        ]);
    }

    // Metode edit() dan update() belum diimplementasikan
    // public function edit(Transaksi $transaksi): View
    // {
    //     // ...
    // }
    // public function update(UpdateTransaksiRequest $request, Transaksi $transaksi): RedirectResponse
    // {
    //      // ...
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $companyId = session('sessionCompany'); // Ambil company ID

        DB::beginTransaction();

        try {
            // 1. Dapatkan data transaksi (pastikan milik company yg benar dan tipe Out)
            $transaksi = DB::table('transaksi')
                ->where('id', $id)
                ->where('company_id', $companyId)
                ->where('type', 'Out') // Pastikan tipe benar
                ->first();

            if (!$transaksi) {
                throw new \Exception('Transaksi stock out tidak ditemukan atau tidak sesuai.');
            }

            // 2. Dapatkan semua detail transaksi
            $details = DB::table('transaksi_detail')
                ->where('transaksi_id', $id)
                ->get();

            // 3. Kembalikan stok barang (karena ini transaksi OUT yang dihapus)
            $stockUpdates = [];
            foreach ($details as $detail) {
                if (isset($stockUpdates[$detail->barang_id])) {
                    $stockUpdates[$detail->barang_id] += $detail->qty;
                } else {
                    $stockUpdates[$detail->barang_id] = $detail->qty;
                }
            }

            if (!empty($stockUpdates)) {
                foreach ($stockUpdates as $barangId => $totalQty) {
                    // Tambah stok (kebalikan dari store)
                    DB::table('barang')
                        ->where('id', $barangId)
                        ->where('company_id', $companyId)
                        ->increment('stock_barang', $totalQty, ['updated_at' => now()]); // <-- INCREMENT
                }
            }

            // 4. Hapus detail transaksi
            DB::table('transaksi_detail')
                ->where('transaksi_id', $id)
                ->delete();

            // 5. Hapus file attachment jika ada
            if ($transaksi->attachment) {
                Storage::delete('public/uploads/attachments/' . $transaksi->company_id . '/' . $transaksi->attachment);
            }

            // 6. Hapus transaksi utama
            DB::table('transaksi')
                ->where('id', $id) // ID sudah unik
                ->delete();

            DB::commit();

            return redirect()->route('transaksi-stock-out.index') // Redirect ke index Stock Out
                ->with('success', 'Transaksi stock out berhasil dihapus dan stok dikembalikan.'); // Pesan sukses Stock Out
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting stock out transaction ID {$id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi stock out: ' . $e->getMessage());
        }
    }

    /**
     * Export data transaksi stock out to PDF.
     */
    public function exportPdf()
    {
        Log::info('Memanggil metode exportPdf di TransaksiStockOutController');
        try {
            $companyId = session('sessionCompany');
            $activeCompany = \App\Models\Company::find($companyId);

            // 1. Ambil data Transaksi Stock Out sesuai company
            $transaksis = DB::table('transaksi')
                ->select('transaksi.no_surat', 'transaksi.tanggal', 'transaksi.type', 'transaksi.keterangan', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'Out') // <-- Filter type Out
                ->where('transaksi.company_id', $companyId) // Filter company
                ->orderByDesc('transaksi.tanggal')
                ->get();

            // 2. Ambil Setting Aplikasi & Logo (Sama)
            $setting = SettingAplikasi::first();
            $logoPath = null;
            $logoUrl = null;
            $logoFilename = $activeCompany?->logo_perusahaan ?? $setting?->logo_perusahaan;
            if ($logoFilename) { /* ... (cek path logo) ... */
                $companyLogoPath = storage_path('app/public/uploads/logo-perusahaans/' . $activeCompany->logo_perusahaan);
                $globalLogoPath = $setting?->logo_perusahaan ? storage_path('app/public/uploads/logo-perusahaans/' . $setting->logo_perusahaan) : null;
                if (file_exists($companyLogoPath)) $logoPath = $companyLogoPath;
                elseif ($globalLogoPath && file_exists($globalLogoPath)) $logoPath = $globalLogoPath;
                else Log::warning('File logo perusahaan/setting tidak ditemukan.');
            } else Log::warning('Nama file logo tidak ditemukan.');
            if ($logoPath) { /* ... (encode logo ke base64) ... */
                try { /* ... */
                    $logoMimeType = mime_content_type($logoPath);
                    if (str_starts_with($logoMimeType, 'image/')) $logoUrl = 'data:' . $logoMimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    else Log::warning('File logo bukan gambar: ' . $logoPath);
                } catch (\Exception $e) { /* ... */
                    Log::error('Gagal baca/encode logo: ' . $e->getMessage());
                    $logoUrl = null;
                }
            }

            // 3. Data tambahan
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()->name ?? 'N/A';
            $namaPerusahaanCetak = $activeCompany?->nama_perusahaan ?? $setting?->nama_perusahaan ?? 'Nama Perusahaan Tidak Ditemukan';

            // 4. Siapkan data
            $data = compact('transaksis', 'setting', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaan');


            // 5. Generate PDF
            $pdf = Pdf::loadView('transaksi-stock-out.export-pdf', $data); // <-- View PDF Stock Out
            $pdf->setPaper('a4', 'portrait');
            $filename = 'Laporan-Transaksi-Keluar-' . Str::slug($namaPerusahaanCetak) . '-' . date('YmdHis') . '.pdf';
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating Transaksi Stock Out PDF: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal membuat PDF Laporan Transaksi Keluar.');
        }
    }

    /**
     * Export detail data transaksi stock out to PDF.
     */
    public function exportItemPdf($id)
    {
        Log::info("Memanggil exportItemPdf untuk Transaksi Stock Out ID: {$id}");
        try {
            $companyId = session('sessionCompany');
            $activeCompany = \App\Models\Company::find($companyId);

            // 1. Ambil data Transaksi Header (filter by ID, company, type Out)
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
                ->where('transaksi.id', $id)
                ->where('transaksi.company_id', $companyId) // Filter company
                ->where('transaksi.type', 'Out') // <-- Filter type Out
                ->first();

            if (!$transaksi) {
                Log::error("Transaksi Stock Out tidak ditemukan untuk ID: {$id} pada company ID: {$companyId}");
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Transaksi tidak ditemukan atau tidak sesuai.');
            }

            // 2. Ambil data Transaksi Detail
            $details = DB::table('transaksi_detail')
                ->select(
                    'transaksi_detail.qty',
                    'barang.kode_barang',
                    'barang.nama_barang',
                    'jenis_material.nama_jenis_material',
                    'unit_satuan.nama_unit_satuan'
                )
                ->join('barang', 'transaksi_detail.barang_id', '=', 'barang.id')
                ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
                ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
                ->where('transaksi_detail.transaksi_id', $id)
                ->get();

            // 3. Ambil Setting Aplikasi & Logo (Sama)
            $setting = SettingAplikasi::first();
            $logoPath = null;
            $logoUrl = null;
            $logoFilename = $activeCompany?->logo_perusahaan ?? $setting?->logo_perusahaan;
            if ($logoFilename) { /* ... (cek path logo) ... */
                $companyLogoPath = storage_path('app/public/uploads/logo-perusahaans/' . $activeCompany->logo_perusahaan);
                $globalLogoPath = $setting?->logo_perusahaan ? storage_path('app/public/uploads/logo-perusahaans/' . $setting->logo_perusahaan) : null;
                if (file_exists($companyLogoPath)) $logoPath = $companyLogoPath;
                elseif ($globalLogoPath && file_exists($globalLogoPath)) $logoPath = $globalLogoPath;
                else Log::warning('File logo perusahaan/setting tidak ditemukan.');
            } else Log::warning('Nama file logo tidak ditemukan.');
            if ($logoPath) { /* ... (encode logo ke base64) ... */
                try { /* ... */
                    $logoMimeType = mime_content_type($logoPath);
                    if (str_starts_with($logoMimeType, 'image/')) $logoUrl = 'data:' . $logoMimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    else Log::warning('File logo bukan gambar: ' . $logoPath);
                } catch (\Exception $e) { /* ... */
                    Log::error('Gagal baca/encode logo: ' . $e->getMessage());
                    $logoUrl = null;
                }
            }

            // 4. Data tambahan
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()->name ?? 'N/A';
            $namaPerusahaanCetak = $activeCompany?->nama_perusahaan ?? $setting?->nama_perusahaan ?? 'Nama Perusahaan Tidak Ditemukan';

            // 5. Siapkan data
            $data = compact('transaksi', 'details', 'setting', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaan');

            // 6. Generate PDF
            $pdf = Pdf::loadView('transaksi-stock-out.export-item-pdf', $data); // <-- View PDF Item Stock Out
            $pdf->setPaper('a4', 'portrait');
            $filename = 'Detail-Transaksi-Keluar-' . Str::slug($namaPerusahaanCetak) . '-' . ($transaksi->no_surat ?? $id) . '.pdf';
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error("Error generating Detail Transaksi Stock Out PDF for ID {$id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal membuat PDF Detail Transaksi Keluar.');
        }
    }
}
