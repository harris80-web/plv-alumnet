<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'message',
    ];

    public function ticket()
    {
        return $this->belongsTo(ChatTicket::class, 'ticket_id', 'ticket_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }

    /** JSON shape shared by the widget's initial render and its poll responses. */
    public function toChatArray(): array
    {
        return [
            'id' => $this->id,
            'senderType' => $this->sender_type,
            'senderName' => $this->sender_type === 'agent' ? trim(($this->sender->user_first_name ?? '') . ' ' . ($this->sender->user_last_name ?? '')) : null,
            'message' => $this->message,
            'timeLabel' => $this->created_at->format('h:i A'),
        ];
    }
}
