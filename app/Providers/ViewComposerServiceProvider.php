<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
// Tambahkan use model jika belum ada di atas
use App\Models\JenisMaterial;
use App\Models\UnitSatuan;
use App\Models\User;
use App\Models\Barang;


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
            // Anda bisa menggabungkan composer untuk barang jika mau,
            // tapi dipisah juga tidak apa-apa.
            $companyId = session('sessionCompany'); // Ambil ID company

            // Filter JenisMaterial
            $jenisMaterials = JenisMaterial::select('id', 'nama_jenis_material')
                ->where('company_id', $companyId) // Filter
                ->orderBy('nama_jenis_material')  // Tambah order by
                ->get();

            // Filter UnitSatuan (TAMBAHKAN where clause di sini)
            $unitSatuans = UnitSatuan::select('id', 'nama_unit_satuan')
                ->where('company_id', $companyId) // <<<---- TAMBAHKAN FILTER INI
                ->orderBy('nama_unit_satuan')   // Tambah order by
                ->get();

            // Kirim kedua variabel ke view
            $view->with(compact('jenisMaterials', 'unitSatuans'));

            // Hapus composer terpisah untuk unitSatuan di bawah ini jika sudah digabung
        });

        /* HAPUS ATAU KOMENTARI COMPOSER INI KARENA SUDAH DIGABUNG DI ATAS
        View::composer(['barang.create', 'barang.edit'], function ($view) {
            return $view->with(
                'unitSatuans',
                \App\Models\UnitSatuan::select('id', 'nama_unit_satuan')->get() // <- Ini yang lama tanpa filter
            );
        });
        */


        View::composer(['transaksi.create', 'transaksi.edit'], function ($view) {
            // Sebaiknya filter user juga jika perlu, atau sesuaikan
            return $view->with(
                'users',
                User::select('id', 'name')->orderBy('name')->get() // Tambah order by
            );
        });


        View::composer(['bom.create', 'bom.edit'], function ($view) {
            // PENTING: Filter barang di BOM juga berdasarkan company!
            $companyId = session('sessionCompany');
            return $view->with(
                // 'barangs', // Nama variabel mungkin perlu disesuaikan (misal: barangOptions)
                // Barang::select('id', 'kode_barang', 'nama_barang')
                //     ->where('company_id', $companyId) // <<<---- TAMBAHKAN FILTER INI
                //     ->orderBy('nama_barang')       // Tambah order by
                //     ->get()

                // --- Pilihan Lebih Baik untuk BOM ---
                // Pisahkan antara Produk Jadi dan Material jika logikanya berbeda
                $view->with([
                    'produkJadi' => Barang::where('company_id', $companyId)
                        // ->where('jenis_barang', 'Produk Jadi') // Tambahkan filter jika ada kolom jenis
                        ->orderBy('nama_barang')
                        ->get(['id', 'kode_barang', 'nama_barang']), // Ambil kolom perlu
                    'barangMaterials' => Barang::with('unitSatuan') // Eager load unit default
                        ->where('company_id', $companyId)
                        // ->where('jenis_barang', 'Material') // Tambahkan filter jika ada kolom jenis
                        ->orderBy('nama_barang')
                        ->get(['id', 'kode_barang', 'nama_barang', 'unit_satuan_id']) // Ambil kolom perlu
                ])

            );
        });
    }
}
