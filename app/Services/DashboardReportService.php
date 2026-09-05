<?php

namespace App\Services;

use App\Models\Alumnus;
use App\Models\Employer;
use App\Models\Industry;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Every query behind the admin dashboard's "Reports & Analytics" section —
 * pulled out of UserController so the same report-building logic can be
 * reused, unduplicated, by both the original all-in-one dashboard and the
 * grouped report-detail pages (see ReportController). Filters are always
 * arrays now (multi-select, item 15) — an empty array means "no filter",
 * matching the old null-means-no-filter convention one level up.
 */
class DashboardReportService
{
    /** "Hires per Month" range selector — one of a small fixed set of lengths, never an arbitrary integer. */
    public function resolveHireMonths(?string $raw): int
    {
        $allowed = [3, 6, 12, 24];
        $value = (int) $raw;

        return in_array($value, $allowed, true) ? $value : 6;
    }

    /** How many rows the "Top Hiring Companies" list shows. */
    public function resolveTopCompaniesLimit(?string $raw): int
    {
        $allowed = [5, 10, 15, 20];
        $value = (int) $raw;

        return in_array($value, $allowed, true) ? $value : 5;
    }

    /** Normalizes the Employment Status filter to a de-duped subset of ['employed', 'unemployed']. */
    public function resolveEmploymentStatuses($raw): array
    {
        return collect(is_array($raw) ? $raw : [])
            ->filter(fn ($v) => in_array($v, ['employed', 'unemployed'], true))
            ->unique()->values()->all();
    }

