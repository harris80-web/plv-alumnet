<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'industry_id', 
        'employer_position', 
        'employer_company_name',
        'employer_company_logo',
        'employer_year_established',
        'employer_website_url',
        'employer_company_size',
        'employer_company_document',
        'employer_id_picture',
        'employer_id_picture_selfie',
        'employer_company_id_picture',
        'employer_company_id_picture_selfie',
        'employer_approved',
    ];

    protected $casts = [
        'employer_approved' => 'boolean',
        'employer_year_established' => 'integer',
    ];

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function industry()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Industry::class, 'industry_id', 'industry_id');
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class, 'user_id', 'user_id');
    }

    /** Star ratings/written reviews only now — up/downvotes live on JobPostingVote instead (see votes() below). */
    public function reviews()
    {
        return $this->hasMany(EmployerReview::class, 'employer_id', 'user_id');
    }

    /**
     * Every up/downvote cast on ANY of this employer's job postings — a
     * vote is per-posting now (JobPostingVote, unique per posting+alumnus),
     * not per-company, so the same alumnus can vote on more than one of
     * this employer's postings and each counts separately here.
     */
    public function votes()
    {
        return $this->hasManyThrough(
            JobPostingVote::class,
            JobPosting::class,
            'user_id',        // FK on job_postings referencing employers.user_id
            'job_posting_id',  // FK on job_posting_votes referencing job_postings.job_posting_id
            'user_id',         // local key on employers
            'job_posting_id'   // local key on job_postings
        );
    }

    /** Saved company addresses — picked from a dropdown when posting a job instead of retyping. */
    public function addresses()
    {
        return $this->hasMany(EmployerAddress::class, 'employer_id', 'user_id');
    }

    /**
     * Uses the already-loaded `votes` collection when eager-loaded (see
     * JobPostingController::filteredJobPostingsQuery()), so listing job
     * cards doesn't run 2 extra count queries per card — falls back to a
     * real query only when votes weren't eager-loaded (e.g. the reviews
     * page itself, one employer at a time).
     */
    public function upvoteCount(): int
    {
        return $this->relationLoaded('votes')
            ? $this->votes->where('vote', 'upvote')->count()
            : $this->votes()->where('vote', 'upvote')->count();
    }

    public function downvoteCount(): int
    {
        return $this->relationLoaded('votes')
            ? $this->votes->where('vote', 'downvote')->count()
            : $this->votes()->where('vote', 'downvote')->count();
    }

    /** Count of alumni who've left a 5-star rating (independent of vote) on this company. */
    public function ratingCount(): int
    {
        return $this->relationLoaded('reviews')
            ? $this->reviews->whereNotNull('rating')->count()
            : $this->reviews()->whereNotNull('rating')->count();
    }

    /** Average of every alumnus's 1-5 star rating, or null if nobody's rated yet. */
    public function averageRating(): ?float
    {
        $ratings = $this->relationLoaded('reviews')
            ? $this->reviews->pluck('rating')->filter()
            : $this->reviews()->whereNotNull('rating')->pluck('rating');

        return $ratings->isEmpty() ? null : round($ratings->avg(), 1);
    }

    public function scopeApproved($query)
    {
        return $query->where('employer_approved', true);
    }
}
