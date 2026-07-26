<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class JobPosting extends Model
{
    //
    use SoftDeletes;
    protected $primaryKey = 'job_posting_id';
    public $incrementing = true; 
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'job_posting_id',
        'user_id',
        'job_posting_title',
        'job_posting_company',
        'job_posting_address',
        'job_posting_employment_type',
        'job_posting_setup',
        'job_posting_description',
        'job_closing_date',
        'job_approved',
        'job_posting_image',
    ];

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('job_closing_date', '>=', Carbon::today());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('closing_date', '<', Carbon::today());
    }

    public function employer()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Employer::class, 'user_id', 'user_id');
    }

    public function office()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Office::class, 'user_id', 'user_id');
    }

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_posting_skills', 'job_posting_id', 'skill_id')
            ->withPivot(['is_required', 'weight'])
            ->withTimestamps();
    }
 
    public function jobPostingSkills()
    {
        return $this->hasMany(JobPostingSkill::class, 'job_posting_id', 'job_posting_id');
    }

    public function program()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function programs()
    {
        // job_program is the table name, job_posting_id and program_id are the keys
        return $this->belongsToMany(Program::class, 'job_program', 'job_posting_id', 'program_id');
    }

    public function applicants()
    {
        // Assuming your pivot table is 'applications' and links to 'alumni'
        return $this->belongsToMany(Alumnus::class, 'job_applications', 'job_id', 'alumnus_id')
            ->withPivot('application_status', 'application_date') // Allows you to access $job->pivot->status
            ->withTimestamps();
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id', 'job_posting_id');
    }
 
    public function matches()
    {
        return $this->hasMany(JobMatch::class, 'job_posting_id', 'job_posting_id');
    }
 
    /**
     * Applicants ranked by their frozen application_score (fair,
     * point-in-time ranking — not affected by later resume edits).
     */
    public function rankedApplicants()
    {
        return $this->applications()->with('alumnus.user')->orderByDesc('application_score');
    }
 
    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('job_approved', true);
    }
 
    public function scopeOpen($query)
    {
        return $query->where('job_closing_date', '>=', now()->toDateString());
    }
 
    public function scopeForProgram($query, $programId)
    {
        return $query->whereHas('programs', fn ($q) => $q->where('programs.program_id', $programId));
    }
}
