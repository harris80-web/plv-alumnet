<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * job_applications.application_date was created as a bare
 * $table->timestamp('application_date') — NOT NULL with no stated default.
 * JobApplicationController@applyJob never sets it; it relied on the database
 * filling it in.
 *
 * XAMPP's MariaDB 10.4 does that implicitly (explicit_defaults_for_timestamp
 * is off there, so the first TIMESTAMP column silently gets DEFAULT
 * CURRENT_TIMESTAMP). Hostinger's server doesn't, so on a database built
 * there with `migrate` every job application failed with
 * "1364 Field 'application_date' doesn't have a default value". The live site
 * only escaped it because its tables came from a local dump that carried the
 * implicit default along.
 *
 * Stating the default explicitly makes both servers behave the same. On a
 * database that already has it this is a no-op. Raw SQL, like the other
 * column changes in this project, since doctrine/dbal isn't installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE job_applications MODIFY application_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE job_applications MODIFY application_date TIMESTAMP NOT NULL');
    }
};
