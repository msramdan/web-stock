{{-- resources/views/errors/403.blade.php --}}
@extends('layouts.app') {{-- Gunakan layout utama --}}

@section('title', __('Akses Ditolak'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-12 order-md-1 order-last">
                    {{-- Ambil pesan dari exception jika ada, jika tidak gunakan default --}}
                    <h3><i
                            class="bi bi-exclamation-octagon-fill text-danger me-2"></i>{{ $exception->getMessage() ?: __('Akses Ditolak') }}
                    </h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Anda tidak memiliki izin yang cukup untuk mengakses sumber daya ini.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body text-center">
                <div class="my-4">
                    <i class="bi bi-lock-fill display-1 text-danger"></i>
                </div>
                <h5 class="card-title">{{ __('Error 403 - Akses Tidak Diizinkan') }}</h5>
                <p class="card-text">
                    {{-- Pesan lebih detail jika ada --}}
                    {{ $exception->getMessage() ?: __('Anda tidak memiliki peran atau izin yang diperlukan untuk melakukan tindakan ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.') }}
                </p>
                <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3 me-2">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('Kembali') }}
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-house-door-fill me-2"></i>{{ __('Ke Dashboard') }}
                </a>
            </div>
        </div>
    </section>
@endsection
