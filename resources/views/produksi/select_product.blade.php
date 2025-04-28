@extends('layouts.app')

@section('title', __('Pilih Produk untuk Produksi'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Pilih Produk Jadi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Pilih produk jadi yang ingin Anda produksi.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produksi.index') }}">{{ __('Produksi') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Pilih Produk') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert></x-alert> {{-- Tampilkan alert jika ada error --}}
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pilih Produk</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                @if ($produkJadiList->isEmpty())
                                    <div class="alert alert-light-warning color-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Tidak ada Produk Jadi dengan Bill of
                                        Materials (BoM) yang terdaftar untuk perusahaan ini. Silakan buat BoM terlebih
                                        dahulu.
                                    </div>
                                    <a href="{{ route('bom.create') }}" class="btn btn-primary mt-2">Buat BoM Baru</a>
                                @else
                                    <form class="form form-horizontal" action="{{ route('produksi.create') }}"
                                        method="GET">
                                        {{-- Tidak perlu @csrf karena method GET --}}
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="barang_id">Produk Jadi</label>
                                                </div>
                                                <div class="col-md-8 form-group">
                                                    <select class="form-select" id="barang_id" name="barang_id" required>
                                                        <option value="" disabled selected>-- Pilih Produk Jadi --
                                                        </option>
                                                        @foreach ($produkJadiList as $id => $nama)
                                                            <option value="{{ $id }}">{{ $nama }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-12 d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-primary me-1 mb-1">Lanjut</button>
                                                    <a href="{{ route('produksi.index') }}"
                                                        class="btn btn-light-secondary me-1 mb-1">Batal</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    {{-- Tambahkan CSS jika perlu, misal untuk Select2 --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
@endpush

@push('js')
    {{-- Tambahkan JS jika perlu --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#barang_id').select2({
                theme: "bootstrap-5"
            });
        });
    </script> --}}
@endpush
