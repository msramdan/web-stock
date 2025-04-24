<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class JenisMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'STOK ASPAL',
            'PRODUK JADI EMULSI',
            'MATERIAL UNTUK EMULSI',
            'MATERIAL UNTUK ANIONIK',
            'PRODUKSI JADI POLIMER & AKAP',
            'BAHAN BAKAR DAN PELUMAS',
            'MATERIAL KEMASAN',
            'PRODUKSI JADI TCM',
        ];

        foreach ($data as $item) {
            DB::table('jenis_material')->insert([
                'company_id' => 1,
                'nama_jenis_material' => $item,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
