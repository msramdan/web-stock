<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request; // Gunakan Request standar
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF
use Carbon\Carbon; // Import Carbon
use Illuminate\Support\Facades\Log; // Import Log
use Illuminate\Support\Str; // Import Str
use Symfony\Component\HttpFoundation\StreamedResponse; // <-- Tambahkan ini


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
                    return formatTanggalIndonesia($row->tanggal);
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
            // Perubahan: validasi numeric > 0
            'cart_items.*.qty' => 'required|numeric|gt:0',
        ], [
            'no_surat.unique' => 'No. Surat sudah pernah digunakan di perusahaan ini.',
            'cart_items.*.id.exists' => 'Salah satu barang yang dipilih tidak valid atau bukan milik perusahaan ini.',
            // Perubahan: pesan error untuk qty
            'cart_items.*.qty.numeric' => 'Jumlah barang harus berupa angka.',
            'cart_items.*.qty.gt' => 'Jumlah barang harus lebih besar dari 0.',
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
        // Ambil stok terkini dari DB (gunakan lockForUpdate jika memungkinkan/perlu)
        $barangStocks = DB::table('barang')
            ->whereIn('id', $barangIds)
            ->where('company_id', $companyId)
            // ->lockForUpdate() // Aktifkan jika sering terjadi race condition
            ->pluck('stock_barang', 'id');

        $stockValidationErrors = [];
        foreach ($cartItems as $item) {
            // Perubahan: Konversi qty ke float untuk perbandingan
            $requestedQty = (float) str_replace(',', '.', $item['qty'] ?? 0);
            $barangId = $item['id'];
            // Perubahan: Konversi stok DB ke float
            $availableStock = (float) ($barangStocks[$barangId] ?? 0);

            if ($availableStock < $requestedQty) {
                $barangInfo = DB::table('barang')->where('id', $barangId)->value('kode_barang') ?? "ID:{$barangId}";
                // Perubahan: Format angka dalam pesan error menggunakan helper baru
                $stockValidationErrors[] = "Stok '{$barangInfo}' tidak mencukupi (tersedia: " . formatAngkaDesimal($availableStock, 4) . ", diminta: " . formatAngkaDesimal($requestedQty, 4) . ").";
            }
        }

        if (!empty($stockValidationErrors)) {
            // Gunakan key error berbeda jika diperlukan untuk membedakan dari validasi standar
            return redirect()->back()
                ->withErrors(['cart_items_stock' => $stockValidationErrors])
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
                // Perubahan: Path dengan company ID
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
                // Perubahan: Konversi qty ke float
                $itemQty = (float) str_replace(',', '.', $item['qty'] ?? 0);

                if ($itemQty <= 0) { // Double check validasi
                    throw new \Exception("Jumlah barang dengan ID {$item['id']} tidak valid.");
                }

                $transaksiDetails[] = [
                    'barang_id' => $item['id'],
                    'qty' => $itemQty, // Simpan float
                    'transaksi_id' => $transaksiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (isset($stockUpdates[$item['id']])) {
                    $stockUpdates[$item['id']] += $itemQty;
                } else {
                    $stockUpdates[$item['id']] = $itemQty;
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
                    // Perubahan: Decrement dengan float
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
                // Perubahan: Path dengan company ID
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
                // Perubahan: Gunakan float
                $detailQty = (float) $detail->qty;
                if (isset($stockUpdates[$detail->barang_id])) {
                    $stockUpdates[$detail->barang_id] += $detailQty;
                } else {
                    $stockUpdates[$detail->barang_id] = $detailQty;
                }
            }

            if (!empty($stockUpdates)) {
                foreach ($stockUpdates as $barangId => $totalQty) {
                    // Tambah stok (kebalikan dari store)
                    // Perubahan: Increment dengan float
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
                // Perubahan: Path dengan company ID
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
    public function exportPdf(): RedirectResponse|StreamedResponse
    {
        try {
            $companyId = session('sessionCompany');
            if (!$companyId) {
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal export PDF: Silakan pilih perusahaan.');
            }

            $activeCompany = Company::find($companyId);
            if (!$activeCompany) {
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal export PDF: Perusahaan tidak ditemukan.');
            }
            $namaPerusahaanCetak = $activeCompany->nama_perusahaan;

            // Get OUT transactions for active company
            $transaksis = DB::table('transaksi')
                ->select('transaksi.no_surat', 'transaksi.tanggal', 'transaksi.type', 'transaksi.keterangan', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'Out')
                ->where('transaksi.company_id', $companyId)
                ->orderByDesc('transaksi.tanggal')
                ->get();

            $logoUrl = function_exists('get_company_logo_base64') ? get_company_logo_base64($activeCompany) : null;
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()?->name ?? 'N/A';

            $data = compact('transaksis', 'activeCompany', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaanCetak');

            $pdf = Pdf::loadView('transaksi-stock-out.export-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true);

            // New filename format: [date_time]-Laporan-Transaksi-Keluar-[company].pdf
            $namaPerusahaan = str_replace(' ', '-', strtoupper($namaPerusahaanCetak));
            $filename = date('Y-m-d_H-i') . '-Laporan-Transaksi-Keluar-' . $namaPerusahaan . '.pdf';

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
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal saat generate output PDF Laporan Transaksi Keluar.');
            }
        } catch (\Throwable $th) {
            return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal memproses PDF Laporan Transaksi Keluar.');
        }
    }

    /**
     * Export detail data transaksi stock out to PDF.
     */
    public function exportItemPdf($id): RedirectResponse|StreamedResponse // Tambahkan return type hint
    {
        // Log::info(...); // Logging dinonaktifkan
        try {
            $companyId = session('sessionCompany');
            if (!$companyId) { // Cek company ID juga di sini
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal export PDF: Silakan pilih perusahaan.');
            }

            $activeCompany = Company::find($companyId);
            if (!$activeCompany) {
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal export PDF: Perusahaan tidak ditemukan.');
            }
            $namaPerusahaanCetak = $activeCompany->nama_perusahaan;


            // 1. Ambil data Transaksi Header (filter by ID, company, dan type Out)
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
                ->where('transaksi.id', $id)
                ->where('transaksi.company_id', $companyId) // Filter company
                ->where('transaksi.type', 'Out') // <-- Filter type Out
                ->first();

            if (!$transaksi) {
                // Log::error(...); // Logging dinonaktifkan
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Transaksi keluar tidak ditemukan atau tidak sesuai.');
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

            // 3. Ambil Logo (Hanya dari activeCompany)
            $logoUrl = function_exists('get_company_logo_base64') ? get_company_logo_base64($activeCompany) : null;

            // 4. Data tambahan
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()?->name ?? 'N/A';
            // $namaPerusahaanCetak sudah ada

            // 5. Siapkan data untuk view (HAPUS $setting, ganti namaPerusahaan jika perlu)
            // Pastikan view menggunakan 'namaPerusahaanCetak' atau sesuaikan key di compact
            $data = compact('transaksi', 'details', 'activeCompany', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaanCetak');

            // PENTING: Pastikan view 'transaksi-stock-out.export-item-pdf.blade.php' ada dan benar
            $pdf = Pdf::loadView('transaksi-stock-out.export-item-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true);

            // Sesuaikan nama file
            $namaPerusahaan = str_replace(' ', '-', strtoupper($namaPerusahaanCetak));
            $filename = 'Detail-Transaksi-Keluar-' . $namaPerusahaan . '-' . ($transaksi->no_surat ?? $id) . '.pdf';

            // --- PENDEKATAN MANUAL STREAM ---
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
                // Log::error(...); // Logging dinonaktifkan
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal saat generate output PDF Detail Transaksi Keluar.');
            }
            // --- AKHIR PENDEKATAN MANUAL STREAM ---

        } catch (\Throwable $th) {
            // Log::error(...); // Logging dinonaktifkan
            return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal memproses PDF Detail Transaksi Keluar.');
        }
    }
}
