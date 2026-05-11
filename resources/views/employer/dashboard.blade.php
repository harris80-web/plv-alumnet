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
    <h1>Employer Dashboard</h1>
    <br><br>
    <a href="{{ route('user.profile') }}">View Profile</a>
    <br><br>
    <a href="{{ route('jobPosting.jobBoard') }}">Job board</a>
    <br><br>
    <a href="{{ route('jobPosting.myJobPosts', ['id' => Auth::id()]) }}">My job postings</a>
    <br><br>
    
    <form method="POST" action="{{ route('user.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>-->

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
            <h2 class="text-2xl font-bold font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-8 tracking-wide">
                DASHBOARD FOR [COMPANY NAME]</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow-lg border-t-8 border-orange-600 p-8 text-center transition-transform hover:-translate-y-1">
                    <div class="text-6xl font-bold text-orange-600 mb-2">0</div>
                    <p class="text-orange-700 font-semibold leading-tight">Active Job<br>Postings</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg border-t-8 border-orange-600 p-8 text-center transition-transform hover:-translate-y-1">
                    <div class="text-6xl font-bold text-orange-600 mb-2">0</div>
                    <p class="text-orange-700 font-semibold leading-tight">New Unreviewed<br>Applicants</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg border-t-8 border-orange-600 p-8 text-center transition-transform hover:-translate-y-1">
                    <div class="text-6xl font-bold text-orange-600 mb-2">0</div>
                    <p class="text-orange-700 font-semibold leading-tight">Job Posts<br>Expiring Soon</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            <div class="flex justify-between items-end mb-8 pb-4">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">JOB POSTING</h2>
                <a href="{{ route('jobPosting.jobBoard') }}" class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A] transition-all">
                    GO TO JOB BOARD >
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

                    <div class="min-w-full md:min-w-[calc(50%-12px)] lg:min-w-[calc(33.333%-16px)] snap-center">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col transition-transform hover:scale-[1.02]">
                            <div class="relative h-48 bg-[url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=600q=80')] bg-cover bg-center">
                                <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-[#0E0F3B] uppercase text-xl">Senior Designer</h3>
                                    <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">2 hours ago</span>
                                </div>
                                <div class="flex items-center text-gray-400 text-xs font-semibold mb-4 uppercase">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    April 13, 2026
                                </div>
                                <div class="space-y-1 mb-6 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <p>Applications Received: 12</p>
                                </div>
                                <button class="w-full bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white py-3 rounded-md font-bold text-xs tracking-widest hover:bg-black transition-all uppercase">View Applicants</button>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-full md:min-w-[calc(50%-12px)] lg:min-w-[calc(33.333%-16px)] snap-center">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col transition-transform hover:scale-[1.02]">
                            <div class="relative h-48 bg-[url('https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?auto=format&fit=crop&w=600q=80')] bg-cover bg-center">
                                <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-[#0E0F3B] uppercase text-xl">Project Manager</h3>
                                    <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">5 hours ago</span>
                                </div>
                                <div class="flex items-center text-gray-400 text-xs font-semibold mb-4 uppercase">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    April 13, 2026
                                </div>
                                <div class="space-y-1 mb-6 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <p>Applications Received: 8</p>
                                </div>
                                <button class="w-full bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white py-3 rounded-md font-bold text-xs tracking-widest hover:bg-black transition-all uppercase">View Applicants</button>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-full md:min-w-[calc(50%-12px)] lg:min-w-[calc(33.333%-16px)] snap-center">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col transition-transform hover:scale-[1.02]">
                            <div class="relative h-48 bg-[url('https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=600q=80')] bg-cover bg-center">
                                <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                            </div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-[#0E0F3B] uppercase text-xl">Web Developer</h3>
                                    <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">1 day ago</span>
                                </div>
                                <div class="flex items-center text-gray-400 text-xs font-semibold mb-4 uppercase">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    April 13, 2026
                                </div>
                                <div class="space-y-1 mb-6 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <p>Applications Received: 24</p>
                                </div>
                                <button class="w-full bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white py-3 rounded-md font-bold text-xs tracking-widest hover:bg-black transition-all uppercase">View Applicants</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="dots" class="flex justify-center gap-2 mt-10">
                <div class="dot w-2.5 h-2.5 rounded-full bg-[#f97316] transition-colors cursor-pointer" onclick="scrollToIndex(0)"></div>
                <div class="dot w-2.5 h-2.5 rounded-full bg-gray-300 transition-colors cursor-pointer" onclick="scrollToIndex(1)"></div>
                <div class="dot w-2.5 h-2.5 rounded-full bg-gray-300 transition-colors cursor-pointer" onclick="scrollToIndex(2)"></div>
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
                    <a href="#" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Review Candidates</span>
                    <a href="#" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Post Jobs</span>
                    <a href="employer_job_board.php" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-clipboard-list text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Manage Postings</span>
                    <a href="#" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Manage Profile</span>
                    <a href="#status-section" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

            </div>
        </div>
    </section>

    <section class="py-16 px-6 max-w-6xl mx-auto relative">
        <div class="flex justify-between items-end mb-8 pl-4">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent 
            text-4xl font-bold text-blue-900 uppercase tracking-tighter"> | Campus Events</span>
            <a href="#" class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A]">Go to Events ></a>
        </div>

        <div class="flex items-center">
            <button class="p-2 text-gray-400 hover:text-blue-900"><i class="fa-solid fa-chevron-left text-2xl"></i></button>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex-grow">
                <script>
                    for (let i = 0; i < 3; i++) {
                        document.write(`
                        <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-100">
                            <div class="h-40">
                                <img src="{{ asset('assets/Landing Page/Event.png') }}" class="w-full h-full object-cover mix-blend-multiply">
                            </div>
                            
                            <div class="p-4 flex gap-4 items-start relative ">
                                <div class="flex-grow">
                                    <h3 class="font-bold text-blue-900 uppercase text-sm">Event Title</h3>
                                    <p class="text-[10px] text-gray-500 mb-2">
                                    <i class="fa-solid fa-location-dot mr-1"></i> LOCATION
                                    </p>
                                    <p class="text-xs text-black w-3/4 leading-tight">
                                      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id arcu tristique.
                                    </p>
                                </div>
                                 <div class="flex-shrink-0 absolute right-0 bg-orange-700 text-white p-2 text-center w-16 rounded-sm shadow-sm">
                                    <span class="block text-xl font-bold leading-none tracking-tighter">01</span>
                                    <span class="text-[10px] uppercase font-semibold">Jan</span>
                                </div> 
                            </div>
                            
                        </div>
                        `);
                    }
                </script>
            </div>

            <button class="p-2 text-gray-400 hover:text-blue-900"><i class="fa-solid fa-chevron-right text-2xl"></i></button>
        </div>
        <div class="flex justify-center mt-6 gap-2">
            <div class="w-2 h-2 rounded-full bg-gray-300"></div>
            <div class="w-2 h-2 rounded-full bg-orange-600"></div>
            <div class="w-2 h-2 rounded-full bg-gray-300"></div>
        </div>
    </section>


    <!--USER PROFILE/EDIT PROFILE/CHANGE PASSWORD SIDE BAR -->
    <div id="menuOverlay" class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity duration-300"></div>

    <div id="notificationPopup" class="fixed top-20 right-[250px] w-72 bg-white rounded-xl shadow-2xl z-[70] hidden transform origin-top-right transition-all duration-300 scale-95 opacity-0">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-[#0E0F3B] font-bold">Notifications</h3>
            <button onclick="toggleNotifications()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-10 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm">No notifications</p>
        </div>
    </div>

    <div id="userSidebar" class="fixed top-0 right-0 h-full w-80 bg-white z-[70] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="p-6 relative flex flex-col h-full">
            <button onclick="toggleSidebar()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <div class="flex flex-col items-center mt-8 mb-6">
                <div class="w-24 h-24 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-4 overflow-hidden">
                    <i class="fa-solid fa-user text-5xl text-white"></i>
                </div>
                <h2 class="text-[#ED7A07] font-semibold text-xl uppercase tracking-wider">User</h2>
                <div class="w-full border-b mt-4"></div>
            </div>

            <nav class="space-y-4 flex-grow">
                <a href="{{ route('user.profile') }}" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white p-3 w-full transition font-regular hover:font-semibold">
                    <i class="fa-solid fa-address-card w-6"></i> View Profile
                </a>
                <a href="employer_edit.php" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white p-3 w-full transition font-regular hover:font-semibold">
                    <i class="fa-solid fa-user-pen w-6"></i> Edit Profile
                </a>
                <a href="employer_change_password.php" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white p-3 w-full transition font-regular hover:font-semibold">
                    <i class="fa-solid fa-lock w-6"></i> Change Password
                </a>
            </nav>

            <div class="border-t pt-6">
                <form method="POST" action="{{ route('user.logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>

    @include('partials.footer-employer')
    
</body>

</html>