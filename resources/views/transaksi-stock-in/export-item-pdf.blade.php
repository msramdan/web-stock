<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    {{-- Gunakan No Surat jika ada, jika tidak pakai ID --}}
    <title>Detail Transaksi Masuk - {{ $transaksi->no_surat ?? $transaksi->id }}</title>
    <style>
        /* --- Base Page Setup (Portrait A4) --- */
        @page {
            margin: 25mm 20mm 25mm 20mm;
            size: a4 portrait;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            /* Ukuran font sedikit lebih kecil agar detail muat */
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

        /* --- Header Styling (Sama) --- */
        /* Salin CSS Header dari export-pdf.blade.php */
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

        /* --- Info Cetak & Judul --- */
        .creator-info {
            font-size: 9px;
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

        /* --- Styling Detail Transaksi Header --- */
        .transaction-header-table {
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #666;
            /* Border tipis untuk header */
        }

        .transaction-header-table td {
            border: none;
            /* Hilangkan border dalam sel header */
            padding: 4px 6px;
            font-size: 11px;
            vertical-align: top;
            /* Rata atas */
        }

        .transaction-header-table td.label {
            font-weight: bold;
            width: 120px;
            /* Lebar kolom label */
            background-color: #f8f8f8;
            /* Warna latar label */
            border-right: 1px solid #ddd;
            /* Garis pemisah */
        }

        .attachment-link a {
            color: #0066cc;
            text-decoration: none;
        }

        .attachment-link a:hover {
            text-decoration: underline;
        }

        /* --- Styling Tabel Detail Item --- */
        .details-table th {
            background-color: #EAEAEA;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            padding: 4px 5px;
            border: 1px solid #333;
        }

        .details-table td {
            font-size: 10px;
            vertical-align: middle;
            padding: 4px 5px;
            border: 1px solid #333;
        }

        /* Lebar kolom detail (sesuaikan) */
        .details-table th:nth-child(1),
        .details-table td:nth-child(1) {
            width: 5%;
            text-align: center;
        }

        /* No */
        .details-table th:nth-child(2),
        .details-table td:nth-child(2) {
            width: 25%;
        }

        /* Kode Barang */
        .details-table th:nth-child(3),
        .details-table td:nth-child(3) {
            width: 25%;
        }

        /* Jenis Material */
        .details-table th:nth-child(4),
        .details-table td:nth-child(4) {
            width: 20%;
        }

        /* Unit Satuan */
        .details-table th:nth-child(5),
        .details-table td:nth-child(5) {
            width: 15%;
            text-align: center;
        }

        /* Qty */

        /* Styling Tanda Tangan (Sama) */
        .signature-section {
            page-break-inside: avoid;
            margin-top: 30px;
            float: right;
            width: 200px;
            text-align: center;
            font-size: 12px;
            font-family: 'Times New Roman', Times, serif;
        }

        .signature-place-date {
            margin-bottom: 50px;
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

        .text-center {
            text-align: center;
        }

        .no-border td {
            border: none;
            padding: 10px;
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
    <div class="creator-info">Dicetak oleh: {{ $namaPembuat }} pada {{ $tanggalCetak }}</div>

    {{-- Judul Dokumen --}}
    <div class="doc-title">
        DETAIL TRANSAKSI MASUK
    </div>

    {{-- Detail Header Transaksi --}}
    <table class="transaction-header-table">
        <tr>
            <td class="label">No Surat</td>
            <td>{{ $transaksi->no_surat ?? '-' }}</td>
            <td class="label">User</td>
            <td>{{ $transaksi->user_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td>{{ $transaksi->tanggal ? \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y H:i') : '-' }}</td>
            <td class="label">Tipe</td>
            <td>{{ $transaksi->type ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Keterangan</td>
            <td colspan="3">{{ $transaksi->keterangan ?? '-' }}</td>
        </tr>
        @if ($transaksi->attachment)
            <tr>
                <td class="label">Attachment</td>
                <td colspan="3" class="attachment-link">
                    {{-- Tampilkan nama file saja, karena link langsung mungkin tidak berfungsi di PDF --}}
                    {{ basename($transaksi->attachment) }}
                    {{-- Atau jika ingin link (mungkin perlu diatur agar bisa diklik di PDF viewer tertentu):
                 <a href="{{ asset('storage/' . $transaksi->attachment) }}" target="_blank">Lihat Lampiran</a>
                 --}}
                </td>
            </tr>
        @endif
    </table>

    {{-- Tabel Detail Item --}}
    <table class="details-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Jenis Material</th>
                <th>Unit Satuan</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->kode_barang ?? '-' }}</td>
                    <td>{{ $detail->nama_jenis_material ?? '-' }}</td>
                    <td>{{ $detail->nama_unit_satuan ?? '-' }}</td>
                    <td class="text-center">{{ number_format($detail->qty ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="no-border">
                    <td colspan="5" class="text-center">Tidak ada detail barang untuk transaksi ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Bagian Tanda Tangan --}}
    <div class="signature-section">
        <div class="signature-place-date">Demak, {{ \Carbon\Carbon::parse($tanggalCetak)->translatedFormat('d F Y') }}
        </div>
        <span class="signature-line"></span>
        <div class="signature-name">{{ $namaPembuat }}</div>
    </div>

</body>

</html>
