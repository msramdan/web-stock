@extends('layouts.app')

@section('title', 'Detail Produksi - ' . $produksi->no_produksi)

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>Detail Produksi</h3>
                    <p class="text-subtitle text-muted">
                        Informasi detail untuk No. Produksi: <strong>{{ $produksi->no_produksi }}</strong>
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produksi.index') }}">Produksi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                {{-- Kolom Kiri: Informasi Header Produksi --}}
                <div class="col-md-5 col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h4 class="card-title">Informasi Utama</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body pt-2">
                                <dl class="row mt-3">
                                    <dt class="col-sm-4">No. Produksi</dt>
                                    <dd class="col-sm-8">: {{ $produksi->no_produksi }}</dd>

                                    <dt class="col-sm-4">Jumlah Batch</dt>
                                    <dd class="col-sm-8">: {{ $produksi->batch }}</dd>

                                    <dt class="col-sm-4">Tanggal</dt>
                                    <dd class="col-sm-8">: {{ $produksi->tanggal->isoFormat('D MMMM YYYY, HH:mm') }}</dd>

                                    <dt class="col-sm-4">Produk Jadi</dt>
                                    {{-- PERBAIKAN: Menggunakan relasi 'produkJadi' --}}
                                    <dd class="col-sm-8">: ({{ $produksi->produkJadi?->kode_barang }})
                                        {{ $produksi->produkJadi?->nama_barang }}</dd>

                                    <dt class="col-sm-4">BoM Digunakan</dt>
                                    <dd class="col-sm-8">: {{ $produksi->bom?->deskripsi }} (ID: {{ $produksi->bom_id }})
                                    </dd>

                                    <dt class="col-sm-4">Keterangan</dt>
                                    <dd class="col-sm-8">: {{ $produksi->keterangan ?: '-' }}</dd>

                                    <dt class="col-sm-4">Lampiran</dt>
                                    <dd class="col-sm-8">:
                                        @if ($attachmentUrl)
                                            <a href="{{ $attachmentUrl }}" target="_blank"
                                                class="btn icon icon-left btn-sm btn-outline-primary">
                                                <i class="bi bi-paperclip"></i> Lihat/Unduh
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">Dibuat Oleh</dt>
                                    <dd class="col-sm-8">: {{ $produksi->user?->name ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">Dibuat Pada</dt>
                                    <dd class="col-sm-8">: {{ $produksi->created_at->isoFormat('D MMM YYYY, HH:mm') }}</dd>

                                    <dt class="col-sm-4">Diperbarui Pada</dt>
                                    <dd class="col-sm-8">: {{ $produksi->updated_at->isoFormat('D MMM YYYY, HH:mm') }}</dd>
                                </dl>

                                <a href="{{ route('produksi.index') }}" class="btn btn-secondary mt-3"><i
                                        class="fas fa-arrow-left"></i> Kembali</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Detail & Biaya --}}
                <div class="col-md-7 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Detail Item Produksi</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Tipe</th>
                                            <th>Item</th>
                                            <th class="text-center">Satuan</th>
                                            <th class="text-center">Kuantitas/Batch</th>
                                            <th class="text-center">Total Dibutuhkan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Produk Jadi (IN) --}}
                                        @php $detailIn = $produksi->details->where('type', 'In')->first(); @endphp
                                        @if ($detailIn)
                                            <tr class="table-light">
                                                <td class="text-center"><span class="badge bg-light-success">MASUK</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $detailIn->barang?->kode_barang }}</strong> <br>
                                                    <small>{{ $detailIn->barang?->nama_barang }}</small>
                                                </td>
                                                <td class="text-center">{{ $detailIn->unitSatuan?->nama_satuan ?? '-' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ rtrim(rtrim(number_format($detailIn->qty_rate ?? 0, 4, ',', '.'), '0'), ',') }}
                                                </td>
                                                <td class="text-center fw-bold">
                                                    {{ rtrim(rtrim(number_format($detailIn->qty_total_diperlukan ?? 0, 4, ',', '.'), '0'), ',') }}
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- Material (OUT) --}}
                                        @forelse($produksi->details->where('type', 'Out') as $detail)
                                            <tr>
                                                <td class="text-center"><span class="badge bg-light-danger">KELUAR</span>
                                                </td>
                                                <td>{{ $detail->barang?->kode_barang }}
                                                    <br><small>{{ $detail->barang?->nama_barang }}</small></td>
                                                <td class="text-center">{{ $detail->unitSatuan?->nama_satuan ?? '-' }}</td>
                                                <td class="text-center">
                                                    {{ rtrim(rtrim(number_format($detail->qty_rate ?? 0, 4, ',', '.'), '0'), ',') }}
                                                </td>
                                                <td class="text-center">
                                                    {{ rtrim(rtrim(number_format($detail->qty_total_diperlukan ?? 0, 4, ',', '.'), '0'), ',') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Tidak ada detail material.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Card Informasi Biaya --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Informasi Biaya Produksi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Total Biaya Produksi') }}</label>
                                        <p class="fs-4 fw-bold">Rp
                                            {{ number_format($produksi->total_biaya ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Harga Pokok Produksi (HPP) / Unit') }}</label>
                                        <p class="fs-4 fw-bold">Rp
                                            {{ number_format($produksi->harga_satuan_jadi ?? 0, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('mazer/static/css/pages/bootstrap-icons.css') }}">
    <style>
        .table-sm td,
        .table-sm th {
            padding: 0.5rem;
        }

        dt {
            font-weight: 600;
        }
    </style>
@endpush
