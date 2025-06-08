<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Form Permintaan Barang' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 15px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .logo {
            width: 150px;
            height: auto;
        }

        .header-table .header-text-cell {
            text-align: center;
            vertical-align: middle;
        }

        .header-table .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .header-table .company-address {
            font-size: 10px;
            margin: 2px 0;
        }

        .header-table .company-contact {
            font-size: 10px;
            margin: 0;
        }

        hr.header-line {
            border: none;
            border-top: 1px solid #000;
            margin: 0 0 15px 0;
        }

        .form-info {
            margin-bottom: 20px;
        }

        .form-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .form-info td {
            padding: 3px 5px;
            font-size: 10px;
        }

        .form-info .label {
            font-weight: bold;
            width: 150px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
            font-size: 9px;
        }

        .detail-table th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }

        .detail-table td.text-end {
            text-align: right;
        }

        .detail-table td.text-center {
            text-align: center;
        }

        .detail-table .no {
            width: 5%;
        }

        .detail-table .barang {
            width: 30%;
        }

        .detail-table .qty {
            width: 10%;
        }

        .detail-table .satuan {
            width: 10%;
        }

        .detail-table .harga {
            width: 15%;
        }

        .detail-table .total {
            width: 15%;
        }

        /* MODIFIKASI BAGIAN FOOTER DAN SIGNATURES */
        .footer {
            width: 100%;
            margin-top: 50px;
            /* Jarak dari tabel detail ke tanda tangan */
            page-break-inside: avoid;
            /* Mencegah page break di tengah blok tanda tangan */
            position: relative;
            /* Untuk positioning jika diperlukan nanti */
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatures-table td {
            width: 50%;
            /* Setiap kolom mengambil setengah lebar */
            text-align: center;
            vertical-align: top;
            /* Konten di atas */
            padding-top: 10px;
            /* Jarak atas untuk label */
            border: none;
            /* Tidak ada border untuk sel tabel tanda tangan */
        }

        .signature-block {
            display: inline-block;
            /* Agar bisa diatur width dan margin */
            width: 200px;
            /* Lebar spesifik untuk blok tanda tangan, sesuaikan jika perlu */
            /* margin: 0 auto; */
            /* Tidak perlu auto jika td sudah 50% dan text-align center */
        }

        .signature-block .signature-label {
            margin-bottom: 40px;
            /* Jarak antara label (Pemohon) dan garis tanda tangan */
            font-size: 12px;
        }

        .signature-block .signature-space {
            display: block;
            width: 100%;
            /* Garis tanda tangan selebar bloknya */
            height: 1px;
            /* Garis tipis */
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            /* Jarak dari garis ke nama */
        }

        .signature-block .name {
            font-weight: bold;
            font-size: 10px;
        }

        .signature-block .title-org {
            font-size: 10px;
            margin-top: 2px;
        }

        /* AKHIR MODIFIKASI FOOTER DAN SIGNATURES */

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-cell" style="width: 20%; text-align: right; padding-right: 10px;">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" class="logo">
                    @endif
                </td>
                <td class="header-text-cell" style="width: 60%;">
                    <div class="company-name">{{ $company?->nama_perusahaan ?? 'NAMA PERUSAHAAN' }}</div>
                    <p class="company-address">{{ $company?->alamat ?? 'Alamat Perusahaan' }}</p>
                    <p class="company-contact">
                        Telp: {{ $company?->no_telepon ?? '-' }} | Email: {{ $company?->email ?? '-' }}
                    </p>
                </td>
                <td style="width: 20%;"></td>
            </tr>
        </table>
        <hr class="header-line">

        <div class="header">
            <h1>{{ $title ?? 'Form Permintaan Barang' }}</h1>
        </div>

        <div class="form-info">
            <table>
                <tr>
                    <td class="label">No. Permintaan</td>
                    <td>: {{ $permintaan->no_permintaan_barang ?? ($no_permintaan_barang ?? '_________________') }}</td>
                    <td class="label" style="text-align:right; width: auto;">Tanggal Pengajuan</td>
                    <td style="width: 150px;">:
                        {{ isset($permintaan->tgl_pengajuan) ? \Carbon\Carbon::parse($permintaan->tgl_pengajuan)->format('d M Y') : $tgl_pengajuan ?? '___ / ___ / ______' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Supplier</td>
                    <td colspan="3">: {{ $permintaan->nama_supplier ?? ($nama_supplier ?? '_________________') }}
                    </td>
                </tr>
                @if (isset($permintaan) &&
                        ($permintaan->nama_bank || $permintaan->account_name_supplier || $permintaan->account_number_supplier))
                    <tr>
                        <td class="label">Bank</td>
                        <td colspan="3">: {{ $permintaan->nama_bank ?: '-' }} / A.N:
                            {{ $permintaan->account_name_supplier ?: '-' }} / No.Rek:
                            {{ $permintaan->account_number_supplier ?: '-' }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <table class="detail-table">
            <thead>
                <tr>
                    <th class="no">No.</th>
                    <th class="barang">Nama Barang / Spesifikasi</th>
                    @if (isset($permintaan) && $permintaan->details->isNotEmpty())
                        <th class="qty">Stok Terakhir</th>
                    @endif
                    <th class="qty">Jumlah</th>
                    <th class="satuan">Satuan</th>
                    <th class="harga">Harga/Satuan (Rp)</th>
                    <th class="total">Total Harga (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($permintaan) && $permintaan->details->isNotEmpty())
                    @foreach ($permintaan->details as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $detail->barang->nama_barang ?? 'N/A' }}
                                <br><small>({{ $detail->barang->kode_barang ?? 'N/A' }})</small>
                            </td>
                            <td class="text-end">
                                {{ number_format($detail->stok_terakhir, 2, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($detail->jumlah_pesanan, 2, ',', '.') }}
                            </td>
                            <td class="text-center">{{ $detail->satuan }}</td>
                            <td class="text-end">{{ number_format($detail->harga_per_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    @php $colspanDetail = isset($permintaan) && $permintaan->details->isNotEmpty() ? 7 : 6; @endphp
                    @for ($i = 0; $i < 15; $i++)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>&nbsp;</td>
                            @if (isset($permintaan) && $permintaan->details->isNotEmpty())
                                <td>&nbsp;</td>
                            @endif
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-end">&nbsp;</td>
                            <td class="text-end">&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
            @if (isset($permintaan))
                @php
                    $colspanSummary = isset($permintaan->details) && $permintaan->details->isNotEmpty() ? 6 : 5;
                @endphp
                <tfoot>
                    <tr>
                        <td colspan="{{ $colspanSummary }}" class="text-end" style="border:none; font-weight:bold;">Sub
                            Total</td>
                        <td class="text-end" style="font-weight:bold;">
                            {{ number_format($permintaan->sub_total_pesanan, 0, ',', '.') }}</td>
                    </tr>
                    @if ($permintaan->include_ppn == 'yes' && (float) $permintaan->nominal_ppn > 0)
                        <tr>
                            <td colspan="{{ $colspanSummary }}" class="text-end" style="border:none;">PPN (11%)</td>
                            <td class="text-end">{{ number_format($permintaan->nominal_ppn, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="{{ $colspanSummary }}" class="text-end"
                            style="border:none; font-weight:bold; font-size: 11px;">TOTAL</td>
                        <td class="text-end" style="font-weight:bold; font-size: 11px;">
                            {{ number_format($permintaan->total_pesanan, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
        <div class="clearfix"></div>

        @if (isset($permintaan) && !empty($permintaan->keterangan))
            <div style="margin-top: 10px; font-size:10px;">
                <strong>Keterangan:</strong><br>
                {{ nl2br(e($permintaan->keterangan)) }}
            </div>
        @endif

        {{-- MODIFIKASI BAGIAN TANDA TANGAN --}}
        <div class="footer">
            <table class="signatures-table">
                <tr>
                    <td>
                        <div class="signature-block">
                            <div class="signature-label">Pemohon,</div>
                            <div class="signature-space"></div>
                            <div class="name">({{ $permintaan->user->name ?? ($pemohon ?? '_________________') }})
                            </div>
                            {{-- <div class="title-org">(Jabatan Pemohon Jika Ada)</div> --}}
                        </div>
                    </td>
                    <td>
                        <div class="signature-block">
                            <div class="signature-label">Mengetahui,</div>
                            <div class="signature-space"></div>
                            <div class="name">({{ $permintaan->mengetahui ?? '_________________' }})</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