    /** Shared by Batch (graduation year) and Program ID filters — both are positive integers. */
    public function resolveIntArray($raw): array
    {
        return collect(is_array($raw) ? $raw : [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->unique()->values()->all();
    }

    /** College filter — only real Program::COLLEGES codes survive, anything else is silently dropped. */
    public function resolveColleges($raw): array
    {
        $allowed = array_keys(Program::COLLEGES);

        return collect(is_array($raw) ? $raw : [])
            ->filter(fn ($v) => in_array($v, $allowed, true))
            ->unique()->values()->all();
    }

    /**
     * Item 16 — Year filter, deliberately separate from Batch (alumni
     * graduation year). Any positive year is accepted; one outside the
     * system's real data range just yields empty results for that
     * report, same as picking a Batch year nobody graduated in.
     */
    public function resolveYears($raw): array
    {
        return $this->resolveIntArray($raw);
    }

    /** Descending list of years actually spanned by event-dated data, for the Year filter's dropdown. */
    public function yearOptions(): array
    {
        $currentYear = (int) now()->year;

        $minYear = collect([
            DB::table('job_postings')->min('created_at'),
            DB::table('employers')->min('created_at'),
            DB::table('job_applications')->whereNotNull('hired_at')->min('hired_at'),
        ])->filter()->map(fn ($d) => Carbon::parse($d)->year)->min();

        $minYear = $minYear ?: $currentYear;

        return range($currentYear, $minYear);
    }

    /**
     * The 4 overview stat cards. $years only ever touches Active Job
     * Postings here (item 16 — it's the one genuinely event-dated report
     * in this set; Total Alumni/Employment-status-derived counts are
     * cohort stats, not date-scoped).
     */
    public function buildOverviewStats(array $batches, array $programIds, array $employmentStatuses, array $colleges, array $years): array
    {
        $applyAlumniFilters = function ($query, string $alumniTable = 'alumni') use ($batches, $programIds, $employmentStatuses, $colleges) {
            if (!empty($batches)) {
                $query->whereIn(DB::raw("YEAR($alumniTable.alumnus_batch)"), $batches);
            }
            if (!empty($programIds)) {
                $query->whereIn("$alumniTable.program_id", $programIds);
            }
            if (count($employmentStatuses) === 1) {
                $query->where("$alumniTable.alumnus_employment_status", $employmentStatuses[0] === 'employed' ? 1 : 0);
            }
            if (!empty($colleges)) {
                $query->whereIn("$alumniTable.program_id", function ($sub) use ($colleges) {
                    $sub->select('program_id')->from('programs')->whereIn('college', $colleges);
                });
            }
            return $query;
        };

        $jobApplicationsQuery = $applyAlumniFilters(
            DB::table('job_applications')->join('alumni', 'alumni.user_id', '=', 'job_applications.alumnus_id')
        );
        $jobPlacementCount = (clone $jobApplicationsQuery)->where('application_status', 'hired')->count();
        $jobApplicationCount = $jobApplicationsQuery->count();
        $jobPlacementRate = $jobApplicationCount > 0
            ? ($jobPlacementCount / $jobApplicationCount) * 100
            : 0;

        $activeJobsQuery = DB::table('job_postings')
            ->where('job_approved', true)
            ->where('job_closing_date', '>', now());
        if (!empty($years)) {
            $activeJobsQuery->whereIn(DB::raw('YEAR(job_closing_date)'), $years);
        }

        return [
            'jobPlacementRate' => round($jobPlacementRate, 2),
            'activeJobs' => $activeJobsQuery->count(),
            'industryPartners' => DB::table('users')
                ->where('user_active', true)
                ->where('user_role', 'employer')
                ->count(),
            'alumniUsers' => $applyAlumniFilters(
                DB::table('users')
                    ->join('alumni', 'alumni.user_id', '=', 'users.user_id')
                    ->where('users.user_active', true)
                    ->where('users.user_role', 'alumni')
            )->count(),
        ];
    }

    /**
     * Everything under the dashboard's "Reports & Analytics" section.
     *
     * Design note: $batches/$programIds/$colleges scope every report here (a
     * cohort lens on the whole section), but $employmentStatuses is
     * deliberately NOT applied to the rate/breakdown reports
     * (employment-by-batch, industry distribution, gender breakdown,
     * alignment) — filtering "Employed" alumni down to a chart of
     * employment status would make the chart trivially 100/0%. It's applied
     * only to the Employed Alumni table below, and only when it resolves to
     * exactly ['unemployed'] (picking both, or neither, keeps the default
     * "employed" list). $years (item 16) only touches the reports that are
     * genuinely event-dated: Hires per Month, Employment by Month, and
     * Registered/Pending Companies — never the batch-denominated reports,
     * where mixing the two concepts would be confusing.
     */
    public function buildEmploymentReports(
        array $batches,
        array $programIds,
        array $employmentStatuses,
        array $colleges,
        array $years,
        int $hireMonths = 6,
        int $topCompaniesLimit = 5
    ): array {
        $alumniQuery = Alumnus::with(['user', 'program', 'industry']);
        if (!empty($batches)) {
            $alumniQuery->whereIn(DB::raw('YEAR(alumnus_batch)'), $batches);
        }
        if (!empty($programIds)) {
            $alumniQuery->whereIn('program_id', $programIds);
        }
        if (!empty($colleges)) {
            $alumniQuery->whereHas('program', fn ($q) => $q->whereIn('college', $colleges));
        }
        $allAlumni = $alumniQuery->get();
        $employedAlumni = $allAlumni->where('alumnus_employment_status', true);

        $totalAlumni = $allAlumni->count();
        $employedCount = $employedAlumni->count();
        $employmentRate = $totalAlumni > 0 ? round($employedCount / $totalAlumni * 100, 2) : 0;
        $unemploymentRate = $totalAlumni > 0 ? round(100 - $employmentRate, 2) : 0;

        // 1. Employment rate by batch/year
        $employmentByBatch = $allAlumni->groupBy(fn ($a) => $a->alumnus_batch?->year)
            ->filter(fn ($group, $key) => $key !== null && $key !== '')
            ->sortKeys()
            ->map(function ($group) {
                $total = $group->count();
                $employed = $group->where('alumnus_employment_status', true)->count();
                return ['total' => $total, 'employed' => $employed, 'rate' => $total > 0 ? round($employed / $total * 100, 2) : 0];
            });

        // "Which month do alumni get employed" — a Jan–Dec seasonality count
        // from alumnus_employment_date, pooled across every year in the
        // (batch/program/college-filtered) cohort by default; scoped down to
        // the selected Year(s) when the Year filter is set (item 16).
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $employedWithDate = $employedAlumni->filter(fn ($a) => $a->alumnus_employment_date);
        if (!empty($years)) {
            $employedWithDate = $employedWithDate->filter(fn ($a) => in_array($a->alumnus_employment_date->year, $years, true));
        }
        $employmentByMonth = collect($monthLabels)->mapWithKeys(function ($label, $i) use ($employedWithDate) {
            $count = $employedWithDate->filter(fn ($a) => $a->alumnus_employment_date->month === $i + 1)->count();
            return [$label => $count];
        });

        // Industry/sector distribution of employed alumni — lists every
        // industry in the system (0 shown for one with no employed alumni
        // yet), not just the ones that happen to have a match right now.
        $industryCounts = $employedAlumni->groupBy(fn ($a) => $a->industry->industry_name ?? 'Unspecified')->map->count();
        $industryDistribution = Industry::orderBy('industry_name')->pluck('industry_name')
            ->mapWithKeys(fn ($name) => [$name => $industryCounts->get($name, 0)]);
        if ($industryCounts->has('Unspecified')) {
            $industryDistribution->put('Unspecified', $industryCounts->get('Unspecified'));
        }
        $industryDistribution = $industryDistribution->sortDesc();

        // Employment rate by gender
        $genderLabels = Alumnus::genderLabels();
        $genderEmployment = $allAlumni->groupBy(fn ($a) => $a->alumnus_gender ?: '')
            ->map(function ($group, $key) use ($genderLabels) {
                $total = $group->count();
                $employed = $group->where('alumnus_employment_status', true)->count();
                return [
                    'label' => $genderLabels[$key] ?? 'Unspecified',
                    'total' => $total,
                    'employed' => $employed,
                    'rate' => $total > 0 ? round($employed / $total * 100, 2) : 0,
                ];
            })->values();

        // Job-to-degree alignment, overall and per program (employed alumni only)
        $overallAligned = $employedAlumni->filter->hasCourseAlignedJob()->count();
        $alignmentRate = $employedCount > 0 ? round($overallAligned / $employedCount * 100, 2) : 0;
        $programAlignmentCounts = $employedAlumni->groupBy(fn ($a) => $a->program->program_name ?? 'Unspecified')
            ->map(function ($group) {
                $total = $group->count();
                $aligned = $group->filter->hasCourseAlignedJob()->count();
                return ['total' => $total, 'aligned' => $aligned, 'rate' => $total > 0 ? round($aligned / $total * 100, 2) : 0];
            });
        // Lists every program in the system, not just the ones with an
        // employed match right now — unless the Course and/or College
        // filter already narrows the cohort, where only the matching
        // programs apply.
        if (!empty($programIds) || !empty($colleges)) {
            $programAlignment = $programAlignmentCounts->sortByDesc('total');
        } else {
            $emptyProgramRow = ['total' => 0, 'aligned' => 0, 'rate' => 0];
            $programAlignment = Program::orderBy('program_name')->pluck('program_name')
                ->mapWithKeys(fn ($name) => [$name => $programAlignmentCounts->get($name, $emptyProgramRow)]);
            if ($programAlignmentCounts->has('Unspecified')) {
                $programAlignment->put('Unspecified', $programAlignmentCounts->get('Unspecified'));
            }
            $programAlignment = $programAlignment->sortByDesc('total');
        }

        // Employment interval — months from batch graduation date to first job date.
        $employmentInterval = ['Before Graduation' => 0, 'Within 6 months' => 0, '6–12 months' => 0, '1–2 years' => 0, 'Over 2 years' => 0];
        foreach ($allAlumni as $a) {
            if (!$a->alumnus_first_job_date || !$a->alumnus_batch) {
                continue;
            }
            if ($a->wasEmployedBeforeGraduation()) {
                $employmentInterval['Before Graduation']++;
                continue;
            }
            $graduation = Carbon::parse($a->alumnus_batch);
            $months = max(0, $graduation->diffInMonths($a->alumnus_first_job_date, false));
            $bucket = match (true) {
                $months <= 6 => 'Within 6 months',
                $months <= 12 => '6–12 months',
                $months <= 24 => '1–2 years',
                default => 'Over 2 years',
            };
            $employmentInterval[$bucket]++;
        }

        // "Job Before Graduation" & "From an Internship" — both answered
        // only once an alumnus has a recorded first-job date.
        $firstJobKnownAlumni = $allAlumni->filter(fn ($a) => $a->alumnus_first_job_date);
        $beforeGraduationAlumni = $firstJobKnownAlumni->filter->wasEmployedBeforeGraduation();
        $beforeGraduationCount = $beforeGraduationAlumni->count();
        $beforeGraduationRate = $firstJobKnownAlumni->count() > 0
            ? round($beforeGraduationCount / $firstJobKnownAlumni->count() * 100, 2)
            : 0;

        $internshipAlumni = $firstJobKnownAlumni->filter(fn ($a) => $a->alumnus_first_job_is_internship);
        $internshipCount = $internshipAlumni->count();
        $internshipRate = $firstJobKnownAlumni->count() > 0
            ? round($internshipCount / $firstJobKnownAlumni->count() * 100, 2)
            : 0;
        $beforeGraduationInternshipCount = $beforeGraduationAlumni->filter(fn ($a) => $a->alumnus_first_job_is_internship)->count();

        // Item — pie-chart-friendly version of the same 3 numbers above: 4
        // mutually exclusive buckets (the raw counts overlap, which doesn't
        // slice into a pie cleanly) via inclusion-exclusion, e.g. "Before
        // Graduation Only" = every before-graduation alumnus MINUS the ones
        // who were also an internship.
        $jobBeforeGradPie = [
            'Before Graduation & Internship' => $beforeGraduationInternshipCount,
            'Before Graduation Only' => $beforeGraduationCount - $beforeGraduationInternshipCount,
            'Internship Only' => $internshipCount - $beforeGraduationInternshipCount,
            'Neither' => $firstJobKnownAlumni->count() - $beforeGraduationCount - $internshipCount + $beforeGraduationInternshipCount,
        ];

        // Employed Alumni report — the one table where $employmentStatuses
        // actually changes which list is shown, and only when it resolves
        // to exactly ["unemployed"] (see class doc note above).
        $showUnemployedTable = $employmentStatuses === ['unemployed'];
        $employedAlumniTable = $showUnemployedTable
            ? $allAlumni->where('alumnus_employment_status', false)->sortByDesc('updated_at')->values()
            : $employedAlumni->sortByDesc('alumnus_employment_date')->values();

        // Job placement & hiring — same alumni cohort filters as jobPlacementRate.
        $hiringBase = fn () => DB::table('job_applications')
            ->join('alumni', 'alumni.user_id', '=', 'job_applications.alumnus_id')
            ->join('job_postings', 'job_postings.job_posting_id', '=', 'job_applications.job_id')
            ->when(!empty($batches), fn ($q) => $q->whereIn(DB::raw('YEAR(alumni.alumnus_batch)'), $batches))
            ->when(!empty($programIds), fn ($q) => $q->whereIn('alumni.program_id', $programIds))
            ->when(!empty($colleges), fn ($q) => $q->whereIn('alumni.program_id', function ($sub) use ($colleges) {
                $sub->select('program_id')->from('programs')->whereIn('college', $colleges);
            }));

        $totalApplications = $hiringBase()->count();
        $totalHired = $hiringBase()->where('job_applications.application_status', 'hired')->count();

        // hired_at is set the moment an application is actually marked
        // hired. Without a Year filter: last $hireMonths months from now
        // (unchanged default). With one: Jan–Dec buckets for each selected
        // year instead (item 16), chronological oldest-year-first.
        if (!empty($years)) {
            $sortedYears = collect($years)->sort()->values();
            $hiresPerMonth = collect();
            foreach ($sortedYears as $year) {
                foreach ($monthLabels as $i => $label) {
                    $count = $hiringBase()
                        ->where('job_applications.application_status', 'hired')
                        ->whereYear('job_applications.hired_at', $year)
                        ->whereMonth('job_applications.hired_at', $i + 1)
                        ->count();
                    $hiresPerMonth->put("$label $year", $count);
                }
            }
        } else {
            $hiresPerMonth = collect(range($hireMonths - 1, 0))->mapWithKeys(function ($i) use ($hiringBase) {
                $month = now()->subMonths($i);
                $count = $hiringBase()
                    ->where('job_applications.application_status', 'hired')
                    ->whereYear('job_applications.hired_at', $month->year)
                    ->whereMonth('job_applications.hired_at', $month->month)
                    ->count();
                return [$month->format('M Y') => $count];
            });
        }

        $topHiringCompanies = $hiringBase()
            ->where('job_applications.application_status', 'hired')
            ->select('job_postings.job_posting_company', DB::raw('count(*) as hires'))
            ->groupBy('job_postings.job_posting_company')
            ->orderByDesc('hires')
            ->limit($topCompaniesLimit)
            ->get();

        // Registered vs pending/unregistered companies — Year-scoped by
        // registration date (created_at) when the Year filter is set.
        $registeredCompaniesQuery = Employer::with(['user', 'industry'])->where('employer_approved', true);
        $pendingCompaniesQuery = Employer::with(['user', 'industry'])->where('employer_approved', false);
        if (!empty($years)) {
            $registeredCompaniesQuery->whereIn(DB::raw('YEAR(created_at)'), $years);
            $pendingCompaniesQuery->whereIn(DB::raw('YEAR(created_at)'), $years);
        }
        $registeredCompanies = $registeredCompaniesQuery->latest('created_at')->get();
        $pendingCompanies = $pendingCompaniesQuery->latest('created_at')->get();

        return compact(
            'totalAlumni', 'employedCount', 'employmentRate', 'unemploymentRate', 'employmentByBatch', 'employmentByMonth', 'industryDistribution',
            'genderEmployment', 'programAlignment', 'alignmentRate', 'employmentInterval',
            'beforeGraduationCount', 'beforeGraduationRate', 'internshipCount', 'internshipRate', 'beforeGraduationInternshipCount', 'jobBeforeGradPie',
            'employedAlumniTable', 'totalApplications', 'totalHired', 'hiresPerMonth', 'topHiringCompanies',
            'registeredCompanies', 'pendingCompanies'
        );
    }
}
