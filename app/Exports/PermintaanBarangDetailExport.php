<?php

namespace App\Exports;

use App\Models\Permintaan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PermintaanBarangDetailExport implements FromView, ShouldAutoSize, WithTitle, WithColumnFormatting
{
    protected $permintaan;

    public function __construct(Permintaan $permintaan)
    {
        // Eager load relasi yang dibutuhkan untuk view Excel
        // Pastikan relasi 'company' dan 'user' juga di-load jika dibutuhkan di template excel
        $this->permintaan = $permintaan->load(['details.barang.unitSatuan', 'user', 'company']);
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        // Anda perlu membuat file view blade ini
        return view('permintaan-barang.excel.detail_template', [
            'permintaan' => $this->permintaan,
            'company'    => $this->permintaan->company, // Mengambil data company dari relasi
            'title'      => 'Detail Permintaan Barang ' . $this->permintaan->no_permintaan_barang
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        // Judul untuk sheet di Excel
        return 'Detail Permintaan ' . Str::slug($this->permintaan->no_permintaan_barang, '_');
    }
}
