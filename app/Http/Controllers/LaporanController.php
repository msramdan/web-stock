<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisMaterial;
use App\Models\Barang; // Tambahkan ini
use App\Exports\LaporanTransaksiExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    public function index(Request $request) // Tambahkan Request $request
    {
        $companyId = session('sessionCompany');
        $jenisMaterials = JenisMaterial::where('company_id', $companyId)
            ->orderBy('nama_jenis_material')->get();

        // Logika untuk mengisi dropdown barang berdasarkan filter yang mungkin sudah ada saat reload
        $queryBarang = Barang::where('company_id', $companyId)->orderBy('nama_barang', 'asc');
        if ($request->old('jenis_material_id')) {
            $queryBarang->where('jenis_material_id', $request->old('jenis_material_id'));
        }
        if ($request->old('tipe_barang')) {
            // Sesuaikan nama kolom 'tipe_barang' jika berbeda di model Barang Anda
            if ($request->old('tipe_barang') === 'Bahan Baku') {
                $queryBarang->where('tipe_barang', 'BAHAN_BAKU');
            } elseif ($request->old('tipe_barang') === 'Barang Jadi') {
                $queryBarang->where('tipe_barang', 'PRODUK_JADI');
            }
        }
        $barangs = $queryBarang->get(['id', 'nama_barang']);


        return view('laporan.index', compact('jenisMaterials', 'barangs'));
    }

    // METHOD BARU UNTUK AJAX
    public function getBarangOptions(Request $request)
    {
        $companyId = session('sessionCompany');
        $jenisMaterialId = $request->input('jenis_material_id');
        $tipeBarangInput = $request->input('tipe_barang'); // Misal: "Bahan Baku" atau "Barang Jadi"

        $query = Barang::where('company_id', $companyId);

        if ($jenisMaterialId) {
            $query->where('jenis_material_id', $jenisMaterialId);
        }

        if ($tipeBarangInput) {
            // Sesuaikan dengan nilai yang Anda gunakan di model Barang (misal: 'BAHAN_BAKU', 'PRODUK_JADI')
            if ($tipeBarangInput === 'Bahan Baku') {
                $query->where('tipe_barang', 'BAHAN_BAKU');
            } elseif ($tipeBarangInput === 'Barang Jadi') {
                $query->where('tipe_barang', 'PRODUK_JADI');
            }
        }

        $barangs = $query->orderBy('nama_barang', 'asc')->get(['id', 'nama_barang']);
        return response()->json($barangs);
    }


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
        ], [ /* messages */]);

        $validator->after(function ($validator) use ($request, $maxRangeDays) {
            if ($request->filled(['tanggal_mulai', 'tanggal_selesai'])) {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
                if ($tanggalMulai->diffInDays($tanggalSelesai) > $maxRangeDays) {
                    $validator->errors()->add('tanggal_selesai', "Rentang tanggal maks {$maxRangeDays} hari.");
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('laporan.index')->withErrors($validator)->withInput();
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $jenisMaterialId = $request->input('jenis_material_id');
        $tipeBarang = $request->input('tipe_barang');
        $barangId = $request->input('barang_id'); // Ambil barang_id

        $startDate = Carbon::parse($tanggalMulai)->format('Y-m-d');
        $endDate = Carbon::parse($tanggalSelesai)->format('Y-m-d');

        $filterDescParts = [];
        if ($tipeBarang) {
            $filterDescParts[] = Str::slug($tipeBarang);
        }
        if ($jenisMaterialId) {
            $filterDescParts[] = 'material-' . $jenisMaterialId;
        }
        if ($barangId) {
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
}
