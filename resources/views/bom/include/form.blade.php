{{-- resources/views/bom/include/form.blade.php --}}

<div class="row mb-2">
    {{-- Input Barang (Produk Jadi) --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="barang_id_produk">{{ __('Barang (Produk Jadi)') }}</label>
            <select class="form-select @error('barang_id') is-invalid @enderror" name="barang_id" id="barang_id_produk"
                required>
                <option value="" selected disabled>-- {{ __('Pilih Barang Jadi') }} --</option>
                @isset($produkJadi)
                    @foreach ($produkJadi as $produk)
                        <option value="{{ $produk->id }}"
                            {{ (isset($bom) && $bom->barang_id == $produk->id) || old('barang_id') == $produk->id ? 'selected' : '' }}>
                            {{ $produk->kode_barang }} - {{ $produk->nama_barang }}
                        </option>
                    @endforeach
                @endisset
            </select>
            @error('barang_id')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    {{-- Input Deskripsi --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="deskripsi">{{ __('Deskripsi') }}</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                placeholder="{{ __('Deskripsi BoM') }}" required>{{ isset($bom) ? $bom->deskripsi : old('deskripsi') }}</textarea>
            @error('deskripsi')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>

{{-- Bagian Input Dinamis Material --}}
<hr>
<h5>{{ __('Material / Komponen') }}</h5>

{{-- === Tombol Tambah Material SEHARUSNYA DI SINI === --}}
<div class="mb-2"> {{-- Tambahkan div dengan margin bawah --}}
    <button type="button" class="btn btn-success btn-sm" id="add_material_row">
        <i class="fas fa-plus"></i> {{ __('Tambah Material') }}
    </button>
</div>
{{-- === Akhir Penempatan Tombol === --}}


@error('materials') {{-- Tampilkan error validasi untuk materials --}}
    <div class="alert alert-danger mt-2 py-2">
        {{ $message }}
        @if ($errors->has('materials.*'))
            <ul class="mb-0 ps-3">
                @foreach ($errors->get('materials.*') as $materialErrors)
                    @foreach ($materialErrors as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @endforeach
            </ul>
        @endif
    </div>
@enderror

<div class="table-responsive"> {{-- Dihilangkan mt-1 --}}
    <table class="table table-bordered" id="materials_table">
        <thead>
            <tr>
                <th style="width: 45%;">{{ __('Material') }}</th>
                <th style="width: 20%;">{{ __('Jumlah') }}</th>
                <th style="width: 25%;">{{ __('Unit Satuan') }}</th>
                <th style="width: 10%;">{{ __('Aksi') }}</th>
            </tr>
        </thead>
        <tbody id="materials_tbody">
            {{-- Handling old input --}}
            @if (old('materials'))
                @foreach (old('materials') as $index => $oldMaterial)
                    <tr id="material_row_{{ $index }}">
                        <td>
                            <select name="materials[{{ $index }}][barang_id]"
                                class="form-select material-select" required>
                                <option value="">-- Pilih Material --</option>
                                @isset($barangMaterials)
                                    @foreach ($barangMaterials as $material)
                                        <option value="{{ $material->id }}" data-unit-id="{{ $material->unit_satuan_id }}"
                                            data-unit-nama="{{ $material->unitSatuan->nama_unit_satuan ?? '' }}"
                                            {{ ($oldMaterial['barang_id'] ?? null) == $material->id ? 'selected' : '' }}>
                                            {{ $material->kode_barang }} - {{ $material->nama_barang }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                            @error("materials.$index.barang_id")
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="number" step="any" name="materials[{{ $index }}][jumlah]"
                                class="form-control quantity-input" value="{{ $oldMaterial['jumlah'] ?? 1 }}"
                                min="0.01" required>
                            @error("materials.$index.jumlah")
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="hidden" name="materials[{{ $index }}][unit_satuan_id]"
                                class="unit-id-input" value="{{ $oldMaterial['unit_satuan_id'] ?? '' }}">
                            @php
                                $unitNamaOld = '';
                                if (isset($oldMaterial['unit_satuan_id']) && isset($barangMaterials)) {
                                    $foundMaterial = $barangMaterials->firstWhere(
                                        'unit_satuan_id',
                                        $oldMaterial['unit_satuan_id'],
                                    );
                                    if ($foundMaterial) {
                                        $unitNamaOld = $foundMaterial->unitSatuan->nama_unit_satuan ?? '';
                                    }
                                    if (empty($unitNamaOld)) {
                                        $foundUnit = \App\Models\UnitSatuan::find($oldMaterial['unit_satuan_id']);
                                        $unitNamaOld = $foundUnit->nama_unit_satuan ?? '';
                                    }
                                }
                            @endphp
                            <input type="text" class="form-control unit-display" value="{{ $unitNamaOld }}"
                                readonly>
                            @error("materials.$index.unit_satuan_id")
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-material"><i
                                    class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
                {{-- Handling data dari edit --}}
            @elseif (isset($bom) && $bom->details && !$errors->has('materials.*'))
                {{-- Jangan tampilkan jika ada error validasi old input --}}
                @foreach ($bom->details as $index => $detail)
                    <tr id="material_row_{{ $index }}">
                        <td>
                            <select name="materials[{{ $index }}][barang_id]"
                                class="form-select material-select" required>
                                <option value="">-- Pilih Material --</option>
                                @isset($barangMaterials)
                                    @foreach ($barangMaterials as $material)
                                        <option value="{{ $material->id }}" data-unit-id="{{ $material->unit_satuan_id }}"
                                            data-unit-nama="{{ $material->unitSatuan->nama_unit_satuan ?? '' }}"
                                            {{ $detail->barang_id == $material->id ? 'selected' : '' }}>
                                            {{ $material->kode_barang }} - {{ $material->nama_barang }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                            <input type="hidden" name="materials[{{ $index }}][detail_id]"
                                value="{{ $detail->id }}">
                        </td>
                        <td>
                            <input type="number" step="any" name="materials[{{ $index }}][jumlah]"
                                class="form-control quantity-input" value="{{ $detail->jumlah }}" min="0.01"
                                required>
                        </td>
                        <td>
                            <input type="hidden" name="materials[{{ $index }}][unit_satuan_id]"
                                class="unit-id-input" value="{{ $detail->unit_satuan_id }}">
                            <input type="text" class="form-control unit-display"
                                value="{{ $detail->unitSatuan->nama_unit_satuan ?? '' }}" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-material"><i
                                    class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

{{-- Hidden row template for JavaScript --}}
<table style="display:none;">
    <tr id="material_row_template">
        <td>
            <select name="materials[__INDEX__][barang_id]" class="form-select material-select" required disabled>
                <option value="">-- Pilih Material --</option>
                @isset($barangMaterials)
                    @foreach ($barangMaterials as $material)
                        <option value="{{ $material->id }}" data-unit-id="{{ $material->unit_satuan_id }}"
                            data-unit-nama="{{ $material->unitSatuan->nama_unit_satuan ?? '' }}">
                            {{ $material->kode_barang }} - {{ $material->nama_barang }}
                        </option>
                    @endforeach
                @endisset
            </select>
        </td>
        <td>
            <input type="number" step="any" name="materials[__INDEX__][jumlah]" class="form-control quantity-input"
                value="1" min="0.01" required disabled>
        </td>
        <td>
            <input type="hidden" name="materials[__INDEX__][unit_satuan_id]" class="unit-id-input" disabled>
            <input type="text" class="form-control unit-display" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-material"><i
                    class="fas fa-trash"></i></button>
        </td>
    </tr>
</table>

{{-- Kode @push tidak diubah, hanya posisi tombol di atas --}}
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        #materials_table .form-control,
        #materials_table .form-select {
            /* min-height: 38px; */
        }

        #materials_table .text-xs {
            font-size: 0.75rem;
        }
    </style>
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
@endpush

@push('js')
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script>
        if (typeof jQuery == 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';
            script.integrity =
                'sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==';
            script.crossOrigin = 'anonymous';
            script.referrerPolicy = 'no-referrer';
            document.head.appendChild(script);
            script.onload = function() {
                initializeBomFormScript();
            };
        } else {
            initializeBomFormScript();
        }

        function initializeBomFormScript() {
            $(document).ready(function() {
                let materialIndex = $('#materials_tbody tr').length > 0 ? $('#materials_tbody tr').length : 0;

                function updateUnit(selectElement) {
                    let selectedOption = selectElement.find('option:selected');
                    let unitId = selectedOption.data('unit-id');
                    let unitNama = selectedOption.data('unit-nama');
                    let row = selectElement.closest('tr');
                    row.find('.unit-id-input').val(unitId || '');
                    row.find('.unit-display').val(unitNama || '');
                }

                $('#materials_tbody .material-select').each(function() {
                    updateUnit($(this));
                    // $(this).select2({ dropdownParent: $(this).parent() });
                });

                $('#add_material_row').on('click', function() {
                    let templateContent = $('#material_row_template').html();
                    let newRowHtml = '<tr id="material_row_' + materialIndex + '">' + templateContent
                        .replace(/__INDEX__/g, materialIndex) + '</tr>';
                    $('#materials_tbody').append(newRowHtml);

                    let newRowElement = $('#material_row_' + materialIndex);
                    newRowElement.find('input, select').prop('disabled', false);

                    // newRowElement.find('.material-select').select2({ dropdownParent: newRowElement.find('.material-select').parent() });

                    newRowElement.find('.material-select').on('change', function() {
                        updateUnit($(this));
                    });
                    materialIndex++;
                });

                $('#materials_table').on('click', '.remove-material', function() {
                    $(this).closest('tr').remove();
                });

                $('#materials_tbody').on('change', '.material-select', function() {
                    updateUnit($(this));
                });
            });
        }
    </script>
@endpush
