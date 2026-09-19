<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Three columns hold a small fixed set of values (validated server-side
 * already, confirmed against live data with no strays) but were typed as
 * a bare string instead of an enum — same raw-ALTER approach as the
 * project's other enum migrations, since doctrine/dbal isn't installed:
 *
 * - chat_tickets.status: ai_active|waiting_agent|with_agent|resolved
 *   (documented progression in the table's own migration comment).
 * - message_flags.status: pending|warned|muted|dismissed
 *   (ChatTicketController::updateFlagStatus() validates in:warned,muted,dismissed).
 * - alumni.alumnus_gender: male|female|prefer_not_to_say
 *   (UserController validates 'required|in:male,female,prefer_not_to_say',
 *   matching Alumnus::genderLabels() exactly) — was also missing a length
 *   cap entirely (plain `string()`, i.e. varchar(255), for 3-word values).
 *
 * job_postings.job_posting_image is a different kind of odd type: not a
 * fixed-value-set problem, just `text` (MySQL's unbounded type) for what's
 * always a short storage path like "jobImages/softdev.png" — narrowed to
 * varchar(255).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE chat_tickets MODIFY status ENUM('ai_active','waiting_agent','with_agent','resolved') NOT NULL DEFAULT 'ai_active'");
        DB::statement("ALTER TABLE message_flags MODIFY status ENUM('pending','warned','muted','dismissed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE alumni MODIFY alumnus_gender ENUM('male','female','prefer_not_to_say') NULL");
        DB::statement("ALTER TABLE job_postings MODIFY job_posting_image VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE job_postings MODIFY job_posting_image TEXT NULL");
        DB::statement("ALTER TABLE alumni MODIFY alumnus_gender VARCHAR(255) NULL");
        DB::statement("ALTER TABLE message_flags MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE chat_tickets MODIFY status VARCHAR(20) NOT NULL DEFAULT 'ai_active'");
    }
};
