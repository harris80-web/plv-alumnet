<?php

namespace App\Http\Controllers;

use App\Mail\ApproveJobPostMail;
use App\Mail\DeclineJobPostMail;
use App\Mail\DeleteJobPostMail;
use App\Models\Industry;
use App\Models\JobPosting;
use App\Models\Program;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

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
        $jobPostings = JobPosting::active()->with(['skills', 'programs', 'industry'])->latest('updated_at')->get();
        $programs = Program::all();
        $industries = Industry::all();
        $user = Auth::user();
        $applications = [];

        if ($user->alumnus) {
            $applications = $user->alumnus->appliedJobs->pluck('job_id')->toArray();
        }

        return view('general.jobBoard', compact('jobPostings', 'programs', 'industries', 'user', 'applications'));
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
            'industry_id' => ['nullable', 'exists:industries,industry_id'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
        ]);


        $jobImagePath = null;
        if ($request->hasFile('job_posting_image')) {
            $jobImagePath = $request->file('job_posting_image')->store('jobImages', 'public');
        }
        $selectedPrograms = array_unique(array_filter($request->program));
        $description = Purifier::clean($validated['job_posting_description'], 'job_description');

        try {
            DB::transaction(function () use ($validated, $jobImagePath, $id, $selectedPrograms, $description) {
                $jobPost = JobPosting::create([
                    'job_posting_image' => $jobImagePath,
                    'job_posting_title' => $validated['job_posting_title'],
                    'job_posting_company' => $validated['job_posting_company'],
                    'job_posting_address' => $validated['job_posting_address'],
                    'job_posting_employment_type' => $validated['job_posting_employment_type'],
                    'job_posting_description' => $description,
                    'job_closing_date' => $validated['job_closing_date'],
                    'job_posting_setup' => $validated['job_posting_setup'],
                    'industry_id' => $validated['industry_id'] ?? null,
                    'user_id' => $id,
                ]);
                $jobPost->programs()->attach($selectedPrograms);
                $this->syncJobSkills($jobPost, $validated['skills'] ?? []);
            });
        } catch (\Exception $e) {
            if ($jobImagePath) {
                // Delete the uploaded image if the transaction fails
                Storage::disk('public')->delete($jobImagePath);
            }
            return back()->withErrors($e->getMessage());
        }

        if(Auth::user()->user_role === 'employer') {
            return redirect()->route('jobPosting.jobBoard')->with('success', 'Job posting added successfully!');
        } else {
            return redirect()->route('jobPosting.jobManagement')->with('success', 'Job posting added successfully!');
        }
        
    }

    public function showMyJobPosts($id)
    {
        $jobPostings = JobPosting::with(['skills', 'applicants', 'industry'])->where('user_id', $id)->latest()->get();
        $programs = Program::all();
        $industries = Industry::all();
        $users = Auth::user();
        return view('general.jobPostings', compact('jobPostings', 'programs', 'industries', 'users'));
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
            'industry_id' => ['nullable', 'exists:industries,industry_id'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
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
        $description = isset($validated['job_posting_description'])
            ? Purifier::clean($validated['job_posting_description'], 'job_description')
            : null;

        try {
            DB::transaction(function () use ($validated, $jobImage, $job, $selectedPrograms, $description) {
                $job->update([
                    'job_posting_title' => $validated['job_posting_title'] ?? $job->job_posting_title,
                    'job_posting_company' => $validated['job_posting_company'] ?? $job->job_posting_company,
                    'job_posting_address' => $validated['job_posting_address'] ?? $job->job_posting_address,
                    'job_posting_employment_type' => $validated['job_posting_employment_type'] ?? $job->job_posting_employment_type,
                    'job_posting_description' => $description ?? $job->job_posting_description,
                    'job_closing_date' => $validated['job_closing_date'] ?? $job->job_closing_date,
                    'job_posting_setup' => $validated['job_posting_setup'] ?? $job->job_posting_setup,
                    'industry_id' => array_key_exists('industry_id', $validated)
                        ? ($validated['industry_id'] ?: null)
                        : $job->industry_id,
                ]);

                $job->programs()->sync($selectedPrograms);
                $this->syncJobSkills($job, $validated['skills'] ?? []);

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

        return redirect()->route('jobPosting.myJobPosts', ['id' => $job->user_id]);
    }

    public function showJobManagement()
    {
        $programs = Program::all();
        $industries = Industry::all();
        $users = Auth::user();

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

        $jobPostings = $query->latest()->get();
        return view('superAdmin.jobManagement', compact('jobPostings', 'programs', 'industries', 'users'));
    }

    public function approveJobPost($id)
    {
        $job = JobPosting::findOrFail($id);
        $job->update(['job_approved' => true]);
        Mail::to($job->employer->user->user_email)->send(new ApproveJobPostMail($job));
        return redirect()->route('jobPosting.jobManagement')->with('success', 'Job posting approved successfully!');
    }

    public function declineJobPost(Request $request, $id)
    {
        $job = JobPosting::findOrFail($id);

        $validated = $request->validate([
            'decline-reason' => 'required|string|max:255',
        ]);
        Log::info("Job posting with ID {$job->job_posting_id}, from: {$job->user->user_first_name} {$job->user->user_last_name} declined. Reason: {$validated['decline-reason']}");
        Mail::to($job->employer->user->user_email)->send(new DeclineJobPostMail($job));
        $job->delete();
        return redirect()->route('jobPosting.jobManagement')->with('success', 'Job posting declined successfully!');
    }

     public function deleteJobPost(Request $request, $id)
    {
        $job = JobPosting::findOrFail($id);

        $validated = $request->validate([
            'delete-reason' => 'required|string|max:255',
        ]);

        // Log the deletion reason (you can also store this in a database table if needed)
        Log::info("Job posting with ID {$job->job_posting_id}, from: {$job->user->user_first_name} {$job->user->user_last_name} deleted. Reason: {$validated['delete-reason']}");
        Mail::to($job->user->user_email)->send(new DeleteJobPostMail($job->user, $job, $validated['delete-reason']));
        $job->delete();

        return back()->with('success', 'Job posting deleted successfully!');
    }

    /**
     * Replaces the job's required skills wholesale from a flat list of
     * names — same find-or-create-by-name pattern already established in
     * ResumeBuilderController::save() for alumni skills, so free-typed
     * names still resolve to the shared `skills` table instead of drifting.
     * Every skill added here is marked required with the pivot's default
     * weight — no weighting UI, matching the literal "required skill" ask.
     */
    private function syncJobSkills(JobPosting $jobPost, array $skillNames): void
    {
        $skillIds = collect($skillNames)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->map(function ($name) {
                return Skill::firstOrCreate(
                    ['skill_name' => $name],
                    ['skill_category' => 'domain']
                )->skill_id;
            });

        $jobPost->skills()->sync(
            $skillIds->mapWithKeys(fn ($skillId) => [$skillId => ['is_required' => true]])->all()
        );
    }

    /**
     * Image upload handler for the rich-text description editor's toolbar
     * button (see resources/views/partials/rich-text-editor.blade.php) —
     * Quill inserts the returned URL into the content rather than embedding
     * the file as base64, same storage convention as job_posting_image.
     */
    public function uploadDescriptionImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store('jobDescriptionImages', 'public');

        return response()->json(['url' => asset('storage/' . $path)]);
    }
}
