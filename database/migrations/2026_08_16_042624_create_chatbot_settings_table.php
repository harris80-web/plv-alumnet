<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Single-row settings table (see ChatbotSetting::current()) — a fixed, known set of toggles, not dynamic key/value data. */
    public function up(): void
    {
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('ai_chatbot_enabled')->default(true);
            $table->boolean('live_agent_escalation_enabled')->default(true);
            $table->boolean('job_board_queries_enabled')->default(true);
            $table->boolean('events_queries_enabled')->default(true);
            $table->boolean('general_faq_queries_enabled')->default(true);
            $table->boolean('career_advice_queries_enabled')->default(true);

            $table->unsignedTinyInteger('escalate_after_failed_attempts')->default(3);
            $table->boolean('auto_assign_available_agent')->default(false);
            $table->boolean('allow_queue_estimation')->default(true);
            $table->boolean('live_agent_notification')->default(true);

            $table->boolean('chat_auditing_enabled')->default(true);
            $table->boolean('money_transfer_detection')->default(true);
            $table->boolean('personal_info_detection')->default(true);
            $table->boolean('external_link_detection')->default(true);
            $table->boolean('auto_notify_admin_on_flag')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
