<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class AssignCompanySeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;

        $companies = Company::all();

        foreach ($companies as $company) {
            DB::table('assign_company')->insert([
                'user_id'    => $userId,
                'company_id' => $company->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
