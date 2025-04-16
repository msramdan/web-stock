@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>

    <div class="page-content">
        <section class="row">

            {{-- 1. Area Selamat Datang (Paling Atas) --}}
            <div class="col-12 mb-4"> {{-- Tambahkan margin bawah --}}
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible show fade">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        {{-- Gunakan nama user yang sedang login --}}
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

            {{-- 3. Tabel Transaksi Terakhir --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>5 Transaksi Terakhir (Masuk & Keluar)</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{-- Gunakan table-striped untuk gaya zebra, table-hover untuk efek hover --}}
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
                                    {{-- Loop data transaksi terakhir --}}
                                    @forelse($latestTransactions ?? [] as $transaksi)
                                        <tr>
                                            <td class="text-bold-500">{{ $transaksi->no_surat ?? '-' }}</td>
                                            <td>{{ $transaksi->tanggal ? \Carbon\Carbon::parse($transaksi->tanggal)->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td>
                                                {{-- Badge Sesuai Tipe --}}
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
                                                {{-- Link ke Detail Sesuai Tipe --}}
                                                @php
                                                    // Tentukan nama route berdasarkan tipe transaksi
                                                    $routeName =
                                                        $transaksi->type == 'In'
                                                            ? 'transaksi-stock-in.show'
                                                            : 'transaksi-stock-out.show';
                                                @endphp
                                                {{-- Pastikan route ada sebelum membuat link --}}
                                                @if (Route::has($routeName))
                                                    <a href="{{ route($routeName, $transaksi->id) }}"
                                                        class="btn btn-sm btn-outline-primary icon icon-left">
                                                        <i class="fas fa-eye"></i> Detail {{-- Gunakan ikon FontAwesome --}}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Pesan jika tidak ada transaksi --}}
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
    {{-- Jika menggunakan FontAwesome untuk ikon Detail --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- Jika ikon Mazer (iconly) belum ada global, tambahkan di sini --}}
    {{-- <link rel="stylesheet" href="{{ asset('mazer/compiled/css/iconly.css') }}"> --}}
@endpush

{{-- Tidak perlu JS tambahan khusus untuk fitur ini --}}
