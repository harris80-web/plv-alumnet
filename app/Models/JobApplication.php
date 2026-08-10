<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    //
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'job_id',
        'alumnus_id',
        'application_date',
        'application_status',
        'application_score',
        'is_read',
    ];

    protected $casts = [
        'application_date' => 'datetime',
        'application_score' => 'decimal:2',
        'is_read' => 'boolean',
    ];

    public function job()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(JobPosting::class, 'job_id', 'job_posting_id');
    }
    public function alumnus()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }

    public function scopeForJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }
 
    public function scopeRankedByScore($query)
    {
        return $query->orderByDesc('application_score');
    }
 
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('application_status', $status);
    }
 
    /**
     * Freezes the current live job_matches score onto this application.
     * Call this exactly once, at the moment the alumnus applies.
     */
    public function freezeScoreFromMatch(): void
    {
        $match = JobMatch::where('job_posting_id', $this->job_id)
            ->where('alumnus_id', $this->alumnus_id)
            ->first();
 
        if ($match) {
            $this->application_score = $match->score;
            $this->save();
        }
    }
}
