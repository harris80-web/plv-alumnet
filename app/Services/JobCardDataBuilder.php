<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * One source of truth for the data-* payload partials/job-detail-modal.blade.php's
 * openJobModal() reads, used by BOTH partials/job-post-card.blade.php (Job
 * Board, My Applications, My Job Posts) AND the alumni dashboard's "Job
 * Matches For You" cards. Extracted out so both places can never drift
 * apart again — Job Matches used to hand-roll its own much smaller data-*
 * list, which meant its "View Details" opened the same modal but with most
 * fields undefined (no reviews/rating, no working Apply, no bookmark, no
 * posted-by).
 *
 * A plain PHP class (not a Blade partial) on purpose: a Blade @include()
 * gets its own isolated variable scope — anything it assigns via @php
 * doesn't propagate back to the including view — so a partial couldn't
 * actually hand $cardData back to job-post-card.blade.php. Call build()
 * from a @php block and extract() the result into the view's own scope
 * instead.
 */
class JobCardDataBuilder
{
    /**
     * @param  Collection|null  $appliedJobs  keyed by job_posting_id, alumni only
     * @param  array  $bookmarkedIds
     */
    public static function build(
        JobPosting $job,
        ?User $user,
        ?Collection $appliedJobs = null,
        array $bookmarkedIds = [],
        float $recommendedThreshold = 50
    ): array {
        $isAlumni = $user && $user->user_role === 'alumni';
        $hasApplied = $isAlumni && $appliedJobs && $appliedJobs->has($job->job_posting_id);
        $isBookmarked = $isAlumni && in_array($job->job_posting_id, $bookmarkedIds, true);
        // match_score comes from JobPostingController::filteredJobPostingsQuery()'s
        // correlated subquery (alumni only) — same blended score / threshold the
        // board sorts by, so this badge always agrees with the ordering.
        $isRecommended = $isAlumni && isset($job->match_score) && $job->match_score !== null
            && (float) $job->match_score >= $recommendedThreshold;

        // Company up/down votes — see App\Models\JobPostingVote. Deliberately
        // NOT shown to admin/employer roles (only the alumni-facing vote
        // buttons are gated; the "Reviews" link below stays visible to
        // everyone).
        $employer = $job->employer;
        $companyUpvotes = $employer?->upvoteCount() ?? 0;
        $companyDownvotes = $employer?->downvoteCount() ?? 0;
        // The "Reviews (N)" link/page is rating-driven (see
        // company-review-modal.blade.php's castCompanyRating()) — a bare vote
        // isn't a listed "review", so this counts actual star ratings, not votes.
        $companyRatingCount = $employer?->ratingCount() ?? 0;
        $companyAverageRating = $employer?->averageRating();
        // A vote is per THIS job posting (any alumnus, any hire status — see
        // EmployerReviewController::vote()), while a rating/review is per
        // company (App\Models\EmployerReview), so they come from two
        // different rows now. Filtered out of the already-loaded $employer->votes
        // collection (eager-loaded alongside employer.reviews by callers —
        // see JobPostingController/AlumniDashboardController) rather than a
        // fresh query per card.
        $myCompanyVote = $isAlumni && $employer
            ? $employer->votes->first(fn ($v) => (int) $v->job_posting_id === (int) $job->job_posting_id && (int) $v->alumnus_id === (int) $user->user_id)
            : null;
        $myCompanyRating = $isAlumni && $employer ? $employer->reviews->firstWhere('alumnus_id', $user->user_id) : null;
        $isEmployer = $user && $user->user_role === 'employer';

        // Rating a company is restricted to alumni this company actually
        // hired (see Alumnus::wasHiredByEmployer() and
        // EmployerReviewController::vote()) — everyone else still sees the
        // star button (so they know the feature exists and why it's off)
        // but disabled. Up/downvoting has no such restriction — any alumnus
        // can vote on any job posting.
        $canRateCompany = $isAlumni && $employer && $user->alumnus->wasHiredByEmployer($employer->user_id);

        $cardData = [
            'job-id' => $job->job_posting_id,
            'title' => $job->job_posting_title,
            'company' => $job->job_posting_company,
            'address' => $job->job_posting_address,
            'date' => $job->created_at->diffForHumans(),
            'description' => $job->job_posting_description,
            'type' => $job->job_posting_employment_type,
            'setup' => $job->job_posting_setup,
            'valid' => $job->job_closing_date,
            'image' => $job->thumbnailUrl(),
            'uses-default-image' => $job->usesDefaultThumbnail() ? '1' : '0',
            'image-overlay-color' => $job->defaultThumbnailOverlay()['color'],
            'image-overlay-opacity' => $job->defaultThumbnailOverlay()['overlayOpacity'],
            'image-opacity' => $job->defaultThumbnailOverlay()['imageOpacity'],
            'programs' => $job->programs->pluck('program_name')->implode(', '),
            'industry' => $job->industry->industry_name ?? 'Not specified',
            'skills' => $job->skills->pluck('skill_name')->implode('||'),
            'recommended' => $isRecommended ? '1' : '0',
            'employer-id' => $employer->user_id ?? '',
            'employer-contact' => $employer?->user?->user_number ?? '',
            'reviews-visible' => ($employer && !$isEmployer) ? '1' : '0',
            'upvotes' => $companyUpvotes,
            'downvotes' => $companyDownvotes,
            'rating-count' => $companyRatingCount,
            'average-rating' => $companyAverageRating ?? '',
            'my-vote' => $myCompanyVote->vote ?? '',
            'my-rating' => $myCompanyRating->rating ?? '',
            'my-review-body' => $myCompanyRating->review_body ?? '',
            'vote-visible' => ($employer && $isAlumni) ? '1' : '0',
            'can-rate-company' => $canRateCompany ? '1' : '0',
            'is-alumni' => $isAlumni ? '1' : '0',
            'is-guest' => (!$user) ? '1' : '0',
            'is-bookmarked' => $isBookmarked ? '1' : '0',
            'has-applied' => $hasApplied ? '1' : '0',
            'has-uploaded-resume-file' => $isAlumni && $user->alumnus->hasUploadedResumeFile() ? '1' : '0',
            'has-builder-resume' => $isAlumni && $user->alumnus->hasBuilderResume() ? '1' : '0',
            'has-profile-cover-letter' => $isAlumni && !empty($user->alumnus->alumnus_cover_letter_file_path) ? '1' : '0',
            'application-status' => $hasApplied ? $appliedJobs[$job->job_posting_id]->pivot->application_status : '',
            'posted-by' => $job->user->user_first_name . ' ' . $job->user->user_last_name,
            'posted-by-avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($job->user->user_first_name . ' ' . $job->user->user_last_name) . '&background=random',
        ];

        return compact(
            'isAlumni', 'hasApplied', 'isBookmarked', 'isRecommended',
            'employer', 'companyUpvotes', 'companyDownvotes', 'companyRatingCount',
            'companyAverageRating', 'myCompanyVote', 'myCompanyRating', 'isEmployer', 'canRateCompany', 'cardData'
        );
    }
}
