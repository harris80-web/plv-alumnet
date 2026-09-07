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
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Employment Rate by Batch/Year</div>
                        <div class="card-sub">Employed vs. unemployed counts across graduation batches</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartPlacement"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Employment Rate by Gender</div>
                        <div class="card-sub">Employed vs. total alumni per gender</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartGender"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Employment Interval</div>
                        <div class="card-sub">Time from graduation to first job</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartInterval"></canvas></div>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="card-title">Job-to-Degree Alignment by Program/Course</div>
                    <div class="card-sub">% of employed alumni whose job matches their degree field &mdash; every program</div>
                    <div style="margin-top:10px; height:260px; overflow-y:auto;">
                        <div style="height:{{ max(200, $r['programAlignment']->count() * 26) }}px;"><canvas id="chartAlignment"></canvas></div>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="card-title">Employment by Month</div>
                    <div class="card-sub">Which month alumni get employed (Jan&ndash;Dec)</div>
                    <div style="margin-top:10px; height:220px;"><canvas id="chartEmploymentByMonth"></canvas></div>
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
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Job Before Graduation &amp; Internships</div>
                        <div class="card-sub">Of alumni with a recorded first job</div>
                        <div style="margin-top:10px; height:180px;"><canvas id="chartJobBeforeGrad"></canvas></div>
                        <div class="grid grid-cols-1 gap-2 mt-3">
                            <div class="flex justify-between text-xs"><span>Employed Before Graduation</span><strong>{{ $r['beforeGraduationCount'] }} ({{ $r['beforeGraduationRate'] }}%)</strong></div>
                            <div class="flex justify-between text-xs"><span>First Job Was an Internship</span><strong>{{ $r['internshipCount'] }} ({{ $r['internshipRate'] }}%)</strong></div>
                            <div class="flex justify-between text-xs"><span>Before Graduation &amp; an Internship</span><strong>{{ $r['beforeGraduationInternshipCount'] }}</strong></div>
                        </div>
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
