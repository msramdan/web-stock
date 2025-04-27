{{-- resources/views/errors/company-access-forbidden.blade.php --}}
@extends('layouts.app') {{-- Gunakan layout utama Anda --}}

@section('title', __('Akses Ditolak')) {{-- Judul Halaman --}}

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-12 order-md-1 order-last">
                    {{-- Ganti judul sesuai konteks error --}}
                    <h3><i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>{{ __('Akses Ditolak') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ $message ?? __('Anda tidak memiliki izin untuk mengakses halaman atau sumber daya ini untuk perusahaan yang sedang aktif.') }}
                    </p>
                </div>
                {{-- Breadcrumb bisa dikosongkan atau disesuaikan --}}
                {{-- <x-breadcrumb>
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('Akses Ditolak') }}</li>
            </x-breadcrumb> --}}
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body text-center">
                <div class="my-4">
                    <i class="bi bi-lock-fill display-1 text-danger"></i>
                </div>
                <h5 class="card-title">{{ __('Akses Tidak Diizinkan') }}</h5>
                <p class="card-text">
                    {{ $messageDetailed ?? __('Anda tidak terdaftar atau tidak memiliki hak akses untuk perusahaan yang sedang Anda pilih di sesi ini. Silakan pilih perusahaan lain yang Anda miliki aksesnya, atau hubungi administrator jika Anda merasa ini adalah kesalahan.') }}
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-house-door-fill me-2"></i>{{ __('Kembali ke Dashboard') }}
                </a>
                {{-- Tombol ganti company (jika logikanya memungkinkan) --}}
                {{-- <button type="button" class="btn btn-secondary mt-3 ms-2" onclick="document.getElementById('changeCompany').focus()">
                <i class="bi bi-arrow-left-right me-2"></i> Ganti Perusahaan
            </button> --}}
            </div>
        </div>
    </section>
@endsection

@push('css')
    {{-- Tambahkan CSS jika perlu --}}
    <link rel="stylesheet" href="{{ asset('mazer') }}/compiled/css/error.css"> {{-- Contoh jika ada CSS error Mazer --}}
@endpush
