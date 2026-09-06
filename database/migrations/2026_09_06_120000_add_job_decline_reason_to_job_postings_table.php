<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * job_postings already soft-deletes (deleted_at) — declineJobPost()'s
     * $job->delete() was never actually a hard delete, despite an old code
     * comment claiming otherwise. This column just gives the admin's
     * Declined Job Posts view a direct, unambiguous place to read the
     * decline reason from the job row itself, instead of parsing it back
     * out of the notification text sent to the employer (fragile, and
     * ambiguous if the same employer is declined more than once).
     */
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->text('job_decline_reason')->nullable()->after('job_approved');
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn('job_decline_reason');
        });
    }
};
