@php
    $current_page = 'reports';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni ID & Yearbook Report | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; padding: 18px 22px 30px; flex: 1; }
        .stat-card { background: #fff; border-radius: 10px; padding: 16px 18px 14px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .stat-card .s-label { font-size: 12.5px; font-weight: 600; color: #0E0F3B; }
        .stat-card .s-value { font-size: 30px; font-weight: 700; color: #C73D1A; line-height: 1; margin-top: 8px; }
        .chart-card { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-title { font-size: 13px; font-weight: 700; background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block; }
        .card-sub { font-size: 10.5px; color: #000; margin-top: 2px; }
        table.report-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.report-table th, table.report-table td { border-bottom: 1px solid #f1f5f9; padding: 6px 8px; text-align: left; }
        table.report-table th { color: #0E0F3B; font-weight: 700; background: #f8fafc; }
        .report-table-scroll { max-height: 400px; overflow-y: auto; }
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
                <div class="w-full bg-slate-100 pb-4">
                    <form method="GET" action="{{ route('reports.alumniid') }}" id="dashboardFilterForm"
                        class="flex flex-wrap items-center gap-3 bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-md text-sm font-medium text-slate-700">

                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'batch',
                                'icon' => 'fas fa-calendar',
                                'placeholder' => 'Batch Year',
                                'options' => $batchYearOptions->mapWithKeys(fn ($y) => [$y => $y]),
                                'selected' => $dashboardFilters['batch'],
                            ])
                        </div>

                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'college',
                                'icon' => 'fas fa-university',
                                'placeholder' => 'College',
                                'options' => \App\Models\Program::COLLEGES,
                                'selected' => $dashboardFilters['college'],
                            ])
                        </div>

                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'program_id',
                                'icon' => 'fas fa-graduation-cap',
                                'placeholder' => 'Course',
                                'options' => $programs->pluck('program_name', 'program_id'),
                                'selected' => $dashboardFilters['program_id'],
                                'optionAttrs' => $programs->mapWithKeys(fn ($p) => [$p->program_id => 'data-college="' . e($p->college) . '"']),
                            ])
                        </div>

                        <div class="w-full flex items-center justify-end gap-3 pt-3 mt-1 border-t border-slate-100">
                            @if (array_filter($dashboardFilters))
                            <a href="{{ route('reports.alumniid') }}"
                                class="shrink-0 whitespace-nowrap bg-slate-200 text-[10px] text-slate-600 px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-slate-300 transition shadow-sm font-semibold uppercase tracking-wide">
                                <i data-lucide="x" class="w-3.5 h-3.5 shrink-0"></i> Clear Filters
                            </a>
                            @endif

                            <button type="submit"
                                class="shrink-0 whitespace-nowrap bg-[#1D264F] hover:bg-[#0E0F3B] text-[10px] text-white px-4 py-1.5 rounded-md flex items-center gap-1 transition shadow-sm font-semibold uppercase tracking-wide">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>

                            <button type="button" onclick="exportPdfWithCharts()"
                                class="shrink-0 whitespace-nowrap bg-[#C04828] text-[10px] text-white px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#A03D22] transition shadow-sm font-semibold uppercase tracking-wide">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 shrink-0"></i> EXPORT PDF
                            </button>

                            <a href="{{ route('reports.exportCsv', array_merge(['group' => 'alumniid'], array_filter($dashboardFilters))) }}"
                                class="shrink-0 whitespace-nowrap border border-[#0E0F3B] text-[#0E0F3B] text-[10px] px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#0E0F3B] hover:text-white transition shadow-sm font-semibold uppercase tracking-wide">
                                <i data-lucide="download" class="w-3.5 h-3.5 shrink-0"></i> EXPORT CSV
                            </a>
                        </div>
                    </form>
                </div>

                <div class="mb-4 flex items-center">
                    <a href="{{ route('superAdmin.dashboard') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Return to Dashboard
                    </a>
                </div>

                @php
                    $alumniIdRegistered = $r['alumniIdCounts']->sum();
                    $alumniIdTotal = $r['alumniIdTotal'];
                @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 my-4">
                    <div class="stat-card">
                        <div class="s-label">Total Alumni (filtered)</div>
                        <div class="s-value">{{ number_format($alumniIdTotal) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Registered for ID</div>
                        <div class="s-value">{{ $alumniIdTotal > 0 ? round($alumniIdRegistered / $alumniIdTotal * 100) : 0 }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">IDs Claimed</div>
                        <div class="s-value">{{ $alumniIdTotal > 0 ? round($r['alumniIdCounts']['claimed'] / $alumniIdTotal * 100) : 0 }}%</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Yearbooks Claimed</div>
                        <div class="s-value">{{ $alumniIdTotal > 0 ? round($r['yearbookCounts']['claimed'] / $alumniIdTotal * 100) : 0 }}%</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Alumni ID Status</div>
                        <div class="card-sub">Claim-process stage of every registered alumnus (current filters)</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartAlumniID"></canvas></div>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Yearbook Claiming Status</div>
                        <div class="card-sub">Claim-process stage of every yearbook record (current filters)</div>
                        <div style="margin-top:10px; height:220px;"><canvas id="chartYearbook"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Alumni ID Status by Batch</div>
                        <table class="report-table">
                            <tr><th>Batch</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
                            @forelse ($r['alumniIdByBatch'] as $batch => $row)
                            <tr><td>{{ $batch }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
                            @empty
                            <tr><td colspan="5" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Yearbook Status by Batch</div>
                        <table class="report-table">
                            <tr><th>Batch</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
                            @forelse ($r['yearbookByBatch'] as $batch => $row)
                            <tr><td>{{ $batch }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
                            @empty
                            <tr><td colspan="5" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="chart-card">
                        <div class="card-title">Alumni ID Status by Program</div>
                        <table class="report-table">
                            <tr><th>Program</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
                            @forelse ($r['alumniIdByProgram'] as $program => $row)
                            <tr><td>{{ $program }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
                            @empty
                            <tr><td colspan="5" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                    <div class="chart-card">
                        <div class="card-title">Yearbook Status by Program</div>
                        <table class="report-table">
                            <tr><th>Program</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
                            @forelse ($r['yearbookByProgram'] as $program => $row)
                            <tr><td>{{ $program }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
                            @empty
                            <tr><td colspan="5" style="color:#9ca3af;">No records for the current filters.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                {{-- Raw, one-row-per-alumnus listing — the actual records the
                     status charts/breakdowns above were computed from.
                     Landing here from a specific dashboard chart (see
                     dashboard.blade.php's goToReport() calls) pre-sorts this
                     by that chart's own status column via ?sort=; the admin
                     can still re-sort by any column or search by name after. --}}
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
                    <div class="report-table-scroll">
                        <table class="report-table" id="alumniReportTable">
                            <thead>
                                <tr>
                                    <th data-sort data-sort-key="name">Name <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="batch">Batch <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="id_status">Alumni ID Status <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    <th data-sort data-sort-key="yearbook_status">Yearbook Status <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                </tr>
                            </thead>
                            <tbody id="alumniReportTbody">
                                @forelse ($r['allAlumniWithClaimStatus'] as $a)
                                @php $alumName = trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')); @endphp
                                <tr data-name="{{ strtolower($alumName) }}">
                                    <td>{{ $alumName }}</td>
                                    <td>{{ optional($a->alumnus_batch)->format('Y') }}</td>
                                    <td>{{ $a->alumniId ? ucwords(str_replace('_', ' ', $a->alumniId->status)) : 'Not Registered' }}</td>
                                    <td>{{ $a->yearbook ? ucwords(str_replace('_', ' ', $a->yearbook->claiming_status)) : 'Not Registered' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" style="color:#9ca3af;">No alumni match the current filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        @include('partials.table-pagination-bar', [
                            'id' => 'alumniReportTable',
                            'mode' => 'client',
                            'rowSelector' => '#alumniReportTbody tr[data-name]',
                            'totalItems' => $r['allAlumniWithClaimStatus']->count(),
                        ])
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        const fontDef = { family: 'Montserrat', size: 10 };
        Chart.defaults.font = fontDef;
        Chart.defaults.color = '#6b7280';

        const doughnutOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: 'Montserrat', size: 10 }, boxWidth: 10 } } },
            cutout: '55%',
        };

        new Chart(document.getElementById('chartAlumniID'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Ready to Claim', 'Claimed'],
                datasets: [{
                    data: @json(array_values($r['alumniIdCounts']->all())),
                    backgroundColor: ['#dc2626', '#e05c00', '#16a34a'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: doughnutOptions,
        });

        new Chart(document.getElementById('chartYearbook'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Ready to Claim', 'Claimed'],
                datasets: [{
                    data: @json(array_values($r['yearbookCounts']->all())),
                    backgroundColor: ['#dc2626', '#e05c00', '#16a34a'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: doughnutOptions,
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
        // chart was clicked to land here — e.g. ?sort=id_status (from
        // "Alumni ID Status") or ?sort=yearbook_status (from "Yearbook
        // Claiming Status"). Same stable-sort-composition trick as the
        // Employment report's Alumni Report table.
        (function () {
            const sortParam = new URLSearchParams(window.location.search).get('sort');
            if (!sortParam) return;
            const keys = sortParam.split(',').reverse();
            keys.forEach(key => {
                document.querySelector('#alumniReportTable th[data-sort-key="' + key.trim() + '"]')?.click();
            });
        })();

        const EXPORTABLE_CHART_IDS = ['chartAlumniID', 'chartYearbook'];
        function exportPdfWithCharts() {
            const charts = {};
            EXPORTABLE_CHART_IDS.forEach(id => {
                const canvas = document.getElementById(id);
                if (canvas) { try { charts[id] = canvas.toDataURL('image/png'); } catch (e) {} }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = {!! json_encode(route('reports.exportPdf.post', ['group' => 'alumniid'])) !!};
            form.style.display = 'none';
            const addField = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = value; form.appendChild(i); };
            addField('_token', '{{ csrf_token() }}');
            addField('charts', JSON.stringify(charts));
            const params = new URLSearchParams(window.location.search);
            ['batch', 'program_id', 'college'].forEach(key => {
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
