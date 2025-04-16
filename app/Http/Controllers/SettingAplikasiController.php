<?php

namespace App\Http\Controllers;

use App\Models\SettingAplikasi;
use App\Http\Requests\SettingAplikasis\{StoreSettingAplikasiRequest, UpdateSettingAplikasiRequest};
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};

class SettingAplikasiController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $logoPerusahaanPath = '')
    {
        $this->logoPerusahaanPath = storage_path('app/public/uploads/logo-perusahaans/');
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('permission:setting aplikasi view', only: ['index']),
            new Middleware('permission:setting aplikasi edit', only: ['update']),
        ];
    }

    public function index(): View
    {
        $settingAplikasi = SettingAplikasi::first() ?? new SettingAplikasi();
        return view('setting-aplikasi.edit', compact('settingAplikasi'));
    }

    public function update(UpdateSettingAplikasiRequest $request, SettingAplikasi $settingAplikasi): RedirectResponse
    {
        $validated = $request->validated();

        $validated['logo_perusahaan'] = $this->imageService->upload(name: 'logo_perusahaan', path: $this->logoPerusahaanPath, defaultImage: $settingAplikasi?->logo_perusahaan);

        $settingAplikasi->update($validated);

        return to_route('setting-aplikasi.index')->with('success', __('The setting aplikasi was updated successfully.'));
    }
}
