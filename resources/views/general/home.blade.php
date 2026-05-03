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
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background:
            url('assets/heroSectionBackground.png');
        background-size: cover;
        background-position: center;
    }

    .AlumniServices {
        background:
            url('assets/Landing Page/Alumni Services.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .AlumniTestimonial {
        background:
            url('assets/Landing Page/Alumni Testimonial.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
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
    $current_page = 'index';
    @endphp
    @include('partials.header-general')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="py-16 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center justify-items-center">
        <div class="rounded-lg overflow-hidden shadow-xl w-full">
            <img src="assets/Landing Page/graduationImage.png" alt="Graduation" class="w-full h-full object-cover">
        </div>

        <div class="flex flex-col items-center text-center">
            <h2 class="text-4xl font-bold text-[#0E0F3B] mb-6 leading-tight">
                Your Journey has<br>just begun
            </h2>
            <p class="text-black leading-relaxed mb-8">
                <span class="font-bold text-[#0E0F3B]">PLV-AlumNet</span> is the essential digital platform that elevates the connection between all PLV alumni. We function as a dynamic ecosystem, not just a directory, actively working to bridge opportunities, empower professional success, and inspire mentorship across all generations of graduates.
            </p>
            <a href="{{ route('general.about') }}" class="px-8 py-2 rounded-md border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition-colors duration-300 uppercase text-sm tracking-widest">
                View More
            </a>
        </div>
    </section>

    <section class="AlumniServices orange-gradient py-16 px-6 text-white text-center">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-4xl font-bold mb-4 uppercase">Alumni Services</h2>
            <p class="items-center text-justify mb-12 text-sm w-3/5 mx-auto">
                This section details the exclusive resources, support, and programs available to all graduates of <span class="font-bold">Pamantasan ng Lungsod ng Valenzuela (PLV)</span>. It typically includes services such as career assistance, networking events, information for claiming of alumni IDs and Yearbook, and access to campus facilities. The goal is to keep alumni connected to the university and to foster mutual support among the network's members.
            </p>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-users text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Community Updates</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-address-book text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Alumni Directory</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Job Board</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-comments text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Network Connect</span>
                </div>


                <div class="bg-white rounded-2xl p-6 text-[#0E0F3B] shadow-lg flex flex-col items-center justify-center aspect-square">
                    <div class="w-16 h-16 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-id-card text-3xl text-white"></i>
                    </div>
                    <span class="text-xs font-bold uppercase">Membership Status</span>
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
                                <img src="assets/Landing Page/Event.png" class="w-full h-full object-cover mix-blend-multiply">
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
            <script>
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
            </script>
        </div>
    </section>

    @include('partials.footer')

</body>

</html>