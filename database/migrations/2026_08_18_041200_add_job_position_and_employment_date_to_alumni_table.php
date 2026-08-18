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
            $table->string('alumnus_job_position')->nullable()->after('alumnus_workplace');
            // Start date of the CURRENT job — distinct from
            // alumnus_first_job_date, which is locked forever as their
            // very first job and never changes on a later hire.
            $table->date('alumnus_employment_date')->nullable()->after('alumnus_first_job_date');
            // True only when the current employment came from an in-system
            // hire (see JobApplicationController::hireApplication()) — gates
            // whether alumnus_job_position/alumnus_employment_date are
            // editable on the self-service profile form.
            $table->boolean('alumnus_employed_via_platform')->default(false)->after('alumnus_employment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn(['alumnus_job_position', 'alumnus_employment_date', 'alumnus_employed_via_platform']);
        });
    }
};
