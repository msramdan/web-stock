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
                ->addColumn('produk_jadi', function ($row) {
                    return $row->produkJadi?->kode_barang . ' - ' . $row->produkJadi?->nama_barang;
                })
                ->addColumn('tanggal_f', function ($row) {
                    return formatTanggalIndonesia($row->tanggal);
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
                'qty_per_batch' => $detail->jumlah,
                'unit_satuan_id' => $detail->unit_satuan_id,
                'unit_satuan' => $detail->unitSatuan?->nama_unit_satuan ?? '-',
                'stok_saat_ini' => $detail->material?->stock_barang ?? 0,
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
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048',
            'keterangan' => 'nullable|string',
        ], [
            'no_produksi.unique' => 'No. Produksi sudah digunakan di perusahaan ini.',
            'barang_id.exists' => 'Produk Jadi tidak valid untuk perusahaan ini.',
            'bom_id.exists' => 'BoM tidak valid untuk perusahaan ini.',
            'batch.min' => 'Jumlah batch minimal 1.',
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

        // --- Validasi Stok Bahan Baku berdasarkan BATCH ---
        $batchCount  = (int) $validated['batch'];
        $stockErrors = [];
        $sumOfMaterialQtysPerBatch = 0;

        foreach ($bom->details as $detail) {
            if (empty($detail->barang_id) || $detail->material === null) {
                continue;
            }
            $qtyPerBatch = (float) $detail->jumlah; // Qty bahan per batch
            $requiredQtyTotal = $qtyPerBatch * $batchCount; // Total kebutuhan bahan
            $materialStock = (float) ($detail->material->stock_barang ?? 0);

            if ($materialStock < $requiredQtyTotal) {
                $stockErrors[] = "Stok '{$detail->material->kode_barang} - {$detail->material->nama_barang}' tidak cukup (dibutuhkan: {$requiredQtyTotal} untuk {$batchCount} batch, tersedia: {$materialStock})";
            }
            $sumOfMaterialQtysPerBatch += $qtyPerBatch; // <-- Akumulasi SUM per batch
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
                'batch' => $batchCount,
                'tanggal' => $validated['tanggal'],
                'barang_id' => $validated['barang_id'],
                'bom_id' => $validated['bom_id'],
                'attachment' => $attachmentName,
                'keterangan' => $validated['keterangan'],
            ]);

            // 2. Buat Detail Produksi (Termasuk Produk Jadi 'In' dan Material 'Out')
            $produksiDetails = [];
            $totalProdukJadiDihasilkan = $sumOfMaterialQtysPerBatch * $batchCount; // Asumsi 1 unit/batch

            // Detail Produk Jadi ('In')
            $produkJadiModel = Barang::find($validated['barang_id']);
            $produksiDetails[] = [
                'produksi_id' => $produksi->id,
                'barang_id' => $validated['barang_id'],
                'unit_satuan_id' => $produkJadiModel->unit_satuan_id,
                'type' => 'In',
                'qty_rate' => $sumOfMaterialQtysPerBatch, // Rate = sum bahan per batch
                'qty_total_diperlukan' => $totalProdukJadiDihasilkan, // Total = sum bahan * total batch
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Tambahkan detail untuk Material (Type 'Out')
            foreach ($bom->details as $detail) {
                $qtyPerBatch = (float) $detail->jumlah; // Qty per batch dari BoM
                $qtyDiperlukan = $qtyPerBatch * $batchCount; // Total dibutuhkan
                $produksiDetails[] = [
                    'produksi_id' => $produksi->id,
                    'barang_id' => $detail->barang_id, // ID Material
                    'unit_satuan_id' => $detail->unit_satuan_id, // Unit dari BoM Detail
                    'type' => 'Out',
                    'qty_rate' => $qtyPerBatch, // Qty dari BoM
                    'qty_total_diperlukan' => $qtyDiperlukan, // Total = qty_rate * batch
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
                $qtyDikurangi = (float) $detail->jumlah * $batchCount;
                DB::table('barang')
                    ->where('id', $detail->barang_id)
                    ->where('company_id', $companyId)
                    ->decrement('stock_barang', $qtyDikurangi, ['updated_at' => now()]);
            }

            // 4. Tambah Stok Barang Jadi
            DB::table('barang')
                ->where('id', $validated['barang_id'])
                ->where('company_id', $companyId)
                // Tambah stok sejumlah TOTAL BAHAN BAKU * BATCH
                ->increment('stock_barang', $totalProdukJadiDihasilkan, ['updated_at' => now()]);


            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil disimpan dan stok diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($attachmentName && $companyId) {
                Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data produksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produksi $produksi): View | RedirectResponse
    {
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        $produksi->load([
            'produkJadi.unitSatuan',
            'bom',
            'details' => function ($query) {
                $query->with(['barang.unitSatuan', 'unitSatuan'])->orderBy('type', 'desc');
            }
        ]);

        $attachmentUrl = null;
        if ($produksi->attachment) {
            $attachmentUrl = Storage::url('uploads/attachments/' . $produksi->company_id . '/' . $produksi->attachment);
        }

        // Kirim ke view show.blade.php
        return view('produksi.show', compact('produksi', 'attachmentUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     * (Implementasi edit bisa kompleks jika melibatkan perubahan qty/BoM setelah dibuat)
     */
    public function edit(Produksi $produksi): View
    {
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        $produksi->load([
            'produkJadi.unitSatuan',
            'bom.details.material.unitSatuan',
            'bom.details.unitSatuan',
            'details.barang.unitSatuan',
            'details.unitSatuan',
        ]);

        // Siapkan data bahan (qty_rate sekarang adalah qty_per_batch)
        $requiredMaterials = [];
        foreach ($produksi->bom->details as $detail) {
            $requiredMaterials[] = [
                'material_id' => $detail->barang_id,
                'kode_barang' => $detail->material?->kode_barang ?? 'N/A',
                'nama_barang' => $detail->material?->nama_barang ?? 'Material Tidak Ditemukan',
                'qty_per_batch' => $detail->jumlah, // Ganti nama key
                'unit_satuan_id' => $detail->unit_satuan_id,
                'unit_satuan' => $detail->unitSatuan?->nama_unit_satuan ?? '-',
                'stok_saat_ini' => $detail->material?->stock_barang ?? 0,
            ];
        }

        $attachmentUrl = $produksi->attachment
            ? Storage::url('uploads/attachments/' . $produksi->company_id . '/' . $produksi->attachment)
            : null;

        // Kirim ke view edit.blade.php
        return view('produksi.edit', compact('produksi', 'requiredMaterials', 'attachmentUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produksi $produksi): RedirectResponse
    {
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }
        $companyId = session('sessionCompany');

        // Validasi input: Hapus qty_target, validasi batch
        $validator = Validator::make($request->all(), [
            'no_produksi' => 'required|string|max:255|unique:produksi,no_produksi,' . $produksi->id . ',id,company_id,' . $companyId,
            'batch' => 'required|integer|min:1', // Validasi batch
            'tanggal' => 'required|date_format:Y-m-d\TH:i',
            // 'qty_target' => 'required|numeric|min:0.0001', // <-- HAPUS
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048',
            'keterangan' => 'nullable|string',
            'remove_attachment' => 'nullable|boolean', // Tambahkan ini jika ada checkbox hapus
        ], [
            'no_produksi.unique' => 'No. Produksi sudah digunakan di perusahaan ini.',
            'batch.min' => 'Jumlah batch minimal 1.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $newBatchCount = (int) $validated['batch'];
        $oldBatchCount = (int) $produksi->batch; // Batch lama dari model

        // Cek BoM
        $bom = Bom::with('details.material')
            ->where('id', $produksi->bom_id) // Ambil bom_id dari model $produksi
            ->where('company_id', $companyId)
            ->first();

        if (!$bom || $bom->details->isEmpty()) {
            return redirect()->back()->with('error', 'BoM tidak valid atau tidak memiliki detail bahan.')->withInput();
        }

        // --- Validasi Stok berdasarkan Perbedaan BATCH ---
        $stockErrors = [];
        $batchDiff = $newBatchCount - $oldBatchCount;
        $sumOfMaterialQtysPerBatch = 0;

        foreach ($bom->details as $detail) {
            if (empty($detail->barang_id) || $detail->material === null) continue;
            $qtyPerBatch = (float) $detail->jumlah;
            $sumOfMaterialQtysPerBatch += $qtyPerBatch;

            if ($batchDiff > 0) {
                // Cek stok bahan tambahan
                $additionalQtyNeeded = $qtyPerBatch * $batchDiff;
                $materialStock = (float) ($detail->material->stock_barang ?? 0);
                if ($materialStock < $additionalQtyNeeded) {
                    $stockErrors[] = "Stok '{$detail->material->kode_barang} - {$detail->material->nama_barang}' tidak cukup (butuh tambahan: {$additionalQtyNeeded} untuk {$batchDiff} batch, tersedia: {$materialStock})";
                }
            }
        }

        // Jika batch berkurang, cek stok produk jadi
        if ($batchDiff < 0) {
            $productStock = (float) Barang::where('id', $produksi->barang_id)
                ->where('company_id', $companyId)
                ->value('stock_barang');
            // Produk yg dikurangi = SUM Bahan per Batch * Selisih Batch (absolut)
            $qtyToDeductFromProduct = abs($sumOfMaterialQtysPerBatch * $batchDiff);
            if ($productStock < $qtyToDeductFromProduct) {
                $stockErrors[] = "Stok produk jadi ('{$produksi->produkJadi->kode_barang}') tidak cukup untuk pengurangan batch (stok saat ini: {$productStock}, perlu dikurangi: " . number_format($qtyToDeductFromProduct, 4, ',', '.') . ")";
            }
        }


        if (!empty($stockErrors)) {
            return redirect()->back()->withErrors(['stok' => $stockErrors])->withInput()->with('error', 'Stok tidak mencukupi untuk perubahan batch ini.');
        }


        DB::beginTransaction();
        try {
            // Handle attachment
            $attachmentName = $produksi->attachment;
            if ($request->hasFile('attachment')) {
                // Hapus attachment lama jika ada
                if ($attachmentName) {
                    Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
                }
                // Simpan attachment baru
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $attachmentName = $companyId . '_prod_' . time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/uploads/attachments/' . $companyId, $attachmentName);
            } elseif ($request->input('remove_attachment') === '1') {
                // Hapus attachment jika diminta
                if ($attachmentName) {
                    Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
                    $attachmentName = null;
                }
            }

            // Update header Produksi (tanpa qty_target)
            $produksi->update([
                'no_produksi' => $validated['no_produksi'],
                'batch' => $newBatchCount, // Update batch
                'tanggal' => $validated['tanggal'],
                'attachment' => $attachmentName,
                'keterangan' => $validated['keterangan'],
                'updated_at' => now(),
            ]);

            // Update ProduksiDetail
            $newTotalProdukJadi = $sumOfMaterialQtysPerBatch * $newBatchCount;

            // Update detail Produk Jadi ('In')
            ProduksiDetail::where('produksi_id', $produksi->id)
                ->where('type', 'In')
                ->update([
                    'qty_rate' => $sumOfMaterialQtysPerBatch, // Rate = sum bahan per batch
                    'qty_total_diperlukan' => $newTotalProdukJadi, // Total baru
                    'updated_at' => now(),
                ]);

            // Update detail Material ('Out')
            foreach ($bom->details as $detail) {
                $qtyPerBatch = (float) $detail->jumlah;
                $qtyDiperlukan = $qtyPerBatch * $newBatchCount; // Hitung ulang total
                ProduksiDetail::where('produksi_id', $produksi->id)
                    ->where('type', 'Out')
                    ->where('barang_id', $detail->barang_id)
                    ->update([
                        'qty_rate' => $qtyPerBatch, // Rate tetap dari BoM
                        'qty_total_diperlukan' => $qtyDiperlukan, // Update total
                        'updated_at' => now(),
                    ]);
            }

            // --- Sesuaikan Stok berdasarkan Perbedaan BATCH ---
            if ($batchDiff != 0) {
                // Sesuaikan Stok Material (Sama)
                foreach ($bom->details as $detail) {
                    if (empty($detail->barang_id) || $detail->material === null) continue;
                    $stockChangeMaterial = (float) $detail->jumlah * $batchDiff;
                    if ($stockChangeMaterial > 0) {
                        DB::table('barang')->where('id', $detail->barang_id)->where('company_id', $companyId)->decrement('stock_barang', $stockChangeMaterial, ['updated_at' => now()]);
                    } elseif ($stockChangeMaterial < 0) {
                        DB::table('barang')->where('id', $detail->barang_id)->where('company_id', $companyId)->increment('stock_barang', abs($stockChangeMaterial), ['updated_at' => now()]);
                    }
                }

                // Sesuaikan Stok Produk Jadi (Logika Baru)
                $stockChangeProduct = $sumOfMaterialQtysPerBatch * $batchDiff;
                if ($stockChangeProduct > 0) {
                    // Tambah stok produk jadi
                    DB::table('barang')
                        ->where('id', $produksi->barang_id)
                        ->where('company_id', $companyId)
                        ->increment('stock_barang', $stockChangeProduct, ['updated_at' => now()]);
                } elseif ($stockChangeProduct < 0) {
                    // Kurangi stok produk jadi
                    DB::table('barang')
                        ->where('id', $produksi->barang_id)
                        ->where('company_id', $companyId)
                        ->decrement('stock_barang', abs($stockChangeProduct), ['updated_at' => now()]);
                }
            }
            // --- Akhir Penyesuaian Stok ---


            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil diperbarui dan stok disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus attachment baru jika gagal saat update
            if ($request->hasFile('attachment') && isset($attachmentName) && $attachmentName !== $produksi->getOriginal('attachment')) {
                Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui data produksi: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produksi $produksi): RedirectResponse
    {
        // Validasi company
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        $companyId = session('sessionCompany');
        $produksi->load(['details.barang', 'details.unitSatuan']);
        $batchCount = (int) $produksi->batch; // Ambil jumlah batch

        // Validasi stok produk jadi sebelum pengurangan (Asumsi 1 unit/batch)
        $qtyProductProduced = $batchCount; // Jumlah produk yg dihasilkan = batch
        $productStock = (float) Barang::where('id', $produksi->barang_id)
            ->where('company_id', $companyId)
            ->value('stock_barang');

        if ($productStock < $qtyProductProduced) {
            return redirect()->route('produksi.index')->with('error', "Penghapusan gagal: Stok produk jadi ('{$produksi->produkJadi->kode_barang}') tidak cukup untuk dibatalkan (stok saat ini: {$productStock}, perlu dikurangi: {$qtyProductProduced})");
        }

        DB::beginTransaction();
        try {
            // Pengembalian stok
            foreach ($produksi->details as $detail) {
                // Ambil qty_total_diperlukan yang tersimpan (sudah hasil kalkulasi batch)
                $qtyTotal = (float) $detail->qty_total_diperlukan;

                if ($detail->type === 'Out') {
                    // Tambah kembali stok material
                    DB::table('barang')
                        ->where('id', $detail->barang_id)
                        ->where('company_id', $companyId)
                        ->increment('stock_barang', $qtyTotal, ['updated_at' => now()]);
                } elseif ($detail->type === 'In') {
                    // Kurangi stok produk jadi
                    DB::table('barang')
                        ->where('id', $detail->barang_id)
                        ->where('company_id', $companyId)
                        ->decrement('stock_barang', $qtyTotal, ['updated_at' => now()]);
                }
            }

            // Hapus attachment jika ada
            if ($produksi->attachment) {
                Storage::delete('public/uploads/attachments/' . $produksi->company_id . '/' . $produksi->attachment);
            }

            // Hapus Produksi dan details (cascadeOnDelete seharusnya menangani details)
            $produksi->delete();

            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil dihapus dan stok disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('produksi.index')->with('error', 'Gagal menghapus data produksi: ' . $e->getMessage());
        }
    }
}
