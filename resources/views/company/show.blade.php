@extends('layouts.app')

@section('title', __('Detail of company'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('company') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Detail of company.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('company.index') }}">{{ __('company') }}</a>
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
                                        <td class="fw-bold">{{ __('Nama Perusahaan') }}</td>
                                        <td>{{ $company->nama_perusahaan }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('No Telepon') }}</td>
                                        <td>{{ $company->no_telepon }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Email') }}</td>
                                        <td>{{ $company->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Alamat') }}</td>
                                        <td>{{ $company->alamat }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Logo Perusahaan') }}</td>
                                        <td>
                                            @if (!$company->logo_perusahaan)
                                                <img src="https://via.placeholder.com/350?text=No+Image+Avaiable"
                                                    alt="Logo Perusahaan" class="rounded img-fluid">
                                            @else
                                                <img src="{{ asset('storage/uploads/logo-perusahaans/' . $company->logo_perusahaan) }}"
                                                    alt="Logo Perusahaan" class="rounded img-fluid">
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Created at') }}</td>
                                        <td>{{ $company->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Updated at') }}</td>
                                        <td>{{ $company->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <a href="{{ route('company.index') }}" class="btn btn-secondary">{{ __('Kembali') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
