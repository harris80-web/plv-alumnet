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
        Schema::table('users', function (Blueprint $table) {
            // Bumped by a lightweight heartbeat ping from any authenticated
            // admin-area page (see the heartbeat route/JS) — used by
            // User::isOnline() to give the chatbot's auto-assign feature
            // (ChatbotController::autoAssignIfEnabled()) genuine knowledge
            // of which admins are actually logged in right now, instead of
            // just checking the static user_active flag.
            $table->timestamp('last_active_at')->nullable()->after('user_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_active_at');
        });
    }
};
