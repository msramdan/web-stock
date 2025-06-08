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
            <label for="mengetahui" class="form-label">Mengetahui</label>
            <input type="text" class="form-control @error('mengetahui') is-invalid @enderror" id="mengetahui"
                name="mengetahui" value="{{ old('mengetahui', $permintaanBarang->mengetahui ?? '') }}" maxlength="150"
                placeholder="Nama yang mengetahui/menyetujui">
            @error('mengetahui')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="no_permintaan_barang" class="form-label">No. Permintaan Barang <span
                    class="text-danger">*</span></label>
            <input type="text" class="form-control @error('no_permintaan_barang') is-invalid @enderror"
                id="no_permintaan_barang" name="no_permintaan_barang"
                value="{{ old('no_permintaan_barang', $permintaanBarang->no_permintaan_barang ?? '') }}" maxlength="50"
                placeholder="No. Permintaan Barang" required>
            @error('no_permintaan_barang')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="nama_supplier" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('nama_supplier') is-invalid @enderror" id="nama_supplier"
                name="nama_supplier" value="{{ old('nama_supplier', $permintaanBarang->nama_supplier ?? '') }}"
                maxlength="150" placeholder="Nama Supplier" required>
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
                name="nama_bank" value="{{ old('nama_bank', $permintaanBarang->nama_bank ?? '') }}" maxlength="100"
                placeholder="Nama Bank">
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
                maxlength="150" placeholder="Nama Akun Supplier">
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
                maxlength="25" placeholder="No. Rekening Supplier">
            @error('account_number_supplier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
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
                {{-- UBAH BAGIAN INI: Lebar disesuaikan --}}
                <th style="width: 35%;">Barang <span class="text-danger">*</span></th>
                <th style="width: 10%;">Stok Terakhir</th>
                <th style="width: 15%;">Jumlah Pesanan<span class="text-danger">*</span></th>
                <th style="width: 12%;">Satuan<span class="text-danger">*</span></th>
                <th style="width: 13%;">Harga/Satuan <span class="text-danger">*</span></th>
                <th style="width: 15%;">Total Harga</th>
                <th style="width: 5%;">Aksi</th>
            </tr>
        </thead>
        <tbody id="detail_permintaan_body">
            @php
                $detailsData = old(
                    'details',
                    isset($permintaanBarang)
                        ? $permintaanBarang->details
                            ->map(function ($detail) {
                                return [
                                    'barang_id' => $detail->barang_id,
                                    'nama_barang_selected' => $detail->barang->nama_barang ?? '', // Untuk display awal
                                    'kode_barang_selected' => $detail->barang->kode_barang ?? '', // Untuk display awal
                                    'stok_terakhir' => $detail->stok_terakhir, // Ini akan jadi stok saat ini
                                    'jumlah_pesanan' => $detail->jumlah_pesanan,
                                    'satuan' => $detail->satuan,
                                    'harga_per_satuan' => $detail->harga_per_satuan,
                                    'total_harga' => $detail->total_harga,
                                ];
                            })
                            ->toArray()
                        : [],
                );
            @endphp

            @if (!empty($detailsData))
                @foreach ($detailsData as $index => $detail)
                    <tr class="detail-row" data-row-index="{{ $index }}">
                        <td>
                            <div class="input-group">
                                <input type="hidden" name="details[{{ $index }}][barang_id]"
                                    class="barang-id-input" value="{{ $detail['barang_id'] ?? '' }}">
                                <input type="text" class="form-control form-control-sm nama-barang-display"
                                    value="{{ $detail['kode_barang_selected'] ?? '' }} - {{ $detail['nama_barang_selected'] ?? '' }}"
                                    readonly placeholder="Pilih Barang...">
                                <button type="button" class="btn btn-success btn-sm search-barang-button"
                                    data-bs-toggle="modal" data-bs-target="#modal-item-permintaan"
                                    title="Cari Barang">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @error('details.' . $index . '.barang_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            {{-- Stok akan diisi oleh JS --}}
                            <input type="text" name="details[{{ $index }}][stok_terakhir]"
                                class="form-control form-control-sm stok-terakhir"
                                value="{{ formatAngkaDesimal($detail['stok_terakhir'] ?? 0) }}" readonly>
                        </td>
                        <td>
                            <input type="number" name="details[{{ $index }}][jumlah_pesanan]"
                                class="form-control form-control-sm jumlah-pesanan @error('details.' . $index . '.jumlah_pesanan') is-invalid @enderror"
                                value="{{ formatAngkaDesimal($detail['jumlah_pesanan'] ?? 0) ?? ($detail['jumlah_pesanan'] ?? '') }}"
                                step="any" min="1" required> {{-- Ubah min jika perlu --}}
                            @error('details.' . $index . '.jumlah_pesanan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td> <input type="text" name="details[{{ $index }}][satuan]"
                                class="form-control form-control-sm satuan-barang-display @error('details.' . $index . '.satuan') is-invalid @enderror"
                                value="{{ $detail['satuan'] ?? '' }}" readonly required> @error('details.' . $index .
                                '.satuan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="details[{{ $index }}][harga_per_satuan]"
                                class="form-control form-control-sm harga-satuan @error('details.' . $index . '.harga_per_satuan') is-invalid @enderror"
                                value="{{ formatAngkaDesimal($detail['harga_per_satuan'] ?? 0) ?? ($detail['harga_per_satuan'] ?? '') }}"
                                step="any" min="0" required>
                            @error('details.' . $index . '.harga_per_satuan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="text" name="details[{{ $index }}][total_harga]"
                                class="form-control form-control-sm total-harga-detail"
                                value="{{ number_format((float) ($detail['total_harga'] ?? 0), 0, ',', '.') }}"
                                readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-detail-row"><i
                                    class="fas fa-times"></i></button>
                        </td>
                    </tr>
                @endforeach
            @else
                {{-- Baris Detail Default (jika tidak ada old input atau data edit) --}}
                <tr class="detail-row" data-row-index="0">
                    <td>
                        {{-- UBAH BAGIAN INI: Input untuk display barang dan tombol cari --}}
                        <div class="input-group">
                            <input type="hidden" name="details[0][barang_id]" class="barang-id-input">
                            <input type="text" class="form-control form-control-sm nama-barang-display" readonly
                                placeholder="Pilih Barang...">
                            <button type="button" class="btn btn-success btn-sm search-barang-button"
                                data-bs-toggle="modal" data-bs-target="#modal-item-permintaan" title="Cari Barang">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="details[0][stok_terakhir]"
                            class="form-control form-control-sm stok-terakhir" readonly>
                    </td>
                    <td>
                        <input type="number" name="details[0][jumlah_pesanan]"
                            class="form-control form-control-sm jumlah-pesanan" step="any" min="1"
                            required>
                    </td>
                    <td> <input type="text" name="details[0][satuan]"
                            class="form-control form-control-sm satuan-barang-display" readonly required> </td>
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

{{-- Perubahan: Tata letak Keterangan dan Total --}}
<div class="row mt-4 align-items-start">
    {{-- Kolom Keterangan di Kiri --}}
    <div class="col-lg-6">
        <div class="form-group">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                rows="5">{{ old('keterangan', $permintaanBarang->keterangan ?? '') }}</textarea>
            @error('keterangan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Kolom Total di Kanan --}}
    <div class="col-lg-6">
        <div class="row justify-content-end">
            <div class="col-md-10">
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
                        <input type="text" class="form-control-plaintext text-end fw-bold"
                            id="total_pesanan_display" value="Rp 0" readonly>
                        <input type="hidden" name="total_pesanan" id="total_pesanan_hidden" value="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-item-permintaan">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Pilih Barang untuk Permintaan</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body table-responsive">
                        <div class="container-fluid">
                            <table class="table table-bordered table-striped" id="modal_table_barang_permintaan"
                                style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Jenis Material</th>
                                        <th>Unit Satuan</th>
                                        <th>Stok Terakhir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Isi tabel modal akan dirender oleh JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
