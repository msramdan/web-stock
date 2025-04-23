@extends('layouts.app')

@section('title', __('Detail of BoM'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('BoM') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Detail of bom.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('bom.index') }}">{{ __('BoM') }}</a>
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
                                        <td class="fw-bold">{{ __('Barang') }}</td>
                                        <td>{{ $bom->barang ? $bom->barang->id : '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Deskripsi') }}</td>
                                        <td>{{ $bom->deskripsi }}</td>
                                    </tr>
                                </table>
                            </div>

                            <a href="{{ route('bom.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
