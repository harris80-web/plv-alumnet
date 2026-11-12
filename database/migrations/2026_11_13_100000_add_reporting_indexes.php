<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the columns the dashboard, reports and badge counts filter on.
 *
 * Every one of these tables only had indexes on its primary key and its
 * foreign keys, so anything filtering on a status/date/flag column fell back
 * to a scan. EXPLAIN on the dashboard's monthly hire count showed MySQL
 * walking all ~1000 alumni rows and re-filtering application_status and
 * hired_at with "Using where" for each one — repeated once per month on the
 * chart, which was the single most expensive thing on that page.
 *
 * Composite column order follows how the queries actually filter: the
 * equality column first, the range/second predicate after, so one index
 * serves both the equality lookup and the range scan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // "hired in this month" — the dashboard's hires-per-month chart,
            // the employment-interval report and the placement exports.
            $table->index(['application_status', 'hired_at'], 'ja_status_hired_at_index');
            // Per-job applicant lists and the "N new" unread badge on the
            // employer dashboard / My Job Postings.
            $table->index(['job_id', 'is_read'], 'ja_job_is_read_index');
            $table->index(['job_id', 'application_status'], 'ja_job_status_index');
            // An alumnus's own hires — Alumnus::hiringOptions() and
            // wasHiredByEmployer().
            $table->index(['alumnus_id', 'application_status'], 'ja_alumnus_status_index');
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            // The bell's unread count, run on every authenticated page load.
            $table->index(['user_id', 'read_at'], 'un_user_read_at_index');
            // The notification list itself, newest first.
            $table->index(['user_id', 'created_at'], 'un_user_created_at_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            // Thread rendering and polling, oldest-to-newest within a thread.
            $table->index(['conversation_id', 'message_created_at'], 'msg_convo_created_index');
            // Unread badge per recipient.
            $table->index(['receiver_id', 'message_read'], 'msg_receiver_read_index');
            // The networking report's per-month message volume.
            $table->index('message_created_at', 'msg_created_at_index');
        });

        Schema::table('conversations', function (Blueprint $table) {
            // The networking report's per-month conversation volume.
            $table->index('conversation_created_at', 'convo_created_at_index');
        });

        Schema::table('alumni', function (Blueprint $table) {
            // Employment breakdowns group by these; the batch one is also
            // range-filtered by graduation year across every alumni report.
            $table->index('alumnus_employment_status', 'alumni_employment_status_index');
            $table->index('alumnus_batch', 'alumni_batch_index');
        });

        Schema::table('chat_tickets', function (Blueprint $table) {
            // Live queue counts + avg wait/resolution time on the chatbot page.
            $table->index('status', 'ct_status_index');
            $table->index(['status', 'escalated_at'], 'ct_status_escalated_index');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex('ja_status_hired_at_index');
            $table->dropIndex('ja_job_is_read_index');
            $table->dropIndex('ja_job_status_index');
            $table->dropIndex('ja_alumnus_status_index');
        });
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropIndex('un_user_read_at_index');
            $table->dropIndex('un_user_created_at_index');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_convo_created_index');
            $table->dropIndex('msg_receiver_read_index');
            $table->dropIndex('msg_created_at_index');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('convo_created_at_index');
        });
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropIndex('alumni_employment_status_index');
            $table->dropIndex('alumni_batch_index');
        });
        Schema::table('chat_tickets', function (Blueprint $table) {
            $table->dropIndex('ct_status_index');
            $table->dropIndex('ct_status_escalated_index');
        });
    }
};
