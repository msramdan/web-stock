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
            <x-alert></x-alert> {{-- Tampilkan error global atau stok --}}
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
                                            <small class="text-muted">Pastikan BoM ini sudah sesuai.</small>
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
                                        <label for="batch" class="col-lg-4 col-md-12 col-form-label">Batch <span
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
                                        <label for="qty_target" class="col-lg-4 col-md-12 col-form-label">Target Kuantitas
                                            ({{ $produkJadi->unitSatuan?->nama_unit_satuan ?? 'N/A' }}) <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="number" id="qty_target"
                                                class="form-control @error('qty_target') is-invalid @enderror"
                                                name="qty_target" value="{{ old('qty_target', 1) }}" step="any"
                                                min="0.0001" required>
                                            @error('qty_target')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="attachment"
                                            class="col-lg-4 col-md-12 col-form-label">Attachment</label>
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
                                        <label for="keterangan"
                                            class="col-lg-4 col-md-12 col-form-label">Keterangan</label>
                                        <div class="col-lg-8 col-md-12">
                                            <textarea id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                                rows="3">{{ old('keterangan') }}</textarea>
                                            @error('keterangan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Kebutuhan Bahan --}}
                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Estimasi Kebutuhan Bahan</h4>
                                <p class="text-muted mt-1 mb-0"><small>Kebutuhan akan dihitung ulang saat Anda mengubah
                                        Target Kuantitas.</small></p>
                            </div>
                            <div class="card-content">
                                <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                                    @error('materials')
                                        {{-- Error jika ada masalah di sisi server --}}
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered" id="material-table">
                                            <thead>
                                                <tr>
                                                    <th>Material</th>
                                                    <th class="text-center">Qty/Unit
                                                        <br><small>({{ $produkJadi->unitSatuan?->nama_unit_satuan ?? 'N/A' }})</small>
                                                    </th>
                                                    <th class="text-center">Unit</th>
                                                    <th class="text-center">Stok Saat Ini</th>
                                                    <th class="text-center">Total Dibutuhkan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($requiredMaterials as $material)
                                                    <tr data-material-id="{{ $material['material_id'] }}">
                                                        <td>
                                                            {{ $material['kode_barang'] }} <br>
                                                            <small>{{ $material['nama_barang'] }}</small>
                                                        </td>
                                                        <td class="text-center qty-per-unit">
                                                            {{ $material['qty_per_unit'] }}
                                                        </td>
                                                        <td class="text-center">{{ $material['unit_satuan'] }}</td>
                                                        <td class="text-center current-stock">
                                                            {{ $material['stok_saat_ini'] }}
                                                        </td>
                                                        <td class="text-center required-qty fw-bold">0</td>
                                                        {{-- Akan dihitung JS --}}
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">BoM tidak
                                                            memiliki detail bahan.</td>
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
    {{-- Tambahkan CSS jika perlu --}}
    <style>
        .table-sm td,
        .table-sm th {
            padding: 0.4rem;
            font-size: 0.85rem;
        }

        .required-qty.text-danger {
            font-weight: bold;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qtyTargetInput = document.getElementById('qty_target');
            const materialTableBody = document.getElementById('material-table')?.querySelector('tbody');

            function calculateRequiredMaterials() {
                const targetQty = parseFloat(qtyTargetInput.value) || 0;

                if (!materialTableBody) return;

                materialTableBody.querySelectorAll('tr[data-material-id]').forEach(row => {
                    const qtyPerUnitText = row.querySelector('.qty-per-unit')?.textContent?.trim() || '0';
                    const currentStockText = row.querySelector('.current-stock')?.textContent?.trim() ||
                    '0';

                    // Parsing angka
                    const qtyPerUnit = parseFloat(qtyPerUnitText.replace(',', '.')) ||
                    0; // hanya ganti koma ke titik
                    const currentStock = parseFloat(currentStockText.replace(/\./g, '').replace(',',
                        '.')) || 0; // hapus titik, ubah koma ke titik

                    const requiredQtyCell = row.querySelector('.required-qty');

                    console.log('qtyPerUnitText:', qtyPerUnitText);
                    console.log('qtyPerUnit:', qtyPerUnit);
                    console.log('targetQty:', targetQty);

                    if (requiredQtyCell) {
                        const requiredQty = qtyPerUnit * targetQty;

                        // Tampilkan dengan format Indonesia
                        requiredQtyCell.textContent = requiredQty.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 4
                        });

                        // Tambahkan/hapus class text-danger jika stok tidak cukup
                        if (requiredQty > currentStock) {
                            requiredQtyCell.classList.add('text-danger');
                        } else {
                            requiredQtyCell.classList.remove('text-danger');
                        }
                    }
                });
            }

            // Hitung saat halaman dimuat
            calculateRequiredMaterials();

            // Hitung ulang saat input Target Kuantitas berubah
            qtyTargetInput.addEventListener('input', calculateRequiredMaterials);
            qtyTargetInput.addEventListener('change',
                calculateRequiredMaterials); // Untuk browser yg tidak trigger 'input' di type number
        });
    </script>
@endpush
