<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Home</title>
    @vite('resources/css/app.css')
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

    <section id="status-section" class="py-12 px-6 max-w-6xl mx-auto">
        <h2 class="text-4xl font-bold mb-10">
            <span class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Alumni Dashboard</span>
        </h2>

        <div class="grid md:grid-cols-2 gap-8">

            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 flex flex-col">
                <div class="relative h-64 bg-[url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800q=80')] bg-cover bg-center">
                    <div class="alumniID absolute inset-0 bg-orange-600/80 flex flex-col items-center justify-center text-white p-6 text-center">
                        <div class="w-10 h-10 bg-white rounded-md flex items-center justify-center mb-4">
                            <i class="fa-solid fa-check text-orange-600 text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold uppercase tracking-wide mb-4">Alumni ID Claimed</h3>
                        <p class="text-sm font-medium mb-2">Your Alumni ID has been claimed.</p>
                        <!--<p class="text-xs opacity-90 leading-relaxed">Your details are currently under initial review by the Alumni Office.</p>-->
                    </div>
                </div>
                <div class="py-6 text-center">
                    <h4 class="text-2xl font-bold">
                        <span class="inner-text-shadow text-3xl font-medium bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Alumni ID Status</span>
                    </h4>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 flex flex-col">
                <div class="relative h-64 bg-[url('https://images.unsplash.com/photo-1544822688-c6f14d6986bb?auto=format&fit=crop&w=800q=80')] bg-cover bg-center">
                    <div class="absolute inset-0 bg-[#0E0F3B]/80 flex flex-col items-center justify-center text-white p-6 text-center">
                        <h3 class="text-xl font-bold uppercase tracking-widest mb-6">Ready for Distribution</h3>
                        <p class="text-sm font-bold mb-4">Ready for Distribution to the Association</p>
                        <p class="text-[10px] leading-relaxed mb-6 px-4">The yearbook inventory has arrived at the Alumni Association Office and is being prepared for release. See distribution schedule below:</p>

                        <div class="text-left w-full max-w-[200px] text-[10px] space-y-1">
                            <p><span class="font-bold">DATE:</span></p>
                            <p><span class="font-bold">SCHEDULE:</span></p>
                            <p><span class="font-bold">LOCATION:</span></p>
                        </div>
                    </div>
                </div>
                <div class="py-6 text-center">
                    <h4 class="text-2xl font-bold">
                        <span class="inner-text-shadow text-3xl font-medium bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Yearbook Claiming Status</span>
                    </h4>
                </div>
            </div>

        </div>
    </section>

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
            <a href="job-board.php" class="text-[#ED7A07] font-bold uppercase text-sm hover:border-b-2 border-[#C73D1A] inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                Go to Job Board >
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <script>
                // Array to easily generate multiple cards for the mockup
                for (let i = 0; i < 3; i++) {
                    document.write(`
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 flex flex-col transition-transform hover:scale-[1.02]">
                    <div class="relative h-48 bg-[url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=600q=80')] bg-cover bg-center">
                        <div class="absolute inset-0 bg-[#0E0F3B]/50 mix-blend-multiply"></div>
                        <div class="absolute inset-0 bg-blue-600/20"></div>
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-[#0E0F3B] uppercase text-xl">Job Title</h3>
                            <span class="text-[9px] text-gray-400 flex items-center gap-1 mt-1">
                                <i class="fa-regular fa-calendar"></i> Posted 2 hours ago
                            </span>
                        </div>

                        <p class="text-xs text-gray-400 font-semibold uppercase mb-4 tracking-wider">Company / Business Name</p>
                        
                        <p class="text-[10px] text-gray-500 font-bold uppercase mb-2">Recommended Course/Program</p>
                        
                        <p class="text-[11px] text-gray-600 leading-relaxed mb-6">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer placerat, nulla suscipit aliquam feugiat, nulla elit accumsan nisl, at facilisis nunc tellus at dolor. In nec turpis malesuada, iaculis magna non, feugiat erat.
                        </p>

                        <div class="mt-auto pt-4 border-t border-gray-100">
                            <p class="text-[9px] text-gray-400 flex items-center gap-1 uppercase font-bold">
                                <i class="fa-regular fa-calendar-check"></i> Closing / Validity Date
                            </p>
                        </div>
                    </div>
                </div>
                `);
                }
            </script>

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
                    <span class="text-xs font-bold uppercase mb-4 text-center">Alumni Directory</span>
                    <a href="#" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Job Board</span>
                    <a href="#" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        View More
                    </a>
                </div>

                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center transition-transform hover:scale-105">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-comments text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase mb-4 text-center">Network Connect</span>
                    <a href="#" class="text-[10px] font-bold uppercase py-1.5 px-4 border border-[#0E0F3B] rounded-md hover:bg-[#0E0F3B] hover:text-white transition-colors">
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

    <section class="AlumniTestimonial py-20 px-6 text-white text-center mb-10">
        <h2 class="text-3xl font-bold uppercase mb-12 tracking-widest">Alumni Testimonials</h2>

        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8">
            @foreach ($testimonials as $testimonial)
            <div class="bg-white text-left p-6 rounded-lg shadow-2xl relative flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center">
                        @if ($testimonial->alumnus->user->user_profile_picture)
                            <img src="{{ asset($testimonial->alumnus->user->user_profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover rounded-full">
                        @else
                        <i class="fa-solid fa-user text-3xl text-white"></i>
                        @endif

                    </div>
                </div>
                <div>
                    <h4 class="text-blue-900 font-bold uppercase text-lg">{{ $testimonial->alumnus->user->user_first_name }} {{ $testimonial->alumnus->user->user_last_name }}</h4>
                    <p class="text-blue-700 text-[10px] font-semibold mb-3 uppercase">{{ $testimonial->alumnus->program->program_name }}, Batch {{ $testimonial->alumnus->alumnus_batch }}</p>
                    <p class="text-gray-600 text-xs leading-relaxed">{{ $testimonial->testimonial_body }}</p>
                </div>
            </div>
            @endforeach
            <!-- <script>
                const roles = ["BS Information Technology, Batch 2023", "BS Psychology, Batch 2022", "BS Electrical Engineering, Batch 2018", "BS Civil Engineering, Batch 2022"];
                roles.forEach(role => {
                    document.write(`
                    <div class="bg-white text-left p-6 rounded-lg shadow-2xl relative flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-user text-3xl text-white"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-blue-900 font-bold uppercase text-lg">Alumni Name</h4>
                            <p class="text-blue-700 text-[10px] font-semibold mb-3 uppercase">${role}</p>
                            <p class="text-gray-600 text-xs leading-relaxed">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>
                        </div>
                    </div>
                    `);
                });
            </script> -->
        </div>
    </section>

    <section class="Experience py-5 px-6 max-w-7xl mx-auto mb-5 flex flex-col md:flex-row items-center gap-12">

        <div class="relative w-full ">

            <div class="relative z-10 bg-[#0E0F3B] p-8 shadow-2xl w-full max-w-md mx-auto shadow-outer">
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

        <div class="w-full md:w-1/2 space-y-6 text-center flex flex-col items-center">
            <h2 class="text-5xl md:text-6xl font-bold text-[#0E0F3B] leading-tight">
                Share your<br>experience
            </h2>

            <p class="text-[#0E0F3B] font-medium text-lg text-justify leading-relaxed max-w-md mx-auto">
                Tell us about the connections, opportunities, or mentorship you've gained through the AlumNet. Your testimonial helps highlight the value of our network for all PLV graduates.
            </p>
        </div>
    </section>

</html>