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
    @include('partials.header-general')

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
                    This policy outlines how Pamantasan ng Lungsod ng Valenzuela (PLV) and the PLV Alumni manage information collected from unregistered visitors accessing the public areas of the AlumNet platform (e.g., public event previews, login pages, and FAQs).
                </p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8 mt-16">

            <div class="relative p-[2px] rounded-3xl bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] shadow-xl">
                <div class="bg-white rounded-[22px] p-10 h-full flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-orange-700 text-white rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-user text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold mb-6">
                        <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">User Information</span>
                    </h2>
                    <p class="text-gray-800 font-bold leading-relaxed text-sm">
                        This data is used exclusively for site maintenance, analyzing traffic to improve public content, and diagnosing technical issues. We do not attempt to personally identify public visitors.
                    </p>
                </div>
            </div>

            <div class="relative p-[2px] rounded-3xl bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] shadow-xl">
                <div class="bg-white rounded-[22px] p-10 h-full flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-orange-700 text-white rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-cookie-bite text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold mb-6 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Cookies</h2>
                    <p class="text-gray-800 font-bold leading-relaxed text-sm mb-6">
                        We use essential cookies to manage basic site functionality and performance tracking. By using the public site, you consent to the use of these cookies.
                    </p>
                    <p class="text-gray-500 text-xs italic mt-auto">
                        For questions about this policy, please contact us at
                    </p>
                </div>
            </div>

        </div>
    </section>

    @include('partials.footer')

</body>

</html>