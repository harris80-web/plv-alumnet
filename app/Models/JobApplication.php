<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    //
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'application_status',
        'application_date',
        'alumnus_id',
        'job_id',
    ];

    public function job()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(JobPosting::class, 'job_id', 'job_posting_id');
    }
    public function alumnus()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'alumnus_id');
    }
}
