<?php

namespace App\Http\Controllers;

use App\Mail\DeactEmployerMail;
use App\Models\Employer;
use App\Models\JobPosting;
use App\Models\Notice;
use App\Models\Program;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\DashboardReportService;
use App\Services\PdfChartImages;
use App\Services\StoredFileCleaner;

class EmployerController extends Controller
{
    /** How many days out counts as "expiring soon" for the dashboard stat tile. */
    private const EXPIRING_SOON_DAYS = JobPosting::EXPIRING_SOON_DAYS;

    /** "Applications & Hires per Month" dropdown: months shown => label. */
    private const MONTHLY_PERIODS = [3 => 'Quarterly (3 months)', 6 => 'Semiannual (6 months)', 12 => 'Annually (1 year)'];
    private const DEFAULT_MONTHLY_PERIOD = 6;

    /**
     * Employer dashboard — was a closure returning a static view with zero
     * data (see routes/web.php history); every number/list here was
     * previously hardcoded in the blade file.
     *
     * The filter bar (Year / Job Posting / Year Graduated / College / Course,
     * same look as the admin dashboard's) narrows the Overview widgets and
     * the Analytics; the JOB POSTING carousel further down always lists
     * every post.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $employer = $user->employer;
        $filters = $this->dashboardFilters($request, $user->user_id);

        $recentAnnouncements = Notice::category('announcement')
            ->visibleToEmployer()
            ->orderByDesc('event_datetime')
            ->take(3)
            ->get();

        // Carousel: every post, active ones first, then by applicant count (unfiltered).
        $carouselPostings = JobPosting::where('user_id', $user->user_id)
            ->withCount('applications')
            ->orderByRaw('CASE WHEN job_approved = 1 AND job_closing_date >= ? THEN 0 ELSE 1 END', [JobPosting::localToday()])
            ->orderByDesc('applications_count')
            ->get();

        return view('employer.dashboard', array_merge(
            $this->dashboardData($user->user_id, $filters),
            $this->dashboardFilterOptions($user->user_id),
            compact('employer', 'recentAnnouncements', 'filters', 'carouselPostings')
        ));
    }

    /**
     * The dashboard's Overview + analytics as a PDF. The page's Export PDF
     * button POSTs each Chart.js canvas as a JPEG data URL (same approach as
     * the admin dashboard's export — see superAdmin/dashboard.blade.php's
     * exportPdfWithCharts()); without them (plain GET) each chart falls back
     * to a table in the PDF, so the export never breaks. It also sends the
     * applied filters and the monthly chart's period, so the PDF matches
     * what's on screen.
     */
    public function exportDashboardPdf(Request $request)
    {
        $user = Auth::user();
        $employer = $user->employer;
        $filters = $this->dashboardFilters($request, $user->user_id);
        $data = $this->dashboardData($user->user_id, $filters);
        $data['monthlyActivity'] = $data['monthlyByPeriod'][$filters['period']];
        $filterSummary = $this->dashboardFilterSummary($filters);
        $charts = PdfChartImages::fromRequest($request, ['chartApplicationStatus', 'chartMonthlyActivity']);

        $pdf = Pdf::loadView('employer.dashboard-report-pdf', array_merge($data, compact('employer', 'charts', 'filters', 'filterSummary')))
            ->setPaper('a4', 'portrait');

        return $pdf->download('employer_dashboard_report_' . now(config('app.local_timezone'))->format('Y-m-d') . '.pdf');
    }

