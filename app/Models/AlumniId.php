<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AlumniId extends Model
{
    protected $fillable = [
        'alumnus_id',
        'status',
        'status_updated_at',
        'updated_by',
    ];

    protected $casts = [
        'status_updated_at' => 'datetime',
    ];

    /**
     * Linear claim process — index also doubles as the "next status" lookup
     * for the single Mark button (see markNext()). Kept here as the one
     * source of truth so the controller, the view's status buttons, and the
     * badge colors can't drift out of sync with each other.
     */
    public const STATUSES = ['pending', 'under_review', 'ready_to_claim', 'claimed'];

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'under_review' => 'Under Review',
            'ready_to_claim' => 'Ready to Claim',
            'claimed' => 'Claimed',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            'pending' => 'bg-amber-100 text-amber-700',
            'under_review' => 'bg-blue-100 text-blue-700',
            'ready_to_claim' => 'bg-purple-100 text-purple-700',
            'claimed' => 'bg-green-100 text-green-700',
        ];
    }

    /** Solid variant of statusBadgeClasses() for buttons (e.g. bulk status change) rather than soft badges. */
    public static function statusButtonClasses(): array
    {
        return [
            'pending' => 'bg-amber-500 hover:bg-amber-600',
            'under_review' => 'bg-blue-500 hover:bg-blue-600',
            'ready_to_claim' => 'bg-purple-500 hover:bg-purple-600',
            'claimed' => 'bg-green-600 hover:bg-green-700',
        ];
    }

    /**
     * Short enough to keep the uniform-width Mark button on one line —
     * statusLabels() itself is used for the badge text and bulk buttons,
     * where the fuller wording fits fine.
     */
    public static function markShortLabels(): array
    {
        return [
            'under_review' => 'Reviewed',
            'ready_to_claim' => 'Ready',
            'claimed' => 'Claimed',
        ];
    }

    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function badgeClass(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-slate-100 text-slate-500';
    }

    /**
     * Null once already at the last stage — the "Mark" button hides itself
     * in that case rather than looping back to pending.
     */
    public function nextStatus(): ?string
    {
        $index = array_search($this->status, self::STATUSES, true);
        return self::STATUSES[$index + 1] ?? null;
    }

    /** Advances exactly one stage, e.g. pending -> under_review. */
    public function markNext(): void
    {
        $next = $this->nextStatus();
        if ($next) {
            $this->setStatus($next);
        }
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->status_updated_at = now();
        $this->updated_by = Auth::id();
        $this->save();
    }
}
