<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Company;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiStockInController extends Controller implements HasMiddleware
{
    // Constructor tidak perlu ImageService jika hanya untuk attachment
    public function __construct(/*public ImageService $imageService,*/public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/'); // Path lengkap
    }

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:transaksi stock in view', only: ['index', 'show']),
            new Middleware('permission:transaksi stock in create', only: ['create', 'store']),
            new Middleware('permission:transaksi stock in edit', only: ['edit', 'update']), // Edit/Update belum diimplementasi
            new Middleware('permission:transaksi stock in delete', only: ['destroy']),
            new Middleware('permission:transaksi stock in export pdf', only: ['exportPdf', 'exportItemPdf']), // Tambahkan exportItemPdf
        ];
    }

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $companyId = session('sessionCompany'); // Ambil company ID
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'In')
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
                    // Gunakan nama file yang disimpan, bukan path lengkap
                    $url = Storage::url('uploads/attachments/' . $row->attachment);

                    // Tampilkan ikon berdasarkan tipe file (opsional)
                    $icon = 'bi-file-earmark-arrow-down'; // Default icon
                    $extension = pathinfo($row->attachment, PATHINFO_EXTENSION);
                    if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                        $icon = 'bi-file-earmark-image';
                    } elseif (strtolower($extension) === 'pdf') {
                        $icon = 'bi-file-earmark-pdf';
                    } elseif (in_array(strtolower($extension), ['doc', 'docx'])) {
                        $icon = 'bi-file-earmark-word';
                    }

                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-outline-primary" title="Download ' . e($row->attachment) . '">
                                <i class="bi ' . $icon . '"></i>
                            </a>';
                })
                ->addColumn('action', 'transaksi-stock-in.include.action')
                ->rawColumns(['attachment', 'action']) // Biarkan attachment di rawColumns
                ->toJson();
        }

        return view('transaksi-stock-in.index');
    }

    public function create(): View
    {
        // Tidak perlu mengirim data barang ke view create jika menggunakan AJAX search
        return view('transaksi-stock-in.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = session('sessionCompany');
        $userId = Auth::id();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'no_surat' => 'required|string|max:255|unique:transaksi,no_surat,NULL,id,company_id,' . $companyId,
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048', // Max 10MB
            'cart_items' => 'required|json',
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

        DB::beginTransaction();

        try {
            // Handle file upload
            $attachmentName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                // Buat nama unik: companyId_timestamp_namaAsli
                $attachmentName = $companyId . '_' . time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                // Simpan file ke storage/app/public/uploads/attachments/{companyId}/
                $file->storeAs('public/uploads/attachments/' . $companyId, $attachmentName);
                // Simpan nama file saja di DB
            }

            // Create transaction using Query Builder
            $transaksiId = DB::table('transaksi')->insertGetId([
                'company_id' => $companyId, // Simpan company_id
                'no_surat' => $request->no_surat,
                'tanggal' => $request->tanggal,
                'type' => 'In',
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
                // Perubahan: Konversi qty ke float, ganti koma jika dari JS kirim string
                // Jika JS sudah mengirim float (dari JSON.stringify), ini aman
                $itemQty = (float) str_replace(',', '.', $item['qty'] ?? 0);

                if ($itemQty <= 0) { // Double check validasi
                    throw new \Exception("Jumlah barang dengan ID {$item['id']} harus lebih besar dari 0.");
                }

                $transaksiDetails[] = [
                    'barang_id' => $item['id'],
                    'qty' => $itemQty, // Simpan nilai float/numeric
                    'transaksi_id' => $transaksiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // Kumpulkan data untuk update stok
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
                // Seharusnya tidak terjadi karena validasi di awal
                throw new \Exception('Tidak ada item detail transaksi yang valid.');
            }


            // Bulk update stock (increment stock_barang)
            if (!empty($stockUpdates)) {
                foreach ($stockUpdates as $barangId => $totalQty) {
                    // Perubahan: Increment dengan float (increment/decrement support float)
                    DB::table('barang')
                        ->where('id', $barangId)
                        ->where('company_id', $companyId) // Pastikan update stok di company yang benar
                        ->increment('stock_barang', $totalQty, ['updated_at' => now()]);
                }
            }

            DB::commit();

            return redirect()->route('transaksi-stock-in.index')
                ->with('success', 'Transaksi stock in berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus file yang mungkin sudah terupload jika transaksi gagal
            if ($attachmentName && $companyId) {
                Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
            }
            Log::error('Error storing stock in transaction: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Gagal membuat transaksi stock in: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id): View
    {
        $companyId = session('sessionCompany'); // Ambil company ID

        // Get transaction data
        $transaksi = DB::table('transaksi')
            ->select('transaksi.*', 'users.name as user_name')
            ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
            ->where('transaksi.id', $id)
            ->where('transaksi.company_id', $companyId) // Filter company
            ->where('transaksi.type', 'In')
            ->first();

        if (!$transaksi) {
            abort(404, 'Transaksi tidak ditemukan atau tidak sesuai.');
        }

        // Get transaction details with item information
        $details = DB::table('transaksi_detail')
            ->select(
                'transaksi_detail.qty', // Hanya qty
                'barang.kode_barang',
                'barang.nama_barang', // Tambah nama barang
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan'
            )
            ->join('barang', 'transaksi_detail.barang_id', '=', 'barang.id')
            ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
            ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
            ->where('transaksi_detail.transaksi_id', $id)
            // Pastikan join barang juga implisit terfilter company karena relasi
            ->get();

        // Siapkan URL attachment jika ada
        $attachmentUrl = null;
        if ($transaksi->attachment) {
            $attachmentUrl = Storage::url('uploads/attachments/' . $transaksi->company_id . '/' . $transaksi->attachment);
        }


        return view('transaksi-stock-in.show', [
            'transaksi' => $transaksi,
            'details' => $details,
            'attachmentUrl' => $attachmentUrl, // Kirim URL ke view
        ]);
    }


    public function destroy($id): RedirectResponse
    {
        $companyId = session('sessionCompany'); // Ambil company ID

        DB::beginTransaction();

        try {
            // 1. Dapatkan data transaksi (pastikan milik company yg benar)
            $transaksi = DB::table('transaksi')
                ->where('id', $id)
                ->where('company_id', $companyId)
                ->where('type', 'In') // Pastikan tipe benar
                ->first();

            if (!$transaksi) {
                throw new \Exception('Transaksi stock in tidak ditemukan atau tidak sesuai.');
            }

            // 2. Dapatkan semua detail transaksi
            $details = DB::table('transaksi_detail')
                ->where('transaksi_id', $id)
                ->get();

            // 3. Kembalikan stok barang (karena ini transaksi IN yang dihapus)
            $stockUpdates = [];
            foreach ($details as $detail) {
                // Perubahan: Gunakan float untuk qty
                $detailQty = (float) $detail->qty; // Konversi dari DB (mungkin sudah float/decimal)
                if (isset($stockUpdates[$detail->barang_id])) {
                    $stockUpdates[$detail->barang_id] += $detailQty;
                } else {
                    $stockUpdates[$detail->barang_id] = $detailQty;
                }
            }

            if (!empty($stockUpdates)) {
                foreach ($stockUpdates as $barangId => $totalQty) {
                    // Kurangi stok (kebalikan dari store)
                    // Perubahan: Decrement dengan float
                    DB::table('barang')
                        ->where('id', $barangId)
                        ->where('company_id', $companyId) // Pastikan update di company yang benar
                        ->decrement('stock_barang', $totalQty, ['updated_at' => now()]);
                }
            }


            // 4. Hapus detail transaksi
            DB::table('transaksi_detail')
                ->where('transaksi_id', $id)
                ->delete();

            // 5. Hapus file attachment jika ada
            if ($transaksi->attachment) {
                // Perubahan: Gunakan path dengan company ID
                Storage::delete('public/uploads/attachments/' . $transaksi->company_id . '/' . $transaksi->attachment);
            }

            // 6. Hapus transaksi utama
            DB::table('transaksi')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return redirect()->route('transaksi-stock-in.index')
                ->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting stock in transaction ID {$id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Export data transaksi stock in to PDF.
     */
    public function exportPdf(): RedirectResponse|StreamedResponse
    {
        try {
            $companyId = session('sessionCompany');
            if (!$companyId) {
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal export PDF: Silakan pilih perusahaan.');
            }

            $activeCompany = Company::find($companyId);
            if (!$activeCompany) {
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal export PDF: Perusahaan tidak ditemukan.');
            }
            $namaPerusahaanCetak = $activeCompany->nama_perusahaan;

            // Ambil data transaksi IN untuk company aktif
            $transaksis = DB::table('transaksi')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'In')
                ->where('transaksi.company_id', $companyId)
                ->select('transaksi.no_surat', 'transaksi.tanggal', 'transaksi.type', 'transaksi.keterangan', 'users.name as user_name')
                ->orderByDesc('transaksi.tanggal')->get();

            $logoUrl = function_exists('get_company_logo_base64') ? get_company_logo_base64($activeCompany) : null;
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()?->name ?? 'N/A';

            $data = compact('transaksis', 'activeCompany', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaanCetak');

            $pdf = Pdf::loadView('transaksi-stock-in.export-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true);

            // Format nama file baru: tanggal dan jam di awal dengan format Y-m-d_H-i
            $filename = date('Y-m-d_H-i') . '-Laporan-Transaksi-Masuk-' . str_replace(' ', '-', strtoupper($namaPerusahaanCetak)) . '.pdf';

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
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal saat generate output PDF Laporan Transaksi Masuk.');
            }
        } catch (\Throwable $th) {
            return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal memproses PDF Laporan Transaksi Masuk.');
        }
    }

    /**
     * Export detail data transaksi stock in to PDF.
     */
    public function exportItemPdf($id): RedirectResponse|StreamedResponse // Tambahkan return type hint
    {
        // Log::info(...); // Logging dinonaktifkan
        try {
            $companyId = session('sessionCompany');
            if (!$companyId) { // Cek company ID juga di sini
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal export PDF: Silakan pilih perusahaan.');
            }

            $activeCompany = Company::find($companyId); // Ambil data company
            if (!$activeCompany) {
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal export PDF: Perusahaan tidak ditemukan.');
            }
            $namaPerusahaanCetak = $activeCompany->nama_perusahaan;

            // 1. Ambil data Transaksi Header (filter by ID, company, dan type)
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
                ->where('transaksi.id', $id)
                ->where('transaksi.company_id', $companyId) // Filter company
                ->where('transaksi.type', 'In') // Filter type
                ->first();

            if (!$transaksi) {
                // Log::error(...); // Logging dinonaktifkan
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Transaksi masuk tidak ditemukan atau tidak sesuai.');
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
            // $namaPerusahaanCetak sudah ada di atas

            // 5. Siapkan data untuk view (HAPUS $setting)
            $data = compact('transaksi', 'details', 'activeCompany', 'logoUrl', 'tanggalCetak', 'namaPembuat', 'namaPerusahaanCetak'); // Kirim activeCompany

            // PENTING: Pastikan view 'transaksi-stock-in.export-item-pdf.blade.php' ada dan benar
            $pdf = Pdf::loadView('transaksi-stock-in.export-item-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true);

            $namaPerusahaan = str_replace(' ', '-', strtoupper($namaPerusahaanCetak));
            $filename = 'Detail-Transaksi-Masuk-' . $namaPerusahaan . '-' . ($transaksi->no_surat ?? $id) . '.pdf';


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
                return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal saat generate output PDF Detail Transaksi Masuk.');
            }
            // --- AKHIR PENDEKATAN MANUAL STREAM ---

        } catch (\Throwable $th) {
            // Log::error(...); // Logging dinonaktifkan
            return redirect()->route('transaksi-stock-in.index')->with('error', 'Gagal memproses PDF Detail Transaksi Masuk.');
        }
    }
}
