@php
    $current_page = 'reports';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Placement & Hiring Report | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; padding: 18px 22px 30px; flex: 1; }
        .section-heading { font-size: 20px; font-weight: bold; }
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
                <div class="mb-4 flex items-center gap-3">
                    <a href="{{ route('superAdmin.dashboard') }}" class="text-slate-400 hover:text-[#C73D1A]"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
                    <span class="section-heading bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Job Placement &amp; Hiring Report</span>
                </div>

                @include('partials.report-filters-form', [
                    'formAction' => route('reports.placement'),
                    'clearRoute' => route('reports.placement'),
                    'dashboardFilters' => $dashboardFilters,
                    'batchYearOptions' => $batchYearOptions,
                    'programs' => $programs,
                    'yearOptions' => $yearOptions,
                ])

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 my-4">
                    <div class="stat-card">
                        <div class="s-label">Job Placement Rate</div>
                        <div class="s-value">{{ $stats['jobPlacementRate'] }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Total Applications</div>
                        <div class="s-value">{{ $r['totalApplications'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Total Hired</div>
                        <div class="s-value">{{ $r['totalHired'] }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Active Job Postings</div>
                        <div class="s-value">{{ $stats['activeJobs'] }}</div>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="card-title">Top Hiring Companies</div>
                            <div class="card-sub">Most hires among current filters</div>
                        </div>
                        <select id="topCompaniesSelect" onchange="updateTopCompanies(this.value)"
                            style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:12px; font-family:'Montserrat',sans-serif;">
                            <option value="5" {{ $topCompaniesLimit === 5 ? 'selected' : '' }}>Top 5</option>
                            <option value="10" {{ $topCompaniesLimit === 10 ? 'selected' : '' }}>Top 10</option>
                            <option value="15" {{ $topCompaniesLimit === 15 ? 'selected' : '' }}>Top 15</option>
                            <option value="20" {{ $topCompaniesLimit === 20 ? 'selected' : '' }}>Top 20</option>
                        </select>
                    </div>
                    <div style="margin-top:10px;">
                        @forelse ($r['topHiringCompanies'] as $row)
                        @php $maxHires = $r['topHiringCompanies']->max('hires') ?: 1; @endphp
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:7px; font-size:10px;">
                            <span style="width:200px; flex-shrink:0; color:#374151;">{{ $row->job_posting_company }}</span>
                            <div style="flex:1; background:#f1f5f9; border-radius:4px; height:7px;"><div style="height:7px; border-radius:4px; background:#e05c00; width:{{ round($row->hires / $maxHires * 100) }}%"></div></div>
                            <span style="width:26px; text-align:right;">{{ $row->hires }}</span>
                        </div>
                        @empty
                        <p style="font-size:11px;color:#9ca3af;">No hires recorded yet for the current filters.</p>
                        @endforelse
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="card-title">Hires per Month</div>
                            <div class="card-sub">Date an applicant was actually marked hired, grouped by month</div>
                        </div>
                        <select id="hiresRangeSelect" onchange="updateHiresRange(this.value)"
                            style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:12px; font-family:'Montserrat',sans-serif;">
                            <option value="3" {{ $hireMonths === 3 ? 'selected' : '' }}>Last 3 months</option>
                            <option value="6" {{ $hireMonths === 6 ? 'selected' : '' }}>Last 6 months</option>
                            <option value="12" {{ $hireMonths === 12 ? 'selected' : '' }}>Last 12 months</option>
                            <option value="24" {{ $hireMonths === 24 ? 'selected' : '' }}>Last 24 months</option>
                        </select>
                    </div>
                    <div style="margin-top:10px; height:220px;"><canvas id="chartHires"></canvas></div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('reports.exportCsv', array_merge(['group' => 'placement'], array_filter($dashboardFilters), ['hire_months' => $hireMonths, 'top_companies' => $topCompaniesLimit])) }}"
                        class="text-xs font-bold text-slate-500 hover:text-[#C73D1A] flex items-center gap-1">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
                    </a>
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

        const hiresPerMonth = @json($r['hiresPerMonth']);
        new Chart(document.getElementById('chartHires'), {
            type: 'line',
            data: { labels: Object.keys(hiresPerMonth), datasets: [{ label: 'Hires', data: Object.values(hiresPerMonth), borderColor: '#e05c00', backgroundColor: 'rgba(224,92,0,.08)', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { maxRotation: 60, autoSkip: true, maxTicksLimit: 12 } }, y: { ticks: { precision: 0 } } } }
        });

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

        const EXPORTABLE_CHART_IDS = ['chartHires'];
        function exportPdfWithCharts() {
            const charts = {};
            EXPORTABLE_CHART_IDS.forEach(id => {
                const canvas = document.getElementById(id);
                if (canvas) { try { charts[id] = canvas.toDataURL('image/png'); } catch (e) {} }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = {!! json_encode(route('reports.exportPdf.post', ['group' => 'placement'])) !!};
            form.style.display = 'none';
            const addField = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = value; form.appendChild(i); };
            addField('_token', '{{ csrf_token() }}');
            addField('charts', JSON.stringify(charts));
            addField('hire_months', document.getElementById('hiresRangeSelect')?.value || '{{ $hireMonths }}');
            addField('top_companies', document.getElementById('topCompaniesSelect')?.value || '{{ $topCompaniesLimit }}');
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
