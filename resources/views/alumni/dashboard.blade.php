<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Home</title>
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
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
        background-size: cover;
        background-position: center;
    }

    .AlumniServices {
        background: url("{{ asset('assets/Landing Page/Alumni Services.png') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .AlumniTestimonial {
        background: url("{{ asset('assets/Landing Page/Alumni Testimonial.png') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .Experience {
        background: url("{{ asset('assets/Landing Page/ShareExperience.png') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    html {
        scroll-behavior: smooth;
    }

    html,
    body {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
        overflow-y: scroll;

    }
</style>

<body>
    @php
    $current_page = 'index_alumni';
    @endphp
    @include('partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">Honoring the Past. Shaping the Future.</p>
        </div>
    </section>
    @include('partials.success')
    <section id="status-section" class="py-12 px-6 max-w-6xl mx-auto">
        <h2 class="text-4xl font-bold mb-10">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Alumni Dashboard</span>
        </h2>

        <div class="grid md:grid-cols-2 gap-8">

            @php
                $alumniIdRecord = Auth::user()->alumnus->alumniId ?? null;
                $alumniIdCardConfig = [
                    'pending' => ['icon' => 'fa-clock', 'title' => 'Pending Submission', 'desc' => 'Your Alumni ID request has been submitted and is waiting to be processed.'],
                    'under_review' => ['icon' => 'fa-magnifying-glass', 'title' => 'Under Review', 'desc' => 'Your Alumni ID is currently under review by the Alumni Office.'],
                    'ready_to_claim' => ['icon' => 'fa-bell', 'title' => 'Ready to Claim', 'desc' => 'Your Alumni ID is ready! Visit the Alumni Office to claim it.'],
                    'claimed' => ['icon' => 'fa-check', 'title' => 'Alumni ID Claimed', 'desc' => 'Your Alumni ID has been claimed.'],
                ];
                $alumniIdCard = $alumniIdCardConfig[$alumniIdRecord->status ?? null] ?? ['icon' => 'fa-circle-question', 'title' => 'No Record Found', 'desc' => 'No Alumni ID request found yet. Please contact the Alumni Office.'];

                // Static stepper across the known status order — reflects
                // current status only, there's no per-transition history
                // table to draw a real timeline from.
                $alumniIdSteps = \App\Models\AlumniId::STATUSES;
                $alumniIdStepLabels = \App\Models\AlumniId::statusLabels();
                $alumniIdCurrentIndex = $alumniIdRecord ? array_search($alumniIdRecord->status, $alumniIdSteps, true) : -1;
            @endphp
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 flex flex-col">
                <div class="px-5 py-3 border-b border-gray-100 text-center">
                    <h4 class="w-fit mx-auto text-sm font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Alumni ID Claiming Status
                    </h4>
                </div>
                <div class="relative h-64 bg-[url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800q=80')] bg-cover bg-center">
                    <div class="alumniID absolute inset-0 flex flex-col items-center justify-center text-white p-6 text-center"
                        style="background-image: linear-gradient(-25deg, rgba(237,122,7,0.6), rgba(199,61,26,0.6));">
                        <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center mb-4">
                            <i class="fa-solid {{ $alumniIdCard['icon'] }} text-orange-600 text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold uppercase tracking-wide mb-4">{{ $alumniIdCard['title'] }}</h3>
                        <p class="text-sm font-medium mb-2">{{ $alumniIdCard['desc'] }}</p>
                    </div>
                </div>
                <div class="py-6 text-center">
                    <button type="button"
                        onclick="openAnimatedModal(document.getElementById('alumniIdStatusModal'), document.getElementById('alumniIdStatusModalPanel'))"
                        class="px-8 py-2 rounded-md border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition-colors duration-300 uppercase text-sm tracking-widest">
                        View Status
                    </button>
                </div>
            </div>

            @php
                $yearbookRecord = Auth::user()->alumnus->yearbook ?? null;
                $yearbookCardConfig = [
                    'pending' => ['title' => 'Pending', 'desc' => 'Your yearbook request is being processed.'],
                    'on_hand' => ['title' => 'On Hand', 'desc' => 'Your yearbook has arrived at the Alumni Office and is being prepared for release.'],
                    'ready_to_claim' => ['title' => 'Ready to Claim', 'desc' => 'Your yearbook is ready! See the distribution details below.'],
                    'claimed' => ['title' => 'Yearbook Claimed', 'desc' => 'You have claimed your yearbook.'],
                    'not_yet_claimed' => ['title' => 'Not Yet Claimed', 'desc' => 'Your yearbook has not been claimed yet.'],
                ];
                $yearbookCard = $yearbookCardConfig[$yearbookRecord->claiming_status ?? null] ?? ['title' => 'No Record Found', 'desc' => 'No yearbook record found yet. Please contact the Alumni Office.'];

                // 'not_yet_claimed' is a separate terminal state, not a stage
                // further along than 'claimed' — kept out of the linear
                // stepper and called out as its own message instead.
                $yearbookSteps = ['pending', 'on_hand', 'ready_to_claim', 'claimed'];
                $yearbookStepLabels = \App\Models\AlumniYearbook::claimingStatusLabels();
                $yearbookIsNotYetClaimed = ($yearbookRecord->claiming_status ?? null) === 'not_yet_claimed';
                $yearbookCurrentIndex = $yearbookRecord && !$yearbookIsNotYetClaimed
                    ? array_search($yearbookRecord->claiming_status, $yearbookSteps, true)
                    : -1;
            @endphp
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 flex flex-col">
                <div class="px-5 py-3 border-b border-gray-100 text-center">
                    <h4 class="w-fit mx-auto text-sm font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Yearbook Claiming Status
                    </h4>
                </div>
                <div class="relative h-64 bg-[url('https://images.unsplash.com/photo-1544822688-c6f14d6986bb?auto=format&fit=crop&w=800q=80')] bg-cover bg-center">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white p-6 text-center"
                        style="background-image: linear-gradient(180deg, rgba(32,113,201,0.7) 60%, rgba(29,70,164,0.7) 80%, rgba(14,15,59,0.7) 95%);">
                        <h3 class="text-xl font-bold uppercase tracking-widest mb-4">{{ $yearbookCard['title'] }}</h3>
                        <p class="text-[11px] leading-relaxed mb-6 px-4">{{ $yearbookCard['desc'] }}</p>

                        <div class="text-left w-full max-w-[200px] text-[10px] space-y-1">
                            <p><span class="font-bold">DATE:</span> {{ optional($yearbookRecord?->distribution_scheduled_at)->format('M d, Y') ?? 'TBA' }}</p>
                            <p><span class="font-bold">SCHEDULE:</span> {{ optional($yearbookRecord?->distribution_scheduled_at)->format('h:i A') ?? 'TBA' }}</p>
                            <p><span class="font-bold">LOCATION:</span> {{ $yearbookRecord ? $yearbookRecord->locationLabel() : 'TBA' }}</p>
                        </div>
                    </div>
                </div>
                <div class="py-6 text-center">
                    <button type="button"
                        onclick="openAnimatedModal(document.getElementById('yearbookStatusModal'), document.getElementById('yearbookStatusModalPanel'))"
                        class="px-8 py-2 rounded-md border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition-colors duration-300 uppercase text-sm tracking-widest">
                        View Status
                    </button>
                </div>
            </div>

        </div>
    </section>

    {{-- ===== Alumni ID Status modal ===== --}}
    <div id="alumniIdStatusModal"
        class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div id="alumniIdStatusModalPanel"
            class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden opacity-0 scale-95 transition-all duration-200">
            <div class="bg-[#0E0F3B] px-6 py-4 flex items-center justify-between">
                <span class="text-white font-bold uppercase tracking-widest text-sm">Alumni ID Status</span>
                <button type="button"
                    onclick="closeAnimatedModal(document.getElementById('alumniIdStatusModal'), document.getElementById('alumniIdStatusModalPanel'))"
                    class="text-white hover:text-gray-300">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                @if($alumniIdRecord)
                    <p class="text-xs font-bold text-[#C73D1A] uppercase tracking-wide mb-1">Reference No.</p>
                    <p class="text-lg font-bold text-[#0E0F3B] mb-6">ALID-{{ str_pad($alumniIdRecord->id, 6, '0', STR_PAD_LEFT) }}</p>

                    <div class="flex items-center mb-2">
                        @foreach($alumniIdSteps as $i => $stepKey)
                            <div class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold {{ $i <= $alumniIdCurrentIndex ? 'bg-[#C73D1A] text-white' : 'bg-gray-200 text-gray-400' }}">
                                @if($i < $alumniIdCurrentIndex)
                                    <i class="fa-solid fa-check"></i>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            @if(!$loop->last)
                                <div class="flex-1 h-0.5 {{ $i < $alumniIdCurrentIndex ? 'bg-[#C73D1A]' : 'bg-gray-200' }}"></div>
                            @endif
                        @endforeach
                    </div>
                    <div class="grid grid-cols-4 text-[10px] font-medium text-center mb-6">
                        @foreach($alumniIdSteps as $i => $stepKey)
                            <span class="{{ $i <= $alumniIdCurrentIndex ? 'text-[#C73D1A]' : 'text-gray-400' }}">{{ $alumniIdStepLabels[$stepKey] }}</span>
                        @endforeach
                    </div>

                    <p class="text-sm text-gray-600 mb-4">{{ $alumniIdCard['desc'] }}</p>

                    <div class="border-t border-gray-100 pt-4 text-xs text-gray-500">
                        Last updated {{ $alumniIdRecord->status_updated_at ? $alumniIdRecord->status_updated_at->format('F j, Y g:i A') : 'Not yet updated' }}
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-6">No Alumni ID request found yet. Please contact the Alumni Office.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Yearbook Claiming Status modal ===== --}}
    <div id="yearbookStatusModal"
        class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div id="yearbookStatusModalPanel"
            class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden opacity-0 scale-95 transition-all duration-200">
            <div class="bg-[#0E0F3B] px-6 py-4 flex items-center justify-between">
                <span class="text-white font-bold uppercase tracking-widest text-sm">Yearbook Claiming Status</span>
                <button type="button"
                    onclick="closeAnimatedModal(document.getElementById('yearbookStatusModal'), document.getElementById('yearbookStatusModalPanel'))"
                    class="text-white hover:text-gray-300">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                @if($yearbookRecord)
                    <p class="text-xs font-bold text-[#C73D1A] uppercase tracking-wide mb-1">Reference No.</p>
                    <p class="text-lg font-bold text-[#0E0F3B] mb-6">ALYB-{{ str_pad($yearbookRecord->id, 6, '0', STR_PAD_LEFT) }}</p>

                    @if($yearbookIsNotYetClaimed)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 mb-6 text-sm text-slate-600">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $yearbookCard['desc'] }}
                        </div>
                    @else
                        <div class="flex items-center mb-2">
                            @foreach($yearbookSteps as $i => $stepKey)
                                <div class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-bold {{ $i <= $yearbookCurrentIndex ? 'bg-[#C73D1A] text-white' : 'bg-gray-200 text-gray-400' }}">
                                    @if($i < $yearbookCurrentIndex)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                @if(!$loop->last)
                                    <div class="flex-1 h-0.5 {{ $i < $yearbookCurrentIndex ? 'bg-[#C73D1A]' : 'bg-gray-200' }}"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="grid grid-cols-4 text-[10px] font-medium text-center mb-6">
                            @foreach($yearbookSteps as $i => $stepKey)
                                <span class="{{ $i <= $yearbookCurrentIndex ? 'text-[#C73D1A]' : 'text-gray-400' }}">{{ $yearbookStepLabels[$stepKey] }}</span>
                            @endforeach
                        </div>
                    @endif

                    <p class="text-sm text-gray-600 mb-4">{{ $yearbookCard['desc'] }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm border-t border-gray-100 pt-4">
                        <div>
                            <p class="text-xs font-bold text-[#C73D1A] uppercase tracking-wide mb-1">Date</p>
                            <p class="text-[#0E0F3B] font-semibold">{{ optional($yearbookRecord->distribution_scheduled_at)->format('M d, Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#C73D1A] uppercase tracking-wide mb-1">Schedule</p>
                            <p class="text-[#0E0F3B] font-semibold">{{ optional($yearbookRecord->distribution_scheduled_at)->format('h:i A') ?? 'TBA' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-bold text-[#C73D1A] uppercase tracking-wide mb-1">Location</p>
                            <p class="text-[#0E0F3B] font-semibold">{{ $yearbookRecord->locationLabel() }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 mt-4 pt-4 text-xs text-gray-500">
                        Last updated {{ $yearbookRecord->status_updated_at ? $yearbookRecord->status_updated_at->format('F j, Y g:i A') : 'Not yet updated' }}
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-6">No yearbook record found yet. Please contact the Alumni Office.</p>
                @endif
            </div>
        </div>
    </div>

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
            <a href="{{ route('alumni.about') }}" class="px-8 py-2 rounded-md border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition-colors duration-300 uppercase text-sm tracking-widest">
                View More
            </a>
        </div>
    </section>

    <section class="py-16 px-6 max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <h2 class="text-3xl font-bold text-[#0E0F3B] uppercase tracking-tight">
                Job Matches <span class="text-[#0E0F3B]">For You!</span>
            </h2>
            <a href="{{ route('jobPosting.jobBoard') }}" class="text-[#ED7A07] font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A] inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                Go to Job Board >
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            @forelse ($jobMatches as $match)
            @php
                $job = $match->jobPosting;
                $cardImage = $job->job_posting_image
                    ? asset('storage/' . $job->job_posting_image)
                    : 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=600q=80';
                // Same $appliedJobs contract as partials.job-post-card, see AlumniDashboardController::index().
                $hasApplied = $appliedJobs->has($job->job_posting_id);
            @endphp
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col transition-transform hover:scale-[1.02]">
                {{-- Same data-* contract + openJobModal() as partials.job-post-card
                     (shared modal: partials.job-detail-modal) so clicking a
                     recommended job here opens the identical "View Details"
                     modal used on the job board. --}}
                <div class="relative h-48 bg-cover bg-center group cursor-pointer" style="background-image:url('{{ $cardImage }}');"
                    role="button" tabindex="0" aria-label="View job details"
                    data-title="{{ $job->job_posting_title }}"
                    data-company="{{ $job->job_posting_company }}"
                    data-address="{{ $job->job_posting_address }}"
                    data-date="{{ $job->created_at->diffForHumans() }}"
                    data-description="{{ $job->job_posting_description }}"
                    data-type="{{ $job->job_posting_employment_type }}"
                    data-setup="{{ $job->job_posting_setup }}"
                    data-valid="{{ $job->job_closing_date }}"
                    data-image="{{ $cardImage }}"
                    data-programs="{{ $job->programs->pluck('program_name')->implode(', ') }}"
                    data-industry="{{ $job->industry->industry_name ?? 'Not specified' }}"
                    onclick="openJobModal(this)"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openJobModal(this);}">
                    <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                    <div class="absolute inset-0 bg-blue-600/20"></div>
                    <span class="absolute top-3 right-3 bg-white/90 text-[#C73D1A] text-[10px] font-bold uppercase px-2 py-1 rounded-full">
                        {{ round($match->blendedScore()) }}% Match
                    </span>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="bg-white/90 text-[#1D264F] text-[11px] font-bold px-3 py-1.5 rounded-full shadow-lg">
                            <i class="fas fa-eye mr-1"></i> VIEW DETAILS
                        </span>
                    </div>
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-[#0E0F3B] uppercase text-xl">{{ $job->job_posting_title }}</h3>
                        <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-1 shrink-0">
                            <i class="fa-regular fa-calendar"></i> {{ $job->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-400 font-semibold uppercase mb-4 tracking-wider">{{ $job->job_posting_company }}</p>

                    <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">
                        {{ $job->programs->pluck('program_name')->implode(', ') ?: 'Open to all programs' }}
                    </p>

                    <p class="text-[11px] text-gray-600 leading-relaxed mb-6">
                        {{ $match->ai_explanation ?: Str::limit(strip_tags($job->job_posting_description), 160) }}
                    </p>

                    <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
                        <p class="text-[9px] text-gray-400 flex items-center gap-1 uppercase font-bold">
                            <i class="fa-regular fa-calendar-check"></i>
                            {{ $job->job_closing_date ? 'Closes ' . \Carbon\Carbon::parse($job->job_closing_date)->format('M d, Y') : 'Open-ended' }}
                        </p>
                        @if ($hasApplied)
                        <button disabled class="bg-green-600 cursor-not-allowed text-white px-4 py-1.5 rounded-md font-bold text-xs flex items-center gap-1.5 shrink-0">
                            <i class="fas fa-check-circle"></i> APPLIED
                        </button>
                        @else
                        <form action="{{ route('jobApplication.apply', $job->job_posting_id) }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-4 py-1.5 rounded-md font-bold text-xs transition-colors">
                                APPLY
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="md:col-span-3 bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center text-gray-500">
                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block text-gray-300"></i>
                <p class="mb-4">No job matches yet. Complete your resume so we can start recommending jobs for you.</p>
                <a href="{{ route('resume.build') }}" class="inline-block px-6 py-2 rounded-md bg-[#0E0F3B] text-white text-sm font-bold uppercase hover:bg-[#1D264F] transition-colors">
                    Build Your Resume
                </a>
            </div>
            @endforelse

        </div>
    </section>

    @include('partials.job-detail-modal')

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
                    <a href="{{ route('notices.announcements') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Alumni Directory</span>
                    <a href="{{ route('alumni.index') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Job Board</span>
                    <a href="{{ route('jobPosting.jobBoard') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-comments text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Network Connect</span>
                    <a href="{{ route('messages.index') }}" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-id-card text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Membership Status</span>
                    <a href="#status-section" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

            </div>
        </div>
    </section>

    @php
        $upcomingNotices = \App\Models\Notice::whereIn('category', ['event', 'seminar'])
            ->visibleToAlumni()
            ->upcoming()
            ->orderBy('event_datetime')
            ->limit(3)
            ->get();
        $recentAnnouncements = \App\Models\Notice::category('announcement')
            ->visibleToAlumni()
            ->orderByDesc('event_datetime')
            ->limit(3)
            ->get();
    @endphp

    <section class="py-16 px-6 max-w-6xl mx-auto relative">
        <div class="flex justify-between items-end mb-8 pl-4">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent
            text-4xl font-bold text-blue-900 uppercase tracking-tighter"> | Campus Events</span>
            <a href="{{ route('notices.eventsSeminars') }}" class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A]">Go to Events ></a>
        </div>

        @if ($upcomingNotices->isEmpty())
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center text-gray-500">
            <i class="fa-regular fa-calendar-xmark text-3xl mb-3 block text-gray-300"></i>
            <p>No upcoming events or seminars right now.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($upcomingNotices as $notice)
            <a href="{{ route('notices.eventsSeminars', ['tab' => $notice->category === 'seminar' ? 'seminar' : 'events']) }}"
                class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-100 hover:shadow-2xl transition-shadow">
                <div class="h-40">
                    <img src="{{ $notice->thumbnailUrl() }}" class="w-full h-full object-cover mix-blend-multiply">
                </div>

                <div class="p-4 flex gap-4 items-start relative">
                    <div class="flex-grow">
                        <h3 class="font-bold text-blue-900 uppercase text-sm">{{ $notice->title }}</h3>
                        @if($notice->location)
                        <p class="text-[10px] text-gray-500 mb-2">
                            <i class="fa-solid fa-location-dot mr-1"></i> {{ $notice->location }}
                        </p>
                        @endif
                        <p class="text-xs text-black w-3/4 leading-tight notice-description-content">
                            {{ \Illuminate\Support\Str::limit(strip_tags($notice->description ?? ''), 80) ?: 'No description provided.' }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 absolute right-0 bg-orange-700 text-white p-2 text-center w-16 rounded-sm shadow-sm">
                        <span class="block text-xl font-bold leading-none tracking-tighter">{{ $notice->event_datetime->format('d') }}</span>
                        <span class="text-[10px] uppercase font-semibold">{{ $notice->event_datetime->format('M') }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </section>

    <section class="py-4 px-6 max-w-6xl mx-auto relative pb-16">
        <div class="flex justify-between items-end mb-8 pl-4">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent
            text-4xl font-bold text-blue-900 uppercase tracking-tighter"> | Announcements</span>
            <a href="{{ route('notices.announcements') }}" class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A]">Go to Announcements ></a>
        </div>

        @if ($recentAnnouncements->isEmpty())
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-10 text-center text-gray-500">
            <i class="fa-solid fa-bullhorn text-3xl mb-3 block text-gray-300"></i>
            <p>No announcements right now.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach ($recentAnnouncements as $notice)
            <a href="{{ route('notices.announcements') }}" class="flex items-center gap-4 bg-white shadow-md rounded-lg border border-gray-100 p-4 hover:shadow-lg transition-shadow">
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

    <section id="alumni-testimonials" class="AlumniTestimonial py-20 px-6 text-white text-center mb-10">
        <h2 class="text-3xl font-bold uppercase mb-12 tracking-widest">Alumni Testimonials</h2>

        <div id="testimonial-cards-wrap">
            @include('partials.testimonial-cards')
        </div>
    </section>
    @include('partials.testimonial-cards-script')

    <section class="Experience w-full min-h-[560px] py-16 px-6 mb-5 flex items-center">
        <div class="max-w-7xl mx-auto w-full flex flex-col md:flex-row items-center gap-12">

        <div class="relative w-full ">

            <div class="relative z-10 bg-[#0E0F3B] p-8 rounded-2xl shadow-2xl w-full max-w-md mx-auto shadow-outer">
                <form action="{{ route('testimonials.submit', Auth::user()) }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- <div>
                        <label for="testimonial_name" class="block text-white font-bold mb-1 text-sm">Name:</label>
                        <input type="text" name="testimonial_name"
                            class="w-full p-2 rounded-lg bg-white border-b-2 border-[#ED7A07] shadow-inner focus:ring-2 focus:ring-[#C73D1A] outline-none">
                    </div>

                    <div>
                        <label class="block text-white font-bold mb-1 text-sm">Program:</label>
                        <input type="text" class="w-full p-2 rounded-lg bg-white border-b-2 border-[#ED7A07] focus:ring-2 focus:ring-[#C73D1A] outline-none">
                    </div>

                    <div>
                        <label class="block text-white font-bold mb-1 text-sm">Batch:</label>
                        <input type="text" class="w-full p-2 rounded-lg bg-white border-b-2 border-[#ED7A07] focus:ring-2 focus:ring-[#C73D1A] outline-none">
                    </div> -->

                    <div>
                        <label for="testimonial_body" class="block text-white font-bold mb-1 text-sm">Message:</label>
                        <textarea name="testimonial_body" rows="4" class="w-full p-2 rounded-lg bg-white border-b-2 border-[#ED7A07] focus:ring-2 focus:ring-[#C73D1A] outline-none resize-none"></textarea>
                    </div>

                    <div class="flex justify-center pt-2">
                        <button type="submit" class="bg-[#ED7A07] text-white font-bold px-10 py-2 rounded-md hover:bg-orange-600 transition uppercase tracking-wider text-sm shadow-lg">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full md:w-1/2 space-y-3 text-center flex flex-col items-center">
            <h2 class="text-3xl md:text-4xl font-bold text-[#0E0F3B] leading-tight">
                Share your experience
            </h2>

            <p class="text-[#0E0F3B] font-medium text-sm text-center leading-relaxed max-w-md mx-auto">
                Tell us about the connections, opportunities, or mentorship you've gained through the AlumNet. Your testimonial helps highlight the value of our network for all PLV graduates.
            </p>
        </div>
        </div>
    </section>

    @include('partials.footer-alumni')

</body>

</html>