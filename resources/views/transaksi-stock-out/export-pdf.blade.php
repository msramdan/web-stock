<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Transaksi Keluar</title> {{-- Judul Diubah --}}
    <style>
        /* --- Base Page Setup (Potrait A4) --- */
        @page {
            margin: 25mm 20mm 25mm 20mm;
            /* Margin normal */
            size: a4 portrait;
            /* <-- Ubah ke portrait */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        th,
        td {
            padding: 3px 4px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            border: 1px solid #333;
        }

        .table-header {
            border: none;
            margin-bottom: 5px;
            font-size: 12px;
            width: 100%;
        }

        .table-header td {
            border: none;
            vertical-align: middle;
            padding: 0 5px;
        }

        .logo-cell {
            width: 70px;
            text-align: right;
            padding-right: 10px;
        }

        .logo {
            max-width: 90px;
            max-height: 50px;
            height: auto;
            display: block;
        }

        .header-text-cell {
            text-align: center;
            font-size: 13px;
            vertical-align: middle;
        }

        .header-instansi {
            font-size: 14px;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .header-address {
            font-size: 10px;
            color: #333;
            line-height: 1.3;
            margin-top: 2px;
        }

        .header-contact {
            font-size: 10px;
            display: block;
            margin-top: 2px;
        }

        .header-contact span {
            color: #333;
        }

        hr.header-line {
            border: none;
            border-top: 2px solid #000;
            margin: 5px 0 15px 0;
        }

        .creator-info {
            font-size: 8px;
            color: #555;
            margin-bottom: 15px;
            font-family: 'Times New Roman', Times, serif;
            text-align: right;
        }

        .doc-title {
            text-align: center;
            font-size: 13px;
            margin-bottom: 20px;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.3;
            font-weight: bold;
            text-transform: uppercase;
        }

        .table-data {
            width: 100%;
            border: 1px solid #333;
            margin-bottom: 20px;
        }

        .table-data th {
            background-color: #EAEAEA;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            padding: 3px 4px;
            border: 1px solid #333;
        }

        .table-data td {
            font-size: 9px;
            vertical-align: middle;
            padding: 3px 4px;
            border: 1px solid #333;
        }

        /* Lebar kolom untuk 5 kolom di landscape */
        .table-data th:nth-child(1),
        .table-data td:nth-child(1) {
            width: 4%;
            text-align: center;
        }

        /* No */
        .table-data th:nth-child(2),
        .table-data td:nth-child(2) {
            width: 18%;
        }

        /* No Surat */
        .table-data th:nth-child(3),
        .table-data td:nth-child(3) {
            width: 15%;
            text-align: center;
        }

        /* Tanggal */
        .table-data th:nth-child(4),
        .table-data td:nth-child(4) {
            width: 5%;
            text-align: center;
        }

        /* Type */
        .table-data th:nth-child(5),
        .table-data td:nth-child(5) {
            width: 40%;
        }

        /* Keterangan */
        /* Kolom User (ke-6) dihapus */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .no-border td {
            border: none;
            padding: 10px;
        }

        .signature-section {
            page-break-inside: avoid;
            margin-top: 30px;
            float: right;
            width: 200px;
            text-align: center;
            font-size: 11px;
            font-family: 'Times New Roman', Times, serif;
        }

        .signature-place-date {
            margin-bottom: 40px;
        }

        .signature-name {
            font-weight: bold;
            border-top: 1px solid #fff;
            padding-top: 2px;
            margin-top: 5px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
            display: block;
            height: 1px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <table class="table-header">
        <tr>
            <td class="logo-cell">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="logo">
                @else
                    <div
                        style="width:80px; height:40px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:9px; color:#999;">
                        Logo</div>
                @endif
            </td>
            <td class="header-text-cell">
                <div class="header-instansi">{{ $activeCompany?->nama_perusahaan ?? 'NAMA PERUSAHAAN' }}</div>
                <div class="header-address">
                    {{ $activeCompany?->alamat ?? 'Alamat Perusahaan' }}
                    @if ($activeCompany?->no_telepon || $activeCompany?->email)
                        <span class="header-contact">
                            @if ($activeCompany?->no_telepon)
                                Telepon: {{ $activeCompany->no_telepon }}
                            @endif
                            @if ($activeCompany?->no_telepon && $activeCompany?->email)
                                |
                            @endif
                            @if ($activeCompany?->email)
                                Email: <span>{{ $activeCompany->email }}</span>
                            @endif
                        </span>
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; padding: 0;">
                <hr class="header-line">
            </td>
        </tr>
    </table>

    {{-- Info Cetak --}}
    <div class="creator-info">Dicetak oleh: {{ $namaPembuat }} pada {{ formatTanggalIndonesia(date('Y-m-d H:i:s')) }}</div>

    {{-- Judul Dokumen --}}
    <div class="doc-title">
        LAPORAN TRANSAKSI KELUAR {{-- Judul Diubah --}}
    </div>

    {{-- Tabel Data Transaksi --}}
    <table class="table-data">
        <thead>
            <tr>
                <th>No</th>
                <th>No Surat</th>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Keterangan</th>
                {{-- Kolom User dihapus --}}
            </tr>
        </thead>
        <tbody>
            {{-- Loop data $transaksis dari TransaksiStockOutController --}}
            @forelse($transaksis as $index => $transaksi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $transaksi->no_surat ?? '-' }}</td>
                    <td class="text-center">
                        {{ formatTanggalIndonesia ($transaksi->tanggal) }}
                    </td>
                    <td class="text-center">{{ $transaksi->type ?? '-' }}</td>
                    <td>{{ $transaksi->keterangan ?? '-' }}</td>
                    {{-- Kolom data User dihapus --}}
                </tr>
            @empty
                <tr class="no-border">
                    <td colspan="5" class="text-center">Tidak ada data transaksi keluar yang dapat ditampilkan.</td>
                    {{-- Colspan 5 --}}
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Bagian Tanda Tangan --}}
    <div class="signature-section">
        <div class="signature-place-date">Demak, {{ formatTanggalIndonesia(date('Y-m-d H:i:s')) }}
        </div>
        <span class="signature-line"></span>
        <div class="signature-name">{{ $namaPembuat }}</div>
    </div>

</body>

</html>
