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
            // alumnus_resume_file_path has sat unused in Alumnus::$fillable since
            // the visibility-settings migration (see its comment) — it's finally
            // wired up now: the alumnus's own uploaded resume/CV document, as
            // opposed to alumnus_resume_summary/etc. which back the in-system
            // Resume Builder-generated PDF instead.
            if (! Schema::hasColumn('alumni', 'alumnus_resume_file_path')) {
                $table->string('alumnus_resume_file_path')->nullable()->after('alumnus_resume_summary');
            }
            if (! Schema::hasColumn('alumni', 'alumnus_cover_letter_file_path')) {
                $table->string('alumnus_cover_letter_file_path')->nullable()->after('alumnus_resume_file_path');
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            // Which resume/cover letter this specific application used — resolved
            // at apply-time by JobApplicationController::applyJob(). 'profile'
            // means "whatever the alumnus currently has on file" (resolved live,
            // same pattern as ResumeBuilderController::viewApplicantResume());
            // 'upload' means a one-off file scoped to just this application, in
            // *_path below.
            if (! Schema::hasColumn('job_applications', 'resume_source')) {
                $table->enum('resume_source', ['profile', 'upload'])->default('profile')->after('application_score');
            }
            if (! Schema::hasColumn('job_applications', 'resume_path')) {
                $table->string('resume_path')->nullable()->after('resume_source');
            }
            if (! Schema::hasColumn('job_applications', 'cover_letter_source')) {
                $table->enum('cover_letter_source', ['none', 'profile', 'upload'])->default('none')->after('resume_path');
            }
            if (! Schema::hasColumn('job_applications', 'cover_letter_path')) {
                $table->string('cover_letter_path')->nullable()->after('cover_letter_source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['resume_source', 'resume_path', 'cover_letter_source', 'cover_letter_path']);
        });

        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn(['alumnus_resume_file_path', 'alumnus_cover_letter_file_path']);
        });
    }
};
