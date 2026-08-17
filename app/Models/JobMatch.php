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
        'ai_score',
        'ai_explanation',
        'ai_computed_at',
        'computed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'score_breakdown' => 'array', // JSON: e.g. {"skills":42.5,"program":15,"experience":20,"certifications":8}
        'ai_score' => 'decimal:2',
        'ai_computed_at' => 'datetime',
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

    /**
     * Deterministic `score` blended with the Gemini semantic assessment
     * (70/30) once it's available — used to rank the alumnus-facing "Job
     * Matches For You" recommendations. Falls back to the deterministic
     * score alone when AI enrichment hasn't run yet (or Gemini isn't
     * configured), so recommendations still work without it.
     */
    public function blendedScore(): float
    {
        if ($this->ai_score === null) {
            return (float) $this->score;
        }

        return round(((float) $this->score * 0.7) + ((float) $this->ai_score * 0.3), 2);
    }
}
