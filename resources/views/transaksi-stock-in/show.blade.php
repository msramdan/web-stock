@extends('layouts.app')

@section('title', __('Create Transaksi Stock In'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Transaksi Stock In') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Tambah Transaksi Stock In.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('transaksi-stock-in.index') }}">{{ __('Transaksi Stock In') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('Create') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        {{-- Untuk error dari validator --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Untuk error dari try-catch --}}
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="card-body">
                            @extends('layouts.app')

                        @section('content')
                            <section class="content">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Detail Transaksi
                                                        {{ $transaksi->type == 'In' ? 'Masuk' : 'Keluar' }}</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <table class="table table-bordered">
                                                                        <tr>
                                                                            <th style="width: 30%">No Surat</th>
                                                                            <td>{{ $transaksi->no_surat }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Tanggal</th>
                                                                            <td>{{ formatTanggalIndonesia($transaksi->tanggal) }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>User</th>
                                                                            <td>{{ $transaksi->user_name }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Keterangan</th>
                                                                            <td>{{ $transaksi->keterangan ?? '-' }}</td>
                                                                        </tr>
                                                                        @if ($transaksi->attachment)
                                                                            <tr>
                                                                                <th>Attachment</th>
                                                                                <td>
                                                                                    @php
                                                                                        $companyId = session(
                                                                                            'sessionCompany',
                                                                                        );
                                                                                        $filePath =
                                                                                            'uploads/attachments/' .
                                                                                            $transaksi->attachment;
                                                                                    @endphp

                                                                                    @if (Storage::exists('public/' . $filePath))
                                                                                        <a href="{{ Storage::url($filePath) }}"
                                                                                            target="_blank"
                                                                                            class="text-primary">
                                                                                            <i
                                                                                                class="bi bi-file-earmark-arrow-down"></i>
                                                                                            Lihat Lampiran
                                                                                        </a>
                                                                                    @else
                                                                                        <span class="text-danger">File tidak
                                                                                            ditemukan</span>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered table-striped">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Kode Barang</th>
                                                                                    <th>Nama Barang</th>
                                                                                    <th>Jenis Material</th>
                                                                                    <th>Unit Satuan</th>
                                                                                    <th>Qty</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($details as $index => $detail)
                                                                                    <tr>
                                                                                        <td>{{ $index + 1 }}</td>
                                                                                        <td>{{ $detail->kode_barang }}</td>
                                                                                        <td>{{ $detail->nama_barang }}</td>
                                                                                        <td>{{ $detail->nama_jenis_material }}
                                                                                        </td>
                                                                                        <td>{{ $detail->nama_unit_satuan }}
                                                                                        </td>
                                                                                        <td>{{ formatAngkaRibuan($detail->qty, 4) }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <a href="{{ route('transaksi-stock-in.index') }}"
                                                        class="btn btn-secondary">
                                                        <i class="fas fa-arrow-left"></i> Kembali
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        @endsection
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
