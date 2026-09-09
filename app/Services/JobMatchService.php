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
 * score_breakdown docblock example, rounded to a clean 100. On top of that
 * 100, the employer's alumni-submitted reputation (upvotes/downvotes AND
 * star ratings — see App\Models\EmployerReview) can each nudge the final
 * score by up to ±5 points (±10 combined), never enough to substitute for
 * actual fit.
 */
class JobMatchService
{
    /** Net-vote ratio needs at least this many total votes before it affects ranking — one or two votes shouldn't swing anything. */
    private const MIN_VOTES_FOR_REPUTATION = 3;

    /** Average star rating needs at least this many ratings before it affects ranking — same reasoning as MIN_VOTES_FOR_REPUTATION. */
    private const MIN_RATINGS_FOR_REPUTATION = 3;

    /** Max points the vote net-ratio can add or subtract — small on purpose, see scoreCompanyVoteReputation(). */
    private const VOTE_REPUTATION_MAX_POINTS = 5;

    /** Max points the average star rating can add or subtract — independent of, and on top of, the vote-based nudge above. */
    private const RATING_REPUTATION_MAX_POINTS = 5;

    public function scoreFor(JobPosting $job, Alumnus $alumnus): JobMatch
    {
        $breakdown = [
            'skills' => $this->scoreSkills($job, $alumnus),
            'experience' => $this->scoreExperience($alumnus),
            'program' => $this->scoreProgram($job, $alumnus),
            'certifications' => $this->scoreCertifications($alumnus),
            'company_vote_reputation' => $this->scoreCompanyVoteReputation($job),
            'company_rating_reputation' => $this->scoreCompanyRatingReputation($job),
        ];

        // The 4 fit components already sum to 100 on their own — reputation
        // (votes + star rating, ±5 each, ±10 combined) is a small nudge on
        // top, not a 5th/6th criterion, and is clamped so it can never push
        // a genuinely poor fit above a genuinely good one (a job already at
        // 100 on fit alone gets no further benefit from a good reputation;
        // it can only help a job that isn't already maxed out — see
        // App\Models\EmployerReview).
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
    private function scoreCompanyVoteReputation(JobPosting $job): float
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

        return round($netRatio * self::VOTE_REPUTATION_MAX_POINTS, 2);
    }

    /**
     * Same idea as scoreCompanyVoteReputation(), but from the employer's
     * average 1-5 star rating (see App\Models\EmployerReview::rating,
     * independent of and additional to the plain up/downvote) instead of
     * the vote net-ratio. 3 stars is treated as neutral (0 point swing) —
     * below 3 nudges the score down, above 3 nudges it up, scaled linearly
     * so a perfect 5-star average earns the full +5 and a rock-bottom
     * 1-star average costs the full -5. Neutral (0) until a company has at
     * least MIN_RATINGS_FOR_REPUTATION ratings, so a single early review
     * can't swing anything.
     */
    private function scoreCompanyRatingReputation(JobPosting $job): float
    {
        $employer = $job->employer;
        if (!$employer) {
            return 0.0;
        }

        if ($employer->ratingCount() < self::MIN_RATINGS_FOR_REPUTATION) {
            return 0.0;
        }

        $averageRating = $employer->averageRating();
        if ($averageRating === null) {
            return 0.0;
        }

        $normalized = ($averageRating - 3) / 2; // -1 (1 star) .. 0 (3 star) .. 1 (5 star)

        return round($normalized * self::RATING_REPUTATION_MAX_POINTS, 2);
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
