<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPostingSkill extends Model
{
    //
    protected $table = 'job_posting_skills';
 
    protected $fillable = [
        'job_posting_id',
        'skill_id',
        'is_required',
        'weight',
    ];
 
    protected $casts = [
        'is_required' => 'boolean',
        'weight' => 'decimal:2',
    ];
 
    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id', 'job_posting_id');
    }
 
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id', 'skill_id');
    }
}
