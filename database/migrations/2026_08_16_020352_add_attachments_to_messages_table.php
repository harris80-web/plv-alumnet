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
        Schema::table('messages', function (Blueprint $table) {
            // message_content stays NOT NULL (avoids requiring doctrine/dbal
            // just to relax it) — an attachment-only message stores '' there;
            // see MessageController::store().
            $table->string('attachment_path', 255)->nullable()->after('message_content');
            $table->string('attachment_original_name', 255)->nullable()->after('attachment_path');
            $table->string('attachment_mime_type', 100)->nullable()->after('attachment_original_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_original_name', 'attachment_mime_type']);
        });
    }
};
