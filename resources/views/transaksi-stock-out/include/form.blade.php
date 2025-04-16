<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="no-surat">{{ __('No Surat') }}</label>
            <input type="text" name="no_surat" id="no-surat"
                class="form-control @error('no_surat') is-invalid @enderror"
                value="{{ isset($transaksi) ? $transaksi->no_surat : old('no_surat') }}"
                placeholder="{{ __('No Surat') }}" required />
            @error('no_surat')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="tanggal">{{ __('Tanggal') }}</label>
            <input type="datetime-local" name="tanggal" id="tanggal"
                class="form-control @error('tanggal') is-invalid @enderror"
                value="{{ isset($transaksi) && $transaksi?->tanggal ? $transaksi?->tanggal?->format('Y-m-d\TH:i') : old('tanggal') }}"
                placeholder="{{ __('Tanggal') }}" required />
            @error('tanggal')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="keterangan">{{ __('Keterangan') }}</label>
            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                placeholder="{{ __('Keterangan') }}" required>{{ isset($transaksi) ? $transaksi->keterangan : old('keterangan') }}</textarea>
            @error('keterangan')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    @isset($transaksi)
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-5 text-center">
                    @if (!$transaksi->attachment)
                        <img src="https://via.placeholder.com/350?text=No+Image+Avaiable" alt="Attachment"
                            class="rounded mb-2 mt-2 img-fluid">
                    @else
                        <img src="{{ asset('storage/uploads/attachments/' . $transaksi->attachment) }}" alt="Attachment"
                            class="rounded mb-2 mt-2 img-fluid">
                    @endif
                </div>

                <div class="col-md-7">
                    <div class="form-group ms-3">
                        <label for="attachment">{{ __('Attachment') }}</label>
                        <input type="file" name="attachment"
                            class="form-control @error('attachment') is-invalid @enderror" id="attachment">

                        @error('attachment')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                        <div id="attachment-help-block" class="form-text">
                            {{ __('Leave the attachment blank if you don`t want to change it.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-md-6">
            <div class="form-group">
                <label for="attachment">{{ __('Attachment') }}</label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                    id="attachment">

                @error('attachment')
                    <span class="text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>
    @endisset
    <input type="hidden" name="type" id="no-surat" class="form-control @error('type') is-invalid @enderror"
        value="Out" readonly required />
</div>
