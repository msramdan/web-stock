<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="kode-barang">{{ __('Kode Barang') }}</label>
            <input type="text" name="kode_barang" id="kode-barang"
                class="form-control @error('kode_barang') is-invalid @enderror"
                value="{{ isset($barang) ? $barang->kode_barang : old('kode_barang') }}"
                placeholder="{{ __('Kode Barang') }}" required />
            @error('kode_barang')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="nama-barang">{{ __('Nama Barang') }}</label>
            <input type="text" name="nama_barang" id="nama-barang"
                class="form-control @error('nama_barang') is-invalid @enderror"
                value="{{ isset($barang) ? $barang->nama_barang : old('nama_barang') }}"
                placeholder="{{ __('Nama Barang') }}" required />
            @error('nama_barang')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>


    <div class="col-md-6">
        <div class="form-group">
            <label for="deskripsi-barang">{{ __('Deskripsi Barang') }}</label>
            <textarea name="deskripsi_barang" id="deskripsi-barang"
                class="form-control @error('deskripsi_barang') is-invalid @enderror" placeholder="{{ __('Deskripsi Barang') }}"
                required>{{ isset($barang) ? $barang->deskripsi_barang : old('deskripsi_barang') }}</textarea>
            @error('deskripsi_barang')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="jenis-material-id">{{ __('Jenis Material') }}</label>
            <select class="form-select @error('jenis_material_id') is-invalid @enderror" name="jenis_material_id"
                id="jenis-material-id" class="form-control" required>
                <option value="" selected disabled>-- {{ __('Select jenis material') }} --</option>

                @foreach ($jenisMaterials as $jenisMaterial)
                    <option value="{{ $jenisMaterial?->id }}"
                        {{ isset($barang) && $barang?->jenis_material_id == $jenisMaterial?->id ? 'selected' : (old('jenis_material_id') == $jenisMaterial?->id ? 'selected' : '') }}>
                        {{ $jenisMaterial?->nama_jenis_material }}
                    </option>
                @endforeach
            </select>
            @error('jenis_material_id')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="unit-satuan-id">{{ __('Unit Satuan') }}</label>
            <select class="form-select @error('unit_satuan_id') is-invalid @enderror" name="unit_satuan_id"
                id="unit-satuan-id" class="form-control" required>
                <option value="" selected disabled>-- {{ __('Select unit satuan') }} --</option>

                @foreach ($unitSatuans as $unitSatuan)
                    <option value="{{ $unitSatuan?->id }}"
                        {{ isset($barang) && $barang?->unit_satuan_id == $unitSatuan?->id ? 'selected' : (old('unit_satuan_id') == $unitSatuan?->id ? 'selected' : '') }}>
                        {{ $unitSatuan?->nama_unit_satuan }}
                    </option>
                @endforeach
            </select>
            @error('unit_satuan_id')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <input type="hidden" name="stock_barang" value="0"> @isset($barang)
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-5 text-center">
                    @if (!$barang->photo_barang)
                        <img src="https://via.placeholder.com/350?text=No+Image+Avaiable" alt="Photo Barang"
                            class="rounded mb-2 mt-2 img-fluid">
                    @else
                        <img src="{{ asset('storage/uploads/photo-barangs/' . $barang->photo_barang) }}" alt="Photo Barang"
                            class="rounded mb-2 mt-2 img-fluid">
                    @endif
                </div>

                <div class="col-md-7">
                    <div class="form-group ms-3">
                        <label for="photo_barang">{{ __('Photo Barang') }}</label>
                        <input type="file" name="photo_barang"
                            class="form-control @error('photo_barang') is-invalid @enderror" id="photo_barang">

                        @error('photo_barang')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                        <div id="photo_barang-help-block" class="form-text">
                            {{ __('Leave the photo barang blank if you don`t want to change it.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-md-6">
            <div class="form-group">
                <label for="photo_barang">{{ __('Photo Barang') }}</label>
                <input type="file" name="photo_barang" class="form-control @error('photo_barang') is-invalid @enderror"
                    id="photo_barang">

                @error('photo_barang')
                    <span class="text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>
    @endisset
</div>
