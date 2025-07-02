<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisMaterial;
use App\Models\Barang; // Pastikan model Barang sudah di-import
use App\Exports\LaporanTransaksiExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    public function index(Request $request) // Request $request tetap berguna untuk old()
    {
        $companyId = session('sessionCompany');
        $jenisMaterials = JenisMaterial::where('company_id', $companyId) // Menggunakan company_id
            ->orderBy('nama_jenis_material')->get();

        // Mengambil SEMUA barang untuk dropdown "Nama Barang" yang relevan dengan companyId
        // Dropdown ini akan statis.
        $barangs = Barang::where('company_id', $companyId) // Menggunakan company_id
            ->orderBy('nama_barang', 'asc')
            ->get(['id', 'nama_barang']); // Ambil id dan nama_barang

        return view('laporan.index', compact('jenisMaterials', 'barangs'));
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
}
