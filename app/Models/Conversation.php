<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $primaryKey = 'conversation_id';

    protected $fillable = [
        'conversation_user_a',
        'conversation_user_b',
        'conversation_last_message_at',
        'conversation_created_at',
    ];

    protected $casts = [
        'conversation_last_message_at' => 'datetime',
        'conversation_created_at' => 'datetime',
    ];

    public function userA()
    {
        return $this->belongsTo(User::class, 'conversation_user_a', 'user_id');
    }

    public function userB()
    {
        return $this->belongsTo(User::class, 'conversation_user_b', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
    }

    /** Latest message only — the sidebar preview line, one query instead of loading the whole thread. */
    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id', 'conversation_id')->latestOfMany('message_id');
    }

    /**
     * Finds (or you can extend to create) the conversation between two
     * specific users, regardless of who is user_a vs user_b.
     */
    public function scopeBetween($query, $userIdOne, $userIdTwo)
    {
        return $query->where(function ($q) use ($userIdOne, $userIdTwo) {
            $q->where('conversation_user_a', $userIdOne)->where('conversation_user_b', $userIdTwo);
        })->orWhere(function ($q) use ($userIdOne, $userIdTwo) {
            $q->where('conversation_user_a', $userIdTwo)->where('conversation_user_b', $userIdOne);
        });
    }

    /** Conversations the given user is a participant in, either side. */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('conversation_user_a', $userId)->orWhere('conversation_user_b', $userId);
        });
    }

    /**
     * Sidebar list only shows conversations someone actually started —
     * clicking a name in "To:" creates the row immediately (so the URL is
     * navigable right away), but it shouldn't clutter the other person's
     * chat list until a first message actually exists.
     */
    public function scopeWithMessages($query)
    {
        return $query->whereHas('messages');
    }

    /** Finds the conversation between these two users, creating it if it doesn't exist yet. */
    public static function findOrCreateBetween(int $userIdOne, int $userIdTwo): self
    {
        return static::between($userIdOne, $userIdTwo)->first()
            ?? static::create([
                'conversation_user_a' => $userIdOne,
                'conversation_user_b' => $userIdTwo,
            ]);
    }

    /** Whichever participant isn't the given user — the "who am I talking to" lookup every view needs. */
    public function otherUser(int $currentUserId): ?User
    {
        $this->loadMissing(['userA', 'userB']);

        return $this->conversation_user_a === $currentUserId ? $this->userB : $this->userA;
    }

    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('receiver_id', $userId)
            ->where('message_read', false)
            ->count();
    }
}
