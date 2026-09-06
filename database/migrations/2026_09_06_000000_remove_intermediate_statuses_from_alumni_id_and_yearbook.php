<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // "Under Review" removed as a status entirely, per request — any
        // record currently sitting there falls back to 'pending' (the
        // earliest remaining stage) rather than being force-advanced to
        // 'ready_to_claim', since that's a judgment call only staff should
        // make.
        DB::table('alumni_ids')->where('status', 'under_review')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE alumni_ids MODIFY status ENUM('pending', 'ready_to_claim', 'claimed') NOT NULL DEFAULT 'pending'");

        // "On Hand" and "Not Yet Claimed" removed the same way — leaves the
        // same 3-stage lifecycle (pending -> ready_to_claim -> claimed) as
        // Alumni ID above. distribution_status (a separate column tracking
        // whether the school has received the printed yearbooks at all, not
        // whether an individual alumnus has claimed theirs) is untouched —
        // it's not the "status" this request is about.
        DB::table('alumni_yearbooks')->whereIn('claiming_status', ['on_hand', 'not_yet_claimed'])->update(['claiming_status' => 'pending']);
        DB::statement("ALTER TABLE alumni_yearbooks MODIFY claiming_status ENUM('pending', 'ready_to_claim', 'claimed') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE alumni_ids MODIFY status ENUM('pending', 'under_review', 'ready_to_claim', 'claimed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE alumni_yearbooks MODIFY claiming_status ENUM('pending', 'on_hand', 'ready_to_claim', 'claimed', 'not_yet_claimed') NOT NULL DEFAULT 'pending'");
    }
};
