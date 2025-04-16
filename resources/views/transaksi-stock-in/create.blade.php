@extends('layouts.app')

@section('title', __('Create Transaksi Stock In'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Transaksi Stock In') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Create a new Transaksi Stock In.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('transaksi-stock-in.index') }}">{{ __('Transaksi Stock In') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('Create') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="transactionForm" method="POST" action="{{ route('transaksi-stock-in.store') }}"
                                enctype="multipart/form-data">

                                @csrf
                                @method('POST')

                                @include('transaksi-stock-in.include.form')
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body text-right">
                                                <button type="submit" id="submitBtn" class="btn btn-success">
                                                    <i class="fas fa-save"></i> Simpan
                                                </button>
                                            </div>
                                        </div>
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
