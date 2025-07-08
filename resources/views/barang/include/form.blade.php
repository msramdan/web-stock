<div class="row mb-2">
    {{-- Kode Barang --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="kode-barang">{{ __('Kode Barang') }}</label>
            <input type="text" name="kode_barang" id="kode-barang"
                class="form-control @error('kode_barang') is-invalid @enderror"
                value="{{ isset($barang) ? $barang->kode_barang : old('kode_barang') }}"
                placeholder="{{ __('Kode Barang') }}" required />
            @error('kode_barang')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Nama Barang --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="nama-barang">{{ __('Nama Barang') }}</label>
            <input type="text" name="nama_barang" id="nama-barang"
                class="form-control @error('nama_barang') is-invalid @enderror"
                value="{{ isset($barang) ? $barang->nama_barang : old('nama_barang') }}"
                placeholder="{{ __('Nama Barang') }}" required />
            @error('nama_barang')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Deskripsi Barang --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="deskripsi-barang">{{ __('Deskripsi Barang') }}</label>
            <textarea name="deskripsi_barang" id="deskripsi-barang"
                class="form-control @error('deskripsi_barang') is-invalid @enderror" placeholder="{{ __('Deskripsi Barang') }}"
                required>{{ isset($barang) ? $barang->deskripsi_barang : old('deskripsi_barang') }}</textarea>
            @error('deskripsi_barang')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Jenis Material --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="jenis-material-id">{{ __('Jenis Material') }}</label>
            <select class="form-select @error('jenis_material_id') is-invalid @enderror" name="jenis_material_id"
                id="jenis-material-id" required>
                <option value="" selected disabled>-- {{ __('Pilih Jenis Material') }} --</option>
                @foreach ($jenisMaterials as $jenisMaterial)
                    <option value="{{ $jenisMaterial?->id }}"
                        {{ (isset($barang) && $barang?->jenis_material_id == $jenisMaterial?->id) || old('jenis_material_id') == $jenisMaterial?->id ? 'selected' : '' }}>
                        {{ $jenisMaterial?->nama_jenis_material }}
                    </option>
                @endforeach
            </select>
            @error('jenis_material_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Unit Satuan --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="unit-satuan-id">{{ __('Unit Satuan') }}</label>
            <select class="form-select @error('unit_satuan_id') is-invalid @enderror" name="unit_satuan_id"
                id="unit-satuan-id" required>
                <option value="" selected disabled>-- {{ __('Pilih Unit Satuan') }} --</option>
                @foreach ($unitSatuans as $unitSatuan)
                    <option value="{{ $unitSatuan?->id }}"
                        {{ (isset($barang) && $barang?->unit_satuan_id == $unitSatuan?->id) || old('unit_satuan_id') == $unitSatuan?->id ? 'selected' : '' }}>
                        {{ $unitSatuan?->nama_unit_satuan }}
                    </option>
                @endforeach
            </select>
            @error('unit_satuan_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- === PERUBAHAN INPUT TIPE BARANG === --}}
    <div class="col-md-6">
        <div class="form-group">
            <label for="tipe_barang">{{ __('Tipe Barang') }}</label>
            <select class="form-select @error('tipe_barang') is-invalid @enderror" name="tipe_barang" id="tipe_barang"
                required>
                <option value="" selected disabled>-- {{ __('Pilih Tipe Barang') }} --</option>
                <option value="Bahan Baku"
                    {{ (isset($barang) && $barang->tipe_barang == 'Bahan Baku') || old('tipe_barang') == 'Bahan Baku' ? 'selected' : '' }}>
                    Bahan Baku
                </option>
                <option value="Barang Jadi"
                    {{ (isset($barang) && $barang->tipe_barang == 'Barang Jadi') || old('tipe_barang') == 'Barang Jadi' ? 'selected' : '' }}>
                    Barang Jadi
                </option>
                {{-- Opsi baru untuk Kemasan --}}
                <option value="Kemasan"
                    {{ (isset($barang) && $barang->tipe_barang == 'Kemasan') || old('tipe_barang') == 'Kemasan' ? 'selected' : '' }}>
                    Kemasan
                </option>
            </select>
            @error('tipe_barang')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    {{-- Container untuk Harga (Logika lama dipertahankan) --}}
    <div class="col-md-6" id="harga-barang-container" style="display: none;">
        <div class="form-group">
            <label for="harga">{{ __('Harga Barang') }}</label>
            <input type="number" name="harga" id="harga"
                class="form-control @error('harga') is-invalid @enderror"
                value="{{ isset($barang) ? $barang->harga : old('harga') }}" placeholder="{{ __('Harga Barang') }}"
                step="0.01" min="0">
            @error('harga')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <div class="form-text">Harga per satuan</div>
        </div>
    </div>

    {{-- Container baru untuk Kapasitas --}}
    <div class="col-md-6" id="kapasitas-barang-container" style="display: none;">
        <div class="form-group">
            <label for="kapasitas">{{ __('Kapasitas') }}</label>
            <input type="number" name="kapasitas" id="kapasitas"
                class="form-control @error('kapasitas') is-invalid @enderror"
                value="{{ isset($barang) ? $barang->kapasitas : old('kapasitas') }}"
                placeholder="{{ __('Kapasitas per Kemasan') }}" step="1" min="1">
            @error('kapasitas')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            <div class="form-text">Kapasitas muat per kemasan.</div>
        </div>
    </div>
    {{-- === AKHIR PERUBAHAN === --}}


    @isset($barang)
        <div class="col-md-6">
            <div class="form-group">
                <label for="stock_barang">{{ __('Stock Barang Saat Ini') }}</label>
                <input type="number" step="any" name="stock_barang" id="stock_barang"
                    class="form-control @error('stock_barang') is-invalid @enderror"
                    value="{{ $barang->stock_barang ?? old('stock_barang', 0) }}" placeholder="0" readonly>
                <small class="text-muted">Stok diupdate melalui Transaksi In/Out atau Produksi.</small>
                @error('stock_barang')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @else
        <input type="hidden" name="stock_barang" value="0">
    @endisset


    @isset($barang)
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4 text-center">
                    @if ($barang->photo_barang)
                        <img src="{{ asset('storage/uploads/photo-barangs/' . $barang->photo_barang) }}"
                            alt="Photo Barang" class="rounded mb-2 mt-2 img-fluid" style="max-height: 100px;">
                    @else
                        <img src="https://via.placeholder.com/100?text=No+Image" alt="Photo Barang"
                            class="rounded mb-2 mt-2 img-fluid">
                    @endif
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="photo_barang">{{ __('Ganti Photo Barang') }}</label>
                        <input type="file" name="photo_barang"
                            class="form-control @error('photo_barang') is-invalid @enderror" id="photo_barang">
                        @error('photo_barang')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <div id="photo_barang-help-block" class="form-text">
                            {{ __('Kosongkan jika tidak ingin mengubah photo.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-md-6">
            <div class="form-group">
                <label for="photo_barang">{{ __('Photo Barang') }}</label>
                <input type="file" name="photo_barang"
                    class="form-control @error('photo_barang') is-invalid @enderror" id="photo_barang">
                @error('photo_barang')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @endisset
</div>

{{-- Script JavaScript untuk menampilkan/menyembunyikan field --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipeBarangSelect = document.getElementById('tipe_barang');
        const hargaContainer = document.getElementById('harga-barang-container');
        const hargaInput = document.getElementById('harga');
        const kapasitasContainer = document.getElementById('kapasitas-barang-container');
        const kapasitasInput = document.getElementById('kapasitas');

        function toggleFields() {
            const selectedValue = tipeBarangSelect.value;

            // Logika untuk Harga
            if (selectedValue === 'Bahan Baku') {
                hargaContainer.style.display = 'block';
                hargaInput.setAttribute('required', 'required');
            } else {
                hargaContainer.style.display = 'none';
                hargaInput.removeAttribute('required');
            }

            // Logika untuk Kapasitas
            if (selectedValue === 'Kemasan') {
                kapasitasContainer.style.display = 'block';
                kapasitasInput.setAttribute('required', 'required');
            } else {
                kapasitasContainer.style.display = 'none';
                kapasitasInput.removeAttribute('required');
            }
        }

        // Panggil fungsi saat halaman dimuat untuk set state awal
        toggleFields();

        // Tambahkan event listener untuk perubahan pada dropdown
        tipeBarangSelect.addEventListener('change', toggleFields);
    });
</script>
