@extends('layouts.app')

@section('title', __('Edit User'))

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('User') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Edit user.') }}
                    </p>
                </div>

                <x-breadcrumb>
                    <li class="breadcrumb-item">
                        <a href="/">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('users.index') }}">{{ __('User') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ __('Edit') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                                <section class="section">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="card">
                                                <div class="card-body">

                                                    @csrf
                                                    @method('PUT')

                                                    @include('users.include.form')

                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input companyCheckbox" type="checkbox" value=""
                                                            id="superAdminCheckbox">
                                                        <label class="form-check-label " for="superAdminCheckbox">
                                                            Assign All Company ?
                                                        </label>
                                                    </div>

                                                    <hr>
                                                    @foreach ($companies as $row)
                                                        <div class="form-check">
                                                            <input class="form-check-input companyCheckbox" name="companies[]" type="checkbox"
                                                                value="{{ $row->id }}" id="flexCheckDefault"
                                                                {{ cekAssign($row->id, $user->id) > 0 ? 'checked' : '' }}>
                                                            <label class="form-check-label " for="flexCheckDefault">
                                                                {{ $row->nama_perusahaan }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                    @error('companies')
                                                        <span class="text-danger">
                                                            Companies wajib diisi minimal 1.
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Kembali') }}</a>
                                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                        </div>
                                    </div>
                                </section>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('js')
    <script>
        // Ambil elemen checkbox super admin
        var superAdminCheckbox = document.getElementById('superAdminCheckbox');

        // Ambil elemen checkbox list company
        var companyCheckboxes = document.querySelectorAll('.companyCheckbox');

        // Tambahkan event listener untuk checkbox super admin
        superAdminCheckbox.addEventListener('change', function() {
            // Setel status checkbox list company berdasarkan checkbox super admin
            companyCheckboxes.forEach(function(checkbox) {
                checkbox.checked = superAdminCheckbox.checked;
            });
        });

        // Tambahkan event listener untuk setiap checkbox list company
        companyCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // Jika salah satu checkbox list company di-uncheck, setel checkbox super admin menjadi unchecked
                if (!checkbox.checked) {
                    superAdminCheckbox.checked = false;
                }
            });
        });
    </script>
@endpush
