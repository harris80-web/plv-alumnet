<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    //
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
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'conversationuser_a_id', 'user_id');
    }

    public function userB()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'conversation_user_b_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
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
}
