<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniYearbook extends Model
{
    protected $fillable = [
        'alumnus_id',
        'distribution_status',
        'distribution_scheduled_at',
        'distribution_end_time',
        'distribution_building',
        'distribution_room_floor',
        'claiming_status',
        'status_updated_at',
        'updated_by',
    ];

    protected $casts = [
        'distribution_scheduled_at' => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    /**
     * "On Hand" and "Not Yet Claimed" were removed as claiming statuses
     * entirely (see migration
     * 2026_09_06_000000_remove_intermediate_statuses_from_alumni_id_and_yearbook)
     * — same 3-stage lifecycle as AlumniId::STATUSES now. distribution_status
     * below is unrelated (whether the school has received the printed
     * yearbooks at all, not whether an individual alumnus has claimed
     * theirs) and keeps its own separate 'on_hand' value.
     */
    public const CLAIMING_STATUSES = ['pending', 'ready_to_claim', 'claimed'];
    public const DISTRIBUTION_STATUSES = ['pending', 'on_hand'];

    public static function claimingStatusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'ready_to_claim' => 'Ready to Claim',
            'claimed' => 'Claimed',
        ];
    }

    public static function claimingStatusBadgeClasses(): array
    {
        return [
            'pending' => 'bg-amber-100 text-amber-700',
            'ready_to_claim' => 'bg-purple-100 text-purple-700',
            'claimed' => 'bg-green-100 text-green-700',
        ];
    }

    public static function distributionStatusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'on_hand' => 'On Hand',
        ];
    }

    public static function distributionStatusBadgeClasses(): array
    {
        return [
            'pending' => 'bg-amber-100 text-amber-700',
            'on_hand' => 'bg-green-100 text-green-700',
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

    public function claimingStatusLabel(): string
    {
        return self::claimingStatusLabels()[$this->claiming_status] ?? $this->claiming_status;
    }

    public function claimingBadgeClass(): string
    {
        return self::claimingStatusBadgeClasses()[$this->claiming_status] ?? 'bg-slate-100 text-slate-500';
    }

    public function distributionStatusLabel(): string
    {
        return self::distributionStatusLabels()[$this->distribution_status] ?? $this->distribution_status;
    }

    public function distributionBadgeClass(): string
    {
        return self::distributionStatusBadgeClasses()[$this->distribution_status] ?? 'bg-slate-100 text-slate-500';
    }

    /**
     * Building + room/floor are stored separately (two distinct inputs in
     * the modal) but shown as one string everywhere else — this is the one
     * place that joins them so the table and dashboard card can't drift
     * apart on the format.
     */
    /** Office closing time — the default end of a claiming window. */
    public const DEFAULT_END_TIME = '17:00';

    /** "9:00 AM – 5:00 PM", or just the start time if no end is set, or null if unscheduled. */
    public function timeRangeLabel(): ?string
    {
        if (!$this->distribution_scheduled_at) {
            return null;
        }
        $start = $this->distribution_scheduled_at->format('g:i A');
        if (!$this->distribution_end_time) {
            return $start;
        }

        return $start . ' – ' . \Carbon\Carbon::createFromFormat('H:i:s', $this->distribution_end_time)->format('g:i A');
    }

    /** Date plus time window, e.g. "Nov 20, 2026, 9:00 AM – 5:00 PM". */
    public function scheduleLabel(): ?string
    {
        return $this->distribution_scheduled_at
            ? $this->distribution_scheduled_at->format('M d, Y') . ', ' . $this->timeRangeLabel()
            : null;
    }

    public function locationLabel(): string
    {
        $parts = array_filter([$this->distribution_building, $this->distribution_room_floor]);
        return $parts ? implode(' — ', $parts) : 'Not yet set';
    }

    /**
     * Applies whichever fields are present in $data (partial-update
     * friendly — the bulk-edit modal only sends fields the admin actually
     * touched, see AlumniYearbookController::bulkUpdateYearbook()) and
     * stamps who/when in one place so the two controller actions can't
     * forget to do it consistently.
     */
    public function applyUpdate(array $data, ?int $userId): void
    {
        $this->fill($data);
        // A schedule always has an end: the office's closing time unless one was given.
        if ($this->distribution_scheduled_at && !$this->distribution_end_time) {
            $this->distribution_end_time = self::DEFAULT_END_TIME;
        }
        $this->status_updated_at = now();
        $this->updated_by = $userId;
        $this->save();
    }
}
