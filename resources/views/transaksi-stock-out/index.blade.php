@extends('layouts.app')

@section('title', __('Transaksi Stock Out'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Transaksi Stock Out') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Below is a list of all Transaksi Stock Out.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="/">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Transaksi Stock Out') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert></x-alert>

            <div class="d-flex justify-content-end">
                {{-- Tombol Export PDF List --}}
                @can('transaksi stock out export pdf')
                    <a href="{{ route('transaksi-stock-out.exportPdf') }}" class="btn btn-success mb-3 me-2" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        {{ __('Export PDF') }}
                    </a>
                @endcan

                @can('transaksi stock out create')
                    <a href="{{ route('transaksi-stock-out.create') }}" class="btn btn-primary mb-3">
                        <i class="fas fa-plus"></i>
                        {{ __('Create Stock Out') }}
                    </a>
                @endcan
            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive p-1">
                                <table class="table table-striped" id="data-table" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('No Surat') }}</th>
                                            <th>{{ __('Tanggal') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Keterangan') }}</th>
                                            <th>{{ __('Attachment') }}</th>
                                            <th>{{ __('User') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.css" />
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.js"></script>
    <script>
        $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('transaksi-stock-out.index') }}", // Route untuk Stock Out
            columns: [{
                    data: 'no_surat',
                    name: 'no_surat'
                },
                {
                    data: 'tanggal',
                    name: 'tanggal',
                    render: function(data, type, row) {
                        if (data) {
                            let date = new Date(data);
                            let day = ('0' + date.getDate()).slice(-2);
                            let month = ('0' + (date.getMonth() + 1)).slice(-2);
                            let year = date.getFullYear();
                            let hours = ('0' + date.getHours()).slice(-2);
                            let minutes = ('0' + date.getMinutes()).slice(-2);
                            return `${day}/${month}/${year} ${hours}:${minutes}`;
                        }
                        return '-';
                    }
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'keterangan',
                    name: 'keterangan'
                },
                {
                    data: 'attachment',
                    name: 'attachment',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'user_name',
                    name: 'users.name'
                }, // Alias dari join di controller
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                    // Pastikan view 'transaksi-stock-out.include.action' sudah ada dan benar
                }
            ],
            order: [
                [1, 'desc']
            ] // Urutkan berdasarkan tanggal descending
        });
    </script>
@endpush
