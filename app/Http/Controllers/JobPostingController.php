<?php

namespace App\Http\Controllers;

use App\Mail\ApproveJobPostMail;
use App\Mail\DeclineJobPostMail;
use App\Mail\DeleteJobPostMail;
use App\Models\Industry;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Program;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserNotification;
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

    /** Blended-score floor (see JobMatch::blendedScore()) above which a job counts as "recommended" on the board. */
    private const RECOMMENDED_SCORE_THRESHOLD = 50;

    public function showJobBoard(Request $request)
    {
        $user = Auth::user();

        $jobPostings = $this->filteredJobPostingsQuery($request, $user)
            ->latest('job_postings.updated_at')
            ->paginate(6)
            ->withQueryString();

        return view('general.jobBoard', $this->jobBoardViewData($request, $user, $jobPostings, 'board'));
    }

    /**
     * Bookmarks tab of the job board — same filters/pagination/card partial
     * as showJobBoard(), scoped down to jobs the current alumnus saved.
     */
    public function showBookmarks(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni', 403);

        $jobPostings = $this->filteredJobPostingsQuery($request, $user)
            ->whereHas('bookmarkedBy', fn ($q) => $q->where('job_bookmarks.alumnus_id', $user->user_id))
            ->latest('job_postings.updated_at')
            ->paginate(6)
            ->withQueryString();

        return view('general.jobBoard', $this->jobBoardViewData($request, $user, $jobPostings, 'bookmarks'));
    }

    /**
     * "My Applications" tab — every job this alumnus has applied to,
     * regardless of whether the posting is still open/approved (a closed or
     * since-declined posting shouldn't vanish from someone's own application
     * history), sorted by when they applied (latest first). Unlike
     * showJobBoard()/showBookmarks() this doesn't start from
     * filteredJobPostingsQuery() — that base query's active()/approved()
     * scope would hide exactly the applications most worth checking on
     * (e.g. one still pending on a job that's since closed) — but it does
     * reuse the same search/program/job_type/date_posted filters via
     * applySearchFilters() so the search bar behaves the same on every tab.
     */
    public function showMyApplications(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->user_role === 'alumni', 403);

        $query = JobPosting::with(['skills', 'programs', 'industry', 'user'])
            ->whereHas('applications', fn ($q) => $q->where('alumnus_id', $user->user_id))
            ->addSelect(['applied_at' => JobApplication::selectRaw('application_date')
                ->whereColumn('job_applications.job_id', 'job_postings.job_posting_id')
                ->where('job_applications.alumnus_id', $user->user_id)
                ->limit(1),
            ]);

        $jobPostings = $this->applySearchFilters($query, $request)
            ->orderByDesc('applied_at')
            ->paginate(6)
            ->withQueryString();

        return view('general.jobBoard', $this->jobBoardViewData($request, $user, $jobPostings, 'applications'));
    }

    /**
     * Shared base query behind both the main job board and the bookmarks
     * tab, so "search/filter" behaves identically no matter which list
     * you're looking at. For a logged-in alumnus, also pulls in their
     * job_matches blended score as a `match_score` column (via a correlated
     * subquery, not a join, so it can't collide with job_postings' own
     * created_at/updated_at columns) and sorts recommended jobs first —
     * job-post-card.blade.php uses the same column + threshold to show the
     * "Recommended" badge, so the badge always matches the ordering.
     */
    private function filteredJobPostingsQuery(Request $request, ?\App\Models\User $user = null)
    {
        $query = JobPosting::active()->approved()->with(['skills', 'programs', 'industry', 'user']);

        if ($user && $user->user_role === 'alumni' && $user->alumnus) {
            $query->addSelect(['match_score' => \App\Models\JobMatch::selectRaw('COALESCE(score * 0.7 + ai_score * 0.3, score)')
                ->whereColumn('job_matches.job_posting_id', 'job_postings.job_posting_id')
                ->where('job_matches.alumnus_id', $user->user_id)
                ->limit(1),
            ])->orderByRaw('match_score IS NULL, match_score DESC');
        }

        return $this->applySearchFilters($query, $request);
    }

    /**
     * search/program/job_type/date_posted filters — shared by every job
     * listing query (board, bookmarks, my-applications) so filtering
     * behaves identically no matter which tab you're on.
     */
    private function applySearchFilters($query, Request $request)
    {
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('job_posting_title', 'like', "%{$search}%")
                    ->orWhere('job_posting_company', 'like', "%{$search}%");
            });
        }

        if ($programId = $request->input('program')) {
            $query->forProgram($programId);
        }

        if ($jobType = $request->input('job_type')) {
            $query->where('job_posting_employment_type', $jobType);
        }

        if ($datePosted = $request->input('date_posted')) {
            $since = match ($datePosted) {
                '24h' => now()->subDay(),
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
                default => null,
            };
            if ($since) {
                $query->where('created_at', '>=', $since);
            }
        }

        return $query;
    }

    /**
     * Guest-safe: showJobBoard() has no auth middleware (the public job
     * board reuses this same view via header-general), so $user may be null
     * and every alumni-only lookup below has to tolerate that.
     */
    private function jobBoardViewData(Request $request, ?\App\Models\User $user, $jobPostings, string $activeTab): array
    {
        $programs = Program::all();
        $industries = Industry::all();

        $appliedJobs = collect();
        $bookmarkedIds = [];

        if ($user && $user->user_role === 'alumni' && $user->alumnus) {
            $appliedJobs = $user->alumnus->appliedJobs->keyBy('job_posting_id');
            $bookmarkedIds = $user->alumnus->bookmarkedJobs->pluck('job_posting_id')->toArray();
        }

        $filters = $request->only(['search', 'program', 'job_type', 'date_posted']);
        $recommendedThreshold = self::RECOMMENDED_SCORE_THRESHOLD;

        return compact('jobPostings', 'programs', 'industries', 'user', 'appliedJobs', 'bookmarkedIds', 'activeTab', 'filters', 'recommendedThreshold');
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
            'hiring_limit' => ['required', 'integer', 'min:1'],
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

        $jobPost = null;

        try {
            DB::transaction(function () use ($validated, $jobImagePath, $id, $selectedPrograms, $description, &$jobPost) {
                $jobPost = JobPosting::create([
                    'job_posting_image' => $jobImagePath,
                    'job_posting_title' => $validated['job_posting_title'],
                    'job_posting_company' => $validated['job_posting_company'],
                    'job_posting_address' => $validated['job_posting_address'],
                    'job_posting_employment_type' => $validated['job_posting_employment_type'],
                    'job_posting_description' => $description,
                    'job_closing_date' => $validated['job_closing_date'],
                    'hiring_limit' => $validated['hiring_limit'],
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

        $this->notifyJobPostingSubmitted($jobPost, Auth::user()->user_role);

        if(Auth::user()->user_role === 'employer') {
            return redirect()->route('jobPosting.jobBoard')->with('success', 'Job posting added successfully!');
        } else {
            return redirect()->route('jobPosting.jobManagement')->with('success', 'Job posting added successfully!');
        }

    }

    /**
     * Mirrors showJobManagement()'s cross-review split: each staff role
     * reviews jobs from employers and from the *other* staff role, never
     * their own — so notification recipients follow the same rule (an
     * employer's posting alerts both staff roles, an admin's posting alerts
     * only super_admin, and vice versa).
     */
    private function notifyJobPostingSubmitted(JobPosting $jobPost, string $submitterRole): void
    {
        $notifyRoles = match ($submitterRole) {
            'admin' => ['super_admin'],
            'super_admin' => ['admin'],
            default => ['admin', 'super_admin'],
        };

        $recipientIds = User::whereIn('user_role', $notifyRoles)->pluck('user_id');
        if ($recipientIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $recipientIds->map(fn ($userId) => [
            'user_id' => $userId,
            'type' => 'job_posting_submitted',
            'title' => 'New job posting awaiting approval',
            'body' => "\"{$jobPost->job_posting_title}\" at {$jobPost->job_posting_company} was submitted for review.",
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        UserNotification::insert($rows);
    }

    public function showMyJobPosts(Request $request, $id)
    {
        $query = JobPosting::with(['skills', 'applicants', 'industry', 'programs'])->where('user_id', $id);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('job_posting_title', 'like', "%{$search}%")
                    ->orWhere('job_posting_company', 'like', "%{$search}%");
            });
        }

        if ($programId = $request->input('program')) {
            $query->forProgram($programId);
        }

        if ($jobType = $request->input('job_type')) {
            $query->where('job_posting_employment_type', $jobType);
        }

        if ($datePosted = $request->input('date_posted')) {
            $since = match ($datePosted) {
                '24h' => now()->subDay(),
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
                default => null,
            };
            if ($since) {
                $query->where('created_at', '>=', $since);
            }
        }

        $jobPostings = $query->latest()->paginate(6)->withQueryString();
        $programs = Program::all();
        $industries = Industry::all();
        $users = Auth::user();
        $filters = $request->only(['search', 'program', 'job_type', 'date_posted']);

        return view('general.jobPostings', compact('jobPostings', 'programs', 'industries', 'users', 'filters'));
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
            'hiring_limit' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($job) {
                    $hired = $job->hiredApplicantsCount();
                    if ($value !== null && $value < $hired) {
                        $fail("Hiring limit can't be lower than the {$hired} applicant(s) already hired for this job post.");
                    }
                },
            ],
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
                    'hiring_limit' => $validated['hiring_limit'] ?? $job->hiring_limit,
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
        UserNotification::create([
            'user_id' => $job->user_id,
            'type' => 'job_posting_approved',
            'title' => 'Job posting approved',
            'body' => "Your job posting \"{$job->job_posting_title}\" was approved and is now live on the job board.",
        ]);
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
        UserNotification::create([
            'user_id' => $job->user_id,
            'type' => 'job_posting_rejected',
            'title' => 'Job posting rejected',
            'body' => "Your job posting \"{$job->job_posting_title}\" was not approved. Reason: {$validated['decline-reason']}",
        ]);
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
