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
    /** Net-vote ratio needs at least this many total votes before it affects ranking — one or two votes shouldn't swing anything. */
    private const MIN_VOTES_FOR_REPUTATION = 3;

    /** Max points a company's reputation can add or subtract — small on purpose, see scoreCompanyReputation(). */
    private const REPUTATION_MAX_POINTS = 5;

    public function scoreFor(JobPosting $job, Alumnus $alumnus): JobMatch
    {
        $breakdown = [
            'skills' => $this->scoreSkills($job, $alumnus),
            'experience' => $this->scoreExperience($alumnus),
            'program' => $this->scoreProgram($job, $alumnus),
            'certifications' => $this->scoreCertifications($alumnus),
            'company_reputation' => $this->scoreCompanyReputation($job),
        ];

        // The 4 fit components already sum to 100 on their own — reputation
        // is a small nudge on top, not a 5th criterion, and is clamped so it
        // can never push a genuinely poor fit above a genuinely good one
        // (a job already at 100 on fit alone gets no further benefit from a
        // good reputation; it can only help a job that isn't already
        // maxed out — see App\Models\EmployerReview).
        $score = round(min(100, max(0, array_sum($breakdown))), 2);

        return JobMatch::updateOrCreate(
            ['job_posting_id' => $job->job_posting_id, 'alumnus_id' => $alumnus->user_id],
            ['score' => $score, 'score_breakdown' => $breakdown, 'computed_at' => now()]
        );
    }

    /**
     * Small tie-breaking bonus/penalty from the employer's alumni-submitted
     * up/downvotes (see App\Models\EmployerReview) — "the higher the
     * upvotes, the more likely their job post appears in the
     * recommendation, if the criteria is still met": this only ever adjusts
     * an already-qualifying score by up to ±5 points, it never substitutes
     * for actual fit. Neutral (0) until a company has at least
     * MIN_VOTES_FOR_REPUTATION votes, so a single early vote can't swing
     * anything.
     */
    private function scoreCompanyReputation(JobPosting $job): float
    {
        $employer = $job->employer;
        if (!$employer) {
            return 0.0;
        }

        $upvotes = $employer->upvoteCount();
        $downvotes = $employer->downvoteCount();
        $total = $upvotes + $downvotes;

        if ($total < self::MIN_VOTES_FOR_REPUTATION) {
            return 0.0;
        }

        $netRatio = ($upvotes - $downvotes) / $total; // -1 (all down) .. 1 (all up)

        return round($netRatio * self::REPUTATION_MAX_POINTS, 2);
    }

    /**
     * Recomputes the deterministic score for one alumnus against every
     * currently open+approved posting. Cheap (no external API calls), so
     * this is safe to call synchronously right after a resume save — the
     * alumnus sees fresh "Job Matches For You" rankings immediately instead
     * of waiting for the next scheduled job-matches:recompute run. AI
     * enrichment (semantic score/explanation) is layered on separately by
     * that scheduled command, not here.
     *
     * @return \Illuminate\Support\Collection<int, JobMatch>
     */
    public function refreshForAlumnus(Alumnus $alumnus)
    {
        return JobPosting::approved()->open()->get()
            ->map(fn (JobPosting $job) => $this->scoreFor($job, $alumnus));
    }

    /**
     * Presence-based overlap between the alumnus's skills and the job's
     * required skills — what fraction of the required list the alumnus
     * actually has. job_posting_skills does have a `weight` column, but
     * there's no UI for an employer to ever set it differently per skill
     * (see JobPostingController::syncJobSkills()), so every skill on every
     * job is scored equally rather than pretending a per-skill weight is in
     * effect. A job with no skills configured gets full credit — can't
     * penalize for an unspecified requirement.
     */
    private function scoreSkills(JobPosting $job, Alumnus $alumnus): float
    {
        $requiredSkills = $job->skills;
        if ($requiredSkills->isEmpty()) {
            return 45.0;
        }

        $alumnusSkillIds = $alumnus->skills->pluck('skill_id')->all();
        $matchedCount = $requiredSkills->filter(fn ($s) => in_array($s->skill_id, $alumnusSkillIds, true))->count();

        return round(($matchedCount / $requiredSkills->count()) * 45, 2);
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
