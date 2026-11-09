<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Job Board</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <link rel="preload" as="image" href="{{ asset('assets/heroSection.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

    /* My Applications' status filter sits beside the search box: same height as it. */
    .jb-status-filter .multiselect-trigger-btn { min-height: 42px; font-size: 0.8125rem; }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    #job-list {
        overflow: visible;
    }

    .tab-active {
        color: #C73D1A;
        border-bottom: 3px solid #C73D1A;
    }

    #postJobModal>div {
        overflow-y: auto;
        max-height: 85vh;
        max-height: 85dvh; /* compact: never fills the screen (matches the panel class) */
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #postJobModal>div::-webkit-scrollbar {
        display: none;
    }

    /* ── Desktop (>= 1024px, the site's desktop line): job list + details panel ──
       The panel is always there on the right — an empty state until a job is
       picked — and takes the larger share (~60%); the list of compact cards
       (partials/job-post-card) the smaller (~40%). The details inside are the
       shared #jobDetailsContent from partials/job-detail-modal (moved here on
       desktop). Phones/tablets keep the full cards + the details modal. */
    @media (min-width: 1024px) {
        #jobBoardMain { max-width: 96rem; }

        #jobBoardSplit { display: flex; align-items: flex-start; }
        #jobListColumn { flex: 1 1 auto; min-width: 0; }

        #jobDetailPanel {
            /* 60% of the board: (page width, capped at 96rem, minus the 3rem page
               padding and the 1.5rem gap) x 0.6. (The job board hides scrollbars,
               so 100vw is the visible width.) */
            --jb-panel-w: calc((min(100vw, 96rem) - 4.5rem) * 0.6);
            position: sticky;
            top: var(--jb-panel-top, 6rem);
            flex: 0 0 var(--jb-panel-w);
            width: var(--jb-panel-w);
            margin-left: 1.5rem;
            height: calc(100vh - var(--jb-panel-top, 6rem) - 1rem);
        }
        #jobDetailPanel .jb-panel-card { height: 100%; }

        /* Details inside the panel: scroll on their own; the panel header stays put.
           The modal's own close (x) on the photo is replaced by the header's button. */
        #jobDetailPanelBody { flex: 1 1 auto; min-height: 0; display: flex; container-type: inline-size; container-name: jobdetail; }
        #jobDetailPanel #jobDetailsContent { flex: 1 1 auto; min-height: 0; }
        #jobDetailPanel #jobDetailsContent .jd-hero > button[onclick="toggleModal()"] { display: none; }
        #jobDetailPanel #jobDetailsContent .jd-hero { height: 14rem; }
        #jobDetailPanel #jobDetailsContent .jd-body { padding: 2rem; }
        #jobDetailPanel #jobDetailsContent #modal-title { font-size: 1.75rem; line-height: 2.125rem; }
        /* The card that's open in the panel */
        .jb-list-card.is-selected { box-shadow: 0 0 0 2px #C73D1A, 0 10px 20px -6px rgba(199, 61, 26, 0.25); background-color: #fffaf8; }
        /* Job title on hover / when selected: the system gradient (each line of a
           two-line title gets the whole gradient). */
        .jb-list-card:hover .jb-list-title-text,
        .jb-list-card:focus-visible .jb-list-title-text,
        .jb-list-card.is-selected .jb-list-title-text {
            background-image: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        .jb-list-card.is-selected .jb-list-arrow { background: #C73D1A; border-color: #C73D1A; color: #fff; }

        /* The admin pagination bar, fitted to the narrower job list: labels never
           break mid-phrase; on a small laptop the page buttons drop to their own line. */
        #jobListColumn [data-pv-pagination] { font-size: 0.8125rem; flex-wrap: wrap; column-gap: 0.5rem; row-gap: 0.5rem; }
        #jobListColumn [data-pv-pagination] span { white-space: nowrap; }
        #jobListColumn [data-pv-pagination] [data-pv-per-page] { padding-left: 0.75rem; padding-right: 2rem; }
        #jobListColumn [data-pv-pagination] > div:last-child { gap: 0.5rem; margin-left: auto; }
    }

    /* A wide panel keeps the modal's own two-column layout (description | details);
       only a narrow one (small desktop) stacks it. */
    @container jobdetail (max-width: 640px) {
        #jobDetailPanel #jobDetailsContent .jd-hero { height: 11rem; }
        #jobDetailPanel #jobDetailsContent .jd-body { padding: 1.5rem; }
        #jobDetailPanel #jobDetailsContent #modal-title { font-size: 1.375rem; line-height: 1.75rem; }
        #jobDetailPanel #jobDetailsContent .jd-split { flex-direction: column; gap: 1.5rem; margin-top: 1.5rem; }
        #jobDetailPanel #jobDetailsContent .jd-col { width: 100%; }
        #jobDetailPanel #jobDetailsContent .jd-row { flex-direction: column; align-items: flex-start; }
    }

    /* Phones/tablets: the cards' hover tooltips (invisible, never shown without a mouse)
       stuck a few px past the screen edge and let the page drag sideways. clip (not hidden)
       doesn't create a scroll box, so nothing else about the layout changes. */
    @media (max-width: 1023px) {
        #jobBoardMain { overflow-x: clip; }
    }
</style>


<body class="min-h-screen flex flex-col">
    @php
    $current_page = Route::currentRouteName();
    $activeTab ??= 'board';
    $filters ??= [];
    $activeTabRoute = match ($activeTab) {
        'bookmarks' => route('jobPosting.bookmarks'),
        'applications' => route('jobPosting.myApplications'),
        default => route('jobPosting.jobBoard'),
    };
    @endphp
    @if (!$user)
    @include('partials.header-general')
    @elseif($user->user_role === 'alumni')
    @include('partials.header-alumni')
    @else
    @include('partials.header-employer')
    @endif

    {{-- Shared hero banner (same title indent on every page) --}}
    @include('partials.hero', ['title' => match($activeTab ?? 'board') { 'bookmarks' => 'My Bookmarked Jobs', 'applications' => 'My Job Applications', default => 'Job Board' }])

    @if($user && $user->user_role === 'alumni')
    <nav class="bg-white border-b sticky top-0 z-10 shadow-md">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex justify-start space-x-8 uppercase text-sm font-bold tracking-wide rp-tabs">
                <a href="{{ route('jobPosting.jobBoard') }}" class="py-4 transition-all {{ $activeTab === 'board' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Job Board</a>
                <a href="{{ route('jobPosting.myApplications') }}" class="py-4 transition-all {{ $activeTab === 'applications' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">My Applications</a>
                <a href="{{ route('jobPosting.bookmarks') }}" class="py-4 transition-all {{ $activeTab === 'bookmarks' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Bookmarks</a>
            </div>
        </div>
    </nav>
    @endif

    @include('partials.success')

    @include('partials.error')
    <main id="jobBoardMain" class="w-full max-w-5xl mx-auto p-6 flex-1">
        <!-- SEARCH & FILTER -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-8">
            <form method="GET" action="{{ $activeTabRoute }}">

                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-1">
                        <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-[#C73D1A]">
                            <i class="fas fa-search"></i>
                        </button>
                        {{-- Searches as you type, with every filter in this form (partials/live-search). --}}
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search job title or company" autocomplete="off"
                            data-live-search data-live-search-target="#jobListColumn"
                            class="w-full pl-11 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    </div>

                    @if ($activeTab === 'applications' && !empty($statusOptions))
                    {{-- My Applications: filter by application status (checkboxes; applied with Apply Filters like the others). --}}
                    <div class="jb-status-filter md:w-64 shrink-0">
                        @include('partials.multiselect-filter', [
                            'name' => 'status',
                            'icon' => 'fas fa-clipboard-check',
                            'placeholder' => 'Application Status',
                            'labelPrefix' => 'Status',
                            'options' => $statusOptions,
                            'selected' => $filters['status'] ?? [],
                        ])
                    </div>
                    @endif

                    <button type="button" onclick="toggleMoreFilters()"
                        class="more-filters-toggle flex items-center justify-center gap-2 px-6 py-2 rounded-full border font-bold text-xs uppercase tracking-wide shrink-0 transition-colors {{ $moreFiltersActive ? 'bg-[#0E0F3B] text-white border-[#0E0F3B]' : 'border-gray-300 text-gray-600 hover:border-[#C73D1A] hover:text-[#C73D1A]' }}">
                        <i class="fas fa-sliders"></i>
                        <span>More Filter</span>
                        <i class="fas fa-chevron-down text-[10px] transition-transform more-filters-chevron {{ $moreFiltersActive ? 'rotate-180' : '' }}"></i>
                    </button>
                </div>

                <div id="moreFiltersPanel" class="{{ $moreFiltersActive ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 mt-4 pt-4 border-t border-gray-100">

                    @include('partials.multiselect-filter', [
                        'name' => 'industry',
                        'icon' => 'fas fa-industry',
                        'placeholder' => 'Industry',
                        'options' => $industries->pluck('industry_name', 'industry_id'),
                        'selected' => $filters['industry'] ?? [],
                    ])

                    @include('partials.multiselect-filter', [
                        'name' => 'job_type',
                        'icon' => 'fas fa-briefcase',
                        'placeholder' => 'Job Type',
                        'options' => ['Full-Time' => 'Full-Time', 'Part-Time' => 'Part-Time', 'Freelance' => 'Freelance'],
                        'selected' => $filters['job_type'] ?? [],
                    ])

                    @include('partials.multiselect-filter', [
                        'name' => 'job_setup',
                        'icon' => 'fas fa-building',
                        'placeholder' => 'Job Setup',
                        'options' => ['On-Site' => 'On-Site', 'Remote' => 'Remote', 'Hybrid' => 'Hybrid'],
                        'selected' => $filters['job_setup'] ?? [],
                    ])

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <input type="text" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Location" autocomplete="off"
                            data-live-search data-live-search-target="#jobListColumn"
                            class="w-full pl-11 pr-4 py-1.5 border rounded-full text-xs focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <select name="date_posted" class="w-full pl-11 pr-10 py-1.5 border rounded-full bg-white text-xs appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                            <option value="">Date Posted</option>
                            <option value="24h" {{ ($filters['date_posted'] ?? '') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="7d" {{ ($filters['date_posted'] ?? '') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30d" {{ ($filters['date_posted'] ?? '') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </span>
                    </div>

                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-end gap-4">
                    @if (array_filter($filters))
                    <a href="{{ $activeTabRoute }}" class="text-xs font-bold text-gray-400 hover:text-[#C73D1A]">
                        <i class="fas fa-times mr-1"></i>CLEAR FILTERS
                    </a>
                    @endif
                    <button type="submit"
                        class="flex items-center justify-center gap-2 px-6 py-2 rounded-full bg-[#C73D1A] hover:bg-[#a83215] text-white font-bold text-xs uppercase tracking-wide shrink-0 transition-colors">
                        <i class="fas fa-filter"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </form>
        </div>

        @if($user && $user->user_role === 'employer')
        <div class="flex justify-end p-4">
            <button
                onclick="openPostJobModal()"
                class="flex items-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-6 py-2.5 rounded-lg font-bold text-sm tracking-widest shadow-lg transition-all transform hover:scale-105 active:scale-95">
                <i class="fas fa-plus text-xs"></i>
                <span>POST A NEW JOB</span>
            </button>
        </div>
        @endif
        <div class="{{ session('noResume', 'hidden') }} bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <h3>You must have a resume in your profile to apply for jobs.</h3>
        </div>
        <div id="jobBoardSplit">
        <div id="jobListColumn">
        <div id="job-list" class="space-y-6 lg:space-y-3">
            <!--JOB POST CONTAINER -->

            @forelse($jobPostings as $job)
            @include('partials.job-post-card', ['job' => $job])
            @empty
            <div class="bg-white rounded-3xl shadow-md p-12 text-center text-gray-500">
                @if($activeTab === 'bookmarks')
                <i class="far fa-bookmark text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">You haven't bookmarked any jobs yet.</p>
                @elseif($activeTab === 'applications')
                <i class="fas fa-file-circle-check text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">{{ array_filter($filters) ? 'No applications match your filters.' : "You haven't applied to any jobs yet." }}</p>
                @else
                <i class="fas fa-briefcase text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">No job postings match your search.</p>
                @endif
            </div>
            @endforelse

        </div>

        {{-- Same pagination bar as the admin management pages: rows per page, record
             count, first / previous / next / last (keeps the search filters and tab). --}}
        @if ($jobPostings->total() > 0)
        <div class="bg-white rounded-2xl shadow-md px-5 lg:px-4 pb-4 mt-6 lg:mt-4">
            @include('partials.table-pagination-bar', ['id' => 'jobBoardPagination', 'mode' => 'reload', 'paginator' => $jobPostings])
        </div>
        @endif
        </div>

        {{-- Desktop details panel (>= 1024px): always on the right — an empty state
             until a job is clicked. Hidden on smaller screens, where the same details
             open in the modal instead (partials/job-detail-modal). --}}
        <aside id="jobDetailPanel" class="hidden lg:block" role="complementary" aria-label="Job details" tabindex="-1">
            <div class="jb-panel-card flex flex-col bg-white rounded-3xl shadow-md border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-gray-100 shrink-0 min-h-[3.75rem]">
                    {{-- System gradient title (same as the page's other section titles). --}}
                    <h2 class="inline-block text-lg font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Job Details</h2>
                    <button type="button" id="jobDetailPanelClose" onclick="toggleModal()" aria-label="Close job details"
                        class="hidden w-9 h-9 rounded-full text-gray-400 hover:text-[#0E0F3B] hover:bg-gray-100 flex items-center justify-center transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A]">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div id="jobDetailPanelBody">
                    {{-- Empty state (no job picked yet) — brand navy, not gray. Centered in the
                         part of the panel that's on screen (see centerEmptyState() below): the
                         panel is taller than what shows before the page is scrolled. --}}
                    <div id="jobDetailEmpty" class="flex-1 relative">
                        <div class="jb-empty-inner absolute inset-x-0 top-1/2 -translate-y-1/2 flex flex-col items-center text-center px-10">
                            <span class="w-20 h-20 rounded-2xl bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center">
                                <i class="fa-solid fa-briefcase text-3xl"></i>
                            </span>
                            <p class="mt-5 text-2xl font-bold text-[#0E0F3B]">Select a job post</p>
                            <p class="mt-2 max-w-sm text-base text-[#1D264F]"><i class="fa-solid fa-arrow-left text-[#C73D1A] mr-1.5"></i> Click any job post on the left to see its full details here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        </div>
    </main>

    @include('partials.job-detail-modal')
    @include('partials.company-review-modal')

    @if($user && $user->user_role === 'alumni')
    @include('partials.job-apply-modal')
    @endif

    @if($user && $user->user_role === 'employer')
    @include('partials.post-job-modal', ['jobPoster' => $user])
    @endif

    @if(!$user)
    @include('partials.footer')
    @elseif($user->user_role === 'alumni')
    @include('partials.footer-alumni')
    @else
    @include('partials.footer-employer')
    @endif

</body>

<script>
    // Deep-link from a job/application-related notification (?job=123) —
    // open that specific job's detail modal directly (works on the Job
    // Board, My Applications, and Bookmarks tabs alike, since they all
    // render partials/job-post-card.blade.php with the same data-job-id).
    // Silently no-ops if that job isn't on the currently-rendered page.
    document.addEventListener('DOMContentLoaded', () => {
        const openJobId = new URLSearchParams(window.location.search).get('job');
        if (openJobId) {
            const el = document.querySelector('[data-job-id="' + openJobId + '"]');
            if (el) el.click();
        }
    });

    // Card Share button — copies that job's own link (jobShareUrl()/
    // copyTextToClipboard() live in partials/job-detail-modal, included above).
    function copyJobLink(button, jobId) {
        copyTextToClipboard(jobShareUrl(jobId)).then(() => {
            const tooltip = button.nextElementSibling;
            tooltip.classList.remove('invisible', 'opacity-0');
            tooltip.classList.add('visible', 'opacity-100', '-translate-y-1');

            setTimeout(() => {
                tooltip.classList.add('invisible', 'opacity-0');
                tooltip.classList.remove('visible', 'opacity-100', '-translate-y-1');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    // "Post a New Job" modal JS (open/close, image preview, add/remove
    // program row, client validation, confirm/pending flow) now lives in
    // partials/post-job-modal.blade.php, shared with jobPostings.blade.php.

    // ── Desktop details panel ─────────────────────────────────────────────
    // Always visible on desktop: an empty state until a job is picked.
    // partials/job-detail-modal.blade.php decides panel vs. modal and fills the
    // shared details; this shows/clears them in the panel.
    window.jobBoardPanel = (function () {
        const split = document.getElementById('jobBoardSplit');
        const panel = document.getElementById('jobDetailPanel');
        const closeBtn = document.getElementById('jobDetailPanelClose');
        const emptyState = document.getElementById('jobDetailEmpty');
        if (!split || !panel) return null;

        // Stick just below the page's sticky header (its height differs per role).
        function setTop() {
            const header = document.querySelector('header.sticky');
            const h = header ? header.getBoundingClientRect().height : 80;
            panel.style.setProperty('--jb-panel-top', (h + 16) + 'px');
        }

        // Highlights the compact card whose details are showing.
        function markSelected(trigger) {
            document.querySelectorAll('#job-list .jb-list-card.is-selected').forEach(c => { c.classList.remove('is-selected'); c.removeAttribute('aria-current'); });
            const jobId = trigger?.dataset?.jobId;
            const card = jobId ? document.querySelector('#job-list .jb-list-card[data-job-id="' + jobId + '"]') : null;
            if (card) { card.classList.add('is-selected'); card.setAttribute('aria-current', 'true'); }
        }

        function open(title, trigger) {
            setTop();
            markSelected(trigger);
            emptyState.classList.add('hidden');
            closeBtn.classList.remove('hidden');
            panel.focus({ preventScroll: true });
        }

        // Back to the empty state (the details go back into the hidden modal).
        function close() {
            placeJobDetails(false);
            emptyState.classList.remove('hidden');
            centerEmptyState();
            closeBtn.classList.add('hidden');
            panel.setAttribute('aria-label', 'Job details');
            markSelected(null);
        }

        // Centers the empty state in the part of the panel that's on screen. Before the
        // page is scrolled only the panel's top shows, so its true middle would be below
        // the fold; once the panel is fully in view this is simply its middle.
        const emptyInner = emptyState.querySelector('.jb-empty-inner');
        let centerQueued = false;
        function centerEmptyState() {
            centerQueued = false;
            if (emptyState.classList.contains('hidden') || !emptyState.offsetHeight) return;
            const box = emptyState.getBoundingClientRect();
            const visibleTop = Math.max(box.top, 0);
            const visibleBottom = Math.min(box.bottom, window.innerHeight);
            const half = emptyInner.offsetHeight / 2;
            // Keep the whole message inside the panel (16px margin). On a short screen,
            // where less of the panel shows than the message needs, it starts right
            // under the panel's header so its title is still in view.
            const margin = Math.min(16, Math.max(0, (visibleBottom - visibleTop - 2 * half) / 2));
            const center = Math.min(Math.max((visibleTop + visibleBottom) / 2 - box.top, half + margin), box.height - half - margin);
            emptyInner.style.top = center + 'px';
        }
        function queueCenter() {
            if (!centerQueued) { centerQueued = true; requestAnimationFrame(centerEmptyState); }
        }
        window.addEventListener('scroll', queueCenter, { passive: true });

        setTop();
        centerEmptyState();
        window.addEventListener('resize', function () { setTop(); queueCenter(); });
        window.addEventListener('load', centerEmptyState);

        // Escape clears the panel — unless a modal (Apply, review, success…) is open on top of the page.
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || !(jobDetailsState.open && jobDetailsState.mode === 'panel')) return;
            const modalOpen = [...document.querySelectorAll('div.fixed.inset-0[id$="Modal"], div.fixed.inset-0[id$="Overlay"]')].some(m => !m.classList.contains('hidden') && getComputedStyle(m).display !== 'none');
            if (!modalOpen) toggleModal();
        });

        return { open, close };
    })();

    // MORE FILTER PANEL
    function toggleMoreFilters() {
        document.getElementById('moreFiltersPanel').classList.toggle('hidden');
        document.querySelector('.more-filters-chevron').classList.toggle('rotate-180');
    }
</script>

@include('partials.staged-multiselect')
@include('partials.live-search')

</html>
