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
        'industry_id',
        'alumnus_employment_status',
        'alumnus_first_job_date',
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
        'alumnus_batch' => 'integer',
        'alumnus_first_job_date' => 'date',
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

    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id', 'industry_id');
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

    public function bookmarkedJobs()
    {
        return $this->belongsToMany(JobPosting::class, 'job_bookmarks', 'alumnus_id', 'job_id')
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

    /**
     * Single source of truth for both the completeness score and the visible
     * checklist in the wizard — otherwise a threshold like "summary must be
     * >40 chars" ends up silently withholding points with no way for the
     * alumnus to see why they're stuck below 100%.
     *
     * Weighted, not equal-split — there's no builder step for `educations`
     * (EducationController is an unwired stub), so that section is left out
     * entirely rather than being a factor no one can ever satisfy.
     */
    public function completenessBreakdown(): array
    {
        return [
            [
                'key' => 'summary',
                'label' => 'Professional summary (more than 40 characters)',
                'done' => mb_strlen((string) $this->alumnus_resume_summary) > 40,
                'points' => 15,
            ],
            [
                'key' => 'linkedin',
                'label' => 'LinkedIn URL',
                'done' => !empty($this->linkedin_url),
                'points' => 10,
            ],
            [
                'key' => 'skills',
                'label' => 'At least one skill',
                'done' => $this->skills()->exists(),
                'points' => 25,
            ],
            [
                'key' => 'experiences',
                'label' => 'At least one work experience or project',
                'done' => $this->experiences()->exists(),
                'points' => 30,
            ],
            [
                'key' => 'certifications',
                'label' => 'At least one certification, seminar, or training',
                'done' => $this->certifications()->exists(),
                'points' => 20,
            ],
        ];
    }

    public function calculateResumeCompleteness(): int
    {
        $score = array_sum(array_map(
            fn ($item) => $item['done'] ? $item['points'] : 0,
            $this->completenessBreakdown()
        ));

        return min(100, (int) $score);
    }

    public function refreshResumeCompleteness(): void
    {
        $this->alumnus_resume_completeness = $this->calculateResumeCompleteness();
        $this->save();
    }

    public function isResumeComplete(): bool
    {
        return $this->alumnus_resume_completeness >= 100;
    }

    /**
     * Shape consumed by the resume builder wizard (resources/views/alumni/profile.blade.php)
     * to prefill itself on load. Single source of truth so the profile page and the
     * standalone resume.build route can't drift apart on field names again.
     */
    public function toResumeFormArray(): array
    {
        return [
            'resume_summary' => $this->alumnus_resume_summary,
            'linkedin_url' => $this->linkedin_url,
            'resume_completeness' => $this->alumnus_resume_completeness ?? 0,
            'skills' => $this->skills->map(fn ($skill) => [
                'name' => $skill->skill_name,
            ])->values(),
            'experiences' => $this->experiences->map(fn ($exp) => [
                'type' => $exp->experience_type,
                'job_title' => $exp->experience_job_title,
                'job_description' => $exp->experience_job_description,
                'duration_months' => $exp->experience_duration_months,
                'industry_id' => $exp->industry_id,
            ])->values(),
            'certifications' => $this->certifications->map(fn ($cert) => [
                'certification_type' => $cert->certification_type,
                'certification_name' => $cert->certification_name,
                'certification_from' => $cert->certification_from,
                'certification_date' => optional($cert->certification_date)->format('Y-m-d'),
            ])->values(),
        ];
    }

    /**
     * Shared by the on-screen resume view and the PDF template so the two
     * never describe the same alumnus differently.
     */
    public function resumeFullName(): string
    {
        return collect([
            $this->user->user_first_name,
            $this->user->user_middle_name,
            $this->user->user_last_name,
            $this->user->user_suffix,
        ])->filter()->implode(' ');
    }

    public static function formatExperienceDuration(?int $months): ?string
    {
        $months = (int) $months;
        if ($months <= 0) {
            return null;
        }

        $years = intdiv($months, 12);
        $rem = $months % 12;
        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' yr' . ($years > 1 ? 's' : '');
        }
        if ($rem > 0) {
            $parts[] = $rem . ' mo' . ($rem > 1 ? 's' : '');
        }

        return implode(' ', $parts);
    }

    public static function certificationTypeLabels(): array
    {
        return [
            'certification' => 'Certification',
            'seminar' => 'Seminar',
            'training' => 'Training',
        ];
    }
}
