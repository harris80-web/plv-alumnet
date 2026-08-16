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
        ];
    }
}
