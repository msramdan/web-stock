@extends('layouts.app')

@section('title', __('Buat Produksi Baru'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Produksi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Buat perintah produksi baru.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produksi.index') }}">{{ __('Produksi') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Buat Baru') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert></x-alert>
            @if ($errors->has('stok'))
                <div class="alert alert-light-danger color-danger alert-dismissible show fade">
                    <ul class="mb-0">
                        @foreach ($errors->get('stok') as $error)
                            <li><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('produksi.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="barang_id" value="{{ $produkJadi->id }}">
                <input type="hidden" name="bom_id" value="{{ $bom->id }}">

                <div class="row">
                    {{-- Kolom Kiri: Info Produksi --}}
                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Informasi Produksi</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    {{-- Form Group untuk Produk Jadi, BoM, dll. --}}
                                    <div class="form-group row align-items-center">
                                        <label for="produk_jadi_info" class="col-lg-4 col-md-12 col-form-label">Produk
                                            Jadi</label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="text" id="produk_jadi_info" class="form-control"
                                                value="{{ $produkJadi->kode_barang }} - {{ $produkJadi->nama_barang }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="bom_info" class="col-lg-4 col-md-12 col-form-label">BoM
                                            Digunakan</label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="text" id="bom_info" class="form-control"
                                                value="{{ $bom->deskripsi }} (ID: {{ $bom->id }})" readonly>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group row align-items-center">
                                        <label for="no_produksi" class="col-lg-4 col-md-12 col-form-label">No. Produksi
                                            <span class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="text" id="no_produksi"
                                                class="form-control @error('no_produksi') is-invalid @enderror"
                                                name="no_produksi" value="{{ old('no_produksi') }}"
                                                placeholder="Contoh: PROD/{{ date('Ymd') }}/001" required>
                                            @error('no_produksi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="batch" class="col-lg-4 col-md-12 col-form-label">Jumlah Batch <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="number" id="batch"
                                                class="form-control @error('batch') is-invalid @enderror" name="batch"
                                                value="{{ old('batch', 1) }}" min="1" required>
                                            @error('batch')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="tanggal" class="col-lg-4 col-md-12 col-form-label">Tanggal Produksi
                                            <span class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="datetime-local" id="tanggal"
                                                class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                                value="{{ old('tanggal', now()->format('Y-m-d\TH:i')) }}" required>
                                            @error('tanggal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="attachment" class="col-lg-4 col-md-12 col-form-label">Attachment</label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="file" id="attachment"
                                                class="form-control @error('attachment') is-invalid @enderror"
                                                name="attachment">
                                            @error('attachment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="keterangan" class="col-lg-4 col-md-12 col-form-label">Keterangan</label>
                                        <div class="col-lg-8 col-md-12">
                                            <textarea id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                                rows="3">{{ old('keterangan') }}</textarea>
                                            @error('keterangan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="harga_perunit" class="col-lg-4 col-md-12 col-form-label">Harga Satuan
                                            / Unit</label>
                                        <div class="col-lg-8 col-md-12">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" id="harga_perunit"
                                                    class="form-control @error('harga_perunit') is-invalid @enderror"
                                                    name="harga_perunit" value="{{ old('harga_perunit', 0) }}"
                                                    min="0" step="any">
                                            </div>
                                            @error('harga_perunit')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Kebutuhan Bahan & Kemasan --}}
                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Estimasi Kebutuhan</h4>
                                <p class="text-muted mt-1 mb-0"><small>Kebutuhan akan dihitung ulang saat Anda mengubah
                                        Jumlah Batch.</small></p>
                            </div>
                            <div class="card-content">
                                <div class="card-body" style="max-height: 550px; overflow-y: auto;">
                                    @error('materials')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered" id="material-table">
                                            <thead>
                                                <tr>
                                                    <th>Material</th>
                                                    <th class="text-center">
                                                        Kuantitas/Batch<br><small>({{ $produkJadi->unitSatuan?->nama_unit_satuan ?? 'N/A' }})</small>
                                                    </th>
                                                    <th class="text-center">Unit</th>
                                                    <th class="text-center">Stok Saat Ini</th>
                                                    <th class="text-center">Total Dibutuhkan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($requiredMaterials as $material)
                                                    <tr data-material-id="{{ $material['material_id'] }}">
                                                        <td>{{ $material['kode_barang'] }}
                                                            <br><small>{{ $material['nama_barang'] }}</small></td>
                                                        <td class="text-center qty-per-batch">
                                                            {{ rtrim(rtrim(number_format($material['qty_per_batch'], 4, ',', '.'), '0'), ',') }}
                                                        </td>
                                                        <td class="text-center">{{ $material['unit_satuan'] }}</td>
                                                        <td class="text-center current-stock">
                                                            {{ rtrim(rtrim(number_format($material['stok_saat_ini'], 4, ',', '.'), '0'), ',') }}
                                                        </td>
                                                        <td class="text-center required-qty fw-bold">0</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">BoM tidak
                                                            memiliki detail bahan.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total Kebutuhan Bahan:
                                                    </td>
                                                    <td id="total-required-sum" class="text-center fw-bold">0</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total Biaya Produksi:</td>
                                                    <td id="total-biaya-produksi" class="text-center fw-bold">Rp 0</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <h5 class="mt-4">Daftar Kemasan</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered" id="kemasan-table">
                                            <thead>
                                                <tr>
                                                    <th>Kemasan</th>
                                                    <th class="text-center">Kebutuhan/Batch</th>
                                                    <th class="text-center">Unit</th>
                                                    <th class="text-center">Total Dibutuhkan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($bom->kemasan as $kemasan)
                                                    <tr data-kemasan-id="{{ $kemasan->barang_id }}"
                                                        data-jumlah-per-batch="{{ $kemasan->jumlah }}">
                                                        <td>{{ $kemasan->barang->nama_barang }}</td>
                                                        <td class="text-center">
                                                            {{ rtrim(rtrim(number_format($kemasan->jumlah, 4, ',', '.'), '0'), ',') }}
                                                        </td>
                                                        <td class="text-center">
                                                            {{ $kemasan->unitSatuan->nama_unit_satuan }}</td>
                                                        <td class="text-center required-kemasan-qty fw-bold">0</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">Tidak ada
                                                            kemasan untuk BoM ini.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer pt-0 border-top-0">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('produksi.create') }}"
                                            class="btn btn-light-secondary me-1 mb-1">Ganti Produk</a>
                                        <button type="submit" class="btn btn-primary me-1 mb-1">Simpan Produksi</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('css')
    <style>
        .table-sm td,
        .table-sm th {
            padding: 0.4rem;
            font-size: 0.85rem;
        }

        .required-qty.text-danger,
        .required-kemasan-qty.text-danger {
            font-weight: bold;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Element References ---
            const batchInput = document.getElementById('batch');
            const hargaPerUnitInput = document.getElementById('harga_perunit');
            const materialTableBody = document.getElementById('material-table')?.querySelector('tbody');
            const totalRequiredSumCell = document.getElementById('total-required-sum');
            const totalBiayaProduksiCell = document.getElementById('total-biaya-produksi');
            const kemasanTableBody = document.getElementById('kemasan-table')?.querySelector('tbody');

            // --- Definisi Fungsi Kalkulasi ---
            function calculateRequiredItems() {
                if (!batchInput || !materialTableBody || !kemasanTableBody) return;

                const batchCount = parseInt(batchInput.value) || 0;
                const hargaPerUnit = parseFloat(hargaPerUnitInput.value) || 0;
                let totalMaterialSum = 0;

                // Kalkulasi Kebutuhan Material
                materialTableBody.querySelectorAll('tr[data-material-id]').forEach((row) => {
                    const qtyPerBatchText = row.querySelector('.qty-per-batch').textContent?.trim() || '0';
                    const qtyPerBatch = parseFloat(qtyPerBatchText.replace(/\./g, '').replace(',', '.')) ||
                        0;
                    const currentStockText = row.querySelector('.current-stock').textContent?.trim() || '0';
                    const currentStock = parseFloat(currentStockText.replace(/\./g, '').replace(',',
                        '.')) || 0;
                    const requiredQtyCell = row.querySelector('.required-qty');

                    const requiredQty = qtyPerBatch * batchCount;

                    requiredQtyCell.textContent = requiredQty.toLocaleString('id-ID', {
                        maximumFractionDigits: 4
                    });

                    if (requiredQty > currentStock) {
                        requiredQtyCell.classList.add('text-danger');
                    } else {
                        requiredQtyCell.classList.remove('text-danger');
                    }

                    totalMaterialSum += requiredQty;
                });

                if (totalRequiredSumCell) {
                    totalRequiredSumCell.textContent = totalMaterialSum.toLocaleString('id-ID', {
                        maximumFractionDigits: 4
                    });
                }

                // Kalkulasi Kebutuhan Kemasan
                kemasanTableBody.querySelectorAll('tr[data-kemasan-id]').forEach((row) => {
                    const jumlahPerBatch = parseFloat(row.dataset.jumlahPerBatch) || 0;
                    const requiredKemasanQty = jumlahPerBatch * batchCount;
                    row.querySelector('.required-kemasan-qty').textContent = requiredKemasanQty
                        .toLocaleString('id-ID', {
                            maximumFractionDigits: 4
                        });
                });

                // Kalkulasi Total Biaya Produksi
                if (totalBiayaProduksiCell) {
                    const totalBiaya = batchCount * hargaPerUnit;
                    totalBiayaProduksiCell.textContent = 'Rp ' + totalBiaya.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }

            // --- Inisialisasi dan Pemasangan Event Listener ---
            calculateRequiredItems(); // Panggil saat halaman dimuat

            if (batchInput) {
                batchInput.addEventListener('input', calculateRequiredItems);
            }
            if (hargaPerUnitInput) {
                hargaPerUnitInput.addEventListener('input', calculateRequiredItems);
            }
        });
    </script>
@endpush
