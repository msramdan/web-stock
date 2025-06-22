<?php

namespace App\Exports;

use App\Models\Produksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProduksiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil data produksi hanya dari company yang aktif di session
        return Produksi::where('company_id', session('sessionCompany'))
            ->with(['barangJadi:id,nama_barang', 'user:id,name', 'bom:id,kode_bom'])
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Definisikan header kolom untuk file Excel
        return [
            'No. Produksi',
            'Tanggal',
            'Produk Jadi',
            'Kode BoM',
            'Batch',
            'Total Biaya Produksi',
            'HPP / Unit',
            'Dibuat Oleh',
            'Keterangan',
        ];
    }

    /**
     * @param mixed $produksi
     * @return array
     */
    public function map($produksi): array
    {
        // Petakan data dari collection ke setiap kolom di Excel
        return [
            $produksi->no_produksi,
            \Carbon\Carbon::parse($produksi->tanggal)->format('d-m-Y H:i'),
            $produksi->barangJadi->nama_barang ?? '-',
            $produksi->bom->kode_bom ?? '-',
            $produksi->batch,
            $produksi->total_biaya,
            $produksi->harga_satuan_jadi,
            $produksi->user->name ?? '-',
            $produksi->keterangan,
        ];
    }
}
