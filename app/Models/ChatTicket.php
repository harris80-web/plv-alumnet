<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTicket extends Model
{
    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'user_id',
        'office_id',
        'status',
        'failed_attempts',
        'escalated_at',
        'claimed_at',
        'resolved_at',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'claimed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /** ai_active -> waiting_agent -> with_agent -> resolved. */
    public const STATUSES = ['ai_active', 'waiting_agent', 'with_agent', 'resolved'];

    public static function statusLabels(): array
    {
        return [
            'ai_active' => 'AI Active',
            'waiting_agent' => 'Waiting for Agent',
            'with_agent' => 'With Agent',
            'resolved' => 'Resolved',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            'ai_active' => 'bg-blue-100 text-blue-700',
            'waiting_agent' => 'bg-amber-100 text-amber-700',
            'with_agent' => 'bg-purple-100 text-purple-700',
            'resolved' => 'bg-green-100 text-green-700',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id', 'office_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatTicketMessage::class, 'ticket_id', 'ticket_id')->orderBy('id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatTicketMessage::class, 'ticket_id', 'ticket_id')->latestOfMany();
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['ai_active', 'waiting_agent', 'with_agent']);
    }

    public function scopeWaitingAgent($query)
    {
        return $query->where('status', 'waiting_agent');
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function badgeClass(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-slate-100 text-slate-500';
    }

    /** Moves an AI-only session into the live queue once it can't help. */
    public function escalate(): void
    {
        $this->status = 'waiting_agent';
        $this->escalated_at = now();
        $this->save();
    }

    /** An agent picks this ticket up from the queue. */
    public function claimBy(Office $office): void
    {
        $this->office_id = $office->office_id;
        $this->status = 'with_agent';
        $this->claimed_at = now();
        $this->save();
    }

    public function resolve(): void
    {
        $this->status = 'resolved';
        $this->resolved_at = now();
        $this->save();
    }
}
