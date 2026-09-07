<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // notices.recipient's 'general' value was meant to reach employers
        // (see Notice::scopeVisibleToEmployer(), which already filtered on
        // it) but was mislabeled "General Public" in the admin's Add/Edit
        // Notice form — Faq::RECIPIENTS already gets this right with an
        // explicit 'employer' value/label, so this brings Notice in line
        // with it. No existing rows used 'general' at the time of writing,
        // but the UPDATE below covers it either way.
        DB::statement("ALTER TABLE notices MODIFY recipient ENUM('alumni', 'general', 'employer', 'everyone') NOT NULL DEFAULT 'everyone'");
        DB::table('notices')->where('recipient', 'general')->update(['recipient' => 'employer']);
        DB::statement("ALTER TABLE notices MODIFY recipient ENUM('alumni', 'employer', 'everyone') NOT NULL DEFAULT 'everyone'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE notices MODIFY recipient ENUM('alumni', 'general', 'employer', 'everyone') NOT NULL DEFAULT 'everyone'");
        DB::table('notices')->where('recipient', 'employer')->update(['recipient' => 'general']);
        DB::statement("ALTER TABLE notices MODIFY recipient ENUM('alumni', 'general', 'everyone') NOT NULL DEFAULT 'everyone'");
    }
};
