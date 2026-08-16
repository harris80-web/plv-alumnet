<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSetting extends Model
{
    protected $fillable = [
        'ai_chatbot_enabled',
        'live_agent_escalation_enabled',
        'job_board_queries_enabled',
        'events_queries_enabled',
        'general_faq_queries_enabled',
        'career_advice_queries_enabled',
        'escalate_after_failed_attempts',
        'auto_assign_available_agent',
        'allow_queue_estimation',
        'live_agent_notification',
        'chat_auditing_enabled',
        'money_transfer_detection',
        'personal_info_detection',
        'external_link_detection',
        'auto_notify_admin_on_flag',
    ];

    protected $casts = [
        'ai_chatbot_enabled' => 'boolean',
        'live_agent_escalation_enabled' => 'boolean',
        'job_board_queries_enabled' => 'boolean',
        'events_queries_enabled' => 'boolean',
        'general_faq_queries_enabled' => 'boolean',
        'career_advice_queries_enabled' => 'boolean',
        'auto_assign_available_agent' => 'boolean',
        'allow_queue_estimation' => 'boolean',
        'live_agent_notification' => 'boolean',
        'chat_auditing_enabled' => 'boolean',
        'money_transfer_detection' => 'boolean',
        'personal_info_detection' => 'boolean',
        'external_link_detection' => 'boolean',
        'auto_notify_admin_on_flag' => 'boolean',
    ];

    /** Single settings row for the whole app — created on first access if missing. */
    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }
}
