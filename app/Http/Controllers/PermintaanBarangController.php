<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\DetailPermintaan;
use App\Models\Barang;
use App\Models\UnitSatuan;
use App\Models\Company;
use App\Http\Requests\PermintaanBarang\StorePermintaanBarangRequest;
use App\Http\Requests\PermintaanBarang\UpdatePermintaanBarangRequest;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use App\Exports\PermintaanBarangExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PermintaanBarangController extends Controller implements HasMiddleware
{
    /**
     * Mendefinisikan middleware untuk controller ini.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:permintaan barang view', only: ['index', 'show']),
            new Middleware('permission:permintaan barang create', only: ['create', 'store']),
            new Middleware('permission:permintaan barang edit', only: ['edit', 'update']),
            new Middleware('permission:permintaan barang delete', only: ['destroy']),
            new Middleware('permission:permintaan barang export pdf', only: ['printBlankForm', 'printSpecificForm']),
            new Middleware('permission:permintaan barang export excel', only: ['exportItemExcel']), // Tambahkan permission check untuk export excel per item
        ];
    }

    private function getCompanyId()
    {
        return session('sessionCompany');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        if ($request->query('export') === 'excel') {
            if (!Auth::user()->can('permintaan barang export excel')) { // Pastikan permission ini ada
                return redirect()->route('permintaan-barang.index')->with('error', __('Anda tidak memiliki izin untuk mengekspor data ini.'));
            }
            $fileName = 'daftar_permintaan_barang_' . date('YmdHis') . '.xlsx';
            return Excel::download(new PermintaanBarangExport($request), $fileName); // Request sudah mengandung companyId jika diperlukan di Export class
        }

        if ($request->ajax()) {
            $query = Permintaan::where('company_id', $companyId)
                ->with('user') // Eager load user
                ->select('permintaan.*'); // Select semua kolom dari permintaan

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user?->name ?? 'N/A'; // Menggunakan optional chaining
                })
                ->addColumn('tgl_pengajuan_formatted', function ($row) {
                    return \Carbon\Carbon::parse($row->tgl_pengajuan)->format('d-m-Y H:i');
                })
                ->addColumn('total_pesanan_formatted', function ($row) {
                    // Menggunakan helper jika ada, atau format langsung
                    return 'Rp ' . number_format($row->total_pesanan, 0, ',', '.');
                })
                ->addColumn('action', 'permintaan-barang.include.action')
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('permintaan-barang.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = $this->getCompanyId();
        $barangs = Barang::where('company_id', $companyId)
            ->with('unitSatuan') // Eager load unitSatuan
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'nama_barang', 'stock_barang', 'unit_satuan_id']); // Ambil stock_barang juga

        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan', 'asc')
            ->get(['id', 'nama_unit_satuan']);

        return view('permintaan-barang.create', compact('barangs', 'unitSatuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermintaanBarangRequest $request)
    {
        $validated = $request->validated();
        $companyId = $this->getCompanyId();

        DB::beginTransaction();
        try {
            $subTotalPesanan = 0;
            foreach ($validated['details'] as $detail) {
                $subTotalPesanan += ($detail['jumlah_pesanan'] * $detail['harga_per_satuan']);
            }

            $nominalPpn = 0;
            if (($validated['include_ppn'] ?? 'no') === 'yes') {
                $nominalPpn = $subTotalPesanan * 0.11;
            }
            $totalPesanan = $subTotalPesanan + $nominalPpn;

            $permintaan = Permintaan::create([
                'tgl_pengajuan' => $validated['tgl_pengajuan'],
                'no_permintaan_barang' => $validated['no_permintaan_barang'],
                'nama_supplier' => $validated['nama_supplier'],
                'nama_bank' => $validated['nama_bank'] ?? null,
                'account_name_supplier' => $validated['account_name_supplier'] ?? null,
                'account_number_supplier' => $validated['account_number_supplier'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'include_ppn' => $validated['include_ppn'] ?? 'no',
                'nominal_ppn' => $nominalPpn,
                'sub_total_pesanan' => $subTotalPesanan,
                'total_pesanan' => $totalPesanan,
                'user_id' => Auth::id(),
                'company_id' => $companyId,
            ]);

            $detailPermintaans = [];
            foreach ($validated['details'] as $detailData) {
                $barang = Barang::where('id', $detailData['barang_id'])->where('company_id', $companyId)->first(); // Pastikan barang milik company yg benar
                if (!$barang) {
                    // Seharusnya tidak terjadi jika validasi di request sudah benar
                    throw new \Exception("Barang dengan ID {$detailData['barang_id']} tidak ditemukan untuk perusahaan ini.");
                }
                $detailPermintaans[] = [
                    'permintaan_id' => $permintaan->id,
                    'barang_id' => $detailData['barang_id'],
                    'stok_terakhir' => $barang->stock_barang, // Ambil stock_barang
                    'jumlah_pesanan' => $detailData['jumlah_pesanan'],
                    'satuan' => $detailData['satuan'], // Pastikan ini nama satuan, bukan ID
                    'harga_per_satuan' => $detailData['harga_per_satuan'],
                    'total_harga' => ($detailData['jumlah_pesanan'] * $detailData['harga_per_satuan']),
                    // 'created_at' & 'updated_at' akan diisi otomatis oleh Eloquent jika menggunakan model
                ];
            }
            DetailPermintaan::insert($detailPermintaans); // Bulk insert lebih efisien

            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing Permintaan Barang: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan permintaan barang: Terjadi kesalahan sistem.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $permintaanBarang->load(['details.barang.unitSatuan', 'user', 'company']); // Eager load lebih dalam
        return view('permintaan-barang.show', compact('permintaanBarang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $barangs = Barang::where('company_id', $companyId)
            ->with('unitSatuan')
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'nama_barang', 'kode_barang', 'stock_barang', 'unit_satuan_id']); // Tambah kode_barang

        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan', 'asc')
            ->get(['id', 'nama_unit_satuan']);

        $permintaanBarang->load('details.barang'); // Eager load details dan barang terkait
        return view('permintaan-barang.edit', compact('permintaanBarang', 'barangs', 'unitSatuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermintaanBarangRequest $request, Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $subTotalPesanan = 0;
            foreach ($validated['details'] as $detail) {
                $subTotalPesanan += ($detail['jumlah_pesanan'] * $detail['harga_per_satuan']);
            }

            $nominalPpn = 0;
            if (($validated['include_ppn'] ?? 'no') === 'yes') {
                $nominalPpn = $subTotalPesanan * 0.11;
            }
            $totalPesanan = $subTotalPesanan + $nominalPpn;

            $permintaanBarang->update([
                'tgl_pengajuan' => $validated['tgl_pengajuan'],
                'no_permintaan_barang' => $validated['no_permintaan_barang'],
                'nama_supplier' => $validated['nama_supplier'],
                'nama_bank' => $validated['nama_bank'] ?? null,
                'account_name_supplier' => $validated['account_name_supplier'] ?? null,
                'account_number_supplier' => $validated['account_number_supplier'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'include_ppn' => $validated['include_ppn'] ?? 'no',
                'nominal_ppn' => $nominalPpn,
                'sub_total_pesanan' => $subTotalPesanan,
                'total_pesanan' => $totalPesanan,
                // user_id tidak diupdate di sini, diasumsikan user penginput awal tetap
            ]);

            // Hapus detail lama
            $permintaanBarang->details()->delete();

            // Tambahkan detail yang baru
            $detailPermintaans = [];
            foreach ($validated['details'] as $detailData) {
                $barang = Barang::where('id', $detailData['barang_id'])->where('company_id', $companyId)->first();
                if (!$barang) {
                    throw new \Exception("Barang dengan ID {$detailData['barang_id']} tidak ditemukan untuk perusahaan ini.");
                }
                $detailPermintaans[] = [
                    'permintaan_id' => $permintaanBarang->id,
                    'barang_id' => $detailData['barang_id'],
                    'stok_terakhir' => $barang->stock_barang, // Ambil stock_barang
                    'jumlah_pesanan' => $detailData['jumlah_pesanan'],
                    'satuan' => $detailData['satuan'],
                    'harga_per_satuan' => $detailData['harga_per_satuan'],
                    'total_harga' => ($detailData['jumlah_pesanan'] * $detailData['harga_per_satuan']),
                ];
            }
            if (!empty($detailPermintaans)) {
                DetailPermintaan::insert($detailPermintaans);
            }


            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating Permintaan Barang ID {$permintaanBarang->id}: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui permintaan barang: Terjadi kesalahan sistem.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        DB::beginTransaction();
        try {
            $permintaanBarang->details()->delete(); // Hapus detail dulu
            $permintaanBarang->delete();
            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting Permintaan Barang ID {$permintaanBarang->id}: {$e->getMessage()}");
            return redirect()->route('permintaan-barang.index')->with('error', 'Gagal menghapus permintaan barang: Terjadi kesalahan sistem.');
        }
    }

    /**
     * Print blank form.
     */
    public function printBlankForm()
    {
        $companyId = $this->getCompanyId();
        $company = Company::find($companyId);
        if (!$company) {
            return redirect()->back()->with('error', 'Perusahaan aktif tidak ditemukan.');
        }

        $data = [
            'title' => 'Formulir Permintaan Barang',
            'company' => $company,
            'pemohon' => Auth::user()?->name ?? 'N/A',
            'no_permintaan_barang' => '_________________',
            'tgl_pengajuan' => '___ / ___ / ______',
            'nama_supplier' => '_________________',
            'details' => [], // array kosong untuk template
            'keterangan' => '',
            'sub_total_pesanan' => 0,
            'nominal_ppn' => 0,
            'total_pesanan' => 0,
        ];
        // Menggunakan alias DomPDF yang sudah diimpor
        $pdf = DomPDF::loadView('permintaan-barang.pdf.form_permintaan_template', $data);
        return $pdf->stream('form_permintaan_barang_kosong.pdf');
    }

    /**
     * Print specific form.
     * @param int $id (atau Permintaan $permintaanBarang dengan route model binding)
     */
    public function printSpecificForm(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $permintaanBarang->load(['details.barang.unitSatuan', 'user', 'company']);

        $data = [
            'title' => 'Form Permintaan Barang',
            'permintaan' => $permintaanBarang,
            'company' => $permintaanBarang->company,
        ];
        $pdf = DomPDF::loadView('permintaan-barang.pdf.form_permintaan_template', $data);

        // PERBAIKAN NAMA FILE PDF
        $safeNoPermintaan = Str::slug($permintaanBarang->no_permintaan_barang, '-'); // Mengganti '/' dengan '-' atau karakter aman lainnya
        $fileName = 'permintaan_barang_' . $safeNoPermintaan . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Export specific Permintaan Barang to Excel.
     */
    public function exportItemExcel(Request $request, Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $request->merge(['id_permintaan_specific' => $permintaanBarang->id, 'company_id_filter' => $companyId]);

        // PERBAIKAN NAMA FILE EXCEL
        $safeNoPermintaan = Str::slug($permintaanBarang->no_permintaan_barang, '-');
        $fileName = 'permintaan_barang_' . $safeNoPermintaan . '_' . date('YmdHis') . '.xlsx';

        return Excel::download(new PermintaanBarangExport($request), $fileName);
    }
}
