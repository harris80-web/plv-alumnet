<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Privacy Policy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
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

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<body>
    @include('partials.header-employer')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Privacy Policy</h1>
            <p class="text-xl font-light">PLV-AlumNet:Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="container mx-auto px-6 max-w-5xl mt-10 mb-10 font-[Montserrat]">
        <div class="text-center mb-12">
            <h3 class="text-4xl md:text-5xl font-bold mb-2">
                <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">PLV-AlumNet Privacy Policy</span>
            </h3>
            <p class="text-xs font-bold text-[#0E0F3B] tracking-widest uppercase mb-8">Effective Date</p>

            <div class="max-w-3xl mx-auto space-y-6 text-black text-sm md:text-base leading-relaxed font-medium">
                <p>
                    This policy is designed to comply with the <strong class="text-[#C73D1A]">Philippine Data Privacy Act of 2012</strong> (Republic Act No. 10173) and its Implementing Rules and Regulations.
                </p>
                <p>
                    This policy outlines how Pamantasan ng Lungsod ng Valenzuela (PLV) and the PLV Alumni manage employer-related information and job posting data within the AlumNet platform.
                </p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mt-16">

            <div class="relative p-[2px] rounded-3xl bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] shadow-xl">
                <div class="bg-white rounded-[22px] p-10 h-full flex flex-col items-center">
                    <div class="w-20 h-20 bg-orange-700 text-white rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-right-left text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold mb-6 text-center">
                        <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Use and Sharing of Employer Data</span>
                    </h2>
                    <ul class="text-gray-800 text-sm space-y-4 text-left">
                        <li>
                            <span class="font-bold text-orange-700 uppercase text-[10px]">• Public Display:</span>
                            Company Profile, Logo, and Job Posting details are visible to logged-in alumni on the Job Board.
                        </li>
                        <li>
                            <span class="font-bold text-orange-700 uppercase text-[10px]">• Communication:</span>
                            Employer representative contact information is provided to alumni when they apply for a job or send an inquiry.
                        </li>
                        <li>
                            <span class="font-bold text-orange-700 uppercase text-[10px]">• System Management:</span>
                            Activity data (e.g., hiring stages) is tracked to measure the effectiveness of the Job Board.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="relative p-[2px] rounded-3xl bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] shadow-xl">
                <div class="bg-white rounded-[22px] p-10 h-full flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-orange-700 text-white rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-shield-halved text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold mb-6 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Data Confidentiality</h2>
                    <p class="text-gray-800 font-bold leading-relaxed text-sm mb-6">
                        Applicant data viewed or downloaded by employers must be handled in compliance with Philippine data privacy laws and may not be used for purposes other than recruitment related to the specific job posting.
                    </p>
                    <p class="text-gray-500 text-xs italic mt-auto">
                        For questions about this policy, please contact us at
                    </p>
                </div>
            </div>

        </div>
    </section>

    @include('partials.footer-employer')

</body>

</html>