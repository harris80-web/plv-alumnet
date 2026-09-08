<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\EmployerReview;
use App\Models\JobPostingVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerReviewController extends Controller
{
    /**
     * AJAX vote/rating endpoint — shared by three different UI actions on a
     * job card: the quick up/down click (sends `vote` + `job_posting_id`),
     * the 5-star picker (sends just `rating`, which then opens the review
     * modal), and the review modal's save button (sends `rating` plus
     * `review_body`). A vote is per JOB POSTING (JobPostingVote, unique on
     * job_posting_id+alumnus_id) and open to any alumnus regardless of hire
     * status. A rating is per COMPANY (EmployerReview, unique on
     * employer_id+alumnus_id) and stays restricted to alumni this specific
     * employer actually hired — see wasHiredByEmployer() below.
     *
     * `review_body`'s mere presence in the request (not its value) is what
     * tells a modal submission apart from a bare rating click — see
     * $isReviewSubmission below.
     */
    public function vote(Request $request, Employer $employer)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni' && $user->alumnus, 403);

        $validated = $request->validate([
            'vote' => ['nullable', 'in:' . implode(',', JobPostingVote::VOTES)],
            'job_posting_id' => ['required_with:vote', 'nullable', 'integer', 'exists:job_postings,job_posting_id'],
            'rating' => ['nullable', 'integer', 'min:' . EmployerReview::MIN_RATING, 'max:' . EmployerReview::MAX_RATING],
            'review_body' => ['nullable', 'string', 'max:1000'],
        ]);

        $hasVote = array_key_exists('vote', $validated) && $validated['vote'] !== null;
        $hasRating = array_key_exists('rating', $validated) && $validated['rating'] !== null;
        abort_unless($hasVote || $hasRating, 422, 'Provide a vote or a rating.');

        $myVote = null;

        if ($hasVote) {
            $jobPosting = $employer->jobPostings()->where('job_posting_id', $validated['job_posting_id'])->firstOrFail();

            // Bare vote click on a vote you already have toggles it off.
            $existingVote = JobPostingVote::where('job_posting_id', $jobPosting->job_posting_id)
                ->where('alumnus_id', $user->user_id)
                ->first();

            if ($existingVote && $existingVote->vote === $validated['vote']) {
                $existingVote->delete();
                $myVote = null;
            } else {
                $myVote = JobPostingVote::updateOrCreate(
                    ['job_posting_id' => $jobPosting->job_posting_id, 'alumnus_id' => $user->user_id],
                    ['vote' => $validated['vote']]
                )->vote;
            }
        }

        $review = EmployerReview::where('employer_id', $employer->user_id)
            ->where('alumnus_id', $user->user_id)
            ->first();

        if ($hasRating) {
            // Client-side the star picker is simply disabled for an alumnus
            // this company never hired (see JobCardDataBuilder's
            // can-rate-company and job-post-card/job-detail-modal's
            // rendering of it) — this is the actual enforcement, since a
            // disabled button alone doesn't stop a direct request here.
            abort_unless($user->alumnus->wasHiredByEmployer($employer->user_id), 403, 'Only alumni this company has hired can rate it.');

            $isReviewSubmission = $request->has('review_body');
            $attributes = ['rating' => $validated['rating']];
            if ($isReviewSubmission) {
                $attributes['review_body'] = $validated['review_body'] ?: null;
            }

            $review = EmployerReview::updateOrCreate(
                ['employer_id' => $employer->user_id, 'alumnus_id' => $user->user_id],
                $attributes
            );
        }

        return response()->json([
            'upvotes' => $employer->upvoteCount(),
            'downvotes' => $employer->downvoteCount(),
            'myVote' => $myVote,
            'myRating' => $review->rating ?? null,
            'reviewBody' => $review->review_body ?? null,
            'averageRating' => $employer->averageRating(),
            'ratingCount' => $employer->ratingCount(),
        ]);
    }

    /**
     * Full reviews list for one company — real pagination (not "load
     * everything then filter in JS") so this stays fast no matter how many
     * reviews a popular employer accumulates. Public: the "Reviews" button
     * on a job card is visible to every role (see partials/job-post-card.blade.php),
     * only the vote/rating buttons themselves are alumni-only.
     *
     * Rating-driven, not vote-driven: a star rating is the deliberate
     * "leave a review" action now (see castCompanyRating() in
     * company-review-modal.blade.php) — a bare up/downvote isn't something
     * this page lists at all, so every row here has a rating, and the
     * filter is by star count (1-5) instead of vote type.
     */
    public function reviews(Request $request, Employer $employer)
    {
        $filter = (int) $request->query('rating');
        $filter = in_array($filter, range(EmployerReview::MIN_RATING, EmployerReview::MAX_RATING), true) ? $filter : null;

        $query = EmployerReview::with(['alumnus.user', 'alumnus.program'])
            ->where('employer_id', $employer->user_id)
            ->whereNotNull('rating')
            ->latest();

        if ($filter) {
            $query->where('rating', $filter);
        }

        $reviews = $query->paginate(10)->withQueryString();

        $averageRating = $employer->averageRating();
        $ratingCount = $employer->ratingCount();
        // Per-star counts for the filter tabs, keyed by rating (1-5).
        $ratingBreakdown = EmployerReview::where('employer_id', $employer->user_id)
            ->whereNotNull('rating')
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $user = Auth::user();

        // Captured once from the job card that linked here (see
        // partials/job-post-card.blade.php) and carried through the filter
        // links below, so "Back" always returns to that original page
        // instead of bouncing between this page's own filtered/unfiltered
        // views — url()->previous() isn't usable here since every filter
        // click is itself a full navigation that overwrites it. Only trust
        // it when it actually points back into this app.
        $backUrl = $request->query('back');
        if (!$backUrl || !str_starts_with($backUrl, url('/'))) {
            $backUrl = route('jobPosting.jobBoard');
        }

        return view('general.companyReviews', compact('employer', 'reviews', 'filter', 'averageRating', 'ratingCount', 'ratingBreakdown', 'user', 'backUrl'));
    }
}
