<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Alumnus;
use App\Services\DashboardReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Item 19 (+17) — grouped report-detail pages. Per the user's own call on
 * how to group them (not 13 bespoke pages, not 1 generic template): reports
 * that share the same underlying data source get one page each. All three
 * reuse DashboardReportService — the exact same query-building code behind
 * the main dashboard — so a detail page's numbers can never drift from the
 * summary card that links to it.
 *
 * - employment  (Group A): the alumni-cohort half of buildEmploymentReports().
 * - placement   (Group B): the job-placement/hiring half.
 * - companies   (Group C): Employed Alumni Report + Registered/Pending
 *   Companies — this group's page IS the new sidebar page item 17 asked
 *   for, not a separate page built twice.
 * - alumniid    (Group D): Alumni ID & Yearbook claim-status breakdown.
 * - networking  (Group E): monthly conversations/messages activity.
 *
 * Gated behind Office::PERMISSIONS['reports'] like every other admin
 * section — existing `admin` accounts won't have it until a super_admin
 * grants it from User Management.
 */
class ReportController extends Controller
{
    private const GROUPS = ['employment', 'placement', 'companies', 'alumniid', 'networking'];

    private function authorizeReports(): void
    {
        abort_unless(Auth::check() && Auth::user()->canAccessAdminFeature('reports'), 403);
    }

    /** @return array{0: array, 1: array, 2: array, 3: array, 4: array} [$batches, $programIds, $employmentStatuses, $colleges, $years] */
    private function resolveFilters(DashboardReportService $s): array
    {
        return [
            $s->resolveIntArray(request()->input('batch')),
            $s->resolveIntArray(request()->input('program_id')),
            $s->resolveEmploymentStatuses(request()->input('employment_status')),
            $s->resolveColleges(request()->input('college')),
            $s->resolveYears(request()->input('year')),
        ];
    }

    private function filterOptions(DashboardReportService $reportService): array
    {
        return [
            'batchYearOptions' => Alumnus::whereNotNull('alumnus_batch')
                ->selectRaw('DISTINCT YEAR(alumnus_batch) as year')
                ->orderByDesc('year')
                ->pluck('year'),
            'programs' => Program::orderBy('program_name')->get(),
            'yearOptions' => $reportService->yearOptions(),
        ];
    }

    /**
     * One report, one page (per the user's explicit correction — the
     * grouped pages above bundle several charts onto one page; every
     * dashboard chart now instead links here to ITS OWN page: the chart
     * itself, then a raw one-row-per-record table of exactly what that
     * chart was computed from, pre-sorted by that chart's own dimension via
     * $defaultSort (primary key first — the view reverses it before
     * simulating clicks, since the shared column-sorter is a stable
     * single-column sort and two stable sorts compose into a multi-level
     * one). Filters/search/export all still work same as the grouped pages.
     */
    private const SINGLE_REPORTS = [
        'employment-status', 'employment-by-batch', 'employment-by-gender', 'employment-interval',
        'job-alignment', 'employment-by-month', 'industry-distribution', 'job-before-grad',
        'hires-per-month', 'top-hiring-companies',
        'alumni-id-status', 'yearbook-status',
    ];

    private const ALUMNI_FAMILY_REPORTS = [
        'employment-status', 'employment-by-batch', 'employment-by-gender', 'employment-interval',
        'job-alignment', 'employment-by-month', 'industry-distribution', 'job-before-grad',
    ];

    private const APPLICATION_FAMILY_REPORTS = ['hires-per-month', 'top-hiring-companies'];

    public function show(string $report, DashboardReportService $reportService)
    {
        $this->authorizeReports();
        abort_unless(in_array($report, self::SINGLE_REPORTS, true), 404);

        return view('superAdmin.reports.single', $this->buildSingleReport($report, $reportService));
    }

    /** Generic CSV export — works for any single report since $def's shape (chart + tableColumns/tableRows) is the same regardless of family. */
    public function showExportCsv(string $report, DashboardReportService $reportService)
    {
        $this->authorizeReports();
        abort_unless(in_array($report, self::SINGLE_REPORTS, true), 404);
        $def = $this->buildSingleReport($report, $reportService);

        $callback = function () use ($def) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['PLV-AlumNet — ' . $def['title']]);
            fputcsv($out, ['Generated', now()->format('M d, Y h:i A')]);
            fputcsv($out, []);

            fputcsv($out, ['CHART DATA']);
            $datasetLabels = collect($def['chart']['datasets'])->map(fn ($ds, $i) => $ds['label'] ?? ('Value ' . ($i + 1)))->all();
            fputcsv($out, array_merge(['Label'], $datasetLabels));
            foreach ($def['chart']['labels'] as $i => $label) {
                $row = [$label];
                foreach ($def['chart']['datasets'] as $ds) {
                    $row[] = $ds['data'][$i] ?? '';
                }
                fputcsv($out, $row);
            }
            fputcsv($out, []);

