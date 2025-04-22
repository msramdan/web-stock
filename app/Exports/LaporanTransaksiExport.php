<?php

namespace App\Exports;

use App\Models\TransaksiDetail;
use App\Models\JenisMaterial;
use App\Models\Transaksi;
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

    public function __construct(string $tanggalMulai, string $tanggalSelesai, $jenisMaterialId)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->jenisMaterialId = $jenisMaterialId;
    }

    /**
     * Mengambil data transaksi berdasarkan filter.
     * Menggunakan nama tabel 'transaksi' (singular) yang benar.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil nama tabel yang benar dari setiap model yang terlibat
        $transaksiTableName = (new \App\Models\Transaksi)->getTable(); // 'transaksi'
        $barangTableName = (new \App\Models\Barang)->getTable(); // Seharusnya 'barang'
        $jenisMaterialTableName = (new \App\Models\JenisMaterial)->getTable(); // Misal: 'jenis_materials'
        $unitSatuanTableName = (new \App\Models\UnitSatuan)->getTable(); // Misal: 'unit_satuans'
        $userTableName = (new \App\Models\User)->getTable(); // 'users'
        $transaksiDetailTableName = (new \App\Models\TransaksiDetail)->getTable(); // 'transaksi_detail'

        $query = TransaksiDetail::select(
            // Gunakan nama tabel dinamis di select
            $transaksiTableName . '.no_surat',
            $transaksiTableName . '.tanggal',
            $transaksiTableName . '.type',
            $userTableName . '.name as user_name',
            $barangTableName . '.kode_barang',
            $barangTableName . '.deskripsi_barang',
            $jenisMaterialTableName . '.nama_jenis_material',
            $unitSatuanTableName . '.nama_unit_satuan',
            $transaksiDetailTableName . '.qty' // Gunakan nama tabel detail
        )
            // Gunakan nama tabel dinamis di semua join
            ->join($transaksiTableName, $transaksiDetailTableName . '.transaksi_id', '=', $transaksiTableName . '.id')
            ->join($barangTableName, $transaksiDetailTableName . '.barang_id', '=', $barangTableName . '.id')
            ->join($jenisMaterialTableName, $barangTableName . '.jenis_material_id', '=', $jenisMaterialTableName . '.id')
            ->join($unitSatuanTableName, $barangTableName . '.unit_satuan_id', '=', $unitSatuanTableName . '.id')
            ->join($userTableName, $transaksiTableName . '.user_id', '=', $userTableName . '.id')
            // Gunakan nama tabel dinamis di whereBetween
            ->whereBetween($transaksiTableName . '.tanggal', [
                Carbon::parse($this->tanggalMulai)->startOfDay(),
                Carbon::parse($this->tanggalSelesai)->endOfDay()
            ]);

        // Gunakan nama tabel dinamis di where (filter material)
        if (!empty($this->jenisMaterialId)) {
            $query->where($barangTableName . '.jenis_material_id', $this->jenisMaterialId);
        }

        // Gunakan nama tabel dinamis di orderBy
        $query->orderBy($transaksiTableName . '.tanggal', 'asc')
            ->orderBy($transaksiTableName . '.no_surat', 'asc')
            ->orderBy($barangTableName . '.kode_barang', 'asc');

        return $query->get();
    }

    // Method headings() dan map() biarkan seperti sebelumnya (sudah benar)
    public function headings(): array
    {
        return [
            'No Surat',
            'Tanggal Transaksi',
            'Tipe',
            'User',
            'Kode Barang',
            'Deskripsi Barang',
            'Jenis Material',
            'Unit Satuan',
            'Qty',
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->no_surat,
            Carbon::parse($detail->tanggal)->format('d/m/Y H:i'),
            $detail->type,
            $detail->user_name,
            $detail->kode_barang,
            $detail->deskripsi_barang,
            $detail->nama_jenis_material,
            $detail->nama_unit_satuan,
            $detail->qty,
        ];
    }
}
