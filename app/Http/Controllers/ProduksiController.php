<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\Barang;
use App\Models\Bom;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ProduksiExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ProduksiController extends Controller implements HasMiddleware
{
    public function __construct(public string $attachmentPath = '')
    {
        $this->attachmentPath = storage_path('app/public/uploads/attachments/');
    }

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:produksi view', only: ['index', 'show']),
            new Middleware('permission:produksi create', only: ['create', 'store', 'getBomProduksi']),
            new Middleware('permission:produksi edit', only: ['edit', 'update']),
            new Middleware('permission:produksi delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View | \Illuminate\Http\JsonResponse
    {
        if ($request->ajax()) {
            $companyId = session('sessionCompany');
            $produksi = Produksi::with('produkJadi:id,kode_barang,nama_barang', 'user:id,name')
                ->where('company_id', $companyId)
                ->select('produksi.*');

            return DataTables::of($produksi)
                ->addIndexColumn()
                ->addColumn('produk_jadi', function ($row) {
                    return $row->produkJadi?->nama_barang ?? 'N/A';
                })
                ->addColumn('tanggal_f', function ($row) {
                    if (function_exists('formatTanggalIndonesia')) {
                        return formatTanggalIndonesia($row->tanggal);
                    }
                    return Carbon::parse($row->tanggal)->format('d-m-Y H:i');
                })
                ->addColumn('dibuat_oleh', function ($row) {
                    return $row->user?->name ?? 'N/A';
                })
                ->addColumn('total_biaya', function ($row) {
                    return 'Rp ' . number_format($row->total_biaya ?? 0, 0, ',', '.');
                })
                ->addColumn('harga_satuan_jadi', function ($row) {
                    return 'Rp ' . number_format($row->harga_satuan_jadi ?? 0, 0, ',', '.');
                })
                ->addColumn('action', 'produksi.include.action')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('produksi.index');
    }

    public function create(Request $request)
    {
        $companyId = session('sessionCompany');

        if ($request->has('barang_id') && $request->has('bom_id')) {
            $bomId = $request->input('bom_id');
            $barangId = $request->input('barang_id');

            $bom = Bom::where('id', $bomId)->where('barang_id', $barangId)
                ->where('company_id', $companyId)
                ->with('details.material.unitSatuan', 'details.unitSatuan')
                ->first();

            if (!$bom || $bom->details->isEmpty()) {
                return redirect()->route('produksi.create')->with('error', 'BoM tidak valid atau tidak memiliki bahan.');
            }

            $produkJadi = Barang::find($bom->barang_id);
            $requiredMaterials = [];
            foreach ($bom->details as $detail) {
                $requiredMaterials[] = [
                    'material_id' => $detail->barang_id,
                    'kode_barang' => $detail->material?->kode_barang ?? 'N/A',
                    'nama_barang' => $detail->material?->nama_barang ?? 'Material Tidak Ditemukan',
                    'qty_per_batch' => $detail->jumlah,
                    'unit_satuan_id' => $detail->unit_satuan_id,
                    'unit_satuan' => $detail->unitSatuan?->nama_satuan ?? '-',
                    'stok_saat_ini' => $detail->material?->stock_barang ?? 0,
                ];
            }

            return view('produksi.create', compact('produkJadi', 'bom', 'requiredMaterials'));
        }

        // PERBAIKAN: Mengembalikan query ke logika asli Anda yang sudah benar
        $produkJadiList = DB::table('barang')
            ->where('company_id', $companyId)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('bom')
                    ->whereColumn('bom.barang_id', 'barang.id');
            })
            ->orderBy('nama_barang')
            ->pluck('nama_barang', 'id');

        if ($produkJadiList->isEmpty()) {
            return redirect()->route('produksi.index')->with('error', 'Tidak ada Produk Jadi dengan BoM yang terdaftar.');
        }

        return view('produksi.select_product', compact('produkJadiList'));
    }

    public function getBomProduksi(Request $request)
    {
        $barangId = $request->barang_id;
        $boms = Bom::where('barang_id', $barangId)
            ->where('company_id', session('sessionCompany'))
            ->get(['id', 'deskripsi']);
        return response()->json($boms);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = session('sessionCompany');
        $userId = Auth::id();

        $validated = $request->validate([
            'no_produksi' => 'required|string|max:255|unique:produksi,no_produksi,NULL,id,company_id,' . $companyId,
            'batch' => 'required|integer|min:1',
            'tanggal' => 'required|date_format:Y-m-d\TH:i',
            'barang_id' => 'required|integer|exists:barang,id',
            'bom_id' => 'required|integer|exists:bom,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10048',
            'keterangan' => 'nullable|string',
        ]);

        $bom = Bom::with('details.material')->where('id', $validated['bom_id'])->first();
        if (!$bom || $bom->details->isEmpty()) {
            return redirect()->back()->with('error', 'BoM tidak valid.')->withInput();
        }

        $batchCount = (int) $validated['batch'];
        $stockErrors = [];
        $sumOfMaterialQtysPerBatch = 0;
        foreach ($bom->details as $detail) {
            $requiredQtyTotal = (float) $detail->jumlah * $batchCount;
            $materialStock = (float) ($detail->material->stock_barang ?? 0);
            if ($materialStock < $requiredQtyTotal) {
                $stockErrors[] = "Stok '{$detail->material->nama_barang}' tidak cukup (butuh: {$requiredQtyTotal}, tersedia: {$materialStock})";
            }
            $sumOfMaterialQtysPerBatch += (float) $detail->jumlah;
        }

        if (!empty($stockErrors)) {
            return redirect()->back()->withErrors(['stok' => $stockErrors])->withInput();
        }

        DB::beginTransaction();
        try {
            $attachmentName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $attachmentName = $companyId . '_prod_' . time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/uploads/attachments/' . $companyId, $attachmentName);
            }

            // --- PERUBAHAN UTAMA: LAKUKAN KALKULASI SEBELUM MEMBUAT RECORD ---

            // 1. Hitung total biaya produksi dari semua bahan baku
            $totalBiayaProduksi = 0;
            $sumOfMaterialQtysPerBatch = 0;
            foreach ($bom->details as $detail) {
                $qtyDibutuhkan = (float) $detail->jumlah * $batchCount;
                // Panggil fungsi FIFO yang sudah kita buat
                $biayaMaterial = $this->ambilStokDanBiayaFIFO($detail->barang_id, $qtyDibutuhkan, $companyId);
                $totalBiayaProduksi += $biayaMaterial;
                $sumOfMaterialQtysPerBatch += (float) $detail->jumlah;
            }

            // 2. Hitung total produk jadi dan HPP per unit
            $totalProdukJadiDihasilkan = $sumOfMaterialQtysPerBatch * $batchCount;
            $hargaSatuanBarangJadi = ($totalProdukJadiDihasilkan > 0) ? $totalBiayaProduksi / $totalProdukJadiDihasilkan : 0;

            // 3. Gabungkan semua data (termasuk hasil kalkulasi) untuk disimpan
            $produksiData = array_merge($validated, [
                'company_id' => $companyId,
                'user_id' => $userId,
                'attachment' => $attachmentName,
                'total_biaya' => $totalBiayaProduksi,
                'harga_satuan_jadi' => $hargaSatuanBarangJadi
            ]);

            // 4. Buat record Produksi dengan data yang sudah lengkap
            $produksi = Produksi::create($produksiData);

            // Proses stok dan detail
            foreach ($bom->details as $detail) {
                DB::table('barang')->where('id', $detail->barang_id)->decrement('stock_barang', (float) $detail->jumlah * $batchCount);
            }
            DB::table('barang')->where('id', $validated['barang_id'])->increment('stock_barang', $totalProdukJadiDihasilkan);

            DB::commit();
            return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil disimpan dengan biaya terhitung.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($attachmentName)) {
                Storage::delete('public/uploads/attachments/' . $companyId . '/' . $attachmentName);
            }
            Log::error('Error storing production: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal menyimpan data produksi: ' . $e->getMessage())->withInput();
        }
    }

    private function ambilStokDanBiayaFIFO(int $barangId, float $qtyDibutuhkan, int $companyId): float
    {
        $batchTersedia = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.transaksi_id', '=', 'transaksi.id')
            ->where('transaksi_detail.barang_id', $barangId)
            ->where('transaksi.company_id', $companyId)->where('transaksi.type', 'In')
            ->where('transaksi_detail.sisa_qty', '>', 0)
            ->orderBy('transaksi.tanggal', 'asc')->orderBy('transaksi_detail.created_at', 'asc')
            ->select('transaksi_detail.id', 'transaksi_detail.harga_satuan', 'transaksi_detail.sisa_qty')
            ->get();

        $totalBiaya = 0;
        $sisaQtyDibutuhkan = $qtyDibutuhkan;

        foreach ($batchTersedia as $batch) {
            if ($sisaQtyDibutuhkan <= 0) break;
            $qtyDiambilDariBatch = min($sisaQtyDibutuhkan, (float)$batch->sisa_qty);
            DB::table('transaksi_detail')->where('id', $batch->id)->decrement('sisa_qty', $qtyDiambilDariBatch);
            $totalBiaya += $qtyDiambilDariBatch * (float)$batch->harga_satuan;
            $sisaQtyDibutuhkan -= $qtyDiambilDariBatch;
        }

        if ($sisaQtyDibutuhkan > 0.0001) {
            $barang = Barang::find($barangId);
            throw new \Exception("Stok FIFO tidak konsisten untuk '{$barang->nama_barang}'.");
        }
        return $totalBiaya;
    }

    /**
     * Display the specified resource.
     */
    public function show(Produksi $produksi): View | RedirectResponse
    {
        if ($produksi->company_id != session('sessionCompany')) {
            abort(403, 'Akses ditolak.');
        }

        // PERBAIKAN: Menggunakan nama relasi yang benar 'produkJadi'
        $produksi->load([
            'produkJadi.unitSatuan', // <-- Relasi yang benar
            'bom',
            'user',
            'details' => function ($query) {
                $query->with(['barang.unitSatuan', 'unitSatuan'])->orderBy('type', 'desc');
            }
        ]);

        $attachmentUrl = null;
        if ($produksi->attachment) {
            $attachmentUrl = Storage::url('uploads/attachments/' . $produksi->company_id . '/' . $produksi->attachment);
        }

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

    public function exportExcel()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return Excel::download(new ProduksiExport, "produksi_{$timestamp}.xlsx");
    }
}