            fputcsv($out, ['RAW DATA']);
            fputcsv($out, collect($def['tableColumns'])->pluck('label')->all());
            foreach ($def['tableRows'] as $row) {
                fputcsv($out, collect($def['tableColumns'])->map(fn ($c) => $row[$c['key']] ?? '')->all());
            }
            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $report . '_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /** Generic PDF export — the raw table only (no chart image; see single.blade.php). */
    public function showExportPdf(string $report, DashboardReportService $reportService)
    {
        $this->authorizeReports();
        abort_unless(in_array($report, self::SINGLE_REPORTS, true), 404);
        $def = $this->buildSingleReport($report, $reportService);

        $pdf = Pdf::loadView('superAdmin.reports.single-pdf', $def)->setPaper('a4', 'portrait');

        return $pdf->download($report . '_' . now()->format('Y-m-d') . '.pdf');
    }

    private function buildSingleReport(string $report, DashboardReportService $reportService): array
    {
        if (in_array($report, self::ALUMNI_FAMILY_REPORTS, true)) {
            $def = $this->buildAlumniFamilyReport($report, $reportService);
        } elseif (in_array($report, self::APPLICATION_FAMILY_REPORTS, true)) {
            $def = $this->buildApplicationFamilyReport($report, $reportService);
        } else {
            $def = $this->buildClaimFamilyReport($report, $reportService);
        }

        // The single.blade.php page ALSO re-sorts client-side (simulating
        // clicks on the matching <th data-sort>) so re-sorting by hand still
        // works after load, but that's JS-only — CSV/PDF export (and the
        // very first HTML paint, before that script runs) read $tableRows
        // in whatever order the query returned them, which was never
        // actually sorted to match the chart. Sorting it here once, in PHP,
        // makes every output — page, CSV, PDF — agree from the start.
        $def['tableRows'] = $this->sortRowsBy($def['tableRows'], $def['defaultSort']);

        return $def;
    }

    /**
     * Multi-level sort matching the client-side click-simulation's own
     * composition rule: $sortKeys lists the primary key first, so this
     * applies them in REVERSE (least significant first) — PHP's usort() is
     * stable (guaranteed since PHP 8.0), so each later pass's ties preserve
     * the previous pass's order, and the LAST pass (the first key in
     * $sortKeys) ends up as the actual primary sort.
     */
    /** A key prefixed with "-" (e.g. "-hired_date") sorts that pass descending — everything else stays ascending. */
    private function sortRowsBy(array $rows, array $sortKeys): array
    {
        foreach (array_reverse($sortKeys) as $rawKey) {
            $descending = str_starts_with($rawKey, '-');
            $key = $descending ? substr($rawKey, 1) : $rawKey;

            usort($rows, function ($a, $b) use ($key, $descending) {
                $av = $a[$key . '_sort'] ?? $a[$key] ?? '';
                $bv = $b[$key . '_sort'] ?? $b[$key] ?? '';
                $cmp = (is_numeric($av) && is_numeric($bv)) ? ($av <=> $bv) : strcasecmp((string) $av, (string) $bv);

                return $descending ? -$cmp : $cmp;
            });
        }

        return array_values($rows);
    }

