<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="barang-id">{{ __('Barang') }}</label>
            <select class="form-select @error('barang_id') is-invalid @enderror" name="barang_id" id="barang-id"
                class="form-control" required>
                <option value="" selected disabled>-- {{ __('Select barang') }} --</option>

                @foreach ($barangs as $barang)
                    <option value="{{ $barang?->id }}"
                        {{ isset($bom) && $bom?->barang_id == $barang?->id ? 'selected' : (old('barang_id') == $barang?->id ? 'selected' : '') }}>
                        {{ $barang?->kode_barang }} - {{ $barang?->nama_barang }}
                    </option>
                @endforeach
            </select>
            @error('barang_id')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="deskripsi">{{ __('Deskripsi') }}</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                placeholder="{{ __('Deskripsi') }}" required>{{ isset($bom) ? $bom->deskripsi : old('deskripsi') }}</textarea>
            @error('deskripsi')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
