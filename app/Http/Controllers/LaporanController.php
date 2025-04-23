<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisMaterial; // <-- Tambahkan model JenisMaterial
use App\Exports\LaporanTransaksiExport; // <-- Kita akan buat ini nanti
use Maatwebsite\Excel\Facades\Excel; // <-- Tambahkan Facade Excel
use Carbon\Carbon; // <-- Tambahkan Carbon untuk manipulasi tanggal
use Illuminate\Support\Facades\Validator; // <-- Tambahkan Validator

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil data Jenis Material untuk dropdown filter
        $jenisMaterials = JenisMaterial::orderBy('nama_jenis_material')->get();

        // Tampilkan view form filter dan kirim data jenis material
        return view('laporan.index', compact('jenisMaterials'));
    }

    /**
     * Menghandle request export laporan ke Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        // --- Validasi Input ---
        $maxRangeDays = env('MAX_REPORT_RANGE_DAYS', 90); // Ambil dari .env, default 90

        $validator = Validator::make($request->all(), [
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_material_id' => 'nullable|exists:jenis_materials,id', // Jenis Material opsional
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        // Validasi tambahan untuk range tanggal
        $validator->after(function ($validator) use ($request, $maxRangeDays) {
            if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
                $tanggalMulai = Carbon::parse($request->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($request->tanggal_selesai);

                if ($tanggalMulai->diffInDays($tanggalSelesai) > $maxRangeDays) {
                    $validator->errors()->add(
                        'tanggal_selesai',
                        "Rentang tanggal tidak boleh melebihi {$maxRangeDays} hari."
                    );
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->route('laporan.index')
                ->withErrors($validator)
                ->withInput(); // Kembalikan ke form dengan error dan input lama
        }

        // --- Ambil Data dari Request ---
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $jenisMaterialId = $request->input('jenis_material_id'); // Bisa null

        // --- Nama File Excel ---
        $fileName = 'laporan_transaksi_' . $tanggalMulai . '_sd_' . $tanggalSelesai . '.xlsx';

        // --- Proses Export ---
        // Gunakan class export yang akan kita buat (LaporanTransaksiExport)
        // Kirim parameter filter ke class export
        return Excel::download(new LaporanTransaksiExport($tanggalMulai, $tanggalSelesai, $jenisMaterialId), $fileName);
    }
}
