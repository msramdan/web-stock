<?php

namespace Database\Seeders;

use App\Http\Controllers\SettingAplikasiController;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(JenisMaterialSeeder::class);
        $this->call(UnitSatuanSeeder::class);
    }
}
