<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message_content',
        'message_read',
        'message_created_at',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime_type',
    ];

    protected $casts = [
        'message_read' => 'boolean',
        'message_created_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id', 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('message_read', false);
    }

    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    public function isImageAttachment(): bool
    {
        return $this->hasAttachment() && Str::startsWith((string) $this->attachment_mime_type, 'image/');
    }

    public function attachmentUrl(): ?string
    {
        return $this->hasAttachment() ? asset('storage/' . $this->attachment_path) : null;
    }

    /**
     * Shape consumed by both the initial page render and the polling
     * endpoint (MessageController::poll()) so a message looks identical
     * whichever path rendered it.
     */
    public function toChatArray(): array
    {
        return [
            'id' => $this->message_id,
            'senderId' => $this->sender_id,
            'content' => $this->message_content,
            'hasAttachment' => $this->hasAttachment(),
            'isImage' => $this->isImageAttachment(),
            'attachmentUrl' => $this->attachmentUrl(),
            'attachmentName' => $this->attachment_original_name,
            'createdAt' => $this->created_at->toIso8601String(),
            'timeLabel' => $this->created_at->format('h:i A'),
        ];
    }
}
