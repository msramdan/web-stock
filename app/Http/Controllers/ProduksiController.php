<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\Barang;
use App\Models\Bom;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Gunakan Request standar dulu, nanti bisa buat FormRequest
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator; // Gunakan Validator Facade
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables; // Untuk index AJAX

class ProduksiController extends Controller implements HasMiddleware
{
    // Konstruktor bisa ditambahkan jika perlu dependency injection lain
    public function __construct(public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/'); // Sesuaikan path jika perlu
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        // Definisikan permission yang sesuai (buat permission ini di sistem Anda)
        return [
            'auth',
            new Middleware('permission:produksi view', only: ['index', 'show']),
            new Middleware('permission:produksi create', only: ['create', 'store']),
            new Middleware('permission:produksi edit', only: ['edit', 'update']), // Jika ada fitur edit
            new Middleware('permission:produksi delete', only: ['destroy']), // Jika ada fitur delete
        ];
    }

    /**
     * Display a listing of the resource.
     * Menampilkan daftar Produksi yang sudah dibuat.
     */
    public function index(Request $request): View | \Illuminate\Http\JsonResponse
    {
        $companyId = session('sessionCompany');

        if ($request->ajax()) {
            $produksi = Produksi::with('produkJadi:id,kode_barang,nama_barang') // Eager load produk jadi
                ->where('company_id', $companyId)
                ->select('produksi.*') // Pilih semua kolom dari produksi
                ->orderBy('tanggal', 'desc'); // Urutkan terbaru

            return DataTables::of($produksi)
                ->addColumn('qty_target', function ($row) {
                    return rtrim(rtrim($row->qty_target, '0'), '.');
                })
                ->addColumn('produk_jadi', function ($row) {
                    return $row->produkJadi?->kode_barang . ' - ' . $row->produkJadi?->nama_barang;
                })
                ->addColumn('tanggal_f', function ($row) {
                    return \Carbon\Carbon::parse($row->tanggal)->isoFormat('D MMM YYYY, HH:mm');
                })
                ->addColumn('action', 'produksi.include.action') // Buat view ini nanti
                ->toJson();
        }

        return view('produksi.index'); // Buat view ini nanti
    }

