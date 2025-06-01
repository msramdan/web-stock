<?php

namespace App\Exports;

use App\Models\Permintaan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermintaanBarangExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $companyId = Auth::user()->company_id ?? session('company_id');

        // Cek apakah ada ID spesifik untuk export satu item
        if ($this->request->filled('id_permintaan_specific')) {
            $query = Permintaan::where('id', $this->request->input('id_permintaan_specific'))
                ->where('company_id', $companyId) // Tetap pastikan company scope
                ->with('user');
        } else {
            // Query untuk list (jika tidak ada ID spesifik, berarti export daftar)
            $query = Permintaan::where('company_id', $companyId)
                ->with('user')
                ->select('permintaan.*') // select.* mungkin tidak perlu jika mapping lengkap
                ->orderBy('tgl_pengajuan', 'desc');

            // Terapkan filter lain dari request jika ada (untuk export daftar yang mungkin masih difilter di backend)
            // Filter UI sudah dihapus, tapi bisa ada filter lain dari query string jika diperlukan
            if ($this->request->filled('no_permintaan_param')) { // contoh parameter filter backend
                $query->where('no_permintaan_barang', 'like', '%' . $this->request->input('no_permintaan_param') . '%');
            }
            // Tambahkan filter lain jika perlu
        }

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
            number_format($permintaan->nominal_ppn, 0, ',', '.'),
            number_format($permintaan->sub_total_pesanan, 0, ',', '.'),
            number_format($permintaan->total_pesanan, 0, ',', '.'),
            $permintaan->user ? $permintaan->user->name : 'N/A',
            \Carbon\Carbon::parse($permintaan->created_at)->format('d-m-Y H:i'),
        ];
    }
}
