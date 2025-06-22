@extends('layouts.app')

@section('title', __('Pilih Produk untuk Produksi'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('Pilih Produk Jadi') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Pilih produk jadi yang ingin Anda produksi.') }}
                    </p>
                </div>
                <x-breadcrumb>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produksi.index') }}">{{ __('Produksi') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Pilih Produk') }}</li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <x-alert></x-alert>
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Pilih Produk dan BoM</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                @if ($produkJadiList->isEmpty())
                                    <div class="alert alert-light-warning color-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Tidak ada Produk Jadi dengan BoM.
                                    </div>
                                    <a href="{{ route('bom.create') }}" class="btn btn-primary mt-2">Buat BoM Baru</a>
                                @else
                                    <form id="productionForm" class="form form-horizontal"
                                        action="{{ route('produksi.create') }}" method="GET">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="barang_id">Produk Jadi</label>
                                                </div>
                                                <div class="col-md-8 form-group">
                                                    <select class="form-select" id="barang_id" name="barang_id" required>
                                                        <option value="" disabled selected>-- Pilih Produk Jadi --
                                                        </option>
                                                        @foreach ($produkJadiList as $id => $nama)
                                                            <option value="{{ $id }}"
                                                                {{ old('barang_id') == $id ? 'selected' : '' }}>
                                                                {{ $nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="bom_id">Pilih BoM</label>
                                                </div>
                                                <div class="col-md-8 form-group">
                                                    <select class="form-select" id="bom_id" name="bom_id" required
                                                        {{ !old('barang_id') ? 'disabled' : '' }}>
                                                        <option value="" disabled selected>-- Pilih BoM --</option>
                                                        @if (old('barang_id'))
                                                            @php
                                                                $boms = DB::table('bom')
                                                                    ->where('barang_id', old('barang_id'))
                                                                    ->where('company_id', session('sessionCompany'))
                                                                    ->orderBy('nama_bom', 'asc')
                                                                    ->get(['id', 'nama_bom']);
                                                            @endphp
                                                            @foreach ($boms as $bom)
                                                                <option value="{{ $bom->id }}"
                                                                    {{ old('bom_id') == $bom->id ? 'selected' : '' }}>
                                                                    {{ $bom->nama_bom }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>

                                                <div class="col-sm-12 d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-primary me-1 mb-1" id="submitBtn"
                                                        {{ !old('bom_id') ? 'disabled' : '' }}>Lanjut</button>
                                                    <a href="{{ route('produksi.index') }}"
                                                        class="btn btn-light-secondary me-1 mb-1">Batal</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Initialize with old input if exists
            @if (old('barang_id'))
                loadBoms({{ old('barang_id') }});
            @endif

            $('#barang_id').change(function() {
                var barangId = $(this).val();
                if (barangId) {
                    loadBoms(barangId);
                } else {
                    $('#bom_id').empty().append(
                            '<option value="" disabled selected>-- Pilih BoM --</option>')
                        .prop('disabled', true);
                    $('#submitBtn').prop('disabled', true);
                }
            });

            $('#bom_id').change(function() {
                $('#submitBtn').prop('disabled', !$(this).val());
            });

            function loadBoms(barangId) {
                $.ajax({
                    // PERBAIKAN: Menggunakan nama route yang benar dari file web.php Anda
                    url: '{{ route('produksi.getBoms') }}',

                    type: 'GET',
                    data: {
                        barang_id: barangId
                    },
                    success: function(data) {
                        $('#bom_id').empty().append(
                            '<option value="" disabled selected>-- Pilih BoM --</option>');
                        if (data.length > 0) {
                            $('#bom_id').prop('disabled', false);
                            $.each(data, function(key, value) {
                                // Menggunakan deskripsi sesuai kode asli
                                $('#bom_id').append($('<option>', {
                                    value: value.id,
                                    text: value.deskripsi
                                }));
                            });
                        } else {
                            $('#bom_id').prop('disabled', true);
                            alert('Produk ini tidak memiliki BoM yang tersedia.');
                        }
                        $('#submitBtn').prop('disabled', true);
                    }
                });
            }
        });
    </script>
@endpush
