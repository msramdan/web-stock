<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('company')->insert([
            [
                'nama_perusahaan' => 'PT. Aspal Polimer Emulsindo',
                'no_telepon' => '(0291) 6913767',
                'email' => 'info@aspalpolimer.com',
                'alamat' => 'Kampung Sekaran, RT.001/RW.001, Sekaran, Mranak, Kec. Wonosalam, Kabupaten Demak, Jawa Tengah 59571',
                'logo_perusahaan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_perusahaan' => 'PT. Modifikasi Bitumen Sumatera',
                'no_telepon' => '0813-1441-3317',
                'email' => 'info@bitumensumatera.com',
                'alamat' => 'Jl. Lintas Sumatera Jl. Lintas Prabumulih - Muara Enim No.08, RT.-02/RW.Dusun VI, Belimbing, Kec. Gn. Megang, Kabupaten Muara Enim, Sumatera Selatan 31352',
                'logo_perusahaan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
