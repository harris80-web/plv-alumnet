<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageFlag extends Model
{
    protected $fillable = [
        'message_id',
        'flag_reasons',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'flag_reasons' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public const REASONS = ['money_transfer', 'personal_info', 'external_link'];
    public const STATUSES = ['pending', 'warned', 'muted', 'dismissed'];

    public static function reasonLabels(): array
    {
        return [
            'money_transfer' => 'Money Transfer',
            'personal_info' => 'Personal Info Shared',
            'external_link' => 'External Link',
        ];
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'message_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function reasonLabelsList(): array
    {
        $labels = self::reasonLabels();
        return array_map(fn ($r) => $labels[$r] ?? $r, $this->flag_reasons ?? []);
    }
}
