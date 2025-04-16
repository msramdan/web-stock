<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Http\Requests\Transaksis\{StoreTransaksiRequest, UpdateTransaksiRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SettingAplikasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransaksiStockOutController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/');
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
            new Middleware('permission:transaksi stock out edit', only: ['edit', 'update']),
            new Middleware('permission:transaksi stock out delete', only: ['destroy']),
            new Middleware('permission:transaksi stock out export pdf', only: ['exportPdf']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'transaksi.user_id')
                ->where('transaksi.type', 'Out')
                ->orderByDesc('transaksi.tanggal');

            return DataTables::of($transaksi)
                ->addColumn('keterangan', function ($row) {
                    return str($row->keterangan)->limit(100);
                })
                ->addColumn('user', function ($row) {
                    return $row->user_name ?? '-';
                })
                ->addColumn('attachment', function ($row) {
                    if (!$row->attachment) {
                        return '<span class="text-muted">-</span>';
                    }

                    $url = asset('storage/uploads/attachments/' . $row->attachment);

                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-download"></i>
                            </a>';
                })
                ->addColumn('action', 'transaksi-stock-out.include.action')
                ->rawColumns(['attachment', 'action'])
                ->toJson();
        }

        return view('transaksi-stock-out.index');
    }

    public function create(): View
    {
        return view('transaksi-stock-out.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'no_surat' => 'required|string|max:255|unique:transaksi,no_surat',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048',
            'cart_items' => 'required|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            // Create transaction using Query Builder
            $transaksiId = DB::table('transaksi')->insertGetId([
                'no_surat' => $request->no_surat,
                'tanggal' => $request->tanggal,
                'type' => 'Out',
                'keterangan' => $request->keterangan,
                'attachment' => $attachmentPath,
                'user_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Process cart items
            $cartItems = json_decode($request->cart_items, true);
            $transaksiDetails = [];
            $stockUpdates = [];

            foreach ($cartItems as $item) {
                // Validate item data
                if (!isset($item['id']) || !isset($item['qty']) || $item['qty'] < 1) {
                    throw new \Exception('Invalid cart item data.');
                }

                // Check item existence
                $barang = DB::table('barang')
                    ->where('id', $item['id'])
                    ->first();

                if (!$barang) {
                    throw new \Exception('Barang tidak ditemukan.');
                }

                // Prepare transaction details
                $transaksiDetails[] = [
                    'barang_id' => $item['id'],
                    'qty' => $item['qty'],
                    'transaksi_id' => $transaksiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Prepare stock updates (using stock_barang field)
                $stockUpdates[$item['id']] = [
                    'stock_barang' => DB::raw('stock_barang - ' . $item['qty']), // Changed to stock_barang
                    'updated_at' => now(),
                ];
            }

            // Bulk insert transaction details
            DB::table('transaksi_detail')->insert($transaksiDetails);

            // Bulk update stock (increment stock_barang)
            foreach ($stockUpdates as $id => $update) {
                DB::table('barang')
                    ->where('id', $id)
                    ->update($update);
            }

            DB::commit();

            return redirect()->route('transaksi-stock-out.index')
                ->with('success', 'Transaksi stock in berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat transaksi stock in: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        // Get transaction data
        $transaksi = DB::table('transaksi')
            ->select('transaksi.*', 'users.name as user_name')
            ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
            ->where('transaksi.id', $id)
            ->first();

        if (!$transaksi) {
            abort(404);
        }

        // Get transaction details with item information
        $details = DB::table('transaksi_detail')
            ->select(
                'transaksi_detail.*',
                'barang.kode_barang',
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan'
            )
            ->join('barang', 'transaksi_detail.barang_id', '=', 'barang.id')
            ->leftJoin('jenis_material', 'barang.jenis_material_id', '=', 'jenis_material.id')
            ->leftJoin('unit_satuan', 'barang.unit_satuan_id', '=', 'unit_satuan.id')
            ->where('transaksi_detail.transaksi_id', $id)
            ->get();

        return view('transaksi-stock-out.show', [
            'transaksi' => $transaksi,
            'details' => $details
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaksi $transaksi): View
    {
        $transaksi->load(['user:id,name',]);

        return view('transaksi-stock-out.edit', compact('transaksi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi): RedirectResponse
    {
        $validated = $request->validated();

        $validated['attachment'] = $this->imageService->upload(name: 'attachment', path: $this->attachmentPath, defaultImage: $transaksi?->attachment);

        $transaksi->update($validated);

        return to_route('transaksi-stock-out.index')->with('success', __('The transaksi was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // 1. Dapatkan data transaksi
            $transaksi = DB::table('transaksi')->where('id', $id)->first();

            if (!$transaksi) {
                throw new \Exception('Transaksi tidak ditemukan');
            }

            // 2. Dapatkan semua detail transaksi
            $details = DB::table('transaksi_detail')
                ->where('transaksi_id', $id)
                ->get();

            // 3. Kurangi stok barang (karena ini transaksi IN)
            foreach ($details as $detail) {
                DB::table('barang')
                    ->where('id', $detail->barang_id)
                    ->update([
                        'stock_barang' => DB::raw('stock_barang + ' . $detail->qty),
                        'updated_at' => now()
                    ]);
            }

            // 4. Hapus detail transaksi
            DB::table('transaksi_detail')
                ->where('transaksi_id', $id)
                ->delete();

            // 5. Hapus file attachment jika ada
            if ($transaksi->attachment) {
                Storage::disk('public')->delete($transaksi->attachment);
            }

            // 6. Hapus transaksi utama
            DB::table('transaksi')
                ->where('id', $id)
                ->delete();

            DB::commit();

            return redirect()->route('transaksi-stock-out.index')
                ->with('success', 'Transaksi berhasil dihapus dan stok dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Export data transaksi stock out to PDF.
     */
    public function exportPdf()
    {
        Log::info('Memanggil metode exportPdf di TransaksiStockOutController');
        try {
            // 1. Ambil data Transaksi Stock Out
            $transaksis = DB::table('transaksi')
                ->select('transaksi.no_surat', 'transaksi.tanggal', 'transaksi.type', 'transaksi.keterangan', 'users.name as user_name') // Tambahkan select user_name
                ->join('users', 'users.id', '=', 'transaksi.user_id') // Join user
                ->where('transaksi.type', 'Out') // <-- Filter type Out
                ->orderByDesc('transaksi.tanggal')
                ->get();

            // 2. Ambil Setting Aplikasi
            $setting = SettingAplikasi::first();
            $logoPath = null;
            $logoUrl = null;
            if ($setting && $setting->logo_perusahaan) {
                $dbLogoPath = storage_path('app/public/uploads/logo-perusahaans/' . $setting->logo_perusahaan);
                if (file_exists($dbLogoPath)) $logoPath = $dbLogoPath;
                else Log::warning('File logo perusahaan tidak ditemukan: ' . $dbLogoPath);
            } else Log::warning('Setting aplikasi/logo tidak ditemukan.');

            // Encode logo
            if ($logoPath) {
                try {
                    $logoMimeType = mime_content_type($logoPath);
                    if (str_starts_with($logoMimeType, 'image/')) $logoUrl = 'data:' . $logoMimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    else Log::warning('File logo bukan gambar: ' . $logoPath);
                } catch (\Exception $e) {
                    Log::error('Gagal baca/encode logo: ' . $e->getMessage());
                    $logoUrl = null;
                }
            }

            // 3. Data tambahan
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()->name ?? 'N/A';

            // 4. Siapkan data
            $data = compact('transaksis', 'setting', 'logoUrl', 'tanggalCetak', 'namaPembuat');

            // 5. Generate PDF
            $pdf = Pdf::loadView('transaksi-stock-out.export-pdf', $data); // <-- View PDF Stock Out
            $pdf->setPaper('a4', 'portrait');
            $filename = 'Laporan-Transaksi-Keluar-' . date('YmdHis') . '.pdf';
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
            // 1. Ambil data Transaksi Header
            $transaksi = DB::table('transaksi')
                ->select('transaksi.*', 'users.name as user_name')
                ->leftJoin('users', 'transaksi.user_id', '=', 'users.id')
                ->where('transaksi.id', $id)
                ->where('transaksi.type', 'Out') // <-- Filter type Out
                ->first();

            if (!$transaksi) {
                Log::error("Transaksi Stock Out tidak ditemukan untuk ID: {$id}");
                return redirect()->route('transaksi-stock-out.index')->with('error', 'Transaksi tidak ditemukan.');
            }

            // 2. Ambil data Transaksi Detail
            $details = DB::table('transaksi_detail')
                ->select(
                    'transaksi_detail.qty',
                    'barang.kode_barang',
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
            if ($setting && $setting->logo_perusahaan) {
                $dbLogoPath = storage_path('app/public/uploads/logo-perusahaans/' . $setting->logo_perusahaan);
                if (file_exists($dbLogoPath)) $logoPath = $dbLogoPath;
                else Log::warning('File logo perusahaan tidak ditemukan: ' . $dbLogoPath);
            } else Log::warning('Setting aplikasi/logo tidak ditemukan.');

            if ($logoPath) {
                try {
                    $logoMimeType = mime_content_type($logoPath);
                    if (str_starts_with($logoMimeType, 'image/')) $logoUrl = 'data:' . $logoMimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    else Log::warning('File logo bukan gambar: ' . $logoPath);
                } catch (\Exception $e) {
                    Log::error('Gagal baca/encode logo: ' . $e->getMessage());
                    $logoUrl = null;
                }
            }

            // 4. Data tambahan
            $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');
            $namaPembuat = auth()->user()->name ?? 'N/A';

            // 5. Siapkan data
            $data = compact('transaksi', 'details', 'setting', 'logoUrl', 'tanggalCetak', 'namaPembuat');

            // 6. Generate PDF
            $pdf = Pdf::loadView('transaksi-stock-out.export-item-pdf', $data); // <-- View PDF Item Stock Out
            $pdf->setPaper('a4', 'portrait'); // <-- Gunakan portrait untuk detail
            $filename = 'Detail-Transaksi-Keluar-' . ($transaksi->no_surat ?? $id) . '.pdf';
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error("Error generating Detail Transaksi Stock Out PDF for ID {$id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('transaksi-stock-out.index')->with('error', 'Gagal membuat PDF Detail Transaksi Keluar.');
        }
    }
}
