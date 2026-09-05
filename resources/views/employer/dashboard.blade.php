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
</head>

<style>
    .HeroSection {
        background:
            url("{{ asset('assets/heroSectionBackground.png') }}");
        background-size: cover;
        background-position: center;
    }

    .EmployerFeatures {
        background:
            url("{{ asset('assets/Landing Page/Alumni Services.png') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<body>
    @php
    $current_page = Route::currentRouteName();
    @endphp
    @include('partials.header-employer')
    @include('partials.success')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="bg-gray-50 p-8">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-8 tracking-wide">
                DASHBOARD FOR {{ strtoupper($employer->employer_company_name ?? 'YOUR COMPANY') }}
            </h2>

            @php
                $dashboardTiles = [
                    ['value' => $stats['activePostings'], 'label' => 'Active Job Postings', 'icon' => 'fa-briefcase'],
                    ['value' => $stats['totalApplicants'], 'label' => 'Total Applicants', 'icon' => 'fa-users'],
                    ['value' => $stats['unreadApplicants'], 'label' => 'New Unreviewed', 'icon' => 'fa-user-clock'],
                    ['value' => $stats['pending'], 'label' => 'Pending Review', 'icon' => 'fa-hourglass-half'],
                    ['value' => $stats['shortlisted'], 'label' => 'Shortlisted', 'icon' => 'fa-list-check'],
                    ['value' => $stats['hired'], 'label' => 'Hired via AlumNet', 'icon' => 'fa-handshake'],
                    ['value' => $stats['expiringSoon'], 'label' => 'Expiring Soon', 'icon' => 'fa-calendar-xmark'],
                ];
            @endphp

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 md:p-8">
                <h3 class="font-bold text-[#0E0F3B] mb-5 flex items-center gap-2 text-sm uppercase tracking-wide">
                    <i class="fa-solid fa-chart-pie text-[#1D46A4]"></i> Overview
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($dashboardTiles as $tile)
                    <div class="bg-slate-50 rounded-xl border border-gray-200 p-4 text-center transition-all hover:-translate-y-0.5 hover:shadow-md">
                        <div class="text-2xl md:text-3xl font-bold text-orange-600 mb-1.5">{{ $tile['value'] }}</div>
                        <p class="text-orange-700 font-semibold text-xs leading-tight flex flex-col items-center justify-center gap-1.5">
                            <i class="fa-solid {{ $tile['icon'] }} text-orange-700 text-sm"></i>
                            <span>{{ $tile['label'] }}</span>
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- ANALYTICS WIDGETS: applicant traffic per posting + recent applicants quick-links -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                    <h3 class="font-bold text-[#0E0F3B] mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-chart-simple text-[#1D46A4]"></i> Applicant Traffic by Job Posting
                    </h3>
                    @forelse ($jobPostings->take(5) as $job)
                    <a href="{{ route('jobApplication.showApplications', ['jobPostingId' => $job->job_posting_id]) }}" class="block mb-4 last:mb-0 group">
                        <div class="flex justify-between gap-2 text-xs font-semibold text-gray-600 mb-1.5">
                            <span class="truncate group-hover:text-[#C73D1A] transition-colors">{{ $job->job_posting_title }}</span>
                            <span class="shrink-0">{{ $job->applications_count }} {{ \Illuminate\Support\Str::plural('applicant', $job->applications_count) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-[#1D46A4] h-2 rounded-full transition-all" style="width: {{ $job->applications_count / $maxApplicantsPerPosting * 100 }}%"></div>
                        </div>
                    </a>
                    @empty
                    <p class="text-sm text-gray-400">You haven't posted any jobs yet.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-[#0E0F3B] flex items-center gap-2">
                            <i class="fa-solid fa-user-clock text-[#1D46A4]"></i> Recent Applicants
                        </h3>
                        @if ($stats['unreadApplicants'] > 0)
                        <span class="text-[10px] font-bold uppercase bg-red-100 text-red-600 px-2.5 py-1 rounded-full">{{ $stats['unreadApplicants'] }} new</span>
                        @endif
                    </div>
                    @forelse ($recentApplicants as $application)
                    <a href="{{ route('jobApplication.showApplications', ['jobPostingId' => $application->job_id]) }}"
                        class="flex items-center gap-3 py-2.5 -mx-2 px-2 rounded-lg border-b border-gray-50 last:border-0 hover:bg-slate-50 transition-colors">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($application->alumnus->user->user_first_name . ' ' . $application->alumnus->user->user_last_name) }}&background=random"
                            class="w-9 h-9 rounded-full shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#0E0F3B] truncate">{{ $application->alumnus->user->user_first_name }} {{ $application->alumnus->user->user_last_name }}</p>
                            <p class="text-xs text-gray-500 truncate">Applied to {{ $application->job->job_posting_title }}</p>
                        </div>
                        <span class="text-[10px] text-gray-400 shrink-0">{{ $application->application_date->diffForHumans() }}</span>
                        @unless ($application->is_read)
                        <span class="w-2 h-2 rounded-full bg-[#C73D1A] shrink-0"></span>
                        @endunless
                    </a>
                    @empty
                    <p class="text-sm text-gray-400">No applicants yet.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </section>

    <section class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            <div class="flex justify-between items-end mb-8 pb-4">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">JOB POSTING</h2>
                <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}" class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A] transition-all">
                    GO TO MY JOB POSTINGS >
                </a>
            </div>

            <div class="relative px-8 md:px-12">
                <button id="prevBtn" class="absolute left-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#C73D1A] transition-colors z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-12 md:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button id="nextBtn" class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#C73D1A] transition-colors z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-12 md:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div id="slider" class="flex gap-6 overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth">

                    @forelse ($jobPostings as $job)
                    @php
                        $cardImage = $job->job_posting_image
                            ? asset('storage/' . $job->job_posting_image)
                            : 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=600q=80';
                    @endphp
                    <div class="min-w-full md:min-w-[calc(50%-12px)] lg:min-w-[calc(33.333%-16px)] snap-center">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col transition-transform hover:scale-[1.02]">
                            <div class="relative h-48 bg-cover bg-center" style="background-image:url('{{ $cardImage }}');">
                                <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                                @unless ($job->job_approved)
                                <span class="absolute top-3 left-3 bg-amber-500 text-white text-[9px] font-bold uppercase px-2 py-1 rounded-full">Pending Approval</span>
                                @endunless
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex justify-between items-start gap-2 mb-2">
                                    <h3 class="font-bold text-[#0E0F3B] uppercase text-lg md:text-xl leading-tight line-clamp-2 min-h-[2.5rem] md:min-h-[3rem]">{{ $job->job_posting_title }}</h3>
                                    <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-1 shrink-0">{{ $job->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center text-gray-400 text-xs font-semibold mb-4 uppercase">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $job->job_closing_date ? 'Closes ' . \Carbon\Carbon::parse($job->job_closing_date)->format('M d, Y') : 'Open-ended' }}
                                </div>
                                <div class="space-y-1 mb-6 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <p>Applications Received: {{ $job->applications_count }}</p>
                                </div>
                                <a href="{{ route('jobApplication.showApplications', ['jobPostingId' => $job->job_posting_id]) }}"
                                    class="block text-center w-full bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white py-3 rounded-md font-bold text-xs tracking-widest hover:bg-black transition-all uppercase">
                                    View Applicants
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="w-full bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center text-gray-500">
                        <i class="fa-solid fa-briefcase text-3xl mb-3 block text-gray-300"></i>
                        <p class="mb-4">You haven't posted any jobs yet.</p>
                        <a href="{{ route('jobPosting.jobBoard') }}" class="inline-block px-6 py-2 rounded-md bg-[#0E0F3B] text-white text-sm font-bold uppercase hover:bg-[#1D264F] transition-colors">
                            Post a Job
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
            // the grid's responsive breakpoints (full/half/third width).
            function cardScrollAmount() {
                const firstCard = slider.children[0];
                if (!firstCard) return slider.clientWidth;
                const gap = parseFloat(getComputedStyle(slider).columnGap || 0);
                return firstCard.getBoundingClientRect().width + gap;
            }

            prevBtn.addEventListener('click', () => slider.scrollBy({ left: -cardScrollAmount(), behavior: 'smooth' }));
            nextBtn.addEventListener('click', () => slider.scrollBy({ left: cardScrollAmount(), behavior: 'smooth' }));
        })();
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

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-users text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Community Updates</span>
                    <a href="{{ route('notices.employerAnnouncements') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Review Candidates</span>
                    <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Post Jobs</span>
                    <a href="{{ route('jobPosting.jobBoard') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-clipboard-list text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Manage Postings</span>
                    <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Manage Profile</span>
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