    /**
     * The filter bar's values, cleaned up: only real years/ids survive, Job
     * Posting only keeps this employer's own posts, and the monthly chart's
     * period falls back to 6 months.
     *
     * Year defaults to the current year (same as the admin dashboard) until
     * the employer applies the filter bar themselves — its hidden "filtered"
     * marker then makes an empty Year mean "all years". Years after this one
     * are dropped (nothing can have happened yet).
     *
     * @return array{year: int[], job_id: int[], batch: int[], college: string[], program_id: int[], period: int, applied: bool}
     */
    private function dashboardFilters(Request $request, int $employerId): array
    {
        $service = app(DashboardReportService::class);
        $jobIds = $service->resolveIntArray($request->input('job_id'));
        $period = (int) $request->input('period');
        $currentYear = now(config('app.local_timezone'))->year;
        $applied = $request->has(DashboardReportService::FILTERS_APPLIED_PARAM)
            || $request->hasAny(['year', 'job_id', 'batch', 'college', 'program_id']);
        $years = array_values(array_filter($service->resolveIntArray($request->input('year')), fn ($y) => $y >= 2000 && $y <= $currentYear));
        if (!$applied) {
            $years = [$currentYear];
        }
        sort($years);

        return [
            'year' => $years,
            'job_id' => $jobIds ? JobPosting::where('user_id', $employerId)->whereIn('job_posting_id', $jobIds)->pluck('job_posting_id')->map(fn ($id) => (int) $id)->all() : [],
            'batch' => $service->resolveIntArray($request->input('batch')),
            'college' => $service->resolveColleges($request->input('college')),
            'program_id' => $service->resolveIntArray($request->input('program_id')),
            'period' => array_key_exists($period, self::MONTHLY_PERIODS) ? $period : self::DEFAULT_MONTHLY_PERIOD,
            // Something other than the defaults is in the URL (shows "Reset Filters").
            'applied' => $applied,
        ];
    }

    /** Options for the filter bar's dropdowns. */
    private function dashboardFilterOptions(int $employerId): array
    {
        $localTz = config('app.local_timezone');
        $currentYear = now($localTz)->year;
        // Year: from this employer's first post / application to this year.
        $firstYear = collect([
            JobPosting::where('user_id', $employerId)->min('created_at'),
            $this->employerApplications($employerId)->min('job_applications.application_date'),
        ])->filter()->map(fn ($d) => \Carbon\Carbon::parse($d, config('app.timezone'))->setTimezone($localTz)->year)->min() ?? $currentYear;

        $programs = Program::orderBy('program_name')->get();

        return [
            'yearOptions' => collect(range($currentYear, min($firstYear, $currentYear)))->mapWithKeys(fn ($y) => [$y => $y]),
            'jobPostingOptions' => JobPosting::where('user_id', $employerId)->latest()->pluck('job_posting_title', 'job_posting_id'),
            'batchYearOptions' => app(DashboardReportService::class)->batchYearOptions()->mapWithKeys(fn ($y) => [$y => $y]),
            'courseOptions' => $programs->mapWithKeys(fn ($p) => [$p->program_id => $p->labeledName()]),
            'courseCollegeAttrs' => $programs->mapWithKeys(fn ($p) => [$p->program_id => 'data-college="' . e($p->college) . '"']),
            'monthlyPeriods' => self::MONTHLY_PERIODS,
        ];
    }

    /** "Year: 2026 · Course: …" lines for the PDF header (only the filters in use). */
    private function dashboardFilterSummary(array $filters): array
    {
        return array_filter([
            'Year' => implode(', ', $filters['year']),
            'Job Posting' => JobPosting::whereIn('job_posting_id', $filters['job_id'])->pluck('job_posting_title')->implode(', '),
            'Year Graduated' => implode(', ', $filters['batch']),
            'College' => implode(', ', $filters['college']),
            'Course' => Program::whereIn('program_id', $filters['program_id'])->get()->map->labeledName()->implode(', '),
        ]);
    }

    /**
     * This employer's job applications, narrowed by the dashboard filters:
     * Job Posting, Year (the year the application came in, PH time) and the
     * applicant's Year Graduated / College / Course. $applyYear = false lets
     * the monthly chart pick its own months.
     */
    private function employerApplications(int $employerId, array $filters = [], bool $applyYear = true)
    {
        $query = DB::table('job_applications')
            ->join('job_postings', 'job_postings.job_posting_id', '=', 'job_applications.job_id')
            ->where('job_postings.user_id', $employerId);

        if (!empty($filters['job_id'])) {
            $query->whereIn('job_applications.job_id', $filters['job_id']);
        }

        if ($applyYear && !empty($filters['year'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['year'] as $year) {
                    [$from, $to] = \App\Models\JobApplication::localYearBounds($year);
                    $q->orWhere(fn ($r) => $r->where('job_applications.application_date', '>=', $from)->where('job_applications.application_date', '<', $to));
                }
            });
        }

        if (!empty($filters['batch']) || !empty($filters['college']) || !empty($filters['program_id'])) {
            $query->join('alumni', 'alumni.user_id', '=', 'job_applications.alumnus_id');
            if (!empty($filters['batch'])) {
                $query->whereIn(DB::raw('YEAR(alumni.alumnus_batch)'), $filters['batch']);
            }
            if (!empty($filters['program_id'])) {
                $query->whereIn('alumni.program_id', $filters['program_id']);
            }
            if (!empty($filters['college'])) {
                $query->whereIn('alumni.program_id', Program::whereIn('college', $filters['college'])->pluck('program_id')->all());
            }
        }

