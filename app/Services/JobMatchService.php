<?php

namespace App\Services;

use App\Models\Alumnus;
use App\Models\JobMatch;
use App\Models\JobPosting;

/**
 * Computes how well an alumnus's profile/resume fits a job posting on a
 * 0-100 scale and persists it as the "live" JobMatch row (recalculated every
 * call — distinct from JobApplication::application_score, which is frozen
 * once at the moment they apply via JobApplication::freezeScoreFromMatch()).
 *
 * Weights (skills 45 / experience 25 / program 20 / certifications 10)
 * follow the relative ordering already hinted at in JobMatch's own
 * score_breakdown docblock example, rounded to a clean 100.
 */
class JobMatchService
{
    public function scoreFor(JobPosting $job, Alumnus $alumnus): JobMatch
    {
        $breakdown = [
            'skills' => $this->scoreSkills($job, $alumnus),
            'experience' => $this->scoreExperience($alumnus),
            'program' => $this->scoreProgram($job, $alumnus),
            'certifications' => $this->scoreCertifications($alumnus),
        ];

        $score = round(array_sum($breakdown), 2);

        return JobMatch::updateOrCreate(
            ['job_posting_id' => $job->job_posting_id, 'alumnus_id' => $alumnus->user_id],
            ['score' => $score, 'score_breakdown' => $breakdown, 'computed_at' => now()]
        );
    }

    /**
     * Weighted overlap between the alumnus's skills and the job's required
     * skills (job_posting_skills.weight, 1-5). A job with no skills
     * configured gets full credit — can't penalize for an unspecified
     * requirement.
     */
    private function scoreSkills(JobPosting $job, Alumnus $alumnus): float
    {
        $requiredSkills = $job->skills;
        if ($requiredSkills->isEmpty()) {
            return 45.0;
        }

        $totalWeight = $requiredSkills->sum(fn ($s) => (int) $s->pivot->weight);
        if ($totalWeight <= 0) {
            return 45.0;
        }

        $alumnusSkillIds = $alumnus->skills->pluck('skill_id')->all();
        $matchedWeight = $requiredSkills
            ->filter(fn ($s) => in_array($s->skill_id, $alumnusSkillIds, true))
            ->sum(fn ($s) => (int) $s->pivot->weight);

        return round(($matchedWeight / $totalWeight) * 45, 2);
    }

    /**
     * 15 pts for having any work-type experience at all, plus up to 10 more
     * scaled by total months (capped at 12 months = full credit) — same
     * "presence over precision" philosophy as Alumnus::completenessBreakdown().
     */
    private function scoreExperience(Alumnus $alumnus): float
    {
        $workExperiences = $alumnus->experiences->where('experience_type', 'work');
        if ($workExperiences->isEmpty()) {
            return 0.0;
        }

        $totalMonths = $workExperiences->sum('experience_duration_months');
        $durationScore = min(10, ($totalMonths / 12) * 10);

        return round(15 + $durationScore, 2);
    }

    /** Binary — full credit if the alumnus's program is one of the job's targets. */
    private function scoreProgram(JobPosting $job, Alumnus $alumnus): float
    {
        $targetProgramIds = $job->programs->pluck('program_id')->all();
        if (empty($targetProgramIds)) {
            return 20.0;
        }

        return in_array($alumnus->program_id, $targetProgramIds, true) ? 20.0 : 0.0;
    }

    /** Presence-based, same rule as the resume completeness score. */
    private function scoreCertifications(Alumnus $alumnus): float
    {
        return $alumnus->certifications->isNotEmpty() ? 10.0 : 0.0;
    }
}
