<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            'None',
            'Technology',
            'Healthcare',
            'Business & Finance',
            'Education',
            'Government & Public Service',
            'Engineering & Construction',
        ];

        foreach ($industries as $industryName) {
            DB::table('industries')->insert([
                'industry_name' => $industryName,
            ]);
        }
    }
}
