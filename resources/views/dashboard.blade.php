@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>

    <div class="page-content">
        <section class="row">

            {{-- 1. Area Selamat Datang (Paling Atas) --}}
            <div class="col-12 mb-4">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible show fade">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h4>Hi 👋, {{ auth()->user()->name }}</h4>
                        <p>{{ __('You are logged in!') }}</p>
                    </div>
                </div>
            </div>

            {{-- 2. Card Ringkasan --}}
            <div class="col-12 mb-4">
                <div class="row">
                    {{-- Card Total Barang --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon purple mb-2">
                                            <i class="iconly-boldBookmark"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Barang</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($totalBarang ?? 0) }}</h6>
                                        <a href="{{ route('barang.index') }}" class="stretched-link"
                                            title="Lihat Detail Barang"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Total Jenis Material --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon blue mb-2">
                                            <i class="iconly-boldPaper"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Jenis Material</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($totalJenisMaterial ?? 0) }}</h6>
                                        <a href="{{ route('jenis-material.index') }}" class="stretched-link"
                                            title="Lihat Detail Jenis Material"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Total Unit Satuan --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon green mb-2">
                                            <i class="iconly-boldFilter"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Unit Satuan</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($totalUnitSatuan ?? 0) }}</h6>
                                        <a href="{{ route('unit-satuan.index') }}" class="stretched-link"
                                            title="Lihat Detail Unit Satuan"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Card Total User --}}
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon red mb-2">
                                            <i class="iconly-boldUser"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total User</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($totalUser ?? 0) }}</h6>
                                        <a href="{{ route('users.index') }}" class="stretched-link"
                                            title="Lihat Detail User"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Grafik Transaksi Bulanan --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Tren Transaksi Bulanan (12 Bulan Terakhir)</h4>
                    </div>
                    <div class="card-body">
                        <div id="chart-transaksi-bulanan"></div>
                    </div>
                </div>
            </div>

            {{-- 4. Tabel Transaksi Terakhir --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>5 Transaksi Terakhir (Masuk & Keluar)</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-lg">
                                <thead>
                                    <tr>
                                        <th>No Surat</th>
                                        <th>Tanggal</th>
                                        <th>Tipe</th>
                                        <th>User</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestTransactions ?? [] as $transaksi)
                                        <tr>
                                            <td class="text-bold-500">{{ $transaksi->no_surat ?? '-' }}</td>
                                            <td>{{ $transaksi->tanggal ? \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td>
                                                @if ($transaksi->type == 'In')
                                                    <span class="badge bg-light-success">Masuk</span>
                                                @elseif($transaksi->type == 'Out')
                                                    <span class="badge bg-light-danger">Keluar</span>
                                                @else
                                                    <span class="badge bg-light-secondary">{{ $transaksi->type }}</span>
                                                @endif
                                            </td>
                                            <td class="text-bold-500">{{ $transaksi->user_name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $routeName =
                                                        $transaksi->type == 'In'
                                                            ? 'transaksi-stock-in.show'
                                                            : 'transaksi-stock-out.show';
                                                    $routeExists = Route::has($routeName);
                                                @endphp
                                                @if ($routeExists)
                                                    <a href="{{ route($routeName, $transaksi->id) }}"
                                                        class="btn btn-sm btn-outline-primary icon icon-left"
                                                        title="Lihat Detail Transaksi">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-4">Belum ada transaksi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection

@push('css')
    {{-- Pastikan Iconly dan FontAwesome dimuat (jika digunakan) --}}
    <link rel="stylesheet" href="{{ asset('mazer/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('mazer/extensions/apexcharts/apexcharts.css') }}">
@endpush

@push('js')
    {{-- Pastikan library ApexCharts dimuat sebelum script ini (misal di footer layout) --}}
    <script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartMonths = @json($chartMonths ?? []);
            const chartStockIn = @json($chartStockIn ?? []);
            const chartStockOut = @json($chartStockOut ?? []);

            var optionsTransaksiBulanan = {
                series: [{
                        name: 'Transaksi Masuk',
                        data: chartStockIn
                    },
                    {
                        name: 'Transaksi Keluar',
                        data: chartStockOut
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartMonths,
                    title: {
                        text: 'Bulan (12 Bulan Terakhir)'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Transaksi'
                    },
                    labels: {
                        formatter: function(val) {
                            return Math.floor(val);
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " transaksi"
                        }
                    }
                },
                colors: ['#435ebe', '#dc3545'], // Warna Masuk (Biru Mazer), Keluar (Merah Bootstrap)
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: 10
                }
            };

            var chartElement = document.querySelector("#chart-transaksi-bulanan");
            if (chartElement && typeof ApexCharts !== 'undefined') {
                var chart = new ApexCharts(chartElement, optionsTransaksiBulanan);
                chart.render();
            } else if (!chartElement) {
                console.error('Elemen #chart-transaksi-bulanan tidak ditemukan.');
            } else {
                console.error('ApexCharts library tidak dimuat.');
            }
        });
    </script>
@endpush
