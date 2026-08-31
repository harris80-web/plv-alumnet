<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * alumnus_batch was a bare YEAR column — admins could only ever record
     * "2023," never an actual graduation date. Widened to a real DATE.
     *
     * Doesn't use Schema::table(...)->change() (needs doctrine/dbal, not
     * installed) and deliberately doesn't rely on MySQL's implicit
     * YEAR->DATE conversion during an ALTER...MODIFY either (YEAR stores a
     * bare number; DATE needs YYYY-MM-DD, and that conversion isn't
     * guaranteed well-defined across MySQL versions). Instead: add a new
     * date column, explicitly backfill every existing row from its old
     * year value, then swap the columns.
     */
    public function up(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->date('alumnus_batch_date')->nullable()->after('alumnus_batch');
        });

        // Backfilled to April 15 of the existing batch year — a plausible
        // PLV commencement date, and simply the most defensible single
        // choice for data that only ever recorded a year. New/edited
        // records go through the real date picker from here on.
        DB::statement("UPDATE alumni SET alumnus_batch_date = STR_TO_DATE(CONCAT(alumnus_batch, '-04-15'), '%Y-%m-%d') WHERE alumnus_batch IS NOT NULL");

        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn('alumnus_batch');
        });

        Schema::table('alumni', function (Blueprint $table) {
            $table->renameColumn('alumnus_batch_date', 'alumnus_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->year('alumnus_batch_year')->nullable()->after('alumnus_batch');
        });

        DB::statement('UPDATE alumni SET alumnus_batch_year = YEAR(alumnus_batch) WHERE alumnus_batch IS NOT NULL');

        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn('alumnus_batch');
        });

        Schema::table('alumni', function (Blueprint $table) {
            $table->renameColumn('alumnus_batch_year', 'alumnus_batch');
        });
    }
};
