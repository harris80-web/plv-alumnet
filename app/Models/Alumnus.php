<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumnus extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'program_id',
        'section_id',
        'alumnus_employment_status',
        'alumnus_resume_summary',
        'alumnus_resume_file_path',
        'linkedin_url',
        'alumnus_resume_completeness',
        'alumnus_batch',
        'alumnus_is_public',
        'alumnus_change_password',
    ];

    protected $casts = [
        'alumnus_is_public' => 'boolean',
        'alumnus_change_password' => 'boolean',
        'alumnus_resume_completeness' => 'integer',
        'alumnus_batch' => 'integer'
    ];

    public function user()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function program()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function section()
    {
        // "I belong to one user (the employer)"
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function educations()
    {
        return $this->hasMany(Education::class, 'user_id', 'user_id');
    }

     public function experiences()
    {
        return $this->hasMany(Experience::class, 'alumnus_id', 'user_id');
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class, 'alumnus_id', 'user_id');
    }

     public function skills()
    {
        // No proficiency level by design — just presence of the skill.
        return $this->belongsToMany(Skill::class, 'alumnus_skill', 'alumnus_id', 'skill_id')
            ->withTimestamps();
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'alumnus_id', 'user_id');
    }

    public function jobMatches()
    {
        return $this->hasMany(JobMatch::class, 'alumnus_id', 'user_id');
    }

    public function testimonial()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(Testimonial::class, 'user_id', 'user_id');
    }

    public function appliedJobs()
    {
        // Assuming your pivot table is 'applications' and links to 'alumni'
        return $this->belongsToMany(JobPosting::class, 'job_applications', 'alumnus_id', 'job_id')
            ->withPivot('application_status', 'application_date') // Allows you to access $job->pivot->status
            ->withTimestamps();
    }

    public function scopePublicProfiles($query)
    {
        return $query->where('alumnus_is_public', true);
    }
 
    public function scopeInProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function calculateResumeCompleteness(): int
    {
        $sections = [
            !empty($this->alumnus_resume_summary),
            $this->educations()->exists(),
            $this->experiences()->exists(),
            $this->skills()->exists(),
            $this->certifications()->exists(),
            !empty($this->linkedin_url),
        ];
 
        $filled = count(array_filter($sections));
 
        return (int) round(($filled / count($sections)) * 100);
    }
 
    public function refreshResumeCompleteness(): void
    {
        $this->alumnus_resume_completeness = $this->calculateResumeCompleteness();
        $this->save();
    }
}
