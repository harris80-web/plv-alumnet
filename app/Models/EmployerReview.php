<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per (employer, alumnus) pair — an alumnus's up/downvote on a
 * company, plus an optional written review. Unique on that pair (see
 * migration), so casting a new vote updates this same row instead of
 * stacking duplicates — see EmployerReviewController::vote().
 */
class EmployerReview extends Model
{
    protected $fillable = [
        'employer_id',
        'alumnus_id',
        'vote',
        'review_body',
    ];

    public const VOTES = ['upvote', 'downvote'];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id', 'user_id');
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
