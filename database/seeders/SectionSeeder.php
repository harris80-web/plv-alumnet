<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sections')->insert([
            'section_name' => '4-1',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-2',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-3',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-4',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-5',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-6',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-7',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-8',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-9',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-10',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-11',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-12',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-13',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-14',
        ]);
        DB::table('sections')->insert([
            'section_name' => '4-15',
        ]);
    }
}
