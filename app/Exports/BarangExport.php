<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\Company;

class BarangExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $tipeBarang;
    protected $companyId;
    protected $activeCompany;
    protected $data;
    protected $grandTotal = 0;

    public function __construct($tipeBarang = null)
    {
        $this->tipeBarang = $tipeBarang;
        $this->companyId = session('sessionCompany');
        $this->activeCompany = Company::find($this->companyId);
    }

    public function collection()
    {
        $companyId = $this->companyId;

        $query = DB::table('barang')
            ->leftJoin('jenis_material', function ($join) use ($companyId) {
                $join->on('barang.jenis_material_id', '=', 'jenis_material.id')
                    ->where('jenis_material.company_id', '=', $companyId);
            })
            ->leftJoin('unit_satuan', function ($join) use ($companyId) {
                $join->on('barang.unit_satuan_id', '=', 'unit_satuan.id')
                    ->where('unit_satuan.company_id', '=', $companyId);
            })
            ->where('barang.company_id', $companyId)
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'barang.tipe_barang',
                'barang.deskripsi_barang',
                'barang.harga',
                'barang.stock_barang',
                'jenis_material.nama_jenis_material',
                'unit_satuan.nama_unit_satuan',
                DB::raw('barang.harga * barang.stock_barang as total_harga')
            );

        if (!empty($this->tipeBarang)) {
            $query->where('barang.tipe_barang', $this->tipeBarang);
        }

        $this->data = $query->orderBy('barang.kode_barang')->get();

        // Hitung grand total
        $this->grandTotal = $this->data->sum('total_harga');

        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Tipe Barang',
            'Deskripsi',
            'Jenis Material',
            'Harga',
            'Unit Satuan',
            'Stok',
            'Total Harga',
        ];
    }

    public function map($row): array
    {
        $tipeBarangText = $row->tipe_barang ?? '-';
        if ($tipeBarangText == 'Barang Jadi') $tipeBarangText = 'Barang Jadi';
        if ($tipeBarangText == 'Bahan Baku') $tipeBarangText = 'Bahan Baku';

        return [
            $row->kode_barang ?? '-',
            $row->nama_barang ?? '-',
            $tipeBarangText,
            $row->deskripsi_barang ?? '-',
            $row->nama_jenis_material ?? '-',
            $row->harga !== null ? $row->harga : '-',
            $row->nama_unit_satuan ?? '-',
            (float) $row->stock_barang,
            (float) $row->total_harga,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Harga
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Stok
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Harga
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = $this->data->count() + 2; // +2 for headings (1-based index)
                $sheet = $event->sheet;

                // Tampilkan GRAND TOTAL di baris paling bawah
                $sheet->setCellValue('H' . $rowCount, 'Grand Total:');
                $sheet->setCellValue('I' . $rowCount, $this->grandTotal);

                // Style Grand Total
                $sheet->getStyle('H' . $rowCount)->getFont()->setBold(true);
                $sheet->getStyle('I' . $rowCount)->getFont()->setBold(true);
                $sheet->getStyle('I' . $rowCount)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Optional: highlight total row
                $sheet->getStyle('H' . $rowCount . ':I' . $rowCount)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FCE4D6');
            },
        ];
    }
}
