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

    public function reviews()
    {
        return $this->hasMany(EmployerReview::class, 'employer_id', 'user_id');
    }

    /**
     * Uses the already-loaded `reviews` collection when eager-loaded (see
     * JobPostingController::filteredJobPostingsQuery()), so listing job
     * cards doesn't run 2 extra count queries per card — falls back to a
     * real query only when reviews weren't eager-loaded (e.g. the reviews
     * page itself, one employer at a time).
     */
    public function upvoteCount(): int
    {
        return $this->relationLoaded('reviews')
            ? $this->reviews->where('vote', 'upvote')->count()
            : $this->reviews()->where('vote', 'upvote')->count();
    }

    public function downvoteCount(): int
    {
        return $this->relationLoaded('reviews')
            ? $this->reviews->where('vote', 'downvote')->count()
            : $this->reviews()->where('vote', 'downvote')->count();
    }

    public function scopeApproved($query)
    {
        return $query->where('employer_approved', true);
    }
}
