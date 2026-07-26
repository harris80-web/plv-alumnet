<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    //
    protected $table = 'skills';
    protected $primaryKey = 'skill_id';
 
    protected $fillable = [
        'skill_name',
        'skill_category',
    ];
 
    public function alumni()
    {
        return $this->belongsToMany(Alumnus::class, 'alumnus_skill', 'skill_id', 'alumnus_id')
            ->withTimestamps();
    }
 
    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'job_posting_skills', 'skill_id', 'job_posting_id')
            ->withPivot(['is_required', 'weight'])
            ->withTimestamps();
    }
 
    /**
     * Used by the skill typeahead endpoint (Tom Select / Choices.js)
     * so alumni pick from the master list instead of free-typing —
     * this is what prevents duplicate skill rows.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('skill_name', 'like', "%{$term}%")
            ->orderBy('skill_name');
    }
 
    public function scopeInCategory($query, string $category)
    {
        return $query->where('skill_category', $category);
    }
}
