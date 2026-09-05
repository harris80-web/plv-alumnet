<?php

namespace App\Http\Controllers;

use App\Mail\DeactEmployerMail;
use App\Models\Employer;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmployerController extends Controller
{
    /** How many days out counts as "expiring soon" for the dashboard stat tile. */
    private const EXPIRING_SOON_DAYS = 7;

    /**
     * Employer dashboard — was a closure returning a static view with zero
     * data (see routes/web.php history); every number/list here was
     * previously hardcoded in the blade file.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $employer = $user->employer;
        $employerId = $user->user_id;

        // One grouped query for the whole applicant-status breakdown
        // (pending/shortlisted/hired/declined) instead of four separate
        // COUNT queries — the dashboard's stat tiles and the "total
        // applicants" figure both read off this same result.
        $applicationsByStatus = DB::table('job_applications')
            ->join('job_postings', 'job_postings.job_posting_id', '=', 'job_applications.job_id')
            ->where('job_postings.user_id', $employerId)
            ->selectRaw('application_status, COUNT(*) as total')
            ->groupBy('application_status')
            ->pluck('total', 'application_status');

        $stats = [
            'activePostings' => JobPosting::where('user_id', $employerId)->approved()->open()->count(),
            // Mirrors the same $job->applicants->where('pivot.is_read', false)
            // convention used on the My Job Postings page (general/jobPostings.blade.php).
            'unreadApplicants' => DB::table('job_applications')
                ->join('job_postings', 'job_postings.job_posting_id', '=', 'job_applications.job_id')
                ->where('job_postings.user_id', $employerId)
                ->where('job_applications.is_read', false)
                ->count(),
            'expiringSoon' => JobPosting::where('user_id', $employerId)->approved()->open()
                ->whereBetween('job_closing_date', [now()->toDateString(), now()->addDays(self::EXPIRING_SOON_DAYS)->toDateString()])
                ->count(),
            'totalApplicants' => $applicationsByStatus->sum(),
            'pending' => $applicationsByStatus->get('pending', 0),
            'shortlisted' => $applicationsByStatus->get('shortlisted', 0),
            'hired' => $applicationsByStatus->get('hired', 0),
        ];

        // Active (approved + not yet closed) postings first, then ranked by
        // applicant count within each group — an active post with 20
        // applicants should surface before a closed one with 2, but a
        // closed post with 20 shouldn't outrank an active post with 3
        // either. All of the employer's postings are included, not just
        // the active ones.
        $jobPostings = JobPosting::where('user_id', $employerId)
            ->withCount('applications')
            ->orderByRaw('CASE WHEN job_approved = 1 AND job_closing_date >= ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderByDesc('applications_count')
            ->get();

        // Highest applicant count among their postings — the "traffic by
        // posting" widget scales each bar against this so the busiest post
        // reads as a full bar, not an arbitrary fixed max.
        $maxApplicantsPerPosting = max(1, (int) $jobPostings->max('applications_count'));

        // Newest applicants across every posting, for the "jump straight to
        // whoever just applied" widget — same is_read flag the unreadApplicants
        // stat and My Job Postings page already use.
        $recentApplicants = JobApplication::with(['alumnus.user', 'job'])
            ->whereHas('job', fn ($q) => $q->where('user_id', $employerId))
            ->latest('application_date')
            ->take(6)
            ->get();

        $recentAnnouncements = Notice::category('announcement')
            ->visibleToEmployer()
            ->orderByDesc('event_datetime')
            ->take(3)
            ->get();

        return view('employer.dashboard', compact(
            'employer', 'stats', 'jobPostings', 'maxApplicantsPerPosting',
            'recentApplicants', 'recentAnnouncements'
        ));
    }

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
    public function show(Employer $employer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employer $employer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employer $employer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employer $employer)
    {
        //
    }

    public function updateEmployerProfile(Request $request, $employer)
    {
        $user = User::where('user_id', $employer)->firstOrFail();
        // Validate the incoming request data
        $validated = $request->validate([
            'user_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'user_first_name' => 'nullable|string',
            'user_last_name' => 'nullable|string',
            'user_middle_name' => 'nullable|string',
            'user_suffix' => 'nullable|string',
            'employer_position' => 'nullable|string',
            'user_email' => 'nullable|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'nullable|string',
            'employer_company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'employer_company_name' => 'nullable|string',
            'employer_year_established' => 'nullable|date_format:Y',
            'employer_company_size' => 'nullable|string',
            'employer_website_url' => 'nullable|url',
            'industry_id' => 'nullable|exists:industries,industry_id',
        ]);

        // Update the user's profile information
        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            if ($oldProfilePicture && Storage::disk('public')->exists($oldProfilePicture)) {
                Storage::disk('public')->delete($oldProfilePicture);
            }
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        $oldCompanyLogo = $user->employer->employer_company_logo ?? null;
        $companyLogo = null;
        if ($request->hasFile('employer_company_logo')) {
            if ($oldCompanyLogo && Storage::disk('public')->exists($oldCompanyLogo)) {
                Storage::disk('public')->delete($oldCompanyLogo);
            }
            $companyLogo = $request->file('employer_company_logo')->store('companyLogos', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $employer, $profilePicture, $companyLogo) {
                $employer = Employer::where('user_id', $employer)->firstOrFail();

                $employer->update([
                    'employer_position' => $validated['employer_position'] ?? $employer->employer_position,
                    'employer_company_name' => $validated['employer_company_name'] ?? $employer->employer_company_name,
                    'employer_year_established' => $validated['employer_year_established'] ?? $employer->employer_year_established,
                    'employer_company_size' => $validated['employer_company_size'] ?? $employer->employer_company_size,
                    'employer_website_url' => $validated['employer_website_url'] ?? $employer->employer_website_url,
                    'industry_id' => $validated['industry_id'] ?? $employer->industry_id,
                ]);


                $employer->user->update([
                    'user_first_name' => $validated['user_first_name'] ?? $employer->user->user_first_name,
                    'user_last_name' => $validated['user_last_name'] ?? $employer->user->user_last_name,
                    'user_middle_name' => $validated['user_middle_name'] ?? $employer->user->user_middle_name,
                    'user_suffix' => $validated['user_suffix'] ?? $employer->user->user_suffix,
                    'user_email' => $validated['user_email'] ?? $employer->user->user_email,
                    'user_number' => $validated['user_number'] ?? $employer->user->user_number,
                ]);

                if ($profilePicture != null) {
                    $employer->user->update([
                        'user_profile_picture' => $profilePicture,
                    ]);
                }

                if ($companyLogo != null) {
                    $employer->update([
                        'employer_company_logo' => $companyLogo,
                    ]);
                }
            });
        } catch (\Exception $e) {
            dd([
                'Message' => $e->getMessage(),
                'File' => $e->getFile(),
                'Line' => $e->getLine()
            ]);
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }
            if ($companyLogo) {
                Storage::disk('public')->delete($companyLogo);
            }

            return redirect()->route('user.profile')->with('error', 'An error occurred while uploading the resume: ' . $e->getMessage());
        }

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    public function deactivateEmployer(Request $request, $id)
    {
        $Employer = Employer::where('user_id', $id)->firstOrFail();

        $validated = $request->validate([
            'deactivate-reason' => 'required|string|max:255',
        ]);

        Log::info("Employer with ID {$Employer->user->user_id}: {$Employer->user->user_first_name} {$Employer->user->user_last_name} deactivated. Reason: {$validated['deactivate-reason']}");

        try {
            $Employer->user->update([
                'user_active' => false,
            ]);
            Mail::to($Employer->user->user_email)->send(new DeactEmployerMail($Employer->user, $validated['deactivate-reason']));

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deactivating the Employer. Please try again later.');
        }
        
        return back()->with('success', 'Employer deactivated successfully!');
    }

    /**
     * Bulk counterpart to deactivateEmployer() above — same user_active flip
     * + DeactEmployerMail, just over a batch. Mirrors
     * UserController::bulkDeactivateAlumni()'s "drop out of the batch"
     * philosophy: silently skips any selected id that's already inactive
     * (or not an employer id at all) rather than erroring the whole request.
     * Mail is queued (not ->send()) for the same reason bulkDeactivateAlumni
     * queues it — a synchronous SMTP failure partway through a batch would
     * otherwise leave later ids un-deactivated while earlier ones already
     * went through.
     */
    public function bulkDeactivateEmployer(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'deactivate-reason' => ['required', 'string', 'max:255'],
        ]);

        $employers = Employer::with('user')
            ->whereIn('user_id', $validated['ids'])
            ->whereHas('user', fn ($q) => $q->where('user_active', true))
            ->get();

        foreach ($employers as $employer) {
            $employer->user->update(['user_active' => false]);
            Log::info("Employer with ID {$employer->user_id}: {$employer->user->user_first_name} {$employer->user->user_last_name} deactivated (bulk). Reason: {$validated['deactivate-reason']}");
            Mail::to($employer->user->user_email)->queue(new DeactEmployerMail($employer->user, $validated['deactivate-reason']));
        }

        if ($employers->isEmpty()) {
            return back()->with('error', 'None of the selected accounts could be deactivated (already inactive?).');
        }

        return back()->with('success', $employers->count() . ' employer account(s) deactivated.');
    }
}
