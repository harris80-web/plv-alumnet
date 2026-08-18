<?php

namespace App\Http\Controllers;

use App\Models\JobMatch;
use App\Models\Testimonial;
use App\Services\JobMatchService;
use Illuminate\Support\Facades\Auth;

class AlumniDashboardController extends Controller
{
    /**
     * "Job Matches For You" is normally populated from job_matches rows kept
     * fresh in the background (RecomputeJobMatches, scheduled hourly/daily —
     * see routes/console.php) plus an immediate refresh on resume save
     * (ResumeBuilderController::save()). But the schedule only actually
     * fires if something is running `php artisan schedule:run` on a cron,
     * which isn't guaranteed (e.g. local dev). Without that, a brand new
     * alumnus who hasn't applied to anything yet (the only other path that
     * creates a job_matches row — see JobApplicationController::applyJob())
     * would see nothing here at all. So: self-heal on page load whenever
     * this alumnus's matches are missing or stale, using the same cheap
     * deterministic-only refresh as the resume-save hook (no Gemini call,
     * safe to run inline). This makes the section work correctly regardless
     * of whether the cron is set up; the schedule/AI pass just keeps things
     * fresher when it is.
     */
    public function index(JobMatchService $matchService)
    {
        $testimonials = Testimonial::with(['alumnus.user', 'alumnus.program'])
            ->where('testimonial_post', true)
            ->latest()
            ->paginate(4)
            ->withQueryString()
            // Lands the reload back on the testimonials section instead of the top of the dashboard.
            ->fragment('alumni-testimonials');

        $alumnus = Auth::user()->alumnus ?? null;
        $jobMatches = collect();
        $appliedJobs = collect();

        if ($alumnus) {
            $hasFreshMatches = JobMatch::where('alumnus_id', $alumnus->user_id)
                ->where('computed_at', '>=', now()->subDay())
                ->exists();

            if (!$hasFreshMatches) {
                $matchService->refreshForAlumnus($alumnus);
            }

            $jobMatches = JobMatch::with(['jobPosting.programs', 'jobPosting.industry'])
                ->where('alumnus_id', $alumnus->user_id)
                ->whereHas('jobPosting', fn ($q) => $q->approved()->open())
                ->get()
                ->sortByDesc(fn (JobMatch $match) => $match->blendedScore())
                ->take(3)
                ->values();

            // Same shape job-post-card.blade.php already expects
            // ($appliedJobs->has($id) / ->pivot->application_status) — the
            // dashboard card reuses that Apply/Applied logic verbatim.
            $appliedJobs = $alumnus->appliedJobs->keyBy('job_posting_id');
        }

        return view('alumni.dashboard', compact('testimonials', 'jobMatches', 'appliedJobs'));
    }
}
