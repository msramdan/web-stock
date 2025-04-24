<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UnitSatuanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Kilogram',
            'Gram',
            'Ton',
            'Liter',
            'Mililiter',
            'Meter',
            'Centimeter',
            'Unit',
            'Paket',
            'Drum',
            'Galon',
        ];

        foreach ($data as $item) {
            DB::table('unit_satuan')->insert([
                'company_id' => 1,
                'nama_unit_satuan' => $item,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
