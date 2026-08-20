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
        $programNames = [
            'Bachelor of Arts in Communication',
            'Bachelor of Early Childhood Education',
            'Bachelor of Science in Accountancy',
            'Bachelor of Science in Business Administration Major in Financial Management',
            'Bachelor of Science in Business Administration Major in Human Resource Management',
            'Bachelor of Science in Business Administration Major in Marketing Management',
            'Bachelor of Science in Civil Engineering',
            'Bachelor of Science in Electrical Engineering',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Psychology',
            'Bachelor of Public Administration',
            'Bachelor of Science in Social Work',
            'Bachelor of Secondary Education Major in English',
            'Bachelor of Secondary Education Major in Filipino',
            'Bachelor of Secondary Education Major in Mathematics',
            'Bachelor of Secondary Education Major in Science',
            'Bachelor of Secondary Education Major in Social Studies',
        ];

        // updateOrInsert keyed on program_name so re-running this seeder
        // (e.g. `php artisan db:seed` more than once) can't duplicate rows —
        // it previously used a plain insert() per program with no
        // existence check, which is how the programs table ended up with
        // the same 17 names repeated 5x over.
        foreach ($programNames as $name) {
            DB::table('programs')->updateOrInsert(['program_name' => $name]);
        }
    }
}
