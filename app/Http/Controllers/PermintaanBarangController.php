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
use App\Exports\PermintaanBarangDetailExport;

class PermintaanBarangController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:permintaan barang view', only: ['index', 'show']),
            new Middleware('permission:permintaan barang create', only: ['create', 'store']),
            new Middleware('permission:permintaan barang edit', only: ['edit', 'update']),
            new Middleware('permission:permintaan barang delete', only: ['destroy']),
            new Middleware('permission:permintaan barang export pdf', only: ['printBlankForm', 'printSpecificForm']),
            new Middleware('permission:permintaan barang export excel', only: ['exportItemExcel']),
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
            if (!Auth::user()->can('permintaan barang export excel')) {
                return redirect()->route('permintaan-barang.index')->with('error', __('Anda tidak memiliki izin untuk mengekspor data ini.'));
            }
            // Jika PermintaanBarangExport memerlukan companyId, pastikan itu dilewatkan atau diakses di dalam Export class
            $requestToExport = $request->duplicate(); // Duplikasi request untuk dimodifikasi
            $requestToExport->mergeIfMissing(['company_id_filter' => $companyId]);

            $fileName = 'daftar_permintaan_barang_' . date('YmdHis') . '.xlsx';
            return Excel::download(new PermintaanBarangExport($requestToExport), $fileName);
        }

        if ($request->ajax()) {
            $query = Permintaan::where('company_id', $companyId)
                ->with('user:id,name')
                ->select('permintaan.*');

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user?->name ?? 'N/A';
                })
                ->addColumn('tgl_pengajuan_formatted', function ($row) {
                    return Carbon::parse($row->tgl_pengajuan)->format('d-m-Y H:i');
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

    public function store(StorePermintaanBarangRequest $request)
    {
        $validated = $request->validated();
        $companyId = $this->getCompanyId();
        if (!$companyId) {
            return redirect()->back()->withInput()->with('error', 'Sesi perusahaan tidak valid. Silakan pilih perusahaan.');
        }

        DB::beginTransaction();
        try {
            $subTotalPesanan = 0;
            foreach ($validated['details'] as $detail) {
                $subTotalPesanan += ((float)$detail['jumlah_pesanan'] * (float)$detail['harga_per_satuan']);
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

            foreach ($validated['details'] as $detailData) {
                $barang = Barang::where('id', $detailData['barang_id'])
                    ->where('company_id', $companyId)
                    ->first();
                if (!$barang) {
                    throw new \Exception("Barang dengan ID {$detailData['barang_id']} tidak valid atau tidak ditemukan untuk perusahaan ini.");
                }

                DetailPermintaan::create([
                    'permintaan_id' => $permintaan->id,
                    'barang_id' => $detailData['barang_id'],
                    'stok_terakhir' => $barang->stock_barang,
                    'jumlah_pesanan' => $detailData['jumlah_pesanan'],
                    'satuan' => $detailData['satuan'],
                    'harga_per_satuan' => $detailData['harga_per_satuan'],
                    'total_harga' => ((float)$detailData['jumlah_pesanan'] * (float)$detailData['harga_per_satuan']),
                ]);
            }

            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing Permintaan Barang: " . $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan permintaan barang: ' . $e->getMessage());
        }
    }

    public function show(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $permintaanBarang->load(['details.barang.unitSatuan', 'user:id,name', 'company']);
        return view('permintaan-barang.show', compact('permintaanBarang'));
    }

    public function edit(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }
        if (!$companyId) {
            return redirect()->route('dashboard')->with('error', 'Silakan pilih perusahaan untuk mengedit permintaan barang.');
        }
        $permintaanBarang->load('details.barang');
        // $barangs dan $unitSatuans tidak lagi dipass ke view ini
        return view('permintaan-barang.edit', compact('permintaanBarang'));
    }

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
                $subTotalPesanan += ((float)$detail['jumlah_pesanan'] * (float)$detail['harga_per_satuan']);
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
            ]);

            $permintaanBarang->details()->delete();

            foreach ($validated['details'] as $detailData) {
                $barang = Barang::where('id', $detailData['barang_id'])
                    ->where('company_id', $companyId)
                    ->first();
                if (!$barang) {
                    throw new \Exception("Barang dengan ID {$detailData['barang_id']} tidak valid atau tidak ditemukan untuk perusahaan ini.");
                }
                DetailPermintaan::create([
                    'permintaan_id' => $permintaanBarang->id,
                    'barang_id' => $detailData['barang_id'],
                    'stok_terakhir' => $barang->stock_barang,
                    'jumlah_pesanan' => $detailData['jumlah_pesanan'],
                    'satuan' => $detailData['satuan'],
                    'harga_per_satuan' => $detailData['harga_per_satuan'],
                    'total_harga' => ((float)$detailData['jumlah_pesanan'] * (float)$detailData['harga_per_satuan']),
                ]);
            }

            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating Permintaan Barang ID {$permintaanBarang->id}: " . $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui permintaan barang: ' . $e->getMessage());
        }
    }

    public function destroy(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        DB::beginTransaction();
        try {
            $permintaanBarang->details()->delete();
            $permintaanBarang->delete();
            DB::commit();
            return redirect()->route('permintaan-barang.index')->with('success', 'Permintaan barang berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting Permintaan Barang ID {$permintaanBarang->id}: " . $e->getMessage());
            return redirect()->route('permintaan-barang.index')->with('error', 'Gagal menghapus permintaan barang: ' . $e->getMessage());
        }
    }

    public function printBlankForm()
    {
        $companyId = $this->getCompanyId();
        $company = Company::find($companyId);
        if (!$company) {
            return redirect()->back()->with('error', 'Data perusahaan tidak ditemukan. Silakan pilih perusahaan yang valid.');
        }

        $data = [
            'title' => 'Formulir Permintaan Barang',
            'company' => $company,
            'pemohon' => Auth::user()?->name ?? 'N/A',
            'no_permintaan_barang' => '_________________',
            'tgl_pengajuan' => '___ / ___ / ______ __:__',
            'nama_supplier' => '_________________',
            'details' => [],
            'keterangan' => '',
            'sub_total_pesanan' => 0,
            'nominal_ppn' => 0,
            'total_pesanan' => 0,
        ];
        $pdf = DomPDF::loadView('permintaan-barang.pdf.form_permintaan_template', $data);
        return $pdf->stream('form_permintaan_barang_kosong.pdf');
    }

    public function printSpecificForm(Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }

        $permintaanBarang->load(['details.barang.unitSatuan', 'user:id,name', 'company']);

        $data = [
            'title' => 'Form Permintaan Barang',
            'permintaan' => $permintaanBarang,
            'company' => $permintaanBarang->company,
        ];
        $pdf = DomPDF::loadView('permintaan-barang.pdf.form_permintaan_template', $data);

        // PERBAIKAN NAMA FILE PDF dari karakter ilegal
        $safeNoPermintaan = Str::slug($permintaanBarang->no_permintaan_barang, '_'); // Mengganti '/' dan spasi dengan '_'
        $fileName = 'permintaan_barang_' . $safeNoPermintaan . '.pdf';

        return $pdf->stream($fileName);
    }

    public function exportItemExcel(Request $request, Permintaan $permintaanBarang)
    {
        $companyId = $this->getCompanyId();
        if ($permintaanBarang->company_id != $companyId) {
            abort(403, 'Anda tidak memiliki akses ke dokumen permintaan ini.');
        }
        if (!Auth::user()->can('permintaan barang export excel')) {
            return redirect()->route('permintaan-barang.index')->with('error', __('Anda tidak memiliki izin untuk mengekspor data ini.'));
        }

        $safeNoPermintaan = Str::slug($permintaanBarang->no_permintaan_barang, '_');
        // Nama file bisa dibedakan sedikit jika perlu, atau disamakan jika kontennya memang satu baris juga
        $fileName = 'permintaan_barang_item_' . $safeNoPermintaan . '_' . date('YmdHis') . '.xlsx';

        // Buat instance request baru untuk dikirim ke PermintaanBarangExport
        $exportRequest = new Request();
        $exportRequest->merge([
            'id_permintaan_specific' => $permintaanBarang->id,
            'company_id_filter'      => $companyId // Pastikan company_id_filter juga terkirim
        ]);

        return Excel::download(new PermintaanBarangExport($exportRequest), $fileName);
    }
}
