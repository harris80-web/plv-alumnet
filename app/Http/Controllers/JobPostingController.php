<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobPostingController extends Controller
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
    public function show(JobPosting $jobPosting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobPosting $jobPosting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPosting $jobPosting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosting $jobPosting)
    {
        //
    }

    public function showJobBoard()
    {
        $allJobs = JobPosting::all();
        $programs = Program::all();
        $applications = Auth::user()->alumnus ? Auth::user()->alumnus->appliedJobs->pluck('job_id')->toArray() : [];

        $user = Auth::user();
        $query = JobPosting::query()->with('user');

        if ($user->user_role === 'super_admin') {
            // Super Admin sees jobs from Employers and Admins
            $query->whereHas('user', function ($q) {
                $q->whereIn('user_role', ['employer', 'admin']);
            });
        } elseif ($user->user_role === 'admin') {
            // Admin sees jobs from Employers and Super Admins
            $query->whereHas('user', function ($q) {
                $q->whereIn('user_role', ['employer', 'super_admin']);
            });
        }

        $jobPostings = $query->get();
        return view('general.jobBoard', compact('jobPostings', 'programs', 'user', 'applications', 'allJobs'));
    }

    public function addJobPost(Request $request, $id)
    {
        $validated = $request->validate([
            'job_posting_image' => ['required', 'image', 'mimes:jpeg,png,jpg,svg'],
            'job_posting_title' => ['required', 'string'],
            'job_posting_company' => ['required', 'string'],
            'job_posting_address' => ['required', 'string'],
            'job_posting_employment_type' => ['required', 'string', Rule::in('Full-Time', 'Part-Time', 'Freelance')],
            'job_posting_description' => ['required', 'string'],
            'job_closing_date' => ['required', 'date'],
            'job_posting_setup' => ['required', 'string', Rule::in('On-Site', 'Remote', 'Hybrid')],
            'program' => ['required', 'array', 'max:3'],
            'program.*' => ['exists:programs,program_id'],
        ]);


        $jobImagePath = null;
        if ($request->hasFile('job_posting_image')) {
            $jobImagePath = $request->file('job_posting_image')->store('jobImages', 'public');
        }
        $selectedPrograms = array_unique(array_filter($request->program));

        try {
            DB::transaction(function () use ($validated, $jobImagePath, $id, $selectedPrograms) {
                $jobPost = JobPosting::create([
                    'job_posting_image' => $jobImagePath,
                    'job_posting_title' => $validated['job_posting_title'],
                    'job_posting_company' => $validated['job_posting_company'],
                    'job_posting_address' => $validated['job_posting_address'],
                    'job_posting_employment_type' => $validated['job_posting_employment_type'],
                    'job_posting_description' => $validated['job_posting_description'],
                    'job_closing_date' => $validated['job_closing_date'],
                    'job_posting_setup' => $validated['job_posting_setup'],
                    'employer_id' => $id,
                ]);
                $jobPost->programs()->attach($selectedPrograms);
            });
        } catch (\Exception $e) {
            if ($jobImagePath) {
                // Delete the uploaded image if the transaction fails
                Storage::disk('public')->delete($jobImagePath);
            }
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('jobPosting.jobBoard')->with('success', 'Job posting added successfully!');
    }

    public function showMyJobPosts($id)
    {
        $jobPostings = JobPosting::where('employer_id', $id)->get();
        $programs = Program::all();
        $users = Auth::user();
        return view('general.jobPostings', compact('jobPostings', 'programs', 'users'));
    }

    public function editJobPost(Request $request, $id)
    {
        $job = JobPosting::findOrFail($id);
        $validated = $request->validate([
            'job_posting_image' => 'nullable|image|mimes:jpeg,png,jpg,svg',
            'job_posting_title' => 'nullable|string',
            'job_posting_company' => 'nullable|string',
            'job_posting_address' => 'nullable|string',
            'job_posting_employment_type' => 'nullable|string',
            'job_posting_description' => 'nullable|string',
            'job_closing_date' => 'nullable|date',
            'job_posting_setup' => 'nullable|string',
            'program' => 'required|array|max:3',
            'program.*' => 'exists:programs,program_id',
        ]);

        $oldJobImage = $job->job_posting_image ?? null;
        $jobImage = null;
        if ($request->hasFile('job_posting_image')) {
            if ($oldJobImage && Storage::disk('public')->exists($oldJobImage)) {
                Storage::disk('public')->delete($oldJobImage);
            }
            $jobImage = $request->file('job_posting_image')->store('jobImages', 'public');
        }
        $selectedPrograms = array_unique(array_filter($request->program));

        try {
            DB::transaction(function () use ($validated, $jobImage, $job, $selectedPrograms) {
                $job->update([
                    'job_posting_title' => $validated['job_posting_title'] ?? $job->job_posting_title,
                    'job_posting_company' => $validated['job_posting_company'] ?? $job->job_posting_company,
                    'job_posting_address' => $validated['job_posting_address'] ?? $job->job_posting_address,
                    'job_posting_employment_type' => $validated['job_posting_employment_type'] ?? $job->job_posting_employment_type,
                    'job_posting_description' => $validated['job_posting_description'] ?? $job->job_posting_description,
                    'job_closing_date' => $validated['job_closing_date'] ?? $job->job_closing_date,
                    'job_posting_setup' => $validated['job_posting_setup'] ?? $job->job_posting_setup,
                ]);

                $job->programs()->sync($selectedPrograms);

                if ($jobImage != null) {
                    $job->update([
                        'job_posting_image' => $jobImage,
                    ]);
                }
            });
        } catch (\Exception $e) {
            if ($jobImage) {
                Storage::disk('public')->delete($jobImage);
            }
            return back()->withErrors(['error' => 'Failed to upload job posting image. Please try again.']);
        }

        return redirect()->route('jobPosting.myJobPosts', ['id' => $job->employer_id]);
    }

    public function showJobManagement()
    {
        $jobPostings = JobPosting::latest()->get();
        $programs = Program::all();
        $users = Auth::user();
        return view('superAdmin.jobManagement', compact('jobPostings', 'programs', 'users'));
    }

    public function approveJobPost($id)
    {
        $job = JobPosting::findOrFail($id);
        $job->update(['job_approved' => true]);
        return redirect()->route('jobPosting.jobManagement')->with('success', 'Job posting approved successfully!');
    }
}
