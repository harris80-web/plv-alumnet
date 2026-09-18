@php
    $current_page = 'reports';
    // Which tab renders active on first paint — carried in the query string so
    // "Apply Filters" / "Clear Filters" (both plain GET navigations) come back
    // to the tab the admin was working in instead of resetting to Alumni.
    $activeTab = request()->query('tab') === 'company' ? 'company' : 'alumni';
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

                {{--
                    One filter bar, rendered above both panels so it stays put (and
                    keeps working) whichever tab is open. It is NOT duplicated per
                    tab on purpose: partials/report-filters-form uses a fixed form
                    id and partials/report-college-cascade resolves its two
                    dropdowns with document.querySelector, so a second copy would
                    silently break the College → Course cascade.
                --}}
                @include('partials.report-filters-form', [
                    'formAction' => route('reports.companies'),
                    'clearRoute' => route('reports.companies'),
                    'dashboardFilters' => $dashboardFilters,
                    'batchYearOptions' => $batchYearOptions,
                    'programs' => $programs,
                    'yearOptions' => $yearOptions,
                ])

                <!-- ══ TAB: ALUMNI ══ -->
                <div id="rep-tab-alumni" class="{{ $activeTab === 'alumni' ? '' : 'hidden' }}">
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
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div class="chart-card">
                            <div class="card-title">Registered Companies <span>({{ $r['registeredCompanies']->count() }})</span></div>
                            <div class="card-sub">Approved employer accounts</div>
                            <div style="overflow-x:auto; margin-top:10px; max-height:320px; overflow-y:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                                    <thead>
                                        <tr style="background:#0E0F3B; color:#fff; text-align:left;">
                                            <th style="padding:6px 8px;">Company</th>
                                            <th style="padding:6px 8px;">Industry</th>
                                            <th style="padding:6px 8px;">Contact</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($r['registeredCompanies'] as $employer)
                                        <tr style="border-top:1px solid #f1f5f9;">
                                            <td style="padding:6px 8px; font-weight:600; color:#0E0F3B;">{{ $employer->employer_company_name }}</td>
                                            <td style="padding:6px 8px;">{{ $employer->industry->industry_name ?? 'N/A' }}</td>
                                            <td style="padding:6px 8px;">{{ $employer->user->user_email ?? 'N/A' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" style="padding:16px; text-align:center; color:#9ca3af;">None yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="chart-card">
                            <div class="card-title">Pending / Unregistered Companies <span>({{ $r['pendingCompanies']->count() }})</span></div>
                            <div class="card-sub">Awaiting admin approval</div>
                            <div style="overflow-x:auto; margin-top:10px; max-height:320px; overflow-y:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                                    <thead>
                                        <tr style="background:#0E0F3B; color:#fff; text-align:left;">
                                            <th style="padding:6px 8px;">Company</th>
                                            <th style="padding:6px 8px;">Industry</th>
                                            <th style="padding:6px 8px;">Contact</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($r['pendingCompanies'] as $employer)
                                        <tr style="border-top:1px solid #f1f5f9;">
                                            <td style="padding:6px 8px; font-weight:600; color:#0E0F3B;">{{ $employer->employer_company_name }}</td>
                                            <td style="padding:6px 8px;">{{ $employer->industry->industry_name ?? 'N/A' }}</td>
                                            <td style="padding:6px 8px;">{{ $employer->user->user_email ?? 'N/A' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" style="padding:16px; text-align:center; color:#9ca3af;">None pending.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('reports.exportCsv', array_merge(['group' => 'companies'], array_filter($dashboardFilters))) }}"
                        class="text-xs font-bold text-slate-500 hover:text-[#C73D1A] flex items-center gap-1">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
                    </a>
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
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }
    </script>

    @include('partials.staged-multiselect')
    @include('partials.report-college-cascade')
</body>

</html>
