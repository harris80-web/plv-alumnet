{{--
    One report, one page — every dashboard chart links here to ITS OWN page
    (see ReportController::SINGLE_REPORTS): a raw one-row-per-record table of
    exactly what that report covers, pre-sorted by the report's own
    dimension via $defaultSort (no chart rendered here or in the PDF export
    — table only). Filters, a table search box, column sorting, and CSV/PDF
    export all work regardless of which specific report this is — driven
    entirely by the $tableColumns/$tableRows/$defaultSort/$filterSet props
    ReportController::buildSingleReport() hands this view, so this one file
    covers every report instead of a bespoke page per chart.

    Raw-table format modeled on superAdmin/reports/companies.blade.php's
    Employed Alumni Report — real per-record rows, not aggregated counts.
--}}
@php
    $current_page = 'reports';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; padding: 18px 22px 30px; flex: 1; }
        .chart-card { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-title { font-size: 15px; font-weight: 700; background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block; }
        .card-sub { font-size: 10.5px; color: #000; margin-top: 2px; }
        table.report-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.report-table th, table.report-table td { border-bottom: 1px solid #f1f5f9; padding: 6px 8px; text-align: left; }
        table.report-table th { color: #0E0F3B; font-weight: 700; background: #f8fafc; }
        .report-table-scroll { overflow-y: auto; }
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
                <div class="mb-4 flex items-center">
                    <a href="{{ route('superAdmin.dashboard') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Return to Dashboard
                    </a>
                </div>

                {{-- FILTERS --}}
                <div class="w-full bg-slate-100 pb-4">
                    <form method="GET" action="{{ route('reports.show', $report) }}" id="dashboardFilterForm"
                        class="flex flex-wrap items-center gap-3 bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-md text-sm font-medium text-slate-700">

                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'batch', 'icon' => 'fas fa-calendar', 'placeholder' => 'Batch Year',
                                'options' => $batchYearOptions->mapWithKeys(fn ($y) => [$y => $y]),
                                'selected' => $dashboardFilters['batch'],
                            ])
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'college', 'icon' => 'fas fa-university', 'placeholder' => 'College',
                                'options' => \App\Models\Program::COLLEGES,
                                'selected' => $dashboardFilters['college'],
                            ])
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'program_id', 'icon' => 'fas fa-graduation-cap', 'placeholder' => 'Course',
                                'options' => $programs->pluck('program_name', 'program_id'),
                                'selected' => $dashboardFilters['program_id'],
                                'optionAttrs' => $programs->mapWithKeys(fn ($p) => [$p->program_id => 'data-college="' . e($p->college) . '"']),
                            ])
                        </div>

                        @if ($filterSet === 'full')
                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'employment_status', 'icon' => 'fas fa-user-check', 'placeholder' => 'Employment Status',
                                'options' => ['employed' => 'Employed', 'unemployed' => 'Unemployed'],
                                'selected' => $dashboardFilters['employment_status'],
                            ])
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            @include('partials.multiselect-filter', [
                                'name' => 'year', 'icon' => 'fas fa-calendar-day', 'placeholder' => 'Year',
                                'options' => collect($yearOptions)->mapWithKeys(fn ($y) => [$y => $y]),
                                'selected' => $dashboardFilters['year'],
                            ])
                        </div>
                        @endif

                        @if ($report === 'hires-per-month')
                        <div class="shrink-0 w-40">
                            <select name="hire_months" onchange="this.form.submit()"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                                <option value="3" {{ $hireMonths === 3 ? 'selected' : '' }}>Last 3 months</option>
                                <option value="6" {{ $hireMonths === 6 ? 'selected' : '' }}>Last 6 months</option>
                                <option value="12" {{ $hireMonths === 12 ? 'selected' : '' }}>Last 12 months</option>
                                <option value="24" {{ $hireMonths === 24 ? 'selected' : '' }}>Last 24 months</option>
                            </select>
                        </div>
                        @endif
                        @if ($report === 'top-hiring-companies')
                        <div class="shrink-0 w-40">
                            <select name="top_companies" onchange="this.form.submit()"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                                <option value="5" {{ $topCompaniesLimit === 5 ? 'selected' : '' }}>Top 5</option>
                                <option value="10" {{ $topCompaniesLimit === 10 ? 'selected' : '' }}>Top 10</option>
                                <option value="15" {{ $topCompaniesLimit === 15 ? 'selected' : '' }}>Top 15</option>
                                <option value="20" {{ $topCompaniesLimit === 20 ? 'selected' : '' }}>Top 20</option>
                            </select>
                        </div>
                        @endif

                        <div class="w-full flex items-center justify-end gap-3 pt-3 mt-1 border-t border-slate-100">
                            @if (array_filter($dashboardFilters))
                            <a href="{{ route('reports.show', $report) }}"
                                class="shrink-0 whitespace-nowrap bg-slate-200 text-[10px] text-slate-600 px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-slate-300 transition shadow-sm font-semibold uppercase tracking-wide">
                                <i data-lucide="x" class="w-3.5 h-3.5 shrink-0"></i> Clear Filters
                            </a>
                            @endif

                            <button type="submit"
                                class="shrink-0 whitespace-nowrap bg-[#1D264F] hover:bg-[#0E0F3B] text-[10px] text-white px-4 py-1.5 rounded-md flex items-center gap-1 transition shadow-sm font-semibold uppercase tracking-wide">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>

                            <a href="{{ route('reports.show.exportPdf', array_merge(
                                    ['report' => $report],
                                    array_filter($dashboardFilters),
                                    $report === 'hires-per-month' ? ['hire_months' => $hireMonths] : [],
                                    $report === 'top-hiring-companies' ? ['top_companies' => $topCompaniesLimit] : []
                                )) }}"
                                class="shrink-0 whitespace-nowrap bg-[#C04828] text-[10px] text-white px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#A03D22] transition shadow-sm font-semibold uppercase tracking-wide">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 shrink-0"></i> EXPORT PDF
                            </a>

                            <a href="{{ route('reports.show.exportCsv', array_merge(
                                    ['report' => $report],
                                    array_filter($dashboardFilters),
                                    $report === 'hires-per-month' ? ['hire_months' => $hireMonths] : [],
                                    $report === 'top-hiring-companies' ? ['top_companies' => $topCompaniesLimit] : []
                                )) }}"
                                class="shrink-0 whitespace-nowrap border border-[#0E0F3B] text-[#0E0F3B] text-[10px] px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#0E0F3B] hover:text-white transition shadow-sm font-semibold uppercase tracking-wide">
                                <i data-lucide="download" class="w-3.5 h-3.5 shrink-0"></i> EXPORT CSV
                            </a>
                        </div>
                    </form>
                </div>

                {{-- RAW DATA TABLE --}}
                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="card-title" style="font-size:13px;">{{ $title }}</div>
                            <div class="card-sub">{{ count($tableRows) }} record(s) &mdash; click a column to re-sort, or search</div>
                        </div>
                        <div class="relative">
                            <i data-lucide="search" style="width:12px;height:12px;position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                            <input type="text" id="reportTableSearch" placeholder="Search..." oninput="filterReportTable()"
                                style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px 5px 26px; font-size:11px; font-family:'Montserrat',sans-serif;">
                        </div>
                    </div>
                    <div class="report-table-scroll" style="max-height:450px;">
                        <table class="report-table" id="reportDataTable">
                            <thead>
                                <tr>
                                    @foreach ($tableColumns as $col)
                                    <th data-sort data-sort-key="{{ $col['key'] }}">{{ $col['label'] }} <i data-lucide="chevron-down" class="sort-icon" style="width:9px;height:9px;display:inline-block;"></i></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="reportDataTbody">
                                @forelse ($tableRows as $row)
                                <tr data-search="{{ strtolower(implode(' ', array_map(fn ($c) => (string) ($row[$c['key']] ?? ''), $tableColumns))) }}">
                                    @foreach ($tableColumns as $col)
                                    <td @if(isset($row[$col['key'] . '_sort'])) data-sort-value="{{ $row[$col['key'] . '_sort'] }}" @endif>{{ $row[$col['key']] ?? '' }}</td>
                                    @endforeach
                                </tr>
                                @empty
                                <tr><td colspan="{{ count($tableColumns) }}" style="color:#9ca3af;">No records match the current filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        @include('partials.table-pagination-bar', [
                            'id' => 'reportDataTable',
                            'mode' => 'client',
                            'rowSelector' => '#reportDataTbody tr[data-search]',
                            'totalItems' => count($tableRows),
                        ])
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function filterReportTable() {
            const q = document.getElementById('reportTableSearch').value.trim().toLowerCase();
            document.querySelectorAll('#reportDataTbody tr[data-search]').forEach(row => {
                row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
            });
            document.dispatchEvent(new CustomEvent('pv:filtered'));
        }

        // Pre-sorts the raw table to match the chart it came from —
        // $defaultSort lists the primary key first; reversed here since the
        // shared column-sorter (partials/table-sort.blade.php, loaded
        // globally via the admin header) is a stable single-column sort
        // triggered by clicking a <th data-sort> — two sequential stable
        // sorts (secondary key clicked first) compose into the requested
        // multi-level sort. A key prefixed with "-" (e.g. "-hired_date")
        // wants that column descending — table-sort.blade.php's own click
        // handler toggles asc→desc only on a column's SECOND click (first
        // click is always ascending), so pre-seeding data-sort-dir="asc"
        // right before the (only) simulated click here tricks its toggle
        // into landing on "desc" immediately instead of needing two clicks.
        const DEFAULT_SORT = @json($defaultSort);
        DEFAULT_SORT.slice().reverse().forEach(rawKey => {
            const descending = rawKey.startsWith('-');
            const key = descending ? rawKey.slice(1) : rawKey;
            const th = document.querySelector('#reportDataTable th[data-sort-key="' + key + '"]');
            if (!th) return;
            if (descending) th.dataset.sortDir = 'asc';
            th.click();
        });

    </script>

    @include('partials.staged-multiselect')
    @include('partials.report-college-cascade')
</body>

</html>
