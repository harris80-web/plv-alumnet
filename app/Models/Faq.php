<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $primaryKey = 'faq_id';

    protected $fillable = [
        'faq_question',
        'faq_answer',
        'faq_recipient',
        'created_by',
    ];

    public const RECIPIENTS = ['everyone', 'alumni', 'employer'];

    public static function recipientLabels(): array
    {
        return [
            'everyone' => 'Everyone',
            'alumni' => 'Alumni Only',
            'employer' => 'Employer Only',
        ];
    }

    public static function recipientBadgeClasses(): array
    {
        return [
            'everyone' => 'bg-green-100 text-green-700',
            'alumni' => 'bg-blue-100 text-blue-700',
            'employer' => 'bg-purple-100 text-purple-700',
        ];
    }

    /** Solid variant of recipientBadgeClasses() for buttons (bulk recipient change) rather than soft badges. */
    public static function recipientButtonClasses(): array
    {
        return [
            'everyone' => 'bg-green-600 hover:bg-green-700',
            'alumni' => 'bg-blue-500 hover:bg-blue-600',
            'employer' => 'bg-purple-500 hover:bg-purple-600',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function recipientLabel(): string
    {
        return self::recipientLabels()[$this->faq_recipient] ?? $this->faq_recipient;
    }

    public function badgeClass(): string
    {
        return self::recipientBadgeClasses()[$this->faq_recipient] ?? 'bg-slate-100 text-slate-500';
    }

    /** Alumni-facing FAQ page only shows FAQs actually meant for alumni (or everyone). */
    public function scopeVisibleToAlumni($query)
    {
        return $query->whereIn('faq_recipient', ['alumni', 'everyone']);
    }

    /** Employer-facing FAQ page only shows FAQs actually meant for employers (or everyone). */
    public function scopeVisibleToEmployer($query)
    {
        return $query->whereIn('faq_recipient', ['employer', 'everyone']);
    }

    /** Public, not-logged-in FAQ page — only the ones that apply regardless of role. */
    public function scopeVisibleToGeneral($query)
    {
        return $query->where('faq_recipient', 'everyone');
    }
}
