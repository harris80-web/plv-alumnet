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
