<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Transaksi Masuk</title>
    <style>
        /* --- Base Page Setup --- */
        @page {
            /* A4 Portrait: Lebar 210mm, Tinggi 297mm */
            /* Margin: Atas 25mm, Kanan 20mm, Bawah 25mm, Kiri 20mm (Sama seperti Barang) */
            margin: 25mm 20mm 25mm 20mm;
            size: a4 portrait;
            /* Pastikan portrait */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            /* Kembalikan ke font size Barang */
            line-height: 1.4;
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
            padding: 3px 5px;
            /* Kembalikan padding Barang */
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            border: 1px solid #333;
        }

        /* --- Header Styling (Sama seperti Barang) --- */
        .table-header {
            border: none;
            margin-bottom: 5px;
            font-size: 13px;
            width: 100%;
        }

        .table-header td {
            border: none;
            vertical-align: middle;
            padding: 0 5px;
        }

        .logo-cell {
            width: 80px;
            text-align: right;
            padding-right: 10px;
        }

        .logo {
            max-width: 100px;
            max-height: 60px;
            height: auto;
            display: block;
        }

        .header-text-cell {
            text-align: center;
            font-size: 14px;
            vertical-align: middle;
        }

        .header-instansi {
            font-size: 15px;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .header-address {
            font-size: 11px;
            /* Ukuran font alamat */
            color: #333;
            line-height: 1.3;
            margin-top: 2px;
        }

        .header-contact {
            font-size: 11px;
            /* Ukuran font kontak */
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

        /* Info Pembuat dan Tanggal Cetak */
        .creator-info {
            font-size: 9px;
            color: #555;
            margin-bottom: 15px;
            font-family: 'Times New Roman', Times, serif;
            text-align: right;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.3;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Styling untuk tabel data */
        .table-data {
            width: 100%;
            border: 1px solid #333;
            margin-bottom: 40px;
            /* Jarak untuk TTD */
        }

        .table-data th {
            background-color: #EAEAEA;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            /* Font header tabel */
            padding: 4px 5px;
            /* Padding header */
            border: 1px solid #333;
        }

        .table-data td {
            font-size: 10px;
            /* Font isi tabel */
            vertical-align: middle;
            padding: 4px 5px;
            /* Padding isi */
            border: 1px solid #333;
        }

        /* Penyesuaian lebar kolom (Contoh untuk 5 kolom di Portrait) */
        .table-data th:nth-child(1),
        .table-data td:nth-child(1) {
            width: 5%;
            text-align: center;
        }

        /* No */
        .table-data th:nth-child(2),
        .table-data td:nth-child(2) {
            width: 25%;
        }

        /* No Surat */
        .table-data th:nth-child(3),
        .table-data td:nth-child(3) {
            width: 20%;
            text-align: center;
        }

        /* Tanggal */
        .table-data th:nth-child(4),
        .table-data td:nth-child(4) {
            width: 10%;
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

        /* Styling untuk bagian tanda tangan (Sama seperti Barang) */
        .signature-section {
            page-break-inside: avoid;
            margin-top: 30px;
            float: right;
            width: 200px;
            /* Sesuaikan lebar jika perlu */
            text-align: center;
            font-size: 12px;
            /* Samakan font size TTD */
            font-family: 'Times New Roman', Times, serif;
        }

        .signature-place-date {
            margin-bottom: 50px;
            /* Jarak lebih besar untuk TTD */
        }

        .signature-name {
            font-weight: bold;
            border-top: 1px solid #fff;
            padding-top: 2px;
            margin-top: 5px;
        }

        /* Garis bawah untuk tanda tangan manual */
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            /* Lebar garis (misal 80%) */
            margin: 0 auto 5px auto;
            /* Tengahkan dan beri margin bawah */
            display: block;
            height: 1px;
        }
    </style>
</head>

<body>
    {{-- Header Tabel --}}
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
    <div class="creator-info">Dicetak oleh: {{ $namaPembuat }} pada {{ $tanggalCetak }}</div>

    {{-- Judul Dokumen --}}
    <div class="doc-title">
        LAPORAN TRANSAKSI MASUK
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
                {{-- Kolom User Dihapus --}}
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $index => $transaksi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $transaksi->no_surat ?? '-' }}</td>
                    <td class="text-center">
                        {{ $transaksi->tanggal ? \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="text-center">{{ $transaksi->type ?? '-' }}</td>
                    <td>{{ $transaksi->keterangan ?? '-' }}</td>
                    {{-- Kolom data User Dihapus --}}
                </tr>
            @empty
                <tr class="no-border">
                    <td colspan="5" class="text-center">Tidak ada data transaksi masuk yang dapat ditampilkan.</td>
                    {{-- Colspan jadi 5 --}}
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Bagian Tanda Tangan --}}
    <div class="signature-section">
        <div class="signature-place-date">Demak, {{ \Carbon\Carbon::parse($tanggalCetak)->translatedFormat('d F Y') }}
        </div>
        <span class="signature-line"></span> {{-- Tambahkan garis TTD --}}
        <div class="signature-name">{{ $namaPembuat }}</div>
    </div>

</body>

</html>
