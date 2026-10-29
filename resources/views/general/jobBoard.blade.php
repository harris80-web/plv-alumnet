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

    /* ── Desktop "View Details" side panel (>= 1024px, the site's desktop line) ──
       Docked beside the job list, not an overlay: the page widens and the list
       shrinks while it's open. The panel holds the FULL details, so it takes the
       larger share (~60%) and the job list the smaller (~40%). The details inside
       are the shared #jobDetailsContent from partials/job-detail-modal (moved here
       on desktop). */
    @media (min-width: 1024px) {
        #jobBoardMain { transition: max-width 280ms ease-out; }
        #jobBoardMain.is-panel-open { max-width: 96rem; }

        #jobBoardSplit { display: flex; align-items: flex-start; }
        #jobListColumn { flex: 1 1 auto; min-width: 0; container-type: inline-size; container-name: joblist; }

        #jobDetailPanel {
            /* 60% of the open board: (page width, capped at 96rem, minus the 3rem page
               padding and the 1.5rem gap) x 0.6. A fixed length, so the details don't
               reflow while the column animates open. (The job board hides scrollbars,
               so 100vw is the visible width.) */
            --jb-panel-w: calc((min(100vw, 96rem) - 4.5rem) * 0.6);
            position: sticky;
            top: var(--jb-panel-top, 6rem);
            flex: 0 0 auto;
            width: 0;
            margin-left: 0;
            height: calc(100vh - var(--jb-panel-top, 6rem) - 1rem);
            overflow: hidden;
            transition: width 280ms ease-out, margin-left 280ms ease-out;
        }
        #jobBoardSplit.is-panel-open #jobDetailPanel { width: var(--jb-panel-w); margin-left: 1.5rem; }

        /* Slides in from the right as the column opens */
        #jobDetailPanel .jb-panel-card {
            width: var(--jb-panel-w);
            height: 100%;
            transform: translateX(2rem);
            opacity: 0;
            transition: transform 280ms ease-out, opacity 200ms ease-out;
        }
        #jobBoardSplit.is-panel-open #jobDetailPanel .jb-panel-card { transform: none; opacity: 1; }

        /* Details inside the panel: scroll on their own; the panel header stays put.
           The modal's own close (x) on the photo is replaced by the header's close button. */
        #jobDetailPanelBody { flex: 1 1 auto; min-height: 0; display: flex; container-type: inline-size; container-name: jobdetail; }
        #jobDetailPanel #jobDetailsContent { flex: 1 1 auto; min-height: 0; }
        #jobDetailPanel #jobDetailsContent .jd-hero > button[onclick="toggleModal()"] { display: none; }
        #jobDetailPanel #jobDetailsContent .jd-hero { height: 14rem; }
        #jobDetailPanel #jobDetailsContent .jd-body { padding: 2rem; }
        #jobDetailPanel #jobDetailsContent #modal-title { font-size: 1.75rem; line-height: 2.125rem; }

        /* The card that's open in the panel */
        #jobBoardSplit.is-panel-open .rp-job-card.is-selected { box-shadow: 0 0 0 2px #C73D1A, 0 10px 15px -3px rgb(0 0 0 / 0.1); }
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

    /* The narrower job list (panel open) stacks each card: photo on top, actions wrap. */
    @container joblist (max-width: 720px) {
        #jobListColumn .rp-job-card { flex-direction: column; min-height: 0; }
        #jobListColumn .rp-job-card > div:first-child { width: 100%; height: 10rem; border-radius: 1.5rem 1.5rem 0 0; }
        #jobListColumn .rp-job-card .rp-job-body { padding: 1.25rem; }
        #jobListColumn .rp-job-card h2 { font-size: 1.25rem; line-height: 1.75rem; }
        #jobListColumn .rp-job-card .rp-job-actions { flex-wrap: wrap; gap: 0.75rem 1rem; }
    }
    @container joblist (max-width: 460px) {
        #jobListColumn .rp-job-card .rp-job-meta { grid-template-columns: 1fr; }
        #jobListColumn .rp-job-card .rp-job-actions-buttons { flex-wrap: wrap; row-gap: 0.5rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        #jobBoardMain, #jobDetailPanel, #jobDetailPanel .jb-panel-card { transition: none !important; }
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

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">
                @if($activeTab === 'bookmarks')
                My Bookmarked Jobs
                @elseif($activeTab === 'applications')
                My Job Applications
                @else
                Job Board
                @endif
            </h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

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
        <div id="job-list" class="space-y-6">
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
                <p class="font-semibold">You haven't applied to any jobs yet.</p>
                @else
                <i class="fas fa-briefcase text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">No job postings match your search.</p>
                @endif
            </div>
            @endforelse

        </div>

        {{ $jobPostings->onEachSide(1)->links('partials.pagination') }}
        </div>

        {{-- Desktop "View Details" panel (>= 1024px). Hidden on smaller screens, where
             the same details open in the modal instead (partials/job-detail-modal). --}}
        <aside id="jobDetailPanel" class="hidden lg:block" role="complementary" aria-label="Job details" tabindex="-1" inert>
            <div class="jb-panel-card flex flex-col bg-white rounded-3xl shadow-md border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-gray-100 shrink-0">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-[#C73D1A]">Job Details</p>
                    <button type="button" id="jobDetailPanelClose" onclick="toggleModal()" aria-label="Close job details"
                        class="w-9 h-9 rounded-full text-gray-400 hover:text-[#0E0F3B] hover:bg-gray-100 flex items-center justify-center transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A]">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div id="jobDetailPanelBody"></div>
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

    // ── Desktop "View Details" panel ─────────────────────────────────────
    // partials/job-detail-modal.blade.php decides panel vs. modal and fills the
    // shared details; this only opens/closes the docked column around them.
    window.jobBoardPanel = (function () {
        const split = document.getElementById('jobBoardSplit');
        const main = document.getElementById('jobBoardMain');
        const panel = document.getElementById('jobDetailPanel');
        if (!split || !panel) return null;

        // Stick just below the page's sticky header (its height differs per role).
        function setTop() {
            const header = document.querySelector('header.sticky');
            const h = header ? header.getBoundingClientRect().height : 80;
            panel.style.setProperty('--jb-panel-top', (h + 16) + 'px');
        }

        function markSelected(trigger) {
            document.querySelectorAll('#job-list .rp-job-card.is-selected').forEach(c => c.classList.remove('is-selected'));
            trigger?.closest('.rp-job-card')?.classList.add('is-selected');
        }

        function open(title, trigger) {
            setTop();
            markSelected(trigger);
            main.classList.add('is-panel-open');
            split.classList.add('is-panel-open');
            panel.inert = false;
            panel.removeAttribute('aria-hidden');
            panel.focus({ preventScroll: true });
        }

        function close() {
            split.classList.remove('is-panel-open');
            main.classList.remove('is-panel-open');
            panel.inert = true;
            panel.setAttribute('aria-hidden', 'true');
            markSelected(null);
        }

        window.addEventListener('resize', setTop);

        // Escape closes the panel — unless a modal (Apply, review, success…) is open on top of the page.
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape' || !split.classList.contains('is-panel-open')) return;
            const modalOpen = [...document.querySelectorAll('div.fixed.inset-0[id$="Modal"], div.fixed.inset-0[id$="Overlay"]')].some(m => !m.classList.contains('hidden') && getComputedStyle(m).display !== 'none');
            if (!modalOpen) toggleModal();
        });

        panel.setAttribute('aria-hidden', 'true');
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
