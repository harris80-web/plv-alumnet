<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\EmployerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerReviewController extends Controller
{
    /**
     * AJAX vote/rating endpoint — shared by three different UI actions on a
     * job card: the quick up/down click (sends just `vote`), the 5-star
     * picker (sends just `rating`, which then opens the review modal), and
     * the review modal's save button (sends whichever of `vote`/`rating`
     * is currently set, plus `review_body`). All three share one row per
     * (employer, alumnus) pair (see EmployerReview's class doc) so a vote
     * and a rating from the same alumnus on the same company never
     * duplicate or overwrite each other.
     *
     * `review_body`'s mere presence in the request (not its value) is what
     * tells a modal submission apart from a bare vote/rating click — see
     * $isReviewSubmission below, same convention as before this method
     * grew a second independent field.
     */
    public function vote(Request $request, Employer $employer)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni' && $user->alumnus, 403);

        $validated = $request->validate([
            'vote' => ['nullable', 'in:' . implode(',', EmployerReview::VOTES)],
            'rating' => ['nullable', 'integer', 'min:' . EmployerReview::MIN_RATING, 'max:' . EmployerReview::MAX_RATING],
            'review_body' => ['nullable', 'string', 'max:1000'],
        ]);

        $hasVote = array_key_exists('vote', $validated) && $validated['vote'] !== null;
        $hasRating = array_key_exists('rating', $validated) && $validated['rating'] !== null;
        abort_unless($hasVote || $hasRating, 422, 'Provide a vote or a rating.');

        $isReviewSubmission = $request->has('review_body');

        $existing = EmployerReview::where('employer_id', $employer->user_id)
            ->where('alumnus_id', $user->user_id)
            ->first();

        // Bare vote click (no rating, no review text) on a vote you already
        // have toggles just the vote off — clearing only that field, not
        // the whole row, since an independently-set rating/review on the
        // same row must survive an un-vote. Only delete the row outright
        // if nothing else is left on it afterward.
        if ($hasVote && !$hasRating && !$isReviewSubmission && $existing && $existing->vote === $validated['vote']) {
            if ($existing->rating === null && $existing->review_body === null) {
                $existing->delete();
            } else {
                $existing->update(['vote' => null]);
            }
            $review = $existing->rating === null && $existing->review_body === null ? null : $existing->fresh();
        } else {
            $attributes = [];
            if ($hasVote) {
                $attributes['vote'] = $validated['vote'];
            }
            if ($hasRating) {
                $attributes['rating'] = $validated['rating'];
            }
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
            'myVote' => $review->vote ?? null,
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
