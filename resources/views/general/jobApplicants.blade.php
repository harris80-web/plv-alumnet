<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
    <div>
        <h2>JOB</h2>
        <p>{{ $jobPost->job_posting_title }}</p>
        <p>{{ $jobPost->job_posting_company }}</p>
        <p>{{ $jobPost->job_posting_address }}</p>
        <p>{{ $jobPost->job_posting_employment_type }}</p>
        <p>{{ $jobPost->job_posting_setup }}</p>
        <p>{{ $jobPost->job_posting_description }}</p>
        <p>{{ $jobPost->job_closing_date }}</p>
        @foreach ($jobPost->programs as $program)
            <p>{{ $program->program_name }}</p>
        @endforeach
        
        <img src="{{ asset("storage/" . $jobPost->job_posting_image) }}" alt="" class="w-[100px] h-[100px] object-cover">
    </div>

    <div>
        <h2>JOB APPLICANTS</h2>
        @foreach ($jobPost->applicants as $applicant)
            <p>{{ $applicant->user->user_first_name }} {{ $applicant->user->user_middle_name }} {{ $applicant->user->user_last_name }} {{ $applicant->user->user_suffix }} </p>
            <p>{{ $applicant->program->program_name }}</p>
            
            <a href="{{ asset("storage/" . $applicant->alumnus_resume) ?? '#' }}">View Resume</a>
            <p>{{ $applicant->pivot->application_status }}</p>
            <form action="{{ route('jobApplication.hireApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Hire</button>
            </form>
            <form action="{{ route('jobApplication.declineApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Decline</button>
            </form>
            <form action="{{ route('jobApplication.shortlistApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Shortlist</button>
            </form>
        @endforeach
    </div>
    
