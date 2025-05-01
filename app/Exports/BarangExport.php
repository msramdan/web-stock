<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use App\Models\Company; // Import Company model if needed for company name in filename

class BarangExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tipeBarang;
    protected $companyId;
    protected $activeCompany; // Tambahkan properti untuk menyimpan info company

    // Terima filter tipe_barang saat inisialisasi
    public function __construct($tipeBarang = null)
    {
        $this->tipeBarang = $tipeBarang;
        $this->companyId = session('sessionCompany');
        $this->activeCompany = Company::find($this->companyId); // Ambil info company
    }

    public function collection()
    {
        $companyId = $this->companyId;

        // Query data barang (mirip dengan di controller index)
        $query = DB::table('barang')
            ->leftJoin('jenis_material', function ($join) use ($companyId) {
                $join->on('barang.jenis_material_id', '=', 'jenis_material.id')
                    ->where('jenis_material.company_id', '=', $companyId); // Filter join
            })
            ->leftJoin('unit_satuan', function ($join) use ($companyId) {
                $join->on('barang.unit_satuan_id', '=', 'unit_satuan.id')
                    ->where('unit_satuan.company_id', '=', $companyId); // Filter join
            })
            ->where('barang.company_id', $companyId) // Filter utama barang
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'barang.tipe_barang',
                'barang.deskripsi_barang',
                'barang.stock_barang',
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan'
            );

        // Terapkan filter tipe barang jika ada
        if (!empty($this->tipeBarang)) {
            $query->where('barang.tipe_barang', $this->tipeBarang);
        }

        // Urutkan berdasarkan kode barang
        $results = $query->orderBy('barang.kode_barang')->get();

        // Penanganan data kosong
        if ($results->isEmpty()) {
            return collect([]);
        }

        return $results;
    }

    public function headings(): array
    {
        // Definisikan header kolom Excel
        return [
            'Kode Barang',
            'Nama Barang',
            'Tipe Barang',
            'Deskripsi',
            'Jenis Material',
            'Unit Satuan',
            'Stok',
        ];
    }

    public function map($row): array
    {
        $finalStock = formatAngkaRibuan( $row->stock_barang);

        // Format tipe barang
        $tipeBarangText = $row->tipe_barang ?? '-';
        if ($tipeBarangText == 'Barang Jadi') $tipeBarangText = 'Barang Jadi';
        if ($tipeBarangText == 'Bahan Baku') $tipeBarangText = 'Bahan Baku';


        return [
            $row->kode_barang ?? '-',
            $row->nama_barang ?? '-',
            $tipeBarangText, // Tampilkan teks tipe barang
            $row->deskripsi_barang ?? '-',
            $row->nama_jenis_material ?? '-',
            $row->nama_unit_satuan ?? '-',
            $finalStock, // Stok yang sudah diformat
        ];
    }

    // Opsional: Jika ingin nama file dinamis (tambahkan WithProperties concern)
    // public function properties(): array
    // {
    //     return [
    //         'creator'        => auth()->user()->name ?? 'System',
    //         'title'          => 'Data Barang',
    //         'description'    => 'Laporan Data Barang Perusahaan ' . ($this->activeCompany->nama_perusahaan ?? 'N/A'),
    //         'company'        => $this->activeCompany->nama_perusahaan ?? 'N/A',
    //     ];
    // }
}
