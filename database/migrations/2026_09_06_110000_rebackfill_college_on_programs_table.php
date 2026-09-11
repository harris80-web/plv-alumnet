<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The original add_college_to_programs_table migration's backfill only ever
 * ran once, against whatever rows existed in `programs` at that moment — if
 * the table is ever recreated (migrate:fresh) and reseeded afterward, the
 * newly-inserted rows never get a `college` value at all, since
 * ProgramSeeder didn't previously set one (see the fix now in
 * ProgramSeeder::run()). This migration is safe to run any number of times
 * — it just re-applies the same name => college mapping to whatever rows
 * currently exist, correcting exactly this "college came back all null"
 * scenario without a destructive migrate:fresh.
 */
return new class extends Migration
{
    private const PROGRAM_COLLEGES = [
        'Bachelor of Arts in Communication' => 'CAS',
        'Bachelor of Science in Psychology' => 'CAS',
        'Bachelor of Science in Accountancy' => 'CABA',
        'Bachelor of Science in Business Administration Major in Financial Management' => 'CABA',
        'Bachelor of Science in Business Administration Major in Human Resource Management' => 'CABA',
        'Bachelor of Science in Business Administration Major in Marketing Management' => 'CABA',
        'Bachelor of Science in Civil Engineering' => 'CEIT',
        'Bachelor of Science in Electrical Engineering' => 'CEIT',
        'Bachelor of Science in Information Technology' => 'CEIT',
        'Bachelor of Public Administration' => 'CPAG',
        'Bachelor of Science in Social Work' => 'CPAG',
        'Bachelor of Early Childhood Education' => 'COED',
        'Bachelor of Secondary Education Major in English' => 'COED',
        'Bachelor of Secondary Education Major in Filipino' => 'COED',
        'Bachelor of Secondary Education Major in Mathematics' => 'COED',
        'Bachelor of Secondary Education Major in Science' => 'COED',
        'Bachelor of Secondary Education Major in Social Studies' => 'COED',
    ];

    public function up(): void
    {
        foreach (self::PROGRAM_COLLEGES as $programName => $college) {
            DB::table('programs')->where('program_name', $programName)->update(['college' => $college]);
        }
    }

    public function down(): void
    {
        // Intentionally a no-op — this is a data-repair migration, not a
        // schema change; rolling it back to null would just reintroduce
        // the bug it fixes.
    }
};
