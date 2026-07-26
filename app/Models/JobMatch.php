<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobMatch extends Model
{
    //

    protected $table = 'job_matches';
    protected $primaryKey = 'match_id';
 
    protected $fillable = [
        'job_posting_id',
        'alumnus_id',
        'score',
        'score_breakdown',
        'computed_at',
    ];
 
    protected $casts = [
        'score' => 'decimal:2',
        'score_breakdown' => 'array', // JSON: e.g. {"skills":42.5,"program":15,"experience":20,"certifications":8}
        'computed_at' => 'datetime',
    ];
 
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id', 'job_posting_id');
    }
 
    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }
 
    /**
     * This is the "live" ranking (recalculates as resumes/postings change).
     * Use JobApplication::application_score instead when ranking actual
     * applicants for a specific posting, to keep ranking fair over time.
     */
    public function scopeRanked($query)
    {
        return $query->orderByDesc('score');
    }
}
