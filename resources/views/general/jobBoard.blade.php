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
    <h1>Job Board</h1>
    <div class="job-board">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        <br><br>
        
        <div>
            <form action="{{ route('jobPosting.addJobPost', ['id' => $user->user_id]) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div>
                    <label for="job_posting_image">Job photo:</label>
                    <input type="file" id="job_posting_image" name="job_posting_image">
                </div>
                <div>
                    <label for="job_posting_title">Job title:</label>
                    <input type="text" id="job_posting_title" name="job_posting_title">
                </div>
                <div>
                    <label for="job_posting_company">Business name:</label>
                    <input type="text" id="job_posting_company" name="job_posting_company">
                </div>
                <div>
                    <label for="job_posting_address">Business address:</label>
                    <input type="text" id="job_posting_address" name="job_posting_address">
                </div>
                <div>
                    <label for="job_posting_employment_type">Job type:</label>
                    <input type="text" id="job_posting_employment_type" name="job_posting_employment_type">
                </div>
                <div>
                    <label for="job_posting_setup">Job setup:</label>
                    <input type="text" id="job_posting_setup" name="job_posting_setup">
                </div>
                <div>
                    <label for="program">Recommended program:</label>
                    <select name="program" id="">
                        <option value="" selected hidden>Select a program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="job_posting_description">Job description:</label>
                    <input type="text" id="job_posting_description" name="job_posting_description">
                </div>
                <div>
                    <label for="job_closing_date">Validity date:</label>
                    <input type="date" id="job_closing_date" name="job_closing_date">
                </div>
                <button type="submit">Add Job Posting</button>
            </form>
        </div>
        <br><br><br>
        @foreach($jobPostings as $job)
            <div class="job-posting">
                <h2>{{ $job->job_posting_title }}</h2>
                <p><strong>Company:</strong> {{ $job->job_posting_company }}</p>
                <p><strong>Location:</strong> {{ $job->job_posting_address }}</p>
                <p><strong>Job type:</strong> {{ $job->job_posting_employment_type }}</p>
                <p><strong>Job setup:</strong> {{ $job->job_posting_setup }}</p>
                <p><strong>Description:</strong> {{ $job->job_posting_description }}</p>
                <p><strong>Valid until:</strong> {{ $job->job_closing_date }}</p>
                <img src="{{ asset('storage/'.$job->job_posting_image) }}" alt="Job Image" style="max-width: 200px; max-height: 200px;">
            </div>
        @endforeach
    </div>
    
</body>
</html>-->

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
</style>


