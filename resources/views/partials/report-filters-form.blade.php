{{--
    Shared filter bar for the admin dashboard and its 3 grouped report-detail
    pages (superAdmin/reports/*) — Batch/College/Course/Employment
    Status/Year, all staged multi-selects (item 15), College cascading
    Course (items 12/13), plus Apply/Clear/Export PDF actions.

    Requires the including page to also:
      - @include('partials.staged-multiselect') once, anywhere on the page
      - define its own exportPdfWithCharts() JS function (the chart-canvas
        ids to capture differ per page) — the button below just calls it
      - include the applyCollegeCascade() cascade script (copy the block
        from dashboard.blade.php's <script> — kept per-page rather than
        shared here since it's a handful of lines wired to page-specific
        selectors already declared inline)

    Params:
      $formAction        — form's action URL (route('superAdmin.dashboard') or a reports.* route)
      $clearRoute        — "Clear Filters" link target (same route, no query string)
      $dashboardFilters  — ['batch' => [...], 'program_id' => [...], 'employment_status' => [...], 'college' => [...], 'year' => [...]]
      $batchYearOptions  — Collection of distinct batch years
      $programs          — Collection of Program models
      $yearOptions       — array of years for the Year filter
--}}
<div class="w-full bg-slate-100 px-6 py-4">
    <form method="GET" action="{{ $formAction }}" id="dashboardFilterForm"
        class="flex flex-wrap items-center gap-3 bg-white px-6 py-4 rounded-xl border border-slate-200 shadow-md text-sm font-medium text-slate-700">

        <!-- Batch Year (multi-select, item 15) -->
        <div class="shrink-0" style="width:170px;">
            @include('partials.multiselect-filter', [
                'name' => 'batch',
                'icon' => 'fas fa-calendar',
                'placeholder' => 'Batch Year',
                'options' => $batchYearOptions->mapWithKeys(fn ($y) => [$y => $y]),
                'selected' => $dashboardFilters['batch'],
            ])
        </div>

        <!-- College (item 12/13 — cascades the Course filter below) -->
        <div class="shrink-0" style="width:190px;">
            @include('partials.multiselect-filter', [
                'name' => 'college',
                'icon' => 'fas fa-university',
                'placeholder' => 'College',
                'options' => \App\Models\Program::COLLEGES,
                'selected' => $dashboardFilters['college'],
            ])
        </div>

        <!-- Course -->
        <div class="shrink-0" style="width:220px;">
            @include('partials.multiselect-filter', [
                'name' => 'program_id',
                'icon' => 'fas fa-graduation-cap',
                'placeholder' => 'Course',
                'options' => $programs->pluck('program_name', 'program_id'),
                'selected' => $dashboardFilters['program_id'],
                'optionAttrs' => $programs->mapWithKeys(fn ($p) => [$p->program_id => 'data-college="' . e($p->college) . '"']),
            ])
        </div>

        <!-- Employment Status -->
        <div class="shrink-0" style="width:180px;">
            @include('partials.multiselect-filter', [
                'name' => 'employment_status',
                'icon' => 'fas fa-user-check',
                'placeholder' => 'Employment Status',
                'options' => ['employed' => 'Employed', 'unemployed' => 'Unemployed'],
                'selected' => $dashboardFilters['employment_status'],
            ])
        </div>

        <!-- Year (item 16 — distinct from Batch: when things happened, not when alumni graduated) -->
        <div class="shrink-0" style="width:170px;">
            @include('partials.multiselect-filter', [
                'name' => 'year',
                'icon' => 'fas fa-calendar-day',
                'placeholder' => 'Year',
                'options' => collect($yearOptions)->mapWithKeys(fn ($y) => [$y => $y]),
                'selected' => $dashboardFilters['year'],
            ])
        </div>

        <div class="w-full flex items-center justify-end gap-3 pt-3 mt-1 border-t border-slate-100">
            @if (array_filter($dashboardFilters))
            <a href="{{ $clearRoute }}"
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
        </div>
    </form>
</div>
