<?php

namespace App\Http\Controllers;

use App\Mail\ApplyJobMail;
use App\Mail\DeclineApplicantMail;
use App\Mail\HireApplicantMail;
use App\Mail\ShortlistApplicantMail;
use App\Models\Alumnus;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\UserNotification;
use App\Services\JobMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(JobApplication $jobApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobApplication $jobApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobApplication $jobApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobApplication $jobApplication)
    {
        //
    }

    public function applyJob($jobPostingId)
    {
        abort_unless(Auth::user()->user_role === 'alumni', 403);

        $job = JobPosting::with(['skills', 'programs'])->findOrFail($jobPostingId);
        $alumniId = Auth::id();
        $alumni = Alumnus::with(['skills', 'experiences', 'certifications'])->findOrFail($alumniId);

        // `alumnus_resume` was removed when the resume feature was rebuilt
        // (see alumnus_resume_summary/alumnus_resume_completeness) — gate on
        // having started a resume instead of requiring it to be finished,
        // since applying shouldn't wait on a 100%-complete resume.
        if (($alumni->alumnus_resume_completeness ?? 0) <= 0) {
            return redirect()->back()->with('noResume', 'flex');
        }

        // Check if the user has already applied for this job
        $existingApplication = JobApplication::where('alumnus_id', $alumniId)
            ->where('job_id', $jobPostingId)
            ->first();

        if ($existingApplication) {
            $existingApplication->delete();
            return redirect()->back();
        }

        $match = app(JobMatchService::class)->scoreFor($job, $alumni);

        // Create a new job application
        JobApplication::create([
            'alumnus_id' => $alumniId,
            'job_id' => $jobPostingId,
            'application_status' => 'pending',
            'application_score' => $match->score,
        ]);
        Mail::to($job->user->user_email)->queue(new ApplyJobMail($job, $alumni));

        return redirect()->route('jobPosting.jobBoard')->with('matchScore', $match->score);
    }

    public function showApplications($jobPostingId)
    {
        $jobPost = JobPosting::with(['applicants.user', 'industry'])->findOrFail($jobPostingId);
        abort_unless($jobPost->user_id === Auth::id(), 403);

        // Opening the list is what "reads" the applications — clear the
        // unread flag now so the count on the My Job Postings card resets.
        JobApplication::where('job_id', $jobPostingId)->update(['is_read' => true]);

        return view('general.jobApplicants', compact('jobPost'));
    }

    /**
     * Looks up the application and confirms the acting employer actually
     * owns the job it belongs to before handing back to the caller — used
     * by hire/decline/shortlist below so one employer can't act on another
     * employer's applicants by guessing an application id.
     */
    private function authorizedApplication($applicationId): JobApplication
    {
        $application = JobApplication::with('job')->findOrFail($applicationId);
        abort_unless($application->job->user_id === Auth::id(), 403);

        return $application;
    }

    /**
     * Shared by hireApplicant() (single) and bulkHireApplicants() so both
     * paths flip the same status + alumnus side effects the same way —
     * getting hired through the system sets these automatically, same as
     * before (see AlumnusController::updateAlumniProfile for the manual
     * path). First job date only gets set the first time ever — a later
     * hire (job change) shouldn't overwrite the actual first job date.
     *
     * Mail is queued, not sent inline — bulkHireApplicants() can call this
     * in a loop over a dozen-plus applicants, and a live SMTP round-trip
     * per applicant was enough to blow past PHP's execution time limit on
     * a single request. Requires a queue worker (`php artisan queue:work`)
     * to actually deliver — see QUEUE_CONNECTION in .env.
     */
    private function hireApplication(JobApplication $application): void
    {
        $application->application_status = 'hired';
        $application->save();

        $alumnus = $application->alumnus;
        $alumnus->alumnus_employment_status = true;
        if ($application->job->industry_id) {
            $alumnus->industry_id = $application->job->industry_id;
        }
        if ($application->job->job_posting_company) {
            $alumnus->alumnus_workplace = $application->job->job_posting_company;
            $alumnus->alumnus_workplace_undisclosed = false;
        }
        // Job position + employment date come from the posting itself and
        // are locked on the self-service profile form while this flag is
        // true (see AlumnusController::updateAlumniProfile()) — every new
        // in-system hire overwrites them with the new posting's details,
        // same as workplace/industry above.
        $alumnus->alumnus_job_position = $application->job->job_posting_title;
        $alumnus->alumnus_employment_date = now();
        $alumnus->alumnus_employed_via_platform = true;
        if (!$alumnus->alumnus_first_job_date) {
            $alumnus->alumnus_first_job_date = now();
        }
        $alumnus->save();

        Mail::to($alumnus->user->user_email)->queue(new HireApplicantMail($application));
        UserNotification::create([
            'user_id' => $alumnus->user_id,
            'type' => 'job_application_hired',
            'title' => 'Congratulations — you were hired!',
            'body' => "You've been hired for \"{$application->job->job_posting_title}\" at {$application->job->job_posting_company}.",
        ]);
    }

    public function hireApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);

        if ($application->job->remainingHiringSlots() <= 0) {
            return back()->with('error', 'Hiring limit reached for this job post — no more applicants can be hired.');
        }

        $this->hireApplication($application);

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $application->job_id])->with('success', 'Application status updated successfully.');
    }

    private function declineApplication(JobApplication $application): void
    {
        $application->application_status = 'declined';
        $application->save();
        Mail::to($application->alumnus->user->user_email)->queue(new DeclineApplicantMail($application));
        UserNotification::create([
            'user_id' => $application->alumnus_id,
            'type' => 'job_application_declined',
            'title' => 'Application update',
            'body' => "Your application for \"{$application->job->job_posting_title}\" at {$application->job->job_posting_company} was not selected this time.",
        ]);
    }

    public function declineApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $this->declineApplication($application);

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $application->job_id])->with('success', 'Application status updated successfully.');
    }

    private function shortlistApplication(JobApplication $application): void
    {
        $application->application_status = 'shortlisted';
        $application->save();
        Mail::to($application->alumnus->user->user_email)->queue(new ShortlistApplicantMail($application));
        UserNotification::create([
            'user_id' => $application->alumnus_id,
            'type' => 'job_application_shortlisted',
            'title' => "You've been shortlisted!",
            'body' => "You've been shortlisted for \"{$application->job->job_posting_title}\" at {$application->job->job_posting_company}.",
        ]);
    }

    public function shortlistApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $this->shortlistApplication($application);

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $application->job_id])->with('success', 'Application status updated successfully.');
    }

    /**
     * Shared lookup behind every bulk-* endpoint below — resolves the job
     * (with ownership check) and the subset of the requested application
     * ids that actually belong to it and aren't already in an excluded
     * status, so e.g. an already-hired or already-declined row silently
     * drops out of a batch instead of erroring the whole request.
     */
    private function resolveBulkTargets(Request $request, $jobPostingId, array $excludedStatuses): array
    {
        $jobPost = JobPosting::findOrFail($jobPostingId);
        abort_unless($jobPost->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'application_ids' => ['required', 'array', 'min:1'],
            'application_ids.*' => ['integer', 'exists:job_applications,application_id'],
        ]);

        $applications = JobApplication::with(['job', 'alumnus.user'])
            ->whereIn('application_id', $validated['application_ids'])
            ->where('job_id', $jobPost->job_posting_id)
            ->whereNotIn('application_status', $excludedStatuses)
            ->get();

        return [$jobPost, $applications];
    }

    /**
     * Bulk-tag applicants to hire in one go, capped at the job post's
     * hiring_limit — mirrors hireApplicant()'s single-applicant cap check
     * but validates the whole batch against remaining slots up front so a
     * partial hire never happens (all-or-nothing).
     */
    public function bulkHireApplicants(Request $request, $jobPostingId)
    {
        [$jobPost, $applications] = $this->resolveBulkTargets($request, $jobPostingId, ['hired', 'declined']);

        if ($applications->isEmpty()) {
            return back()->with('error', 'None of the selected applicants can be hired.');
        }

        $remainingSlots = $jobPost->remainingHiringSlots();
        if ($applications->count() > $remainingSlots) {
            return back()->with('error', "You can only hire {$remainingSlots} more applicant(s) for this job post.");
        }

        foreach ($applications as $application) {
            $this->hireApplication($application);
        }

        return redirect()->route('jobApplication.showApplications', ['jobPostingId' => $jobPost->job_posting_id])
            ->with('success', $applications->count() . ' applicant(s) hired successfully.');
    }

    /** Bulk-tag applicants to decline — no cap, any non-hired/non-declined row is fair game. */
    public function bulkDeclineApplicants(Request $request, $jobPostingId)
    {
        [$jobPost, $applications] = $this->resolveBulkTargets($request, $jobPostingId, ['hired', 'declined']);

        if ($applications->isEmpty()) {
            return back()->with('error', 'None of the selected applicants can be declined.');
        }

        foreach ($applications as $application) {
            $this->declineApplication($application);
        }

        return redirect()->route('jobApplication.showApplications', ['jobPostingId' => $jobPost->job_posting_id])
            ->with('success', $applications->count() . ' applicant(s) declined.');
    }

    /** Bulk-tag applicants to shortlist — no cap, skips anything already hired/declined/shortlisted. */
    public function bulkShortlistApplicants(Request $request, $jobPostingId)
    {
        [$jobPost, $applications] = $this->resolveBulkTargets($request, $jobPostingId, ['hired', 'declined', 'shortlisted']);

        if ($applications->isEmpty()) {
            return back()->with('error', 'None of the selected applicants can be shortlisted.');
        }

        foreach ($applications as $application) {
            $this->shortlistApplication($application);
        }

        return redirect()->route('jobApplication.showApplications', ['jobPostingId' => $jobPost->job_posting_id])
            ->with('success', $applications->count() . ' applicant(s) shortlisted.');
    }
}
