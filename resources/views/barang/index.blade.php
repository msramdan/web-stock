@extends('layouts.app')

@section('title', __('Barang'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Barang') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Berikut adalah daftar semua barang.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="/">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Barang') }}</li>
                </x-breadcrumb>
            </div>
        </div>
    </div>

    <section class="section">
        <x-alert></x-alert>

        {{-- Baris untuk Filter dan Tombol Aksi --}}
        <div class="row mb-2 align-items-center"> {{-- Tambah align-items-center --}}
            {{-- Filter --}}
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="d-flex flex-wrap gap-2 align-items-center"> {{-- Gunakan flexbox --}}
                    <label for="filter_tipe_barang" class="form-label me-1 mb-0">Filter Tipe:</label> {{-- Label lebih dekat --}}
                    <select id="filter_tipe_barang" class="form-select form-select-sm"
                        style="width: auto; min-width: 150px;"> {{-- Atur lebar --}}
                        <option value="">Semua Tipe</option>
                        <option value="Bahan Baku">Bahan Baku</option>
                        <option value="Barang Jadi">Barang Jadi</option>
                    </select>
                </div>
            </div>
            {{-- Tombol Aksi --}}
            <div class="col-md-6 d-flex justify-content-md-end justify-content-start align-items-center gap-2">
                {{-- Gunakan flexbox & gap --}}
                @can('barang export pdf')
                    {{-- Link Export PDF akan diupdate oleh JS --}}
                    <a href="{{ route('barang.exportPdf') }}" id="exportPdfLink" class="btn btn-danger btn-sm" target="_blank">
                        {{-- Ubah ke danger/merah --}}
                        <i class="fas fa-file-pdf"></i>
                        {{ __('Export PDF') }}
                    </a>
                @endcan

                {{-- === TOMBOL EXPORT EXCEL BARU === --}}
                @can('barang export excel')
                    {{-- Sesuaikan nama permission --}}
                    <a href="{{ route('barang.exportExcel') }}" id="exportExcelLink" class="btn btn-success btn-sm"
                        target="_blank">
                        <i class="fas fa-file-excel"></i>
                        {{ __('Export Excel') }}
                    </a>
                @endcan
                {{-- === AKHIR TOMBOL EXCEL === --}}

                @can('barang create')
                    <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm"> {{-- btn-sm --}}
                        <i class="fas fa-plus"></i>
                        {{ __('Tambah barang') }}
                    </a>
                @endcan
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive p-1">
                            <table class="table table-striped" id="data-table" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('Kode') }}</th> {{-- Disingkat --}}
                                        <th>{{ __('Nama Barang') }}</th>
                                        <th>{{ __('Tipe') }}</th> {{-- Disingkat --}}
                                        <th>{{ __('Deskripsi') }}</th>
                                        <th>{{ __('Jenis Material') }}</th>
                                        <th>{{ __('Satuan') }}</th> {{-- Disingkat --}}
                                        <th>{{ __('Harga Satuan') }}</th>
                                        <th>{{ __('Stok') }}</th>
                                        <th>{{ __('Photo') }}</th>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.css" />
    <style>
        /* Optional: Atur lebar kolom jika perlu */
        #data-table th:nth-child(1) {
            width: 10%;
        }

        #data-table th:nth-child(2) {
            width: 20%;
        }

        #data-table th:nth-child(3) {
            width: 10%;
        }

        /* ... dst ... */
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.12.0/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            var dataTable = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('barang.index') }}",
                    data: function(d) {
                        d.tipe_barang = $('#filter_tipe_barang').val(); // Kirim filter
                    }
                },
                columns: [{
                        data: 'kode_barang',
                        name: 'barang.kode_barang'
                    },
                    {
                        data: 'nama_barang',
                        name: 'barang.nama_barang'
                    },
                    {
                        data: 'tipe_barang', // Kolom Tipe Barang sudah ada di query controller
                        name: 'barang.tipe_barang',
                        render: function(data, type, row) { // Render badge di JS
                            if (data === 'Barang Jadi')
                                return '<span class="badge bg-light-primary">Barang Jadi</span>';
                            if (data === 'Bahan Baku')
                                return '<span class="badge bg-light-secondary">Bahan Baku</span>';
                            return data ?? '-';
                        }
                    },
                    {
                        data: 'deskripsi_barang',
                        name: 'barang.deskripsi_barang',
                        render: function(data) {
                            return data ? data.substr(0, 50) + (data.length > 50 ? '...' : '') :
                                '-';
                        }
                    },
                    {
                        data: 'nama_jenis_material',
                        name: 'jenis_material.nama_jenis_material'
                    }, // Gunakan nama kolom join
                    {
                        data: 'nama_unit_satuan',
                        name: 'unit_satuan.nama_unit_satuan'
                    }, // Gunakan nama kolom join
                    {
                        data: 'harga',
                        name: 'barang.harga'
                    },
                    {
                        data: 'stock_barang',
                        name: 'barang.stock_barang'
                    },
                    {
                        data: 'photo_barang',
                        name: 'barang.photo_barang',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, full, meta) {
                            let imgUrl = data ?
                                `{{ asset('storage/uploads/photo-barangs/') }}/${data}` :
                                `{{ asset('assets/static/images/faces/2.jpg') }}`;
                            return `<img src="${imgUrl}" alt="Photo" style="width:120px;">`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [1, 'asc']
                ] // Default sort by nama_barang
            });

            // Fungsi untuk update link export PDF
            function updateExportLinks() { // Ubah nama fungsi
                var selectedType = $('#filter_tipe_barang').val();
                var exportPdfUrl = "{{ route('barang.exportPdf') }}";
                var exportExcelUrl = "{{ route('barang.exportExcel') }}"; // URL Excel

                if (selectedType) {
                    exportPdfUrl += "?tipe_barang=" + encodeURIComponent(selectedType);
                    exportExcelUrl += "?tipe_barang=" + encodeURIComponent(
                        selectedType); // Tambahkan filter ke Excel
                }
                $('#exportPdfLink').attr('href', exportPdfUrl);
                $('#exportExcelLink').attr('href', exportExcelUrl); // Update link Excel
            }

            // Event listener untuk filter dropdown
            $('#filter_tipe_barang').on('change', function() {
                dataTable.ajax.reload(); // Muat ulang tabel
                updateExportLinks(); // Update kedua link export
            });

            // Panggil sekali saat load untuk set link awal
            updateExportLinks();

        });
    </script>
@endpush
