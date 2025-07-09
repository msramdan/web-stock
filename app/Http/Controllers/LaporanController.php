<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisMaterial;
use App\Models\Barang; // Pastikan model Barang sudah di-import
use App\Exports\LaporanTransaksiExport;
use App\Exports\LaporanStokExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman filter untuk Laporan Transaksi.
     */
    public function indexTransaksi()
    {
        $companyId = session('sessionCompany');
        $jenisMaterials = JenisMaterial::where('company_id', $companyId)->orderBy('nama_jenis_material')->get();
        $barangs = Barang::where('company_id', $companyId)->orderBy('nama_barang', 'asc')->get(['id', 'nama_barang']);

        return view('laporan.transaksi', compact('jenisMaterials', 'barangs')); // View baru
    }

    // Method getBarangOptions(Request $request) TIDAK DIPERLUKAN LAGI

    public function exportExcel(Request $request)
    {
        $maxRangeDays = env('MAX_REPORT_RANGE_DAYS', 90);
        $companyId = session('sessionCompany');

        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_material_id' => ['nullable', Rule::exists('jenis_material', 'id')->where(fn($q) => $q->where('company_id', $companyId))],
            'tipe_barang' => ['nullable', 'string', Rule::in(['Bahan Baku', 'Barang Jadi'])],
            'barang_id' => ['nullable', Rule::exists('barang', 'id')->where(fn($q) => $q->where('company_id', $companyId))], // Validasi untuk barang_id
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
        ]);

        $validator->after(function ($validator) use ($request, $maxRangeDays) {
            if ($request->filled(['tanggal_mulai', 'tanggal_selesai'])) {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
                if ($tanggalMulai->diffInDays($tanggalSelesai) > $maxRangeDays) {
                    $validator->errors()->add('tanggal_selesai', "Rentang tanggal laporan maksimal {$maxRangeDays} hari.");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('laporan.index')
                ->withErrors($validator)
                ->withInput();
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $jenisMaterialId = $request->input('jenis_material_id');
        $tipeBarang = $request->input('tipe_barang');
        $barangId = $request->input('barang_id'); // Ambil barang_id dari request

        $startDate = Carbon::parse($tanggalMulai)->format('Y-m-d');
        $endDate = Carbon::parse($tanggalSelesai)->format('Y-m-d');

        $filterDescParts = [];
        if ($tipeBarang) {
            $filterDescParts[] = Str::slug($tipeBarang);
        }
        if ($jenisMaterialId) {
            $filterDescParts[] = 'material-' . $jenisMaterialId;
        }
        if ($barangId) { // Tambahkan deskripsi untuk barang_id
            $filterDescParts[] = 'barang-' . $barangId;
        }
        if (empty($filterDescParts)) {
            $filterDescParts[] = 'semua';
        }
        $filterDesc = implode('_', $filterDescParts);

        $fileName = $startDate . '_sd_' . $endDate . '_laporan_pergerakan_barang_' . $filterDesc . '.xlsx';

        // Kirim semua filter termasuk barangId ke Export class
        return Excel::download(new LaporanTransaksiExport($tanggalMulai, $tanggalSelesai, $jenisMaterialId, $tipeBarang, $barangId), $fileName);
    }


    /**
     * Menampilkan halaman filter untuk Laporan Stok Barang.
     */
    public function indexStockBarang()
    {
        $companyId = session('sessionCompany');
        $jenisMaterials = JenisMaterial::where('company_id', $companyId)->orderBy('nama_jenis_material')->get();
        $barangs = Barang::where('company_id', $companyId)->orderBy('nama_barang', 'asc')->get(['id', 'nama_barang']);

        return view('laporan.stock-barang', compact('jenisMaterials', 'barangs')); // View baru
    }

    /**
     * Memproses dan mengekspor Laporan Transaksi ke Excel.
     */
    public function exportExcelTransaksi(Request $request)
    {
        $maxRangeDays = env('MAX_REPORT_RANGE_DAYS', 90);
        $companyId = session('sessionCompany');

        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_material_id' => ['nullable', Rule::exists('jenis_material', 'id')->where(fn($q) => $q->where('company_id', $companyId))],
            'tipe_barang' => ['nullable', 'string', Rule::in(['Bahan Baku', 'Barang Jadi'])],
            'barang_id' => ['nullable', Rule::exists('barang', 'id')->where(fn($q) => $q->where('company_id', $companyId))], // Validasi untuk barang_id
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $jenisMaterialId = $request->input('jenis_material_id');
        $tipeBarang = $request->input('tipe_barang');
        $barangId = $request->input('barang_id');

        $fileName = 'Laporan-Transaksi_' . Carbon::parse($tanggalMulai)->format('Ymd') . '-' . Carbon::parse($tanggalSelesai)->format('Ymd') . '.xlsx';

        return Excel::download(new LaporanTransaksiExport($tanggalMulai, $tanggalSelesai, $jenisMaterialId, $tipeBarang, $barangId), $fileName);
    }

    /**
     * Memproses dan mengekspor Laporan Stok Barang ke Excel.
     */
    public function exportExcelStockBarang(Request $request)
    {
        $companyId = session('sessionCompany');
        // 1. Ambil nilai maksimal rentang hari dari .env, defaultnya 31 jika tidak ada
        $maxRangeDays = env('MAX_STOCK_CARD_REPORT_RANGE_DAYS', 31);

        // 2. Validasi input, termasuk tanggal yang sekarang wajib
        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_material_id' => ['nullable', Rule::exists('jenis_material', 'id')->where('company_id', $companyId)],
            'tipe_barang' => ['nullable', 'string', Rule::in(['Bahan Baku', 'Barang Jadi', 'Kemasan'])],
            'barang_id' => ['nullable', Rule::exists('barang', 'id')->where('company_id', $companyId)],
        ]);

        // 3. Tambahkan validasi kustom untuk memeriksa rentang tanggal
        $validator->after(function ($validator) use ($request, $maxRangeDays) {
            if ($request->filled(['tanggal_mulai', 'tanggal_selesai'])) {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
                if ($tanggalMulai->diffInDays($tanggalSelesai) > ($maxRangeDays - 1)) {
                    $validator->errors()->add('tanggal_selesai', "Rentang tanggal laporan stok maksimal {$maxRangeDays} hari.");
                }
            }
        });
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $jenisMaterialId = $request->input('jenis_material_id');
        $tipeBarang = $request->input('tipe_barang');
        $barangId = $request->input('barang_id');

        $fileName = 'Laporan-Stok-Barang_' . date('Y-m-d_H-i') . '.xlsx';

        // Panggil Export Class dengan semua parameter yang dibutuhkan
        return Excel::download(new LaporanStokExport($tanggalMulai, $tanggalSelesai, $jenisMaterialId, $tipeBarang, $barangId), $fileName);
    }
}
