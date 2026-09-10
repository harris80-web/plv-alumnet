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

    /** Stock image shown when an admin doesn't upload a thumbnail — one per category. */
    private const DEFAULT_THUMBNAILS = [
        'event' => 'assets/default images/event_default.svg',
        'seminar' => 'assets/default images/seminar_default.svg',
        'announcement' => 'assets/default images/announcement_default.svg',
    ];

    /**
     * Tint applied over the default thumbnail only (a real uploaded photo is
     * never tinted) — two independent opacities: the color wash on top, and
     * how visible the stock artwork stays underneath it.
     */
    private const DEFAULT_THUMBNAIL_OVERLAYS = [
        'event' => ['color' => '#C73D1A', 'overlayOpacity' => 0.8, 'imageOpacity' => 1.0],
        'seminar' => ['color' => '#1D46A4', 'overlayOpacity' => 0.7, 'imageOpacity' => 0.8],
        'announcement' => ['color' => '#ED7A07', 'overlayOpacity' => 0.7, 'imageOpacity' => 0.5],
    ];

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

        // Same str_replace(' ', '%20', ...) workaround mails/layout.blade.php
        // already uses — the folder name has a literal space, and unlike a
        // plain HTML src="..." attribute (which browsers auto-encode), the
        // notice-detail modal assigns this to img.src via JS, where an
        // unencoded space isn't reliably normalized the same way.
        $path = self::DEFAULT_THUMBNAILS[$this->category] ?? self::DEFAULT_THUMBNAILS['event'];
        return str_replace(' ', '%20', asset($path));
    }

    /** True when thumbnailUrl() is serving the stock per-category image, not an admin upload. */
    public function usesDefaultThumbnail(): bool
    {
        return !$this->thumbnail;
    }

    /** {color, overlayOpacity, imageOpacity} for the current category — only meaningful when usesDefaultThumbnail() is true. */
    public function defaultThumbnailOverlay(): array
    {
        return self::DEFAULT_THUMBNAIL_OVERLAYS[$this->category] ?? self::DEFAULT_THUMBNAIL_OVERLAYS['event'];
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

    /** Guests (not logged in) only see notices explicitly meant for the general public — not alumni- or employer-only announcements. */
    public function scopeVisibleToGuest($query)
    {
        return $query->where('recipient', 'everyone');
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
