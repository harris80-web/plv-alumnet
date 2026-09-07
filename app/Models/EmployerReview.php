<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per (employer, alumnus) pair — an alumnus's up/downvote on a
 * company, an independent optional 5-star rating, plus an optional written
 * review. Unique on that pair (see migration), so casting a new vote or
 * rating updates this same row instead of stacking duplicates — see
 * EmployerReviewController::vote(). Both vote and rating are nullable: an
 * alumnus can vote without rating, rate without voting, or do both.
 */
class EmployerReview extends Model
{
    protected $fillable = [
        'employer_id',
        'alumnus_id',
        'vote',
        'rating',
        'review_body',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public const VOTES = ['upvote', 'downvote'];
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

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
