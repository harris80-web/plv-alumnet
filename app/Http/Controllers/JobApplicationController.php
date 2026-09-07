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

    /**
     * Applying now goes through partials/job-apply-modal.blade.php's 2-step
     * review flow instead of a bare one-click form: the alumnus explicitly
     * picks a resume — their uploaded profile file, a one-off upload just
     * for this job, or their Resume Builder profile (optionally edited in
     * the review step as a one-time scratch copy, see
     * $validated['builder_resume_snapshot'] below) — and optionally a cover
     * letter the same way.
     */
    public function applyJob(Request $request, $jobPostingId)
    {
        abort_unless(Auth::user()->user_role === 'alumni', 403);

        $job = JobPosting::with(['skills', 'programs'])->findOrFail($jobPostingId);
        $alumniId = Auth::id();
        $alumni = Alumnus::with(['skills', 'experiences', 'certifications'])->findOrFail($alumniId);

        // The review step's builder-resume editor posts one JSON blob
        // (deeply-nested indexed form fields for a variable-length
        // skills/experiences/certifications list would be painful to
        // generate client-side) — decode it into the request before
        // validating, so the rest of this method can treat it as a normal
        // nested array like everything else here.
        if ($request->filled('builder_resume_snapshot_json')) {
            $request->merge([
                'builder_resume_snapshot' => json_decode($request->input('builder_resume_snapshot_json'), true) ?? [],
            ]);
        }

        $validated = $request->validate([
            'resume_source' => ['required', 'in:profile,upload,builder'],
            'resume_file' => ['required_if:resume_source,upload', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter_source' => ['nullable', 'in:none,profile,upload'],
            'cover_letter_file' => ['required_if:cover_letter_source,upload', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            // Scratch copy edited in the review step, never written back to
            // alumni/experiences/skills — same field shape as
            // Alumnus::toResumeFormArray()/ResumeBuilderController::save().
            'builder_resume_snapshot' => ['required_if:resume_source,builder', 'nullable', 'array'],
            'builder_resume_snapshot.summary' => ['nullable', 'string', 'max:2000'],
            'builder_resume_snapshot.skills' => ['nullable', 'array'],
            'builder_resume_snapshot.skills.*.name' => ['required', 'string', 'max:100'],
            'builder_resume_snapshot.experiences' => ['nullable', 'array'],
            'builder_resume_snapshot.experiences.*.type' => ['required', 'in:work,project'],
            'builder_resume_snapshot.experiences.*.job_title' => ['required', 'string', 'max:255'],
            'builder_resume_snapshot.experiences.*.job_description' => ['nullable', 'string', 'max:2000'],
            'builder_resume_snapshot.experiences.*.duration_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'builder_resume_snapshot.certifications' => ['nullable', 'array'],
            'builder_resume_snapshot.certifications.*.certification_type' => ['required', 'in:certification,seminar,training'],
            'builder_resume_snapshot.certifications.*.certification_name' => ['required', 'string', 'max:255'],
            'builder_resume_snapshot.certifications.*.certification_from' => ['nullable', 'string', 'max:255'],
            'builder_resume_snapshot.certifications.*.certification_date' => ['nullable', 'date'],
        ]);

        // The apply modal now submits this via fetch() with an
        // Accept: application/json header (see job-apply-modal.blade.php's
        // handleApplySubmit()) so it can show a success/failure modal that
        // actually reflects what happened, instead of showing "Successfully
        // Applied!" optimistically before the real submit even ran. A
        // non-AJAX form post (JS disabled, or a direct hit) still gets the
        // original redirect-based behavior.
        $wantsJson = $request->wantsJson();

        // Belt-and-suspenders — the apply modal only ever offers each
        // resume option when the corresponding hasXResume() check is true,
        // but the server can't trust that a request actually came from it.
        if ($validated['resume_source'] === 'profile' && ! $alumni->hasUploadedResumeFile()) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => "You don't have a resume on file yet."], 422);
            }
            return redirect()->back()->with('noResume', 'flex');
        }
        if ($validated['resume_source'] === 'builder' && ! $alumni->hasBuilderResume()) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => "You don't have a Resume Builder profile yet."], 422);
            }
            return redirect()->back()->with('noResume', 'flex');
        }

        // Idempotent, not a toggle — every real "Apply" button on the job
        // board/dashboard becomes a disabled "APPLIED" badge the moment
        // this succeeds, with no unapply affordance anywhere in the UI, so
        // this used to only ever run a second time via a double-submit
        // (slow network retry, browser back+resubmit, a stale cached
        // page). It silently withdrew the alumnus's application with zero
        // confirmation. A repeat request now just confirms the existing
        // application instead of deleting it.
        $existingApplication = JobApplication::where('alumnus_id', $alumniId)
            ->where('job_id', $jobPostingId)
            ->first();

        if ($existingApplication) {
            if ($wantsJson) {
                return response()->json(['success' => true, 'alreadyApplied' => true, 'message' => 'You have already applied to this job.']);
            }
            return redirect()->route('jobPosting.jobBoard')->with('success', 'You have already applied to this job.');
        }

        $resumePath = null;
        if ($validated['resume_source'] === 'upload' && $request->hasFile('resume_file')) {
            $resumePath = $request->file('resume_file')->store('jobApplications/resumes', 'public');
        }

        $coverLetterSource = $validated['cover_letter_source'] ?? 'none';
        $coverLetterPath = null;
        if ($coverLetterSource === 'upload' && $request->hasFile('cover_letter_file')) {
            $coverLetterPath = $request->file('cover_letter_file')->store('jobApplications/coverLetters', 'public');
        }

        $match = app(JobMatchService::class)->scoreFor($job, $alumni);

        // Create a new job application
        JobApplication::create([
            'alumnus_id' => $alumniId,
            'job_id' => $jobPostingId,
            'application_status' => 'pending',
            'application_score' => $match->score,
            'resume_source' => $validated['resume_source'],
            'resume_path' => $resumePath,
            'cover_letter_source' => $coverLetterSource,
            'cover_letter_path' => $coverLetterPath,
            'builder_resume_snapshot' => $validated['resume_source'] === 'builder'
                ? ($validated['builder_resume_snapshot'] ?? null)
                : null,
        ]);
        Mail::to($job->user->user_email)->queue(new ApplyJobMail($job, $alumni));

        if ($wantsJson) {
            return response()->json(['success' => true, 'matchScore' => $match->score]);
        }
        return redirect()->route('jobPosting.jobBoard')->with('matchScore', $match->score);
    }

    /**
     * Staff (admin/super_admin) get sent back to Job Posting Management's
     * own Applicants tab after an action, not the employer-styled page —
     * they're always acting on a job they themselves posted (see
     * JobPostingController::addJobPost(), which attributes the posting to
     * Auth::id() regardless of role), so this is purely a "which chrome do
     * they belong in" choice, not an authorization one.
     */
    private function isStaff(): bool
    {
        return Auth::check() && in_array(Auth::user()->user_role, ['admin', 'super_admin'], true);
    }

    /**
     * Where a hire/decline/shortlist action sends the actor back to. An
     * employer lands on their own styled applicants page as before; staff
     * acting from Job Posting Management's Applicants tab instead go back
     * to that tab (?tab=applicants&applicantsJob=) so the action doesn't
     * drop them onto a page without their admin sidebar/header.
     */
    private function applicantsRedirectTarget($jobId)
    {
        if ($this->isStaff()) {
            return redirect()->route('jobPosting.jobManagement', ['tab' => 'applicants', 'applicantsJob' => $jobId]);
        }

        return redirect()->route('jobApplication.showApplications', ['jobPostingId' => $jobId]);
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
     * Looks up the application and confirms the acting user actually owns
     * the job it belongs to (staff included — an admin's own job posts are
     * owned by their own user id the same as an employer's) before handing
     * back to the caller — used by hire/decline/shortlist below so nobody
     * can act on someone else's applicant by guessing an application id.
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
        $application->hired_at = now();
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
            // A hire through an in-system job posting is a regular job, not
            // an internship — set explicitly (not left null) so this counts
            // as "answered: no" the same way a self-reported first job does.
            $alumnus->alumnus_first_job_is_internship = false;
        }
        $alumnus->save();

        Mail::to($alumnus->user->user_email)->queue(new HireApplicantMail($application));
        UserNotification::create([
            'user_id' => $alumnus->user_id,
            'type' => 'job_application_hired',
            'reference_id' => $application->job_id,
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

        return $this->statusUpdateRedirect($application, 'hired');
    }

    private function declineApplication(JobApplication $application): void
    {
        $application->application_status = 'declined';
        $application->save();
        Mail::to($application->alumnus->user->user_email)->queue(new DeclineApplicantMail($application));
        UserNotification::create([
            'user_id' => $application->alumnus_id,
            'type' => 'job_application_declined',
            'reference_id' => $application->job_id,
            'title' => 'Application update',
            'body' => "Your application for \"{$application->job->job_posting_title}\" at {$application->job->job_posting_company} was not selected this time.",
        ]);
    }

    public function declineApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $this->declineApplication($application);

        return $this->statusUpdateRedirect($application, 'declined');
    }

    private function shortlistApplication(JobApplication $application): void
    {
        $application->application_status = 'shortlisted';
        $application->save();
        Mail::to($application->alumnus->user->user_email)->queue(new ShortlistApplicantMail($application));
        UserNotification::create([
            'user_id' => $application->alumnus_id,
            'type' => 'job_application_shortlisted',
            'reference_id' => $application->job_id,
            'title' => "You've been shortlisted!",
            'body' => "You've been shortlisted for \"{$application->job->job_posting_title}\" at {$application->job->job_posting_company}.",
        ]);
    }

    public function shortlistApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $this->shortlistApplication($application);

        return $this->statusUpdateRedirect($application, 'shortlisted');
    }

    /**
     * Shared by the 3 single-applicant status actions above — names exactly
     * who changed and to what (not just a generic "updated successfully"),
     * and flashes the affected row's id so the view can highlight/scroll to
     * it after the redirect. The employer shouldn't have to hunt through a
     * long applicant list to confirm a status change actually took.
     */
    private function statusUpdateRedirect(JobApplication $application, string $status)
    {
        $name = trim($application->alumnus->user->user_first_name . ' ' . $application->alumnus->user->user_last_name);

        return $this->applicantsRedirectTarget($application->job_id)
            ->with('success', "{$name}'s application has been marked as " . ucfirst($status) . '.')
            ->with('updatedApplicationIds', [$application->application_id]);
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

        return $this->applicantsRedirectTarget($jobPost->job_posting_id)
            ->with('success', $applications->count() . ' applicant(s) hired successfully.')
            ->with('updatedApplicationIds', $applications->pluck('application_id')->values()->all());
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

        return $this->applicantsRedirectTarget($jobPost->job_posting_id)
            ->with('success', $applications->count() . ' applicant(s) declined.')
            ->with('updatedApplicationIds', $applications->pluck('application_id')->values()->all());
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

        return $this->applicantsRedirectTarget($jobPost->job_posting_id)
            ->with('success', $applications->count() . ' applicant(s) shortlisted.')
            ->with('updatedApplicationIds', $applications->pluck('application_id')->values()->all());
    }
}
