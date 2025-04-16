<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SettingAplikasiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('setting_aplikasi')->insert([
            'nama_aplikasi'     => 'Web Stock',
            'nama_perusahaan'   => 'PT. Aspal Polimer Emulsindo',
            'no_telepon'        => '(0291) 6913767',
            'email'             => 'info@apalindo.co.id',
            'alamat'            => 'Kampung Sekaran, RT.001/RW.001, Sekaran, Mranak, Kec. Wonosalam, Kabupaten Demak, Jawa Tengah 59571',
            'logo_perusahaan'   => null,
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ]);
    }
}
