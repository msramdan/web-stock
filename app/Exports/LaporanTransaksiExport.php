<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class LaporanTransaksiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    protected $tanggalMulai;
    protected $tanggalSelesai;
    protected $jenisMaterialId;
    protected $tipeBarang;
    protected $barangId;
    protected $companyId;

    public function __construct(string $tanggalMulai, string $tanggalSelesai, $jenisMaterialId, $tipeBarang, $barangId = null)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->jenisMaterialId = $jenisMaterialId;
        $this->tipeBarang = $tipeBarang;
        $this->barangId = $barangId;
        $this->companyId = session('sessionCompany');
    }

    public function collection()
    {
        $startDate = Carbon::parse($this->tanggalMulai)->startOfDay();
        $endDate = Carbon::parse($this->tanggalSelesai)->endOfDay();
        $companyId = $this->companyId;

        // Query Transaksi (Stock In/Out)
        $transaksiQuery = DB::table('transaksi_detail as td')
            ->select(
                DB::raw("'Transaksi' as sumber_data"),
                't.no_surat as no_dokumen',
                't.tanggal',
                't.type as tipe_pergerakan',
                't.keterangan as keterangan',
                'u.name as user_name',
                'b.kode_barang',
                'b.nama_barang',
                'b.tipe_barang',
                'b.deskripsi_barang',
                'jm.nama_jenis_material',
                'us.nama_unit_satuan',
                'td.qty'
            )
            ->join('transaksi as t', 'td.transaksi_id', '=', 't.id')
            ->join('barang as b', 'td.barang_id', '=', 'b.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->leftJoin('jenis_material as jm', 'b.jenis_material_id', '=', 'jm.id')
            ->leftJoin('unit_satuan as us', 'b.unit_satuan_id', '=', 'us.id')
            ->where('t.company_id', $companyId)
            ->whereBetween('t.tanggal', [$startDate, $endDate]);

        if (!empty($this->jenisMaterialId)) {
            $transaksiQuery->where('b.jenis_material_id', $this->jenisMaterialId);
        }
        if (!empty($this->tipeBarang)) {
            // Map user-facing filter values to database values
            $tipeBarangDb = $this->tipeBarang; // Use the same value as in the form (Bahan Baku or Barang Jadi)
            $transaksiQuery->where('b.tipe_barang', $tipeBarangDb);
        }
        if (!empty($this->barangId)) {
            $transaksiQuery->where('td.barang_id', $this->barangId);
        }

        // Query Produksi
        $produksiQuery = DB::table('produksi_details as pd')
            ->select(
                DB::raw("'Produksi' as sumber_data"),
                'p.no_produksi as no_dokumen',
                'p.tanggal',
                'p.keterangan as keterangan',
                'pd.type as tipe_pergerakan',
                DB::raw("COALESCE(u.name, '-') as user_name"),
                'b.kode_barang',
                'b.nama_barang',
                'b.tipe_barang',
                'b.deskripsi_barang',
                'jm.nama_jenis_material',
                'us.nama_unit_satuan',
                'pd.qty_total_diperlukan as qty'
            )
            ->join('produksi as p', 'pd.produksi_id', '=', 'p.id')
            ->join('barang as b', 'pd.barang_id', '=', 'b.id')
            ->leftJoin('users as u', 'p.user_id', '=', 'u.id') // Join ke users
            ->leftJoin('jenis_material as jm', 'b.jenis_material_id', '=', 'jm.id')
            ->leftJoin('unit_satuan as us', 'pd.unit_satuan_id', '=', 'us.id')
            ->where('p.company_id', $companyId)
            ->whereBetween('p.tanggal', [$startDate, $endDate]);

        if (!empty($this->jenisMaterialId)) {
            $produksiQuery->where('b.jenis_material_id', $this->jenisMaterialId);
        }
        if (!empty($this->tipeBarang)) {
            // Map user-facing filter values to database values
            $tipeBarangDb = $this->tipeBarang; // Use the same value as in the form (Bahan Baku or Barang Jadi)
            $produksiQuery->where('b.tipe_barang', $tipeBarangDb);
        }
        if (!empty($this->barangId)) {
            $produksiQuery->where('pd.barang_id', $this->barangId);
        }

        $transaksiQuery->unionAll($produksiQuery);

        $results = DB::query()->fromSub($transaksiQuery, 'combined_data')
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_dokumen', 'asc')
            ->orderBy('tipe_pergerakan', 'desc')
            ->orderBy('kode_barang', 'asc')
            ->get();

        return $results ?: collect([]);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Sumber Data',
            'No Dokumen',
            'Keterangan',
            'Tipe Pergerakan',
            'User',
            'Kode Barang',
            'Nama Barang',
            'Tipe Barang',
            'Deskripsi Barang',
            'Jenis Material',
            'Unit Satuan',
            'Qty',
        ];
    }

    public function map($row): array
    {
        return [
            $row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y H:i') : '-',
            $row->sumber_data ?? 'N/A',
            $row->no_dokumen ?? 'N/A',
            $row->keterangan ?? '-',
            $row->tipe_pergerakan ?? '-',
            $row->user_name ?? '-',
            $row->kode_barang ?? '-',
            $row->nama_barang ?? '-',
            $row->tipe_barang ?? '-',
            $row->deskripsi_barang ?? '-',
            $row->nama_jenis_material ?? '-',
            $row->nama_unit_satuan ?? '-',
            (float) $row->qty,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
