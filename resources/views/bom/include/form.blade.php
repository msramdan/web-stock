{{-- resources/views/bom/include/form.blade.php (Layout Mirip Transaksi) --}}
<section class="content">
    <div class="container-fluid">
        {{-- Baris Pertama: Info Utama & Input Material --}}
        <div class="row">
            {{-- Kolom 1: Produk Jadi & Deskripsi --}}
            <div class="col-md-4">
                <div class="card h-100 border">
                    <div class="card-body">
                        <div class="form-group mb-3"> {{-- Tambah margin bawah --}}
                            <label for="barang_id_produk" class="form-label fw-bold">{{ __('Barang (Produk Jadi)') }}
                                <span class="text-danger">*</span></label>
                            <select class="form-select @error('barang_id') is-invalid @enderror" name="barang_id"
                                id="barang_id_produk" required>
                                <option value="" selected disabled>-- {{ __('Pilih Barang Jadi') }} --</option>
                                @foreach ($produkJadi ?? [] as $produk)
                                    <option value="{{ $produk->id }}"
                                        {{ (isset($bom) && $bom->barang_id == $produk->id) || old('barang_id') == $produk->id ? 'selected' : '' }}>
                                        {{ $produk->kode_barang }} - {{ $produk->nama_barang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="deskripsi" class="form-label fw-bold">{{ __('Deskripsi') }} <span
                                    class="text-danger">*</span></label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                placeholder="{{ __('Deskripsi singkat Bill of Material') }}" rows="4" required>{{ isset($bom) ? $bom->deskripsi : old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom 2: Mungkin kosong atau info tambahan jika ada --}}
            <div class="col-md-4">
                <div class="alert alert-light-info color-info">
                    <i class="bi bi-info-circle-fill"></i> Pastikan Produk Jadi dan Material yang dipilih sesuai dengan
                    perusahaan yang aktif.
                </div>
            </div>

            {{-- Kolom 3: Input Material Cepat --}}
            <div class="col-md-4">
                <div class="card h-100 border">
                    <div class="card-header bg-light py-2">
                        <h6 class="card-title mb-0 fw-bold">Tambah Material</h6>
                    </div>
                    <div class="card-body">
                        {{-- Input Pilih Material --}}
                        <div class="form-group mb-2">
                            <label for="material_id_selector" class="form-label">{{ __('Pilih Material') }}</label>
                            <select class="form-select" id="material_id_selector">
                                <option value="">-- Pilih Material --</option>
                                {{-- Pastikan $barangMaterials ada dari ViewComposer/Controller --}}
                                @foreach ($barangMaterials ?? [] as $material)
                                    <option value="{{ $material->id }}" data-unit-id="{{ $material->unit_satuan_id }}"
                                        data-unit-nama="{{ $material->unitSatuan->nama_unit_satuan ?? '' }}">
                                        {{ $material->kode_barang }} - {{ $material->nama_barang }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="selected_material_unit_id">
                            <input type="hidden" id="selected_material_unit_nama">
                        </div>

                        {{-- Input Qty --}}
                        <div class="form-group mb-3">
                            <label for="material_qty" class="form-label">{{ __('Jumlah') }}</label>
                            <input type="number" id="material_qty" value="1" min="0.00000001" step="any"
                                class="form-control">
                        </div>

                        {{-- Tombol Tambah ke Tabel --}}
                        <div class="d-grid">
                            <button type="button" id="add_material_row_from_selector" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> {{ __('Tambahkan ke Daftar') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Baris Kedua: Tabel Daftar Material --}}
        <div class="row mt-4">
            <div class="col-lg-12">
                <h6 class="mb-3 fw-bold">Daftar Material Ditambahkan</h6>
                @error('materials')
                    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                        {{ $message }}
                        @if ($errors->has('materials.*'))
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->get('materials.*') as $materialErrors)
                                    @foreach ($materialErrors as $error)
                                        <li><small>{{ $error }}</small></li>
                                    @endforeach
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror
                <div class="card border">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="materials_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45%;">{{ __('Material') }} <span class="text-danger">*</span>
                                        </th>
                                        <th style="width: 20%;">{{ __('Jumlah') }} <span class="text-danger">*</span>
                                        </th>
                                        <th style="width: 25%;">{{ __('Unit Satuan') }} <span
                                                class="text-danger">*</span></th>
                                        <th style="width: 10%;" class="text-center">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="materials_tbody">
                                    {{-- Handling old input atau data edit --}}
                                    @php
                                        $materialsData = [];
                                        if (old('materials')) {
                                            $materialsData = old('materials');
                                        } elseif (isset($bom) && $bom->details && !$errors->has('materials.*')) {
                                            $materialsData = $bom->details
                                                ->map(
                                                    fn($d) => [
                                                        'id' => $d->id,
                                                        'barang_id' => $d->barang_id,
                                                        'jumlah' => $d->jumlah,
                                                        'unit_satuan_id' => $d->unit_satuan_id,
                                                        'unit_nama_selected' => $d->unitSatuan->nama_unit_satuan ?? '',
                                                    ],
                                                )
                                                ->toArray();
                                        }
                                    @endphp
                                    @forelse ($materialsData as $index => $materialItem)
                                        <tr id="material_row_{{ $index }}">
                                            <td>
                                                <select name="materials[{{ $index }}][barang_id]"
                                                    class="form-select material-select @error("materials.$index.barang_id") is-invalid @enderror"
                                                    required>
                                                    <option value="">-- Pilih Material --</option>
                                                    @foreach ($barangMaterials ?? [] as $material)
                                                        <option value="{{ $material->id }}"
                                                            data-unit-id="{{ $material->unit_satuan_id }}"
                                                            data-unit-nama="{{ $material->unitSatuan->nama_unit_satuan ?? '' }}"
                                                            {{ ($materialItem['barang_id'] ?? null) == $material->id ? 'selected' : '' }}>
                                                            {{ $material->kode_barang }} -
                                                            {{ $material->nama_barang }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @isset($materialItem['id'])
                                                    <input type="hidden" name="materials[{{ $index }}][detail_id]"
                                                        value="{{ $materialItem['id'] }}">
                                                @endisset
                                                @error("materials.$index.barang_id")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="any"
                                                    name="materials[{{ $index }}][jumlah]"
                                                    class="form-control quantity-input @error("materials.$index.jumlah") is-invalid @enderror"
                                                    value="{{ $materialItem['jumlah'] ?? 1 }}" min="0.00000001"
                                                    required>
                                                @error("materials.$index.jumlah")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                @php
                                                    $selectedUnitId = $materialItem['unit_satuan_id'] ?? null;
                                                    $selectedUnitNama = $materialItem['unit_nama_selected'] ?? '';
                                                    if (old('materials') && !$selectedUnitNama && $selectedUnitId) {
                                                        $unit = \App\Models\UnitSatuan::find($selectedUnitId);
                                                        $selectedUnitNama = $unit->nama_unit_satuan ?? '';
                                                    }
                                                @endphp
                                                <input type="hidden"
                                                    name="materials[{{ $index }}][unit_satuan_id]"
                                                    class="unit-id-input" value="{{ $selectedUnitId }}">
                                                <input type="text"
                                                    class="form-control unit-display @error("materials.$index.unit_satuan_id") is-invalid @enderror"
                                                    value="{{ $selectedUnitNama }}" readonly>
                                                @error("materials.$index.unit_satuan_id")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-material"
                                                    title="Hapus Material"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr id="no-material-row"
                                        style="{{ empty($materialsData) ? '' : 'display: none;' }}">
                                        <td colspan="4" class="text-center text-muted p-3">Belum ada material yang
                                            ditambahkan.</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- Akhir Container Fluid --}}
</section>

{{-- Template Hidden Row (Tidak ditampilkan tapi dibutuhkan JS) --}}
<table style="display:none;">
    <tr id="material_row_template">
        <td>
            <select name="materials[__INDEX__][barang_id]" class="form-select material-select" required disabled>
                <option value="">-- Pilih Material --</option>
                @foreach ($barangMaterials ?? [] as $material)
                    <option value="{{ $material->id }}" data-unit-id="{{ $material->unit_satuan_id }}"
                        data-unit-nama="{{ $material->unitSatuan->nama_unit_satuan ?? '' }}">
                        {{ $material->kode_barang }} - {{ $material->nama_barang }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </td>
        <td>
            <input type="number" step="any" name="materials[__INDEX__][jumlah]"
                class="form-control quantity-input" value="1" min="0.00000001" required disabled>
            <div class="invalid-feedback"></div>
        </td>
        <td>
            <input type="hidden" name="materials[__INDEX__][unit_satuan_id]" class="unit-id-input" disabled>
            <input type="text" class="form-control unit-display" readonly disabled>
            <div class="invalid-feedback"></div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-material" title="Hapus Material"><i
                    class="fas fa-trash"></i></button>
        </td>
    </tr>
</table>

{{-- Push CSS & JS --}}
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        #materials_table .form-control,
        #materials_table .form-select {
            min-height: calc(1.5em + .75rem + 2px);
        }

        #materials_table .invalid-feedback {
            font-size: 0.8em;
        }

        #materials_table td {
            vertical-align: middle;
        }
    </style>
