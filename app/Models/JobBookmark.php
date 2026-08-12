<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobBookmark extends Model
{
    protected $primaryKey = 'bookmark_id';

    protected $fillable = [
        'job_id',
        'alumnus_id',
    ];

    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_id', 'job_posting_id');
    }

    public function alumnus()
    {
        return $this->belongsTo(Alumnus::class, 'alumnus_id', 'user_id');
    }
}
