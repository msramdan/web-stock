<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['users.create', 'users.edit'], function ($view) {
            return $view->with(
                'roles',
                Role::select('id', 'name')->get()
            );
        });


        View::composer(['barang.create', 'barang.edit'], function ($view) {
            return $view->with(
                'jenisMaterials',
                \App\Models\JenisMaterial::select('id', 'nama_jenis_material')->get()
            );
        });

        View::composer(['barang.create', 'barang.edit'], function ($view) {
            return $view->with(
                'unitSatuans',
                \App\Models\UnitSatuan::select('id', 'nama_unit_satuan')->get()
            );
        });
    }
}
