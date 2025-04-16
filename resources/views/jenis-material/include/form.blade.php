<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="nama-jenis-material">{{ __('Nama Jenis Material') }}</label>
            <input type="text" name="nama_jenis_material" id="nama-jenis-material" class="form-control @error('nama_jenis_material') is-invalid @enderror" value="{{ isset($jenisMaterial) ? $jenisMaterial->nama_jenis_material : old('nama_jenis_material') }}" placeholder="{{ __('Nama Jenis Material') }}" required />
            @error('nama_jenis_material')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>