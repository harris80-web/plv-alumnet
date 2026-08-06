<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Both columns are treated as optional by the resume builder wizard
     * (Industry has an explicit "(optional)" placeholder; issuing org is
     * `nullable` in ResumeBuilderController's validation) but were created
     * NOT NULL with no default, so leaving either blank throws a SQL error
     * on save. Using raw statements instead of ->change() since this
     * project doesn't have doctrine/dbal installed.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE experiences MODIFY industry_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE certifications MODIFY certification_from VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE experiences MODIFY industry_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE certifications MODIFY certification_from VARCHAR(255) NOT NULL');
    }
};
