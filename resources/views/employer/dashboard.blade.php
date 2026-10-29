<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Home</title>
    @vite('resources/js/app.js')
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<style>
    .HeroSection {
        background:
            url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

    .EmployerFeatures {
        background:
            url("{{ asset('assets/Landing Page/employerFeatures.jpg') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Analytics cards — same as the admin dashboard (superAdmin/dashboard.blade.php). */
    .chart-card {
        background: #fff;
        border-radius: 10px;
        padding: 16px 18px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        font-family: 'Montserrat', sans-serif;
    }

    .chart-card .card-title {
        font-size: 13px;
        font-weight: 700;
        background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
    }

    .chart-card .card-sub {
        font-size: 10.5px;
        color: #000;
        margin-top: 2px;
    }
</style>

<body>
    @php
    $current_page = Route::currentRouteName();
    @endphp
    @include('partials.header-employer')
    @include('partials.success')

    @include('partials.error')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="bg-gray-50 p-8">
        <div class="max-w-6xl mx-auto">
            {{-- Phones: title on its own row, then OVERVIEW + EXPORT PDF side by side.
                 Tablet/desktop (md+): title + EXPORT PDF on one row, OVERVIEW below. --}}
            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-5 md:gap-y-8 mb-4">
                <h2 class="col-span-2 md:col-span-1 text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-wide rp-dashboard-title">
                    DASHBOARD FOR {{ strtoupper($employer->employer_company_name ?? 'YOUR COMPANY') }}
                </h2>
                {{-- Same export button style as the admin dashboard's EXPORT PDF. --}}
                <button type="button" onclick="exportDashboardPdf()" title="Download the Overview and Analytics below as a PDF report"
                    class="col-start-2 row-start-2 md:row-start-1 justify-self-end shrink-0 whitespace-nowrap bg-[#C04828] text-[10px] text-white px-3 py-1.5 rounded-md flex items-center gap-1.5 hover:bg-[#A03D22] transition shadow-sm font-semibold uppercase tracking-wide">
                    <i class="fa-solid fa-file-pdf text-xs"></i> EXPORT PDF
                </button>
                <h3 class="col-start-1 row-start-2 md:col-span-2 font-bold text-[#0E0F3B] flex items-center gap-2 text-sm uppercase tracking-wide">
                    <i class="fa-solid fa-chart-pie text-[#1D46A4]"></i> Overview
                </h3>
            </div>

            @php
                // Same widget layout as the admin pages: icon left of the number, label below.
                // Each widget opens My Job Postings filtered to what it counts (the applicant
                // ones then open a post's Applicants already filtered to that status).
                $myPosts = fn (array $q) => route('jobPosting.myJobPosts', ['id' => Auth::id()] + $q);
                $dashboardTiles = [
                    ['value' => $stats['activePostings'], 'label' => 'Active Job Postings', 'icon' => 'fa-briefcase', 'color' => 'text-slate-800', 'href' => $myPosts(['status' => 'approved'])],
                    ['value' => $stats['totalApplicants'], 'label' => 'Total Applicants', 'icon' => 'fa-users', 'color' => 'text-blue-600', 'badge' => $stats['unreadApplicants'] > 0 ? $stats['unreadApplicants'] . ' new' : null, 'href' => $myPosts(['applicants' => 'any'])],
                    ['value' => $stats['shortlisted'], 'label' => 'Shortlisted', 'icon' => 'fa-list-check', 'color' => 'text-amber-500', 'href' => $myPosts(['applicants' => 'shortlisted'])],
                    ['value' => $stats['hired'], 'label' => 'Hired via AlumNet', 'icon' => 'fa-handshake', 'color' => 'text-green-500', 'href' => $myPosts(['applicants' => 'hired'])],
                    ['value' => $stats['pending'], 'label' => 'Pending Review', 'icon' => 'fa-hourglass-half', 'color' => 'text-[#ED7A07]', 'href' => $myPosts(['applicants' => 'pending'])],
                    ['value' => $stats['expiringSoon'], 'label' => 'Job Postings Expiring Soon', 'icon' => 'fa-calendar-xmark', 'color' => 'text-red-500', 'href' => $myPosts(['status' => 'expiring'])],
                ];
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($dashboardTiles as $tile)
                <a href="{{ $tile['href'] }}" title="View {{ strtolower($tile['label']) }}"
                    class="block bg-white rounded-lg border border-transparent shadow-sm transition-all hover:shadow-md hover:border-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A] px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid {{ $tile['icon'] }} text-lg shrink-0 {{ $tile['color'] }}"></i>
                        <p class="text-2xl font-bold {{ $tile['color'] }}">{{ $tile['value'] }}</p>
                    </div>
                    <p class="text-xs font-medium text-slate-500 mt-1 leading-snug">
                        {{ $tile['label'] }}
                        @if (!empty($tile['badge']))
                        <span class="ml-1 inline-block text-[9px] font-bold uppercase bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full align-middle">{{ $tile['badge'] }}</span>
                        @endif
                    </p>
                </a>
                @endforeach
            </div>

            <!-- ANALYTICS (same chart-card style as the admin dashboard) -->
            <h3 class="font-bold text-[#0E0F3B] mt-10 mb-4 flex items-center gap-2 text-sm uppercase tracking-wide">
                <i class="fa-solid fa-chart-simple text-[#1D46A4]"></i> Analytics
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Applicant Traffic by Job Posting -->
                <div class="chart-card">
                    <div class="card-title">Applicant Traffic by Job Posting</div>
                    <div class="card-sub">Applicants per posting &mdash; active postings first</div>
                    <div class="mt-4">
                        @forelse ($jobPostings->take(5) as $job)
                        <a href="{{ route('jobApplication.showApplications', ['jobPostingId' => $job->job_posting_id]) }}" class="block mb-4 last:mb-0 group">
                            <div class="flex justify-between gap-2 text-xs font-semibold text-gray-600 mb-1.5">
                                <span class="flex items-baseline gap-1.5 min-w-0">
                                    <span class="truncate group-hover:text-[#C73D1A] transition-colors">{{ $job->job_posting_title }}</span>
                                    <span class="shrink-0 text-[10px] font-normal text-gray-400">· Posted {{ $job->created_at->format('M j, Y') }}</span>
                                </span>
                                <span class="shrink-0">{{ $job->applications_count }} {{ \Illuminate\Support\Str::plural('applicant', $job->applications_count) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-[#1a3a6e] h-2 rounded-full transition-all" style="width: {{ $job->applications_count / $maxApplicantsPerPosting * 100 }}%"></div>
                            </div>
                        </a>
                        @empty
                        <p class="text-sm text-gray-400">You haven't posted any jobs yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Application Status Breakdown -->
                <div class="chart-card">
                    <div class="card-title">Application Status Breakdown</div>
                    <div class="card-sub">Where every applicant across your job postings currently stands</div>
                    @if (array_sum($statusBreakdown) > 0)
                    <div style="margin-top:10px; height:220px;"><canvas id="chartApplicationStatus"></canvas></div>
                    @else
                    <div class="h-[220px] flex flex-col items-center justify-center text-gray-400 text-sm">
                        <i class="fa-solid fa-chart-pie text-3xl mb-2 text-gray-300"></i> No applications yet.
                    </div>
                    @endif
                </div>

                <!-- Applications & Hires per Month -->
                <div class="chart-card lg:col-span-2">
                    <div class="card-title">Applications &amp; Hires per Month</div>
                    <div class="card-sub">Applications received and applicants hired across all your job postings &mdash; last 6 months</div>
                    <div style="margin-top:10px; height:240px;"><canvas id="chartMonthlyActivity"></canvas></div>
                </div>

            </div>
        </div>
    </section>

    <section class="bg-gray-50 py-10 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            <div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-2 mb-4 sm:mb-6">
                <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">JOB POSTING</h2>
                <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}" class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-xs sm:text-sm hover:border-b-2 border-[#C73D1A] transition-all">
                    GO TO MY JOB POSTINGS >
                </a>
            </div>

            {{-- Carousel. Phones: one card plus a peek of the next (swipe). Tablets: two
                 cards, desktop: three, with arrow buttons whenever there's more to scroll. --}}
            <div class="relative {{ $jobPostings->count() > 1 ? 'sm:px-12' : '' }}">
                @if ($jobPostings->count() > 1)
                <button type="button" id="prevBtn" aria-label="Previous job postings"
                    class="hidden sm:flex absolute left-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-slate-200 shadow-md text-[#1D264F] items-center justify-center hover:bg-[#1D264F] hover:text-white transition-colors disabled:opacity-30 disabled:pointer-events-none">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </button>
                <button type="button" id="nextBtn" aria-label="Next job postings"
                    class="hidden sm:flex absolute right-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white border border-slate-200 shadow-md text-[#1D264F] items-center justify-center hover:bg-[#1D264F] hover:text-white transition-colors disabled:opacity-30 disabled:pointer-events-none">
                    <i class="fa-solid fa-chevron-right text-sm"></i>
                </button>
                @endif

                <div id="slider" class="flex gap-4 sm:gap-6 overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth">

                    @forelse ($jobPostings as $job)
                    @php
                        $cardImage = $job->thumbnailUrl();
                        $usesDefaultImage = $job->usesDefaultThumbnail();
                        $imgOverlay = $job->defaultThumbnailOverlay();
                        // Same expired / pending / approved status as My Job Postings.
                        $cardStatus = [
                            'pending' => ['Pending Approval', 'text-amber-600', 'bg-amber-500'],
                            'approved' => ['Approved', 'text-green-700', 'bg-green-500'],
                            'expired' => ['Expired', 'text-slate-600', 'bg-slate-400'],
                        ][$job->myPostStatus()];
                    @endphp
                    <div class="shrink-0 w-[85%] sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] snap-start" data-job-card>
                        {{-- No shadow (it crowded the carousel) — a border that warms up on hover instead. --}}
                        <article class="group h-full bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col transition-colors duration-200 hover:border-[#C73D1A]/40">
                            <div class="relative h-32 sm:h-36 shrink-0">
                                <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $cardImage }}'); @if ($usesDefaultImage) opacity:{{ $imgOverlay['imageOpacity'] }}; @endif"></div>
                                @if ($usesDefaultImage)
                                <div class="absolute inset-0" style="background-color:{{ $imgOverlay['color'] }}; opacity:{{ $imgOverlay['overlayOpacity'] }}"></div>
                                @else
                                <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                                @endif
                                <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-white/95 {{ $cardStatus[1] }} text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $cardStatus[2] }}"></span> {{ $cardStatus[0] }}
                                </span>
                            </div>

                            <div class="p-4 sm:p-5 flex flex-col flex-1">
                                {{-- Always exactly two lines tall, so every card's details line up.
                                     text-[15px] (not md:text-base) so leading-snug isn't overridden. --}}
                                <h3 class="font-bold text-[#0E0F3B] uppercase text-sm md:text-[15px] leading-snug line-clamp-2 h-[2.75em] break-words transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold" title="{{ $job->job_posting_title }}">{{ $job->job_posting_title }}</h3>

                                <ul class="mt-2 space-y-1 text-xs text-slate-500">
                                    <li class="flex items-center gap-2">
                                        <i class="fa-regular fa-calendar w-3.5 text-center text-slate-400"></i>
                                        {{ $job->job_closing_date ? 'Closes ' . \Carbon\Carbon::parse($job->job_closing_date)->format('M d, Y') : 'Open-ended' }}
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i class="fa-regular fa-clock w-3.5 text-center text-slate-400"></i>
                                        Posted {{ $job->created_at->diffForHumans() }}
                                    </li>
                                </ul>

                                <div class="mt-3 sm:mt-4 flex items-center gap-3 rounded-xl bg-[#1D264F]/5 px-3 py-2.5">
                                    <span class="w-8 h-8 shrink-0 rounded-lg bg-white text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-users text-xs"></i></span>
                                    <div class="min-w-0">
                                        <p class="text-base font-bold text-[#0E0F3B] leading-none">{{ $job->applications_count }}</p>
                                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ \Illuminate\Support\Str::plural('Application', $job->applications_count) }} received</p>
                                    </div>
                                </div>

                                <div class="mt-auto pt-4 sm:pt-5">
                                    <a href="{{ route('jobApplication.showApplications', ['jobPostingId' => $job->job_posting_id]) }}"
                                        class="flex items-center justify-center gap-2 w-full bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white py-2.5 rounded-lg font-bold text-xs tracking-wider transition-all uppercase">
                                        View Applicants <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                    @empty
                    {{-- Empty state: brand navy, not gray. --}}
                    <div class="w-full bg-white rounded-2xl border border-slate-200 px-6 py-10 text-center">
                        <span class="mx-auto w-14 h-14 rounded-2xl bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-briefcase text-2xl"></i></span>
                        <p class="mt-3 text-base font-bold text-[#0E0F3B]">You haven't posted any jobs yet.</p>
                        <p class="mt-1 text-sm text-[#1D264F]">Your job posts will show up here with their applicant counts.</p>
                        <a href="{{ route('jobPosting.jobBoard') }}" class="mt-5 inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-[#0E0F3B] text-white text-xs font-bold uppercase tracking-widest hover:bg-[#1D264F] transition-colors">
                            <i class="fa-solid fa-plus"></i> Post a Job
                        </a>
                    </div>
                    @endforelse

                </div>
            </div>
        </div>
    </section>

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <script>
        (function () {
            const slider = document.getElementById('slider');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (!slider || !prevBtn || !nextBtn) return;

            // Scrolls by exactly one card's width (+ the flex gap) instead of a
            // fixed pixel amount, since a card's rendered width changes across
            // breakpoints (85% / half / third).
            function cardScrollAmount() {
                const firstCard = slider.querySelector('[data-job-card]');
                if (!firstCard) return slider.clientWidth;
                const gap = parseFloat(getComputedStyle(slider).columnGap || 0);
                return firstCard.getBoundingClientRect().width + gap;
            }

            // Arrows only when there's something to scroll to; dimmed at either end.
            function updateArrows() {
                const max = slider.scrollWidth - slider.clientWidth;
                const scrollable = max > 2;
                prevBtn.style.visibility = nextBtn.style.visibility = scrollable ? '' : 'hidden';
                prevBtn.disabled = !scrollable || slider.scrollLeft <= 2;
                nextBtn.disabled = !scrollable || slider.scrollLeft >= max - 2;
            }

            prevBtn.addEventListener('click', () => slider.scrollBy({ left: -cardScrollAmount(), behavior: 'smooth' }));
            nextBtn.addEventListener('click', () => slider.scrollBy({ left: cardScrollAmount(), behavior: 'smooth' }));
            slider.addEventListener('scroll', updateArrows, { passive: true });
            window.addEventListener('resize', updateArrows);
            updateArrows();
        })();
    </script>

    @include('partials.chart-pdf-image')
    <script>
        // Analytics charts — same Chart.js setup/palette as the admin dashboard.
        (function () {
            const fontDef = { family: 'Montserrat', size: 10 };
            const gridColor = 'rgba(0,0,0,0.05)';
            Chart.defaults.font = fontDef;
            Chart.defaults.color = '#6b7280';

            const statusCanvas = document.getElementById('chartApplicationStatus');
            if (statusCanvas) {
                const statusBreakdown = @json($statusBreakdown);
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusBreakdown),
                        datasets: [{
                            data: Object.values(statusBreakdown),
                            backgroundColor: ['#94a3b8', '#ED7A07', '#1a3a6e', '#C73D1A'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: { legend: { display: true, position: 'bottom', labels: { font: fontDef, boxWidth: 10 } } }
                    }
                });
            }

            const monthly = @json($monthlyActivity);
            new Chart(document.getElementById('chartMonthlyActivity'), {
                type: 'bar',
                data: {
                    labels: monthly.labels,
                    datasets: [
                        { label: 'Applications Received', data: monthly.applications, backgroundColor: '#1a3a6e', borderRadius: 3 },
                        { label: 'Applicants Hired', data: monthly.hires, backgroundColor: '#e05c00', borderRadius: 3 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'bottom', labels: { font: fontDef, boxWidth: 10 } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: fontDef } },
                        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { font: fontDef, precision: 0 } }
                    }
                }
            });
        })();

        // EXPORT PDF — captures each chart as a JPEG and POSTs it with the
        // CSRF token (same approach as the admin dashboard's
        // exportPdfWithCharts()); the server renders the Overview + Analytics PDF.
        function exportDashboardPdf() {
            const charts = {};
            ['chartApplicationStatus', 'chartMonthlyActivity'].forEach(function (id) {
                const canvas = document.getElementById(id);
                if (canvas) {
                    const img = chartCanvasToPdfImage(canvas); // null: the PDF shows a table for this chart
                    if (img) charts[id] = img;
                }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = {!! json_encode(route('employer.dashboard.exportPdf')) !!};
            form.style.display = 'none';
            [['_token', '{{ csrf_token() }}'], ['charts', JSON.stringify(charts)]].forEach(function ([name, value]) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }
    </script>


    <section class="py-16 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center justify-items-center">
        <div class="rounded-lg overflow-hidden shadow-xl w-full">
            <img src="{{ asset('assets/Landing Page/graduationImage.png') }}" alt="Graduation" class="w-full h-full object-cover">
        </div>

        <div class="flex flex-col items-center text-center">
            <h2 class="text-4xl font-bold text-[#0E0F3B] mb-6 leading-tight">
                Your Journey has<br>just begun
            </h2>
            <p class="text-black leading-relaxed mb-8">
                <span class="font-bold text-[#0E0F3B]">PLV-AlumNet</span> is the essential digital platform that elevates the connection between all PLV alumni. We function as a dynamic ecosystem, not just a directory, actively working to bridge opportunities, empower professional success, and inspire mentorship across all generations of graduates.
            </p>
            <button class="px-8 py-2 rounded-md border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition-colors duration-300 uppercase text-sm tracking-widest">
                View More
            </button>
        </div>
    </section>

    <section class="EmployerFeatures orange-gradient py-16 px-6 text-white text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-4xl font-bold mb-4 uppercase">Employer System Features</h2>
            <p class="items-center text-justify mb-12 text-sm w-3/5 mx-auto">
                Everything your company needs to make the most of <span class="font-bold">PLV-AlumNet</span> is right here. Post job openings, review and manage applicants, stay on top of campus announcements, and keep your company profile up to date, all from one place built to connect you with PLV's graduates.
            </p>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-users text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Community Updates</span>
                    <a href="{{ route('notices.employerAnnouncements') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Review Candidates</span>
                    <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Post Jobs</span>
                    <a href="{{ route('jobPosting.jobBoard') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-clipboard-list text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Manage Postings</span>
                    <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="group bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center transition-colors group-hover:text-[#C73D1A] group-hover:font-extrabold">Manage Profile</span>
                    <a href="{{ route('users.editProfile') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

            </div>
        </div>
    </section>

    <section class="py-4 px-6 max-w-6xl mx-auto relative pb-16">
        <div class="flex justify-between items-end mb-8 pl-4">
            <span class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent uppercase tracking-tighter">| Announcements</span>
            <a href="{{ route('notices.employerAnnouncements') }}" class="font-bold uppercase text-xs bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent hover:border-b-2 border-[#C73D1A] transition-colors">Go to Announcements ></a>
        </div>

        @if ($recentAnnouncements->isEmpty())
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center text-gray-500">
            <i class="fa-solid fa-bullhorn text-3xl mb-3 block text-gray-300"></i>
            <p>No announcements right now.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach ($recentAnnouncements as $notice)
            <a href="{{ route('notices.employerAnnouncements', ['notice' => $notice->id]) }}" class="flex items-center gap-4 bg-white shadow-md rounded-lg border border-gray-100 p-4 hover:shadow-lg transition-shadow">
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bullhorn text-amber-600"></i>
                </div>
                <div class="flex-grow min-w-0">
                    <h3 class="font-bold text-blue-900 text-sm truncate">{{ $notice->title }}</h3>
                    <p class="text-xs text-gray-500 truncate">{{ \Illuminate\Support\Str::limit(strip_tags($notice->description ?? ''), 100) }}</p>
                </div>
                <p class="text-[10px] text-gray-400 shrink-0 whitespace-nowrap">{{ $notice->event_datetime->format('M d, Y') }}</p>
            </a>
            @endforeach
        </div>
        @endif
    </section>

    @include('partials.footer-employer')
    
</body>

</html>