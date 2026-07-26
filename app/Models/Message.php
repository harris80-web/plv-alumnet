<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message_content',
        'message_read',
        'message_created_at',
    ];

    protected $casts = [
        'message_read' => 'boolean',
        'message_created_at' => 'datetime',
    ];

    public function conversation()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Conversation::class, 'conversation_id', 'conversation_id');
    }
    public function sender()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
    public function receiver()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'receiver_id', 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('message_read', false);
    }
}
