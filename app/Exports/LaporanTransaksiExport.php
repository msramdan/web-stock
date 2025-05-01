<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class LaporanTransaksiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalSelesai;
    protected $jenisMaterialId;
    protected $tipeBarang;
    protected $companyId;

    public function __construct(string $tanggalMulai, string $tanggalSelesai, $jenisMaterialId, $tipeBarang)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->jenisMaterialId = $jenisMaterialId;
        $this->tipeBarang = $tipeBarang;
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
            $transaksiQuery->where('b.tipe_barang', $this->tipeBarang);
        }

        // Query Produksi
        $produksiQuery = DB::table('produksi_details as pd')
            ->select(
                DB::raw("'Produksi' as sumber_data"),
                'p.no_produksi as no_dokumen',
                'p.tanggal',
                'pd.type as tipe_pergerakan',
                DB::raw("'N/A' as user_name"), // Ganti user_name dengan nilai default
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
            ->leftJoin('jenis_material as jm', 'b.jenis_material_id', '=', 'jm.id')
            ->leftJoin('unit_satuan as us', 'pd.unit_satuan_id', '=', 'us.id')
            ->where('p.company_id', $companyId)
            ->whereBetween('p.tanggal', [$startDate, $endDate]);

        if (!empty($this->jenisMaterialId)) {
            $produksiQuery->where('b.jenis_material_id', $this->jenisMaterialId);
        }
        if (!empty($this->tipeBarang)) {
            $produksiQuery->where('b.tipe_barang', $this->tipeBarang);
        }

        // Gabungkan Query dengan UNION ALL
        $transaksiQuery->unionAll($produksiQuery);

        // Eksekusi dan Urutkan hasil gabungan
        $results = DB::query()->fromSub($transaksiQuery, 'combined_data')
            ->orderBy('tanggal', 'asc')
            ->orderBy('no_dokumen', 'asc')
            ->orderBy('tipe_pergerakan', 'desc')
            ->orderBy('kode_barang', 'asc')
            ->get();

        // Penanganan data kosong
        if ($results->isEmpty()) {
            return collect([]);
        }

        return $results;
    }

    public function headings(): array
    {
        return [
            'Sumber Data',
            'No Dokumen',
            'Tanggal',
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
        $formattedQty = formatAngkaRibuan ($row->qty);
        return [
            $row->sumber_data ?? 'N/A',
            $row->no_dokumen ?? 'N/A',
            $row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y H:i') : '-',
            $row->tipe_pergerakan ?? '-',
            $row->user_name ?? '-',
            $row->kode_barang ?? '-',
            $row->nama_barang ?? '-',
            $row->tipe_barang ?? '-',
            $row->deskripsi_barang ?? '-',
            $row->nama_jenis_material ?? '-',
            $row->nama_unit_satuan ?? '-',
            $formattedQty,
        ];
    }
}