        return $query;
    }

    /** Everything the dashboard page and its PDF export both show, built once so the two can't drift apart. */
    private function dashboardData(int $employerId, array $filters = []): array
    {
        // One grouped query for the whole applicant-status breakdown
        // (pending/shortlisted/hired/declined) instead of four separate
        // COUNT queries — the dashboard's stat tiles, the "total
        // applicants" figure and the status chart all read off this result.
        $applicationsByStatus = $this->employerApplications($employerId, $filters)
            ->selectRaw('job_applications.application_status as status, COUNT(*) as total')
            ->groupBy('job_applications.application_status')
            ->pluck('total', 'status');

        $stats = [
            // Approved and not yet closed — the same rule (and number) as the
            // "Active Job Postings" card on My Job Postings. About the posts
            // themselves, so only the Job Posting filter applies.
            'activePostings' => JobPosting::where('user_id', $employerId)->approved()
                ->where(fn ($q) => $q->whereNull('job_closing_date')->orWhere('job_closing_date', '>=', JobPosting::localToday()))
                ->when($filters['job_id'] ?? [], fn ($q, $ids) => $q->whereIn('job_posting_id', $ids))
                ->count(),
            // Mirrors the same $job->applicants->where('pivot.is_read', false)
            // convention used on the My Job Postings page (general/jobPostings.blade.php).
            'unreadApplicants' => $this->employerApplications($employerId, $filters)
                ->where('job_applications.is_read', false)
                ->count(),
            // About the posts themselves, so only the Job Posting filter applies.
            'expiringSoon' => JobPosting::where('user_id', $employerId)->approved()->open()
                ->when($filters['job_id'] ?? [], fn ($q, $ids) => $q->whereIn('job_posting_id', $ids))
                ->whereBetween('job_closing_date', [JobPosting::localToday(), now(config('app.local_timezone'))->addDays(self::EXPIRING_SOON_DAYS)->toDateString()])
                ->count(),
            'totalApplicants' => (int) $applicationsByStatus->sum(),
            'pending' => (int) $applicationsByStatus->get('pending', 0),
            'shortlisted' => (int) $applicationsByStatus->get('shortlisted', 0),
            'hired' => (int) $applicationsByStatus->get('hired', 0),
        ];

        // Application Status Breakdown chart — every status, zeros included.
        $statusBreakdown = [
            'Pending' => (int) $applicationsByStatus->get('pending', 0),
            'Shortlisted' => (int) $applicationsByStatus->get('shortlisted', 0),
            'Hired' => (int) $applicationsByStatus->get('hired', 0),
            'Declined' => (int) $applicationsByStatus->get('declined', 0),
        ];

        // Applicant Traffic by Job Posting: active (approved + not yet
        // closed) postings first, then ranked by applicant count within each
        // group — an active post with 20 applicants should surface before a
        // closed one with 2, but a closed post with 20 shouldn't outrank an
        // active post with 3 either. Counts follow the filters.
        $applicantsPerPost = $this->employerApplications($employerId, $filters)
            ->selectRaw('job_applications.job_id, COUNT(*) as total')
            ->groupBy('job_applications.job_id')
            ->pluck('total', 'job_id');
        $today = JobPosting::localToday();
        $activeRank = fn ($job) => $job->job_approved && $job->job_closing_date && \Carbon\Carbon::parse($job->job_closing_date)->toDateString() >= $today ? 0 : 1;
        // With a Year filter, only the posts that were posted or received
        // applications in that year (not every old post at zero).
        $localTz = config('app.local_timezone');
        $years = $filters['year'] ?? [];
        $jobPostings = JobPosting::where('user_id', $employerId)
            ->when($filters['job_id'] ?? [], fn ($q, $ids) => $q->whereIn('job_posting_id', $ids))
            ->get()
            ->each(fn ($job) => $job->applications_count = (int) $applicantsPerPost->get($job->job_posting_id, 0))
            ->filter(fn ($job) => !$years || $job->applications_count > 0 || in_array($job->created_at->copy()->setTimezone($localTz)->year, $years, true))
            ->sort(fn ($a, $b) => [$activeRank($a), $b->applications_count, $b->job_posting_id] <=> [$activeRank($b), $a->applications_count, $a->job_posting_id])
            ->values();

        // Highest applicant count among their postings — the "traffic by
        // posting" widget scales each bar against this so the busiest post
        // reads as a full bar, not an arbitrary fixed max.
        $maxApplicantsPerPosting = max(1, (int) $jobPostings->max('applications_count'));

        $monthlyActivity = $this->monthlyApplicationsAndHires($employerId, $filters);
        $monthlyByPeriod = $this->monthlyByPeriod($monthlyActivity, $years);

        return compact('stats', 'statusBreakdown', 'jobPostings', 'maxApplicantsPerPosting', 'monthlyActivity', 'monthlyByPeriod');
    }

    /**
     * Applications received and applicants hired per month for the 12
     * months up to this month (the page's dropdown then shows the last 3, 6
     * or 12 of them), bucketed by the LOCAL (PH) calendar month —
     * timestamps are stored in app.timezone, so they're converted before
     * grouping rather than grouped in SQL. With a Year filter for past
     * year(s) only, the 12 months end in December of the latest one.
     *
     * @return array{labels: string[], applications: int[], hires: int[]}
     */
    private function monthlyApplicationsAndHires(int $employerId, array $filters = []): array
    {
        $localTz = config('app.local_timezone');
        $storedTz = config('app.timezone');
        $lastMonth = now($localTz)->startOfMonth();
        if (!empty($filters['year']) && max($filters['year']) < $lastMonth->year) {
            $lastMonth = \Carbon\Carbon::create(max($filters['year']), 12, 1, 0, 0, 0, $localTz);
        }
        $months = collect(range(11, 0))->map(fn ($i) => $lastMonth->copy()->subMonths($i));
        $since = $months->first()->copy()->setTimezone($storedTz)->toDateTimeString();
        $until = $lastMonth->copy()->addMonth()->setTimezone($storedTz)->toDateTimeString();

        $rows = $this->employerApplications($employerId, $filters, false)
            ->where(fn ($q) => $q
                ->where(fn ($r) => $r->where('job_applications.application_date', '>=', $since)->where('job_applications.application_date', '<', $until))
                ->orWhere(fn ($r) => $r->where('job_applications.hired_at', '>=', $since)->where('job_applications.hired_at', '<', $until)))
            ->get(['job_applications.application_date', 'job_applications.hired_at']);

        $monthKey = fn ($value) => $value ? \Carbon\Carbon::parse($value, $storedTz)->setTimezone($localTz)->format('Y-m') : null;
        $applications = $rows->map(fn ($r) => $monthKey($r->application_date))->filter()->countBy();
        $hires = $rows->map(fn ($r) => $monthKey($r->hired_at))->filter()->countBy();

        return [
            'keys' => $months->map(fn ($m) => $m->format('Y-m'))->all(),
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->all(),
            'applications' => $months->map(fn ($m) => (int) $applications->get($m->format('Y-m'), 0))->all(),
            'hires' => $months->map(fn ($m) => (int) $hires->get($m->format('Y-m'), 0))->all(),
        ];
    }

    /**
     * What the monthly chart shows for each dropdown choice (3 / 6 / 12): the
     * last N months, kept inside the selected Year(s) — e.g. Year 2026 +
     * Annually = Jan 2026 to this month, not Nov 2025 onwards. The page and
     * the PDF both read from this, so they always match.
     */
    private function monthlyByPeriod(array $series, array $years): array
    {
        $byPeriod = [];
        foreach (array_keys(self::MONTHLY_PERIODS) as $count) {
            $slice = array_map(fn ($values) => array_slice($values, -$count), $series);
            $keep = array_keys(array_filter($slice['keys'], fn ($key) => !$years || in_array((int) substr($key, 0, 4), $years, true)));
            $byPeriod[$count] = array_map(fn ($values) => array_values(array_intersect_key($values, array_flip($keep))), $slice);
        }

        return $byPeriod;
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
        // Self-service only — same missing-ownership-check bug already found
        // and fixed once for AlumnusController::updateAlumniProfile(). Only
        // employer/edit-profile.blade.php posts here, always with the
        // logged-in employer's own id.
        abort_unless(Auth::check() && Auth::id() == $employer, 403);

        $user = User::where('user_id', $employer)->firstOrFail();
        // Validate the incoming request data
        $validated = $request->validate([
            'user_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'user_first_name' => 'required|string|max:100',
            'user_last_name' => 'required|string|max:100',
            'user_middle_name' => 'nullable|string',
            'user_suffix' => 'nullable|string',
            'employer_position' => 'nullable|string',
            'user_email' => 'required|email|max:100|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_number' => 'nullable|string',
            'employer_company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
            'employer_company_name' => 'required|string|max:255',
            'employer_year_established' => 'nullable|date_format:Y',
            // employer_company_size is an unsignedBigInteger column — a
            // non-numeric value (e.g. a typed range like "50-100") used to
            // pass this as a bare 'string' rule, then fail at the DB layer
            // with a raw "Data truncated for column" SQL error instead of a
            // normal validation message.
            'employer_company_size' => 'nullable|integer|min:1',
            'employer_website_url' => 'nullable|url',
            'industry_id' => 'nullable|exists:industries,industry_id',
            'addresses' => ['nullable', 'array'],
            'addresses.*' => ['nullable', 'string', 'max:255'],
        ], [], [
            // Without these, an invalid field reads as "The employer
            // company size field must be an integer." — the raw attribute
            // name instead of the label actually shown next to that input.
            'user_profile_picture' => 'Profile Picture',
            'user_first_name' => 'First Name',
            'user_last_name' => 'Last Name',
            'user_middle_name' => 'Middle Name',
            'user_suffix' => 'Suffix',
            'employer_position' => 'Position',
            'user_email' => 'Email',
            'user_number' => 'Phone Number',
            'employer_company_logo' => 'Company Logo',
            'employer_company_name' => 'Business Name',
            'employer_year_established' => 'Year Established',
            'employer_company_size' => 'Company Size',
            'employer_website_url' => 'Company Website',
            'industry_id' => 'Industry / Sector',
        ]);

        // Update the user's profile information. The replaced picture/logo is
        // only removed after the save below succeeds (StoredFileCleaner) —
        // deleting it up front left a broken image whenever the save failed.
        $oldProfilePicture = $user->user_profile_picture ?? null;
        $profilePicture = null;
        if ($request->hasFile('user_profile_picture')) {
            $profilePicture = $request->file('user_profile_picture')->store('profilePictures', 'public');
        }

        $oldCompanyLogo = $user->employer->employer_company_logo ?? null;
        $companyLogo = null;
        if ($request->hasFile('employer_company_logo')) {
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

                // Company addresses — replaced wholesale, same pattern as the
                // resume builder's experience/certification rows. Safe here
                // since job postings store the address as a plain string at
                // posting time (not a foreign key), so deleting/recreating
                // these rows never orphans anything.
                if (array_key_exists('addresses', $validated)) {
                    $employer->addresses()->delete();
                    $addresses = collect($validated['addresses'])
                        ->map(fn ($a) => trim((string) $a))
                        ->filter()
                        ->unique()
                        ->values();
                    foreach ($addresses as $address) {
                        $employer->addresses()->create(['address' => $address]);
                    }
                }
            });
        } catch (\Exception $e) {
            if ($profilePicture) {
                Storage::disk('public')->delete($profilePicture);
            }
            if ($companyLogo) {
                Storage::disk('public')->delete($companyLogo);
            }

            // Never surface $e->getMessage() here — it's a raw driver/SQL
            // string (see the company-size bug this replaced: an out-of-range
            // value used to reach the DB and come back as a bare "Data
            // truncated for column..." dump). Validation above should catch
            // bad input before it gets this far; anything that still lands
            // here is unexpected, so keep the user-facing message generic.
            return redirect()->route('user.profile')->with('error', 'Could not save your profile changes. Please try again, and contact support if this keeps happening.');
        }

        // Saved — drop the replaced files (unless another record shares them).
        app(StoredFileCleaner::class)->deleteIfUnused(
            $profilePicture ? $oldProfilePicture : null,
            $companyLogo ? $oldCompanyLogo : null,
        );

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
            // Queued, not sent inline — a synchronous SMTP hiccup used to be
            // caught below and reported as "an error occurred" even though
            // the deactivation had already gone through, which read as the
            // whole action having silently failed. Matches
            // bulkDeactivateEmployer()'s already-queued mail just below.
            Mail::to($Employer->user->user_email)->queue(new DeactEmployerMail($Employer->user, $validated['deactivate-reason']));

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
