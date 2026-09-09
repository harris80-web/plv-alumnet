<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per (job posting, alumnus) — an alumnus's up/downvote on ONE
 * specific job posting, not on the company as a whole. Any alumnus can
 * vote on any posting regardless of whether that company ever hired them
 * (unlike EmployerReview's star rating, which stays hire-gated); the same
 * alumnus can also vote separately on every other posting the same company
 * has, so a company's total vote count reflects engagement across all its
 * postings rather than being capped at one vote per alumnus ever. Unique
 * on (job_posting_id, alumnus_id) — see migration — so voting again on the
 * same posting updates this row instead of stacking a duplicate.
 */
class JobPostingVote extends Model
{
    protected $fillable = [
        'job_posting_id',
        'alumnus_id',
        'vote',
    ];

    public const VOTES = ['upvote', 'downvote'];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id', 'job_posting_id');
    }

    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }

    public function scopeUpvotes($query)
    {
        return $query->where('vote', 'upvote');
    }

    public function scopeDownvotes($query)
    {
        return $query->where('vote', 'downvote');
    }
}
