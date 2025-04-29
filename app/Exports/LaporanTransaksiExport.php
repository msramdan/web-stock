<?php

namespace App\Exports;

// Pastikan semua model yang dibutuhkan di-import
use App\Models\TransaksiDetail;
use App\Models\Transaksi;
use App\Models\Barang;
use App\Models\JenisMaterial;
use App\Models\UnitSatuan;
use App\Models\User; // Tambahkan use User jika belum ada

use Illuminate\Support\Facades\DB; // Tidak perlu jika pakai Eloquent
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
    protected $companyId; // Tambahkan properti untuk companyId

    public function __construct(string $tanggalMulai, string $tanggalSelesai, $jenisMaterialId)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->jenisMaterialId = $jenisMaterialId;
        $this->companyId = session('sessionCompany'); // Ambil companyId saat export dibuat
    }

    /**
     * Mengambil data transaksi berdasarkan filter.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Menggunakan Eloquent untuk query yang lebih rapi
        $query = TransaksiDetail::with([
            'transaksi' => function ($q) { // Eager load transaksi
                $q->with('user:id,name'); // Eager load user dari transaksi
            },
            'barang' => function ($q) { // Eager load barang
                $q->with(['jenisMaterial', 'unitSatuan']); // Eager load relasi dari barang
            }
        ])
            ->whereHas('transaksi', function ($q) { // Filter berdasarkan transaksi
                $q->where('company_id', $this->companyId) // Filter Company ID dari transaksi
                    ->whereBetween('tanggal', [
                        Carbon::parse($this->tanggalMulai)->startOfDay(),
                        Carbon::parse($this->tanggalSelesai)->endOfDay()
                    ]);
            });

        // Filter berdasarkan jenis material (jika ada)
        if (!empty($this->jenisMaterialId)) {
            $query->whereHas('barang', function ($q) {
                $q->where('jenis_material_id', $this->jenisMaterialId);
            });
        }

        // Urutkan hasil
        $query->orderBy(
            Transaksi::select('tanggal') // Order by tanggal dari tabel transaksi
                ->whereColumn('transaksi.id', 'transaksi_detail.transaksi_id')
                ->orderBy('tanggal', 'asc')
                ->limit(1)
        )
            ->orderBy(
                Transaksi::select('no_surat') // Lalu order by no_surat
                    ->whereColumn('transaksi.id', 'transaksi_detail.transaksi_id')
                    ->orderBy('no_surat', 'asc')
                    ->limit(1)
            )
            ->orderBy(
                Barang::select('kode_barang') // Lalu order by kode barang
                    ->whereColumn('barang.id', 'transaksi_detail.barang_id')
                    ->orderBy('kode_barang', 'asc')
                    ->limit(1)
            );


        return $query->get();
    }

    /**
     * Mendefinisikan header kolom untuk file Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'No Surat',
            'Tanggal Transaksi',
            'Tipe Transaksi', // Ganti Tipe -> Tipe Transaksi
            'User',
            'Kode Barang',
            'Nama Barang', // Tambahkan Nama Barang
            'Tipe Barang', // <-- TAMBAHKAN HEADER BARU
            'Deskripsi Barang',
            'Jenis Material',
            'Unit Satuan',
            'Qty',
        ];
    }

    /**
     * Memetakan data dari collection ke array untuk setiap baris Excel.
     *
     * @param mixed $detail instance dari TransaksiDetail dengan relasi eager loaded
     * @return array
     */
    public function map($detail): array
    {
        // Akses data melalui relasi yang sudah di-load
        return [
            $detail->transaksi?->no_surat ?? '-', // Akses relasi transaksi
            $detail->transaksi?->tanggal ? Carbon::parse($detail->transaksi->tanggal)->format('d/m/Y H:i') : '-',
            $detail->transaksi?->type ?? '-',
            $detail->transaksi?->user?->name ?? '-', // Akses relasi user dari transaksi
            $detail->barang?->kode_barang ?? '-', // Akses relasi barang
            $detail->barang?->nama_barang ?? '-', // Akses relasi barang
            $detail->barang?->tipe_barang ?? '-', // <-- AMBIL DATA TIPE BARANG
            $detail->barang?->deskripsi_barang ?? '-',
            $detail->barang?->jenisMaterial?->nama_jenis_material ?? '-', // Akses relasi dari barang
            $detail->barang?->unitSatuan?->nama_unit_satuan ?? '-', // Akses relasi dari barang
            $detail->qty,
        ];
    }
}
