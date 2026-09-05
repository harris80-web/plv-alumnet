<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Named UserNotification (not Notification) and kept on its own
 * user_notifications table deliberately — Laravel's built-in Notifiable
 * trait (already used on User) already owns the name `notifications()` and
 * expects its own differently-shaped table. Reusing that name/table here
 * would silently collide with the framework's own notification system.
 */
class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'reference_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function toNotificationArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'read' => $this->read_at !== null,
            'timeLabel' => $this->created_at->diffForHumans(),
            'targetUrl' => $this->targetUrl(),
        ];
    }

    /**
     * Where clicking this notification should go. Types backed by a stored
     * reference_id (see the 2026_09_04_133641 migration) deep-link straight
     * to the specific record — e.g. a "new event posted" notification opens
     * that exact event's detail modal via the same ?notice= param the
     * dashboard's own event cards use, not just the general events list.
     * Types below without a reference_id fall back to their nearest list
     * page. alumni_id_status/yearbook_status have no admin-safe page to
     * deep-link an alumnus into, so they go to the dashboard section that
     * shows their own status instead of nowhere.
     */
    public function targetUrl(): ?string
    {
        return match ($this->type) {
            'new_event' => route('notices.eventsSeminars', array_filter(['tab' => 'events', 'notice' => $this->reference_id])),
            'new_seminar' => route('notices.eventsSeminars', array_filter(['tab' => 'seminar', 'notice' => $this->reference_id])),
            'new_announcement' => route('notices.announcements', array_filter(['notice' => $this->reference_id])),
            'job_posting_submitted' => route('jobPosting.jobManagement'),
            'employer_registration_pending' => route('superAdmin.userManagement'),
            'testimonial_submitted' => route('testimonials.manage'),
            'live_agent_escalation' => route('chatbot.management'),
            'job_posting_approved', 'job_posting_rejected' => route('jobPosting.myJobPosts', ['id' => $this->user_id]),
            'job_application_hired', 'job_application_declined', 'job_application_shortlisted' => route('jobPosting.myApplications'),
            'message_mute', 'message_warning' => route('messages.index'),
            'alumni_id_status', 'yearbook_status' => route('alumnus.dashboard') . '#status-section',
            default => null,
        };
    }
}
