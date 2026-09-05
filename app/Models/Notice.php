<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'category',
        'title',
        'thumbnail',
        'event_datetime',
        'location',
        'description',
        'recipient',
        'speaker_name',
        'speaker_topic',
        'created_by',
    ];

    protected $casts = [
        'event_datetime' => 'datetime',
    ];

    public const CATEGORIES = ['event', 'seminar', 'announcement'];
    public const RECIPIENTS = ['alumni', 'employer', 'everyone'];

    public static function categoryLabels(): array
    {
        return [
            'event' => 'Event',
            'seminar' => 'Seminar',
            'announcement' => 'Announcement',
        ];
    }

    public static function categoryBadgeClasses(): array
    {
        return [
            'event' => 'bg-blue-100 text-blue-700',
            'seminar' => 'bg-purple-100 text-purple-700',
            'announcement' => 'bg-amber-100 text-amber-700',
        ];
    }

    public static function recipientLabels(): array
    {
        return [
            'alumni' => 'Alumni Only',
            'employer' => 'Employer Only',
            'everyone' => 'Everyone',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /** Alumni who marked interest/attendance — backs the Attendance column. */
    public function interestedAlumni()
    {
        return $this->belongsToMany(Alumnus::class, 'notice_interests', 'notice_id', 'alumnus_id')
            ->withTimestamps();
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? $this->category;
    }

    public function categoryBadgeClass(): string
    {
        return self::categoryBadgeClasses()[$this->category] ?? 'bg-slate-100 text-slate-500';
    }

    public function recipientLabel(): string
    {
        return self::recipientLabels()[$this->recipient] ?? $this->recipient;
    }

    /** Falls back to a stock image per category so cards never show a broken image. */
    public function thumbnailUrl(): string
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }

        return asset('assets/Landing Page/Event.png');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /** Alumni-facing pages only show notices actually meant for alumni. */
    public function scopeVisibleToAlumni($query)
    {
        return $query->whereIn('recipient', ['alumni', 'everyone']);
    }

    /** Employer-facing pages only show notices actually meant for employers (or everyone). */
    public function scopeVisibleToEmployer($query)
    {
        return $query->whereIn('recipient', ['employer', 'everyone']);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_datetime', '>=', now());
    }

    /**
     * "Lastname, Firstname" per interested alumnus — the admin view modal
     * lists these directly (see NoticeController::index()'s eager load of
     * interestedAlumni.user) rather than just the numeric Attendance count.
     */
    public function interestedAlumniNames(): array
    {
        return $this->interestedAlumni
            ->map(fn (Alumnus $alumnus) => trim($alumnus->user->user_last_name . ', ' . $alumnus->user->user_first_name))
            ->values()
            ->all();
    }
}
