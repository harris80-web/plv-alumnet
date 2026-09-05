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
            // A second, independent resume/CV slot purely for backup — not
            // offered as a choice in the job apply flow (see
            // partials/job-apply-modal.blade.php), just a place to keep a
            // spare copy alongside the primary alumnus_resume_file_path.
            if (! Schema::hasColumn('alumni', 'alumnus_resume_backup_file_path')) {
                $table->string('alumnus_resume_backup_file_path')->nullable()->after('alumnus_resume_file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn('alumnus_resume_backup_file_path');
        });
    }
};
