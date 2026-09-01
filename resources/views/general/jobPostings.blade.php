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

    #job-list {
        overflow: visible;
    }
</style>


<body>
    @php
    $current_page = 'general.jobPostings';
    @endphp
    @include('partials.header-employer')
    @include('partials.success')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="max-w-6xl mx-auto p-6">

        <!-- HEADER ROW -->
        <div class="flex flex-col md:items-center md:justify-between mb-6 gap-4">

            <div class="w-full flex items-center justify-between mb-8">
                <!-- LEFT: TITLE -->
                <h1 class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    MY JOB POSTINGS
                </h1>
                <!-- RIGHT: BUTTON -->
                <button
                    onclick="openPostJobModal()"
                    class="flex items-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-6 py-2.5 rounded-lg font-bold text-sm tracking-widest shadow-lg transition-all transform hover:scale-105 active:scale-95">
                    <i class="fas fa-plus text-xs"></i>
                    <span>POST A NEW JOB</span>
                </button>
            </div>
        </div>

        <!-- SEARCH & FILTER -->
        @php $filters = $filters ?? []; @endphp
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-8">
            <form method="GET" action="{{ route('jobPosting.myJobPosts', ['id' => $users->user_id]) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <div class="relative">
                    <button type="submit" class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-[#C73D1A]">
                        <i class="fas fa-search"></i>
                    </button>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search job title or company" class="w-full pl-11 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-graduation-cap"></i>
                    </span>
                    <select name="program" onchange="this.form.submit()" class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        <option value="">Select Undergraduate Program</option>
                        @foreach ($programs as $program)
                        <option value="{{ $program->program_id }}" {{ (string) ($filters['program'] ?? '') === (string) $program->program_id ? 'selected' : '' }}>{{ $program->program_name }}</option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-briefcase"></i>
                    </span>
                    <select name="job_type" onchange="this.form.submit()" class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        <option value="">Job Type</option>
                        <option value="Full-Time" {{ ($filters['job_type'] ?? '') === 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
                        <option value="Part-Time" {{ ($filters['job_type'] ?? '') === 'Part-Time' ? 'selected' : '' }}>Part-Time</option>
                        <option value="Freelance" {{ ($filters['job_type'] ?? '') === 'Freelance' ? 'selected' : '' }}>Freelance</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
                </div>

                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <select name="date_posted" onchange="this.form.submit()" class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        <option value="">Date Posted</option>
                        <option value="24h" {{ ($filters['date_posted'] ?? '') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                        <option value="7d" {{ ($filters['date_posted'] ?? '') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30d" {{ ($filters['date_posted'] ?? '') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </span>
                </div>

            </form>
            @if (array_filter($filters))
            <div class="mt-3 text-right">
                <a href="{{ route('jobPosting.myJobPosts', ['id' => $users->user_id]) }}" class="text-xs font-bold text-gray-400 hover:text-[#C73D1A]">
                    <i class="fas fa-times mr-1"></i>CLEAR FILTERS
                </a>
            </div>
            @endif
        </div>


        <!--JOB POST CONTAINER-->
        <div id="job-list" class="space-y-6">

            @forelse($jobPostings as $job)
            <!-- JOB POST CONTAINER -->
            <div class="bg-white rounded-3xl shadow-md flex flex-col md:flex-row hover:shadow-lg transition-shadow md:min-h-[340px]">

                <!-- IMAGE -->
                <div class="md:w-1/4 h-48 md:h-auto relative overflow-hidden rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none group cursor-pointer"
                    role="button" tabindex="0" aria-label="View job details"
                    data-image="{{ asset('storage/' . $job->job_posting_image) }}"
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
                    onclick="openMyJobViewModal(this)"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openMyJobViewModal(this);}">
                    <img src="{{ asset('storage/' . $job->job_posting_image) }}"
                        class="object-cover w-full h-full opacity-60 group-hover:opacity-80 group-hover:scale-105 transition-all duration-300">
                    <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="bg-white/90 text-[#1D264F] text-[11px] font-bold px-3 py-1.5 rounded-full shadow-lg">
                            <i class="fas fa-eye mr-1"></i> VIEW DETAILS
                        </span>
                    </div>
                </div>

                <!-- CONTENT -->
                <div class="p-6 flex-1 flex flex-col justify-between">

                    <!-- TOP -->
                    <div>
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold uppercase text-[#0E0F3B]">{{ $job->job_posting_title }}</h2>
                                <p class="text-gray-600">{{ $job->job_posting_company }}</p>
                                <p class="text-gray-500 text-sm">{{ $job->job_posting_address }}</p>
                            </div>

                            <div class="flex flex-col items-end gap-1">
                                @if($job->job_approved)
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase whitespace-nowrap">
                                    <i class="fas fa-check-circle mr-1"></i>Approved
                                </span>
                                @else
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase whitespace-nowrap">
                                    <i class="fas fa-clock mr-1"></i>Pending Approval
                                </span>
                                @endif
                                <p class="text-xs text-gray-400 flex items-center">
                                    <i class="far fa-calendar-alt mr-1"></i> {{ $job->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        @if(!$job->job_approved)
                        <p class="mt-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            <i class="fas fa-circle-info mr-1"></i> This job post is awaiting super admin approval and won't be visible on the job board yet.
                        </p>
                        @endif

                        <!-- META -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-semibold">
                            <div>
                                <p class="text-[#0E0F3B]">Job Type: <span class="font-normal">{{ $job->job_posting_employment_type }}</span></p>
                                <p class="text-[#0E0F3B]">Job Setup: <span class="font-normal">{{ $job->job_posting_setup }}</span></p>
                            </div>

                            <div>
                                <p class="text-blue-900">
                                    Recommended Course/Program:
                                    <span class="font-normal text-black">
                                        @foreach ($job->programs as $program)
                                        {{ $program->program_name }}<br>
                                        @endforeach
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mt-4">
                            <p class="font-bold text-sm text-[#0E0F3B]">Job Description:</p>
                            <div class="relative max-h-[2.6rem] overflow-hidden">
                                <div class="text-gray-500 text-xs job-description-content">{!! $job->job_posting_description !!}</div>
                                <div class="absolute bottom-0 left-0 right-0 h-4 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTTOM -->
                    <div class="mt-6">

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                            <!-- LEFT -->
                            <div class="flex items-center gap-6 text-xs">
                                <p class="text-gray-400 flex items-center">
                                    <i class="far fa-calendar-check mr-1"></i> Valid until {{ $job->job_closing_date }}
                                </p>

                                @if(auth()->user()->user_role === 'employer')
                                <div class="text-sm font-bold">
                                    <p class="text-[#C73D1A]">APPLICATIONS RECEIVED: {{ $job->applicants->count() }}</p>
                                    <p class="text-[#C73D1A]">UNREAD APPLICATIONS: {{ $job->applicants->where('pivot.is_read', false)->count() }}</p>
                                    <p class="text-[#C73D1A]">HIRED: {{ $job->applicants->where('pivot.application_status', 'hired')->count() }} / {{ $job->hiring_limit }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- RIGHT BUTTONS -->
                            <div class="flex gap-2">
                                <a href='{{ route("jobApplication.showApplications", ["jobPostingId" => $job->job_posting_id]) }}' class="bg-[#1D46A4] text-white px-6 py-2 rounded-md font-bold text-xs hover:bg-[#0E0F3B]">
                                    VIEW APPLICANTS
                                </a>

                                <!--<button onclick="openEditPostModal({{ $job->job_posting_id }})"-->
                                <button onclick="openEditPostModal('{{ $job->job_posting_id }}')"
                                    class="border border-[#1D46A4] text-[#1D46A4] px-5 py-2 rounded-md font-bold text-xs hover:bg-[#1D46A4] hover:text-white transition">
                                    EDIT POST
                                </button>
                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="mt-4 flex items-center text-xs text-gray-500 border-t pt-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($job->employer->user->user_first_name . ' ' . $job->employer->user->user_last_name) }}&background=random"
                                class="w-6 h-6 rounded-full mr-2">
                            <span>Posted by <span class="font-bold text-black">{{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}</span></span>
                            @if($job->job_closing_date && \Carbon\Carbon::parse($job->job_closing_date)->isPast())
                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">
                                Down (Expired on {{ \Carbon\Carbon::parse($job->job_closing_date)->format('M d, Y') }})
                            </span>
                            @else
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                Active
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl shadow-md p-12 text-center text-gray-500">
                <i class="fas fa-briefcase text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">No job postings match your search.</p>
            </div>
            @endforelse

        </div>

        {{ $jobPostings->onEachSide(1)->links('partials.pagination') }}

    </main>

    <!--JOB POST MODAL-->

    <div id="jobModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">

        <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl relative max-h-[90vh] overflow-y-auto my-8">

            <div class="h-48 w-full relative rounded-t-3xl overflow-hidden">
                <img id="myjob-modal-img" src="" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>

                <button onclick="toggleModal()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white rounded-full p-1 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 id="myjob-title" class="text-3xl font-bold text-[#1D264F] uppercase tracking-tighter"></h2>
                        <div class="flex items-center text-gray-600 mt-1 space-x-4">
                            <p id="myjob-company" class="font-semibold text-lg"></p>
                            <span class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted <span id="myjob-posted"></span></span>
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

        <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl relative flex flex-col min-h-[600px] max-h-[90vh] overflow-y-auto my-8">

            @foreach($jobPostings as $job)
            <form id="editForm-{{ $job->job_posting_id }}" class="flex flex-col flex-1 hidden" action="{{ route('jobPosting.editJobPost', ['id' => $job->job_posting_id]) }}" method="post" enctype="multipart/form-data">
                @csrf

                <button type="button" onclick="closeEditPostModal()"
                    class="absolute top-11 right-8 text-gray-300 hover:text-gray-500 z-10">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>

                <div class="w-full pt-12 text-center">
                    <h2 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        EDIT JOB POST
                    </h2>
                </div>

                <div class="flex flex-col md:flex-row flex-1">

                    <div class="md:w-1/3 flex flex-col items-center justify-center p-8 bg-white">
                        <div id="editImageFrame-{{ $job->job_posting_id }}" class="w-full aspect-square border-4 border-[#1D264F] rounded-[2rem] flex flex-col items-center justify-center p-2 shadow-sm relative overflow-hidden">

                            <img id="editJobImagePreview-{{ $job->job_posting_id }}" src="{{ asset('storage/' . $job->job_posting_image) }}" class="w-full h-full object-cover rounded-[1.6rem]" />

                            <input type="file" name="job_posting_image" id="editJobImageInput-{{ $job->job_posting_id }}" accept="image/*" class="hidden" onchange="previewJobImage(this, null, null, 'editJobImagePreview-{{ $job->job_posting_id }}', null)">

                            <button type="button" onclick="document.getElementById('editJobImageInput-{{ $job->job_posting_id }}').click()" class="absolute bottom-4 bg-white/80 backdrop-blur-sm text-[#1D264F] px-4 py-1 rounded-full font-bold text-[10px] hover:bg-white transition-all">
                                CHANGE IMAGE
                            </button>
                        </div>
                    </div>

                    <div class="md:w-2/3 p-10 pt-6 space-y-4">

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

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Company Address <span class="text-red-500">*</span></label>
                            <input type="text" name="job_posting_address" value="{{ $job->job_posting_address }}"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Type <span class="text-red-500">*</span></label>
                                <select name="job_posting_employment_type"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none bg-no-repeat bg-[right_0.5rem_center] bg-[length:1em_1em]">
                                    <option {{ $job->job_posting_employment_type == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                                    <option {{ $job->job_posting_employment_type == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                    <option {{ $job->job_posting_employment_type == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Setup <span class="text-red-500">*</span></label>
                                <select name="job_posting_setup"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none">
                                    <option {{ $job->job_posting_setup == 'Remote' ? 'selected' : '' }}>Remote</option>
                                    <option {{ $job->job_posting_setup == 'On-site' ? 'selected' : '' }}>On-Site</option>
                                    <option {{ $job->job_posting_setup == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Recommended Course/Program <span class="text-red-500">*</span></label>

                            <div id="editCourseInputContainer-{{ $job->job_posting_id }}" class="space-y-2">
                                @foreach($job->programs as $index => $jobProgram)
                                <div class="flex items-center gap-3 course-row ">
                                    <select name="program[]"
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

                        <div class="flex justify-end gap-4 mt-10 p-5">
                            <button type="button" onclick="closeEditPostModal()"
                                class="px-10 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-md font-bold text-sm hover:bg-[#0E0F3B] hover:text-white transition">
                                CANCEL
                            </button>

                            <button type="submit"
                                class="px-12 py-2 bg-[#0E0F3B] text-white rounded-md font-bold text-sm hover:bg-blue-900 transition">
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

</body>

<script>
    // Combined Bookmark Toggle & Tooltip Logic
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const tooltip = this.parentElement.querySelector('.bookmark-tooltip');

            if (icon.classList.contains('far')) {
                icon.classList.replace('far', 'fas');
                icon.classList.add('text-blue-900');
                tooltip.classList.remove('invisible', 'opacity-0');
                tooltip.classList.add('visible', 'opacity-100', '-translate-y-1');
                setTimeout(() => {
                    tooltip.classList.add('invisible', 'opacity-0');
                    tooltip.classList.remove('visible', 'opacity-100', '-translate-y-1');
                }, 2000);
            } else {
                icon.classList.replace('fas', 'far');
                icon.classList.remove('text-blue-900');
            }
        });
    });

    // Handle Apply Button Interaction
    document.querySelectorAll('.apply-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.innerText = 'APPLIED';
            this.classList.remove('bg-[#1e293b]', 'hover:bg-black');
            this.classList.add('bg-gray-400', 'cursor-not-allowed');
            this.disabled = true;
        });
    });

    // Share Button Copy Logic
    function copyJobLink(button) {
        const dummyUrl = "https://alumnihub.example/jobs/12345";
        navigator.clipboard.writeText(dummyUrl).then(() => {
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

    // Job post modal
    function toggleModal() {
        const modal = document.getElementById('jobModal');
        modal.classList.toggle('hidden');
        document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
    }

    // Clicking a job's image opens this same modal populated with that
    // job's real data (read off the element's own data-* attributes) —
    // a quick, read-only way to see the full post without opening Edit.
    function openMyJobViewModal(el) {
        document.getElementById('myjob-modal-img').src = el.dataset.image;
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

        const statusEl = document.getElementById('myjob-status');
        const approved = el.dataset.approved === '1';
        statusEl.textContent = approved ? 'Approved' : 'Pending Approval';
        statusEl.className = 'text-[10px] font-bold px-2 py-1 rounded-full uppercase whitespace-nowrap ' +
            (approved ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700');

        document.getElementById('jobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('jobModal');
        if (event.target == modal) toggleModal();
    }

    // Job posts modal tooltip
    function showModalTooltip(tooltipId, buttonElement) {
        const tooltip = document.getElementById(tooltipId);
        tooltip.classList.remove('invisible', 'opacity-0');
        tooltip.classList.add('opacity-100');

        if (tooltipId === 'modal-bookmark-tooltip') {
            const icon = document.getElementById('modal-bookmark-icon');
            if (icon.classList.contains('far')) {
                icon.classList.replace('far', 'fas');
                tooltip.querySelector('div').firstChild.textContent = 'Job post saved!';
            } else {
                icon.classList.replace('fas', 'far');
                tooltip.querySelector('div').firstChild.textContent = 'Removed from bookmarks';
            }
        }

        if (tooltipId === 'modal-share-tooltip') {
            navigator.clipboard.writeText(window.location.href);
        }

        setTimeout(() => {
            tooltip.classList.add('invisible', 'opacity-0');
            tooltip.classList.remove('opacity-100');
        }, 2000);
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