</body>
</html>-->

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Job Applicants</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
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

    .action-dropdown {
        display: none;
        position: absolute;
        right: 0;
        z-index: 50;
        min-width: 160px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(14, 15, 59, 0.18);
        overflow: hidden;
        top: 110%;
        bottom: auto;
    }

    .action-dropdown.drop-up {
        top: auto;
        bottom: 110%;
    }

    .action-dropdown button {
        width: 100%;
        text-align: left;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.15s;
    }

    .action-dropdown button:hover {
        background: #f0f4ff;
    }

    .action-dropdown.open {
        display: block;
    }

    .badge {
        display: inline-block;
        padding: 2px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .badge-hired { background: #dcfce7; color: #16a34a; }
    .badge-declined { background: #fee2e2; color: #dc2626; }
    .badge-shortlisted { background: #fef9c3; color: #ca8a04; }
    .badge-pending { background: #e0e7ff; color: #3730a3; }

    #statusFilterDropdown {
        top: 130%;
        bottom: auto;
        min-width: 140px;
        z-index: 9999;
    }

    tbody tr { transition: background 0.12s; }
    tbody tr:hover { background: #f0f4ff; }
    ::-webkit-scrollbar { display: none; }
</style>

<body>
    @php $current_page = 'employer_job_postings'; @endphp
    @include('partials.header-employer')
    @include('partials.success')
    @if(session('error'))
    <div id="errorToast" class="fixed inset-0 z-[300] flex items-start justify-center pt-6 pointer-events-none">
        <div id="errorBox" class="pointer-events-auto flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 shadow-md transition-all duration-300 ease-out max-w-md w-full mx-4 opacity-0 -translate-y-2">
            <i class="fas fa-circle-exclamation text-red-500 text-base shrink-0"></i>
            <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
            <button onclick="closeErrorToast()" class="text-red-400 hover:text-red-600 shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>
    <script>
        (function () {
            const box = document.getElementById('errorBox');
            box.getBoundingClientRect();
            requestAnimationFrame(() => box.classList.remove('opacity-0', '-translate-y-2'));
        })();

        function closeErrorToast() {
            const box = document.getElementById('errorBox');
            box.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => document.getElementById('errorToast')?.remove(), 300);
        }

        setTimeout(closeErrorToast, 5000);
    </script>
    @endif
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <!-- BACK BUTTON -->
    <div class="max-w-5xl mx-auto px-6 pt-6">
        <a href="{{ route('jobPosting.myJobPosts', ['id' => auth()->user()->user_id]) }}"
            class="inline-flex items-center gap-2 text-[#0E0F3B] font-bold text-sm hover:text-[#C73D1A] transition-colors">
            <i class="fas fa-arrow-left"></i> RETURN
        </a>
    </div>

    <main class="max-w-5xl mx-auto px-6 pb-12">

        <!-- JOB DETAILS CARD -->
        <div class="bg-white rounded-3xl shadow-md flex flex-col md:flex-row mt-4 mb-8 md:min-h-[340px]">

            <!-- Image -->
            <div class="md:w-1/4 h-48 md:h-auto relative overflow-hidden rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none group cursor-pointer"
                role="button" tabindex="0" aria-label="View job details"
                data-image="{{ asset('storage/' . $jobPost->job_posting_image) }}"
                data-title="{{ $jobPost->job_posting_title }}"
                data-company="{{ $jobPost->job_posting_company }}"
                data-posted="{{ $jobPost->created_at->diffForHumans() }}"
                data-address="{{ $jobPost->job_posting_address }}"
                data-description="{{ $jobPost->job_posting_description }}"
                data-type="{{ $jobPost->job_posting_employment_type }}"
                data-setup="{{ $jobPost->job_posting_setup }}"
                data-valid="{{ $jobPost->job_closing_date }}"
                data-programs="{{ $jobPost->programs->pluck('program_name')->implode(', ') }}"
                data-industry="{{ $jobPost->industry->industry_name ?? 'Not specified' }}"
                onclick="openJobViewModal(this)"
                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openJobViewModal(this);}">
                <img src="{{ asset('storage/' . $jobPost->job_posting_image) }}"
                    class="object-cover w-full h-full opacity-60 group-hover:opacity-80 group-hover:scale-105 transition-all duration-300">
                <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="bg-white/90 text-[#1D264F] text-[11px] font-bold px-3 py-1.5 rounded-full shadow-lg">
                        <i class="fas fa-eye mr-1"></i> VIEW FULL POST
                    </span>
                </div>
            </div>

            <!-- Info -->
            <div class="p-6 flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold uppercase text-[#0E0F3B]">{{ $jobPost->job_posting_title }}</h2>
                            <p class="text-gray-600">{{ $jobPost->job_posting_company }}</p>
                            <p class="text-gray-500 text-sm">{{ $jobPost->job_posting_address }}</p>
                        </div>
                        <p class="text-xs text-gray-400 flex items-center">
                            <i class="far fa-calendar-alt mr-1"></i> {{ $jobPost->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-semibold">
                        <div>
                            <p class="text-[#0E0F3B]">Job Type: <span class="font-normal">{{ $jobPost->job_posting_employment_type }}</span></p>
                            <p class="text-[#0E0F3B]">Job Setup: <span class="font-normal">{{ $jobPost->job_posting_setup }}</span></p>
                        </div>
                        <div>
                            <p class="text-blue-900">Recommended Course/Program:
                                <span class="font-normal text-black">
                                    @foreach ($jobPost->programs as $program)
                                        {{ $program->program_name }}<br>
                                    @endforeach
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="font-bold text-sm text-[#0E0F3B]">Job Description:</p>
                        <div class="relative max-h-[2.6rem] overflow-hidden">
                            <div class="text-gray-500 text-xs job-description-content">{!! $jobPost->job_posting_description !!}</div>
                            <div class="absolute bottom-0 left-0 right-0 h-4 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center text-xs text-gray-400">
                    <i class="far fa-calendar-check mr-1"></i> Valid until
                    <span class="font-semibold text-gray-500 ml-1">{{ $jobPost->job_closing_date }}</span>
                </div>
            </div>
        </div>

        <!-- APPLICANTS TABLE -->
        <div class="bg-white rounded-3xl shadow-md">

            <div class="px-8 py-5 border-b">
                <h2 class="text-2xl font-bold tracking-tight text-center">
                    <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">JOB APPLICANTS</span>
                </h2>
            </div>

            @php
                $hiredCount = $jobPost->hiredApplicantsCount();
                $remainingSlots = $jobPost->remainingHiringSlots();
            @endphp

            <div class="px-8 py-4 border-b bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-xs font-semibold text-gray-600">
                    Hiring Limit: <span class="text-[#0E0F3B]">{{ $jobPost->hiring_limit }}</span>
                    &nbsp;&middot;&nbsp; Hired: <span class="text-green-600">{{ $hiredCount }}</span>
                    &nbsp;&middot;&nbsp; Remaining Slots:
                    <span class="{{ $remainingSlots > 0 ? 'text-[#C73D1A]' : 'text-red-500' }}">{{ $remainingSlots }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500"><span id="selectedCount">0</span> selected</span>

                    <form id="bulkShortlistForm" action="{{ route('jobApplication.bulkShortlistApplicants', $jobPost->job_posting_id) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <form id="bulkDeclineForm" action="{{ route('jobApplication.bulkDeclineApplicants', $jobPost->job_posting_id) }}" method="POST" class="hidden">
                        @csrf
                    </form>
                    <form id="bulkHireForm" action="{{ route('jobApplication.bulkHireApplicants', $jobPost->job_posting_id) }}" method="POST" class="hidden">
                        @csrf
                    </form>

                    <button type="button" id="bulkShortlistBtn" disabled onclick="submitBulkAction('shortlist')"
                        class="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">
                        <i class="fas fa-star mr-1"></i> Shortlist
                    </button>
                    <button type="button" id="bulkDeclineBtn" disabled onclick="submitBulkAction('decline')"
                        class="bg-red-500 hover:bg-red-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">
                        <i class="fas fa-user-times mr-1"></i> Decline
                    </button>
                    <button type="button" id="bulkHireBtn" disabled onclick="submitBulkAction('hire')"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">
                        <i class="fas fa-user-check mr-1"></i> Hire
                    </button>
                </div>
            </div>

            <div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#1D264F] text-white text-xs uppercase tracking-wider">
                            <th class="px-4 py-3 text-center font-semibold w-10">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
                                    class="w-4 h-4 cursor-pointer" title="Select all">
                            </th>
                            <th class="px-6 py-3 text-center font-semibold w-12">#</th>
                            <th class="px-6 py-3 text-center font-semibold">Applicant Name</th>
                            <th class="px-6 py-3 text-center font-semibold">Program</th>
                            <th class="px-6 py-3 text-center font-semibold">Compatibility</th>
                            <th class="px-6 py-3 text-center font-semibold">Resume</th>
                            <th class="px-6 py-3 text-center font-semibold">
                                <div class="relative inline-block">
                                    <button onclick="toggleStatusFilter(this)" class="flex items-center gap-1 mx-auto hover:text-yellow-300 transition-colors">
                                        STATUS <i class="fas fa-chevron-down text-[10px]"></i>
                                    </button>
                                    <div id="statusFilterDropdown" class="action-dropdown left-1/2 -translate-x-1/2 right-auto text-left">
                                        <button onclick="filterStatus('All')" class="text-gray-700"><i class="fas fa-list w-4"></i> All</button>
                                        <button onclick="filterStatus('Pending')" class="text-indigo-600"><i class="fas fa-clock w-4"></i> Pending</button>
                                        <button onclick="filterStatus('Hired')" class="text-green-600"><i class="fas fa-user-check w-4"></i> Hired</button>
                                        <button onclick="filterStatus('Declined')" class="text-red-500"><i class="fas fa-user-times w-4"></i> Declined</button>
                                        <button onclick="filterStatus('Shortlisted')" class="text-yellow-600"><i class="fas fa-star w-4"></i> Shortlisted</button>
                                    </div>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                        @forelse ($jobPost->applicants as $index => $applicant)
                        @php
                            $status = $applicant->pivot->application_status ?? 'Pending';
                            $badgeClass = match(strtolower($status)) {
                                'hired'       => 'badge-hired',
                                'declined'    => 'badge-declined',
                                'shortlisted' => 'badge-shortlisted',
                                default       => 'badge-pending',
                            };
                        @endphp
                        <tr data-id="{{ $index + 1 }}" data-status="{{ $status }}">
                            <td class="px-4 py-4 text-center">
                                @if(in_array(strtolower($status), ['pending', 'shortlisted']))
                                <input type="checkbox" class="applicant-checkbox w-4 h-4 accent-[#1D264F] cursor-pointer"
                                    value="{{ $applicant->pivot->application_id }}"
                                    onchange="updateBulkActionUI()">
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-gray-400 font-semibold">{{ $index + 1 }}</td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($applicant->user->user_first_name . '+' . $applicant->user->user_last_name) }}&background=1D264F&color=fff&size=36"
                                        class="w-9 h-9 rounded-full">
                                    <span class="font-semibold text-[#0E0F3B]">
                                        {{ $applicant->user->user_last_name }}, {{ $applicant->user->user_first_name }} {{ $applicant->user->user_middle_name }} {{ $applicant->user->user_suffix }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center text-gray-500 text-xs">
                                {{ $applicant->program->program_name }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php $score = $applicant->pivot->application_score; @endphp
                                <div class="flex flex-col items-center gap-1">
                                    @if($score !== null)
                                        <span class="text-xs font-bold px-2 py-1 rounded-full
                                            {{ $score >= 70 ? 'bg-green-100 text-green-700' : ($score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                            {{ $score }}%
                                        </span>
                                        @if($index < 10)
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full
                                            {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index < 3 ? 'bg-slate-200 text-slate-600' : 'bg-indigo-50 text-indigo-600') }}">
                                            RANK #{{ $index + 1 }}
                                        </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-xs">&mdash;</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if (($applicant->alumnus_resume_completeness ?? 0) > 0)
                                <a href="{{ route('resume.viewApplicant', $applicant->user_id) }}" target="_blank"
                                    class="bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-xs font-bold px-4 py-1.5 rounded-md transition-colors inline-block">
                                    View Resume
                                </a>
                                @else
                                <span class="text-gray-400 text-xs">No resume</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="badge {{ $badgeClass }} status-badge">{{ ucfirst($status) }}</span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block">
                                    <button onclick="toggleDropdown(this)"
                                        class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                                        <i class="fas fa-ellipsis-v text-gray-500"></i>
                                    </button>
                                    <div class="action-dropdown">
                                        @if($remainingSlots > 0)
                                        <form action="{{ route('jobApplication.hireApplicant', $applicant->pivot->application_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-green-600">
                                                <i class="fas fa-user-check w-4"></i> Hire
                                            </button>
                                        </form>
                                        @else
                                        <button type="button" disabled class="text-gray-300 cursor-not-allowed" title="Hiring limit reached">
                                            <i class="fas fa-user-check w-4"></i> Hire
                                        </button>
                                        @endif
                                        <form action="{{ route('jobApplication.declineApplicant', $applicant->pivot->application_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-red-500">
                                                <i class="fas fa-user-times w-4"></i> Decline
                                            </button>
                                        </form>
                                        <form action="{{ route('jobApplication.shortlistApplicant', $applicant->pivot->application_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-yellow-600">
                                                <i class="fas fa-star w-4"></i> Shortlist
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-gray-400">
                                <i class="fas fa-inbox text-5xl mb-3 block"></i>
                                <p class="font-semibold">No applicants yet.</p>
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- JOB VIEW MODAL -->
    <div id="jobViewModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl relative max-h-[90vh] overflow-y-auto my-8">

            <div class="h-48 w-full relative rounded-t-3xl overflow-hidden">
                <img id="jvm-image" src="" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>

                <button onclick="closeJobViewModal()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white rounded-full p-1 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <div class="p-8">
                <div>
                    <h2 id="jvm-title" class="text-3xl font-bold text-[#1D264F] uppercase tracking-tighter"></h2>
                    <div class="flex items-center text-gray-600 mt-1 space-x-4">
                        <p id="jvm-company" class="font-semibold text-lg"></p>
                        <span class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted <span id="jvm-posted"></span></span>
                    </div>
                    <p id="jvm-address" class="text-gray-500 font-medium"></p>
                </div>

                <div class="mt-8 flex flex-col md:flex-row gap-8">
                    <div class="md:w-3/5">
                        <h3 class="font-bold text-[#0E0F3B] mb-3">Job Description:</h3>
                        <div id="jvm-description" class="text-gray-600 text-sm leading-relaxed text-justify job-description-content"></div>
                    </div>

                    <div class="md:w-2/5 space-y-2 text-[#1D264F]">
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Type:</span> <span id="jvm-type"></span></p>
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Setup:</span> <span id="jvm-setup"></span></p>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Recommended Course/Program:</p>
                    <p id="jvm-program" class="text-sm leading-snug text-gray-600 mt-1"></p>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Industry / Sector:</p>
                    <p id="jvm-industry" class="text-sm leading-snug text-gray-600 mt-1"></p>
                </div>

                <div class="mt-10 pt-6 border-t flex items-center text-gray-500 text-sm font-semibold">
                    <i class="far fa-calendar-check mr-2"></i> Valid until <span id="jvm-valid" class="ml-1"></span>
                </div>
            </div>
        </div>
    </div>

    @include('partials.footer-employer')

</body>

<script>
    const HIRING_REMAINING_SLOTS = {{ $remainingSlots }};
    const BULK_ACTION_FORMS = { hire: 'bulkHireForm', decline: 'bulkDeclineForm', shortlist: 'bulkShortlistForm' };
    const BULK_ACTION_VERBS = { hire: 'hire', decline: 'decline', shortlist: 'shortlist' };

    function getCheckedApplicationIds() {
        return [...document.querySelectorAll('.applicant-checkbox:checked')].map(cb => cb.value);
    }

    function updateBulkActionUI() {
        const count = getCheckedApplicationIds().length;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkHireBtn').disabled = count === 0;
        document.getElementById('bulkDeclineBtn').disabled = count === 0;
        document.getElementById('bulkShortlistBtn').disabled = count === 0;

        const allCheckboxes = document.querySelectorAll('.applicant-checkbox');
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) {
            selectAll.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
        }
    }

    function toggleSelectAll(source) {
        document.querySelectorAll('tbody tr[data-status]').forEach(row => {
            if (row.style.display === 'none') return; // respect the current status filter
            const cb = row.querySelector('.applicant-checkbox');
            if (cb) cb.checked = source.checked;
        });
        updateBulkActionUI();
    }

    function submitBulkAction(action) {
        const ids = getCheckedApplicationIds();
        if (ids.length === 0) return;

        if (action === 'hire' && ids.length > HIRING_REMAINING_SLOTS) {
            alert('You can only hire ' + HIRING_REMAINING_SLOTS + ' more applicant(s) for this job post. Uncheck some and try again.');
            return;
        }

        if (!confirm('Are you sure you want to ' + BULK_ACTION_VERBS[action] + ' ' + ids.length + ' selected applicant(s)?')) {
            return;
        }

        const form = document.getElementById(BULK_ACTION_FORMS[action]);
        form.querySelectorAll('input[name="application_ids[]"]').forEach(el => el.remove());
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'application_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
    }

    function openJobViewModal(el) {
        document.getElementById('jvm-image').src = el.dataset.image;
        document.getElementById('jvm-title').textContent = el.dataset.title;
        document.getElementById('jvm-company').textContent = el.dataset.company;
        document.getElementById('jvm-posted').textContent = el.dataset.posted;
        document.getElementById('jvm-address').textContent = el.dataset.address;
        document.getElementById('jvm-description').innerHTML = el.dataset.description;
        document.getElementById('jvm-type').textContent = el.dataset.type;
        document.getElementById('jvm-setup').textContent = el.dataset.setup;
        document.getElementById('jvm-program').textContent = el.dataset.programs;
        document.getElementById('jvm-industry').textContent = el.dataset.industry;
        document.getElementById('jvm-valid').textContent = el.dataset.valid;

        document.getElementById('jobViewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeJobViewModal() {
        document.getElementById('jobViewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', function (event) {
        const modal = document.getElementById('jobViewModal');
        if (event.target === modal) closeJobViewModal();
    });

    function toggleDropdown(btn) {
        const dropdown = btn.nextElementSibling;
        const isOpen = dropdown.classList.contains('open');
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        if (!isOpen) {
            dropdown.classList.add('open');
            const rect = dropdown.getBoundingClientRect();
            if (rect.bottom > window.innerHeight) {
                dropdown.classList.add('drop-up');
            } else {
                dropdown.classList.remove('drop-up');
            }
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        }
    });

    function toggleStatusFilter(btn) {
        const dropdown = document.getElementById('statusFilterDropdown');
        const isOpen = dropdown.classList.contains('open');
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        if (!isOpen) dropdown.classList.add('open');
    }

    function filterStatus(status) {
        const rows = document.querySelectorAll('tbody tr[data-status]');
        rows.forEach(row => {
            if (status === 'All' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        document.getElementById('statusFilterDropdown').classList.remove('open');

        const visibleRows = [...rows].filter(r => r.style.display !== 'none');
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            if (visibleRows.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }
    }
</script>

</html>