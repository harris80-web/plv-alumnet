<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'message_content',
        'message_read'
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
}
