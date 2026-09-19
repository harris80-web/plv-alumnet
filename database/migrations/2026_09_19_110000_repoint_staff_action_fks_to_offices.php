<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff-action columns pointed at the shared users table even though only
 * staff (admin/super_admin) ever write them — alumni_ids/alumni_yearbooks
 * .updated_by, faqs/notices.created_by, message_flags.reviewed_by. Every
 * writer's authorizeStaff() allows just those two roles, and both have an
 * offices row, so they now reference offices.user_id (unique as of
 * 2026_09_19_100000). The stored value is unchanged — still the staff
 * member's user id — so the belongsTo(User) relations that show their name
 * keep working.
 *
 * Verified before writing this: every existing non-null value already has
 * an offices row. The writers call User::staffActorId(), which creates the
 * office row first if a staff account lacks one, so the new FK can't reject a
 * legitimate save. ON DELETE stays SET NULL, as before.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'alumni_ids' => 'updated_by',
        'alumni_yearbooks' => 'updated_by',
        'faqs' => 'created_by',
        'notices' => 'created_by',
        'message_flags' => 'reviewed_by',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
            });
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->foreign($column)->references('user_id')->on('offices')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $column) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
            });
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->foreign($column)->references('user_id')->on('users')->nullOnDelete();
            });
        }
    }
};
