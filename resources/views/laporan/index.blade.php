@extends('layouts.app')

@section('title', __('Laporan Transaksi'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Laporan Transaksi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Filter dan generate laporan transaksi dalam format Excel.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="/">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Laporan Transaksi') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Filter Laporan</h4>
                        </div>
                        <div class="card-body">
                            {{-- Tampilkan error validasi umum atau dari validasi range --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible show fade">
                                    <ul class="ms-0 mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>
                                                <p class="mb-0">{{ $error }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('laporan.exportExcel') }}" method="POST">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tanggal_mulai">{{ __('Tanggal Mulai') }}</label>
                                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                                class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                                value="{{ old('tanggal_mulai') }}" required>
                                            {{-- @error('tanggal_mulai') akan ditangani oleh $errors->any() di atas --}}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tanggal_selesai">{{ __('Tanggal Selesai') }}</label>
                                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                                class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                                value="{{ old('tanggal_selesai') }}" required>
                                            {{-- @error('tanggal_selesai') akan ditangani oleh $errors->any() di atas --}}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="jenis_material_id">{{ __('Jenis Material (Opsional)') }}</label>
                                            <select name="jenis_material_id" id="jenis_material_id"
                                                class="form-select @error('jenis_material_id') is-invalid @enderror">
                                                <option value="" selected>-- Semua Jenis Material --</option>
                                                @foreach ($jenisMaterials as $material)
                                                    <option value="{{ $material->id }}"
                                                        {{ old('jenis_material_id') == $material->id ? 'selected' : '' }}>
                                                        {{ $material->nama_jenis_material }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('jenis_material_id')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> {{ __('Generate Excel') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    {{-- Tambahkan CSS jika diperlukan, misalnya untuk date picker library --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('js')
    {{-- Tambahkan JS jika diperlukan --}}
@endpush
