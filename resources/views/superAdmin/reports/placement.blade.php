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
                    'formAction' => route('reports.placement'),
                    'clearRoute' => route('reports.placement'),
                    'dashboardFilters' => $dashboardFilters,
                    'batchYearOptions' => $batchYearOptions,
                    'programs' => $programs,
                    'yearOptions' => $yearOptions,
                    'exportCsvUrl' => route('reports.exportCsv', array_merge(['group' => 'placement'], array_filter($dashboardFilters), ['hire_months' => $hireMonths, 'top_companies' => $topCompaniesLimit])),
                ])

                <div class="mb-4 mt-3 flex items-center">
                    <a href="{{ route('superAdmin.dashboard') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Return to Dashboard
                    </a>
                </div>

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
                    <div class="report-table-scroll">
                        <table class="report-table">
                            <tr><th>Company</th><th>Hires</th></tr>
                            @forelse ($r['topHiringCompanies'] as $row)
                            <tr><td>{{ $row->job_posting_company }}</td><td>{{ $row->hires }}</td></tr>
                            @empty
                            <tr><td colspan="2" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
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
                    <div class="report-table-scroll">
                        <table class="report-table">
                            <tr><th>Month</th><th>Hires</th></tr>
                            @forelse ($r['hiresPerMonth'] as $month => $count)
                            <tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>
                            @empty
                            <tr><td colspan="2" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                {{-- Raw, one-row-per-application listing — the actual records
                     both charts above were computed from. Landing here from a
                     specific dashboard chart (see dashboard.blade.php's
                     goToReport() calls) pre-sorts this by that chart's own
                     dimension via ?sort=; the admin can still re-sort by any
                     column or search by applicant name afterward. --}}
                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="card-title">Applications Report</div>
                            <div class="card-sub">Every application in the current filters &mdash; click a column to sort, or search by applicant</div>
                        </div>
                        <div class="relative">
                            <i data-lucide="search" style="width:12px;height:12px;position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                            <input type="text" id="applicationTableSearch" placeholder="Search by applicant..." oninput="filterApplicationReportTable()"
                                style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px 5px 26px; font-size:11px; font-family:'Montserrat',sans-serif;">
                        </div>
                    </div>
                    <div class="report-table-scroll" style="max-height:400px;">
                        <table class="report-table" id="applicationReportTable">
                            <thead>
                                <tr>
                                    <th data-sort data-sort-key="applicant">Applicant <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="company">Company <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="position">Position <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="applied_date">Applied Date <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="status">Status <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                </tr>
                            </thead>
                            <tbody id="applicationReportTbody">
                                @forelse ($r['applicationsTable'] as $row)
                                <tr data-name="{{ strtolower($row->applicant_name) }}">
                                    <td>{{ $row->applicant_name }}</td>
                                    <td>{{ $row->company }}</td>
                                    <td>{{ $row->position }}</td>
                                    <td data-sort-value="{{ $row->applied_date }}">{{ \Carbon\Carbon::parse($row->applied_date)->format('M d, Y') }}</td>
                                    <td>{{ ucfirst($row->status) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" style="color:#9ca3af;">No applications match the current filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        @include('partials.table-pagination-bar', [
                            'id' => 'applicationReportTable',
                            'mode' => 'client',
                            'rowSelector' => '#applicationReportTbody tr[data-name]',
                            'totalItems' => $r['applicationsTable']->count(),
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

        const hiresPerMonth = @json($r['hiresPerMonth']);
        new Chart(document.getElementById('chartHires'), {
            type: 'line',
            data: { labels: Object.keys(hiresPerMonth), datasets: [{ label: 'Hires', data: Object.values(hiresPerMonth), borderColor: '#e05c00', backgroundColor: 'rgba(224,92,0,.08)', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { maxRotation: 60, autoSkip: true, maxTicksLimit: 12 } }, y: { ticks: { precision: 0 } } } }
        });

        // Applications Report table — applicant-name search (combines with
        // whatever column sort table-sort.blade.php's click handler already
        // applied; that script only ever reorders <tr>s, it never hides
        // them, so this is free to layer visibility on top independently).
        function filterApplicationReportTable() {
            const q = document.getElementById('applicationTableSearch').value.trim().toLowerCase();
            document.querySelectorAll('#applicationReportTbody tr[data-name]').forEach(row => {
                row.style.display = (!q || row.dataset.name.includes(q)) ? '' : 'none';
            });
            document.dispatchEvent(new CustomEvent('pv:filtered'));
        }

        // Auto-sorts the Applications Report table to match whichever
        // dashboard chart was clicked to land here — e.g. ?sort=company
        // (from "Top Hiring Companies") or ?sort=status,applied_date (from
        // "Hires per Month"). Same stable-sort-composition trick as the
        // Employment report's Alumni Report table: simulate clicks on each
        // requested key in reverse order.
        (function () {
            const sortParam = new URLSearchParams(window.location.search).get('sort');
            if (!sortParam) return;
            const keys = sortParam.split(',').reverse();
            keys.forEach(key => {
                document.querySelector('#applicationReportTable th[data-sort-key="' + key.trim() + '"]')?.click();
            });
        })();

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
