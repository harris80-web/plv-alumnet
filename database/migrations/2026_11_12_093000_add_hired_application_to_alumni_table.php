<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which of an alumnus's own "hired" applications they picked as their
 * current workplace on the edit-profile form.
 *
 * Being hired no longer rewrites the employment section of a profile
 * (JobApplicationController::hireApplication()) — an alumnus can be hired
 * by several employers at once, and the system can't know which one they
 * actually took. Instead they choose, and "Where do you work?" lists their
 * hires plus an "Others" option for a job that didn't come from here.
 *
 * The chosen hire's company/title/date are still copied onto
 * alumnus_workplace / alumnus_job_position / alumnus_employment_date so
 * every existing reader (profile pages, directory, reports, exports) keeps
 * working untouched; this column only records *which* option is selected,
 * so re-opening the form reselects it instead of guessing from a name.
 * NULL means "Others" (or not employed) — i.e. self-entered details.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->unsignedBigInteger('alumnus_hired_application_id')->nullable()->after('alumnus_employed_via_platform');

            // If the application row goes away the profile keeps the copied
            // company/title/date and simply falls back to "Others", rather
            // than the save blowing up on a dangling reference.
            $table->foreign('alumnus_hired_application_id')
                ->references('application_id')->on('job_applications')
                ->nullOnDelete();
        });

        // Backfill: profiles already flagged as employed-via-platform were
        // filled in by the old automatic hire, so point each at the hire that
        // produced it (most recent one at the company currently on the
        // profile). Without this the form would show them as "Others" even
        // though the details did come from a real posting.
        DB::statement("
            UPDATE alumni a
            SET a.alumnus_hired_application_id = (
                SELECT ja.application_id
                FROM job_applications ja
                JOIN job_postings jp ON jp.job_posting_id = ja.job_id
                WHERE ja.alumnus_id = a.user_id
                  AND ja.application_status = 'hired'
                  AND jp.job_posting_company = a.alumnus_workplace
                ORDER BY ja.hired_at DESC, ja.application_id DESC
                LIMIT 1
            )
            WHERE a.alumnus_employed_via_platform = 1
              AND a.alumnus_workplace IS NOT NULL
        ");

        // Keeps the flag meaning exactly \"the workplace on this profile is one
        // of my hires here\", which is what the form and save path now assume:
        // anything left unmatched is self-reported.
        DB::table('alumni')
            ->whereNull('alumnus_hired_application_id')
            ->where('alumnus_employed_via_platform', 1)
            ->update(['alumnus_employed_via_platform' => 0]);
    }

    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropForeign(['alumnus_hired_application_id']);
            $table->dropColumn('alumnus_hired_application_id');
        });
    }
};
