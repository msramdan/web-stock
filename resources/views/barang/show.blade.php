@extends('layouts.app')

@section('title', __('Detail of Barang'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Barang') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Detail of barang.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('barang.index') }}">{{ __('Barang') }}</a>
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
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <tr>
                    <td class="fw-bold">{{ __('Kode Barang') }}</td>
                    <td>{{ $barang->kode_barang }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Deskripsi Barang') }}</td>
                    <td>{{ $barang->deskripsi_barang }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Jenis Material') }}</td>
                    <td>{{ $barang->jenis_material ? $barang->jenis_material->id : '' }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Unit Satuan') }}</td>
                    <td>{{ $barang->unit_satuan ? $barang->unit_satuan->id : '' }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Stock Barang') }}</td>
                    <td>{{ $barang->stock_barang }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Photo Barang') }}</td>
                    <td>
                        @if (!$barang->photo_barang)
                            <img src="https://via.placeholder.com/350?text=No+Image+Avaiable" alt="Photo Barang" class="rounded img-fluid">
                        @else
                            <img src="{{ asset('storage/uploads/photo-barangs/' . $barang->photo_barang) }}" alt="Photo Barang" class="rounded img-fluid">
                        @endif
                    </td>
                </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Created at') }}</td>
                                        <td>{{ $barang->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Updated at') }}</td>
                                        <td>{{ $barang->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <a href="{{ route('barang.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