@endpush
@push('js')
    <script>
        // Pastikan jQuery sudah dimuat
        if (typeof jQuery == 'undefined') {
            /* ... load jQuery ... */ } else {
            initializeBomFormScript();
        }

        function initializeBomFormScript() {
            $(document).ready(function() {
                let materialIndex = $('#materials_tbody tr').length;

                // Simpan data unit dari material terpilih di selector cepat
                $('#material_id_selector').on('change', function() {
                    let selectedOption = $(this).find('option:selected');
                    $('#selected_material_unit_id').val(selectedOption.data('unit-id') || '');
                    $('#selected_material_unit_nama').val(selectedOption.data('unit-nama') || '');
                }).trigger('change');

                // Update unit di baris tabel (jika diubah manual)
                function updateUnitInTable(selectElement) {
                    /* ... (sama) ... */
                    let selectedOption = $(selectElement).find('option:selected');
                    let unitId = selectedOption.data('unit-id');
                    let unitNama = selectedOption.data('unit-nama');
                    let row = $(selectElement).closest('tr');
                    row.find('.unit-id-input').val(unitId || '');
                    row.find('.unit-display').val(unitNama || '');
                    $(selectElement).removeClass('is-invalid');
                    row.find('.unit-display').removeClass('is-invalid');
                }
                // Cek tabel kosong
                function checkEmptyTable() {
                    /* ... (sama) ... */
                    if ($('#materials_tbody tr').length === 0) {
                        $('#no-material-row').show();
                    } else {
                        $('#no-material-row').hide();
                    }
                }
                checkEmptyTable();
                // Inisialisasi unit untuk baris yang sudah ada
                $('#materials_tbody .material-select').each(function() {
                    updateUnitInTable(this);
                });

                // Tombol "Tambahkan ke Daftar"
                $('#add_material_row_from_selector').on('click', function() {
                    let selectedMaterialId = $('#material_id_selector').val();
                    let selectedMaterialText = $('#material_id_selector option:selected').text();
                    let qty = $('#material_qty').val();
                    let unitId = $('#selected_material_unit_id').val();
                    let unitNama = $('#selected_material_unit_nama').val();

                    if (!selectedMaterialId) {
                        alert('Pilih material.');
                        $('#material_id_selector').focus();
                        return;
                    }
                    if (!qty || parseFloat(qty) <= 0) {
                        alert('Jumlah > 0.');
                        $('#material_qty').focus();
                        return;
                    }
                    if (!unitId) {
                        alert('Material tidak punya unit satuan.');
                        return;
                    }

                    let existingRow = null;
                    $('#materials_tbody tr').each(function() {
                        if ($(this).find('.material-select').val() == selectedMaterialId) {
                            existingRow = $(this);
                            return false;
                        }
                    });

                    if (existingRow) {
                        let currentQtyInput = existingRow.find('.quantity-input');
                        currentQtyInput.val((parseFloat(currentQtyInput.val()) || 0) + parseFloat(qty));
                        alert('Jumlah material ' + selectedMaterialText.trim() + ' berhasil ditambahkan.');
                    } else {
                        let templateContent = $('#material_row_template').html();
                        let newRowHtml = '<tr id="material_row_' + materialIndex + '">' + templateContent
                            .replace(/__INDEX__/g, materialIndex) + '</tr>';
                        $('#materials_tbody').append(newRowHtml);
                        let newRowElement = $('#material_row_' + materialIndex);
                        newRowElement.find('input, select').prop('disabled', false);
                        newRowElement.find('.material-select').val(selectedMaterialId);
                        newRowElement.find('.quantity-input').val(qty);
                        newRowElement.find('.unit-id-input').val(unitId);
                        newRowElement.find('.unit-display').val(unitNama);
                        newRowElement.find('.material-select').on('change', function() {
                            updateUnitInTable(this);
                        });
                        newRowElement.find('.quantity-input, .material-select').on('input change',
                        function() {
                            $(this).removeClass('is-invalid');
                        });
                        materialIndex++;
                    }
                    $('#material_id_selector').val('').trigger('change');
                    $('#material_qty').val(1);
                    checkEmptyTable();
                });

                // Hapus baris
                $('#materials_table').on('click', '.remove-material', function() {
                    $(this).closest('tr').remove();
                    checkEmptyTable();
                });
                // Update unit jika select di tabel diubah
                $('#materials_tbody').on('change', '.material-select', function() {
                    updateUnitInTable(this);
                });
                // Reset error di tabel
                $('#materials_tbody').on('input change', '.quantity-input, .material-select', function() {
                    $(this).removeClass('is-invalid');
                });
            });
        }
    </script>
@endpush