    /**
     * Show the form for creating a new resource.
     * Bisa jadi ini halaman untuk memilih produk jadi dulu.
     */
    public function create(Request $request): View | RedirectResponse
    {
        $companyId = session('sessionCompany');

        // Opsi 1: Tampilkan daftar produk jadi yang punya BoM untuk dipilih
        if (!$request->has('barang_id')) {
            $produkJadiList = Barang::where('company_id', $companyId)
                ->whereHas('boms') // Hanya tampilkan barang yg punya BoM
                ->orderBy('nama_barang')
                ->pluck('nama_barang', 'id'); // atau get() jika perlu info lain

            if ($produkJadiList->isEmpty()) {
                return redirect()->route('produksi.index')->with('error', 'Tidak ada Produk Jadi dengan BoM yang terdaftar untuk perusahaan ini.');
            }

            return view('produksi.select_product', compact('produkJadiList')); // View untuk memilih produk
        }

        // Opsi 2: Form utama setelah produk dipilih (dari request atau langkah sebelumnya)
        $barangId = $request->input('barang_id');
        $produkJadi = Barang::with('unitSatuan') // Ambil unit satuan default
            ->where('company_id', $companyId)
            ->whereHas('boms')
            ->find($barangId);

        if (!$produkJadi) {
            return redirect()->route('produksi.create')->with('error', 'Produk Jadi tidak valid atau tidak memiliki BoM.');
        }

        // Ambil BoM aktif untuk produk ini (asumsi hanya 1 BoM aktif per produk, atau ambil yg terbaru)
        $bom = Bom::where('barang_id', $produkJadi->id)
            ->where('company_id', $companyId) // Pastikan BoM dari company yg sama
            ->with('details.material.unitSatuan', 'details.unitSatuan') // Eager load BoM details
            ->latest() // Ambil BoM terbaru jika ada > 1
            ->first();

        if (!$bom || $bom->details->isEmpty()) {
            return redirect()->route('produksi.create')->with('error', 'BoM tidak ditemukan atau kosong untuk produk ini.');
        }

        // Siapkan data untuk ditampilkan di form (kalkulasi awal)
        $requiredMaterials = [];
        foreach ($bom->details as $detail) {
            $requiredMaterials[] = [
                'material_id' => $detail->barang_id,
                'kode_barang' => $detail->material?->kode_barang ?? 'N/A',
                'nama_barang' => $detail->material?->nama_barang ?? 'Material Tidak Ditemukan',
                'qty_per_unit' => $detail->jumlah, // Qty dari BoM detail
                'unit_satuan_id' => $detail->unit_satuan_id,
                'unit_satuan' => $detail->unitSatuan?->nama_unit_satuan ?? '-', // Unit dari BoM detail
                'stok_saat_ini' => $detail->material?->stock_barang ?? 0, // Ambil stok terkini material
            ];
        }

        return view('produksi.create', compact('produkJadi', 'bom', 'requiredMaterials')); // View form utama
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = session('sessionCompany');
        $userId = Auth::id();

        // Validasi Input (Contoh, sebaiknya buat FormRequest: StoreProduksiRequest)
        $validator = Validator::make($request->all(), [
            'no_produksi' => 'required|string|max:255|unique:produksi,no_produksi,NULL,id,company_id,' . $companyId,
            'batch' => 'required|integer|min:1',
            'tanggal' => 'required|date_format:Y-m-d\TH:i', // Sesuaikan format datetime-local
            'barang_id' => 'required|integer|exists:barang,id,company_id,' . $companyId, // Produk Jadi
            'bom_id' => 'required|integer|exists:bom,id,company_id,' . $companyId, // BoM
            'qty_target' => 'required|numeric|min:0.0001', // Target Produksi
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048',
            'keterangan' => 'nullable|string',
        ], [
            'no_produksi.unique' => 'No. Produksi sudah digunakan di perusahaan ini.',
            'barang_id.exists' => 'Produk Jadi tidak valid untuk perusahaan ini.',
            'bom_id.exists' => 'BoM tidak valid untuk perusahaan ini.',
            'qty_target.min' => 'Target Kuantitas minimal harus lebih besar dari 0.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Ambil data tervalidasi
        $validated = $validator->validated();

        // Cek ulang BoM dan detailnya
        $bom = Bom::with('details.material') // Eager load material
            ->where('id', $validated['bom_id'])
            ->where('barang_id', $validated['barang_id']) // Pastikan BoM sesuai Produk Jadi
            ->where('company_id', $companyId)
            ->first();

        if (!$bom || $bom->details->isEmpty()) {
            return redirect()->back()->with('error', 'BoM tidak valid atau tidak memiliki detail bahan.')->withInput();
        }

        // --- Validasi Stok Bahan Baku ---
        $targetProduksi = (float) $validated['qty_target'];
        $stockErrors = [];
        foreach ($bom->details as $detail) {
            $requiredQty = (float) $detail->jumlah * $targetProduksi;
            $materialStock = (float) ($detail->material->stock_barang ?? 0);
            if ($materialStock < $requiredQty) {
                $stockErrors[] = "Stok '{$detail->material->kode_barang} - {$detail->material->nama_barang}' tidak cukup (dibutuhkan: {$requiredQty}, tersedia: {$materialStock})";
            }
        }

        if (!empty($stockErrors)) {
            return redirect()->back()->withErrors(['stok' => $stockErrors])->withInput()->with('error', 'Stok bahan baku tidak mencukupi.');
        }
        // --- Akhir Validasi Stok ---

        DB::beginTransaction();
        try {
            // Handle Attachment
            $attachmentName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $attachmentName = $companyId . '_prod_' . time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/uploads/attachments/' . $companyId, $attachmentName); // Sesuaikan path jika perlu
            }

            // 1. Buat Header Produksi
            $produksi = Produksi::create([
                'company_id' => $companyId,
                'no_produksi' => $validated['no_produksi'],
                'batch' => $validated['batch'],
                'tanggal' => $validated['tanggal'],
                'barang_id' => $validated['barang_id'],
                'bom_id' => $validated['bom_id'],
                'qty_target' => $targetProduksi,
                'attachment' => $attachmentName,
                'keterangan' => $validated['keterangan'],
                // 'status' => 'Scheduled', // Set status awal jika perlu
                // 'user_id' => $userId, // Jika ada kolom user_id
            ]);

            // 2. Buat Detail Produksi (Termasuk Produk Jadi 'In' dan Material 'Out')
            $produksiDetails = [];

            // Tambahkan detail untuk Produk Jadi (Type 'In')
            $produkJadiModel = Barang::find($validated['barang_id']); // Ambil model produk jadi
            $produksiDetails[] = [
                'produksi_id' => $produksi->id,
                'barang_id' => $validated['barang_id'],
                'unit_satuan_id' => $produkJadiModel->unit_satuan_id, // Ambil unit dari barang
                'type' => 'In',
                'qty_rate' => 1,
                'qty_target_produksi' => $targetProduksi,
                'qty_total_diperlukan' => $targetProduksi, // Qty target * 1
                'created_at' => now(),
                'updated_at' => now() // Jika pakai timestamps
            ];

            // Tambahkan detail untuk Material (Type 'Out')
            foreach ($bom->details as $detail) {
                $qtyDiperlukan = (float) $detail->jumlah * $targetProduksi;
                $produksiDetails[] = [
                    'produksi_id' => $produksi->id,
                    'barang_id' => $detail->barang_id, // ID Material
                    'unit_satuan_id' => $detail->unit_satuan_id, // Unit dari BoM Detail
                    'type' => 'Out',
                    'qty_rate' => (float) $detail->jumlah, // Qty dari BoM
                    'qty_target_produksi' => $targetProduksi,
                    'qty_total_diperlukan' => $qtyDiperlukan,
                    'created_at' => now(),
                    'updated_at' => now() // Jika pakai timestamps
                ];
            }

            // Bulk Insert Detail
            if (!empty($produksiDetails)) {
                ProduksiDetail::insert($produksiDetails);
            }

            // 3. Kurangi Stok Bahan Baku (Type 'Out')
            foreach ($bom->details as $detail) {
                $qtyDikurangi = (float) $detail->jumlah * $targetProduksi;
                DB::table('barang')
                    ->where('id', $detail->barang_id)
                    ->where('company_id', $companyId)
                    ->decrement('stock_barang', $qtyDikurangi, ['updated_at' => now()]);
            }

            // 4. Tambah Stok Barang Jadi (Type 'In') - ASUMSI LANGSUNG SELESAI
            // Jika produksi perlu status 'Completed' dulu baru tambah stok, logikanya beda
            DB::table('barang')
                ->where('id', $validated['barang_id'])
                ->where('company_id', $companyId)
                ->increment('stock_barang', $targetProduksi, ['updated_at' => now()]);


            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil disimpan dan stok diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus attachment jika gagal
            if ($attachmentName && $companyId) {
                Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
            }
            // Log::error(...) // Logging dinonaktifkan
            return redirect()->back()->with('error', 'Gagal menyimpan data produksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produksi $produksi): View | RedirectResponse // Gunakan Route Model Binding
    {
        // Validasi company
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        // Eager load relasi yang diperlukan
        $produksi->load([
            'produkJadi.unitSatuan', // Produk Jadi dan unitnya
            'bom', // BoM yang digunakan
            'details' => function ($query) { // Ambil detail beserta relasinya
                $query->with(['barang.unitSatuan', 'unitSatuan'])->orderBy('type', 'desc'); // Urutkan 'In' dulu baru 'Out'
            }
            // 'user' // Jika pakai user_id
        ]);

        // Ambil URL attachment jika ada
        $attachmentUrl = null;
        if ($produksi->attachment) {
            $attachmentUrl = Storage::url('uploads/attachments/' . $produksi->company_id . '/' . $produksi->attachment);
        }


        return view('produksi.show', compact('produksi', 'attachmentUrl')); // Buat view ini
    }

    /**
     * Show the form for editing the specified resource.
     * (Implementasi edit bisa kompleks jika melibatkan perubahan qty/BoM setelah dibuat)
     */
    public function edit(Produksi $produksi): View
    {
        // Validasi company
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        // TODO: Implementasi form edit jika diperlukan
        // Perlu load data seperti create + data produksi yg ada
        return view('produksi.edit', compact('produksi')); // Buat view ini
    }

    /**
     * Update the specified resource in storage.
     * (Implementasi update bisa kompleks)
     */
    public function update(Request $request, Produksi $produksi): RedirectResponse
    {
        // Validasi company
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        // TODO: Implementasi logika update jika diperlukan
        // Hati-hati dengan perubahan qty target dan dampaknya ke stok & detail

        return redirect()->route('produksi.index')->with('info', 'Fitur update produksi belum diimplementasikan.');
    }

    /**
     * Remove the specified resource from storage.
     * (Implementasi destroy perlu membatalkan/mengembalikan stok)
     */
    public function destroy(Produksi $produksi): RedirectResponse
    {
        // Validasi company
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        DB::beginTransaction();
        try {

            // TODO: Implementasi pengembalian stok
            // 1. Ambil detail produksi
            // 2. Untuk type 'Out', TAMBAH stok material
            // 3. Untuk type 'In', KURANGI stok produk jadi
            // Hati-hati jika status produksi sudah 'Completed' atau 'In Progress'

            // Hapus attachment jika ada
            if ($produksi->attachment) {
                Storage::delete('public/uploads/attachments/' . $produksi->company_id . '/' . $produksi->attachment);
            }

            // Hapus detail (cascadeOnDelete harusnya sudah menghapus ini, tapi bisa eksplisit)
            // ProduksiDetail::where('produksi_id', $produksi->id)->delete();

            // Hapus header
            $produksi->delete();

            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil dihapus (Logika pengembalian stok perlu diimplementasikan).');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error(...) // Logging dinonaktifkan
            return redirect()->route('produksi.index')->with('error', 'Gagal menghapus data produksi: ' . $e->getMessage());
        }
    }
}
