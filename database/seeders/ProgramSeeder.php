<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Arts in Communication',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Early Childhood Education',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Accountancy',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Business Administration Major in Financial Management',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Business Administration Major in Human Resource Management',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Business Administration Major in Marketing Management',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Civil Engineering',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Electrical Engineering',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Information Technology',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Psychology',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Public Administration',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Science in Social Work',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Secondary Education Major in English',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Secondary Education Major in Filipino',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Secondary Education Major in Mathematics',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Secondary Education Major in Science',
        ]);
        DB::table('programs')->insert([
            'program_name' => 'Bachelor of Secondary Education Major in Social Studies',
        ]);
    }
}
