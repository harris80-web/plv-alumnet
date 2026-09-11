<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * program_name => college code. Keyed by name (not id) so this backfill
     * is correct regardless of insertion order/id drift. Education programs
     * (Early Childhood + every Secondary Education major) are grouped under
     * CAS — there's no dedicated College of Education in this app's 4-college
     * list, and CAS is the closest fit among the given colleges.
     */
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
        'Bachelor of Secondary Education Major in Social Studies' => 'COED'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->enum('college', ['COED', 'CABA', 'CEIT', 'CAS', 'CPAG'])->nullable()->after('program_name');
        });

        foreach (self::PROGRAM_COLLEGES as $programName => $college) {
            DB::table('programs')->where('program_name', $programName)->update(['college' => $college]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('college');
        });
    }
};
