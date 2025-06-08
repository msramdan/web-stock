<?php

namespace App\Exports;

use App\Models\Permintaan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PermintaanBarangExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Format #,##0
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Sub Total
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        // Ambil company_id_filter yang di-pass dari controller
        $companyId = $this->request->input('company_id_filter', session('sessionCompany'));

        if (!$companyId) {
            return Permintaan::query()->whereRaw('1 = 0'); // Kembalikan query kosong
        }

        // Query untuk list (jika tidak ada ID spesifik, berarti export daftar)
        $query = Permintaan::where('company_id', $companyId)
            ->with('user:id,name') // Eager load user untuk daftar
            ->orderBy('tgl_pengajuan', 'desc');

        // Jika ada filter lain yang ingin diterapkan dari $this->request, tambahkan di sini
        // Misalnya, jika Anda punya filter tanggal di request:
        // if ($this->request->filled('tanggal_mulai') && $this->request->filled('tanggal_selesai')) {
        //    $query->whereBetween('tgl_pengajuan', [$this->request->input('tanggal_mulai'), $this->request->input('tanggal_selesai')]);
        // }

        return $query;
    }

    // Method headings() dan map() tetap sama seperti sebelumnya
    public function headings(): array
    {
        return [
            'No. Permintaan',
            'Tanggal Pengajuan',
            'Nama Supplier',
            'Nama Bank',
            'Nama Akun Supplier',
            'No. Rekening Supplier',
            'Keterangan',
            'Include PPN?',
            'Nominal PPN (Rp)',
            'Sub Total Pesanan (Rp)',
            'Total Pesanan (Rp)',
            'User Penginput',
            'Tanggal Dibuat',
        ];
    }

    public function map($permintaan): array
    {
        return [
            $permintaan->no_permintaan_barang,
            \Carbon\Carbon::parse($permintaan->tgl_pengajuan)->format('d-m-Y H:i'),
            $permintaan->nama_supplier,
            $permintaan->nama_bank,
            $permintaan->account_name_supplier,
            $permintaan->account_number_supplier,
            $permintaan->keterangan,
            $permintaan->include_ppn == 'yes' ? 'Ya' : 'Tidak',
            $permintaan->nominal_ppn,
            $permintaan->sub_total_pesanan,
            $permintaan->total_pesanan,
            $permintaan->user ? $permintaan->user->name : 'N/A',
            \Carbon\Carbon::parse($permintaan->created_at)->format('d-m-Y H:i'),
        ];
    }
}
