<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    //
    protected $primaryKey = 'program_id';

    protected $fillable = [
        'program_name'
    ];

    /**
     * Which of the (coarse, 6-category) seeded industries count as "aligned"
     * with each program — no pivot table for this since it's a small, mostly
     * static 1-to-1(ish) mapping, not user-editable data. Keyed by
     * program_name (not program_id) to match the lookup-by-name convention
     * already used elsewhere for Program (see UserController::importAlumniCsv()).
     * Deliberately conservative: a program with no genuinely fitting
     * category (e.g. Communication) is left out entirely rather than forced
     * into a loose match, so "aligned" stays a meaningful signal.
     */
    private const ALIGNED_INDUSTRIES = [
        'Bachelor of Early Childhood Education' => ['Education'],
        'Bachelor of Science in Accountancy' => ['Business & Finance'],
        'Bachelor of Science in Business Administration Major in Financial Management' => ['Business & Finance'],
        'Bachelor of Science in Business Administration Major in Human Resource Management' => ['Business & Finance'],
        'Bachelor of Science in Business Administration Major in Marketing Management' => ['Business & Finance'],
        'Bachelor of Science in Civil Engineering' => ['Engineering & Construction'],
        'Bachelor of Science in Electrical Engineering' => ['Engineering & Construction'],
        'Bachelor of Science in Information Technology' => ['Technology'],
        'Bachelor of Science in Psychology' => ['Healthcare', 'Government & Public Service'],
        'Bachelor of Public Administration' => ['Government & Public Service'],
        'Bachelor of Science in Social Work' => ['Government & Public Service', 'Healthcare'],
        'Bachelor of Secondary Education Major in English' => ['Education'],
        'Bachelor of Secondary Education Major in Filipino' => ['Education'],
        'Bachelor of Secondary Education Major in Mathematics' => ['Education'],
        'Bachelor of Secondary Education Major in Science' => ['Education'],
        'Bachelor of Secondary Education Major in Social Studies' => ['Education'],
    ];

    public function isAlignedWithIndustry(?Industry $industry): bool
    {
        if (!$industry) {
            return false;
        }

        return in_array($industry->industry_name, self::ALIGNED_INDUSTRIES[$this->program_name] ?? [], true);
    }

    public function alumni()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(Alumnus::class, 'program_id', 'program_id');
    }

    public function jobPostings()
    {
        // hasMany(RelatedModel, foreignKey, localKey)
        return $this->hasMany(JobPosting::class, 'program_id', 'program_id');
    }

    public function educations()
    {
        return $this->hasMany(Education::class, 'program_id', 'program_id');
    }
}
