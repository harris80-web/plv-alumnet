<?php

namespace App\Http\Controllers;

use App\Models\JobBookmark;
use App\Models\JobPosting;
use Illuminate\Support\Facades\Auth;

class JobBookmarkController extends Controller
{
    /**
     * Flips the bookmark for the current alumnus on this job posting —
     * one POST handles both save and remove, mirroring how
     * JobApplicationController::applyJob() toggles an application on the
     * same click-to-apply button.
     */
    public function toggle($jobPostingId)
    {
        abort_unless(Auth::user()->user_role === 'alumni', 403);

        $job = JobPosting::findOrFail($jobPostingId);
        $alumniId = Auth::id();

        $existing = JobBookmark::where('alumnus_id', $alumniId)
            ->where('job_id', $job->job_posting_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Bookmark removed.';
        } else {
            JobBookmark::create([
                'alumnus_id' => $alumniId,
                'job_id' => $job->job_posting_id,
            ]);
            $message = 'Job bookmarked successfully!';
        }

        return redirect()->back()->with('success', $message);
    }
}
