@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Success') }}</h4>
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Error') }}</h4>
        <p>{{ session('error') }}</p>
    </div>
@endif

@if (session('status') == 'profile-information-updated')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Success') }}</h4>
        <p>{{ __('Informasi profil berhasil diperbarui.') }}</p>
    </div>
@endif

@if (session('status') == 'password-updated')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Success') }}</h4>
        <p>{{ __('Kata sandi berhasil diperbarui.') }}</p>
    </div>
@endif

@if (session('status') == 'two-factor-authentication-disabled')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Success') }}</h4>
        <p>{{ __('Autentikasi dua faktor telah dinonaktifkan.') }}</p>
    </div>
@endif

@if (session('status') == 'two-factor-authentication-enabled')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Success') }}</h4>
        <p>{{ __('Autentikasi dua faktor telah diaktifkan.') }}</p>
    </div>
@endif

@if (session('status') == 'recovery-codes-generated')
    <div class="alert alert-success alert-dismissible show fade mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h4 class="alert-heading">{{ __('Success') }}</h4>
        <p>{{ __('Kode pemulihan berhasil dibuat ulang.') }}</p>
    </div>
@endif
