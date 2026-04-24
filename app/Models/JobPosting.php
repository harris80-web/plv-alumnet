<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    //
    protected $primaryKey = 'job_posting_id';

    protected $fillable = [
        'employer_id',
        'program_id',
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

    public function employer()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Employer::class, 'employer_id', 'user_id');
    }

    public function salaryMin()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(SalaryMin::class, 'salary_range_min_id', 'salary_min_id');
    }

    public function salaryMax()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(SalaryMax::class, 'salary_range_max_id', 'salary_max_id');
    }

    public function program()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }
}
