<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\EmployerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerReviewController extends Controller
{
    /**
     * AJAX vote endpoint — used both by the quick up/down click on a job
     * card (sends just `vote`) and by the review modal's save button (sends
     * `vote` + `review_body` together). The two are told apart by whether
     * `review_body` is present at all in the request: a bare vote click on
     * a vote you already have toggles it off (un-votes), but a modal
     * submission carrying the same vote should update the review text, not
     * delete anything — see the `$isReviewSubmission` check below.
     */
    public function vote(Request $request, Employer $employer)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni' && $user->alumnus, 403);

        $validated = $request->validate([
            'vote' => ['required', 'in:' . implode(',', EmployerReview::VOTES)],
            'review_body' => ['nullable', 'string', 'max:1000'],
        ]);

        $isReviewSubmission = $request->has('review_body');

        $existing = EmployerReview::where('employer_id', $employer->user_id)
            ->where('alumnus_id', $user->user_id)
            ->first();

        if (!$isReviewSubmission && $existing && $existing->vote === $validated['vote']) {
            $existing->delete();
            $myVote = null;
            $reviewBody = null;
        } else {
            $attributes = ['vote' => $validated['vote']];
            if ($isReviewSubmission) {
                $attributes['review_body'] = $validated['review_body'] ?: null;
            }

            $review = EmployerReview::updateOrCreate(
                ['employer_id' => $employer->user_id, 'alumnus_id' => $user->user_id],
                $attributes
            );
            $myVote = $review->vote;
            $reviewBody = $review->review_body;
        }

        return response()->json([
            'upvotes' => $employer->upvoteCount(),
            'downvotes' => $employer->downvoteCount(),
            'myVote' => $myVote,
            'reviewBody' => $reviewBody,
        ]);
    }

    /**
     * Full reviews list for one company — real pagination (not "load
     * everything then filter in JS") so this stays fast no matter how many
     * reviews a popular employer accumulates. Public: the "Reviews" button
     * on a job card is visible to every role (see partials/job-post-card.blade.php),
     * only the vote buttons themselves are alumni-only.
     */
    public function reviews(Request $request, Employer $employer)
    {
        $filter = $request->query('vote');

        $query = EmployerReview::with(['alumnus.user', 'alumnus.program'])
            ->where('employer_id', $employer->user_id)
            ->latest();

        if (in_array($filter, EmployerReview::VOTES, true)) {
            $query->where('vote', $filter);
        }

        $reviews = $query->paginate(10)->withQueryString();

        $upvotes = $employer->upvoteCount();
        $downvotes = $employer->downvoteCount();
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

        return view('general.companyReviews', compact('employer', 'reviews', 'filter', 'upvotes', 'downvotes', 'user', 'backUrl'));
    }
}
