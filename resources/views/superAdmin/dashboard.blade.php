@php
    $current_page = 'dashboard';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-text {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        #sidebar:hover .sidebar-text {
            opacity: 1;
            pointer-events: auto;
        }

        /* ── Scrollable content area ── */
        .dash-scroll {
            overflow-y: auto;
            padding: 18px 22px 30px;
            flex: 1;
        }

        /* ── Filter bar ── */
        .filter-bar {
            background: #fff;
            border-radius: 10px;
            padding: 11px 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .filter-bar label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
        }

        .filter-bar input,
        .filter-bar select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 12px;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            color: #374151;
            min-width: 120px;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: #e05c00;
        }

        .btn-export {
            background: #c0392b;
            color: #fff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 7px 14px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            white-space: nowrap;
            margin-left: auto;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-export:hover {
            background: #a93226;
        }

        /* ── Section heading ── */
        .section-heading {
            font-size: 20px;
            font-weight: bold;
        }

        .section-heading span {
            color: #e05c00;
        }

        /* ── Stat Cards ── */
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 18px 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        }

        .stat-card .s-top {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-card .s-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #0E0F3B;
            flex: 1;
        }

        .stat-badge {
            font-size: 11px;
            font-weight: 600;
        }

        .badge-up {
            color: #16a34a;
        }

        .badge-down {
            color: #dc2626;
        }

        .badge-flat {
            color: #6b7280;
        }

        .stat-card .s-value {
            font-size: 30px;
            font-weight: 700;
            color: #C73D1A;
            line-height: 1;
            margin-top: 8px;
        }

        .stat-card .s-icon {
            color: #0E0F3B;
        }

        /* ── Chart Cards ── */
        .chart-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        }

        .card-title {
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;

        }

        .card-sub {
            font-size: 10.5px;
            color: #000000;
            margin-top: 2px;
        }

        /* ── Clickable graphs — each chart links to its detailed,
           filterable, exportable breakdown page. Scoped to just the
           canvas's own wrapper (not the whole chart-card), so cards with
           their own controls (a <select>, a search box) keep working
           normally instead of navigating away on every click. ── */
        .chart-clickable {
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.15s;
        }
        .chart-clickable:hover {
            background: rgba(199, 61, 26, 0.05);
        }

        /* ── Industry bars ── */
        .ind-bar-wrap {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-top: 10px;
        }

        .ind-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ind-label {
            font-size: 10px;
            color: #374151;
            width: 155px;
            flex-shrink: 0;
        }

        .ind-track {
            flex: 1;
            background: #f1f5f9;
            border-radius: 4px;
            height: 7px;
        }

        .ind-fill {
            height: 7px;
            border-radius: 4px;
            background: #e05c00;
        }

        .ind-pct {
            font-size: 10px;
            color: #374151;
            width: 26px;
            text-align: right;
        }

        /* ── Recent Activity ── */
        .activity-section-label {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin: 12px 0 4px;
        }

        .activity-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f1f5f9;
            padding: 8px 0;
            font-size: 12px;
            color: #374151;
        }

        .btn-edit {
            background: #e05c00;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-family: 'Montserrat', sans-serif;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            /* IE / Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">

        @include('partials.super-admin-side-bar')

        <main class="flex-1 flex flex-col overflow-hidden">

            @include('partials.super-admin-header')

            <!-- ════════════ DASHBOARD CONTENT ════════════ -->
            @include('partials.success')
            <div class="dash-scroll">
                <!-- Filter Section Container -->
                @include('partials.report-filters-form', [
                    'formAction' => route('superAdmin.dashboard'),
                    'clearRoute' => route('superAdmin.dashboard'),
                    'dashboardFilters' => $dashboardFilters,
                    'batchYearOptions' => $batchYearOptions,
                    'programs' => $programs,
                    'yearOptions' => $yearOptions,
                ])

                <!-- System Overview Heading -->
                <div class="mb-2 ml-1 flex items-center justify-between">
                    <span
                        class="section-heading bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Overview</span>
                    <a href="{{ route('reports.employment') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        View Detailed Report <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                                <path d="M6 12v5c3 3 9 3 12 0v-5" />
                            </svg>
                            <span class="s-label">Total Alumni Users</span>
                            <!-- <span class="stat-badge badge-up">▲ +12.5%</span> -->
                        </div>
                        <div class="s-value">{{ $stats['alumniUsers']}}</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <span class="s-label">Employment Rate</span>
                        </div>
                        <div class="s-value">{{ $employmentRate }}%</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <span class="s-label">Unemployment Rate</span>
                        </div>
                        <div class="s-value">{{ $unemploymentRate }}%</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span class="s-label">Industry Partners</span>
                            <!--<span class="stat-badge badge-up">▲ +8.9%</span>-->
                        </div>
                        <div class="s-value">{{ $stats['industryPartners'] }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                <line x1="12" y1="12" x2="12" y2="16" />
                                <line x1="10" y1="14" x2="14" y2="14" />
                            </svg>
                            <span class="s-label">Active Job Postings</span>
                            <!--<span class="stat-badge badge-down">▼ -3.1%</span>-->
                        </div>
                        <div class="s-value">{{ $stats['activeJobs'] }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                                <circle cx="12" cy="12" r="2" />
                                <path d="M6 12H4M20 12h-2" />
                            </svg>
                            <span class="s-label">Job Placement Rate</span>
                            <!--<span class="stat-badge badge-flat">▶ +0.0%</span>-->
                        </div>
                        <div class="s-value">{{ $stats['jobPlacementRate'] }}%</div>
                    </div>

                </div>

                <!-- Row 2: 4 chart cards -->
                <div class="grid grid-cols-2 gap-4 mb-4">

                    <!-- Employment Status Breakdown -->
                    <div class="chart-card">
                        <div class="card-title">Employment Status Breakdown</div>
                        <div class="card-sub">Overall distribution of all alumni and their employment status</div>
                        <div class="chart-clickable" style="margin-top:10px; height:160px;" onclick="window.location.href='{{ route('reports.employment') }}'">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>

                    <!-- Industry Distribution -->
                    <div class="chart-card">
                        <div class="card-title">Industry Distribution of <span>Employed Alumni</span></div>
                        <div class="card-sub">Sectors where PLV alumni are currently employed</div>
                        <div class="ind-bar-wrap">
                            @forelse ($industryDistribution as $industryName => $count)
                            @php $pct = $employedCount > 0 ? round($count / $employedCount * 100) : 0; @endphp
                            <div class="ind-row"><span class="ind-label">{{ $industryName }}</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:{{ $pct }}%"></div>
                                </div><span class="ind-pct">{{ $pct }}%</span>
                            </div>
                            @empty
                            <p style="font-size:11px;color:#9ca3af;">No employed alumni match the current filters.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Row 3: 3 chart cards -->
                <div class="grid grid-cols-2 gap-4 mb-4">

                    <!-- Employment Rate by Batch/Year -->
                    <div class="chart-card">
                        <div class="card-title">Employment Rate by Batch/Year</div>
                        <div class="card-sub">Employed vs. unemployed counts across graduation batches</div>
                        <div class="chart-clickable" style="margin-top:10px; height:160px;" onclick="window.location.href='{{ route('reports.employment') }}'">
                            <canvas id="chartPlacement"></canvas>
                        </div>
                    </div>


                    <!-- Job-to-Degree Alignment -->
                    <div class="chart-card">
                        <div class="card-title">Job-to-Degree Alignment by Program/Course</div>
                        <div class="card-sub">% of employed alumni whose job matches their degree field ({{ $alignmentRate }}% overall) &mdash; every program</div>
                        <div class="chart-clickable" style="margin-top:10px; height:220px; overflow-y:auto;" onclick="window.location.href='{{ route('reports.employment') }}'">
                            <div style="height:{{ max(160, $programAlignment->count() * 26) }}px;">
                                <canvas id="chartAlignment"></canvas>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Row 3b: Gender + Employment Interval -->
                <div class="grid grid-cols-2 gap-4 mb-4">

                    <!-- Employment Rate by Gender -->
                    <div class="chart-card">
                        <div class="card-title">Employment Rate by Gender</div>
                        <div class="card-sub">Employed vs. total alumni per gender</div>
                        <div class="chart-clickable" style="margin-top:10px; height:160px;" onclick="window.location.href='{{ route('reports.employment') }}'">
                            <canvas id="chartGender"></canvas>
                        </div>
                    </div>

                    <!-- Employment Interval -->
                    <div class="chart-card">
                        <div class="card-title">Employment Interval</div>
                        <div class="card-sub">Time from graduation to first job (alumni with a recorded first-job date)</div>
                        <div class="chart-clickable" style="margin-top:10px; height:160px;" onclick="window.location.href='{{ route('reports.employment') }}'">
                            <canvas id="chartInterval"></canvas>
                        </div>
                    </div>

                </div>

                <!-- Job Before Graduation & Internships -->
                <div class="chart-card mb-4">
                    <div class="card-title">Job Before Graduation &amp; Internships</div>
                    <div class="card-sub">Of alumni with a recorded first job</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 items-center">
                        <div class="chart-clickable" style="height:180px;" onclick="window.location.href='{{ route('reports.employment') }}'">
                            <canvas id="chartJobBeforeGrad"></canvas>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-xs"><span class="text-slate-500">Employed Before Graduation</span><span class="font-bold text-[#0E0F3B]">{{ $beforeGraduationCount }} <span style="color:#9ca3af;">({{ $beforeGraduationRate }}%)</span></span></div>
                            <div class="flex justify-between text-xs"><span class="text-slate-500">First Job Was an Internship</span><span class="font-bold text-[#0E0F3B]">{{ $internshipCount }} <span style="color:#9ca3af;">({{ $internshipRate }}%)</span></span></div>
                            <div class="flex justify-between text-xs"><span class="text-slate-500">Before Graduation &amp; an Internship</span><span class="font-bold text-[#0E0F3B]">{{ $beforeGraduationInternshipCount }}</span></div>
                        </div>
                    </div>
                </div>

                <!-- Employment by Month -->
                @php
                    // Reflects the Batch/Course filters actually applied to
                    // this chart's data (see buildEmploymentReports() —
                    // $employmentByMonth is built from the same
                    // batch/program-filtered $allAlumni as every other
                    // report here) — this used to just say "all years"
                    // unconditionally even with a Batch filter applied,
                    // which read as if the filter wasn't being respected.
                    // "All years" still refers to employment years pooled
                    // together, not graduation years — those alumni can
                    // have been hired anywhere from graduation to now.
                    $selectedProgramNames = $programs->whereIn('program_id', $dashboardFilters['program_id'])->pluck('program_name');
                    $selectedYears = !empty($dashboardFilters['year']) ? $dashboardFilters['year'] : null;
                @endphp
                <div class="chart-card mb-4">
                    <div class="card-title">Employment by Month</div>
                    <div class="card-sub">Which month alumni get employed (Jan&ndash;Dec{{ $selectedYears ? ', ' . implode(', ', $selectedYears) : ', pooled across employment years' }}) for
                        {{ !empty($dashboardFilters['batch']) ? implode(', ', $dashboardFilters['batch']) . ' batch' : 'all batches' }}
                        @if ($selectedProgramNames->isNotEmpty())
                            &middot; {{ $selectedProgramNames->implode(', ') }}
                        @endif
                        &mdash; includes employment recorded from a system hire and employment alumni added themselves on their profile</div>
                    <div class="chart-clickable" style="margin-top:10px; height:160px;" onclick="window.location.href='{{ route('reports.employment') }}'">
                        <canvas id="chartEmploymentByMonth"></canvas>
                    </div>
                </div>

                <!-- System Overview Heading -->
                <div class="mb-2 ml-1 mt-6 flex items-center justify-between">
                    <span
                        class="section-heading bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Job
                        Placement &amp; Hiring</span>
                    <a href="{{ route('reports.placement') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        View Detailed Report <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div class="stat-card">
                        <div class="s-top">
                            <span class="s-label">Total Applications</span>
                        </div>
                        <div class="s-value">{{ $totalApplications }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-top">
                            <span class="s-label">Total Hired</span>
                        </div>
                        <div class="s-value">{{ $totalHired }}</div>
                    </div>
                    <div class="chart-card" style="grid-column: span 2;">
                        <div class="flex items-center justify-between gap-3" style="flex-wrap:wrap;">
                            <div>
                                <div class="card-title">Top Hiring Companies</div>
                                <div class="card-sub">Most hires among current filters</div>
                            </div>
                            <select id="topCompaniesSelect" onchange="updateTopCompanies(this.value)"
                                style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:12px; font-family:'Montserrat',sans-serif; outline:none; color:#374151;">
                                <option value="5" {{ $topCompaniesLimit === 5 ? 'selected' : '' }}>Top 5</option>
                                <option value="10" {{ $topCompaniesLimit === 10 ? 'selected' : '' }}>Top 10</option>
                                <option value="15" {{ $topCompaniesLimit === 15 ? 'selected' : '' }}>Top 15</option>
                                <option value="20" {{ $topCompaniesLimit === 20 ? 'selected' : '' }}>Top 20</option>
                            </select>
                        </div>
                        <div class="ind-bar-wrap">
                            @forelse ($topHiringCompanies as $row)
                            @php $maxHires = $topHiringCompanies->max('hires') ?: 1; @endphp
                            <div class="ind-row"><span class="ind-label">{{ $row->job_posting_company }}</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:{{ round($row->hires / $maxHires * 100) }}%"></div>
                                </div><span class="ind-pct">{{ $row->hires }}</span>
                            </div>
                            @empty
                            <p style="font-size:11px;color:#9ca3af;">No hires recorded yet for the current filters.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3" style="flex-wrap:wrap;">
                        <div>
                            <div class="card-title">Hires per Month</div>
                            <div class="card-sub">Date an applicant was actually marked hired, grouped by month</div>
                        </div>
                        <select id="hiresRangeSelect" onchange="updateHiresRange(this.value)"
                            style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:12px; font-family:'Montserrat',sans-serif; outline:none; color:#374151;">
                            <option value="3" {{ $hireMonths === 3 ? 'selected' : '' }}>Last 3 months</option>
                            <option value="6" {{ $hireMonths === 6 ? 'selected' : '' }}>Last 6 months</option>
                            <option value="12" {{ $hireMonths === 12 ? 'selected' : '' }}>Last 12 months</option>
                            <option value="24" {{ $hireMonths === 24 ? 'selected' : '' }}>Last 24 months</option>
                        </select>
                    </div>
                    <div class="chart-clickable" style="margin-top:10px; height:160px;" onclick="window.location.href='{{ route('reports.placement') }}'">
                        <canvas id="chartHires"></canvas>
                    </div>
                </div>

                <!-- Row 4: Alumni ID Reports + Recent Activity -->
                <div class="grid grid-cols-3 gap-4">
                     <!-- Networking Activity -->
                    <div class="chart-card">
                        <div class="card-title">Alumni <span>Networking Activity</span></div>
                        <div class="card-sub">Monthly connections & messages — Jan to Jun 2025</div>
                        <div style="margin-top:10px; height:130px;">
                            <canvas id="chartNetworking"></canvas>
                        </div>
                    </div>

                    <!-- Alumni ID & Yearbook Reports -->
                    <div class="chart-card">
                        <div class="card-title">Alumni ID & Yearbook Reports</div>
                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:6px;">Alumni ID
                                    Status</div>
                                <div style="height:130px;"><canvas id="chartAlumniID"></canvas></div>
                                <div style="font-size:9.5px;color:#6b7280;margin-top:8px;line-height:1.7;">
                                    @php $alumniIdRegistered = $alumniIdCounts->sum(); @endphp
                                    Registered for ID: <strong>{{ number_format($alumniIdRegistered) }} ({{ $alumniIdTotal > 0 ? round($alumniIdRegistered / $alumniIdTotal * 100) : 0 }}%)</strong><br>
                                    IDs Claimed: <strong>{{ number_format($alumniIdCounts['claimed']) }} ({{ $alumniIdTotal > 0 ? round($alumniIdCounts['claimed'] / $alumniIdTotal * 100) : 0 }}%)</strong>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:6px;">Yearbook
                                    Distribution</div>
                                <div style="height:130px;"><canvas id="chartYearbook"></canvas></div>
                                <div style="font-size:9.5px;color:#6b7280;margin-top:8px;line-height:1.7;">
                                    @php $yearbookPending = $yearbookCounts['pending'] + $yearbookCounts['ready_to_claim']; @endphp
                                    Claimed: <strong>{{ number_format($yearbookCounts['claimed']) }} ({{ $alumniIdTotal > 0 ? round($yearbookCounts['claimed'] / $alumniIdTotal * 100) : 0 }}%)</strong><br>
                                    Pending: <strong>{{ number_format($yearbookPending) }} ({{ $alumniIdTotal > 0 ? round($yearbookPending / $alumniIdTotal * 100) : 0 }}%)</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity & Updates -->
                    <div class="chart-card">
                        <div class="card-title">Recent Activity & Updates</div>
                        <div class="activity-section-label">Latest Event Posted:</div>
                        @if ($latestEvent)
                        <div class="activity-row">
                            <span>{{ $latestEvent->title }}</span>
                            <div class="flex items-center gap-3">
                                <span style="font-size:11px;color:#6b7280;">{{ $latestEvent->event_datetime->format('m-d-Y') }}</span>
                                <a href="{{ route('notices.management') }}?notice={{ $latestEvent->id }}" class="btn-edit" style="display:inline-block;text-decoration:none;">Edit</a>
                            </div>
                        </div>
                        @else
                        <div class="activity-row" style="color:#9ca3af;font-style:italic;font-size:11px;">
                            <span>No events posted yet</span>
                        </div>
                        @endif
                        <div class="activity-section-label">Recent Directory Updates:</div>
                        @forelse ($recentProfileUpdates as $updatedAlumnus)
                        <div class="activity-row">
                            <span>{{ trim($updatedAlumnus->user->user_first_name . ' ' . $updatedAlumnus->user->user_last_name) }} (Profile Updated)</span>
                        </div>
                        @empty
                        <div class="activity-row"
                            style="color:#9ca3af;font-style:italic;font-size:11px;border-top:1px solid #f1f5f9;">
                            <span>No recent updates</span>
                        </div>
                        @endforelse
                    </div>

                </div>

            </div>
            <!-- ════════════ END DASHBOARD CONTENT ════════════ -->

        </main>
    </div>


    <script>
    lucide.createIcons();

    // ── Item 18: EXPORT PDF now captures every real Chart.js canvas as a
    // PNG and POSTs it alongside the currently-applied filters, so the PDF
    // shows actual graphs instead of re-deriving everything as tables. The
    // 3 decorative canvases below (chartNetworking/chartAlumniID/
    // chartYearbook) are never wired to real data and are deliberately
    // excluded — same reasoning the CSV export has always applied to them. ──
    const EXPORTABLE_CHART_IDS = ['chartStatus', 'chartPlacement', 'chartAlignment', 'chartGender', 'chartInterval', 'chartJobBeforeGrad', 'chartEmploymentByMonth', 'chartHires'];

    function exportPdfWithCharts() {
        const charts = {};
        EXPORTABLE_CHART_IDS.forEach(id => {
            const canvas = document.getElementById(id);
            if (canvas) {
                try { charts[id] = canvas.toDataURL('image/png'); } catch (e) { /* tainted/unsupported canvas — falls back to a table for this one */ }
            }
        });

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = {!! json_encode(route('superAdmin.dashboard.exportPdf.post')) !!};
        form.style.display = 'none';

        const addField = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        };

        addField('_token', '{{ csrf_token() }}');
        addField('charts', JSON.stringify(charts));
        addField('hire_months', document.getElementById('hiresRangeSelect')?.value || '{{ $hireMonths }}');
        addField('top_companies', document.getElementById('topCompaniesSelect')?.value || '{{ $topCompaniesLimit }}');

        // Every currently-APPLIED filter (already reflected in the URL after
        // the last "Apply Filters" submit) — not any unsaved, still-staged
        // checkbox state — matching how the rest of the page's filters work.
        const params = new URLSearchParams(window.location.search);
        ['batch', 'program_id', 'employment_status', 'college', 'year'].forEach(key => {
            params.getAll(key + '[]').forEach(v => addField(key + '[]', v));
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();
    }

    // Preserves whatever's already in the URL (batch/program_id/employment_status)
    // and just swaps hire_months, so changing the range never clears the
    // rest of the dashboard's filters.
    function updateHiresRange(months) {
        const url = new URL(window.location.href);
        url.searchParams.set('hire_months', months);
        window.location.href = url.toString();
    }

    function updateTopCompanies(count) {
        const url = new URL(window.location.href);
        url.searchParams.set('top_companies', count);
        window.location.href = url.toString();
    }

    const fontDef = { family: 'Montserrat', size: 10 };
    const gridColor = 'rgba(0,0,0,0.05)';
    Chart.defaults.font = fontDef;
    Chart.defaults.color = '#6b7280';

    // 1. Networking Activity — dual line
    new Chart(document.getElementById('chartNetworking'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Connections',
                    data: [120, 180, 160, 230, 290, 340],
                    borderColor: '#e05c00',
                    backgroundColor: 'rgba(224,92,0,.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Messages',
                    data: [200, 250, 220, 300, 380, 450],
                    borderColor: '#1a3a6e',
                    backgroundColor: 'rgba(26,58,110,.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { font: fontDef, boxWidth: 10 }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { font: fontDef } },
                y: { grid: { color: gridColor }, ticks: { font: fontDef } }
            }
        }
    });

    // 2. Employment Rate by Batch — stacked bar (real data)
    const batchLabels = @json($employmentByBatch->keys());
    const batchEmployed = @json($employmentByBatch->pluck('employed')->values());
    const batchUnemployed = @json($employmentByBatch->map(fn ($b) => $b['total'] - $b['employed'])->values());
    new Chart(document.getElementById('chartPlacement'), {
        type: 'bar',
        data: {
            labels: batchLabels,
            datasets: [
                {
                    label: 'Unemployed',
                    data: batchUnemployed,
                    backgroundColor: '#e05c00'
                },
                {
                    label: 'Employed',
                    data: batchEmployed,
                    backgroundColor: '#1a3a6e'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { font: fontDef, boxWidth: 10 }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: fontDef } },
                y: { stacked: true, grid: { color: gridColor }, ticks: { font: fontDef, precision: 0 } }
            }
        }
    });

    // 3. Employment Status Breakdown — doughnut (real data)
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Employed', 'Unemployed'],
            datasets: [{
                data: [{{ $employedCount }}, {{ $totalAlumni - $employedCount }}],
                backgroundColor: ['#1a3a6e', '#94a3b8'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { font: fontDef, boxWidth: 10 }
                }
            },
            cutout: '60%'
        }
    });

    // 4. Job-to-Degree Alignment — horizontal bar (every program; card scrolls if the list is tall)
    const alignmentEntries = @json($programAlignment);
    new Chart(document.getElementById('chartAlignment'), {
        type: 'bar',
        data: {
            labels: Object.keys(alignmentEntries),
            datasets: [{
                label: 'Alignment %',
                data: Object.values(alignmentEntries).map(p => p.rate),
                backgroundColor: '#e05c00',
                borderRadius: 3
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: gridColor },
                    max: 100,
                    ticks: { font: fontDef, callback: v => v + '%' }
                },
                y: { grid: { display: false }, ticks: { font: { family: 'Montserrat', size: 8 } } }
            }
        }
    });

    // 4b. Employment Rate by Gender — bar
    const genderEntries = @json($genderEmployment);
    new Chart(document.getElementById('chartGender'), {
        type: 'bar',
        data: {
            labels: genderEntries.map(g => g.label),
            datasets: [{
                label: 'Employment Rate',
                data: genderEntries.map(g => g.rate),
                backgroundColor: ['#e05c00', '#1a3a6e', '#94a3b8'],
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: fontDef } },
                y: { grid: { color: gridColor }, max: 100, ticks: { font: fontDef, callback: v => v + '%' } }
            }
        }
    });

    // 4c. Employment Interval — bar
    const intervalData = @json($employmentInterval);
    new Chart(document.getElementById('chartInterval'), {
        type: 'bar',
        data: {
            labels: Object.keys(intervalData),
            datasets: [{
                label: 'Alumni',
                data: Object.values(intervalData),
                backgroundColor: '#1a3a6e',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: fontDef } },
                y: { grid: { color: gridColor }, ticks: { font: fontDef, precision: 0 } }
            }
        }
    });

    // 4c-1. Job Before Graduation & Internships — pie (4 mutually-exclusive buckets, see DashboardReportService::buildEmploymentReports())
    const jobBeforeGradPie = @json($jobBeforeGradPie);
    new Chart(document.getElementById('chartJobBeforeGrad'), {
        type: 'pie',
        data: {
            labels: Object.keys(jobBeforeGradPie),
            datasets: [{
                data: Object.values(jobBeforeGradPie),
                backgroundColor: ['#C73D1A', '#1a3a6e', '#e05c00', '#cbd5e1'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { font: { family: 'Montserrat', size: 8 }, boxWidth: 8 }
                }
            }
        }
    });

    // 4c-2. Employment by Month — bar (Jan-Dec, alumnus_employment_date, all years pooled)
    const employmentByMonth = @json($employmentByMonth);
    new Chart(document.getElementById('chartEmploymentByMonth'), {
        type: 'bar',
        data: {
            labels: Object.keys(employmentByMonth),
            datasets: [{
                label: 'Alumni Employed',
                data: Object.values(employmentByMonth),
                backgroundColor: '#0e7c66',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: fontDef } },
                y: { grid: { color: gridColor }, ticks: { font: fontDef, precision: 0 } }
            }
        }
    });

    // 4d. Hires per Month — line
    const hiresPerMonth = @json($hiresPerMonth);
    new Chart(document.getElementById('chartHires'), {
        type: 'line',
        data: {
            labels: Object.keys(hiresPerMonth),
            datasets: [{
                label: 'Hires',
                data: Object.values(hiresPerMonth),
                borderColor: '#e05c00',
                backgroundColor: 'rgba(224,92,0,.08)',
                borderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { font: fontDef, maxRotation: 60, minRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
                y: { grid: { color: gridColor }, ticks: { font: fontDef, precision: 0 } }
            }
        }
    });

    // 5. Alumni ID Status — doughnut (pie)
    new Chart(document.getElementById('chartAlumniID'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Ready to Claim', 'Claimed'],
            datasets: [{
                data: {!! json_encode([$alumniIdCounts['pending'], $alumniIdCounts['ready_to_claim'], $alumniIdCounts['claimed']]) !!},
                backgroundColor: ['#dc2626', '#e05c00', '#16a34a'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: { family: 'Montserrat', size: 9 },
                        boxWidth: 8
                    }
                }
            },
            cutout: '55%'
        }
    });

    // 6. Yearbook Distribution — doughnut (pie)
    new Chart(document.getElementById('chartYearbook'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Ready to Claim', 'Claimed'],
            datasets: [{
                data: {!! json_encode([$yearbookCounts['pending'], $yearbookCounts['ready_to_claim'], $yearbookCounts['claimed']]) !!},
                backgroundColor: ['#dc2626', '#e05c00', '#16a34a'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: { family: 'Montserrat', size: 9 },
                        boxWidth: 8
                    }
                }
            },
            cutout: '55%'
        }
    });
</script>

@include('partials.staged-multiselect')
@include('partials.report-college-cascade')

</body>

</html>
