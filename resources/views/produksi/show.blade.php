@extends('layouts.app')

@section('title', __('Detail Produksi') . ' - ' . $produksi->no_produksi)

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Detail Produksi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Informasi detail untuk No. Produksi:') }} <strong>{{ $produksi->no_produksi }}</strong>
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produksi.index') }}">{{ __('Produksi') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Detail') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                {{-- Kolom Kiri: Info Header Produksi --}}
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

                                    <dt class="col-sm-4">Batch</dt>
                                    <dd class="col-sm-8">: {{ $produksi->batch }}</dd>

                                    <dt class="col-sm-4">Tanggal</dt>
                                    <dd class="col-sm-8">: {{ $produksi->tanggal->isoFormat('D MMMM YYYY, HH:mm') }}</dd>

                                    <dt class="col-sm-4">Produk Jadi</dt>
                                    <dd class="col-sm-8">: ({{ $produksi->produkJadi?->kode_barang }})
                                        {{ $produksi->produkJadi?->nama_barang }}</dd>

                                    <dt class="col-sm-4">Target Qty</dt>
                                    <dd class="col-sm-8">: {{ $produksi->qty_target }}
                                        {{ $produksi->produkJadi?->unitSatuan?->nama_unit_satuan }}</dd>

                                    <dt class="col-sm-4">BoM Digunakan</dt>
                                    <dd class="col-sm-8">: {{ $produksi->bom?->deskripsi }} (ID: {{ $produksi->bom_id }})
                                    </dd>

                                    {{-- <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">: {{ $produksi->status }}</dd> --}}

                                    <dt class="col-sm-4">Keterangan</dt>
                                    <dd class="col-sm-8">: {{ $produksi->keterangan ?: '-' }}</dd>

                                    <dt class="col-sm-4">Attachment</dt>
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

                                    {{-- <dt class="col-sm-4">Dibuat Oleh</dt>
                                <dd class="col-sm-8">: {{ $produksi->user?->name ?? '-' }}</dd> --}}

                                    <dt class="col-sm-4">Dibuat Pada</dt>
                                    <dd class="col-sm-8">: {{ $produksi->created_at->isoFormat('D MMM YYYY, HH:mm') }}</dd>

                                    <dt class="col-sm-4">Diperbarui Pada</dt>
                                    <dd class="col-sm-8">: {{ $produksi->updated_at->isoFormat('D MMM YYYY, HH:mm') }}</dd>
                                </dl>

                                <a href="{{ route('produksi.index') }}" class="btn btn-secondary mt-3"><i
                                        class="fas fa-arrow-left"></i> {{ __('Kembali') }}</a>
                                {{-- Tambahkan tombol aksi lain jika perlu (misal: Cetak SPK, Ubah Status) --}}
                                {{-- @can('produksi edit')
                                <a href="{{ route('produksi.edit', $produksi->id) }}" class="btn btn-primary mt-3"><i class="fas fa-pencil-alt"></i> {{ __('Edit') }}</a>
                            @endcan --}}

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Info Detail Bahan & Hasil --}}
                <div class="col-md-7 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Detail Item Produksi</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Tipe</th>
                                                <th>Item</th>
                                                <th class="text-center">Satuan</th>
                                                <th class="text-center">Qty/Unit Rate</th>
                                                <th class="text-center">Qty Target Prod.</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Pisahkan detail In dan Out
                                                $detailIn = $produksi->details->where('type', 'In')->first();
                                                $detailsOut = $produksi->details->where('type', 'Out');
                                            @endphp

                                            {{-- Tampilkan Produk Jadi (In) --}}
                                            @if ($detailIn)
                                                <tr class="table-light">
                                                    <td class="text-center"><span class="badge bg-light-success">IN</span>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $detailIn->barang?->kode_barang }}</strong> <br>
                                                        <small>{{ $detailIn->barang?->nama_barang }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $detailIn->unitSatuan?->nama_unit_satuan ?? '-' }}</td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim($detailIn->qty_rate, '0'), '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim($detailIn->qty_target_produksi, '0'), '.') }}
                                                    </td>
                                                    <td class="text-center fw-bold">
                                                        {{ rtrim(rtrim($detailIn->qty_total_diperlukan, '0'), '.') }}
                                                    </td>
                                                </tr>
                                            @endif

                                            {{-- Tampilkan Material (Out) --}}
                                            @forelse($detailsOut as $detail)
                                                <tr>
                                                    <td class="text-center"><span class="badge bg-light-danger">OUT</span>
                                                    </td>
                                                    <td>
                                                        {{ $detail->barang?->kode_barang }} <br>
                                                        <small>{{ $detail->barang?->nama_barang }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $detail->unitSatuan?->nama_unit_satuan ?? '-' }}</td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim($detail->qty_rate, '0'), '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim($detail->qty_target_produksi, '0'), '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim($detail->qty_total_diperlukan, '0'), '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Tidak ada detail
                                                        material untuk produksi ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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
    <link rel="stylesheet" href="{{ asset('mazer/static/css/pages/bootstrap-icons.css') }}"> {{-- Untuk ikon di tabel --}}
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
