<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * conversations.conversation_user_a/_b and messages.sender_id/receiver_id
 * were created referencing users.user_id, but ConversationController and
 * MessageController gate every single method on user_role === 'alumni'
 * with no exceptions for staff/employer — messaging is alumni-only in
 * practice, so the FK should say so too, same reasoning as the
 * certifications.alumnus_id fix. Verified before writing this migration:
 * zero existing conversations/messages rows have a non-alumni participant,
 * so every row already satisfies the tighter constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['conversation_user_a']);
            $table->dropForeign(['conversation_user_b']);
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('conversation_user_a')->references('user_id')->on('alumni')->cascadeOnDelete();
            $table->foreign('conversation_user_b')->references('user_id')->on('alumni')->cascadeOnDelete();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
        });
        Schema::table('messages', function (Blueprint $table) {
            // No cascadeOnDelete — the original constraint was ON DELETE
            // RESTRICT (Laravel's default when none is specified); keeping
            // that exact behavior, only tightening the referenced table.
            $table->foreign('sender_id')->references('user_id')->on('alumni');
            $table->foreign('receiver_id')->references('user_id')->on('alumni');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')->references('user_id')->on('users');
            $table->foreign('receiver_id')->references('user_id')->on('users');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['conversation_user_a']);
            $table->dropForeign(['conversation_user_b']);
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('conversation_user_a')->references('user_id')->on('users');
            $table->foreign('conversation_user_b')->references('user_id')->on('users');
        });
    }
};
