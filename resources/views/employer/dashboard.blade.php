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

    .AlumniServices {
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow-lg border-t-8 border-orange-600 p-8 text-center transition-transform hover:-translate-y-1">
                    <div class="text-6xl font-bold text-orange-600 mb-2">{{ $stats['activePostings'] }}</div>
                    <p class="text-orange-700 font-semibold leading-tight flex items-center justify-center gap-2">
                        <i class="fa-solid fa-briefcase text-orange-700"></i>
                        <span>Active Job<br>Postings</span>
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-lg border-t-8 border-orange-600 p-8 text-center transition-transform hover:-translate-y-1">
                    <div class="text-6xl font-bold text-orange-600 mb-2">{{ $stats['unreadApplicants'] }}</div>
                    <p class="text-orange-700 font-semibold leading-tight flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-clock text-orange-700"></i>
                        <span>New Unreviewed<br>Applicants</span>
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-lg border-t-8 border-orange-600 p-8 text-center transition-transform hover:-translate-y-1">
                    <div class="text-6xl font-bold text-orange-600 mb-2">{{ $stats['expiringSoon'] }}</div>
                    <p class="text-orange-700 font-semibold leading-tight flex items-center justify-center gap-2">
                        <i class="fa-solid fa-hourglass-half text-orange-700"></i>
                        <span>Job Posts<br>Expiring Soon</span>
                    </p>
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

            <div class="relative px-12">
                <button id="prevBtn" class="absolute left-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#C73D1A] transition-colors z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button id="nextBtn" class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#C73D1A] transition-colors z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-[#0E0F3B] uppercase text-xl">{{ $job->job_posting_title }}</h3>
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

    <section class="AlumniServices orange-gradient py-16 px-6 text-white text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-4xl font-bold mb-4 uppercase">Alumni Services</h2>
            <p class="items-center text-justify mb-12 text-sm w-3/5 mx-auto">
                This section details the exclusive resources, support, and programs available to all graduates of <span class="font-bold">Pamantasan ng Lungsod ng Valenzuela (PLV)</span>. It typically includes services such as career assistance, networking events, information for claiming of alumni IDs and Yearbook, and access to campus facilities. The goal is to keep alumni connected to the university and to foster mutual support among the network's members.
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
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent
            text-4xl font-bold text-blue-900 uppercase tracking-tighter"> | Announcements</span>
            <a href="{{ route('notices.employerAnnouncements') }}" class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A]">Go to Announcements ></a>
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