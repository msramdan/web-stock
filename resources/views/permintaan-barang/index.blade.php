@extends('layouts.app')

@section('title', __('Daftar Permintaan Barang'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-7 order-md-1 order-last">
                    <h3>@yield('title')</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Berikut adalah daftar semua permintaan barang.') }}
                    </p>
                </div>
                <div class="col-12 col-md-5 order-md-2 order-first">
                    <div class="d-flex justify-content-end align-items-center">
                        {{-- Pastikan komponen x-breadcrumb Anda berfungsi --}}
                        <x-breadcrumb>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                        </x-breadcrumb>
                    </div>
                </div>
            </div>
            <div class="row mt-3 mb-2">
                <div class="col-12 d-flex justify-content-md-end justify-content-start align-items-center flex-wrap gap-2">
                    {{-- @can('permintaan barang export pdf')
                        <a href="{{ route('permintaan-barang.printBlankForm') }}" id="printBlankFormLink"
                            class="btn btn-danger btn-sm" target="_blank" title="{{ __('Cetak Form Kosong') }}">
                            <i class="fas fa-print"></i>
                            {{ __('Cetak Form Kosong') }}
                        </a>
                    @endcan --}}
                    @can('permintaan barang export excel')
                        <a href="{{ route('permintaan-barang.index') }}?export=excel" id="exportExcelLinkList"
                            class="btn btn-success btn-sm" target="_blank" title="{{ __('Export Daftar ke Excel') }}">
                            <i class="fas fa-file-excel"></i>
                            {{ __('Daftar Excel') }}
                        </a>
                    @endcan
                    @can('permintaan barang create')
                        {{-- Tombol Buat Permintaan Barang dengan warna biru dan ikon dari /produksi --}}
                        <a href="{{ route('permintaan-barang.create') }}" class="btn icon icon-left btn-primary">
                            <i class="fas fa-plus"></i> {{ __('Buat Permintaan Barang') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div> {{-- Akhir dari .page-title --}}
    </div> {{-- Akhir dari .page-heading --}}

    <section class="section">
        {{-- Pastikan komponen x-alert Anda berfungsi --}}
        <x-alert></x-alert>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('Tabel Permintaan Barang') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive p-1">
                            <table class="table table-striped" id="permintaan-data-table" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('No.') }}</th>
                                        <th>{{ __('Tgl Pengajuan') }}</th>
                                        <th>{{ __('No. Permintaan') }}</th>
                                        <th>{{ __('Supplier') }}</th>
                                        <th class="text-end">{{ __('Total Pesanan') }}</th>
                                        <th>{{ __('User Penginput') }}</th>
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
@endsection

@push('css')
    {{-- Menggunakan CDN agar konsisten dengan barang/index.blade.php --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.css" />
    <link rel="stylesheet" href="{{ asset('mazer/extensions/sweetalert2/sweetalert2.min.css') }}">
    {{-- Bootstrap Icons untuk tombol Buat Permintaan Barang --}}
    <link rel="stylesheet" href="{{ asset('mazer/extensions/bootstrap-icons/bootstrap-icons.css') }}">
@endpush

@push('js')
    {{-- Menggunakan CDN agar konsisten dengan barang/index.blade.php --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.js"></script>
    <script src="{{ asset('mazer/extensions/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var dataTable = $('#permintaan-data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('permintaan-barang.index') }}",
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'tgl_pengajuan_formatted',
                        name: 'tgl_pengajuan'
                    },
                    {
                        data: 'no_permintaan_barang',
                        name: 'no_permintaan_barang'
                    },
                    {
                        data: 'nama_supplier',
                        name: 'nama_supplier'
                    },
                    {
                        data: 'total_pesanan_formatted',
                        name: 'total_pesanan',
                        className: 'text-end'
                    },
                    {
                        data: 'user_name',
                        name: 'user.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

            $('body').on('click', '.delete-permintaan', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: '{{ __('Apakah Anda yakin?') }}',
                    text: "{{ __('Data permintaan barang ini akan dihapus secara permanen!') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __('Ya, hapus!') }}',
                    cancelButtonText: '{{ __('Batal') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });
    </script>
@endpush
