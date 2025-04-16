<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Data Barang</title>
    <style>
        /* --- Base Page Setup --- */
        @page {
            margin: 25mm 20mm 25mm 20mm;
            /* Margin: Atas, Kanan, Bawah, Kiri */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
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
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        /* --- Header Styling --- */
        .table-header {
            border: none;
            margin-bottom: 5px;
            font-size: 13px;
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
            color: #333;
            line-height: 1.3;
            margin-top: 2px;
        }

        .header-contact {
            font-size: 11px;
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

        /* Styling untuk tabel data barang */
        .table-data {
            width: 100%;
            border: 1px solid #333;
            margin-bottom: 40px;
            /* Tambah margin untuk ruang tanda tangan */
        }

        .table-data th {
            background-color: #EAEAEA;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            padding: 4px 5px;
            border: 1px solid #333;
        }

        .table-data td {
            font-size: 10px;
            vertical-align: middle;
            padding: 4px 5px;
            border: 1px solid #333;
        }

        /* Penyesuaian lebar kolom */
        .table-data th:nth-child(1),
        .table-data td:nth-child(1) {
            width: 5%;
            text-align: center;
        }

        .table-data th:nth-child(2),
        .table-data td:nth-child(2) {
            width: 15%;
        }

        .table-data th:nth-child(3),
        .table-data td:nth-child(3) {
            width: 35%;
        }

        .table-data th:nth-child(4),
        .table-data td:nth-child(4) {
            width: 15%;
        }

        .table-data th:nth-child(5),
        .table-data td:nth-child(5) {
            width: 15%;
        }

        .table-data th:nth-child(6),
        .table-data td:nth-child(6) {
            width: 15%;
            text-align: center;
        }

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

        /* Styling untuk bagian tanda tangan */
        .signature-section {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
            font-family: 'Times New Roman', Times, serif;
        }

        .signature-place-date {
            margin-bottom: 5px;
        }

        .signature-space {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 150px;
            margin-bottom: 5px;
            vertical-align: bottom;
        }

        .signature-name {
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <table class="table-header">
        <tr>
            <td class="logo-cell">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="logo">
                @else
                    <div
                        style="width:80px; height:40px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:9px; color:#999;">
                        Logo
                    </div>
                @endif
            </td>
            <td class="header-text-cell">
                <div class="header-instansi">{{ $setting?->nama_perusahaan ?? 'NAMA PERUSAHAAN' }}</div>
                <div class="header-address">
                    {{ $setting?->alamat ?? 'Alamat Perusahaan' }}
                    @if ($setting?->no_telepon || $setting?->email)
                        <span class="header-contact">
                            @if ($setting?->no_telepon)
                                Telepon: {{ $setting->no_telepon }}
                            @endif
                            @if ($setting?->no_telepon && $setting?->email)
                                |
                            @endif
                            @if ($setting?->email)
                                Email: <span>{{ $setting->email }}</span>
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

    <!-- Info Cetak -->
    <div class="creator-info">Dicetak oleh: {{ $namaPembuat }} pada {{ $tanggalCetak }}</div>

    <!-- Judul Dokumen -->
    <div class="doc-title">LAPORAN DATA BARANG</div>

    <!-- Tabel Data Barang -->
    <table class="table-data">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Deskripsi Barang</th>
                <th>Jenis Material</th>
                <th>Unit Satuan</th>
                <th>Stok Barang</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $index => $barang)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $barang->kode_barang ?? '-' }}</td>
                    <td>{{ $barang->deskripsi_barang ?? '-' }}</td>
                    <td>{{ $barang->nama_jenis_material ?? '-' }}</td>
                    <td>{{ $barang->nama_unit_satuan ?? '-' }}</td>
                    <td class="text-center">{{ number_format($barang->stock_barang ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="no-border">
                    <td colspan="6" class="text-center">Tidak ada data barang yang dapat ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-place-date">Demak, {{ \Carbon\Carbon::parse($tanggalCetak)->translatedFormat('d F Y') }}
        </div>
        <div class="signature-space"> </div>
        <div class="signature-name">{{ $namaPembuat }}</div>
    </div>
</body>

</html>
