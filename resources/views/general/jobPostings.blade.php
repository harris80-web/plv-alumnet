<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | My Job Postings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Same table look as the admin Job Placement Management tables (.jobs-table there). */
    .jobs-table {
        width: 100%;
        min-width: 1040px;
        border-collapse: collapse;
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
    }

    .jobs-table thead th {
        padding: 12px 10px;
        text-align: center;
        vertical-align: middle;
        font-size: 9px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.3;
        white-space: nowrap;
    }

    /* 3-dot row menu — same as the admin Job Placement Management table. */
    .action-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        z-index: 50;
        min-width: 160px;
    }

    .action-dropdown.open {
        display: block;
    }

    .jobs-table tbody td {
        padding: 12px 10px;
        text-align: center;
        vertical-align: middle;
        font-size: 10px;
        white-space: nowrap;
        line-height: 1.3;
    }
    /* ---- Phones & tablets (below 1024px): each job row becomes a card ----
       Same markup as the desktop table, so sorting, pagination, the 3-dot menu
       and the tooltips keep working. The header row is hidden and each cell
       shows its own label (data-label). The #my-jobs-tbody selectors are there
       to beat Tailwind's divide-y / border-r and the !important alignment rules
       from partials/table-sort. */
    @media (max-width: 1023.98px) {
        .table-scroll { overflow: visible; }
        .jobs-table { display: block; min-width: 0; }
        .jobs-table thead { display: none; }
        #my-jobs-tbody { display: grid; grid-template-columns: 1fr; gap: 12px; padding: 12px; background: #f8fafc; }
        #my-jobs-tbody > tr {
            display: grid;
            grid-template-columns: auto 1fr 1fr auto;
            grid-template-areas:
                "num    status status  menu"
                "title  title  title   title"
                "loc    loc    loc     loc"
                "type   type   setup   setup"
                "apps   apps   hired   hired"
                "posted posted closing closing"
                "btn    btn    btn     btn";
            gap: 10px 12px;
            padding: 12px 14px 14px 16px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }
        #my-jobs-tbody > tr > td {
            display: block;
            min-width: 0;
            padding: 0;
            border: 0;
            text-align: left !important;
            white-space: normal !important;
            font-size: 12px;
            line-height: 1.35;
        }
        #my-jobs-tbody > tr > td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 2px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        #my-jobs-tbody td[data-cell="num"] { grid-area: num; align-self: center; font-size: 11px; color: #94a3b8; }
        #my-jobs-tbody td[data-cell="num"]::before { content: "#"; }
        #my-jobs-tbody td[data-cell="status"] { grid-area: status; align-self: center; }
        #my-jobs-tbody td[data-cell="status"] > span { font-size: 9px; }
        #my-jobs-tbody td[data-cell="menu"] { grid-area: menu; align-self: center; margin: -6px -6px -6px 0; }
        #my-jobs-tbody td[data-cell="menu"] .menu-button { width: 36px; height: 36px; }
        #my-jobs-tbody td[data-cell="title"] { grid-area: title; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; }
        #my-jobs-tbody td[data-cell="title"] > p:first-child { font-size: 15px; font-weight: 700; line-height: 1.3; }
        #my-jobs-tbody td[data-cell="title"] > p + p { font-size: 12px; }
        #my-jobs-tbody td[data-cell="loc"] { grid-area: loc; }
        #my-jobs-tbody td[data-cell="type"] { grid-area: type; }
        #my-jobs-tbody td[data-cell="setup"] { grid-area: setup; }
        #my-jobs-tbody td[data-cell="apps"] { grid-area: apps; }
        #my-jobs-tbody td[data-cell="hired"] { grid-area: hired; }
        #my-jobs-tbody td[data-cell="posted"] { grid-area: posted; }
        #my-jobs-tbody td[data-cell="closing"] { grid-area: closing; }
        #my-jobs-tbody td[data-cell="btn"] { grid-area: btn; padding-top: 4px; }
        #my-jobs-tbody td[data-cell="btn"] > a,
        #my-jobs-tbody td[data-cell="btn"] .noapp-tip-wrap,
        #my-jobs-tbody td[data-cell="btn"] .noapp-tip-wrap > button { display: flex; width: 100%; align-items: center; justify-content: center; }
        #my-jobs-tbody td[data-cell="btn"] > a,
        #my-jobs-tbody td[data-cell="btn"] .noapp-tip-wrap > button { padding: 10px 12px; font-size: 11px; }
        /* "No job postings" message spans the whole list. */
        #my-jobs-tbody > tr.my-jobs-empty { display: block; grid-column: 1 / -1; }
        #my-jobs-tbody > tr.my-jobs-empty > td { padding: 48px 16px; text-align: center !important; font-size: inherit; }
    }
    @media (min-width: 768px) and (max-width: 1023.98px) {
        #my-jobs-tbody { grid-template-columns: 1fr 1fr; }
    }

    /* Disabled "Applicants" tooltip shown under the button (no room above): arrow points up. */
    .noapp-tip.is-below .noapp-tip-arrow { top: auto; bottom: 100%; border-top-color: transparent; border-bottom-color: #1f2937; }
</style>


<body class="min-h-screen flex flex-col">
    @php
    $current_page = 'general.jobPostings';
    @endphp
    @include('partials.header-employer')
    @include('partials.success')

    @include('partials.error')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @php
        $filters = $filters ?? [];
        $statusCounts = $statusCounts ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'expired' => 0];
        $panelFilterCount = $panelFilterCount ?? 0;
        $activeStatus = $filters['status'] ?? '';
        $myJobsUrl = route('jobPosting.myJobPosts', ['id' => $users->user_id]);
        $hasAnyFilter = (bool) array_filter($filters);

        // Plain-language summary of the slide-in panel's active filters, shown under the toolbar.
        $panelSummary = array_filter([
            'Industry' => $industries->whereIn('industry_id', (array) ($filters['industry'] ?? []))->pluck('industry_name')->implode(', '),
            'Job Type' => implode(', ', (array) ($filters['job_type'] ?? [])),
            'Job Setup' => implode(', ', (array) ($filters['job_setup'] ?? [])),
            'Location' => $filters['location'] ?? '',
            'Date Posted' => ['24h' => 'Last 24 Hours', '7d' => 'Last 7 Days', '30d' => 'Last 30 Days'][$filters['date_posted'] ?? ''] ?? '',
        ]);

        // Status widgets: clicking one filters the table by that status, clicking it again clears it.
        $statusCards = [
            ['key' => 'pending', 'label' => 'Pending Approval', 'icon' => 'fa-clock', 'color' => 'text-[#ED7A07]', 'active' => 'border-[#ED7A07] ring-2 ring-orange-200'],
            ['key' => 'approved', 'label' => 'Approved Job Posts', 'icon' => 'fa-circle-check', 'color' => 'text-green-500', 'active' => 'border-green-500 ring-2 ring-green-200'],
            ['key' => 'expired', 'label' => 'Expired Job Posts', 'icon' => 'fa-calendar-xmark', 'color' => 'text-slate-500', 'active' => 'border-slate-400 ring-2 ring-slate-200'],
        ];
    @endphp

    <main class="max-w-7xl w-full mx-auto px-6 py-8 flex-1">

        <!-- TITLE -->
        <div class="mb-6 rp-postings-header">
            <h1 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent rp-postings-title">
                MY JOB POSTINGS
            </h1>
            <p class="text-sm text-slate-500 mt-1">Manage your job posts, track their applicants, and see where each post is in the approval process.</p>
        </div>

        <!-- STAT CARDS (same widget style as the admin Job Placement Management page) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-transparent shadow-sm transition-all hover:shadow-md px-5 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-briefcase text-lg shrink-0 text-slate-800"></i>
                    <p class="text-2xl font-bold text-slate-800">{{ $statusCounts['total'] }}</p>
                </div>
                <p class="text-xs font-medium text-slate-500 mt-1">Total Job Posts</p>
            </div>
            @foreach ($statusCards as $card)
            @php $isActive = $activeStatus === $card['key']; @endphp
            <button type="button"
                class="text-left bg-white rounded-lg border shadow-sm px-5 py-4 cursor-pointer transition-all hover:shadow-md {{ $isActive ? $card['active'] : 'border-transparent' }}"
                title="Click to {{ $isActive ? 'clear this filter' : 'show only ' . strtolower($card['label']) }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                onclick="pvSearchNavigate('status', '{{ $isActive ? '' : $card['key'] }}', 'page')">
                <div class="flex items-center gap-2">
                    <i class="fas {{ $card['icon'] }} text-lg shrink-0 {{ $card['color'] }}"></i>
                    <p class="text-2xl font-bold {{ $card['color'] }}">{{ $statusCounts[$card['key']] }}</p>
                </div>
                <p class="text-xs font-medium text-slate-500 mt-1">{{ $card['label'] }}</p>
            </button>
            @endforeach
        </div>

        <!-- TABLE CARD -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden w-full">

            <!-- Toolbar: Search / Status / Filter panel / Post / Export -->
            <form method="GET" action="{{ $myJobsUrl }}" class="flex flex-col lg:flex-row lg:items-center gap-3 p-4 border-b border-slate-100">
                {{-- Carry the panel's filters and the page size through a search/status change. --}}
                @foreach (['industry', 'job_type', 'job_setup'] as $arrayFilter)
                    @foreach ((array) ($filters[$arrayFilter] ?? []) as $value)
                    <input type="hidden" name="{{ $arrayFilter }}[]" value="{{ $value }}">
                    @endforeach
                @endforeach
                @foreach (['location', 'date_posted'] as $textFilter)
                    @if (!empty($filters[$textFilter]))
                    <input type="hidden" name="{{ $textFilter }}" value="{{ $filters[$textFilter] }}">
                    @endif
                @endforeach
                @if (request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif

                <div class="relative flex-1 lg:max-w-md">
                    <i class="fas fa-search text-sm absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by Job Title or Company Name"
                        aria-label="Search by job title or company name"
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative flex-1 min-w-0 lg:flex-none lg:w-48">
                        <select name="status" onchange="this.form.submit()" aria-label="Filter by status"
                            class="w-full pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-full text-sm appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $activeStatus === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ $activeStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="expired" {{ $activeStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        <i class="fas fa-chevron-down text-[10px] absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>

                    <button type="button" id="filterSidebarToggle" onclick="toggleFilterSidebar()" title="More filters" aria-label="More filters" aria-controls="filter-sidebar" aria-expanded="false"
                        class="relative p-2 w-9 h-9 bg-white border rounded-lg transition-all shrink-0 {{ $panelFilterCount ? 'border-[#C73D1A] text-[#C73D1A]' : 'border-slate-200 text-slate-500 hover:border-[#C73D1A]' }}">
                        <i class="fas fa-filter text-sm"></i>
                        @if ($panelFilterCount)
                        <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-[#C73D1A] text-white text-[10px] font-bold flex items-center justify-center">{{ $panelFilterCount }}</span>
                        @endif
                    </button>

                    @if ($hasAnyFilter)
                    <a href="{{ $myJobsUrl }}" class="shrink-0 text-xs font-bold text-slate-400 hover:text-[#C73D1A] whitespace-nowrap">
                        <i class="fas fa-times mr-1"></i>CLEAR
                    </a>
                    @endif
                </div>

                <div class="lg:ml-auto flex flex-wrap items-center gap-3">
                    <button type="button" onclick="openPostJobModal()"
                        class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-4 py-2 rounded-lg font-bold text-xs tracking-widest shadow-lg transition-all whitespace-nowrap">
                        <i class="fas fa-plus text-xs"></i>
                        <span>POST A NEW JOB</span>
                    </button>
                    <a href="{{ route('jobPosting.myJobPostsCsv', ['id' => $users->user_id] + request()->except(['page', 'per_page'])) }}"
                        title="Download every job post matching the current filters"
                        class="flex-1 lg:flex-none shrink-0 flex items-center justify-center gap-2 px-5 py-2 bg-[#C73D1A] hover:bg-[#a83215] text-white text-xs font-bold rounded-lg transition-all uppercase whitespace-nowrap">
                        <i class="fas fa-download text-xs"></i> EXPORT CSV
                    </a>
                </div>
            </form>

            @if ($panelSummary)
            <div class="px-4 py-2 border-b border-slate-100 bg-slate-50 text-xs text-slate-500 flex flex-wrap gap-x-4 gap-y-1">
                <span class="font-bold text-[#0E0F3B]">Filtered by:</span>
                @foreach ($panelSummary as $label => $value)
                <span><span class="font-semibold text-slate-600">{{ $label }}:</span> {{ $value }}</span>
                @endforeach
            </div>
            @endif

            {{-- Phones/tablets: the job cards have no column headers to click, so sorting lives here. --}}
            @if ($jobPostings->count() > 1)
            <div class="lg:hidden flex items-center justify-end gap-2 px-4 pt-3">
                <label for="myJobsMobileSort" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Sort by</label>
                <div class="relative w-52 min-w-0">
                    <select id="myJobsMobileSort"
                        class="w-full pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-full text-sm appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                        <option value="">Default order</option>
                        <option value="1:asc">Job Title (A–Z)</option>
                        <option value="1:desc">Job Title (Z–A)</option>
                        <option value="8:desc">Newest posted</option>
                        <option value="8:asc">Oldest posted</option>
                        <option value="9:asc">Closing soonest</option>
                        <option value="9:desc">Closing latest</option>
                        <option value="5:desc">Most applicants</option>
                        <option value="5:asc">Fewest applicants</option>
                        <option value="7:asc">Status</option>
                    </select>
                    <i class="fas fa-chevron-down text-[10px] absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
            @endif

            <!-- TABLE (shown as cards below 1024px, see the "Phones & tablets" CSS) -->
            <div class="overflow-x-auto table-scroll">
                <table class="jobs-table">
                    <thead class="bg-[#0E0F3B] text-white">
                        <tr>
                            <th class="border-r border-slate-700">#</th>
                            <th data-sort class="border-r border-slate-700">Job Title <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort class="border-r border-slate-700">Location <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort class="border-r border-slate-700">Job Type <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort class="border-r border-slate-700">Job Setup <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort data-col-center class="border-r border-slate-700">Applicants <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort data-col-center class="border-r border-slate-700">Hired / Limit <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort class="border-r border-slate-700">Status <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort class="border-r border-slate-700">Posting Date <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-sort class="border-r border-slate-700">Closing Date <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                            <th data-col-center class="border-r border-slate-700">View Applicants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="my-jobs-tbody">
                        @forelse ($jobPostings as $job)
                        @php
                            $status = $job->myPostStatus();
                            $applicantCount = $job->applicants->count();
                            $unreadCount = $job->applicants->where('pivot.is_read', false)->count();
                            $hiredCount = $job->applicants->where('pivot.application_status', 'hired')->count();
                            $badge = [
                                'pending' => ['PENDING', 'bg-amber-100 text-amber-600 border-amber-200', 'Awaiting super admin approval — not visible on the job board yet.'],
                                'approved' => ['APPROVED', 'bg-green-100 text-green-600 border-green-200', 'Approved and live on the job board.'],
                                'expired' => ['EXPIRED', 'bg-slate-100 text-slate-500 border-slate-200', 'Closing date has passed — no longer shown on the job board.'],
                            ][$status];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td data-cell="num" class="font-medium text-black border-r border-slate-100">{{ $jobPostings->firstItem() + $loop->index }}</td>
                            <td data-cell="title" class="border-r border-slate-100">
                                <p class="font-semibold text-[#0E0F3B]">{{ $job->job_posting_title }}</p>
                                <p class="text-slate-400 mt-0.5">{{ $job->job_posting_company }}</p>
                            </td>
                            <td data-cell="loc" data-label="Location" class="font-medium text-black border-r border-slate-100">{{ $job->job_posting_address }}</td>
                            <td data-cell="type" data-label="Job Type" class="font-medium text-black border-r border-slate-100">{{ $job->job_posting_employment_type }}</td>
                            <td data-cell="setup" data-label="Job Setup" class="font-medium text-black border-r border-slate-100">{{ $job->job_posting_setup }}</td>
                            <td data-cell="apps" data-label="Applicants" class="font-medium text-black border-r border-slate-100" data-sort-value="{{ $applicantCount }}">
                                {{ $applicantCount }}
                                @if ($unreadCount)
                                <span class="ml-1 px-1.5 py-0.5 rounded-full bg-orange-100 text-[#C73D1A] text-[8px] font-bold whitespace-nowrap" title="{{ $unreadCount }} unread application(s)">{{ $unreadCount }} NEW</span>
                                @endif
                            </td>
                            <td data-cell="hired" data-label="Hired / Limit" class="font-medium text-black border-r border-slate-100" data-sort-value="{{ $hiredCount }}">{{ $hiredCount }} / {{ $job->hiring_limit }}</td>
                            <td data-cell="status" class="border-r border-slate-100">
                                <span title="{{ $badge[2] }}" class="px-2 py-1 rounded-full border text-[7px] font-bold inline-block whitespace-nowrap {{ $badge[1] }}">{{ $badge[0] }}</span>
                            </td>
                            <td data-cell="posted" data-label="Posting Date" class="font-medium text-black border-r border-slate-100" data-sort-value="{{ $job->created_at->timestamp }}">{{ $job->created_at->format('M d, Y') }}</td>
                            <td data-cell="closing" data-label="Closing Date" class="font-medium border-r border-slate-100 {{ $status === 'expired' ? 'text-slate-400' : 'text-black' }}" data-sort-value="{{ $job->job_closing_date ? str_replace('-', '', $job->job_closing_date) : 0 }}">
                                {{ $job->job_closing_date ? \Carbon\Carbon::parse($job->job_closing_date)->format('M d, Y') : '—' }}
                            </td>
                            <td data-cell="btn" class="border-r border-slate-100">
                                @if ($job->job_approved)
                                <a href="{{ route('jobApplication.showApplications', ['jobPostingId' => $job->job_posting_id]) }}"
                                    class="inline-block px-3 py-1.5 bg-[#1D264F] hover:bg-blue-900 text-white rounded-md text-[10px] font-bold uppercase tracking-wide transition-colors whitespace-nowrap">
                                    <i class="fas fa-users mr-1"></i> Applicants
                                </a>
                                @else
                                @php $noApplicantsReason = $status === 'expired' ? 'Expired before it was approved — no applicants' : 'Still pending approval — no applicants yet'; @endphp
                                {{-- Disabled buttons get no mouse events (so no hover/native title tooltip):
                                     the wrapper takes hover + keyboard focus and shows the reason instead.
                                     Same tooltip look as the job details modal's (partials/job-detail-modal). --}}
                                <span class="noapp-tip-wrap relative inline-block cursor-not-allowed group/noapp focus:outline-none" tabindex="0"
                                    aria-label="Applicants unavailable: {{ $noApplicantsReason }}">
                                    <button type="button" disabled aria-hidden="true" tabindex="-1"
                                        class="pointer-events-none px-3 py-1.5 bg-slate-200 text-slate-400 rounded-md text-[10px] font-bold uppercase tracking-wide whitespace-nowrap group-focus-visible/noapp:ring-2 group-focus-visible/noapp:ring-[#C73D1A]">
                                        <i class="fas fa-users mr-1"></i> Applicants
                                    </button>
                                    <span role="tooltip"
                                        class="noapp-tip fixed left-0 top-0 z-40 opacity-0 invisible group-hover/noapp:opacity-100 group-hover/noapp:visible group-focus/noapp:opacity-100 group-focus/noapp:visible pointer-events-none transition-opacity duration-200">
                                        <span class="relative block bg-gray-800 text-white text-[10px] font-medium normal-case tracking-normal text-left py-1.5 px-2.5 rounded shadow-lg whitespace-nowrap">
                                            {{ $noApplicantsReason }}
                                            <span class="noapp-tip-arrow absolute top-full right-6 border-4 border-transparent border-t-gray-800"></span>
                                        </span>
                                    </span>
                                </span>
                                @endif
                            </td>
                            <td data-cell="menu" class="relative">
                                {{-- Same 3-dot menu as the admin Job Placement Management table. --}}
                                <div class="inline-block text-left">
                                    <button type="button" class="menu-button w-7 h-7 inline-flex items-center justify-center hover:bg-slate-100 rounded-full transition-colors"
                                        title="Actions" aria-label="Actions for {{ $job->job_posting_title }}" aria-haspopup="menu" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical text-sm text-slate-500"></i>
                                    </button>
                                    <div class="action-dropdown bg-white border border-slate-200 rounded-md shadow-xl" role="menu">
                                        <div class="py-1">
                                            <button type="button" role="menuitem"
                                                class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 whitespace-nowrap"
                                                data-job-id="{{ $job->job_posting_id }}"
                                                data-image="{{ $job->thumbnailUrl() }}"
                                                data-uses-default-image="{{ $job->usesDefaultThumbnail() ? '1' : '0' }}"
                                                data-image-overlay-color="{{ $job->defaultThumbnailOverlay()['color'] }}"
                                                data-image-overlay-opacity="{{ $job->defaultThumbnailOverlay()['overlayOpacity'] }}"
                                                data-image-opacity="{{ $job->defaultThumbnailOverlay()['imageOpacity'] }}"
                                                data-title="{{ $job->job_posting_title }}"
                                                data-company="{{ $job->job_posting_company }}"
                                                data-posted="{{ $job->created_at->diffForHumans() }}"
                                                data-address="{{ $job->job_posting_address }}"
                                                data-description="{{ $job->job_posting_description }}"
                                                data-type="{{ $job->job_posting_employment_type }}"
                                                data-setup="{{ $job->job_posting_setup }}"
                                                data-valid="{{ $job->job_closing_date }}"
                                                data-programs="{{ $job->programs->pluck('program_name')->implode(', ') }}"
                                                data-industry="{{ $job->industry->industry_name ?? 'Not specified' }}"
                                                data-approved="{{ $job->job_approved ? '1' : '0' }}"
                                                data-status="{{ $status }}"
                                                data-status-label="{{ $job->myPostStatusLabel() }}"
                                                onclick="closeActionMenus(); openMyJobViewModal(this)">
                                                <i class="fas fa-eye w-4 mr-3 text-blue-500"></i> View Job Post
                                            </button>
                                            <button type="button" role="menuitem"
                                                class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-orange-50 whitespace-nowrap"
                                                onclick="closeActionMenus(); openEditPostModal('{{ $job->job_posting_id }}')">
                                                <i class="fas fa-pen w-4 mr-3 text-[#C73D1A]"></i> Edit Job Post
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="my-jobs-empty">
                            <td colspan="12" class="py-16 text-center text-slate-400">
                                <i class="fas fa-briefcase text-4xl mb-3 block text-slate-300"></i>
                                @if ($hasAnyFilter)
                                <p class="font-semibold text-sm">No job postings match your filters.</p>
                                <a href="{{ $myJobsUrl }}" class="inline-block mt-2 text-xs font-bold text-[#C73D1A] hover:underline">Clear filters</a>
                                @else
                                <p class="font-semibold text-sm">You haven't posted any jobs yet.</p>
                                <button type="button" onclick="openPostJobModal()" class="mt-2 text-xs font-bold text-[#C73D1A] hover:underline">Post your first job</button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                @include('partials.table-pagination-bar', [
                    'id' => 'myJobPostsTable',
                    'mode' => 'reload',
                    'paginator' => $jobPostings,
                    'perPageParam' => 'per_page',
                ])
            </div>
            @include('partials.table-sort')
        </div>
    </main>

    <!-- FILTER PANEL (same slide-in "Filter by" panel as the admin Job Placement Management page) -->
    <div id="filter-sidebar-overlay" onclick="toggleFilterSidebar()" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[205] hidden"></div>
    <div id="filter-sidebar" role="dialog" aria-modal="true" aria-labelledby="filterSidebarTitle" inert aria-hidden="true"
        class="fixed top-0 right-0 h-full w-[320px] max-w-full bg-white shadow-2xl z-[206] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <form method="GET" action="{{ $myJobsUrl }}" class="flex flex-col h-full">
            {{-- Keep the toolbar's search/status and the page size when applying panel filters. --}}
            @foreach (['search', 'status'] as $keep)
                @if (!empty($filters[$keep]))
                <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
                @endif
            @endforeach
            @if (request('per_page'))
            <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="p-6 flex items-center justify-between border-b border-slate-100">
                <h2 id="filterSidebarTitle" class="text-xl font-bold text-[#1e1b4b]">Filter by</h2>
                <button type="button" onclick="toggleFilterSidebar()" class="text-slate-400 hover:text-slate-600" aria-label="Close filters">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <div>
                    <label for="filterLocation" class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Location</label>
                    <input type="text" id="filterLocation" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Enter Location"
                        class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#C73D1A] focus:border-[#C73D1A]">
                </div>

                <hr class="border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider mb-3">Industry</p>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach ($industries as $industry)
                        <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium cursor-pointer">
                            <input type="checkbox" name="industry[]" value="{{ $industry->industry_id }}" class="w-4 h-4 rounded border-slate-300 accent-[#ED7A07]"
                                {{ in_array((string) $industry->industry_id, array_map('strval', (array) ($filters['industry'] ?? [])), true) ? 'checked' : '' }}>
                            {{ $industry->industry_name }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider mb-3">Job Type</p>
                    <div class="space-y-2">
                        @foreach (['Full-Time', 'Part-Time', 'Freelance'] as $type)
                        <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium cursor-pointer">
                            <input type="checkbox" name="job_type[]" value="{{ $type }}" class="w-4 h-4 rounded border-slate-300 accent-[#ED7A07]"
                                {{ in_array($type, (array) ($filters['job_type'] ?? []), true) ? 'checked' : '' }}>
                            {{ $type }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider mb-3">Job Setup</p>
                    <div class="space-y-2">
                        @foreach (['On-Site', 'Remote', 'Hybrid'] as $setup)
                        <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium cursor-pointer">
                            <input type="checkbox" name="job_setup[]" value="{{ $setup }}" class="w-4 h-4 rounded border-slate-300 accent-[#ED7A07]"
                                {{ in_array($setup, (array) ($filters['job_setup'] ?? []), true) ? 'checked' : '' }}>
                            {{ $setup }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider mb-3">Date Posted</p>
                    <div class="space-y-2">
                        @foreach (['' => 'Any Time', '24h' => 'Last 24 Hours', '7d' => 'Last 7 Days', '30d' => 'Last 30 Days'] as $value => $label)
                        <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium cursor-pointer">
                            <input type="radio" name="date_posted" value="{{ $value }}" class="w-4 h-4 accent-[#ED7A07]"
                                {{ ($filters['date_posted'] ?? '') === (string) $value ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="resetFilterSidebar(this.form)"
                    class="flex-1 border border-[#0E0F3B] text-[#0E0F3B] py-3 rounded text-sm font-bold tracking-widest hover:bg-[#0E0F3B] hover:text-white transition-colors uppercase">
                    Reset
                </button>
                <button type="submit"
                    class="flex-1 bg-[#0a0a23] text-white py-3 rounded text-sm font-bold tracking-widest hover:bg-black transition-colors uppercase">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!--JOB POST MODAL-->

    <div id="jobModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">

        <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl relative max-h-[90vh] overflow-y-auto my-8">

            <div class="h-48 w-full relative rounded-t-3xl overflow-hidden">
                <img id="myjob-modal-img" src="" class="w-full h-full object-cover">
                <div id="myjob-modal-img-overlay" class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>

                <button onclick="toggleModal()" class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 id="myjob-title" class="text-3xl font-bold text-[#1D264F] uppercase tracking-tighter"></h2>
                        <div class="flex flex-wrap items-center text-gray-600 mt-1 gap-x-4 gap-y-1">
                            <p id="myjob-company" class="font-semibold text-lg"></p>
                            <span class="flex items-center text-sm whitespace-nowrap"><i class="far fa-calendar-alt mr-2"></i> Posted&nbsp;<span id="myjob-posted"></span></span>
                        </div>
                        <p id="myjob-address" class="text-gray-500 font-medium"></p>
                    </div>

                    <span id="myjob-status" class="text-[10px] font-bold px-2 py-1 rounded-full uppercase whitespace-nowrap"></span>
                </div>

                <div class="mt-8 flex flex-col md:flex-row gap-8">
                    <div class="md:w-3/5">
                        <h3 class="font-bold text-[#0E0F3B] mb-3">Job Description:</h3>
                        <div id="myjob-description" class="text-gray-600 text-sm leading-relaxed text-justify job-description-content"></div>
                    </div>

                    <div class="md:w-2/5 space-y-2 text-[#1D264F]">
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Type:</span> <span id="myjob-type"></span></p>
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Setup:</span> <span id="myjob-setup"></span></p>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Recommended Course/Program:</p>
                    <p id="myjob-program" class="text-sm leading-snug text-gray-600 mt-1"></p>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Industry / Sector:</p>
                    <p id="myjob-industry" class="text-sm leading-snug text-gray-600 mt-1"></p>
                </div>

                <div class="mt-10 pt-6 border-t flex items-center justify-between">
                    <div class="text-gray-500 text-sm flex items-center font-semibold">
                        <i class="far fa-calendar-check mr-2"></i> Valid until <span id="myjob-valid" class="ml-1"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.post-job-modal', ['jobPoster' => $users])

    <!-- EDIT JOB MODAL -->
    <div id="editPostModal"
        class="fixed inset-0 z-[999] bg-black/70 flex items-center justify-center overflow-y-auto hidden p-4">

        <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl relative flex flex-col min-h-[600px] max-h-[90vh] overflow-y-auto my-8 rp-editpost-panel">

            @foreach($jobPostings as $job)
            <form id="editForm-{{ $job->job_posting_id }}" class="flex flex-col flex-1 hidden" action="{{ route('jobPosting.editJobPost', ['id' => $job->job_posting_id]) }}" method="post" enctype="multipart/form-data">
                @csrf

                <button type="button" onclick="closeEditPostModal()"
                    class="absolute top-11 right-8 text-gray-300 hover:text-gray-500 z-10">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>

                <div class="w-full pt-12 text-center">
                    <h2 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent rp-editpost-title">
                        EDIT JOB POST
                    </h2>
                </div>

                <div class="flex flex-col flex-1">

                    <div class="p-10 pt-6 space-y-4 rp-editpost-body">

                        <!-- THUMBNAIL -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase block mb-1">Thumbnail <span class="text-red-500">*</span></label>
                            <div id="editImageFrame-{{ $job->job_posting_id }}"
                                class="relative w-full h-48 rounded-lg overflow-hidden border-2 border-dashed border-[#0E0F3B] bg-slate-100 flex items-center justify-center transition-colors"
                                ondragover="handleDropzoneDragOver(event)" ondragleave="handleDropzoneDragLeave(event)" ondrop="handleDropzoneDrop(event, 'editJobImageInput-{{ $job->job_posting_id }}')">

                                <img id="editJobImagePreview-{{ $job->job_posting_id }}" src="{{ !$job->usesDefaultThumbnail() ? $job->thumbnailUrl() : '' }}" class="w-full h-full object-cover {{ !$job->usesDefaultThumbnail() ? '' : 'hidden' }}" />

                                <div id="editUploadPlaceholder-{{ $job->job_posting_id }}" class="text-slate-400 text-xs flex flex-col items-center gap-1 pointer-events-none {{ !$job->usesDefaultThumbnail() ? 'hidden' : '' }}">
                                    <i class="fas fa-cloud-arrow-up text-2xl text-[#C73D1A]"></i>
                                    <span>Drag & drop a thumbnail, or click Upload</span>
                                </div>

                                <input type="file" name="job_posting_image" id="editJobImageInput-{{ $job->job_posting_id }}" accept="image/*" class="hidden" onchange="previewJobImage(this, null, 'editUploadPlaceholder-{{ $job->job_posting_id }}', 'editJobImagePreview-{{ $job->job_posting_id }}', null)">

                                <button type="button" onclick="document.getElementById('editJobImageInput-{{ $job->job_posting_id }}').click()"
                                    class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-[#0E0F3B] text-[10px] font-bold px-3 py-1.5 rounded-full shadow uppercase">
                                    Upload
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Title <span class="text-red-500">*</span></label>
                                <input type="text" name="job_posting_title" value="{{ $job->job_posting_title }}"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" name="job_posting_company" value="{{ $job->job_posting_company }}"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                            </div>
                        </div>

                        @php $employerAddressesForEdit = optional($users->employer)->addresses ?? collect(); @endphp
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Company Address <span class="text-red-500">*</span></label>
                            @if ($employerAddressesForEdit->isNotEmpty())
                            <select name="job_posting_address"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white">
                                <option value="">Select an address</option>
                                @foreach ($employerAddressesForEdit as $savedAddress)
                                <option value="{{ $savedAddress->address }}" @selected($savedAddress->address === $job->job_posting_address)>{{ $savedAddress->address }}</option>
                                @endforeach
                                {{-- The job's current address might not be one of the saved ones (e.g. it was typed before this feature existed) — keep it selectable so saving doesn't silently change it. --}}
                                @if (!$employerAddressesForEdit->contains('address', $job->job_posting_address))
                                <option value="{{ $job->job_posting_address }}" selected>{{ $job->job_posting_address }} (current)</option>
                                @endif
                            </select>
                            @else
                            <input type="text" name="job_posting_address" value="{{ $job->job_posting_address }}"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Type <span class="text-red-500">*</span></label>
                                <select name="job_posting_employment_type"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none bg-no-repeat bg-[right_0.5rem_center] bg-[length:1em_1em]">
                                    @foreach (['Full-Time', 'Part-Time', 'Freelance'] as $jobTypeOption)
                                    <option value="{{ $jobTypeOption }}" @selected(strcasecmp((string) $job->job_posting_employment_type, $jobTypeOption) === 0)>{{ $jobTypeOption }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Setup <span class="text-red-500">*</span></label>
                                <select name="job_posting_setup"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none">
                                    @foreach (['Remote', 'On-Site', 'Hybrid'] as $jobSetupOption)
                                    <option value="{{ $jobSetupOption }}" @selected(strcasecmp((string) $job->job_posting_setup, $jobSetupOption) === 0)>{{ $jobSetupOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Recommended Course/Program <span class="text-red-500">*</span></label>

                            <div id="editCourseInputContainer-{{ $job->job_posting_id }}" class="space-y-2">
                                @foreach($job->programs as $index => $jobProgram)
                                <div class="flex items-center gap-3 course-row ">
                                    <select name="program[]" onchange="preventDuplicateCourseSelection(this)"
                                        class="flex-1 border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white w-full">
                                        <option value="" selected disabled>Select Undergraduate Program</option>
                                        @foreach($programs as $program)
                                        <option value="{{ $program->program_id }}" {{ $jobProgram->program_id == $program->program_id ? 'selected' : '' }}>
                                            {{ $program->program_name }}
                                        </option>
                                        @endforeach
                                    </select>

                                    @if($index === 0)
                                    <button type="button"
                                        onclick="addCourseField('editCourseInputContainer-{{ $job->job_posting_id }}', 'editCourseLimitMsg-{{ $job->job_posting_id }}')"
                                        class="bg-[#1D264F] text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#0E0F3B] transition-colors">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                    @else
                                    <button type="button" onclick="removeCourseField(this, 'editCourseLimitMsg-{{ $job->job_posting_id }}')"
                                        class="bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-700 transition-colors">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <p id="editCourseLimitMsg-{{ $job->job_posting_id }}" class="text-[9px] text-gray-400 italic hidden">
                                Maximum of 3 programs reached.
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Industry / Sector</label>
                            <select name="industry_id"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white">
                                <option value="">Select Industry</option>
                                @foreach($industries as $industry)
                                <option value="{{ $industry->industry_id }}" {{ $job->industry_id == $industry->industry_id ? 'selected' : '' }}>
                                    {{ $industry->industry_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        @include('partials.job-posting-skills-field', ['uid' => 'edit-' . $job->job_posting_id, 'selectedSkills' => $job->skills])

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Description <span class="text-red-500">*</span></label>
                            @include('partials.rich-text-editor', ['uid' => 'edit-' . $job->job_posting_id, 'fieldName' => 'job_posting_description', 'initialValue' => $job->job_posting_description])
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Closing / Validity Date <span class="text-red-500">*</span></label>
                                <input type="date" name="job_closing_date" value="{{ $job->job_closing_date }}"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] text-gray-400">
                            </div>

                            @php $jobHiredCount = $job->applicants->where('pivot.application_status', 'hired')->count(); @endphp
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Hiring Limit <span class="text-red-500">*</span></label>
                                <input type="number" name="hiring_limit" min="{{ $jobHiredCount }}" value="{{ $job->hiring_limit }}"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                                @if($jobHiredCount > 0)
                                <p class="text-[9px] text-gray-400">{{ $jobHiredCount }} already hired — can't go lower than that.</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-10 p-5 rp-editpost-actions">
                            <button type="button" onclick="closeEditPostModal()"
                                class="px-10 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-md font-bold text-sm hover:bg-[#0E0F3B] hover:text-white transition rp-editpost-btn">
                                CANCEL
                            </button>

                            <button type="submit"
                                class="px-12 py-2 bg-[#0E0F3B] text-white rounded-md font-bold text-sm hover:bg-blue-900 transition rp-editpost-btn">
                                SAVE CHANGES
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @endforeach

        </div>
    </div>

    @include('partials.footer-employer')
    @include('partials.action-dropdown-fix')

</body>

<script>
    // FILTER PANEL (slide-in "Filter by", same as the admin Job Placement Management page)
    function toggleFilterSidebar() {
        const sidebar = document.getElementById('filter-sidebar');
        const overlay = document.getElementById('filter-sidebar-overlay');
        const toggle = document.getElementById('filterSidebarToggle');
        const opening = sidebar.classList.contains('translate-x-full');
        sidebar.classList.toggle('translate-x-full', !opening);
        overlay.classList.toggle('hidden', !opening);
        document.body.style.overflow = opening ? 'hidden' : '';
        // Closed = inert: no off-screen tab stops, nothing announced by screen readers.
        sidebar.inert = !opening;
        sidebar.setAttribute('aria-hidden', opening ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) document.getElementById('filterLocation').focus({ preventScroll: true });
        else toggle.focus({ preventScroll: true });
    }

    // Clears only the panel's own fields (the toolbar's search/status ride
    // along as hidden inputs) and applies right away.
    function resetFilterSidebar(form) {
        form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        form.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
        const anyTime = form.querySelector('input[type="radio"][value=""]');
        if (anyTime) anyTime.checked = true;
        form.submit();
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !document.getElementById('filter-sidebar').classList.contains('translate-x-full')) {
            toggleFilterSidebar();
        }
    });

    // ROW 3-DOT MENUS (same menu as the admin Job Placement Management table).
    // positionFixedDropdown() (partials/action-dropdown-fix) pins the open menu
    // with position:fixed so the table's horizontal-scroll wrapper can't clip
    // it, flipping it upward near the bottom of the screen.
    function closeActionMenus() {
        document.querySelectorAll('.action-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open');
            menu.style.position = menu.style.top = menu.style.left = menu.style.right = menu.style.marginTop = '';
            const trigger = menu.previousElementSibling;
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.menu-button');
        if (!trigger) {
            if (!e.target.closest('.action-dropdown')) closeActionMenus();
            return;
        }
        const menu = trigger.nextElementSibling;
        const wasOpen = menu.classList.contains('open');
        closeActionMenus();
        if (wasOpen) return;
        menu.classList.add('open');
        positionFixedDropdown(trigger, menu);
        trigger.setAttribute('aria-expanded', 'true');
    });

    // Keep an open (position:fixed) menu attached to its row while the page or
    // the table scrolls / resizes; close it once its row is out of view.
    function repositionOpenMenu() {
        const menu = document.querySelector('.action-dropdown.open');
        if (!menu) return;
        const trigger = menu.previousElementSibling;
        const r = trigger.getBoundingClientRect();
        const box = (trigger.closest('.table-scroll') || document.documentElement).getBoundingClientRect();
        const visible = r.bottom > Math.max(0, box.top) && r.top < Math.min(window.innerHeight, box.bottom)
            && r.right > Math.max(0, box.left) && r.left < Math.min(window.innerWidth, box.right);
        if (visible) positionFixedDropdown(trigger, menu);
        else closeActionMenus();
    }
    window.addEventListener('scroll', repositionOpenMenu, true);
    window.addEventListener('resize', repositionOpenMenu);

    // Tooltip on a disabled "Applicants" button. The table's horizontal-scroll
    // box would clip it, so it is position: fixed and placed here: above the
    // button (below it when the sticky header is in the way), kept inside the
    // screen, with its arrow on the button's middle.
    function placeNoApplicantsTip(wrap) {
        const tip = wrap.querySelector('.noapp-tip');
        const arrow = tip.querySelector('.noapp-tip-arrow');
        const r = wrap.getBoundingClientRect();
        const header = document.querySelector('header.sticky');
        const minTop = (header ? Math.max(0, header.getBoundingClientRect().bottom) : 0) + 4;
        const w = tip.offsetWidth, h = tip.offsetHeight;
        const left = Math.max(8, Math.min(r.right - w, window.innerWidth - w - 8));
        const below = r.top - h - 8 < minTop;
        tip.classList.toggle('is-below', below);
        tip.style.left = left + 'px';
        tip.style.top = (below ? r.bottom + 8 : r.top - h - 8) + 'px';
        arrow.style.right = 'auto';
        arrow.style.left = Math.min(Math.max(r.left + r.width / 2 - left - 4, 6), w - 14) + 'px';
    }
    ['mouseover', 'focusin'].forEach(type => document.addEventListener(type, e => {
        const wrap = e.target.closest && e.target.closest('.noapp-tip-wrap');
        if (wrap) placeNoApplicantsTip(wrap);
    }));
    // Follow the button while the page/table scrolls; drop focus once it's out of view.
    function repositionNoApplicantsTips() {
        document.querySelectorAll('.noapp-tip-wrap:hover, .noapp-tip-wrap:focus').forEach(wrap => {
            const r = wrap.getBoundingClientRect();
            const box = (wrap.closest('.table-scroll') || document.documentElement).getBoundingClientRect();
            const inView = r.bottom > Math.max(0, box.top) && r.top < Math.min(window.innerHeight, box.bottom)
                && r.right > Math.max(0, box.left) && r.left < Math.min(window.innerWidth, box.right);
            if (inView) placeNoApplicantsTip(wrap);
            else wrap.blur();
        });
    }
    window.addEventListener('scroll', repositionNoApplicantsTips, true);
    window.addEventListener('resize', repositionNoApplicantsTips);

    // Phones/tablets "Sort by": runs the same column sort as clicking a table
    // header (partials/table-sort), which the card view has no headers for.
    (function () {
        const select = document.getElementById('myJobsMobileSort');
        const tbody = document.getElementById('my-jobs-tbody');
        if (!select || !tbody) return;
        [...tbody.rows].forEach((row, i) => { row.dataset.defaultOrder = i; });
        select.addEventListener('change', () => {
            const headers = tbody.closest('table').querySelectorAll('thead th');
            if (!select.value) {
                [...tbody.rows].sort((a, b) => a.dataset.defaultOrder - b.dataset.defaultOrder).forEach(row => tbody.appendChild(row));
                headers.forEach(th => th.removeAttribute('data-sort-dir'));
            } else {
                const [col, dir] = select.value.split(':');
                const th = headers[col];
                // A header click flips its current direction, so set the opposite first.
                if (dir === 'asc') th.removeAttribute('data-sort-dir');
                else th.dataset.sortDir = 'asc';
                th.click();
            }
            // The orange "sorted column" highlight is for the desktop table, not the cards.
            tbody.querySelectorAll('.pv-sort-active').forEach(cell => cell.classList.remove('pv-sort-active'));
        });
    })();
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeActionMenus();
    });

    // Job post modal
    function toggleModal() {
        const modal = document.getElementById('jobModal');
        modal.classList.toggle('hidden');
        document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
    }

    // Deep-link from a "job posting approved/rejected" notification
    // (?job=123) — open that specific posting's view modal directly.
    // Silently no-ops if it's on a page the pagination hasn't loaded.
    document.addEventListener('DOMContentLoaded', () => {
        const openJobId = new URLSearchParams(window.location.search).get('job');
        if (openJobId) {
            const el = document.querySelector('[data-job-id="' + openJobId + '"]');
            if (el) el.click();
            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete('job');
            history.replaceState(history.state, '', cleanUrl);
        }
    });

    // A row's View (eye) button opens this same modal populated with that
    // job's real data (read off the button's own data-* attributes) —
    // a quick, read-only way to see the full post without opening Edit.
    function openMyJobViewModal(el) {
        const myJobImg = document.getElementById('myjob-modal-img');
        const myJobImgOverlay = document.getElementById('myjob-modal-img-overlay');
        myJobImg.src = el.dataset.image;
        if (el.dataset.usesDefaultImage === '1') {
            myJobImg.style.opacity = el.dataset.imageOpacity;
            myJobImgOverlay.className = 'absolute inset-0';
            myJobImgOverlay.style.backgroundColor = el.dataset.imageOverlayColor;
            myJobImgOverlay.style.opacity = el.dataset.imageOverlayOpacity;
        } else {
            myJobImg.style.opacity = '';
            myJobImgOverlay.className = 'absolute inset-0 bg-blue-900/40 mix-blend-multiply';
            myJobImgOverlay.style.backgroundColor = '';
            myJobImgOverlay.style.opacity = '';
        }
        document.getElementById('myjob-title').textContent = el.dataset.title;
        document.getElementById('myjob-company').textContent = el.dataset.company;
        document.getElementById('myjob-posted').textContent = el.dataset.posted;
        document.getElementById('myjob-address').textContent = el.dataset.address;
        document.getElementById('myjob-description').innerHTML = el.dataset.description;
        document.getElementById('myjob-type').textContent = el.dataset.type;
        document.getElementById('myjob-setup').textContent = el.dataset.setup;
        document.getElementById('myjob-program').textContent = el.dataset.programs;
        document.getElementById('myjob-industry').textContent = el.dataset.industry;
        document.getElementById('myjob-valid').textContent = el.dataset.valid;

        // Same expired / pending / approved status as the row's badge (JobPosting::myPostStatus()).
        const statusEl = document.getElementById('myjob-status');
        const statusColors = { approved: 'bg-green-100 text-green-700', pending: 'bg-amber-100 text-amber-700', expired: 'bg-slate-100 text-slate-500' };
        const status = el.dataset.status || (el.dataset.approved === '1' ? 'approved' : 'pending');
        statusEl.textContent = el.dataset.statusLabel || (status === 'approved' ? 'Approved' : 'Pending Approval');
        statusEl.className = 'text-[10px] font-bold px-2 py-1 rounded-full uppercase whitespace-nowrap ' + (statusColors[status] || statusColors.pending);

        document.getElementById('jobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('jobModal');
        if (event.target == modal) toggleModal();
    }

    // "Post a New Job" modal JS (open/close, image preview, add/remove
    // program row, client validation, confirm/pending flow) now lives in
    // partials/post-job-modal.blade.php, shared with jobBoard.blade.php.
    // previewJobImage()/addCourseField()/removeCourseField() from that
    // partial are still what the Edit Job Post form below calls with its
    // own explicit ids (editJobImagePreview-{id}, editCourseInputContainer-{id}, etc.).

    // EDIT JOB POST MODAL
    let currentEditId = null;

    function openEditPostModal(jobId) {
        // Hide all forms first
        document.querySelectorAll('[id^="editForm-"]').forEach(f => f.classList.add('hidden'));

        // Show the form for this job
        document.getElementById('editForm-' + jobId).classList.remove('hidden');

        currentEditId = jobId;
        document.getElementById('editPostModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditPostModal() {
        document.getElementById('editPostModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('editPostModal')) closeEditPostModal();
    });
</script>

</html>