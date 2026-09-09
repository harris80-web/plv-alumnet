<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per (employer, alumnus) pair — an alumnus's optional 5-star
 * rating on a company plus an optional written review. Unique on that pair
 * (see migration), so casting a new rating updates this same row instead
 * of stacking duplicates — see EmployerReviewController::vote(). Rating is
 * restricted to alumni the company actually hired (Alumnus::
 * wasHiredByEmployer()) — one rating per company, ever, regardless of how
 * many of that company's postings they applied to.
 *
 * Up/downvotes are a separate concept now, in JobPostingVote — per JOB
 * POSTING rather than per company, and open to any alumnus regardless of
 * hire status (see Employer::votes()/upvoteCount()/downvoteCount()).
 */
class EmployerReview extends Model
{
    protected $fillable = [
        'employer_id',
        'alumnus_id',
        'rating',
        'review_body',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

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
}
