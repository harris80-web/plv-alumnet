@php
    $current_page = 'reports';
    // Which tab renders active on first paint — carried in the query string so
    // "Apply Filters" / "Clear Filters" (both plain GET navigations) come back
    // to the tab the admin was working in instead of resetting to Alumni.
    $activeTab = request()->query('tab') === 'company' ? 'company' : 'alumni';

    // The two tabs' filters travel together, so applying one never drops the other.
    $companyKeys = ['company_search', 'company_industry', 'company_status', 'company_year', 'company_sort'];
    $companyQuery = array_filter(request()->only($companyKeys), fn ($v) => $v !== null && $v !== '' && $v !== []);
    $companyFiltered = (bool) array_diff_key($companyQuery, ['company_sort' => 1]) || ($companyQuery['company_sort'] ?? 'newest') !== 'newest';
    $alumniQuery = request()->only(['batch', 'program_id', 'employment_status', 'college', 'year', \App\Services\DashboardReportService::FILTERS_APPLIED_PARAM]);
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni & Company Registration Reports | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; padding: 18px 22px 30px; flex: 1; }
        .section-heading { font-size: 20px; font-weight: bold; }
        .chart-card { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-title { font-size: 13px; font-weight: 700; background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block; }
        .card-sub { font-size: 10.5px; color: #000; margin-top: 2px; }
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }

        /* Same tab style as superAdmin/jobManagement.blade.php's .page-tab-btn */
        .page-tab-btn {
            border-bottom: 2px solid transparent;
            color: #64748b;
        }

        .page-tab-btn.active {
            border-bottom: 2px solid #ea580c;
            color: #ea580c;
            font-weight: 600;
        }

        .page-tab-btn:hover:not(.active) {
            color: #ea580c;
        }

        /* Company tab: search + sort pills match the multiselect-filter triggers
           (grey border at rest, #C73D1A + soft ring when focused). */
        .company-pill { border-color: #d1d5db; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
        .company-pill:focus { border-color: #C73D1A; box-shadow: 0 0 0 2px rgba(199, 61, 26, 0.15); }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden min-w-0">
            @include('partials.super-admin-header')

            <!-- PAGE TABS -->
            <div class="bg-white px-8 flex gap-8 border-b border-slate-200 shrink-0 shadow-md">
                <button type="button" id="rep-tab-btn-alumni" onclick="repSwitchTab('alumni')"
                    class="page-tab-btn py-3 px-2 flex items-center gap-2 text-sm transition-colors {{ $activeTab === 'alumni' ? 'active' : '' }}">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i> Alumni
                </button>
                <button type="button" id="rep-tab-btn-company" onclick="repSwitchTab('company')"
                    class="page-tab-btn py-3 px-2 flex items-center gap-2 text-sm transition-colors {{ $activeTab === 'company' ? 'active' : '' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i> Company
                </button>
            </div>

            <div class="dash-scroll">
                <!-- <div class="mb-4 flex items-center gap-3">
                    <span class="section-heading bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Alumni &amp; Company Registration Reports</span>
                </div> -->

                <!-- ══ TAB: ALUMNI ══ -->
                <div id="rep-tab-alumni" class="{{ $activeTab === 'alumni' ? '' : 'hidden' }}">
                {{--
                    Alumni cohort filters (Year / Year Graduated / College / Course /
                    Employment Status) — Alumni tab only; they describe alumni, not
                    employers. Still one copy: partials/report-filters-form uses a
                    fixed form id and report-college-cascade looks its dropdowns up
                    with document.querySelector.
                --}}
                @include('partials.report-filters-form', [
                    'formAction' => route('reports.companies'),
                    'clearRoute' => route('reports.companies'),
                    'dashboardFilters' => $dashboardFilters,
                    'batchYearOptions' => $batchYearOptions,
                    'programs' => $programs,
                    'yearOptions' => $yearOptions,
                    'exportCsvUrl' => route('reports.exportCsv', array_merge(['group' => 'companies'], array_filter($dashboardFilters) + [\App\Services\DashboardReportService::FILTERS_APPLIED_PARAM => 1], $companyQuery)),
                ])
                    <div class="chart-card my-4">
                        <div class="flex items-center justify-between mb-3 gap-3">
                            <div>
                                <div class="card-title">Employed Alumni Report</div>
                                <p class="card-sub">
                                    {{ $r['employedAlumniTable']->count() }}
                                    {{ $dashboardFilters['employment_status'] === ['unemployed'] ? 'unemployed' : 'employed' }}
                                    {{ $r['employedAlumniTable']->count() === 1 ? 'alumnus' : 'alumni' }} matching the current filters
                                </p>
                            </div>
                            <div style="position:relative; width:260px;">
                                <input type="text" id="employedAlumniSearch" onkeyup="filterEmployedAlumniRows()"
                                    placeholder="Search by name"
                                    style="width:100%; padding:6px 10px; font-size:12px; border:1px solid #d1d5db; border-radius:6px; font-family:'Montserrat',sans-serif; outline:none;">
                            </div>
                        </div>
                        <div style="overflow-x:auto; overflow-y:auto; max-height:420px;">
                            <table style="width:100%; border-collapse:collapse; font-size:11px;">
                                <thead>
                                    <tr style="background:#0E0F3B; color:#fff; text-align:left; position:sticky; top:0; z-index:1;">
                                        <th style="padding:8px 10px;">Name</th>
                                        <th style="padding:8px 10px;">Batch</th>
                                        <th style="padding:8px 10px;">Program</th>
                                        <th style="padding:8px 10px;">College</th>
                                        <th style="padding:8px 10px;">Workplace</th>
                                        <th style="padding:8px 10px;">Position</th>
                                        <th style="padding:8px 10px;">Industry</th>
                                        <th style="padding:8px 10px;">Employment Date</th>
                                        <th style="padding:8px 10px;">Aligned?</th>
                                    </tr>
                                </thead>
                                <tbody id="employedAlumniTbody">
                                    @forelse ($r['employedAlumniTable'] as $a)
                                    @php $fullName = trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')); @endphp
                                    <tr data-search="{{ mb_strtolower($fullName) }}" style="border-top:1px solid #f1f5f9;">
                                        <td style="padding:7px 10px; font-weight:600; color:#0E0F3B;">{{ $fullName }}</td>
                                        <td style="padding:7px 10px;">{{ optional($a->alumnus_batch)->format('Y') }}</td>
                                        <td style="padding:7px 10px;">{{ $a->program->program_name ?? 'N/A' }}</td>
                                        <td style="padding:7px 10px;">{{ $a->program?->collegeName() ?? 'N/A' }}</td>
                                        <td style="padding:7px 10px;">{{ $a->alumnus_workplace_undisclosed ? 'Undisclosed' : ($a->alumnus_workplace ?? 'N/A') }}</td>
                                        <td style="padding:7px 10px;">{{ $a->alumnus_job_position ?? 'N/A' }}</td>
                                        <td style="padding:7px 10px;">{{ $a->industry->industry_name ?? 'N/A' }}</td>
                                        <td style="padding:7px 10px;">{{ optional($a->alumnus_employment_date)->format('M d, Y') ?? 'N/A' }}</td>
                                        <td style="padding:7px 10px;">
                                            @if ($a->alumnus_employment_status)
                                            <span style="padding:2px 8px; border-radius:999px; font-size:9px; font-weight:700; {{ $a->hasCourseAlignedJob() ? 'background:#dcfce7;color:#16a34a;' : 'background:#f1f5f9;color:#64748b;' }}">
                                                {{ $a->hasCourseAlignedJob() ? 'ALIGNED' : 'NOT ALIGNED' }}
                                            </span>
                                            @else — @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="9" style="padding:24px; text-align:center; color:#9ca3af;">No matching alumni.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <p id="employedAlumniNoResults" class="hidden" style="text-align:center; color:#9ca3af; padding:20px 0; font-size:11px;">No matching alumni.</p>
                        </div>
                    </div>
                </div>

                <!-- ══ TAB: COMPANY ══ -->
                <div id="rep-tab-company" class="{{ $activeTab === 'company' ? '' : 'hidden' }}">
                    {{-- Employer filters: find a specific company by name, email or contact person,
                         and narrow by industry, approval status and the year it registered. --}}
                    <div class="w-full bg-slate-100 px-6 py-4">
                        <form method="GET" action="{{ route('reports.companies') }}" id="companyFilterForm"
                            class="flex flex-wrap items-center gap-3 bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-md text-sm font-medium text-slate-700">
                            <input type="hidden" name="tab" value="company">
                            {{-- Keep the Alumni tab's filters as they are --}}
                            @foreach ($alumniQuery as $key => $value)
                                @foreach ((array) $value as $v)
                                <input type="hidden" name="{{ is_array($value) ? $key . '[]' : $key }}" value="{{ $v }}">
                                @endforeach
                            @endforeach

                            <!-- Search (same pill style as the filter dropdowns) -->
                            <div class="relative" style="flex: 1.4 1 0%; min-width: 230px;">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs"><i class="fas fa-search"></i></span>
                                <input type="text" name="company_search" value="{{ $companyFilters['search'] }}" maxlength="100" autocomplete="off" data-live-search
                                    placeholder="Search company, email or name" title="Search by company name, email or contact person" aria-label="Search companies"
                                    class="company-pill w-full pl-11 pr-4 py-1.5 border rounded-full bg-white text-xs text-[#0E0F3B] placeholder-gray-500 focus:outline-none">
                            </div>

                            <!-- Industry -->
                            <div class="flex-1 min-w-[150px]">
                                @include('partials.multiselect-filter', [
                                    'name' => 'company_industry',
                                    'icon' => 'fas fa-industry',
                                    'placeholder' => 'Industry',
                                    'labelPrefix' => 'Industry',
                                    'options' => $industries->pluck('industry_name', 'industry_id'),
                                    'selected' => $companyFilters['industry_ids'],
                                ])
                            </div>

                            <!-- Status -->
                            <div class="flex-1 min-w-[150px]">
                                @include('partials.multiselect-filter', [
                                    'name' => 'company_status',
                                    'icon' => 'fas fa-user-check',
                                    'placeholder' => 'Status',
                                    'labelPrefix' => 'Status',
                                    'options' => ['registered' => 'Registered (approved)', 'pending' => 'Pending approval'],
                                    'selected' => $companyFilters['statuses'],
                                ])
                            </div>

                            <!-- Year Registered -->
                            <div class="flex-1 min-w-[150px]">
                                @include('partials.multiselect-filter', [
                                    'name' => 'company_year',
                                    'icon' => 'fas fa-calendar-day',
                                    'placeholder' => 'Year Registered',
                                    'labelPrefix' => 'Registered',
                                    'options' => collect($companyYearOptions)->mapWithKeys(fn ($y) => [$y => $y]),
                                    'selected' => $companyFilters['years'],
                                ])
                            </div>

                            <!-- Sort (single choice, styled like the dropdowns) -->
                            <div class="relative" style="flex: 1.25 1 0%; min-width: 190px;">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs"><i class="fas fa-arrow-down-wide-short"></i></span>
                                <select name="company_sort" aria-label="Sort companies"
                                    class="company-pill w-full appearance-none pl-11 pr-10 py-1.5 border rounded-full bg-white text-xs focus:outline-none cursor-pointer {{ $companyFilters['sort'] !== 'newest' ? 'text-[#0E0F3B] font-semibold' : 'text-gray-500' }}">
                                    @foreach ($companySorts as $key => $label)
                                    <option value="{{ $key }}" @selected($companyFilters['sort'] === $key)>Sort: {{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none"><i class="fas fa-chevron-down text-[10px]"></i></span>
                            </div>

                            <div class="w-full flex items-center justify-end gap-3 pt-3 mt-1 border-t border-slate-100">
                                @if ($companyFiltered)
                                <a href="{{ route('reports.companies', array_merge($alumniQuery, ['tab' => 'company'])) }}"
                                    class="shrink-0 whitespace-nowrap bg-slate-200 text-[10px] text-slate-600 px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-slate-300 transition shadow-sm font-semibold uppercase tracking-wide">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 shrink-0"></i> Reset Filters
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
                                <a href="{{ route('reports.exportCsv', array_merge(['group' => 'companies'], $alumniQuery, $companyQuery)) }}"
                                    class="shrink-0 whitespace-nowrap border border-[#0E0F3B] text-[#0E0F3B] text-[10px] px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#0E0F3B] hover:text-white transition shadow-sm font-semibold uppercase tracking-wide">
                                    <i data-lucide="download" class="w-3.5 h-3.5 shrink-0"></i> EXPORT CSV
                                </a>
                            </div>
                        </form>
                    </div>

                    @php
                        $companyTables = [];
                        if ($companyFilters['statuses'] !== ['pending']) $companyTables[] = ['Registered Companies', 'Approved employer accounts', $r['registeredCompanies'], 'No registered companies match these filters.'];
                        if ($companyFilters['statuses'] !== ['registered']) $companyTables[] = ['Pending / Unregistered Companies', 'Awaiting admin approval', $r['pendingCompanies'], 'No pending companies match these filters.'];
                    @endphp
                    @foreach ($companyTables as [$title, $sub, $rows, $emptyText])
                    <div class="chart-card my-4">
                        <div class="card-title">{{ $title }} <span>({{ $rows->count() }})</span></div>
                        <div class="card-sub">{{ $sub }}</div>
                        <div style="overflow-x:auto; margin-top:10px; max-height:360px; overflow-y:auto;">
                            <table style="width:100%; border-collapse:collapse; font-size:11px;">
                                <thead>
                                    <tr style="background:#0E0F3B; color:#fff; text-align:left; position:sticky; top:0; z-index:1;">
                                        <th style="padding:6px 8px;">Company</th>
                                        <th style="padding:6px 8px;">Industry</th>
                                        <th style="padding:6px 8px;">Contact Person</th>
                                        <th style="padding:6px 8px;">Email</th>
                                        <th style="padding:6px 8px; text-align:center;">Job Posts</th>
                                        <th style="padding:6px 8px;">Registered On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $employer)
                                    <tr style="border-top:1px solid #f1f5f9;">
                                        <td style="padding:6px 8px; font-weight:600; color:#0E0F3B;">{{ $employer->employer_company_name }}</td>
                                        <td style="padding:6px 8px;">{{ $employer->industry->industry_name ?? 'N/A' }}</td>
                                        <td style="padding:6px 8px;">{{ trim(($employer->user->user_first_name ?? '') . ' ' . ($employer->user->user_last_name ?? '')) ?: 'N/A' }}</td>
                                        <td style="padding:6px 8px;">{{ $employer->user->user_email ?? 'N/A' }}</td>
                                        <td style="padding:6px 8px; text-align:center;">{{ $employer->job_postings_count }}</td>
                                        <td style="padding:6px 8px; white-space:nowrap;">{{ optional($employer->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" style="padding:16px; text-align:center; color:#1D264F;">{{ $emptyText }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        /* ── Alumni | Company tabs ─────────────────────────────────────
           The filter bar is shared, so switching tabs only swaps which
           panel is visible — the current filters stay applied to both.
           The active tab is mirrored into the URL and into the filter
           form so Apply/Clear Filters reload onto the same tab. */
        const REP_CLEAR_LINK = document.querySelector('#dashboardFilterForm a[href]');
        const REP_CLEAR_BASE = REP_CLEAR_LINK ? REP_CLEAR_LINK.href.split('?')[0] : null;

        function repSwitchTab(tab) {
            const isCompany = tab === 'company';
            document.getElementById('rep-tab-alumni').classList.toggle('hidden', isCompany);
            document.getElementById('rep-tab-company').classList.toggle('hidden', !isCompany);
            document.getElementById('rep-tab-btn-alumni').classList.toggle('active', !isCompany);
            document.getElementById('rep-tab-btn-company').classList.toggle('active', isCompany);
            repSyncTab(isCompany ? 'company' : 'alumni');
        }

        function repSyncTab(tab) {
            // Carried on the filter form's own GET submit (Apply Filters)…
            const form = document.getElementById('dashboardFilterForm');
            if (form) {
                let field = form.querySelector('input[name="tab"]');
                if (!field) {
                    field = document.createElement('input');
                    field.type = 'hidden';
                    field.name = 'tab';
                    form.appendChild(field);
                }
                field.value = tab;
            }
            // …and on Clear Filters, which drops the query string entirely.
            if (REP_CLEAR_LINK) REP_CLEAR_LINK.href = REP_CLEAR_BASE + '?tab=' + tab;
            // …and in the address bar, so a refresh keeps the tab.
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        }

        repSyncTab(@json($activeTab));

        // Applying the Alumni filters keeps the Company tab's filters (and vice versa — see #companyFilterForm).
        (function () {
            const form = document.getElementById('dashboardFilterForm');
            if (!form) return;
            const companyQuery = @json($companyQuery);
            Object.entries(companyQuery).forEach(([name, value]) => {
                (Array.isArray(value) ? value : [value]).forEach(v => {
                    const i = document.createElement('input');
                    i.type = 'hidden'; i.name = Array.isArray(value) ? name + '[]' : name; i.value = v;
                    form.appendChild(i);
                });
            });
        })();

        function filterEmployedAlumniRows() {
            const query = document.getElementById('employedAlumniSearch').value.trim().toLowerCase();
            const rows = document.querySelectorAll('#employedAlumniTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            document.getElementById('employedAlumniNoResults').classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // This group has no Chart.js canvases (both reports here are
        // tables, not graphs) — export just POSTs the current filters, no
        // chart images to capture.
        function exportPdfWithCharts() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = {!! json_encode(route('reports.exportPdf.post', ['group' => 'companies'])) !!};
            form.style.display = 'none';
            const addField = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = value; form.appendChild(i); };
            addField('_token', '{{ csrf_token() }}');
            addField('charts', '{}');
            const params = new URLSearchParams(window.location.search);
            ['batch', 'program_id', 'employment_status', 'college', 'year'].forEach(key => {
                params.getAll(key + '[]').forEach(v => addField(key + '[]', v));
            });
            // Applied-filters marker, so an emptied Year/Year Graduated exports as "all years", not the current-year default.
            if (params.has('filtered')) addField('filtered', '1');
            // Company tab filters
            ['company_search', 'company_sort'].forEach(key => { if (params.get(key)) addField(key, params.get(key)); });
            ['company_industry', 'company_status', 'company_year'].forEach(key => {
                params.getAll(key + '[]').forEach(v => addField(key + '[]', v));
            });
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }
    </script>

    @include('partials.staged-multiselect')
    @include('partials.live-search')
    @include('partials.report-college-cascade')
</body>

</html>
