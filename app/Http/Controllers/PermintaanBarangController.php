<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\DetailPermintaan;
use App\Models\Barang;
use App\Models\UnitSatuan;
use App\Models\Company; // Pastikan model Company ada dan benar
use App\Http\Requests\PermintaanBarang\StorePermintaanBarangRequest;
use App\Http\Requests\PermintaanBarang\UpdatePermintaanBarangRequest;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF; // Untuk Barryvdh\DomPDF
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use App\Exports\PermintaanBarangExport; // Tambahkan ini
use Maatwebsite\Excel\Facades\Excel; // Tambahkan ini

class PermintaanBarangController extends Controller implements HasMiddleware
{
    /**
     * Mendefinisikan middleware untuk controller ini.
     */
    public static function middleware(): array
    {
        return [
            'auth', // Memastikan user terautentikasi untuk semua action
            // Middleware company.access sudah diterapkan di web.php, jadi tidak perlu di sini lagi
            // new Middleware(\App\Http\Middleware\CheckCompanyAccess::class),

            new Middleware('permission:permintaan barang view', only: ['index', 'show']),
            new Middleware('permission:permintaan barang create', only: ['create', 'store']),
            new Middleware('permission:permintaan barang edit', only: ['edit', 'update']),
            new Middleware('permission:permintaan barang delete', only: ['destroy']),
            // Menggunakan 'permintaan barang export pdf' untuk aksi cetak PDF
            new Middleware('permission:permintaan barang export pdf', only: ['printBlankForm', 'printSpecificForm']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = session('sessionCompany');

        if ($request->query('export') === 'excel') {
            if (!Auth::user()->can('permintaan barang export excel')) {
                return redirect()->route('permintaan-barang.index')->with('error', __('Anda tidak memiliki izin untuk mengekspor data ini.'));
            }
            $fileName = 'daftar_permintaan_barang_' . date('YmdHis') . '.xlsx';
            return Excel::download(new PermintaanBarangExport($request), $fileName);
        }

        if ($request->ajax()) {
            $query = Permintaan::where('company_id', $companyId)
                ->with('user')
                ->select('permintaan.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->addColumn('tgl_pengajuan_formatted', function ($row) {
                    return \Carbon\Carbon::parse($row->tgl_pengajuan)->format('d-m-Y H:i');
                })
                ->addColumn('total_pesanan_formatted', function ($row) {
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
        $companyId = session('sessionCompany');
        $barangs = Barang::where('company_id', $companyId)
            ->with('unitSatuan')
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'nama_barang', 'stock_barang', 'unit_satuan_id']);

        // Ambil UnitSatuan berdasarkan company_id jika relevan, atau semua jika global
        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan')
            ->get(['id', 'nama_unit_satuan']);
        return view('permintaan-barang.create', compact('barangs', 'unitSatuans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermintaanBarangRequest $request)
    {
        $validated = $request->validated();
        $companyId =  session('sessionCompany');

        DB::beginTransaction();
        try {
            $subTotalPesanan = 0;
            foreach ($validated['details'] as $detail) {
                $subTotalPesanan += $detail['jumlah_pesanan'] * $detail['harga_per_satuan'];
            }

            $nominalPpn = 0;
            if (isset($validated['include_ppn']) && $validated['include_ppn'] === 'yes') {
                // Asumsi PPN 11% dari subtotal
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

            foreach ($validated['details'] as $detailData) {
                $barang = Barang::find($detailData['barang_id']);
                DetailPermintaan::create([
                    'permintaan_id' => $permintaan->id,
                    'barang_id' => $detailData['barang_id'],
                    'stok_terakhir' => $barang ? $barang->stock : 0,
                    'jumlah_pesanan' => $detailData['jumlah_pesanan'],
                    'satuan' => $detailData['satuan'],
                    'harga_per_satuan' => $detailData['harga_per_satuan'],
                    'total_harga' => $detailData['jumlah_pesanan'] * $detailData['harga_per_satuan'],
                ]);
            }

            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Tambahkan logging error jika perlu: Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan permintaan barang: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permintaan $permintaanBarang) // Route model binding
    {
        if ($permintaanBarang->company_id != session('sessionCompany')) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $permintaanBarang->load('details.barang', 'user', 'company');
        return view('permintaan-barang.show', compact('permintaanBarang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permintaan $permintaanBarang)
    {
        if ($permintaanBarang->company_id != session('sessionCompany')) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }
        $companyId = session('sessionCompany');

        $barangs = Barang::where('company_id', $companyId)
            ->with('unitSatuan')
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'nama_barang', 'stock_barang', 'unit_satuan_id']);

        $unitSatuans = UnitSatuan::where('company_id', $companyId)
            ->orderBy('nama_unit_satuan')
            ->get(['id', 'nama_unit_satuan']);
        $permintaanBarang->load('details');
        return view('permintaan-barang.edit', compact('permintaanBarang', 'barangs', 'unitSatuans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermintaanBarangRequest $request, Permintaan $permintaanBarang)
    {

        if ($permintaanBarang->company_id != session('sessionCompany')) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $subTotalPesanan = 0;
            foreach ($validated['details'] as $detail) {
                $subTotalPesanan += $detail['jumlah_pesanan'] * $detail['harga_per_satuan'];
            }

            $nominalPpn = 0;
            if (isset($validated['include_ppn']) && $validated['include_ppn'] === 'yes') {
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
            ]);

            // Hapus detail lama dan tambahkan yang baru
            $permintaanBarang->details()->delete();
            foreach ($validated['details'] as $detailData) {
                $barang = Barang::find($detailData['barang_id']);
                DetailPermintaan::create([
                    'permintaan_id' => $permintaanBarang->id,
                    'barang_id' => $detailData['barang_id'],
                    'stok_terakhir' => $barang ? $barang->stock : 0,
                    'jumlah_pesanan' => $detailData['jumlah_pesanan'],
                    'satuan' => $detailData['satuan'],
                    'harga_per_satuan' => $detailData['harga_per_satuan'],
                    'total_harga' => $detailData['jumlah_pesanan'] * $detailData['harga_per_satuan'],
                ]);
            }

            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui permintaan barang: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permintaan $permintaanBarang)
    {
        if ($permintaanBarang->company_id != session('sessionCompany')) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        try {
            DB::beginTransaction();
            // Hapus detail dulu karena ada foreign key constraint
            $permintaanBarang->details()->delete();
            $permintaanBarang->delete(); // Hapus master
            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('permintaan-barang.index')->with('error', 'Gagal menghapus permintaan barang: ' . $e->getMessage());
        }
    }

    /**
     * Print blank form.
     */
    public function printBlankForm()
    {
        $companyId = session('sessionCompany');
        $company = Company::find($companyId);

        $data = [
            'title' => 'Formulir Permintaan Barang',
            'company' => $company, // Kirim data perusahaan ke view PDF
            'pemohon' => Auth::user()->name,
            // Data dummy lainnya bisa ditambahkan jika perlu untuk template kosong
            'no_permintaan_barang' => '_________________',
            'tgl_pengajuan' => '___ / ___ / ______',
            'nama_supplier' => '_________________',
            'details' => [],
            'keterangan' => '',
            'sub_total_pesanan' => 0,
            'nominal_ppn' => 0,
            'total_pesanan' => 0,
        ];
        $pdf = PDF::loadView('permintaan-barang.pdf.form_permintaan_template', $data);
        return $pdf->stream('form_permintaan_barang_kosong.pdf');
    }

    /**
     * Print specific form.
     * @param int $id (atau Permintaan $permintaanBarang dengan route model binding)
     */
    public function printSpecificForm(Permintaan $permintaanBarang) // Menggunakan Route Model Binding
    {
        if ($permintaanBarang->company_id != session('sessionCompany')) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $permintaanBarang->load('details.barang', 'user', 'company');

        $data = [
            'title' => 'Form Permintaan Barang',
            'permintaan' => $permintaanBarang,
            'company' => $permintaanBarang->company,
        ];
        $pdf = FacadePdf::loadView('permintaan-barang.pdf.form_permintaan_template', $data);
        return $pdf->stream('permintaan_barang_' . $permintaanBarang->no_permintaan_barang . '.pdf');
    }

    /**
     * Export specific Permintaan Barang to Excel.
     *
     * @param  \App\Models\Permintaan  $permintaanBarang
     * @param  \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportItemExcel(Request $request, Permintaan $permintaanBarang)
    {
        if (!Auth::user()->can('permintaan barang export excel')) {
            return redirect()->route('permintaan-barang.index')->with('error', __('Anda tidak memiliki izin untuk mengekspor data ini.'));
        }

        $companyId = session('sessionCompany');
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        // Mengirimkan ID spesifik ke class Export. Class Export perlu dimodifikasi untuk menangani ini.
        $request->merge(['id_permintaan_specific' => $permintaanBarang->id]);

        $fileName = 'permintaan_barang_' . $permintaanBarang->no_permintaan_barang . '_' . date('YmdHis') . '.xlsx';
        return Excel::download(new PermintaanBarangExport($request), $fileName);
    }
}
