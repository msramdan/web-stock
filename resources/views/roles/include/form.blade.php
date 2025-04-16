<div class="row mb-2">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="{{ __('Name') }}" value="{{ isset($role) ? $role->name : old('name') }}" autofocus required>
            @error('name')
                <span class="text-danger">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <label class="mb-1">{{ __('Permissions') }}</label>
        @error('permissions')
            <div class="text-danger mb-2 mt-0">{{ $message }}</div>
        @enderror
    </div>

    @foreach(config('permission.permissions') as $permission)
        <div class="col-md-3">
            <div class="card" style="border: 1px solid #ddd; border-radius: 8px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);">
                <div class="card-header" style="background-color: #f8f9fa; padding: 10px; border-bottom: 1px solid #ddd;">
                    <div class="form-check">
                        <input class="form-check-input group-checkbox" type="checkbox" id="group_{{ str()->slug($permission['group']) }}"
                            data-group="{{ str()->slug($permission['group']) }}">
                        <label class="form-check-label" for="group_{{ str()->slug($permission['group']) }}">
                            <h5 style="margin: 0; font-weight: bold; color: #333;">{{ ucwords($permission['group']) }}</h5>
                        </label>
                    </div>
                </div>
                <div class="card-body" style="padding: 10px;height:140px">
                    @foreach ($permission['access'] as $access)
                        <div class="form-check" style="margin-bottom: 5px;">
                            <input class="form-check-input permission-checkbox" type="checkbox"
                                name="permissions[]" id="{{ str()->slug($access) }}"
                                value="{{ $access }}" data-group="{{ str()->slug($permission['group']) }}"
                                {{ isset($role) && $role->hasPermissionTo($access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ str()->slug($access) }}"
                                style="font-weight: 500; color: #555;">
                                {{ ucwords(__($access)) }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    @endforeach
</div>
@push('js')
    <script>$(document).ready(function () {
        // Saat grup checkbox diklik, ceklis semua checkbox dalam grup tersebut
        $('.group-checkbox').on('change', function () {
            let group = $(this).data('group');
            let isChecked = $(this).prop('checked');

            $(`.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
        });

        // Saat halaman dimuat, cek apakah semua permission dalam grup sudah tercentang
        $('.group-checkbox').each(function () {
            let group = $(this).data('group');
            let allPermissions = $(`.permission-checkbox[data-group="${group}"]`);
            let allChecked = allPermissions.length > 0 && allPermissions.filter(':checked').length === allPermissions.length;

            $(this).prop('checked', allChecked);
        });

        // Jika semua permission dalam grup dicentang/dicek manual, grup harus otomatis tercentang
        $('.permission-checkbox').on('change', function () {
            let group = $(this).data('group');
            let allPermissions = $(`.permission-checkbox[data-group="${group}"]`);
            let allChecked = allPermissions.length > 0 && allPermissions.filter(':checked').length === allPermissions.length;

            $(`.group-checkbox[data-group="${group}"]`).prop('checked', allChecked);
        });
    });
    </script>
@endpush
