{{-- resources/views/errors/500.blade.php --}}
@extends('layouts.app') {{-- Gunakan layout utama --}}

@section('title', __('Kesalahan Server Internal'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-12 order-md-1 order-last">
                    {{-- Ambil pesan dari exception jika tidak dalam mode debug, jika debug tampilkan pesan asli --}}
                    <h3><i
                            class="bi bi-hdd-stack-fill text-danger me-2"></i>{{ config('app.debug') && isset($exception) ? $exception->getMessage() : __('Kesalahan Server Internal') }}
                    </h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Maaf, terjadi kesalahan di server kami.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body text-center">
                <div class="my-4">
                    <i class="bi bi-hdd-network-fill display-1 text-danger"></i>
                </div>
                <h5 class="card-title">{{ __('Error 500') }}</h5>
                <p class="card-text">
                    {{ __('Terjadi masalah saat memproses permintaan Anda. Tim kami telah diberitahu. Silakan coba lagi nanti atau hubungi administrator.') }}
                </p>
                {{-- Tampilkan detail error jika dalam mode debug --}}
                @if (config('app.debug') && isset($exception))
                    <div class="alert alert-light-danger color-danger mt-4 text-start">
                        <small>
                            <strong class="d-block">{{ get_class($exception) }}: {{ $exception->getMessage() }}</strong>
                            File: {{ $exception->getFile() }} (Baris: {{ $exception->getLine() }})
                            {{-- <pre style="max-height: 200px; overflow-y: auto;">{{ $exception->getTraceAsString() }}</pre> --}}
                        </small>
                    </div>
                @endif
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
