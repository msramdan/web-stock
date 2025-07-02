@extends('layouts.app')

@section('title', __('Detail BoM'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('BoM') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Detail Bill of Material.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('bom.index') }}">{{ __('BoM') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('Detail') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5>{{ __('Informasi BoM Utama') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" style="width: 25%;">{{ __('Kode Barang Jadi') }}</td>
                                            <td>{{ $bom->barang?->kode_barang ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Nama Barang Jadi') }}</td>
                                            <td>{{ $bom->barang?->nama_barang ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Unit Satuan Barang Jadi') }}</td>
                                            <td>{{ $bom->barang?->unitSatuan?->nama_unit_satuan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Deskripsi BoM') }}</td>
                                            <td>{{ $bom->deskripsi }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <hr>
                            <h5>{{ __('Material / Komponen Pembentuk') }}</h5>
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;" class="text-center">#</th>
                                            <th style="width: 25%;">Kode Material</th>
                                            <th style="width: 35%;">Nama Material</th>
                                            <th style="width: 15%;" class="text-center">Jumlah</th>
                                            <th style="width: 20%;" class="text-center">Unit Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bom->details as $index => $detail)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $detail->material?->kode_barang ?? 'N/A' }}</td>
                                                <td>{{ $detail->material?->nama_barang ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    {{ rtrim(rtrim(number_format($detail->jumlah ?? 0, 8, ',', '.'), '0'), ',') }}
                                                </td>
                                                <td class="text-center">{{ $detail->unitSatuan?->nama_unit_satuan ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Tidak ada
                                                    material/komponen dalam BoM ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- ⚠️ BAGIAN BARU DITAMBAHKAN DI SINI ⚠️ --}}
                            <hr>
                            <h5 class="mt-4">{{ __('Daftar Kemasan') }}</h5>
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%;" class="text-center">#</th>
                                            <th style="width: 25%;">Kode Kemasan</th>
                                            <th style="width: 35%;">Nama Kemasan</th>
                                            <th style="width: 15%;" class="text-center">Jumlah</th>
                                            <th style="width: 20%;" class="text-center">Unit Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bom->kemasan as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item->barang?->kode_barang ?? 'N/A' }}</td>
                                                <td>{{ $item->barang?->nama_barang ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    {{ rtrim(rtrim(number_format($item->jumlah ?? 0, 4, ',', '.'), '0'), ',') }}
                                                </td>
                                                <td class="text-center">{{ $item->unitSatuan?->nama_unit_satuan ?? '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Tidak ada data kemasan
                                                    untuk BoM ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <a href="{{ route('bom.index') }}" class="btn btn-secondary mt-3"><i
                                    class="fas fa-arrow-left"></i> {{ __('Kembali') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
