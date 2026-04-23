<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTicket extends Model
{
    //
    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'user_query', 
        'office_response', 
        'ticket_status'
    ];

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function office()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Office::class, 'office_id', 'office_id');
    }
}
