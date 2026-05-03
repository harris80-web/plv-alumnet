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
    @foreach($jobPostings as $job)
    <div>
        <h2>{{ $job->job_posting_title }}</h2>
        <p><strong>Company:</strong> {{ $job->job_posting_company }}</p>
        <p><strong>Location:</strong> {{ $job->job_posting_address }}</p>
        <p><strong>Posted by:</strong> {{ $job->employer->user->user_first_name }} {{ $job->employer->user->user_last_name }}</p>
        <p><strong>Job type:</strong> {{ $job->job_posting_employment_type }}</p>
        <p><strong>Job setup:</strong> {{ $job->job_posting_setup }}</p>
        <p><strong>Recommended program:</strong> 
        @foreach ($job->programs as $program)
            {{ $program->program_name }}
            <br><br>
        @endforeach
        </p>
        <p><strong>Description:</strong> {{ $job->job_posting_description }}</p>
        <p><strong>Valid until:</strong> {{ $job->job_closing_date }}</p>
        <img src="{{ asset('storage/'.$job->job_posting_image) }}" alt="Job Image" style="max-width: 200px; max-height: 200px;">
        <br><br><br>
        <h2>EDIT JOB POSTING</h2>
        <form action="{{ route('jobPosting.editJobPost', ['id' => $job->job_posting_id]) }}" method="post" enctype="multipart/form-data">
            @csrf
                <div>
                    <img src="{{ asset('storage/'.$job->job_posting_image) }}" alt="Job Image" style="max-width: 200px; max-height: 200px;">
                    <label for="job_posting_image">Job photo:</label>
                    <input type="file" id="job_posting_image" name="job_posting_image">
                </div>
                <div>
                    <label for="job_posting_title">Job title:</label>
                    <input type="text" id="job_posting_title" name="job_posting_title" value="{{ $job->job_posting_title }}">
                </div>
                <div>
                    <label for="job_posting_company">Business name:</label>
                    <input type="text" id="job_posting_company" name="job_posting_company" value="{{ $job->job_posting_company }}">
                </div>
                <div>
                    <label for="job_posting_address">Business address:</label>
                    <input type="text" id="job_posting_address" name="job_posting_address" value="{{ $job->job_posting_address }}">
                </div>
                <div>
                    <label for="job_posting_employment_type">Job type:</label>
                    <input type="text" id="job_posting_employment_type" name="job_posting_employment_type" value="{{ $job->job_posting_employment_type }}">
                </div>
                <div>
                    <label for="job_posting_setup">Job setup:</label>
                    <input type="text" id="job_posting_setup" name="job_posting_setup" value="{{ $job->job_posting_setup }}">
                </div>
                <div>
                    <label for="program">Recommended program:</label>
                    <select name="program[]" id="">
                        <option value="" selected hidden>Select a program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->program_id }}" >
                                {{ $program->program_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="job_posting_description">Job description:</label>
                    <input type="text" id="job_posting_description" name="job_posting_description" value="{{ $job->job_posting_description }}">
                </div>
                <div>
                    <label for="job_closing_date">Validity date:</label>
                    <input type="date" id="job_closing_date" name="job_closing_date" value="{{ $job->job_closing_date }}">
                </div>
                <button type="submit">Save Changes</button>
        </form>
    </div>
    @endforeach
</body>

</html>-->

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
    $current_page = 'employer_job_postings';
    @endphp
    @include('partials.header-employer')

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

        <!-- FILTER ROW -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" placeholder="Search" class="w-full pl-11 pr-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-graduation-cap"></i>
                </span>
                <select class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    <option selected disabled>Select Undergraduate Program</option>

                    <option>Bachelor of Arts in Communication</option>
                    <option>Bachelor of Early Childhood Education</option>
                    <option>Bachelor of Science in Accountancy</option>

                    <optgroup label="BS in Business Administration">
                        <option>BSBA - Major in Financial Management</option>
                        <option>BSBA - Major in Human Resource Management</option>
                        <option>BSBA - Major in Marketing Management</option>
                    </optgroup>

                    <option>BSCE - Bachelor of Science in Civil Engineering</option>
                    <option>BSEE - Bachelor of Science in Electrical Engineering</option>
                    <option>BSIT - Bachelor of Science in Information Technology</option>
                    <option>Bachelor of Science in Psychology</option>
                    <option>Bachelor of Public Administration</option>
                    <option>Bachelor of Science in Social Work</option>

                    <optgroup label="Bachelor of Secondary Education">
                        <option>BSEd - Major in English</option>
                        <option>BSEd - Major in Filipino</option>
                        <option>BSEd - Major in Mathematics</option>
                        <option>BSEd - Major in Science</option>
                        <option>BSEd - Major in Social Studies</option>
                    </optgroup>
                </select>
                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-briefcase"></i>
                </span>
                <select class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    <option>Job Type</option>
                    <option>Full-Time</option>
                    <option>Part-Time</option>
                    <option>Freelance</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <select class="w-full pl-11 pr-10 py-2 border rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    <option>Date Posted</option>
                    <option>Last 24 Hours</option>
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>

        </div>


        <!--JOB POST CONTAINER-->
        <div id="job-list" class="space-y-6">

            @foreach($jobPostings as $job)
            <!-- JOB POST CONTAINER -->
            <div class="bg-white rounded-3xl shadow-md flex flex-col md:flex-row overflow-hidden hover:shadow-lg transition-shadow">

                <!-- IMAGE -->
                <div class="md:w-1/4 h-48 md:h-auto relative overflow-hidden">
                    <img src="{{ asset('storage/' . $job->job_posting_image) }}"
                        class="object-cover w-full h-full opacity-60">
                    <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>
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

                            <p class="text-xs text-gray-400 flex items-center">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $job->created_at->diffForHumans() }}
                            </p>
                        </div>

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
                            <p class="text-gray-500 text-xs line-clamp-2">{{ $job->job_posting_description }}</p>
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
                                    <p class="text-[#C73D1A]">UNREAD APPLICATIONS: {{ $job->applicants->where('pivot.is_read', 0)->count() }}</p>
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
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>


        <!-- PAGINATION -->
        <div class="mt-10 flex justify-center items-center gap-4 text-gray-500 text-sm">
            <button><i class="fas fa-chevron-left"></i></button>
            <button class="font-bold text-black">1</button>
            <button>2</button>
            <button>3</button>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>

    </main>

    <!--JOB POST MODAL-->

    <div id="jobModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl relative">

            <div class="h-48 w-full relative rounded-t-3xl overflow-hidden">
                <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&q=80&w=800" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>

                <button onclick="toggleModal()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white rounded-full p-1 transition-colors">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-3xl font-bold text-[#1D264F] uppercase tracking-tighter">JOB TITLE</h2>
                        <div class="flex items-center text-gray-600 mt-1 space-x-4">
                            <p class="font-semibold text-lg">Business Name</p>
                            <span class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted 2 days ago</span>
                        </div>
                        <p class="text-gray-500 font-medium">Business Address</p>
                    </div>

                    <div class="relative">
                        <div id="modal-bookmark-tooltip" class="invisible opacity-0 absolute bottom-full right-0 mb-2 pointer-events-none transition-all duration-300 z-50">
                            <div class="bg-blue-900 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                                Job post saved!
                                <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-blue-900"></div>
                            </div>
                        </div>
                        <button onclick="showModalTooltip('modal-bookmark-tooltip', this)" class="text-[#1D264F] text-3xl hover:scale-110 transition-transform">
                            <i id="modal-bookmark-icon" class="far fa-bookmark"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-8 flex flex-col md:flex-row gap-8">
                    <div class="md:w-3/5">
                        <h3 class="font-bold text-[#0E0F3B] mb-3">Job Description:</h3>
                        <p class="text-gray-600 text-sm leading-relaxed text-justify">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent placerat, nulla quis aliquam fringilla, nulla elit accumsan nisi, vel feugiat massa erat vel eros. Curabitur sed massa vel leo accumsan imperdiet.
                        </p>
                    </div>

                    <div class="md:w-2/5 space-y-2 text-[#1D264F]">
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Type:</span> Full-Time</p>
                        <p class="flex justify-between text-sm"><span class="font-bold">Job Setup:</span> Remote</p>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <p class="font-bold text-sm">Recommended Course/Program:</p>
                    <p class="text-sm leading-snug text-gray-600 mt-1">
                        BSIT - Bachelor of Science in Information Technology
                    </p>
                </div>

                <div class="mt-10 pt-6 border-t flex items-center justify-between">
                    <div class="text-gray-500 text-sm flex items-center font-semibold">
                        <i class="far fa-calendar-check mr-2"></i> Valid until
                    </div>

                    <div class="flex items-center space-x-6">
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
                </div>
            </div>
        </div>
    </div>

    <!-- POST A NEW JOB MODAL-->
    <div id="postJobModal" class="fixed inset-0 z-[110] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col min-h-[600px]">

            <form class="flex flex-col flex-1" action="{{ route('jobPosting.addJobPost', ['id' => $users->user_id]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <button type="button" onclick="closePostModal()" class="absolute top-11 right-8 text-gray-300 hover:text-gray-500 transition-colors z-10">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>

                <div class="w-full pt-12 text-center">
                    <h2 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">
                        POST A NEW JOB
                    </h2>
                </div>

                <div class="flex flex-col md:flex-row flex-1">

                    <!-- IMAGE UPLOAD -->
                    <div class="md:w-1/3 flex flex-col items-center justify-center p-8 bg-white">
                        <div id="imageFrame" class="w-full aspect-square border-4 border-[#1D264F] rounded-[2rem] flex flex-col items-center justify-center p-2 shadow-sm relative overflow-hidden">

                            <div id="uploadPlaceholder" class="flex flex-col items-center justify-center">
                                <i class="fas fa-upload text-6xl text-[#1D264F] mb-4"></i>
                                <button type="button" onclick="document.getElementById('jobImageInput').click()" class="bg-[#1D264F] text-white px-8 py-2 rounded-full font-bold text-xs tracking-widest hover:bg-[#0E0F3B] transition-colors">
                                    UPLOAD
                                </button>
                            </div>

                            <img id="jobImagePreview" src="#" class="hidden w-full h-full object-cover rounded-[1.6rem]" />

                            <input type="file" name="job_posting_image" id="jobImageInput" accept="image/*" class="hidden" onchange="previewJobImage(this)">

                            <button id="changeImgBtn" type="button" onclick="document.getElementById('jobImageInput').click()" class="hidden absolute bottom-4 bg-white/80 backdrop-blur-sm text-[#1D264F] px-4 py-1 rounded-full font-bold text-[10px] hover:bg-white transition-all">
                                CHANGE IMAGE
                            </button>
                        </div>
                    </div>

                    <!-- FORM FIELDS -->
                    <div class="md:w-2/3 p-10 pt-6 space-y-4">

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Title <span class="text-red-500">*</span></label>
                                <input type="text" name="job_posting_title" placeholder="e.g., Senior Full Stack Developer"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Business Name <span class="text-red-500">*</span></label>
                                <input type="text" name="job_posting_company" placeholder="Enter the registered name"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Business Address <span class="text-red-500">*</span></label>
                            <input type="text" name="job_posting_address" placeholder="Enter business address"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Type <span class="text-red-500">*</span></label>
                                <select name="job_posting_employment_type"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none bg-no-repeat bg-[right_0.5rem_center] bg-[length:1em_1em]">
                                    <option>Select Type (e.g., Full-time)</option>
                                    <option>Full-Time</option>
                                    <option>Part-Time</option>
                                    <option>Freelance</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Setup <span class="text-red-500">*</span></label>
                                <select name="job_posting_setup"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none">
                                    <option>Select Setup (e.g., Remote)</option>
                                    <option>Remote</option>
                                    <option>On-Site</option>
                                    <option>Hybrid</option>
                                </select>
                            </div>
                        </div>

                        <!-- COURSE -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Recommended Course/Program <span class="text-red-500">*</span></label>

                            <div id="course-input-container" class="space-y-2">
                                <div class="flex items-center gap-3 course-row">
                                    <select name="program[]"
                                        class="w-full flex-1 border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white">
                                        <option selected disabled>Select Undergraduate Program</option>
                                        @foreach($programs as $program)
                                        <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                                        @endforeach
                                    </select>

                                    <button type="button" onclick="addCourseField()"
                                        class="bg-[#1D264F] text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#0E0F3B] transition-colors">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <p id="course-limit-msg" class="text-[9px] text-gray-400 italic hidden">
                                Maximum of 3 programs reached.
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Description <span class="text-red-500">*</span></label>
                            <textarea name="job_posting_description" rows="4"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]"></textarea>
                        </div>

                        <div class="w-1/2 space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Closing / Validity Date <span class="text-red-500">*</span></label>
                            <input type="date" name="job_closing_date"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] text-gray-400">
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="flex justify-end gap-4 mt-8">
                            <button type="button" onclick="closePostModal()"
                                class="px-10 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-md font-bold text-sm hover:bg-[#0E0F3B] hover:text-white transition-colors">
                                CANCEL
                            </button>

                            <button type="submit"
                                class="px-12 py-2 bg-[#0E0F3B] text-white rounded-md font-bold text-sm hover:bg-blue-900 transition-colors">
                                POST
                            </button>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- EDIT JOB MODAL -->
    <div id="editPostModal"
        class="fixed inset-0 z-[999] bg-black/70 flex items-center justify-center overflow-y-auto hidden">

        <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl relative overflow-hidden flex flex-col min-h-[600px]">

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

                            <img src="{{ asset('storage/' . $job->job_posting_image) }}" class="w-full h-full object-cover rounded-[1.6rem]" />

                            <input type="file" name="job_posting_image" id="editJobImageInput-{{ $job->job_posting_id }}" accept="image/*" class="hidden" onchange="previewJobImage(this)">

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
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">Business Name <span class="text-red-500">*</span></label>
                                <input type="text" name="job_posting_company" value="{{ $job->job_posting_company }}"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Business Address <span class="text-red-500">*</span></label>
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
                                    <button type="button" onclick="addCourseField()"
                                        class="bg-[#1D264F] text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#0E0F3B] transition-colors">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                    @else
                                    <button type="button" onclick="removeCourseField(this)"
                                        class="bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-700 transition-colors">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <p id="editCourseLimitMsg" class="text-[9px] text-gray-400 italic hidden">
                                Maximum of 3 programs reached.
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Description <span class="text-red-500">*</span></label>
                            <textarea name="job_posting_description" rows="4"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">{{ $job->job_posting_description }}</textarea>
                        </div>

                        <div class="w-1/2 space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Closing / Validity Date <span class="text-red-500">*</span></label>
                            <input type="date" name="job_closing_date" value="{{ $job->job_closing_date }}"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] text-gray-400">
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

    // POST A NEW JOB MODAL
    function openPostJobModal() {
        document.getElementById('postJobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePostModal() {
        document.getElementById('postJobModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('postJobModal')) closePostModal();
    });

    // POST A NEW JOB MODAL - UPLOAD IMAGE
    function previewJobImage(input) {
        const frame = document.getElementById('imageFrame');
        const placeholder = document.getElementById('uploadPlaceholder');
        const preview = document.getElementById('jobImagePreview');
        const changeBtn = document.getElementById('changeImgBtn');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                changeBtn.classList.remove('hidden');
                placeholder.classList.add('hidden');
                frame.classList.remove('p-6');
                frame.classList.add('p-0');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // POST A NEW JOB MODAL - ADD COURSE FIELD
    function addCourseField() {
        const container = document.getElementById('course-input-container');
        const rows = container.getElementsByClassName('course-row');

        if (rows.length < 3) {
            const newRow = rows[0].cloneNode(true);
            const select = newRow.querySelector('select');
            select.selectedIndex = 0;
            const btn = newRow.querySelector('button');
            btn.innerHTML = '<i class="fas fa-minus text-xs"></i>';
            btn.classList.replace('bg-[#1D264F]', 'bg-red-500');
            btn.setAttribute('onclick', 'removeCourseField(this)');
            container.appendChild(newRow);

            if (rows.length === 3) {
                document.getElementById('course-limit-msg').classList.remove('hidden');
            }
        }
    }

    function removeCourseField(button) {
        button.closest('.course-row').remove();
        document.getElementById('course-limit-msg').classList.add('hidden');
    }

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