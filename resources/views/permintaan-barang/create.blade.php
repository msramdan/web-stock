@extends('layouts.app')

@section('title', 'Tambah Permintaan Barang')

@push('style')
    <link rel="stylesheet" href="{{ asset('mazer/extensions/choices.js/public/assets/styles/choices.min.css') }}">
    <style>
        .choices__inner {
            background-color: #fff;
            /* Sesuaikan dengan style input Mazer */
            border: 1px solid #dce7f1;
            /* Sesuaikan dengan style input Mazer */
        }

        .is-invalid .choices__inner {
            border-color: #dc3545;
            /* Warna border error Bootstrap */
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>@yield('title')</h3>
                    <p class="text-subtitle text-muted">Buat permintaan barang baru.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('permintaan-barang.index') }}">Permintaan
                                    Barang</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Formulir Permintaan Barang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('permintaan-barang.store') }}" method="POST">
                        @csrf
                        @include('permintaan-barang.include.form')
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Permintaan</button>
                            <a href="{{ route('permintaan-barang.index') }}" class="btn btn-light">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @stack('scripts_vendor') {{-- Untuk jQuery dan Choices.js dari form.blade.php --}}
    @stack('scripts_custom_form') {{-- Untuk script custom dari form.blade.php --}}
@endpush
