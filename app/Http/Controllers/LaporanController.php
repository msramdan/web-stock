<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisMaterial;
use App\Exports\LaporanTransaksiExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    public function index()
    {
        $companyId = session('sessionCompany');
        $jenisMaterials = JenisMaterial::where('company_id', $companyId)
            ->orderBy('nama_jenis_material')->get();
        return view('laporan.index', compact('jenisMaterials'));
    }

    public function exportExcel(Request $request)
    {
        $maxRangeDays = env('MAX_REPORT_RANGE_DAYS', 90);
        $companyId = session('sessionCompany');

        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_material_id' => ['nullable', Rule::exists('jenis_material', 'id')->where(fn($q) => $q->where('company_id', $companyId))],
            'tipe_barang' => ['nullable', 'string', Rule::in(['Bahan Baku', 'Barang Jadi'])], // <-- VALIDASI BARU
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
        $tipeBarang = $request->input('tipe_barang'); // <-- AMBIL FILTER BARU

        // Buat nama file lebih deskriptif
        $filterDesc = $tipeBarang ? Str::slug($tipeBarang) : ($jenisMaterialId ? 'material_' . $jenisMaterialId : 'semua');
        $fileName = 'laporan_pergerakan_barang_' . $filterDesc . '_' . $tanggalMulai . '_sd_' . $tanggalSelesai . '.xlsx';

        // Kirim SEMUA filter ke class Export
        return Excel::download(new LaporanTransaksiExport($tanggalMulai, $tanggalSelesai, $jenisMaterialId, $tipeBarang), $fileName);
    }
}
