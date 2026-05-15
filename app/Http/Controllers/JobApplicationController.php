<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
        if (Auth::user()->user_role == 'alumni') {
            $alumniId = Auth::id();
        }

        if (Auth::user()->alumnus->alumnus_resume == null) {
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

        // Create a new job application
        JobApplication::create([
            'alumnus_id' => $alumniId,
            'job_id' => $jobPostingId,
            'application_status' => 'pending',
        ]);

        return redirect()->route('jobPosting.jobBoard');
    }

    public function showApplications($jobPostingId)
    {
        $jobPost = JobPosting::with('applicants.user')->findOrFail($jobPostingId);

        return view('general.jobApplicants', compact('jobPost'));
    }

    public function hireApplicant($applicationId)
    {
        $application = JobApplication::where('job_id', $applicationId)->first();
        $application->application_status = 'hired';
        $application->save();

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $applicationId])->with('success', 'Application status updated successfully.');
    }

    public function declineApplicant($applicationId)
    {
        $application = JobApplication::where('job_id', $applicationId)->first();
        $application->application_status = 'declined';
        $application->save();

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $applicationId])->with('success', 'Application status updated successfully.');
    }

    public function shortlistApplicant($applicationId)
    {
        $application = JobApplication::where('job_id', $applicationId)->first();
        $application->application_status = 'shortlisted';
        $application->save();

        return redirect()->route("jobApplication.showApplications", ["jobPostingId" => $applicationId])->with('success', 'Application status updated successfully.');
    }
}
