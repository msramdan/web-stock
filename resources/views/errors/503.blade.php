{{-- resources/views/errors/503.blade.php --}}
@extends('layouts.app') {{-- Gunakan layout utama --}}

@section('title', __('Layanan Tidak Tersedia'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-12 order-md-1 order-last">
                    {{-- Ambil pesan dari exception jika ada, jika tidak gunakan default --}}
                    <h3><i
                            class="bi bi-cloud-slash-fill text-secondary me-2"></i>{{ $exception->getMessage() ?: __('Layanan Tidak Tersedia') }}
                    </h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Sistem sedang dalam pemeliharaan atau kelebihan beban.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body text-center">
                <div class="my-4">
                    <i class="bi bi-gear-wide-connected display-1 text-secondary"></i>
                </div>
                <h5 class="card-title">{{ __('Error 503') }}</h5>
                <p class="card-text">
                    {{ $exception->getMessage() ?: __('Layanan ini sedang tidak tersedia untuk sementara waktu. Silakan coba lagi dalam beberapa saat.') }}
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-house-door-fill me-2"></i>{{ __('Ke Dashboard') }}
                </a>
            </div>
        </div>
    </section>
@endsection
