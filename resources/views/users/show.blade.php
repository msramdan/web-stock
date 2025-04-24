@extends('layouts.app')

@section('title', __('Detail User'))

@section('content')
    <style>
        .avatar.avatar-xl img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }

        .card-header {
            font-size: 1rem;
            font-weight: 600;
        }

        .list-group-item {
            font-size: 0.95rem;
        }
    </style>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>{{ __('User') }}</h3>
                    <p class="text-subtitle text-muted">
                        {{ __('Detail user information.') }}
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
                        {{ __('Detail') }}
                    </li>
                </x-breadcrumb>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <tr>
                                        <td class="fw-bold">{{ __('Avatar') }}</td>
                                        <td>
                                            <div class="avatar avatar-xl">
                                                @if (!$user->avatar)
                                                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim($user->email))) }}?s=500"
                                                        alt="Avatar" class="rounded img-thumbnail">
                                                @else
                                                    <img src="{{ asset('storage/uploads/avatars/' . $user->avatar) }}"
                                                        alt="Avatar" class="rounded img-thumbnail">
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Name') }}</td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Email') }}</td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Role') }}</td>
                                        <td>{{ $user->getRoleNames()->toArray() !== [] ? $user->getRoleNames()[0] : '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('Kembali') }}</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <strong>Akses Company</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse ($companies as $company)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $company->nama_perusahaan }}
                                    <span class="badge bg-primary rounded-pill"><i data-feather="briefcase"></i></span>
                                </li>
                            @empty
                                <li class="list-group-item">Tidak ada akses PT</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
