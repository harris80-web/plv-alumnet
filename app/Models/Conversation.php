<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    //
    protected $primaryKey = 'conversation_id';

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
}
