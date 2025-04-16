@extends('layouts.app')

@section('title', __('Detail of Transaksis'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Transaksis') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Detail of transaksi.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('transaksi-stock-out.index') }}">{{ __('Transaksis') }}</a>
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
                    <td class="fw-bold">{{ __('No Surat') }}</td>
                    <td>{{ $transaksi->no_surat }}</td>
                </tr>
<tr>
                                <td class="fw-bold">{{ __('Tanggal') }}</td>
                                <td>{{ isset($transaksi->tanggal) ? $transaksi->tanggal?->format("Y-m-d H:i:s") : '' }}</td>
                               </tr>
<tr>
                    <td class="fw-bold">{{ __('Type') }}</td>
                    <td>{{ $transaksi->type }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Keterangan') }}</td>
                    <td>{{ $transaksi->keterangan }}</td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('Attachment') }}</td>
                    <td>
                        @if (!$transaksi->attachment)
                            <img src="https://via.placeholder.com/350?text=No+Image+Avaiable" alt="Attachment" class="rounded img-fluid">
                        @else
                            <img src="{{ asset('storage/uploads/attachments/' . $transaksi->attachment) }}" alt="Attachment" class="rounded img-fluid">
                        @endif
                    </td>
                </tr>
<tr>
                    <td class="fw-bold">{{ __('User') }}</td>
                    <td>{{ $transaksi->user ? $transaksi->user->name : '' }}</td>
                </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Created at') }}</td>
                                        <td>{{ $transaksi->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Updated at') }}</td>
                                        <td>{{ $transaksi->updated_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <a href="{{ route('transaksi-stock-out.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
