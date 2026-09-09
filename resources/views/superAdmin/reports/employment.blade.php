@php
    $current_page = 'reports';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment & Alignment Report | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; padding: 18px 22px 30px; flex: 1; }
        .section-heading { font-size: 20px; font-weight: bold; }
        .section-heading span { color: #e05c00; }
        .stat-card { background: #fff; border-radius: 10px; padding: 16px 18px 14px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .stat-card .s-label { font-size: 12.5px; font-weight: 600; color: #0E0F3B; }
        .stat-card .s-value { font-size: 30px; font-weight: 700; color: #C73D1A; line-height: 1; margin-top: 8px; }
        .chart-card { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-title { font-size: 13px; font-weight: 700; background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block; }
        .card-sub { font-size: 10.5px; color: #000; margin-top: 2px; }
        table.report-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.report-table th, table.report-table td { border-bottom: 1px solid #f1f5f9; padding: 6px 8px; text-align: left; }
        table.report-table th { color: #0E0F3B; font-weight: 700; background: #f8fafc; }
        .report-table-scroll { max-height: 220px; overflow-y: auto; }
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden">
            @include('partials.super-admin-header')

            <div class="dash-scroll">
                @include('partials.report-filters-form', [
                    'formAction' => route('reports.employment'),
                    'clearRoute' => route('reports.employment'),
                    'dashboardFilters' => $dashboardFilters,
                    'batchYearOptions' => $batchYearOptions,
                    'programs' => $programs,
                    'yearOptions' => $yearOptions,
                    'exportCsvUrl' => route('reports.exportCsv', array_merge(['group' => 'employment'], array_filter($dashboardFilters))),
                ])

                <div class="mb-4 mt-3 flex items-center">
                    <a href="{{ route('superAdmin.dashboard') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Return to Dashboard
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 my-4">
                    <div class="stat-card">
                        <div class="s-label">Employment Rate</div>
                        <div class="s-value">{{ $r['employmentRate'] }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Unemployment Rate</div>
                        <div class="s-value">{{ $r['unemploymentRate'] }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Job-to-Degree Alignment</div>
                        <div class="s-value">{{ $r['alignmentRate'] }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Total Alumni (filtered)</div>
                        <div class="s-value">{{ $r['totalAlumni'] }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Employment Status Breakdown</div>
                        <div class="card-sub">Overall distribution of all alumni and their employment status</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartStatus"></canvas></div>
                        <table class="report-table">
                            <tr><th>Status</th><th>Alumni</th><th>%</th></tr>
                            <tr><td>Employed</td><td>{{ $r['employedCount'] }}</td><td>{{ $r['employmentRate'] }}%</td></tr>
                            <tr><td>Unemployed</td><td>{{ $r['totalAlumni'] - $r['employedCount'] }}</td><td>{{ $r['unemploymentRate'] }}%</td></tr>
                        </table>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Employment Rate by Batch/Year</div>
                        <div class="card-sub">Employed vs. unemployed counts across graduation batches</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartPlacement"></canvas></div>
                        <div class="report-table-scroll">
                            <table class="report-table">
                                <tr><th>Batch</th><th>Total</th><th>Employed</th><th>Unemployed</th><th>Rate</th></tr>
                                @forelse ($r['employmentByBatch'] as $batchYear => $row)
                                <tr><td>{{ $batchYear }}</td><td>{{ $row['total'] }}</td><td>{{ $row['employed'] }}</td><td>{{ $row['total'] - $row['employed'] }}</td><td>{{ $row['rate'] }}%</td></tr>
                                @empty
                                <tr><td colspan="5" style="color:#9ca3af;">No records for the current filters.</td></tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Employment Rate by Gender</div>
                        <div class="card-sub">Employed vs. total alumni per gender</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartGender"></canvas></div>
                        <table class="report-table">
                            <tr><th>Gender</th><th>Total</th><th>Employed</th><th>Rate</th></tr>
                            @forelse ($r['genderEmployment'] as $row)
                            <tr><td>{{ $row['label'] }}</td><td>{{ $row['total'] }}</td><td>{{ $row['employed'] }}</td><td>{{ $row['rate'] }}%</td></tr>
                            @empty
                            <tr><td colspan="4" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Employment Interval</div>
                        <div class="card-sub">Time from graduation to first job</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartInterval"></canvas></div>
                        <table class="report-table">
                            <tr><th>Interval</th><th>Alumni</th></tr>
                            @forelse ($r['employmentInterval'] as $bucket => $count)
                            <tr><td>{{ $bucket }}</td><td>{{ $count }}</td></tr>
                            @empty
                            <tr><td colspan="2" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="card-title">Job-to-Degree Alignment by Program/Course</div>
                    <div class="card-sub">% of employed alumni whose job matches their degree field &mdash; every program</div>
                    <div style="margin-top:10px; height:260px; overflow-y:auto;">
                        <div style="height:{{ max(200, $r['programAlignment']->count() * 26) }}px;"><canvas id="chartAlignment"></canvas></div>
                    </div>
                    <div class="report-table-scroll">
                        <table class="report-table">
                            <tr><th>Program</th><th>Employed</th><th>Aligned</th><th>Rate</th></tr>
                            @forelse ($r['programAlignment'] as $program => $row)
                            <tr><td>{{ $program }}</td><td>{{ $row['total'] }}</td><td>{{ $row['aligned'] }}</td><td>{{ $row['rate'] }}%</td></tr>
                            @empty
                            <tr><td colspan="4" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="card-title">Employment by Month</div>
                    <div class="card-sub">Which month alumni get employed (Jan&ndash;Dec)</div>
                    <div style="margin-top:10px; height:220px;"><canvas id="chartEmploymentByMonth"></canvas></div>
                    <table class="report-table">
                        <tr><th>Month</th><th>Alumni Employed</th></tr>
                        @forelse ($r['employmentByMonth'] as $month => $count)
                        <tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>
                        @empty
                        <tr><td colspan="2" style="color:#9ca3af;">No records for the current filters.</td></tr>
                        @endforelse
                    </table>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Industry Distribution of Employed Alumni</div>
                        <div style="margin-top:10px;">
                            @forelse ($r['industryDistribution'] as $industryName => $count)
                            @php $pct = $r['employedCount'] > 0 ? round($count / $r['employedCount'] * 100) : 0; @endphp
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px; font-size:10px;">
                                <span style="width:155px; flex-shrink:0; color:#374151;">{{ $industryName }}</span>
                                <div style="flex:1; background:#f1f5f9; border-radius:4px; height:7px;"><div style="height:7px; border-radius:4px; background:#e05c00; width:{{ $pct }}%"></div></div>
                                <span style="width:26px; text-align:right;">{{ $pct }}%</span>
                            </div>
                            @empty
                            <p style="font-size:11px;color:#9ca3af;">No employed alumni match the current filters.</p>
                            @endforelse
                        </div>
                        <div class="report-table-scroll">
                            <table class="report-table">
                                <tr><th>Industry</th><th>Employed</th><th>%</th></tr>
                                @forelse ($r['industryDistribution'] as $industryName => $count)
                                <tr><td>{{ $industryName }}</td><td>{{ $count }}</td><td>{{ $r['employedCount'] > 0 ? round($count / $r['employedCount'] * 100) : 0 }}%</td></tr>
                                @empty
                                <tr><td colspan="3" style="color:#9ca3af;">No records for the current filters.</td></tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Job Before Graduation &amp; Internships</div>
                        <div class="card-sub">Of alumni with a recorded first job</div>
                        <div style="margin-top:10px; height:180px;"><canvas id="chartJobBeforeGrad"></canvas></div>
                        <table class="report-table">
                            <tr><th>Measure</th><th>Alumni</th><th>%</th></tr>
                            <tr><td>Employed Before Graduation</td><td>{{ $r['beforeGraduationCount'] }}</td><td>{{ $r['beforeGraduationRate'] }}%</td></tr>
                            <tr><td>First Job Was an Internship</td><td>{{ $r['internshipCount'] }}</td><td>{{ $r['internshipRate'] }}%</td></tr>
                            <tr><td>Before Graduation &amp; an Internship</td><td>{{ $r['beforeGraduationInternshipCount'] }}</td><td>&mdash;</td></tr>
                        </table>
                    </div>
                </div>

                {{-- Raw, one-row-per-alumnus listing — the actual records every
                     chart/table above was computed from. Landing here from a
                     specific dashboard chart (see dashboard.blade.php's
                     goToReport() calls) pre-sorts this by that chart's own
                     dimension via ?sort=; the admin can still re-sort by any
                     column or search by name afterward. --}}
                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="card-title">Alumni Report</div>
                            <div class="card-sub">Every alumnus in the current filters &mdash; click a column to sort, or search by name</div>
                        </div>
                        <div class="relative">
                            <i data-lucide="search" style="width:12px;height:12px;position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                            <input type="text" id="alumniTableSearch" placeholder="Search by name..." oninput="filterAlumniReportTable()"
                                style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px 5px 26px; font-size:11px; font-family:'Montserrat',sans-serif;">
                        </div>
                    </div>
                    <div class="report-table-scroll" style="max-height:400px;">
                        <table class="report-table" id="alumniReportTable">
                            <thead>
                                <tr>
                                    <th data-sort data-sort-key="name">Name <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="batch">Batch <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="college">College <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="program">Program <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="employment_status">Employment Status <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                </tr>
                            </thead>
                            <tbody id="alumniReportTbody">
                                @forelse ($r['allAlumni'] as $a)
                                @php $alumName = trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')); @endphp
                                <tr data-name="{{ strtolower($alumName) }}">
                                    <td>{{ $alumName }}</td>
                                    <td>{{ optional($a->alumnus_batch)->format('Y') }}</td>
                                    <td>{{ $a->program?->collegeName() ?? 'N/A' }}</td>
                                    <td>{{ $a->program->program_name ?? 'N/A' }}</td>
                                    <td>{{ $a->alumnus_employment_status ? 'Employed' : 'Unemployed' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" style="color:#9ca3af;">No alumni match the current filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        @include('partials.table-pagination-bar', [
                            'id' => 'alumniReportTable',
                            'mode' => 'client',
                            'rowSelector' => '#alumniReportTbody tr[data-name]',
                            'totalItems' => $r['allAlumni']->count(),
                        ])
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        const fontDef = { family: 'Montserrat', size: 10 };
        const gridColor = 'rgba(0,0,0,0.05)';
        Chart.defaults.font = fontDef;
        Chart.defaults.color = '#6b7280';

        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: { labels: ['Employed', 'Unemployed'], datasets: [{ data: [{{ $r['employedCount'] }}, {{ $r['totalAlumni'] - $r['employedCount'] }}], backgroundColor: ['#1a3a6e', '#94a3b8'], borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: fontDef, boxWidth: 10 } } }, cutout: '60%' }
        });

        const batchLabels = @json($r['employmentByBatch']->keys());
        const batchEmployed = @json($r['employmentByBatch']->pluck('employed')->values());
        const batchUnemployed = @json($r['employmentByBatch']->map(fn ($b) => $b['total'] - $b['employed'])->values());
        new Chart(document.getElementById('chartPlacement'), {
            type: 'bar',
            data: { labels: batchLabels, datasets: [{ label: 'Unemployed', data: batchUnemployed, backgroundColor: '#e05c00' }, { label: 'Employed', data: batchEmployed, backgroundColor: '#1a3a6e' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: fontDef, boxWidth: 10 } } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { color: gridColor }, ticks: { precision: 0 } } } }
        });

        const genderEntries = @json($r['genderEmployment']);
        new Chart(document.getElementById('chartGender'), {
            type: 'bar',
            data: { labels: genderEntries.map(g => g.label), datasets: [{ label: 'Employment Rate', data: genderEntries.map(g => g.rate), backgroundColor: ['#e05c00', '#1a3a6e', '#94a3b8'], borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { max: 100, ticks: { callback: v => v + '%' } } } }
        });

        const intervalData = @json($r['employmentInterval']);
        new Chart(document.getElementById('chartInterval'), {
            type: 'bar',
            data: { labels: Object.keys(intervalData), datasets: [{ label: 'Alumni', data: Object.values(intervalData), backgroundColor: '#1a3a6e', borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { precision: 0 } } } }
        });

        const jobBeforeGradPie = @json($r['jobBeforeGradPie']);
        new Chart(document.getElementById('chartJobBeforeGrad'), {
            type: 'pie',
            data: {
                labels: Object.keys(jobBeforeGradPie),
                datasets: [{ data: Object.values(jobBeforeGradPie), backgroundColor: ['#C73D1A', '#1a3a6e', '#e05c00', '#cbd5e1'], borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 8 }, boxWidth: 8 } } } }
        });

        const alignmentEntries = @json($r['programAlignment']);
        new Chart(document.getElementById('chartAlignment'), {
            type: 'bar',
            data: { labels: Object.keys(alignmentEntries), datasets: [{ label: 'Alignment %', data: Object.values(alignmentEntries).map(p => p.rate), backgroundColor: '#e05c00', borderRadius: 3 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { max: 100, ticks: { callback: v => v + '%' } }, y: { grid: { display: false }, ticks: { font: { size: 8 } } } } }
        });

        const employmentByMonth = @json($r['employmentByMonth']);
        new Chart(document.getElementById('chartEmploymentByMonth'), {
            type: 'bar',
            data: { labels: Object.keys(employmentByMonth), datasets: [{ label: 'Alumni Employed', data: Object.values(employmentByMonth), backgroundColor: '#0e7c66', borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { precision: 0 } } } }
        });

        // Alumni Report table — name search (combines with whatever column
        // sort table-sort.blade.php's click handler already applied; that
        // script only ever reorders <tr>s, it never hides them, so this is
        // free to layer visibility on top independently).
        function filterAlumniReportTable() {
            const q = document.getElementById('alumniTableSearch').value.trim().toLowerCase();
            document.querySelectorAll('#alumniReportTbody tr[data-name]').forEach(row => {
                row.style.display = (!q || row.dataset.name.includes(q)) ? '' : 'none';
            });
            document.dispatchEvent(new CustomEvent('pv:filtered'));
        }

        // Auto-sorts the Alumni Report table to match whichever dashboard
        // chart was clicked to land here — e.g. ?sort=employment_status (from
        // "Employment Status Breakdown") or ?sort=employment_status,batch
        // (from "Employment Rate by Batch/Year"). The shared column-sorter
        // (partials/table-sort.blade.php, loaded globally via the admin
        // header) is a stable single-column sort triggered by clicking a
        // <th data-sort> — simulating clicks on each requested key in
        // reverse order composes them into the requested multi-level sort
        // (last-clicked key wins as the primary sort).
        (function () {
            const sortParam = new URLSearchParams(window.location.search).get('sort');
            if (!sortParam) return;
            const keys = sortParam.split(',').reverse();
            keys.forEach(key => {
                document.querySelector('#alumniReportTable th[data-sort-key="' + key.trim() + '"]')?.click();
            });
        })();

        // Item 18 — same client-capture-and-POST pattern as the main dashboard.
        const EXPORTABLE_CHART_IDS = ['chartStatus', 'chartPlacement', 'chartGender', 'chartInterval', 'chartJobBeforeGrad', 'chartAlignment', 'chartEmploymentByMonth'];
        function exportPdfWithCharts() {
            const charts = {};
            EXPORTABLE_CHART_IDS.forEach(id => {
                const canvas = document.getElementById(id);
                if (canvas) { try { charts[id] = canvas.toDataURL('image/png'); } catch (e) {} }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = {!! json_encode(route('reports.exportPdf.post', ['group' => 'employment'])) !!};
            form.style.display = 'none';
            const addField = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = value; form.appendChild(i); };
            addField('_token', '{{ csrf_token() }}');
            addField('charts', JSON.stringify(charts));
            const params = new URLSearchParams(window.location.search);
            ['batch', 'program_id', 'employment_status', 'college', 'year'].forEach(key => {
                params.getAll(key + '[]').forEach(v => addField(key + '[]', v));
            });
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }
    </script>

    @include('partials.staged-multiselect')
    @include('partials.report-college-cascade')
</body>

</html>
