@extends('layouts.app')

@section('title', __('Produksi'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Produksi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Daftar semua perintah produksi.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="/">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Produksi') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert></x-alert>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">{{ __('List Produksi') }}</h5>
                        <div>
                            @can('produksi create')
                                <a href="{{ route('produksi.create') }}"
                                    class="btn btn-primary">{{ __('Buat Produksi Baru') }}</a>
                            @endcan
                            <a href="{{ route('produksi.export.excel') }}"
                                class="btn btn-success">{{ __('Export Excel') }}</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive p-1">
                        <table class="table table-striped" id="data-table" width="100%">
                            <thead>
                                <tr>
                                    {{-- PERBAIKAN: Mengembalikan urutan kolom sesuai file asli + kolom baru --}}
                                    <th>#</th>
                                    <th>{{ __('No Produksi') }}</th>
                                    <th>{{ __('Tanggal') }}</th>
                                    <th>{{ __('Produk Jadi') }}</th>
                                    <th class="text-end">{{ __('Batch') }}</th>
                                    <th class="text-end">{{ __('Total Biaya') }}</th>
                                    <th class="text-end">{{ __('HPP / Unit') }}</th>
                                    <th>{{ __('Dibuat Oleh') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.css" />
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('produksi.index') }}",
                columns: [
                    // PERBAIKAN: Urutan dan nama kolom disesuaikan kembali dengan controller
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'no_produksi',
                        name: 'no_produksi'
                    },
                    {
                        data: 'tanggal_f',
                        name: 'tanggal'
                    },
                    {
                        data: 'produk_jadi',
                        name: 'produkJadi.nama_barang'
                    },
                    {
                        data: 'batch',
                        name: 'batch',
                        className: 'text-end'
                    },
                    {
                        data: 'total_biaya',
                        name: 'total_biaya',
                        className: 'text-end',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'harga_satuan_jadi',
                        name: 'harga_satuan_jadi',
                        className: 'text-end',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'dibuat_oleh',
                        name: 'user.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [2, 'desc']
                ]
            });
        });
    </script>
@endpush
