@extends('layouts.app')

@section('title', 'Edit Produksi - ' . $produksi->no_produksi)

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>Edit Produksi</h3>
                    <p class="text-subtitle text-muted">
                        Ubah perintah produksi <strong>{{ $produksi->no_produksi }}</strong>.
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produksi.index') }}">Produksi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
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

            <form action="{{ route('produksi.update', $produksi->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="barang_id" value="{{ $produksi->barang_id }}">
                <input type="hidden" name="bom_id" value="{{ $produksi->bom_id }}">

                <div class="row">
                    {{-- Kolom Kiri: Informasi Produksi --}}
                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Informasi Produksi</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    {{-- Info Produk Jadi & BoM (Sama) --}}
                                    <div class="form-group row align-items-center">
                                        <label for="produk_jadi_info" class="col-lg-4 col-md-12 col-form-label">Produk
                                            Jadi</label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="text" id="produk_jadi_info" class="form-control"
                                                value="{{ $produksi->produkJadi->kode_barang }} - {{ $produksi->produkJadi->nama_barang }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <label for="bom_info" class="col-lg-4 col-md-12 col-form-label">BoM
                                            Digunakan</label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="text" id="bom_info" class="form-control"
                                                value="{{ $produksi->bom->deskripsi }} (ID: {{ $produksi->bom_id }})"
                                                readonly>
                                            <small class="text-muted">BoM tidak dapat diubah.</small>
                                        </div>
                                    </div>
                                    <hr>
                                    {{-- No Produksi (Sama) --}}
                                    <div class="form-group row align-items-center">
                                        <label for="no_produksi" class="col-lg-4 col-md-12 col-form-label">No. Produksi
                                            <span class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="text" id="no_produksi"
                                                class="form-control @error('no_produksi') is-invalid @enderror"
                                                name="no_produksi" value="{{ old('no_produksi', $produksi->no_produksi) }}"
                                                required>
                                            @error('no_produksi')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Batch (Sama, tapi sekarang jadi input utama kalkulasi) --}}
                                    <div class="form-group row align-items-center">
                                        <label for="batch" class="col-lg-4 col-md-12 col-form-label">Jumlah Batch <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="number" id="batch"
                                                class="form-control @error('batch') is-invalid @enderror" name="batch"
                                                value="{{ old('batch', $produksi->batch) }}" min="1" required>
                                            @error('batch')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Tanggal Produksi (Sama) --}}
                                    <div class="form-group row align-items-center">
                                        <label for="tanggal" class="col-lg-4 col-md-12 col-form-label">Tanggal Produksi
                                            <span class="text-danger">*</span></label>
                                        <div class="col-lg-8 col-md-12">
                                            <input type="datetime-local" id="tanggal"
                                                class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                                value="{{ old('tanggal', $produksi->tanggal->format('Y-m-d\TH:i')) }}"
                                                required>
                                            @error('tanggal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Target Kuantitas (HAPUS BAGIAN INI) --}}
                                    {{-- <div class="form-group row align-items-center"> ... </div> --}}

                                    {{-- Attachment (Sama) --}}
                                    <div class="form-group row align-items-center">
                                        <label for="attachment" class="col-lg-4 col-md-12 col-form-label">Lampiran</label>
                                        <div class="col-lg-8 col-md-12">
                                            @if ($attachmentUrl)
                                                <div class="mb-2">
                                                    <a href="{{ $attachmentUrl }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-paperclip"></i> Lihat Lampiran
                                                    </a>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="remove_attachment" id="remove_attachment" value="1">
                                                        <label class="form-check-label" for="remove_attachment">
                                                            Hapus lampiran saat menyimpan
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" id="attachment"
                                                class="form-control @error('attachment') is-invalid @enderror"
                                                name="attachment">
                                            @error('attachment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Keterangan (Sama) --}}
                                    <div class="form-group row">
                                        <label for="keterangan"
                                            class="col-lg-4 col-md-12 col-form-label">Keterangan</label>
                                        <div class="col-lg-8 col-md-12">
                                            <textarea id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                                rows="3">{{ old('keterangan', $produksi->keterangan) }}</textarea>
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
                                        Jumlah Batch.</small></p> {{-- Ubah teks --}}
                            </div>
                            <div class="card-content">
                                <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                                    @error('materials')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered" id="material-table">
                                            <thead>
                                                <tr>
                                                    <th>Material</th>
                                                    {{-- Ubah Header Kolom --}}
                                                    <th class="text-center">Kuantitas/Batch
                                                        <br><small>({{ $produksi->produkJadi->unitSatuan?->nama_unit_satuan ?? 'N/A' }})</small>
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
                                                        {{-- Ubah Class & Tampilkan Qty/Batch --}}
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
                                            {{-- Footer Total (Sama) --}}
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total Keseluruhan:</td>
                                                    <td id="total-required-sum" class="text-center fw-bold">0</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer pt-0 border-top-0">
                                    <div class="d-flex justify-content-end">
                                        {{-- Kembali ke halaman show, bukan index --}}
                                        <a href="{{ route('produksi.show', $produksi->id) }}"
                                            class="btn btn-light-secondary me-1 mb-1">Kembali</a>
                                        <button type="submit" class="btn btn-primary me-1 mb-1">Simpan Perubahan</button>
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
    {{-- CSS sama seperti create --}}
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

{{-- JavaScript Baru (Sama seperti create, hanya event listener focus/blur untuk batch tidak perlu) --}}
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Element References ---
            const batchInput = document.getElementById('batch'); // <-- Ganti ke input batch
            const materialTableBody = document.getElementById('material-table')?.querySelector('tbody');
            const totalRequiredSumCell = document.getElementById('total-required-sum');

            // --- Log Pemeriksaan Elemen ---
            if (!batchInput) {
                console.error('DEBUG: Element #batch NOT FOUND!');
            }
            if (!materialTableBody) {
                console.error('DEBUG: Element #material-table tbody NOT FOUND!');
            }
            if (!totalRequiredSumCell) {
                console.error('DEBUG: Element #total-required-sum NOT FOUND!');
            }

            // --- Definisi Fungsi: calculateRequiredMaterials ---
            function calculateRequiredMaterials() {
                // console.log('DEBUG: Running calculateRequiredMaterials...');
                if (!batchInput || !materialTableBody || !totalRequiredSumCell) {
                    // console.error('DEBUG: Missing critical element(s) for calculation. Aborting.');
                    return;
                }

                // Parse input BATCH
                const batchCount = parseInt(batchInput.value) || 0; // Ambil nilai batch
                // console.log('DEBUG: Batch Parsed:', batchCount);

                let totalSum = 0; // Inisialisasi total sum

                materialTableBody.querySelectorAll('tr[data-material-id]').forEach((row) => {
                    const qtyPerBatchCell = row.querySelector('.qty-per-batch'); // <-- Cari class baru
                    const currentStockCell = row.querySelector('.current-stock');
                    const requiredQtyCell = row.querySelector('.required-qty');

                    if (!qtyPerBatchCell || !currentStockCell || !requiredQtyCell) {
                        // console.warn('Skipping row: Missing cells.');
                        return;
                    }

                    // Parse Qty/Batch (dari BoM, format ID)
                    const qtyPerBatchText = qtyPerBatchCell.textContent?.trim() || '0';
                    const qtyPerBatch = parseFloat(qtyPerBatchText.replace(/\./g, '').replace(',', '.')) ||
                        0;

                    // Parse Current Stock (format ID)
                    const currentStockText = currentStockCell.textContent?.trim() || '0';
                    const currentStock = parseFloat(currentStockText.replace(/\./g, '').replace(',',
                        '.')) || 0;

                    // Calculate required quantity based on BATCH
                    const requiredQty = qtyPerBatch * batchCount; // <-- Kalkulasi baru
                    // console.log(`DEBUG: Row - Qty/Batch: ${qtyPerBatch}, Required: ${requiredQty}`);

                    requiredQtyCell.textContent = requiredQty.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 4
                    });

                    if (requiredQty > currentStock) {
                        requiredQtyCell.classList.add('text-danger');
                    } else {
                        requiredQtyCell.classList.remove('text-danger');
                    }

                    if (!isNaN(requiredQty)) {
                        totalSum += requiredQty;
                    }
                    // console.log(`DEBUG: Row - Current Total Sum: ${totalSum}`);
                });

                // Update Total di Footer
                // console.log('DEBUG: Final Total Sum Calculated:', totalSum);
                totalRequiredSumCell.textContent = totalSum.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 4
                });
            }

            // --- Inisialisasi dan Pemasangan Event Listener ---

            // Hitung kebutuhan bahan saat halaman dimuat
            if (materialTableBody && totalRequiredSumCell) {
                calculateRequiredMaterials();
            }

            // Event listener untuk perubahan BATCH input
            if (batchInput) {
                batchInput.addEventListener('input', calculateRequiredMaterials);
                batchInput.addEventListener('change', calculateRequiredMaterials); // Fallback
            }

        }); // End DOMContentLoaded
    </script>
@endpush
