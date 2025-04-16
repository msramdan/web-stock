@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>

    <div class="page-content">
        <section class="row">

            {{-- 1. Area Selamat Datang --}}
            <div class="col-12 mb-4">
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
                                        <a href="{{ route('barang.index') }}" class="stretched-link"></a>
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
                                        <a href="{{ route('jenis-material.index') }}" class="stretched-link"></a>
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
                                        <a href="{{ route('unit-satuan.index') }}" class="stretched-link"></a>
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
                                        <a href="{{ route('users.index') }}" class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Grafik Transaksi Bulanan --}}
            <div class="col-12"> {{-- Grafik bisa dibuat full width --}}
                <div class="card">
                    <div class="card-header">
                        <h4>Tren Transaksi Bulanan (12 Bulan Terakhir)</h4>
                    </div>
                    <div class="card-body">
                        {{-- Wadah untuk grafik --}}
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
                                                @endphp
                                                @if (Route::has($routeName))
                                                    <a href="{{ route($routeName, $transaksi->id) }}"
                                                        class="btn btn-sm btn-outline-primary icon icon-left">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
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
    {{-- Pastikan Iconly (untuk card) dan FontAwesome (untuk tabel) dimuat jika belum global --}}
    <link rel="stylesheet" href="{{ asset('mazer/compiled/css/iconly.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- CSS Tambahan untuk ApexCharts jika perlu --}}
    {{-- <link rel="stylesheet" href="{{ asset('mazer/extensions/apexcharts/apexcharts.css') }}"> --}}
@endpush

@push('js')
    {{-- Pastikan library ApexCharts dimuat sebelum script ini --}}
    {{-- Contoh: <script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script> di layout footer --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil data chart dari PHP (pastikan aman dari XSS jika data dari user input)
            const chartMonths = @json($chartMonths ?? []);
            const chartStockIn = @json($chartStockIn ?? []);
            const chartStockOut = @json($chartStockOut ?? []);

            // Opsi untuk ApexCharts Bar Chart
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
                    height: 350, // Tinggi chart
                    toolbar: {
                        show: true // Tampilkan tombol download dll.
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false, // Batang vertikal
                        columnWidth: '55%', // Lebar batang
                        endingShape: 'rounded' // Ujung batang melengkung
                    },
                },
                dataLabels: {
                    enabled: false // Tidak menampilkan label di atas batang
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartMonths, // Label sumbu X (bulan)
                    title: {
                        text: 'Bulan (12 Bulan Terakhir)'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Transaksi'
                    },
                    // Pastikan hanya integer ditampilkan di sumbu Y
                    labels: {
                        formatter: function(val) {
                            return Math.floor(val); // Atau parseInt(val)
                        }
                    },
                    // Jika ingin sumbu Y mulai dari 0
                    // min: 0
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " transaksi" // Tooltip saat hover
                        }
                    }
                },
                // Warna bisa disesuaikan
                colors: ['#435ebe', '#dc3545'], // Biru Mazer untuk Masuk, Merah untuk Keluar
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: 10
                }
            };

            // Render chart jika elemennya ada
            var chartElement = document.querySelector("#chart-transaksi-bulanan");
            if (chartElement && typeof ApexCharts !== 'undefined') { // Cek ApexCharts tersedia
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
