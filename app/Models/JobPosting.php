<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class JobPosting extends Model
{
    //
    use SoftDeletes;
    protected $primaryKey = 'job_posting_id';
    public $incrementing = false; 
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
}
