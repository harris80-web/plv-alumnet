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
     * Where clicking this notification should go — only for types with one
     * unambiguous, role-correct destination page (no specific-record deep
     * link, since none of these types store which job/application/etc. they
     * were about). alumni_id_status and yearbook_status are deliberately
     * left unmapped: there's no confirmed alumnus-facing page for either
     * yet, and linking to the admin-only management page would 403 the
     * alumnus who actually receives these.
     */
    public function targetUrl(): ?string
    {
        return match ($this->type) {
            'job_posting_submitted' => route('jobPosting.jobManagement'),
            'employer_registration_pending' => route('superAdmin.userManagement'),
            'testimonial_submitted' => route('testimonials.manage'),
            'live_agent_escalation' => route('chatbot.management'),
            'job_posting_approved', 'job_posting_rejected' => route('jobPosting.myJobPosts', ['id' => $this->user_id]),
            'job_application_hired', 'job_application_declined', 'job_application_shortlisted' => route('jobPosting.myApplications'),
            'message_mute', 'message_warning' => route('messages.index'),
            default => null,
        };
    }
}
