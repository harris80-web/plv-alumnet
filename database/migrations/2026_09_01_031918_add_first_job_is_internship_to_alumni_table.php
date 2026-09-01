<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            // Same "set once alongside alumnus_first_job_date, then locked"
            // lifecycle as that column itself — see AlumnusController::
            // updateAlumniProfile(). Nullable rather than defaulting to
            // false so "never answered" (existing rows, first job date not
            // yet set) stays distinct from "answered: not an internship".
            $table->boolean('alumnus_first_job_is_internship')->nullable()->after('alumnus_first_job_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn('alumnus_first_job_is_internship');
        });
    }
};
