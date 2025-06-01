<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="tgl_pengajuan" class="form-label">Tanggal Pengajuan <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control @error('tgl_pengajuan') is-invalid @enderror"
                id="tgl_pengajuan" name="tgl_pengajuan"
                value="{{ old('tgl_pengajuan', isset($permintaanBarang) ? \Carbon\Carbon::parse($permintaanBarang->tgl_pengajuan)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                required>
            @error('tgl_pengajuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="no_permintaan_barang" class="form-label">No. Permintaan Barang <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control @error('no_permintaan_barang') is-invalid @enderror"
                id="no_permintaan_barang" name="no_permintaan_barang"
                value="{{ old('no_permintaan_barang', $permintaanBarang->no_permintaan_barang ?? '') }}" maxlength="50"
                required>
            @error('no_permintaan_barang')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group mb-3">
            <label for="nama_supplier" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('nama_supplier') is-invalid @enderror" id="nama_supplier"
                name="nama_supplier" value="{{ old('nama_supplier', $permintaanBarang->nama_supplier ?? '') }}"
                maxlength="150" required>
            @error('nama_supplier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label for="nama_bank" class="form-label">Nama Bank</label>
            <input type="text" class="form-control @error('nama_bank') is-invalid @enderror" id="nama_bank"
                name="nama_bank" value="{{ old('nama_bank', $permintaanBarang->nama_bank ?? '') }}" maxlength="100">
            @error('nama_bank')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label for="account_name_supplier" class="form-label">Nama Akun Supplier</label>
            <input type="text" class="form-control @error('account_name_supplier') is-invalid @enderror"
                id="account_name_supplier" name="account_name_supplier"
                value="{{ old('account_name_supplier', $permintaanBarang->account_name_supplier ?? '') }}"
                maxlength="150">
            @error('account_name_supplier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label for="account_number_supplier" class="form-label">No. Rekening Supplier</label>
            <input type="text" class="form-control @error('account_number_supplier') is-invalid @enderror"
                id="account_number_supplier" name="account_number_supplier"
                value="{{ old('account_number_supplier', $permintaanBarang->account_number_supplier ?? '') }}"
                maxlength="25">
            @error('account_number_supplier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mb-3">
    <label for="keterangan" class="form-label">Keterangan</label>
    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
        rows="3">{{ old('keterangan', $permintaanBarang->keterangan ?? '') }}</textarea>
    @error('keterangan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr>
<h5 class="mb-3">Detail Barang Permintaan</h5>
@error('details')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div class="table-responsive">
    <table class="table table-bordered" id="detailPermintaanTable">
        <thead>
            <tr>
                <th style="width: 30%;">Barang <span class="text-danger">*</span></th>
                <th style="width: 10%;">Stok Akhir</th>
                <th style="width: 15%;">Jumlah Pesanan <span class="text-danger">*</span></th>
                <th style="width: 15%;">Satuan <span class="text-danger">*</span></th>
                <th style="width: 15%;">Harga/Satuan <span class="text-danger">*</span></th>
                <th style="width: 15%;">Total Harga</th>
                <th style="width: 5%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="detail_permintaan_body">
            @if (old('details', isset($permintaanBarang) ? $permintaanBarang->details : []))
                @foreach (old('details', isset($permintaanBarang) ? $permintaanBarang->details->toArray() : []) as $index => $detail)
                    <tr class="detail-row">
                        <td>
                            <select name="details[{{ $index }}][barang_id]"
                                class="form-select form-select-sm select-barang @error('details.' . $index . '.barang_id') is-invalid @enderror"
                                required>
                                <option value="">Pilih Barang</option>
                                @foreach ($barangs as $barang)
                                    <option value="{{ $barang->id }}" data-stock="{{ $barang->stock }}"
                                        data-satuan="{{ $barang->unitSatuan->nama_unit_satuan ?? '' }}"
                                        {{ (isset($detail['barang_id']) && $detail['barang_id'] == $barang->id) || (isset($detail->barang_id) && $detail->barang_id == $barang->id) ? 'selected' : '' }}>
                                        {{ $barang->nama_barang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('details.' . $index . '.barang_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="text" name="details[{{ $index }}][stok_terakhir]"
                                class="form-control form-control-sm stok-terakhir"
                                value="{{ $detail['stok_terakhir'] ?? ($detail->barang->stock ?? 0) }}" readonly>
                        </td>
                        <td>
                            <input type="number" name="details[{{ $index }}][jumlah_pesanan]"
                                class="form-control form-control-sm jumlah-pesanan @error('details.' . $index . '.jumlah_pesanan') is-invalid @enderror"
                                value="{{ $detail['jumlah_pesanan'] ?? ($detail->jumlah_pesanan ?? '') }}"
                                step="any" min="0.01" required>
                            @error('details.' . $index . '.jumlah_pesanan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <select name="details[{{ $index }}][satuan]"
                                class="form-select form-select-sm satuan-barang @error('details.' . $index . '.satuan') is-invalid @enderror"
                                required>
                                <option value="">Pilih Satuan</option>
                                @foreach ($unitSatuans as $satuan)
                                    <option value="{{ $satuan->nama_unit_satuan }}"
                                        {{ (isset($detail['satuan']) && $detail['satuan'] == $satuan->nama_unit_satuan) || (isset($detail->satuan) && $detail->satuan == $satuan->nama_unit_satuan) ? 'selected' : '' }}>
                                        {{ $satuan->nama_unit_satuan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('details.' . $index . '.satuan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="details[{{ $index }}][harga_per_satuan]"
                                class="form-control form-control-sm harga-satuan @error('details.' . $index . '.harga_per_satuan') is-invalid @enderror"
                                value="{{ $detail['harga_per_satuan'] ?? ($detail->harga_per_satuan ?? '') }}"
                                step="any" min="0" required>
                            @error('details.' . $index . '.harga_per_satuan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="text" name="details[{{ $index }}][total_harga]"
                                class="form-control form-control-sm total-harga-detail"
                                value="{{ $detail['total_harga'] ?? ($detail->total_harga ?? 0) }}" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-detail-row"><i
                                    class="fas fa-times"></i></button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr class="detail-row">
                    <td>
                        <select name="details[0][barang_id]" class="form-select form-select-sm select-barang"
                            required>
                            <option value="">Pilih Barang</option>
                            @foreach ($barangs as $barang)
                                <option value="{{ $barang->id }}" data-stock="{{ $barang->stock }}"
                                    data-satuan="{{ $barang->unitSatuan->nama_unit_satuan ?? '' }}">
                                    {{ $barang->nama_barang }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" name="details[0][stok_terakhir]"
                            class="form-control form-control-sm stok-terakhir" readonly>
                    </td>
                    <td>
                        <input type="number" name="details[0][jumlah_pesanan]"
                            class="form-control form-control-sm jumlah-pesanan" step="any" min="0.01"
                            required>
                    </td>
                    <td>
                        <select name="details[0][satuan]" class="form-select form-select-sm satuan-barang" required>
                            <option value="">Pilih Satuan</option>
                            @foreach ($unitSatuans as $satuan)
                                <option value="{{ $satuan->nama_unit_satuan }}">{{ $satuan->nama_unit_satuan }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="details[0][harga_per_satuan]"
                            class="form-control form-control-sm harga-satuan" step="any" min="0" required>
                    </td>
                    <td>
                        <input type="text" name="details[0][total_harga]"
                            class="form-control form-control-sm total-harga-detail" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-detail-row"><i
                                class="fas fa-times"></i></button>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<button type="button" class="btn btn-success btn-sm mt-2" id="add_detail_row"><i class="fas fa-plus"></i> Tambah
    Barang</button>

<hr class="mt-4">
<div class="row justify-content-end">
    <div class="col-md-5">
        <div class="form-group row mb-2">
            <label for="sub_total_pesanan" class="col-sm-5 col-form-label">Sub Total Pesanan</label>
            <div class="col-sm-7">
                <input type="text" class="form-control-plaintext text-end" id="sub_total_pesanan_display"
                    value="Rp 0" readonly>
                <input type="hidden" name="sub_total_pesanan" id="sub_total_pesanan_hidden" value="0">
            </div>
        </div>
        <div class="form-group row mb-2 align-items-center">
            <div class="col-sm-5">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="include_ppn_checkbox"
                        id="include_ppn_checkbox" value="yes"
                        {{ old('include_ppn', $permintaanBarang->include_ppn ?? 'no') == 'yes' ? 'checked' : '' }}>
                    <label class="form-check-label" for="include_ppn_checkbox">
                        PPN (11%)
                    </label>
                    <input type="hidden" name="include_ppn" id="include_ppn_hidden"
                        value="{{ old('include_ppn', $permintaanBarang->include_ppn ?? 'no') }}">
                </div>
            </div>
            <div class="col-sm-7">
                <input type="text" class="form-control-plaintext text-end" id="nominal_ppn_display"
                    value="Rp 0" readonly>
                <input type="hidden" name="nominal_ppn" id="nominal_ppn_hidden" value="0">
            </div>
        </div>
        <div class="form-group row mb-2">
            <label for="total_pesanan" class="col-sm-5 col-form-label fw-bold">Total Pesanan</label>
            <div class="col-sm-7">
                <input type="text" class="form-control-plaintext text-end fw-bold" id="total_pesanan_display"
                    value="Rp 0" readonly>
                <input type="hidden" name="total_pesanan" id="total_pesanan_hidden" value="0">
            </div>
        </div>
    </div>
</div>

@push('scripts_vendor')
    <script src="{{ asset('mazer/extensions/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('mazer/extensions/choices.js/public/assets/scripts/choices.min.js') }}"></script>
@endpush

@push('scripts_custom_form')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let detailRowIndex =
                {{ old('details', isset($permintaanBarang) ? $permintaanBarang->details->count() : 0) ?: 1 }};

            function initializeChoices(element) {
                if (element && !element.classList.contains('choices__input')) {
                    new Choices(element, {
                        searchEnabled: true,
                        shouldSort: false,
                        itemSelectText: 'Tekan enter untuk memilih',
                    });
                }
            }

            function initializeSelectBarang(row) {
                const selectBarangElement = row.querySelector('.select-barang');
                if (selectBarangElement) {
                    initializeChoices(selectBarangElement);
                    selectBarangElement.addEventListener('change', function() {
                        updateRowData(this.closest('tr'));
                    });
                }
            }

            document.querySelectorAll('.select-barang').forEach(function(select) {
                initializeChoices(select);
                select.addEventListener('change', function() {
                    updateRowData(this.closest('tr'));
                });
            });
            document.querySelectorAll('.satuan-barang').forEach(function(select) {
                initializeChoices(select);
            });


            function updateRowData(row) {
                const selectedOption = row.querySelector('.select-barang option:checked');
                const stokInput = row.querySelector('.stok-terakhir');
                const satuanSelect = row.querySelector('.satuan-barang');

                if (selectedOption && selectedOption.value) {
                    const stock = selectedOption.dataset.stock || '0';
                    const satuan = selectedOption.dataset.satuan || '';
                    stokInput.value = parseFloat(stock).toLocaleString('id-ID');

                    // Set satuan dan re-initialize Choices jika ada
                    if (satuanSelect) {
                        const choicesInstance = satuanSelect.choices;
                        if (choicesInstance) {
                            choicesInstance.setChoiceByValue(satuan);
                        } else {
                            // Fallback jika Choices belum terinisialisasi
                            satuanSelect.value = satuan;
                        }
                    }
                } else {
                    stokInput.value = '';
                    if (satuanSelect) {
                        const choicesInstance = satuanSelect.choices;
                        if (choicesInstance) {
                            choicesInstance.setChoiceByValue('');
                        } else {
                            satuanSelect.value = '';
                        }
                    }
                }
                calculateRowTotal(row);
            }


            $('#detail_permintaan_body').on('click', '.remove-detail-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                updateRowIndexes();
            });

            $('#add_detail_row').on('click', function() {
                let newRow = $($('#detailPermintaanTable tbody tr:first').clone(true,
                    true)); // Deep clone with event handlers
                newRow.find('input, select').each(function() {
                    let name = $(this).attr('name');
                    if (name) {
                        name = name.replace(/\[\d+\]/, '[' + detailRowIndex + ']');
                        $(this).attr('name', name).attr('id', name); // Set ID juga jika perlu
                    }
                    if ($(this).is('select.select-barang') || $(this).is('select.satuan-barang')) {
                        // Hancurkan instance Choices lama jika ada
                        if (this.choices) {
                            this.choices.destroy();
                        }
                        $(this).val(''); // Reset value
                    } else {
                        $(this).val(''); // Reset value untuk input lain
                    }

                    // Hapus kelas is-invalid jika ada
                    $(this).removeClass('is-invalid');
                    $(this).closest('td').find('.invalid-feedback').remove();
                });
                newRow.find('.stok-terakhir').val('');
                newRow.find('.total-harga-detail').val('0');
                $('#detail_permintaan_body').append(newRow);

                // Inisialisasi Choices untuk select baru
                initializeSelectBarang(newRow[0]);
                const newSatuanSelect = newRow[0].querySelector('.satuan-barang');
                if (newSatuanSelect) initializeChoices(newSatuanSelect);

                detailRowIndex++;
                updateRowIndexes();
            });

            $('#detail_permintaan_body').on('input', '.jumlah-pesanan, .harga-satuan', function() {
                calculateRowTotal($(this).closest('tr'));
            });

            $('#include_ppn_checkbox').on('change', function() {
                $('#include_ppn_hidden').val(this.checked ? 'yes' : 'no');
                calculateTotals();
            });

            function calculateRowTotal(row) {
                const jumlah = parseFloat($(row).find('.jumlah-pesanan').val()) || 0;
                const harga = parseFloat($(row).find('.harga-satuan').val()) || 0;
                const total = jumlah * harga;
                $(row).find('.total-harga-detail').val(total.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }));
                calculateTotals();
            }

            function calculateTotals() {
                let subTotal = 0;
                $('.total-harga-detail').each(function() {
                    subTotal += parseFloat($(this).val().replace(/\./g, '').replace(/,/g, '.')) || 0;
                });
                $('#sub_total_pesanan_display').val('Rp ' + subTotal.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }));
                $('#sub_total_pesanan_hidden').val(subTotal);

                let nominalPpn = 0;
                if ($('#include_ppn_checkbox').is(':checked')) {
                    nominalPpn = subTotal * 0.11; // PPN 11%
                }
                $('#nominal_ppn_display').val('Rp ' + nominalPpn.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }));
                $('#nominal_ppn_hidden').val(nominalPpn);

                const totalPesanan = subTotal + nominalPpn;
                $('#total_pesanan_display').val('Rp ' + totalPesanan.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }));
                $('#total_pesanan_hidden').val(totalPesanan);
            }

            function updateRowIndexes() {
                $('#detail_permintaan_body tr').each(function(index) {
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            name = name.replace(/\[\d+\]/, '[' + index + ']');
                            $(this).attr('name', name);
                        }
                    });
                });
            }

            // Initial calculation
            $('.detail-row').each(function() {
                updateRowData(this); // Panggil untuk mengisi stok dan satuan saat edit
                calculateRowTotal(this);
            });
            calculateTotals(); // Hitung total keseluruhan saat load
        });
    </script>
@endpush