    private function buildAlumniFamilyReport(string $report, DashboardReportService $reportService): array
    {
        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'employment_status' => $employmentStatuses, 'college' => $colleges, 'year' => $years];
        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years);

        $titles = [
            'employment-status' => 'Employment Status Breakdown',
            'employment-by-batch' => 'Employment Rate by Batch/Year',
            'employment-by-gender' => 'Employment Rate by Gender',
            'employment-interval' => 'Employment Interval',
            'job-alignment' => 'Job-to-Degree Alignment by Program',
            'employment-by-month' => 'Employment by Month',
            'industry-distribution' => 'Industry Distribution of Employed Alumni',
            'job-before-grad' => 'Job Before Graduation & Internships',
        ];

        $chart = match ($report) {
            'employment-status' => [
                'type' => 'doughnut',
                'labels' => ['Employed', 'Unemployed'],
                'datasets' => [['label' => 'Alumni', 'data' => [$r['employedCount'], $r['totalAlumni'] - $r['employedCount']], 'backgroundColor' => ['#1a3a6e', '#94a3b8'], 'borderWidth' => 2, 'borderColor' => '#fff']],
            ],
            'employment-by-batch' => [
                'type' => 'bar',
                'labels' => $r['employmentByBatch']->keys()->values()->all(),
                'datasets' => [
                    ['label' => 'Unemployed', 'data' => $r['employmentByBatch']->map(fn ($b) => $b['total'] - $b['employed'])->values()->all(), 'backgroundColor' => '#e05c00'],
                    ['label' => 'Employed', 'data' => $r['employmentByBatch']->pluck('employed')->values()->all(), 'backgroundColor' => '#1a3a6e'],
                ],
                'stacked' => true,
            ],
            'employment-by-gender' => [
                'type' => 'bar',
                'labels' => collect($r['genderEmployment'])->pluck('label')->all(),
                'datasets' => [['label' => 'Employment Rate', 'data' => collect($r['genderEmployment'])->pluck('rate')->all(), 'backgroundColor' => ['#e05c00', '#1a3a6e', '#94a3b8']]],
                'percent' => true,
            ],
            'employment-interval' => [
                'type' => 'bar',
                'labels' => array_keys($r['employmentInterval']),
                'datasets' => [['label' => 'Alumni', 'data' => array_values($r['employmentInterval']), 'backgroundColor' => '#1a3a6e']],
            ],
            'job-alignment' => [
                'type' => 'bar',
                'labels' => $r['programAlignment']->keys()->values()->all(),
                'datasets' => [['label' => 'Alignment %', 'data' => $r['programAlignment']->map(fn ($p) => $p['rate'])->values()->all(), 'backgroundColor' => '#e05c00']],
                'horizontal' => true,
                'percent' => true,
            ],
            'employment-by-month' => [
                'type' => 'bar',
                'labels' => $r['employmentByMonth']->keys()->values()->all(),
                'datasets' => [['label' => 'Alumni Employed', 'data' => $r['employmentByMonth']->values()->all(), 'backgroundColor' => '#0e7c66']],
            ],
            'industry-distribution' => [
                'type' => 'bar',
                'labels' => $r['industryDistribution']->keys()->values()->all(),
                'datasets' => [['label' => 'Employed', 'data' => $r['industryDistribution']->values()->all(), 'backgroundColor' => '#e05c00']],
                'horizontal' => true,
            ],
            'job-before-grad' => [
                'type' => 'pie',
                'labels' => array_keys($r['jobBeforeGradPie']),
                'datasets' => [['label' => 'Alumni', 'data' => array_values($r['jobBeforeGradPie']), 'backgroundColor' => ['#C73D1A', '#1a3a6e', '#e05c00', '#cbd5e1'], 'borderWidth' => 2, 'borderColor' => '#fff']],
            ],
        };

        // Primary sort key first — buildAlumniTable()'s companion view
        // reverses this before simulating clicks (see the class doc comment
        // above SINGLE_REPORTS).
        $defaultSort = match ($report) {
            'employment-status' => ['employment_status'],
            'employment-by-batch' => ['employment_status', 'batch'],
            'employment-by-gender' => ['gender'],
            'employment-interval' => ['interval'],
            'job-alignment' => ['program'],
            'employment-by-month' => ['employment_month'],
            'industry-distribution' => ['industry'],
            'job-before-grad' => ['first_job_timing'],
        };

        $extraColumn = match ($report) {
            'employment-by-gender' => 'gender',
            'employment-interval' => 'interval',
            'job-alignment' => 'aligned',
            'employment-by-month' => 'employment_month',
            'industry-distribution' => 'industry',
            'job-before-grad' => 'first_job_timing',
            default => null,
        };
        [$tableColumns, $tableRows] = $this->buildAlumniTable($r['allAlumni'], $extraColumn);

        return array_merge([
            'report' => $report,
            'title' => $titles[$report],
            'chart' => $chart,
            'tableColumns' => $tableColumns,
            'tableRows' => $tableRows,
            'defaultSort' => $defaultSort,
            'dashboardFilters' => $dashboardFilters,
            'filterSet' => 'full',
            'hireMonths' => null,
            'topCompaniesLimit' => null,
        ], $this->filterOptions($reportService));
    }

    /** Name/Batch/College/Program/Employment Status — the same raw alumni table shape for every report in ALUMNI_FAMILY_REPORTS, plus one report-specific extra column when the chart's own dimension isn't already one of those five. */
    private function buildAlumniTable($allAlumni, ?string $extraColumn): array
    {
        $columns = [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'batch', 'label' => 'Batch'],
            ['key' => 'college', 'label' => 'College'],
            ['key' => 'program', 'label' => 'Program'],
            ['key' => 'employment_status', 'label' => 'Employment Status'],
        ];
        $extraLabels = [
            'gender' => 'Gender',
            'interval' => 'Employment Interval',
            'aligned' => 'Aligned',
            'employment_month' => 'Employment Month',
            'industry' => 'Industry',
            'first_job_timing' => 'First Job Timing',
        ];
        if ($extraColumn) {
            $columns[] = ['key' => $extraColumn, 'label' => $extraLabels[$extraColumn]];
        }

        $genderLabels = Alumnus::genderLabels();
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $rows = $allAlumni->map(function ($a) use ($extraColumn, $genderLabels, $monthNames) {
            $row = [
                'name' => trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')),
                'batch' => optional($a->alumnus_batch)->format('Y') ?: '',
                'college' => $a->program?->collegeName() ?? 'N/A',
                'program' => $a->program->program_name ?? 'N/A',
                'employment_status' => $a->alumnus_employment_status ? 'Employed' : 'Unemployed',
            ];

            switch ($extraColumn) {
                case 'gender':
                    $row['gender'] = $genderLabels[$a->alumnus_gender] ?? 'Unspecified';
                    break;
                case 'interval':
                    $row['interval'] = $this->intervalBucketFor($a);
                    // Plain alphabetical sort on the label text scrambles
                    // the intended chronological order ("1–2 years" and
                    // "6–12 months" would sort before "Before Graduation",
                    // "Unknown" before "Within 6 months", etc.) — this
                    // numeric key (see buildSingleReport()'s _sort
                    // convention) is what the table actually sorts by.
                    $row['interval_sort'] = array_search($row['interval'], [
                        'Before Graduation', 'Within 6 months', '6–12 months', '1–2 years', 'Over 2 years', 'Unknown',
                    ], true);
                    break;
                case 'aligned':
                    $row['aligned'] = $a->alumnus_employment_status ? ($a->hasCourseAlignedJob() ? 'Yes' : 'No') : 'N/A';
                    break;
                case 'employment_month':
                    $row['employment_month'] = $a->alumnus_employment_date ? $monthNames[$a->alumnus_employment_date->month - 1] : 'N/A';
                    // Numeric sort key — plain alphabetical "Apr" < "Aug" <
                    // "Dec" < "Feb" isn't chronological, so the generic
                    // table view uses this (see single.blade.php's <td
                    // data-sort-value>) instead of the display text.
                    $row['employment_month_sort'] = $a->alumnus_employment_date ? $a->alumnus_employment_date->month : 0;
                    break;
                case 'industry':
                    $row['industry'] = $a->industry->industry_name ?? 'N/A';
                    break;
                case 'first_job_timing':
                    $row['first_job_timing'] = !$a->alumnus_first_job_date ? 'Unknown' : ($a->wasEmployedBeforeGraduation() ? 'Before Graduation' : 'After Graduation');
                    break;
            }

            return $row;
        })->values()->all();

        return [$columns, $rows];
    }

    private function intervalBucketFor($a): string
    {
        if (!$a->alumnus_first_job_date || !$a->alumnus_batch) {
            return 'Unknown';
        }
        if ($a->wasEmployedBeforeGraduation()) {
            return 'Before Graduation';
        }
        $months = max(0, \Carbon\Carbon::parse($a->alumnus_batch)->diffInMonths($a->alumnus_first_job_date, false));

        return match (true) {
            $months <= 6 => 'Within 6 months',
            $months <= 12 => '6–12 months',
            $months <= 24 => '1–2 years',
            default => 'Over 2 years',
        };
    }

    private function buildApplicationFamilyReport(string $report, DashboardReportService $reportService): array
    {
        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'employment_status' => $employmentStatuses, 'college' => $colleges, 'year' => $years];
        $hireMonths = $reportService->resolveHireMonths(request()->input('hire_months'));
        $topCompaniesLimit = $reportService->resolveTopCompaniesLimit(request()->input('top_companies'));
        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years, $hireMonths, $topCompaniesLimit);

        $titles = ['hires-per-month' => 'Hires per Month', 'top-hiring-companies' => 'Top Hiring Companies'];

        $chart = match ($report) {
            'hires-per-month' => [
                'type' => 'line',
                'labels' => $r['hiresPerMonth']->keys()->values()->all(),
                'datasets' => [['label' => 'Hires', 'data' => $r['hiresPerMonth']->values()->all(), 'borderColor' => '#e05c00', 'backgroundColor' => 'rgba(224,92,0,.08)', 'fill' => true]],
            ],
            'top-hiring-companies' => [
                'type' => 'bar',
                'labels' => $r['topHiringCompanies']->pluck('job_posting_company')->values()->all(),
                'datasets' => [['label' => 'Hires', 'data' => $r['topHiringCompanies']->pluck('hires')->values()->all(), 'backgroundColor' => '#e05c00']],
                'horizontal' => true,
            ],
        };

        $defaultSort = match ($report) {
            // Latest hired first — the "-" prefix means descending (see
            // sortRowsBy()). This report is specifically about hires, so
            // its raw table is filtered to hired applications only below,
            // where hired_date is always present.
            'hires-per-month' => ['-hired_date'],
            'top-hiring-companies' => ['company'],
        };

        $tableColumns = [
            ['key' => 'applicant', 'label' => 'Applicant'],
            ['key' => 'company', 'label' => 'Company'],
            ['key' => 'position', 'label' => 'Position'],
            ['key' => 'applied_date', 'label' => 'Applied Date'],
            ['key' => 'hired_date', 'label' => 'Hired Date'],
            ['key' => 'status', 'label' => 'Status'],
        ];

        // "Hires per Month" only ever counts hired applications (see
        // hiresPerMonth's own query) — showing pending/declined/shortlisted
        // rows in its raw table would be records that chart never actually
        // reflects, and none of them have a hired_date to sort by anyway.
        $applications = $report === 'hires-per-month'
            ? $r['applicationsTable']->where('status', 'hired')
            : $r['applicationsTable'];

        $tableRows = $applications->map(fn ($row) => [
            'applicant' => $row->applicant_name,
            'company' => $row->company,
            // Ranks by hire count, most hires first (same ranking
            // topHiringCompanies uses), tie-broken alphabetically by company
            // — plain alphabetical alone would ignore hires entirely and
            // not match what "Top Hiring Companies" actually ranks by. Zero-
            // padded so this sorts correctly as a STRING (sortRowsBy()/the
            // client-side sorter both compare non-numeric values as
            // strings) — "-5" vs "-3" would otherwise compare character by
            // character ('3' < '5') and rank backwards from the real
            // hire-count order.
            'company_sort' => sprintf('%06d_%s', 999999 - ($r['companyHireCounts'][$row->company] ?? 0), $row->company),
            'position' => $row->position,
            'applied_date' => \Carbon\Carbon::parse($row->applied_date)->format('M d, Y'),
            'applied_date_sort' => $row->applied_date,
            'hired_date' => $row->hired_at ? \Carbon\Carbon::parse($row->hired_at)->format('M d, Y') : '—',
            'hired_date_sort' => $row->hired_at ?? '',
            'status' => ucfirst($row->status),
        ])->values()->all();

        return array_merge([
            'report' => $report,
            'title' => $titles[$report],
            'chart' => $chart,
            'tableColumns' => $tableColumns,
            'tableRows' => $tableRows,
            'defaultSort' => $defaultSort,
            'dashboardFilters' => $dashboardFilters,
            'filterSet' => 'full',
            'hireMonths' => $hireMonths,
            'topCompaniesLimit' => $topCompaniesLimit,
        ], $this->filterOptions($reportService));
    }

    private function buildClaimFamilyReport(string $report, DashboardReportService $reportService): array
    {
        [$batches, $programIds, $colleges] = $this->alumniIdFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'college' => $colleges];
        $r = $reportService->buildAlumniIdYearbookReport($batches, $programIds, $colleges);

        $titles = ['alumni-id-status' => 'Alumni ID Status', 'yearbook-status' => 'Yearbook Claiming Status'];

        $chart = match ($report) {
            'alumni-id-status' => [
                'type' => 'doughnut',
                'labels' => ['Pending', 'Ready to Claim', 'Claimed'],
                'datasets' => [['label' => 'Alumni', 'data' => array_values($r['alumniIdCounts']->all()), 'backgroundColor' => ['#dc2626', '#e05c00', '#16a34a'], 'borderWidth' => 2, 'borderColor' => '#fff']],
            ],
            'yearbook-status' => [
                'type' => 'doughnut',
                'labels' => ['Pending', 'Ready to Claim', 'Claimed'],
                'datasets' => [['label' => 'Alumni', 'data' => array_values($r['yearbookCounts']->all()), 'backgroundColor' => ['#dc2626', '#e05c00', '#16a34a'], 'borderWidth' => 2, 'borderColor' => '#fff']],
            ],
        };

        $defaultSort = match ($report) {
            'alumni-id-status' => ['id_status'],
            'yearbook-status' => ['yearbook_status'],
        };

        $tableColumns = [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'batch', 'label' => 'Batch'],
            ['key' => 'id_status', 'label' => 'Alumni ID Status'],
            ['key' => 'yearbook_status', 'label' => 'Yearbook Status'],
        ];
        $tableRows = $r['allAlumniWithClaimStatus']->map(fn ($a) => [
            'name' => trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')),
            'batch' => optional($a->alumnus_batch)->format('Y') ?: '',
            'id_status' => $a->alumniId ? ucwords(str_replace('_', ' ', $a->alumniId->status)) : 'Not Registered',
            'yearbook_status' => $a->yearbook ? ucwords(str_replace('_', ' ', $a->yearbook->claiming_status)) : 'Not Registered',
        ])->values()->all();

        return array_merge([
            'report' => $report,
            'title' => $titles[$report],
            'chart' => $chart,
            'tableColumns' => $tableColumns,
            'tableRows' => $tableRows,
            'defaultSort' => $defaultSort,
            'dashboardFilters' => $dashboardFilters,
            'filterSet' => 'basic',
            'hireMonths' => null,
            'topCompaniesLimit' => null,
        ], $this->filterOptions($reportService));
    }

    public function employment(DashboardReportService $reportService)
    {
        $this->authorizeReports();
        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'employment_status' => $employmentStatuses, 'college' => $colleges, 'year' => $years];

        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years);

        return view('superAdmin.reports.employment', array_merge(
            compact('r', 'dashboardFilters'),
            $this->filterOptions($reportService)
        ));
    }

    public function placement(DashboardReportService $reportService)
    {
        $this->authorizeReports();
        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'employment_status' => $employmentStatuses, 'college' => $colleges, 'year' => $years];
        $hireMonths = $reportService->resolveHireMonths(request()->input('hire_months'));
        $topCompaniesLimit = $reportService->resolveTopCompaniesLimit(request()->input('top_companies'));

        $stats = $reportService->buildOverviewStats($batches, $programIds, $employmentStatuses, $colleges, $years);
        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years, $hireMonths, $topCompaniesLimit);

        return view('superAdmin.reports.placement', array_merge(
            compact('r', 'stats', 'dashboardFilters', 'hireMonths', 'topCompaniesLimit'),
            $this->filterOptions($reportService)
        ));
    }

    public function companies(DashboardReportService $reportService)
    {
        $this->authorizeReports();
        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'employment_status' => $employmentStatuses, 'college' => $colleges, 'year' => $years];

        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years);

        return view('superAdmin.reports.companies', array_merge(
            compact('r', 'dashboardFilters'),
            $this->filterOptions($reportService)
        ));
    }

    /** Group D — batch/program/college lens only (no employment-status or year filter; see buildAlumniIdYearbookReport()'s doc comment). */
    public function alumniid(DashboardReportService $reportService)
    {
        $this->authorizeReports();
        [$batches, $programIds, $colleges] = $this->alumniIdFilters($reportService);
        $dashboardFilters = ['batch' => $batches, 'program_id' => $programIds, 'college' => $colleges];

        $r = $reportService->buildAlumniIdYearbookReport($batches, $programIds, $colleges);

        return view('superAdmin.reports.alumniid', array_merge(
            compact('r', 'dashboardFilters'),
            $this->filterOptions($reportService)
        ));
    }

    /** Group E — just a trailing-month range, no cohort filters (see buildNetworkingReport()'s doc comment). */
    public function networking(DashboardReportService $reportService)
    {
        $this->authorizeReports();
        $networkMonths = $reportService->resolveNetworkMonths(request()->input('network_months'));

        $r = $reportService->buildNetworkingReport($networkMonths);

        return view('superAdmin.reports.networking', compact('r', 'networkMonths'));
    }

    private function groupLabels(array $values, \Closure $labelFor): string
    {
        return empty($values) ? 'All' : collect($values)->map($labelFor)->implode(', ');
    }

    private function filterSummary(array $batches, array $programIds, array $employmentStatuses, array $colleges, array $years): array
    {
        return [
            'batchLabel' => $this->groupLabels($batches, fn ($v) => $v),
            'programLabel' => empty($programIds) ? 'All' : Program::whereIn('program_id', $programIds)->pluck('program_name')->implode(', '),
            'statusLabel' => $this->groupLabels($employmentStatuses, fn ($v) => ucfirst($v)),
            'collegeLabel' => $this->groupLabels($colleges, fn ($v) => Program::COLLEGES[$v] ?? $v),
            'yearLabel' => $this->groupLabels($years, fn ($v) => $v),
        ];
    }

    /** One CSV export route for all 5 groups — {group} picks which section of the shared report data to write out. */
    public function exportCsv(string $group, DashboardReportService $reportService)
    {
        $this->authorizeReports();
        abort_unless(in_array($group, self::GROUPS, true), 404);

        if ($group === 'alumniid') {
            return $this->exportAlumniIdCsv($reportService);
        }
        if ($group === 'networking') {
            return $this->exportNetworkingCsv($reportService);
        }

        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $hireMonths = $reportService->resolveHireMonths(request()->input('hire_months'));
        $topCompaniesLimit = $reportService->resolveTopCompaniesLimit(request()->input('top_companies'));
        $labels = $this->filterSummary($batches, $programIds, $employmentStatuses, $colleges, $years);

        $stats = $reportService->buildOverviewStats($batches, $programIds, $employmentStatuses, $colleges, $years);
        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years, $hireMonths, $topCompaniesLimit);

        $titles = ['employment' => 'Employment & Alignment', 'placement' => 'Job Placement & Hiring', 'companies' => 'Alumni & Company Registration'];

        $callback = function () use ($group, $stats, $r, $labels, $hireMonths, $topCompaniesLimit, $titles) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['PLV-AlumNet — ' . $titles[$group] . ' Report Export']);
            fputcsv($out, ['Generated', now()->format('M d, Y h:i A')]);
            fputcsv($out, ['Filters', "Batch: {$labels['batchLabel']} | Program: {$labels['programLabel']} | Employment Status: {$labels['statusLabel']} | College: {$labels['collegeLabel']} | Year: {$labels['yearLabel']}"]);
            fputcsv($out, []);

            if ($group === 'employment') {
                fputcsv($out, ['OVERVIEW']);
                fputcsv($out, ['Employment Rate', $r['employmentRate'] . '%']);
                fputcsv($out, ['Unemployment Rate', $r['unemploymentRate'] . '%']);
                fputcsv($out, []);

                fputcsv($out, ['EMPLOYMENT RATE BY BATCH/YEAR']);
                fputcsv($out, ['Batch', 'Total', 'Employed', 'Rate']);
                foreach ($r['employmentByBatch'] as $batchYear => $row) {
                    fputcsv($out, [$batchYear, $row['total'], $row['employed'], $row['rate'] . '%']);
                }
                fputcsv($out, []);

                fputcsv($out, ['EMPLOYMENT BY MONTH']);
                fputcsv($out, ['Month', 'Alumni Employed']);
                foreach ($r['employmentByMonth'] as $month => $count) {
                    fputcsv($out, [$month, $count]);
                }
                fputcsv($out, []);

                fputcsv($out, ['INDUSTRY DISTRIBUTION OF EMPLOYED ALUMNI']);
                fputcsv($out, ['Industry', 'Employed Count']);
                foreach ($r['industryDistribution'] as $industry => $count) {
                    fputcsv($out, [$industry, $count]);
                }
                fputcsv($out, []);

                fputcsv($out, ['EMPLOYMENT RATE BY GENDER']);
                fputcsv($out, ['Gender', 'Total', 'Employed', 'Rate']);
                foreach ($r['genderEmployment'] as $row) {
                    fputcsv($out, [$row['label'], $row['total'], $row['employed'], $row['rate'] . '%']);
                }
                fputcsv($out, []);

                fputcsv($out, ['JOB-TO-DEGREE ALIGNMENT BY PROGRAM (Overall: ' . $r['alignmentRate'] . '%)']);
                fputcsv($out, ['Program', 'Employed', 'Aligned', 'Rate']);
                foreach ($r['programAlignment'] as $program => $row) {
                    fputcsv($out, [$program, $row['total'], $row['aligned'], $row['rate'] . '%']);
                }
                fputcsv($out, []);

                fputcsv($out, ['EMPLOYMENT INTERVAL (graduation to first job)']);
                fputcsv($out, ['Interval', 'Alumni']);
                foreach ($r['employmentInterval'] as $bucket => $count) {
                    fputcsv($out, [$bucket, $count]);
                }
                fputcsv($out, []);

                fputcsv($out, ['JOB BEFORE GRADUATION & INTERNSHIPS']);
                fputcsv($out, ['Employed Before Graduation', $r['beforeGraduationCount'], $r['beforeGraduationRate'] . '%']);
                fputcsv($out, ['First Job Was an Internship', $r['internshipCount'], $r['internshipRate'] . '%']);
                fputcsv($out, ['Before Graduation AND an Internship', $r['beforeGraduationInternshipCount']]);
            }

            if ($group === 'placement') {
                fputcsv($out, ['OVERVIEW']);
                fputcsv($out, ['Job Placement Rate', $stats['jobPlacementRate'] . '%']);
                fputcsv($out, ['Total Applications', $r['totalApplications']]);
                fputcsv($out, ['Total Hired', $r['totalHired']]);
                fputcsv($out, []);

                fputcsv($out, ["Top $topCompaniesLimit Hiring Companies"]);
                fputcsv($out, ['Company', 'Hires']);
                foreach ($r['topHiringCompanies'] as $row) {
                    fputcsv($out, [$row->job_posting_company, $row->hires]);
                }
                fputcsv($out, []);

                fputcsv($out, ['HIRES PER MONTH']);
                fputcsv($out, ['Month', 'Hires']);
                foreach ($r['hiresPerMonth'] as $month => $count) {
                    fputcsv($out, [$month, $count]);
                }
            }

            if ($group === 'companies') {
                fputcsv($out, ['EMPLOYED ALUMNI REPORT']);
                fputcsv($out, ['Name', 'Batch', 'Program', 'College', 'Workplace', 'Position', 'Industry', 'Employment Date', 'Aligned']);
                foreach ($r['employedAlumniTable'] as $a) {
                    fputcsv($out, [
                        trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')),
                        optional($a->alumnus_batch)->toDateString(),
                        $a->program->program_name ?? 'N/A',
                        $a->program?->collegeName() ?? 'N/A',
                        $a->alumnus_workplace_undisclosed ? 'Undisclosed' : ($a->alumnus_workplace ?? 'N/A'),
                        $a->alumnus_job_position ?? 'N/A',
                        $a->industry->industry_name ?? 'N/A',
                        optional($a->alumnus_employment_date)->format('M d, Y') ?? 'N/A',
                        $a->alumnus_employment_status ? ($a->hasCourseAlignedJob() ? 'Aligned' : 'Not Aligned') : '',
                    ]);
                }
                fputcsv($out, []);

                fputcsv($out, ['REGISTERED COMPANIES (' . $r['registeredCompanies']->count() . ')']);
                fputcsv($out, ['Company', 'Industry', 'Contact']);
                foreach ($r['registeredCompanies'] as $employer) {
                    fputcsv($out, [$employer->employer_company_name, $employer->industry->industry_name ?? 'N/A', $employer->user->user_email ?? 'N/A']);
                }
                fputcsv($out, []);

                fputcsv($out, ['PENDING / UNREGISTERED COMPANIES (' . $r['pendingCompanies']->count() . ')']);
                fputcsv($out, ['Company', 'Industry', 'Contact']);
                foreach ($r['pendingCompanies'] as $employer) {
                    fputcsv($out, [$employer->employer_company_name, $employer->industry->industry_name ?? 'N/A', $employer->user->user_email ?? 'N/A']);
                }
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $group . '_report_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /** One PDF export route for all 5 groups, same GET+POST/chart-image pattern as the main dashboard (item 18). */
    public function exportPdf(string $group, DashboardReportService $reportService)
    {
        $this->authorizeReports();
        abort_unless(in_array($group, self::GROUPS, true), 404);

        if ($group === 'alumniid') {
            return $this->exportAlumniIdPdf($reportService);
        }
        if ($group === 'networking') {
            return $this->exportNetworkingPdf($reportService);
        }

        [$batches, $programIds, $employmentStatuses, $colleges, $years] = $this->resolveFilters($reportService);
        $hireMonths = $reportService->resolveHireMonths(request()->input('hire_months'));
        $topCompaniesLimit = $reportService->resolveTopCompaniesLimit(request()->input('top_companies'));
        $labels = $this->filterSummary($batches, $programIds, $employmentStatuses, $colleges, $years);

        $stats = $reportService->buildOverviewStats($batches, $programIds, $employmentStatuses, $colleges, $years);
        $r = $reportService->buildEmploymentReports($batches, $programIds, $employmentStatuses, $colleges, $years, $hireMonths, $topCompaniesLimit);
        $charts = json_decode((string) request()->input('charts', '{}'), true) ?: [];

        $pdf = Pdf::loadView('superAdmin.reports.report-pdf', array_merge(
            ['group' => $group, 'r' => $r, 'stats' => $stats, 'charts' => $charts, 'hireMonths' => $hireMonths, 'topCompaniesLimit' => $topCompaniesLimit],
            $labels
        ))->setPaper('a4', 'portrait');

        return $pdf->download($group . '_report_' . now()->format('Y-m-d') . '.pdf');
    }

    private function alumniIdFilters(DashboardReportService $reportService): array
    {
        return [
            $reportService->resolveIntArray(request()->input('batch')),
            $reportService->resolveIntArray(request()->input('program_id')),
            $reportService->resolveColleges(request()->input('college')),
        ];
    }

    private function alumniIdFilterSummary(array $batches, array $programIds, array $colleges): array
    {
        return [
            'batchLabel' => $this->groupLabels($batches, fn ($v) => $v),
            'programLabel' => empty($programIds) ? 'All' : Program::whereIn('program_id', $programIds)->pluck('program_name')->implode(', '),
            'collegeLabel' => $this->groupLabels($colleges, fn ($v) => Program::COLLEGES[$v] ?? $v),
        ];
    }

    private function exportAlumniIdCsv(DashboardReportService $reportService)
    {
        [$batches, $programIds, $colleges] = $this->alumniIdFilters($reportService);
        $labels = $this->alumniIdFilterSummary($batches, $programIds, $colleges);
        $r = $reportService->buildAlumniIdYearbookReport($batches, $programIds, $colleges);

        $callback = function () use ($r, $labels) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['PLV-AlumNet — Alumni ID & Yearbook Report Export']);
            fputcsv($out, ['Generated', now()->format('M d, Y h:i A')]);
            fputcsv($out, ['Filters', "Batch: {$labels['batchLabel']} | Program: {$labels['programLabel']} | College: {$labels['collegeLabel']}"]);
            fputcsv($out, []);

            $statusTable = function ($out, string $title, $counts, int $total) {
                fputcsv($out, [$title]);
                fputcsv($out, ['Status', 'Count', 'Percent']);
                foreach ($counts as $status => $count) {
                    fputcsv($out, [ucwords(str_replace('_', ' ', $status)), $count, ($total > 0 ? round($count / $total * 100) : 0) . '%']);
                }
                fputcsv($out, []);
            };
            $statusTable($out, 'ALUMNI ID STATUS', $r['alumniIdCounts'], $r['alumniIdTotal']);
            $statusTable($out, 'YEARBOOK CLAIMING STATUS', $r['yearbookCounts'], $r['alumniIdTotal']);

            $breakdownTable = function ($out, string $title, $rows, string $keyLabel) {
                fputcsv($out, [$title]);
                fputcsv($out, [$keyLabel, 'Total', 'Pending', 'Ready to Claim', 'Claimed']);
                foreach ($rows as $key => $row) {
                    fputcsv($out, [$key, $row['total'], $row['pending'], $row['ready_to_claim'], $row['claimed']]);
                }
                fputcsv($out, []);
            };
            $breakdownTable($out, 'ALUMNI ID STATUS BY BATCH', $r['alumniIdByBatch'], 'Batch');
            $breakdownTable($out, 'YEARBOOK STATUS BY BATCH', $r['yearbookByBatch'], 'Batch');
            $breakdownTable($out, 'ALUMNI ID STATUS BY PROGRAM', $r['alumniIdByProgram'], 'Program');
            $breakdownTable($out, 'YEARBOOK STATUS BY PROGRAM', $r['yearbookByProgram'], 'Program');

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alumniid_report_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function exportNetworkingCsv(DashboardReportService $reportService)
    {
        $networkMonths = $reportService->resolveNetworkMonths(request()->input('network_months'));
        $r = $reportService->buildNetworkingReport($networkMonths);

        $callback = function () use ($r, $networkMonths) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['PLV-AlumNet — Networking Activity Report Export']);
            fputcsv($out, ['Generated', now()->format('M d, Y h:i A')]);
            fputcsv($out, ['Range', "Last {$networkMonths} months"]);
            fputcsv($out, []);

            fputcsv($out, ['OVERVIEW']);
            fputcsv($out, ['Total Conversations', $r['totalConversations']]);
            fputcsv($out, ['Total Messages', $r['totalMessages']]);
            fputcsv($out, []);

            fputcsv($out, ['MONTHLY ACTIVITY']);
            fputcsv($out, ['Month', 'New Conversations', 'Messages Sent']);
            foreach ($r['monthlyConversations'] as $month => $count) {
                fputcsv($out, [$month, $count, $r['monthlyMessages'][$month] ?? 0]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="networking_report_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function exportAlumniIdPdf(DashboardReportService $reportService)
    {
        [$batches, $programIds, $colleges] = $this->alumniIdFilters($reportService);
        $labels = $this->alumniIdFilterSummary($batches, $programIds, $colleges);
        $r = $reportService->buildAlumniIdYearbookReport($batches, $programIds, $colleges);
        $charts = json_decode((string) request()->input('charts', '{}'), true) ?: [];

        $pdf = Pdf::loadView('superAdmin.reports.report-pdf', array_merge(
            ['group' => 'alumniid', 'r' => $r, 'charts' => $charts],
            $labels
        ))->setPaper('a4', 'portrait');

        return $pdf->download('alumniid_report_' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportNetworkingPdf(DashboardReportService $reportService)
    {
        $networkMonths = $reportService->resolveNetworkMonths(request()->input('network_months'));
        $r = $reportService->buildNetworkingReport($networkMonths);
        $charts = json_decode((string) request()->input('charts', '{}'), true) ?: [];

        $pdf = Pdf::loadView('superAdmin.reports.report-pdf', [
            'group' => 'networking', 'r' => $r, 'charts' => $charts, 'networkMonths' => $networkMonths,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('networking_report_' . now()->format('Y-m-d') . '.pdf');
    }
}
