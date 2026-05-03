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
        DB::table('industries')->insert([
            'industry_name' => 'None',
        ]);
        DB::table('industries')->insert([
            'industry_name' => 'Technology',
        ]);
        DB::table('industries')->insert([
            'industry_name' => 'Healthcare',
        ]);
    }
}
