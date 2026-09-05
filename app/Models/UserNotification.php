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
     * Each destination page reads its own query param on load and either
     * auto-opens the matching record's existing detail modal (jobs,
     * testimonials, chat tickets) or scrolls to + highlights it (the
     * pending-employer table row) — same "silently no-op if it's not on
     * the currently-rendered page" tradeoff the notice deep-link already
     * accepted, since none of these pages fetch across pagination pages
     * just to satisfy a stale notification link.
     *
     * job_posting_rejected has no reference_id on purpose — that job
     * posting is deleted the moment the notification is created (see
     * JobPostingController::declineJobPost()), so there's nothing left to
     * deep-link to; it falls back to the general list like before.
     * alumni_id_status/yearbook_status have no admin-safe page to
     * deep-link an alumnus into, so they go to the dashboard section that
     * shows their own status instead of nowhere.
     */
    public function targetUrl(): ?string
    {
        return match ($this->type) {
            'new_event' => route('notices.eventsSeminars', array_filter(['tab' => 'events', 'notice' => $this->reference_id])),
            'new_seminar' => route('notices.eventsSeminars', array_filter(['tab' => 'seminar', 'notice' => $this->reference_id])),
            'new_announcement' => route('notices.announcements', array_filter(['notice' => $this->reference_id])),
            'job_posting_submitted' => route('jobPosting.jobManagement', array_filter(['job' => $this->reference_id])),
            'employer_registration_pending' => route('superAdmin.userManagement', array_filter(['tab' => 'employer', 'pendingEmployer' => $this->reference_id])),
            'testimonial_submitted' => route('testimonials.manage', array_filter(['testimonial' => $this->reference_id])),
            'live_agent_escalation' => route('chatbot.management', array_filter(['ticket' => $this->reference_id])),
            'job_posting_approved' => route('jobPosting.myJobPosts', array_filter(['id' => $this->user_id, 'job' => $this->reference_id])),
            'job_posting_rejected' => route('jobPosting.myJobPosts', ['id' => $this->user_id]),
            'job_application_hired', 'job_application_declined', 'job_application_shortlisted' => route('jobPosting.myApplications', array_filter(['job' => $this->reference_id])),
            'message_mute', 'message_warning' => $this->reference_id ? route('messages.show', $this->reference_id) : route('messages.index'),
            'alumni_id_status', 'yearbook_status' => route('alumnus.dashboard') . '#status-section',
            default => null,
        };
    }
}
