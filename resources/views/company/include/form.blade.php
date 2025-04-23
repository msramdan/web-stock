<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="nama-perusahaan">{{ __('Nama Perusahaan') }}</label>
            <input type="text" name="nama_perusahaan" id="nama-perusahaan" class="form-control @error('nama_perusahaan') is-invalid @enderror" value="{{ isset($company) ? $company->nama_perusahaan : old('nama_perusahaan') }}" placeholder="{{ __('Nama Perusahaan') }}" required />
            @error('nama_perusahaan')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="no-telepon">{{ __('No Telepon') }}</label>
            <input type="tel" name="no_telepon" id="no-telepon" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ isset($company) ? $company->no_telepon : old('no_telepon') }}" placeholder="{{ __('No Telepon') }}" required />
            @error('no_telepon')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ isset($company) ? $company->email : old('email') }}" placeholder="{{ __('Email') }}" required />
            @error('email')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="alamat">{{ __('Alamat') }}</label>
            <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" placeholder="{{ __('Alamat') }}" required>{{ isset($company) ? $company->alamat : old('alamat') }}</textarea>
            @error('alamat')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    @isset($company)
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-5 text-center">
                    @if (!$company->logo_perusahaan)
                        <img src="https://via.placeholder.com/350?text=No+Image+Avaiable" alt="Logo Perusahaan" class="rounded mb-2 mt-2 img-fluid">
                    @else
                        <img src="{{ asset('storage/uploads/logo-perusahaans/' . $company->logo_perusahaan) }}" alt="Logo Perusahaan" class="rounded mb-2 mt-2 img-fluid">
                    @endif
                </div>

                <div class="col-md-7">
                    <div class="form-group ms-3">
                        <label for="logo_perusahaan">{{ __('Logo Perusahaan') }}</label>
                        <input type="file" name="logo_perusahaan" class="form-control @error('logo_perusahaan') is-invalid @enderror" id="logo_perusahaan">

                        @error('logo_perusahaan')
                          <span class="text-danger">
                                {{ $message }}
                           </span>
                        @enderror
                        <div id="logo_perusahaan-help-block" class="form-text">
                            {{ __('Leave the logo perusahaan blank if you don`t want to change it.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-md-6">
            <div class="form-group">
                <label for="logo_perusahaan">{{ __('Logo Perusahaan') }}</label>
                <input type="file" name="logo_perusahaan" class="form-control @error('logo_perusahaan') is-invalid @enderror" id="logo_perusahaan">

                @error('logo_perusahaan')
                   <span class="text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
        </div>
    @endisset
</div>