<body>
    @php
    $current_page = Route::currentRouteName();
    @endphp
    @if(auth()->user()->user_role === 'alumni')
    @include('partials.header-alumni')
    @else
    @include('partials.header-employer')
    @endif
    @include('partials.success')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    @if(auth()->user()->user_role === 'alumni')
    <nav class="bg-white border-b sticky top-0 z-10 shadow-md">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex justify-start space-x-8 uppercase text-sm font-bold tracking-wide">
                <button class="py-4 tab-active transition-all">Job Board</button>
                <button class="py-4 text-gray-500 hover:text-orange-600 transition-all">My Applications</button>
                <button class="py-4 text-gray-500 hover:text-orange-600 transition-all">Bookmarks</button>
            </div>
        </div>
    </nav>
    @endif

    <main class="max-w-5xl mx-auto p-6">
        <div class="w-full text-center mb-8">
            <h1 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                @if(auth()->user()->user_role === 'alumni')
                ALUMNI CAREER HUB
                @else
                JOB BOARD
                @endif
            </h1>
        </div>

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
                    @foreach ($programs as $program)
                    <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
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
        @if(auth()->user()->user_role === 'employer')
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
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @foreach($jobPostings as $job)
            @if ($job->job_approved ==1)
            <div class="bg-white rounded-3xl shadow-md flex flex-col md:flex-row relative hover:shadow-lg transition-shadow">

                <div class="md:w-1/4 h-48 md:h-auto bg-gray-300 relative">
                    <img src="{{ asset('storage/'.$job->job_posting_image) }}"
                        class="object-cover w-full h-full opacity-60">
                    <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>
                </div>

                <div class="p-6 flex-1 relative">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold uppercase text-[#0E0F3B]">
                                {{ $job->job_posting_title }}
                            </h2>

                            <p class="text-gray-600">
                                {{ $job->job_posting_company }}
                            </p>

                            <p class="text-gray-500 text-sm">
                                {{ $job->job_posting_address }}
                            </p>

                        </div>

                        <div class="text-right">
                            <div class="relative flex flex-col items-end">
                                <div class="bookmark-tooltip invisible opacity-0 absolute bottom-full right-0 mb-2 pointer-events-none transition-all duration-300 z-50">
                                    <div class="bg-blue-900 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                                        Job post saved!
                                        <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-blue-900"></div>
                                    </div>
                                </div>
                                <button class="bookmark-btn text-gray-400 hover:text-blue-900 text-2xl transition-colors">
                                    <i class="far fa-bookmark"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 flex items-center justify-end">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $job->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm font-semibold">
                        <div>
                            <p class="text-[#0E0F3B]">Job Type: <span class="font-normal">{{ $job->job_posting_employment_type }}</span></p>
                            <p class="text-[#0E0F3B]">Job Setup: <span class="font-normal">{{ $job->job_posting_setup }}</span></p>
                        </div>

                        <div>
                            <p>
                            <p class="text-blue-900">Recommended Course/Program: <span class="font-normal ph"></span></p>
                            @foreach ($job->programs as $program)
                            {{ $program->program_name }}
                            <br><br>
                            @endforeach
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="font-bold text-sm text-[#0E0F3B]">Job Description:</p>
                        <p class="text-xs text-gray-500">{{ $job->job_posting_description }}</p>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <p class="text-xs text-gray-400 flex items-center">
                            <i class="far fa-calendar-check mr-1"></i> Valid until: {{ $job->job_closing_date }}
                        </p>
                        <div class="flex items-center space-x-3">
                            @if (auth()->user()->user_role === 'alumni')
                            @if($job->applicants->contains(auth()->user()->alumnus->user_id))
                            <button disabled class="bg-green-600 cursor-not-allowed text-white px-8 py-2 rounded-md font-bold text-sm flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> APPLIED
                            </button>
                            @else
                            <button
                                data-action="{{ route('jobApplication.apply', $job->job_posting_id) }}"
                                onclick="handleApplyClick(this)"
                                class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-8 py-2 rounded-md font-bold text-sm transition-colors">
                                APPLY
                            </button>
                            @endif
                            @endif

                            @php $jobImageUrl = asset('storage/' . $job->job_posting_image); @endphp
                            <button
                                data-title="{{ addslashes($job->job_posting_title) }}"
                                data-company="{{ addslashes($job->job_posting_company) }}"
                                data-address="{{ addslashes($job->job_posting_address) }}"
                                data-date="{{ $job->created_at->diffForHumans() }}"
                                data-description="{{ addslashes($job->job_posting_description) }}"
                                data-type="{{ $job->job_posting_employment_type }}"
                                data-setup="{{ $job->job_posting_setup }}"
                                data-valid="{{ $job->job_closing_date }}"
                                data-image="{{ asset('storage/' . $job->job_posting_image) }}"
                                data-programs="{{ $job->programs->pluck('program_name')->implode(', ') }}"
                                onclick="openJobModal(this)"
                                class="border border-[#1D46A4] text-[#1D46A4] px-6 py-2 rounded-md font-bold text-sm hover:bg-[#1D46A4] hover:border-none hover:text-white transition-colors">VIEW DETAILS</button>
                            <div class="relative flex items-center">
                                <button class="share-btn text-gray-500 hover:text-black transition-colors p-2" onclick="copyJobLink(this)">
                                    <i class="fas fa-share-nodes"></i>
                                </button>
                                <div class="share-tooltip invisible opacity-0 absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-3 rounded shadow-lg transition-all duration-300">
                                    Link Copied!
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-8 border-transparent border-t-gray-800"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-500 border-t pt-4">
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($job->user->user_first_name . ' ' . $job->user->user_last_name) }}&background=random"
                                class="w-6 h-6 rounded-full mr-2">
                            <span>Posted by <span class="font-bold text-black">{{ $job->user->user_first_name }} {{ $job->user->user_last_name }}</span></span>
                        </div>

                        @if (auth()->user()->user_role === 'alumni' && $job->applicants->contains(auth()->user()->alumnus->user_id))
                        @php $status = $job->applicants->find(auth()->user()->alumnus->user_id)->pivot->application_status; @endphp
                        <span class="inline-block text-xs font-bold px-3 py-1 rounded-full
                            {{ $status === 'hired' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $status === 'declined' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                            {{ strtoupper($status) }}
                        </span>
                        @endif
                    </div>
                </div>

            </div>
            @endif

            @endforeach

            <div class="mt-12 flex justify-center items-center space-x-4 text-gray-500 font-medium">
                <button class="hover:text-black"><i class="fas fa-chevron-left text-xs"></i></button>
                <button class="text-black font-bold">1</button>
                <button class="hover:text-black">2</button>
                <button class="hover:text-black">3</button>
                <button class="hover:text-black"><i class="fas fa-chevron-right text-xs"></i></button>
            </div>
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
                            <p id="modal-company" class="font-semibold text-lg">Business Name</p>
                            <span id="modal-date" class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted 2 days ago</span>
                        </div>
                        <p id="modal-address" class="text-gray-500 font-medium">Business Address</p>
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
                        <p id="modal-description" class="text-gray-600 text-sm leading-relaxed text-justify">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent placerat, nulla quis aliquam fringilla, nulla elit accumsan nisi, vel feugiat massa erat vel eros. Curabitur sed massa vel leo accumsan imperdiet.
                        </p>
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

                <div class="mt-10 pt-6 border-t flex items-center justify-between">
                    <div id="modal-valid" class="text-gray-500 text-sm flex items-center font-semibold">
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

            <form action="{{ route('jobPosting.addJobPost', ['id' => $user->user_id]) }}" method="POST" enctype="multipart/form-data">
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
                                <input type="text" name="job_posting_company" value="{{ Auth::user()->employer->employer_company_name }}" readonly 
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
                                        class="flex-1 border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white w-full ">
                                        <option selected disabled>Select Undergraduate Program</option>
                                        @foreach ($programs as $program)
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
                                onclick="handleJobSubmit(event)"
                                class="px-12 py-2 bg-[#0E0F3B] text-white rounded-md font-bold text-sm hover:bg-blue-900 transition-colors">
                                POST
                            </button>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- APPLY CONFIRMATION MODAL -->
    <div id="applyConfirmModal" class="fixed inset-0 z-[200] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-briefcase text-[#1D46A4] text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-[#0E0F3B] mb-2">Confirm Application</h2>
            <p class="text-gray-500 text-sm mb-6">Are you sure you want to apply for this job?</p>
            <div class="flex gap-3">
                <button onclick="cancelApply()" class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-100 transition-colors">
                    CANCEL
                </button>
                <button onclick="confirmApply()" class="flex-1 bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-[#1D46A4] transition-colors">
                    YES, APPLY
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

    @if(auth()->user()->user_role === 'alumni')
    @include('partials.footer-alumni')
    @else
    @include('partials.footer-employer')
    @endif

</body>

<script>
    // Combined Bookmark Toggle & Tooltip Logic
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const tooltip = this.parentElement.querySelector('.bookmark-tooltip');

            // Check if it's currently in 'outline' (unsaved) mode
            if (icon.classList.contains('far')) {
                // SAVE STATE
                icon.classList.replace('far', 'fas');
                icon.classList.add('text-blue-900');

                // Show Tooltip
                tooltip.classList.remove('invisible', 'opacity-0');
                tooltip.classList.add('visible', 'opacity-100', '-translate-y-1');

                // Auto-hide Tooltip after 2 seconds
                setTimeout(() => {
                    tooltip.classList.add('invisible', 'opacity-0');
                    tooltip.classList.remove('visible', 'opacity-100', '-translate-y-1');
                }, 2000);
            } else {
                // UNSAVE STATE
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

    //job post modal script

    function openJobModal(btn) {
        document.getElementById('modal-img').src = btn.dataset.image;
        document.getElementById('modal-title').textContent = btn.dataset.title;
        document.getElementById('modal-company').textContent = btn.dataset.company;
        document.getElementById('modal-date').innerHTML = '<i class="far fa-calendar-alt mr-2"></i> ' + btn.dataset.date;
        document.getElementById('modal-address').textContent = btn.dataset.address;
        document.getElementById('modal-description').textContent = btn.dataset.description;
        document.getElementById('modal-job-type').textContent = btn.dataset.type;
        document.getElementById('modal-job-setup').textContent = btn.dataset.setup;
        document.getElementById('modal-programs').textContent = btn.dataset.programs;
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
    function showModalTooltip(tooltipId, buttonElement) {
        const tooltip = document.getElementById(tooltipId);

        //Show the tooltip
        tooltip.classList.remove('invisible', 'opacity-0');
        tooltip.classList.add('opacity-100');

        // Handle the Bookmark Toggle
        if (tooltipId === 'modal-bookmark-tooltip') {
            const icon = document.getElementById('modal-bookmark-icon');

            // Toggle between regular (far) and solid (fas)
            if (icon.classList.contains('far')) {
                icon.classList.replace('far', 'fas');
                // Optional: Change the tooltip text if it's already saved
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

    let pendingApplyButton = null;

    function handleApplyClick(button) {
        pendingApplyButton = button;
        document.getElementById('applyConfirmModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cancelApply() {
        pendingApplyButton = null;
        document.getElementById('applyConfirmModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function confirmApply() {
        document.getElementById('applyConfirmModal').classList.add('hidden');
        document.body.style.overflow = 'auto';

        if (!pendingApplyButton) return;

        const url = pendingApplyButton.dataset.action;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }

    function closePendingModal() {
        document.getElementById('pendingModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.querySelector('#postJobModal form').submit();
    }

    function handleJobSubmit(event) {
        event.preventDefault();
        document.getElementById('postJobModal').classList.add('hidden');
        document.getElementById('pendingModal').classList.remove('hidden');

        // Submit the form for real after showing the modal
       // event.target.closest('form').submit();
    }
</script>

</html>