<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Laravel's schema builder can't modify an enum's value list in
        // place — same raw-ALTER approach as the migration that originally
        // created this enum (2026_09_05_000000_add_documents_...).
        DB::statement("ALTER TABLE job_applications MODIFY resume_source ENUM('profile', 'upload', 'builder') NOT NULL DEFAULT 'profile'");

        Schema::table('job_applications', function (Blueprint $table) {
            // Only populated when resume_source = 'builder' — a one-time,
            // disconnected copy of the alumnus's Resume Builder content
            // (summary/skills/experiences/certifications), edited only for
            // THIS application in the apply-review step. Deliberately never
            // written back to the alumni/experiences/skills tables, so
            // editing it here can't alter the alumnus's real saved profile.
            $table->json('builder_resume_snapshot')->nullable()->after('cover_letter_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('builder_resume_snapshot');
        });

        DB::statement("ALTER TABLE job_applications MODIFY resume_source ENUM('profile', 'upload') NOT NULL DEFAULT 'profile'");
    }
};
