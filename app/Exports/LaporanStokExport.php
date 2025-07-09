<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LaporanStokExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $tanggalMulai;
    protected $tanggalSelesai;
    protected $jenisMaterialId;
    protected $tipeBarang;
    protected $barangId;
    protected $companyId;
    protected $laporanData = [];
    protected $dateHeaders = [];

    public function __construct($tanggalMulai, $tanggalSelesai, $jenisMaterialId, $tipeBarang, $barangId,)
    {
        $this->tanggalMulai = Carbon::parse($tanggalMulai)->startOfDay();
        $this->tanggalSelesai = Carbon::parse($tanggalSelesai)->endOfDay();
        $this->jenisMaterialId = $jenisMaterialId;
        $this->tipeBarang = $tipeBarang;
        $this->barangId = $barangId;
        $this->companyId = session('sessionCompany');
        $this->laporanData = [];
    }

    public function array(): array
    {
        // Jika data sudah di-generate, kembalikan saja. Mencegah kalkulasi ganda.
        if (!empty($this->laporanData)) {
            return $this->laporanData;
        }

        // 1. Dapatkan daftar barang yang relevan
        $barangs = $this->getFilteredBarang();
        if ($barangs->isEmpty()) {
            return [];
        }
        $barangIds = $barangs->pluck('id');

        // 2. Dapatkan semua pergerakan stok
        $movements = $this->getAllMovements($barangIds);

        // 3. Siapkan rentang tanggal
        $period = CarbonPeriod::create($this->tanggalMulai, $this->tanggalSelesai);

        $dataForExport = [];
        foreach ($barangs as $barang) {
            $stokAwal = $this->calculateInitialStock($barang->id);

            $rowData = [
                'nama_barang' => $barang->nama_barang,
                'stok_awal' => $stokAwal,
            ];

            $stokBerjalan = $stokAwal;
            foreach ($period as $date) {
                $formattedDate = $date->format('d/m/Y');
                $pergerakanHariIni = $movements[$barang->id][$formattedDate] ?? 0;

                // --- PERUBAHAN 1: Ganti nilai kosong dengan '-' ---
                $rowData[$formattedDate] = $pergerakanHariIni == 0 ? '-' : $pergerakanHariIni;

                $stokBerjalan += $pergerakanHariIni;
            }

            $rowData['stok_akhir'] = $stokBerjalan;
            $dataForExport[] = $rowData;
        }

        // Simpan data ke properti kelas agar bisa diakses oleh method `styles`
        $this->laporanData = $dataForExport;

        return $this->laporanData;
    }

    public function headings(): array
    {
        $headers = ['Nama Barang', 'Stok Tgl ' . $this->tanggalMulai->format('d-m-Y')];
        $period = CarbonPeriod::create($this->tanggalMulai, $this->tanggalSelesai);
        foreach ($period as $date) {
            $headers[] = $date->format('d/m/Y');
        }
        $headers[] = 'Stok Akhir ' . $this->tanggalSelesai->format('d-m-Y');
        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header (baris 1)
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFDEEBF7'], // Warna biru muda
            ],
        ]);

        // --- PERUBAHAN 2: Logika Pewarnaan Sel ---
        // Panggil array() sekali lagi untuk memastikan $this->laporanData terisi
        $data = $this->array();
        $startColumnIndex = 2; // Kolom pergerakan dimulai dari kolom ke-3 (index 'C')

        foreach ($data as $rowIndex => $rowData) {
            $currentRow = $rowIndex + 2; // Data dimulai dari baris 2 di Excel
            $currentColumnIndex = $startColumnIndex;

            foreach ($rowData as $key => $value) {
                // Lewati kolom 'nama_barang', 'stok_awal', dan 'stok_akhir'
                if ($key === 'nama_barang' || $key === 'stok_awal' || $key === 'stok_akhir') {
                    continue;
                }

                $currentColumnIndex++;
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColumnIndex) . $currentRow;

                if (is_numeric($value) && $value != 0) {
                    if ($value > 0) { // Stok Masuk
                        $sheet->getStyle($cellCoordinate)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6EFCE']], // Hijau
                            'font' => ['color' => ['argb' => 'FF006100']] // Font hijau tua
                        ]);
                    } else { // Stok Keluar
                        $sheet->getStyle($cellCoordinate)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC7CE']], // Merah muda
                            'font' => ['color' => ['argb' => 'FF9C0006']] // Font merah tua
                        ]);
                    }
                }
            }
        }
    }

    private function getFilteredBarang()
    {
        $query = DB::table('barang')->select('id', 'nama_barang')->where('company_id', $this->companyId);
        if ($this->jenisMaterialId) $query->where('jenis_material_id', $this->jenisMaterialId);
        if ($this->tipeBarang) $query->where('tipe_barang', $this->tipeBarang);
        if ($this->barangId) $query->where('id', $this->barangId);
        return $query->orderBy('nama_barang')->get();
    }

    private function calculateInitialStock($barangId)
    {
        // Stok dari transaksi sebelum tanggal mulai
        $stokAwalTransaksi = DB::table('transaksi_detail as td')
            ->join('transaksi as t', 'td.transaksi_id', '=', 't.id')
            ->where('td.barang_id', $barangId)
            ->where('t.company_id', $this->companyId)
            ->where('t.tanggal', '<', $this->tanggalMulai)
            ->selectRaw("SUM(CASE WHEN t.type = 'In' THEN td.qty ELSE -td.qty END) as total")
            ->value('total') ?? 0;

        // Stok dari produksi sebelum tanggal mulai
        $stokAwalProduksi = DB::table('produksi_details as pd')
            ->join('produksi as p', 'pd.produksi_id', '=', 'p.id')
            ->where('pd.barang_id', $barangId)
            ->where('p.company_id', $this->companyId)
            ->where('p.tanggal', '<', $this->tanggalMulai)
            ->selectRaw("SUM(CASE WHEN pd.type = 'In' THEN pd.qty_total_diperlukan ELSE -pd.qty_total_diperlukan END) as total")
            ->value('total') ?? 0;

        return $stokAwalTransaksi + $stokAwalProduksi;
    }

    private function getAllMovements($barangIds)
    {
        $transaksi = DB::table('transaksi_detail as td')
            ->join('transaksi as t', 'td.transaksi_id', '=', 't.id')
            ->whereIn('td.barang_id', $barangIds)
            ->whereBetween('t.tanggal', [$this->tanggalMulai, $this->tanggalSelesai])
            ->select('td.barang_id', DB::raw("DATE_FORMAT(t.tanggal, '%d/%m/%Y') as tanggal_formatted"), DB::raw("SUM(CASE WHEN t.type = 'In' THEN td.qty ELSE -td.qty END) as pergerakan"))
            ->groupBy('td.barang_id', 'tanggal_formatted')
            ->get();

        $produksi = DB::table('produksi_details as pd')
            ->join('produksi as p', 'pd.produksi_id', '=', 'p.id')
            ->whereIn('pd.barang_id', $barangIds)
            ->whereBetween('p.tanggal', [$this->tanggalMulai, $this->tanggalSelesai])
            ->select('pd.barang_id', DB::raw("DATE_FORMAT(p.tanggal, '%d/%m/%Y') as tanggal_formatted"), DB::raw("SUM(CASE WHEN pd.type = 'In' THEN pd.qty_total_diperlukan ELSE -pd.qty_total_diperlukan END) as pergerakan"))
            ->groupBy('pd.barang_id', 'tanggal_formatted')
            ->get();

        $allMovements = collect($transaksi)->merge($produksi);

        // Kelompokkan hasil akhir
        $formattedMovements = [];
        foreach ($allMovements as $move) {
            if (!isset($formattedMovements[$move->barang_id])) {
                $formattedMovements[$move->barang_id] = [];
            }
            if (!isset($formattedMovements[$move->barang_id][$move->tanggal_formatted])) {
                $formattedMovements[$move->barang_id][$move->tanggal_formatted] = 0;
            }
            $formattedMovements[$move->barang_id][$move->tanggal_formatted] += $move->pergerakan;
        }

        return $formattedMovements;
    }
}
