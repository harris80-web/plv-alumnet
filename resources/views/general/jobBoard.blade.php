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
    <link rel="preload" as="image" href="{{ asset('assets/heroSectionBackground.png') }}">
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

    .tab-active {
        color: #C73D1A;
        border-bottom: 3px solid #C73D1A;
    }

    #postJobModal>div {
        overflow-y: auto;
        max-height: 90vh;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #postJobModal>div::-webkit-scrollbar {
        display: none;
    }
</style>


<body>
    @php
    $current_page = Route::currentRouteName();
    $activeTab ??= 'board';
    $filters ??= [];
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
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @if($user && $user->user_role === 'alumni')
    <nav class="bg-white border-b sticky top-0 z-10 shadow-md">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex justify-start space-x-8 uppercase text-sm font-bold tracking-wide">
                <a href="{{ route('jobPosting.jobBoard') }}" class="py-4 transition-all {{ $activeTab === 'board' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Job Board</a>
                <button class="py-4 text-gray-500 hover:text-orange-600 transition-all">My Applications</button>
                <a href="{{ route('jobPosting.bookmarks') }}" class="py-4 transition-all {{ $activeTab === 'bookmarks' ? 'tab-active' : 'text-gray-500 hover:text-orange-600' }}">Bookmarks</a>
            </div>
        </div>
    </nav>
    @endif

    @include('partials.success')

    <main class="max-w-5xl mx-auto p-6">
        <div class="w-full text-center mb-8">
            <h1 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                @if($activeTab === 'bookmarks')
                MY BOOKMARKS
                @elseif($user && $user->user_role === 'alumni')
                ALUMNI CAREER HUB
                @else
                JOB BOARD
                @endif
            </h1>
        </div>

        <!-- SEARCH & FILTER -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-8">
            <form method="GET" action="{{ $activeTab === 'bookmarks' ? route('jobPosting.bookmarks') : route('jobPosting.jobBoard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">

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
                <a href="{{ $activeTab === 'bookmarks' ? route('jobPosting.bookmarks') : route('jobPosting.jobBoard') }}" class="text-xs font-bold text-gray-400 hover:text-[#C73D1A]">
                    <i class="fas fa-times mr-1"></i>CLEAR FILTERS
                </a>
            </div>
            @endif
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
        <div id="job-list" class="space-y-6">
            <!--JOB POST CONTAINER -->

            @forelse($jobPostings as $job)
            @include('partials.job-post-card', ['job' => $job])
            @empty
            <div class="bg-white rounded-3xl shadow-md p-12 text-center text-gray-500">
                @if($activeTab === 'bookmarks')
                <i class="far fa-bookmark text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">You haven't bookmarked any jobs yet.</p>
                @else
                <i class="fas fa-briefcase text-4xl mb-3 text-gray-300"></i>
                <p class="font-semibold">No job postings match your search.</p>
                @endif
            </div>
            @endforelse

        </div>

        {{ $jobPostings->onEachSide(1)->links('partials.pagination') }}
    </main>

    <!--JOB POST MODAL-->

    <div id="jobModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl relative">

            <div class="h-48 w-full relative rounded-t-3xl overflow-hidden">
                <img id="modal-img" src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&q=80&w=800" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>

                <button onclick="toggleModal()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white rounded-full p-1 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 id="modal-title" class="text-3xl font-bold text-[#1D264F] uppercase tracking-tighter">JOB TITLE</h2>
                        <div class="flex items-center text-gray-600 mt-1 space-x-4">
                            <p id="modal-company" class="font-semibold text-lg">Company Name</p>
                            <span id="modal-date" class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted 2 days ago</span>
                        </div>
                        <p id="modal-address" class="text-gray-500 font-medium">Company Address</p>
                    </div>

                    <div class="relative">
                        <div id="modal-share-tooltip" class="invisible opacity-0 absolute bottom-full right-0 mb-2 pointer-events-none transition-all duration-300 z-50">
                            <div class="bg-gray-800 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                                Link Copied!
                                <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-gray-800"></div>
                            </div>
                        </div>
                        <button onclick="showModalTooltip('modal-share-tooltip')" class="text-[#1D264F] text-2xl hover:scale-110 transition-transform">
                            <i class="fas fa-share-nodes"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-8 flex flex-col md:flex-row gap-8">
                    <div class="md:w-3/5">
                        <h3 class="font-bold text-[#0E0F3B] mb-3">Job Description:</h3>
                        <div id="modal-description" class="text-gray-600 text-sm leading-relaxed text-justify job-description-content">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent placerat, nulla quis aliquam fringilla, nulla elit accumsan nisi, vel feugiat massa erat vel eros. Curabitur sed massa vel leo accumsan imperdiet.
                        </div>
                    </div>

                    <div class="md:w-2/5 space-y-2 text-[#1D264F]">
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Type:</span> <span id="modal-job-type">Full-Time</span></p>
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Setup:</span> <span id="modal-job-setup">Remote</span></p>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Recommended Course/Program:</p>
                    <p id="modal-programs" class="text-sm leading-snug text-gray-600 mt-1">
                        BSIT - Bachelor of Science in Information Technology
                    </p>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Industry / Sector:</p>
                    <p id="modal-industry" class="text-sm leading-snug text-gray-600 mt-1"></p>
                </div>

                <div class="mt-10 pt-6 border-t flex items-center justify-between">
                    <div id="modal-valid" class="text-gray-500 text-sm flex items-center font-semibold">
                        <i class="far fa-calendar-check mr-2"></i> Valid until
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($user && $user->user_role === 'employer')
    <!-- POST A NEW JOB MODAL-->
    <div id="postJobModal"
        class="fixed inset-0 z-[110] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 overflow-y-auto">

        <div class="bg-white w-full max-w-3xl rounded-[1.8rem] shadow-2xl relative max-h-[90vh] overflow-y-auto my-8">

            <form action="{{ route('jobPosting.addJobPost', ['id' => $user->user_id]) }}"
                method="POST"
                enctype="multipart/form-data">
                @csrf

                <!-- CLOSE -->
                <button type="button"
                    onclick="closePostModal()"
                    class="absolute top-5 right-5 text-gray-300 hover:text-gray-500 transition-colors z-10">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>

                <!-- TITLE -->
                <div class="w-full pt-6 text-center">
                    <h2
                        class="inline-block text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">
                        POST A NEW JOB
                    </h2>
                </div>

                <!-- CONTENT -->
                <div class="p-5 text-sm">

                    <!-- TOP GRID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- LEFT -->
                        <div class="space-y-2">

                            <!-- JOB TITLE -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Job Title <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="job_posting_title"
                                    placeholder="e.g., Senior Full Stack Developer"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <!-- COMPANY NAME -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Company Name <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="job_posting_company"
                                    value="@if ($user->user_role == 'alumni'){{ $user->employer_company_name }}@else{{ $user->employer->employer_company_name }}@endif"
                                    readonly
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs bg-gray-50 focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <!-- TYPE + SETUP -->
                            <div class="grid grid-cols-2 gap-3">

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                        Job Type <span class="text-red-500">*</span>
                                    </label>

                                    <select name="job_posting_employment_type"
                                        class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] appearance-none bg-white">
                                        <option>Select Type</option>
                                        <option>Full-Time</option>
                                        <option>Part-Time</option>
                                        <option>Freelance</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                        Job Setup <span class="text-red-500">*</span>
                                    </label>

                                    <select name="job_posting_setup"
                                        class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] appearance-none bg-white">
                                        <option>Select Setup</option>
                                        <option>Remote</option>
                                        <option>On-Site</option>
                                        <option>Hybrid</option>
                                    </select>
                                </div>

                            </div>

                            <!-- ADDRESS -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Company Address <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                    name="job_posting_address"
                                    placeholder="Enter business address"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <!-- DATE -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Closing / Validity Date <span class="text-red-500">*</span>
                                </label>

                                <input type="date"
                                    name="job_closing_date"
                                    id="job_closing_date"
                                    class="w-full border border-[#0E0F3B] uppercase rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] text-gray-400"
                                    onchange="this.classList.remove('text-gray-400'); this.classList.add('text-black')">
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="space-y-2">

                            <!-- IMAGE -->
                            <div class="space-y-1">

                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Upload Image <span class="text-red-500">*</span>
                                </label>

                                <div id="imageFrame"
                                    class="w-full h-[150px] border-2 border-dashed border-[#1D264F] rounded-2xl flex flex-col items-center justify-center bg-[#F8FAFC] relative overflow-hidden">

                                    <div id="uploadPlaceholder"
                                        class="flex flex-col items-center justify-center text-center px-4">

                                        <div
                                            class="w-12 h-12 rounded-full bg-[#1D264F] flex items-center justify-center mb-2 shadow-md">
                                            <i class="fas fa-cloud-upload-alt text-white text-lg"></i>
                                        </div>

                                        <p class="text-[11px] text-gray-500 mb-2">
                                            Upload job image
                                        </p>

                                        <button type="button" onclick="document.getElementById('jobImageInput').click()" class="bg-[#0E0F3B] text-white px-4 py-1.5 rounded-lg font-semibold text-[10px] tracking-wide hover:bg-blue-900 transition-all shadow-sm">
                                            CHOOSE FILE
                                        </button>

                                    </div>

                                    <img id="jobImagePreview" src="#" class="hidden w-full h-full object-cover" />

                                    <input type="file" name="job_posting_image" id="jobImageInput" accept="image/*" class="hidden" onchange="previewJobImage(this)">

                                    <button id="changeImgBtn" type="button" onclick="document.getElementById('jobImageInput').click()" class="hidden absolute bottom-2 right-2 bg-white/90 backdrop-blur-sm text-[#1D264F] px-3 py-1 rounded-lg font-bold text-[9px] hover:bg-white transition-all shadow-md">
                                        CHANGE IMAGE
                                    </button>

                                </div>

                            </div>

                            <!-- COURSE -->
                            <div class="space-y-1">

                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Recommended Course/Program
                                    <span class="text-red-500">*</span>
                                </label>

                                <div id="course-input-container" class="space-y-2">

                                    <div class="flex items-center gap-2 course-row">

                                        <select name="program[]"
                                            class="flex-1 border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] bg-white w-full">

                                            <option selected disabled>
                                                Select Undergraduate Program
                                            </option>

                                            @foreach ($programs as $program)
                                            <option value="{{ $program->program_id }}">
                                                {{ $program->program_name }}
                                            </option>
                                            @endforeach

                                        </select>

                                        <button type="button" onclick="addCourseField()" class="bg-[#1D264F] text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#0E0F3B] transition-colors shadow-sm">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>

                                    </div>

                                </div>

                                <p id="course-limit-msg" class="text-[9px] text-gray-400 italic hidden">
                                    Maximum of 3 programs reached.
                                </p>

                            </div>

                        </div>

                    </div>

                    <!-- INDUSTRY -->
                    <div class="space-y-1 mt-4">
                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">Industry / Sector</label>
                        <select name="industry_id"
                            class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white">
                            <option value="">Select Industry</option>
                            @foreach($industries as $industry)
                            <option value="{{ $industry->industry_id }}">{{ $industry->industry_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- REQUIRED SKILLS -->
                    <div class="mt-4">
                        @include('partials.job-posting-skills-field', ['uid' => 'board-create'])
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="space-y-1 mt-4">

                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                            Job Description <span class="text-red-500">*</span>
                        </label>

                        @include('partials.rich-text-editor', ['uid' => 'board-create', 'fieldName' => 'job_posting_description'])
                    </div>

                    <div id="modalErrors" class="hidden flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mt-5 mb-7 shadow-sm">
                        <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                        <div>
                            <ul id="modalErrorList" class="text-xs space-y-0.5 list-disc list-inside text-red-600"></ul>
                        </div>
                    </div>

                    <!-- error messages-->
                    @if ($errors->any())
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mb-6 shadow-sm">
                        <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                        <div>
                            <ul class="text-xs space-y-0.5 list-disc list-inside text-red-600">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => openPostJobModal());
                    </script>
                    @endif


                    <!-- BUTTONS -->
                    <div class="flex justify-end gap-3 mt-5">
                        <button type="button"
                            onclick="closePostModal()"
                            class="px-6 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-lg font-bold text-xs hover:bg-[#0E0F3B] hover:text-white transition-colors">
                            CANCEL
                        </button>

                        <button type="button"
                            onclick="handleJobSubmit()"
                            class="px-7 py-2 bg-[#0E0F3B] text-white rounded-lg font-bold text-xs hover:bg-blue-900 transition-colors shadow-md">
                            POST
                        </button>
                    </div>

                </div>

            </form>
        </div>
    </div>

    <!-- POST JOB CONFIRMATION MODAL -->
    <div id="postConfirmModal" class="fixed inset-0 z-[210] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-paper-plane text-[#1D46A4] text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-[#0E0F3B] mb-2">Post this Job?</h2>
            <p class="text-gray-500 text-sm mb-6">Please confirm that all job details are correct before submitting for admin approval.</p>
            <div class="flex gap-3">
                <button onclick="cancelPostConfirm()" class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-100 transition-colors">
                    CANCEL
                </button>
                <button onclick="confirmPostJob()" class="flex-1 bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-[#1D46A4] transition-colors">
                    YES, POST IT
                </button>
            </div>
        </div>
    </div>

    <!-- JOB POST PENDING APPROVAL MODAL -->
    <div id="pendingModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full relative text-center">
            <button onclick="closePendingModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="flex justify-center mb-6">
                <div class="bg-[#0E0F3B] rounded-full p-4">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-2">
                Job Post is now Pending for Approval
            </h2>
            <p class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-sm mb-8 leading-relaxed">
                Your job post has been queued for review by the PLV-AlumNet Admin. We'll notify you as soon as it is approved.
            </p>
            <button onclick="closePendingModal()" class="w-full bg-[#0E0F3B] text-white py-3 rounded-md font-bold hover:bg-blue-900 transition-colors uppercase tracking-wider">
                Back to Job Board
            </button>
        </div>
    </div>
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

    //job post modal script

    function openJobModal(btn) {
        document.getElementById('modal-img').src = btn.dataset.image;
        document.getElementById('modal-title').textContent = btn.dataset.title;
        document.getElementById('modal-company').textContent = btn.dataset.company;
        document.getElementById('modal-date').innerHTML = '<i class="far fa-calendar-alt mr-2"></i> ' + btn.dataset.date;
        document.getElementById('modal-address').textContent = btn.dataset.address;
        document.getElementById('modal-description').innerHTML = btn.dataset.description;
        document.getElementById('modal-job-type').textContent = btn.dataset.type;
        document.getElementById('modal-job-setup').textContent = btn.dataset.setup;
        document.getElementById('modal-programs').textContent = btn.dataset.programs;
        document.getElementById('modal-industry').textContent = btn.dataset.industry;
        document.getElementById('modal-valid').innerHTML = '<i class="far fa-calendar-check mr-2"></i> Valid until: ' + btn.dataset.valid;

        document.getElementById('jobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function toggleModal() {
        const modal = document.getElementById('jobModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Optional: Close modal if user clicks outside the white box
    window.onclick = function(event) {
        const modal = document.getElementById('jobModal');
        if (event.target == modal) {
            toggleModal();
        }
    }

    //job posts modal tooltip
    function showModalTooltip(tooltipId) {
        const tooltip = document.getElementById(tooltipId);

        tooltip.classList.remove('invisible', 'opacity-0');
        tooltip.classList.add('opacity-100');

        if (tooltipId === 'modal-share-tooltip') {
            navigator.clipboard.writeText(window.location.href);
        }

        setTimeout(() => {
            tooltip.classList.add('invisible', 'opacity-0');
            tooltip.classList.remove('opacity-100');
        }, 2000);
    }

    //POST A NEW JOB MODAL
    function openPostJobModal() {
        const modal = document.getElementById('postJobModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePostModal() {
        const modal = document.getElementById('postJobModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Ensure clicking outside the modal content closes it
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('postJobModal');
        if (e.target === modal) {
            closePostModal();
        }
    });

    //POST A NEW JOB MODAL - UPLOAD DOCUMENT
    function previewJobImage(input) {
        const frame = document.getElementById('imageFrame');
        const placeholder = document.getElementById('uploadPlaceholder');
        const preview = document.getElementById('jobImagePreview');
        const changeBtn = document.getElementById('changeImgBtn');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // Set image source
                preview.src = e.target.result;

                // Show image and "Change" button, hide placeholder
                preview.classList.remove('hidden');
                changeBtn.classList.remove('hidden');
                placeholder.classList.add('hidden');

                // Remove padding from frame to make image full-size
                frame.classList.remove('p-6');
                frame.classList.add('p-0');
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    //POST A NEW JOB MODAL - ADD COURSE/PROGRAM INPUT FIELD
    function addCourseField() {
        const container = document.getElementById('course-input-container');
        const rows = container.getElementsByClassName('course-row');

        if (rows.length < 3) {
            // Clone the first row
            const newRow = rows[0].cloneNode(true);

            // Reset the selection in the new row
            const select = newRow.querySelector('select');
            select.selectedIndex = 0;

            // Change the button on the NEW row to a "minus" button
            const btn = newRow.querySelector('button');
            btn.innerHTML = '<i class="fas fa-minus text-xs"></i>';
            btn.classList.replace('bg-[#1D264F]', 'bg-red-500');
            btn.setAttribute('onclick', 'removeCourseField(this)');

            // Append the row
            container.appendChild(newRow);

            // Hide add button on the original row if limit is reached
            if (rows.length === 3) {
                document.getElementById('course-limit-msg').classList.remove('hidden');
                document.getElementById('add-course-btn').classList.add('opacity-50', 'pointer-events-none');
            }
        }
    }

    function removeCourseField(button) {
        const row = button.closest('.course-row');
        row.remove();

        // Re-enable the add button and hide limit message
        document.getElementById('course-limit-msg').classList.add('hidden');
        document.getElementById('add-course-btn').classList.remove('opacity-50', 'pointer-events-none');
    }

    function handleJobSubmit() {
        const form = document.querySelector('#postJobModal form');
        const errors = [];

        // Get field values
        const title = form.querySelector('[name="job_posting_title"]').value.trim();
        const address = form.querySelector('[name="job_posting_address"]').value.trim();
        const description = form.querySelector('[name="job_posting_description"]').value.trim();
        const employmentType = form.querySelector('[name="job_posting_employment_type"]').value;
        const setup = form.querySelector('[name="job_posting_setup"]').value;
        const closingDate = form.querySelector('[name="job_closing_date"]').value;
        const image = form.querySelector('[name="job_posting_image"]').files.length;
        const programs = form.querySelectorAll('[name="program[]"]');

        // Validate each field
        if (!title) errors.push('Job title is required.');
        if (employmentType === 'Select Type') errors.push('Please select a job type.');
        if (setup === 'Select Setup') errors.push('Please select a job setup.');
        if (!address) errors.push('Company address is required.');
        if (!closingDate) errors.push('Closing / validity date is required.');
        if (!image) errors.push('Please upload a job image.');
        if (!description) errors.push('Job description is required.');

        // Check if at least one program is selected
        let programSelected = false;
        programs.forEach(select => {
            if (select.selectedIndex > 0) programSelected = true;
        });
        if (!programSelected) errors.push('Please select at least one recommended program.');

        // Show errors inside modal if any
        if (errors.length > 0) {
            const errorBox = document.getElementById('modalErrors');
            const errorList = document.getElementById('modalErrorList');

            errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
            errorBox.classList.remove('hidden');

            // Scroll to errors smoothly inside modal
            errorBox.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
            return;
        }

        // No errors — hide error box and show confirmation
        document.getElementById('modalErrors').classList.add('hidden');
        document.getElementById('postJobModal').classList.add('hidden');
        document.getElementById('postConfirmModal').classList.remove('hidden');
    }

    function cancelPostConfirm() {
        document.getElementById('postConfirmModal').classList.add('hidden');
        document.getElementById('postJobModal').classList.remove('hidden');
    }

    function confirmPostJob() {
        document.getElementById('postConfirmModal').classList.add('hidden');
        document.getElementById('pendingModal').classList.remove('hidden');
    }

    function closePendingModal() {
        document.getElementById('pendingModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.querySelector('#postJobModal form').submit();
    }
</script>

</html>
