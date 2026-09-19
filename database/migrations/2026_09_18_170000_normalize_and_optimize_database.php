<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database cleanup pass, driven by a full codebase audit (grep across
 * app/, resources/views/, routes/, database/seeders/ — not guesswork):
 *
 * - Drops 3 tables that are pure dead weight: each has an unrouted
 *   resource-controller stub, zero references anywhere outside its own
 *   model/controller, and zero rows in the live database.
 * - Drops 3 columns that are never read or written anywhere (verified the
 *   same way), distinct from columns that merely LOOK redundant but are
 *   actually live (e.g. messages.message_created_at and
 *   conversations.conversation_created_at are read by
 *   DashboardReportService's monthly reports — left alone on purpose).
 * - Normalizes certifications.alumnus_id to reference alumni.user_id
 *   instead of the looser users.user_id it was created against — every
 *   other alumnus-scoped table (experiences, job_applications,
 *   job_matches, ...) already points at alumni.user_id, and the
 *   controller only ever writes this column for an actual alumnus, so
 *   this just makes the constraint match the domain rule already being
 *   enforced in code. Safe: alumni.user_id IS users.user_id (shared 1:1
 *   PK), so every existing row already satisfies the tighter FK.
 * - Adds the two indexes actually missing anywhere in the schema —
 *   verified via SHOW INDEX against the live database first, since MySQL
 *   auto-creates a supporting index for every FK constraint regardless of
 *   which Laravel migration syntax declared it (foreignId()->constrained()
 *   and a plain unsignedBigInteger()+foreign() both end up indexed) —
 *   most FK columns already had one; only users.user_role (filtered in
 *   ~60 places across the app) and job_postings' approved+open listing
 *   query had none at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Dead tables: unrouted CRUD stubs, zero other references, zero rows ----
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('employment_types');
        Schema::dropIfExists('education');

        // ---- Dead columns ----
        Schema::table('alumni', function (Blueprint $table) {
            // Never read or set anywhere in the app — users.must_change_password
            // is the one actually enforced (see ForcePasswordChange middleware).
            $table->dropColumn('alumnus_change_password');
        });

        Schema::table('offices', function (Blueprint $table) {
            // office_created_at duplicates the standard created_at with no
            // distinct reader; office_last_log is never read or written.
            $table->dropColumn(['office_created_at', 'office_last_log']);
        });

        // ---- Normalization: certifications.alumnus_id -> alumni.user_id ----
        Schema::table('certifications', function (Blueprint $table) {
            $table->dropForeign(['alumnus_id']);
        });
        Schema::table('certifications', function (Blueprint $table) {
            $table->foreign('alumnus_id')->references('user_id')->on('alumni')->cascadeOnDelete();
        });

        // ---- The two genuinely missing indexes ----
        Schema::table('users', function (Blueprint $table) {
            $table->index('user_role');
        });

        // The job board's main listing query is always
        // JobPosting::approved()->open() — supports that filter directly.
        Schema::table('job_postings', function (Blueprint $table) {
            $table->index(['job_approved', 'job_closing_date']);
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropIndex(['job_approved', 'job_closing_date']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_role']);
        });

        Schema::table('certifications', function (Blueprint $table) {
            $table->dropForeign(['alumnus_id']);
        });
        Schema::table('certifications', function (Blueprint $table) {
            $table->foreign('alumnus_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->timestamp('office_created_at')->nullable();
            $table->timestamp('office_last_log')->nullable();
        });

        Schema::table('alumni', function (Blueprint $table) {
            $table->boolean('alumnus_change_password')->default(false);
        });

        // Table drops aren't reversed — they held 0 rows at drop time and
        // nothing in the app references them anymore; recreating an empty,
        // dead table on rollback would serve no purpose.
    }
};
