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
        // program_name => college code (CABA/CEIT/CAS/CPAG) — keeping college
        // in the seeder itself (not just in a one-off backfill migration)
        // means a fresh migrate:fresh + db:seed always produces correctly
        // classified programs, instead of silently reintroducing null
        // colleges the way a schema-only migration's backfill would if it
        // ever runs before this seeder inserts the rows. Education programs
        // are grouped under CAS — there's no dedicated College of Education
        // in this app's 4-college list, and CAS is the closest fit.
        $programColleges = [
            'Bachelor of Arts in Communication' => 'CAS',
            'Bachelor of Early Childhood Education' => 'CAS',
            'Bachelor of Science in Accountancy' => 'CABA',
            'Bachelor of Science in Business Administration Major in Financial Management' => 'CABA',
            'Bachelor of Science in Business Administration Major in Human Resource Management' => 'CABA',
            'Bachelor of Science in Business Administration Major in Marketing Management' => 'CABA',
            'Bachelor of Science in Civil Engineering' => 'CEIT',
            'Bachelor of Science in Electrical Engineering' => 'CEIT',
            'Bachelor of Science in Information Technology' => 'CEIT',
            'Bachelor of Science in Psychology' => 'CAS',
            'Bachelor of Public Administration' => 'CPAG',
            'Bachelor of Science in Social Work' => 'CPAG',
            'Bachelor of Secondary Education Major in English' => 'CAS',
            'Bachelor of Secondary Education Major in Filipino' => 'CAS',
            'Bachelor of Secondary Education Major in Mathematics' => 'CAS',
            'Bachelor of Secondary Education Major in Science' => 'CAS',
            'Bachelor of Secondary Education Major in Social Studies' => 'CAS',
        ];

        // updateOrInsert keyed on program_name so re-running this seeder
        // (e.g. `php artisan db:seed` more than once) can't duplicate rows —
        // it previously used a plain insert() per program with no
        // existence check, which is how the programs table ended up with
        // the same 17 names repeated 5x over. Now also always (re)sets
        // college on every run, so an existing row missing one gets fixed
        // too, not just newly-inserted rows.
        foreach ($programColleges as $name => $college) {
            DB::table('programs')->updateOrInsert(['program_name' => $name], ['college' => $college]);
        }
    }
}
