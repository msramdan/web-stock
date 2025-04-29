@extends('layouts.app')
@section('title', __('Laporan Transaksi'))
@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Laporan Transaksi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Filter dan generate laporan transaksi gabungan (Transaksi & Produksi) dalam format Excel.') }}
                    </p>
                </div> <x-breadcrumb>
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
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible show fade">
                                    <ul class="ms-0 mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>
                                                <p class="mb-0">{{ $error }}</p>
                                            </li>
                                        @endforeach
                                    </ul> <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif
                            <form action="{{ route('laporan.exportExcel') }}" method="POST"> @csrf
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-group"> <label for="tanggal_mulai">{{ __('Tanggal Mulai') }} <span
                                                    class="text-danger">*</span></label> <input type="date"
                                                name="tanggal_mulai" id="tanggal_mulai"
                                                class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                                value="{{ old('tanggal_mulai') }}" required> </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"> <label for="tanggal_selesai">{{ __('Tanggal Selesai') }}
                                                <span class="text-danger">*</span></label> <input type="date"
                                                name="tanggal_selesai" id="tanggal_selesai"
                                                class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                                value="{{ old('tanggal_selesai') }}" required> </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group"> <label
                                                for="jenis_material_id">{{ __('Jenis Material (Opsional)') }}</label>
                                            <select name="jenis_material_id" id="jenis_material_id"
                                                class="form-select @error('jenis_material_id') is-invalid @enderror">
                                                <option value="" selected>-- Semua Jenis --</option>
                                                @foreach ($jenisMaterials as $material)
                                                    <option value="{{ $material->id }}"
                                                        {{ old('jenis_material_id') == $material->id ? 'selected' : '' }}>
                                                        {{ $material->nama_jenis_material }} </option>
                                                @endforeach
                                            </select> @error('jenis_material_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipe_barang">{{ __('Tipe Barang (Opsional)') }}
                                                <i class="fas fa-info-circle" data-bs-toggle="tooltip"
                                                    title="Filter berdasarkan tipe barang: Bahan Baku atau Barang Jadi"></i>
                                            </label>
                                            <select name="tipe_barang" id="tipe_barang"
                                                class="form-select @error('tipe_barang') is-invalid @enderror">
                                                <option value="" {{ old('tipe_barang') == '' ? 'selected' : '' }}>--
                                                    Semua Tipe --</option>
                                                <option value="Bahan Baku"
                                                    {{ old('tipe_barang') == 'Bahan Baku' ? 'selected' : '' }}>Bahan Baku
                                                </option>
                                                <option value="Barang Jadi"
                                                    {{ old('tipe_barang') == 'Barang Jadi' ? 'selected' : '' }}>Barang Jadi
                                                </option>
                                            </select>
                                            @error('tipe_barang')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-end"> <button type="submit"
                                            class="btn btn-success"><i class="fas fa-file-excel"></i>
                                            {{ __('Generate Excel') }}</button> </div>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
@endpush
