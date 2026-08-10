<?php

namespace App\Http\Controllers;

use App\Mail\ApplyJobMail;
use App\Mail\DeclineApplicantMail;
use App\Mail\HireApplicantMail;
use App\Mail\ShortlistApplicantMail;
use App\Models\Alumnus;
use App\Models\JobApplication;
use App\Models\JobPosting;
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
        Mail::to($job->user->user_email)->send(new ApplyJobMail($job, $alumni));

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

    public function hireApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $application->application_status = 'hired';
        $application->save();

        // Getting hired through the system is what flips these automatically
        // — an alumnus who found work elsewhere still sets these by hand on
        // their own profile (see AlumnusController::updateAlumniProfile).
        // First job date only gets set the first time ever — a later hire
        // (job change) shouldn't overwrite the actual first job date.
        $alumnus = $application->alumnus;
        $alumnus->alumnus_employment_status = true;
        if ($application->job->industry_id) {
            $alumnus->industry_id = $application->job->industry_id;
        }
        if (!$alumnus->alumnus_first_job_date) {
            $alumnus->alumnus_first_job_date = now();
        }
        $alumnus->save();

        Mail::to($application->alumnus->user->user_email)->send(new HireApplicantMail($application));

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $application->job_id])->with('success', 'Application status updated successfully.');
    }

    public function declineApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $application->application_status = 'declined';
        $application->save();
        Mail::to($application->alumnus->user->user_email)->send(new DeclineApplicantMail($application));

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $application->job_id])->with('success', 'Application status updated successfully.');
    }

    public function shortlistApplicant($applicationId)
    {
        $application = $this->authorizedApplication($applicationId);
        $application->application_status = 'shortlisted';
        $application->save();
        Mail::to($application->alumnus->user->user_email)->send(new ShortlistApplicantMail($application));

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $application->job_id])->with('success', 'Application status updated successfully.');
    }
}
