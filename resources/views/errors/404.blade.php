{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app') {{-- Gunakan layout utama --}}

@section('title', __('Halaman Tidak Ditemukan'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-12 order-md-1 order-last">
                    <h3><i class="bi bi-compass-fill text-warning me-2"></i>{{ __('Halaman Tidak Ditemukan') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Maaf, halaman yang Anda cari tidak dapat ditemukan.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body text-center">
                <div class="my-4">
                    <i class="bi bi-question-circle-fill display-1 text-warning"></i>
                </div>
                <h5 class="card-title">{{ __('Error 404') }}</h5>
                <p class="card-text">
                    {{ __('URL yang Anda minta tidak tersedia di server ini. Mungkin halaman tersebut telah dipindahkan, dihapus, atau Anda salah mengetik alamat.') }}
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
