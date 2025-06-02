<table>
    <thead>
        {{-- Baris untuk Informasi Perusahaan --}}
        @if (isset($company) && $company)
            <tr>
                <th colspan="7" style="font-weight: bold; font-size: 14px; text-align: center;">
                    {{ strtoupper($company->nama_perusahaan) }}</th>
            </tr>
            <tr>
                <th colspan="7" style="text-align: center;">{{ $company->alamat ?: '-' }}</th>
            </tr>
            <tr>
                <th colspan="7" style="text-align: center;">Telp: {{ $company->no_telepon ?: '-' }} | Email:
                    {{ $company->email ?: '-' }}</th>
            </tr>
            <tr>
                <th colspan="7"></th>
            </tr> {{-- Baris kosong untuk spasi --}}
        @endif

        {{-- Baris untuk Judul Dokumen --}}
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 16px; text-align: center;">
                {{ strtoupper($title ?? 'DETAIL PERMINTAAN BARANG') }}</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr> {{-- Baris kosong untuk spasi --}}

        {{-- Baris untuk Informasi Permintaan --}}
        <tr>
            <td style="font-weight: bold;">No. Permintaan</td>
            <td colspan="2">: {{ $permintaan->no_permintaan_barang ?? '-' }}</td>
            <td style="font-weight: bold; text-align:right;">Tanggal Pengajuan</td>
            <td colspan="3">:
                {{ isset($permintaan->tgl_pengajuan) ? \Carbon\Carbon::parse($permintaan->tgl_pengajuan)->translatedFormat('d M Y H:i') : '-' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Supplier</td>
            <td colspan="6">: {{ $permintaan->nama_supplier ?? '-' }}</td>
        </tr>
        @if ($permintaan->nama_bank || $permintaan->account_name_supplier || $permintaan->account_number_supplier)
            <tr>
                <td style="font-weight: bold;">Bank</td>
                <td colspan="6">: {{ $permintaan->nama_bank ?: '-' }} / A.N:
                    {{ $permintaan->account_name_supplier ?: '-' }} / No.Rek:
                    {{ $permintaan->account_number_supplier ?: '-' }}</td>
            </tr>
        @endif
        @if ($permintaan->keterangan)
            <tr>
                <td style="font-weight: bold;">Keterangan</td>
                <td colspan="6">: {{ $permintaan->keterangan }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="7"></td>
        </tr> {{-- Baris kosong --}}

        {{-- Header Tabel Detail Barang --}}
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">No.</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Kode Barang</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Nama Barang</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Stok Terakhir</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Jumlah</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Satuan</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Harga/Satuan (Rp)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Total Harga (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @if (isset($permintaan) && $permintaan->details->isNotEmpty())
            @foreach ($permintaan->details as $index => $detail)
                <tr>
                    <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #000;">{{ $detail->barang->kode_barang ?? 'N/A' }}</td>
                    <td style="border: 1px solid #000;">{{ $detail->barang->nama_barang ?? 'N/A' }}</td>
                    <td style="text-align: right; border: 1px solid #000;">
                        {{ formatAngkaDesimal($detail->stok_terakhir, 2) }}</td>
                    <td style="text-align: right; border: 1px solid #000;">
                        {{ formatAngkaDesimal($detail->jumlah_pesanan, 2) }}</td>
                    <td style="text-align: center; border: 1px solid #000;">{{ $detail->satuan }}</td>
                    <td style="text-align: right; border: 1px solid #000;">
                        {{ number_format((float) $detail->harga_per_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: right; border: 1px solid #000;">
                        {{ number_format((float) $detail->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="8" style="text-align: center; border: 1px solid #000;">Tidak ada detail barang.</td>
            </tr>
        @endif
    </tbody>
    @if (isset($permintaan))
        <tfoot>
            <tr>
                <td colspan="7" style="text-align: right; font-weight: bold; border-top: 1px solid #000;">Sub Total
                </td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000;">
                    {{ number_format((float) $permintaan->sub_total_pesanan, 0, ',', '.') }}</td>
            </tr>
            @if ($permintaan->include_ppn == 'yes' && $permintaan->nominal_ppn > 0)
                <tr>
                    <td colspan="7" style="text-align: right;">PPN (11%)</td>
                    <td style="text-align: right; border: 1px solid #000;">
                        {{ number_format((float) $permintaan->nominal_ppn, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="7" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000;">
                    {{ number_format((float) $permintaan->total_pesanan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    @endif
</table>
