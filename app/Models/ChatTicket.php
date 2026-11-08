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

    /**
     * An AI chat with no new message for this long counts as finished.
     * AI chats never get a "resolved" status (only agents resolve
     * tickets), so idle time is the only sign the person is done.
     */
    public const AI_IDLE_MINUTES = 30;

    /**
     * Tickets where the person actually asked something. Opening the
     * widget creates a ticket with just the greeting, and those shouldn't
     * count as sessions the AI handled.
     */
    public function scopeAsked($query)
    {
        return $query->whereHas('messages', fn ($q) => $q->where('sender_type', 'user'));
    }

    /** Still with the AI and nobody has written for AI_IDLE_MINUTES. */
    private function whereIdleWithAi($query)
    {
        return $query->asked()
            ->where('status', 'ai_active')
            ->whereNull('escalated_at')
            ->whereDoesntHave('messages', fn ($q) => $q->where('created_at', '>=', now()->subMinutes(self::AI_IDLE_MINUTES)));
    }

    /** Finished with the AI, and the AI's last reply was an answer (failed_attempts resets to 0 on every answer). */
    public function scopeAiResolved($query)
    {
        return $this->whereIdleWithAi($query)->where('failed_attempts', 0);
    }

    /** Finished with the AI, but its last reply was "I'm not sure" — the person left without an answer. */
    public function scopeAiUnanswered($query)
    {
        return $this->whereIdleWithAi($query)->where('failed_attempts', '>', 0);
    }

    /** The person asked something and the AI chat is still going (a message within AI_IDLE_MINUTES). */
    public function scopeAiInProgress($query)
    {
        return $query->asked()
            ->where('status', 'ai_active')
            ->whereNull('escalated_at')
            ->whereHas('messages', fn ($q) => $q->where('created_at', '>=', now()->subMinutes(self::AI_IDLE_MINUTES)));
    }

    /**
     * AI Resolution Rate: of finished sessions, the share the AI answered
     * without handing over to staff. Sessions still in progress are left
     * out, since their outcome isn't known yet.
     *
     * @param  callable|null  $scope  narrows every count the same way (e.g. "created this week")
     * @return array{aiResolved:int, unanswered:int, escalated:int, finished:int, inProgress:int, rate:int}
     */
    public static function aiOutcomeStats(?callable $scope = null): array
    {
        $base = fn () => tap(static::query(), fn ($q) => $scope ? $scope($q) : null);

        $aiResolved = $base()->aiResolved()->count();
        $unanswered = $base()->aiUnanswered()->count();
        $escalated = $base()->whereNotNull('escalated_at')->count();
        $finished = $aiResolved + $unanswered + $escalated;

        return [
            'aiResolved' => $aiResolved,
            'unanswered' => $unanswered,
            'escalated' => $escalated,
            'finished' => $finished,
            'inProgress' => $base()->aiInProgress()->count(),
            'rate' => $finished > 0 ? (int) round($aiResolved / $finished * 100) : 0,
        ];
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
