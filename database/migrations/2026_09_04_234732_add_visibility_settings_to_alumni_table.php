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
            // Restoring columns the *current* create_alumni_table migration
            // file defines (linkedin_url, alumnus_resume_summary,
            // alumnus_resume_completeness) but this already-migrated
            // database never actually got — that file was edited after
            // being run here, so ResumeBuilderController::save() has been
            // failing on every attempt (it writes all three together in one
            // transaction). Restored here rather than by editing the old
            // migration, which wouldn't re-run against an already-migrated
            // database. alumnus_resume_file_path is NOT restored — nothing
            // in the app reads or writes it, it's dead weight in $fillable.
            if (! Schema::hasColumn('alumni', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('alumnus_resume_file_path');
            }
            if (! Schema::hasColumn('alumni', 'alumnus_resume_summary')) {
                $table->text('alumnus_resume_summary')->nullable()->after('alumnus_resume_file_path');
            }
            if (! Schema::hasColumn('alumni', 'alumnus_resume_completeness')) {
                $table->unsignedTinyInteger('alumnus_resume_completeness')->default(0)->after('alumnus_resume_file_path');
            }

            // Per-field directory visibility — independent of alumnus_is_public
            // (which gates the whole profile and isn't enforced anywhere yet).
            // Default true so existing alumni see no change until they
            // deliberately opt to hide one of these. Anchored after
            // alumnus_resume_completeness (not the never-existent
            // "alumnus_skills" — skills are the alumnus_skill pivot table,
            // not a column on this table) since that's the last of the
            // resume-related columns this migration deals with.
            $table->boolean('alumnus_show_skills')->default(true)->after('alumnus_resume_completeness');
            $table->boolean('alumnus_show_email')->default(true)->after('alumnus_show_skills');
            $table->boolean('alumnus_show_linkedin')->default(true)->after('alumnus_show_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn(['alumnus_show_skills', 'alumnus_show_email', 'alumnus_show_linkedin']);
        });
    }
};
