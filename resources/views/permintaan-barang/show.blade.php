@extends('layouts.app')

@section('title', 'Detail Permintaan Barang')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>@yield('title')</h3>
                    <p class="text-subtitle text-muted">No: {{ $permintaanBarang->no_permintaan_barang }}</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('permintaan-barang.index') }}">Permintaan
                                    Barang</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title">Informasi Permintaan</h5>
                        <div>
                            @can('permintaanbarang print')
                                <a href="{{ route('permintaan-barang.printSpecific', $permintaanBarang->id) }}"
                                    class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-print"></i> Cetak</a>
                            @endcan
                            @can('permintaanbarang edit')
                                <a href="{{ route('permintaan-barang.edit', $permintaanBarang->id) }}"
                                    class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2 row">
                                <strong class="col-sm-5">No. Permintaan:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->no_permintaan_barang }}</div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">Tgl. Pengajuan:</strong>
                                <div class="col-sm-7">
                                    {{ \Carbon\Carbon::parse($permintaanBarang->tgl_pengajuan)->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">Nama Supplier:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->nama_supplier }}</div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">User Penginput:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->user->name ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">Perusahaan:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->company->nama_company ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2 row">
                                <strong class="col-sm-5">Nama Bank:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->nama_bank ?: '-' }}</div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">Nama Akun Supplier:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->account_name_supplier ?: '-' }}</div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">No. Rekening Supplier:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->account_number_supplier ?: '-' }}</div>
                            </div>
                            <div class="mb-2 row">
                                <strong class="col-sm-5">Keterangan:</strong>
                                <div class="col-sm-7">{{ $permintaanBarang->keterangan ?: '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5>Detail Barang Diminta:</h5>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Stok Saat Itu</th>
                                    <th class="text-end">Jumlah Pesanan</th>
                                    <th>Satuan</th>
                                    <th class="text-end">Harga/Satuan</th>
                                    <th class="text-end">Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($permintaanBarang->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->barang->nama_barang ?? 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($detail->stok_terakhir, 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($detail->jumlah_pesanan, 0, ',', '.') }}</td>
                                        <td>{{ $detail->satuan }}</td>
                                        <td class="text-end">Rp {{ number_format($detail->harga_per_satuan, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada detail barang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Sub Total Pesanan</td>
                                    <td class="text-end fw-bold">Rp
                                        {{ number_format($permintaanBarang->sub_total_pesanan, 0, ',', '.') }}</td>
                                </tr>
                                @if ($permintaanBarang->include_ppn == 'yes')
                                    <tr>
                                        <td colspan="6" class="text-end">PPN (11%)</td>
                                        <td class="text-end">Rp
                                            {{ number_format($permintaanBarang->nominal_ppn, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="6" class="text-end fw-bolder fs-5">Total Pesanan</td>
                                    <td class="text-end fw-bolder fs-5">Rp
                                        {{ number_format($permintaanBarang->total_pesanan, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('permintaan-barang.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
