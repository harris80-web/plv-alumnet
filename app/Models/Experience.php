<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    //
    protected $table = 'experiences';
    protected $primaryKey = 'experience_id';
 
    protected $fillable = [
        'alumnus_id',
        'experience_type',
        'industry_id',
        'experience_start_date',
        'experience_end_date',
        'experience_duration_months',
        'experience_job_title',
        'experience_job_description',
    ];

    protected $casts = [
        'experience_duration_months' => 'integer',
        'experience_start_date' => 'date',
        'experience_end_date' => 'date',
    ];

    /** Item 21 — null end_date (with a start_date set) means "currently ongoing". */
    public function isOngoing(): bool
    {
        return $this->experience_start_date !== null && $this->experience_end_date === null;
    }

    /** "Mon YYYY – Mon YYYY" / "Mon YYYY – Present", or null for a legacy row with no dates at all. */
    public function dateRangeLabel(): ?string
    {
        if (!$this->experience_start_date) {
            return null;
        }

        $end = $this->experience_end_date ? $this->experience_end_date->format('M Y') : 'Present';
        return $this->experience_start_date->format('M Y') . ' – ' . $end;
    }
 
    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }
 
    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id', 'industry_id');
    }
}
