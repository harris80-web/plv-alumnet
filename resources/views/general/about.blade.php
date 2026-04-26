<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | About</title>
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

    .PLVSection {
        background:
            url("{{ asset('assets/About Page/plv_section.jpg') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .AssociatedPartners {
        
        background-image: url("{{ asset('assets/About Page/associated_partners_section.jpg') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;

        width: 100%;
        display: block;
    }

    .AlumniAssociation {
        background:
            url("{{ asset('assets/About Page/alumni_association_section.jpg') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .SystemCore {
        background:
            url("{{ asset('assets/About Page/system_core_section.jpg') }}");
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
        scrollbar-width: none;
    }
</style>

<body>
    @php
    $current_page = 'about';
    @endphp
    @include('partials.header-general')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">About</h1>
            <p class="text-xl font-light">PLV-AlumNet:Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section class="PLVSection relative w-full mt-[90px] py-[100px] px-[10%] flex flex-col lg:flex-row items-center gap-10 lg:gap-[150px] text-white bg-cover bg-center">
        <h2 class="flex-shrink-0 text-7xl lg:text-[84px] font-bold leading-[1.1] pl-5 drop-shadow-[4px_4px_15px_rgba(0,0,0,0.8)]">| PLV</h2>
        <p class="flex-1 font-['DM_Sans'] text-2xl leading-relaxed text-justify drop-shadow-[2px_2px_8px_rgba(0,0,0,0.6)]">
            Established in 2002 by a city ordinance, the Pamantasan ng Lungsod ng Valenzuela (PLV) is a premier local government-funded university dedicated to providing high-quality, accessible higher education. Its mission is to develop competent, competitive, and civic-minded graduates who will contribute to the growth and advancement of Valenzuela City.
        </p>
    </section>

    <section class="w-full py-[100px] px-[10%] flex flex-col-reverse lg:flex-row items-center justify-between gap-10 lg:gap-[100px] bg-white">
        <p class="flex-1 font-['DM_Sans'] text-2xl leading-relaxed text-[#0E0F3B] text-justify">
            "To provide the citizens of Valenzuela an efficient effective institution of higher learning that will make them skillful, productive, competent, competitive, civic-minded, God-loving toward a peaceful, healthy and progressive City."
        </p>
        <h2 class="flex-shrink-0 text-5xl lg:text-[64px] font-bold leading-none text-[#0E0F3B] pl-6 border-l-8 border-[#0E0F3B]"> PLV Mission</h2>
    </section>

    <section class="AssociatedPartners w-full py-[100px] px-[10%] flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-[100px] text-white">
        <h2 class="flex-shrink-0 text-5xl lg:text-[64px] font-bold leading-[1.1] border-l-8 border-white pl-6 drop-shadow-[2px_2px_10px_rgba(0,0,0,0.5)]">
            Associated<br>Partners
        </h2>

        <div class="flex flex-col gap-6 flex-1 max-w-[600px] w-full">
            <div class="bg-white/90 p-5 px-10 rounded-[20px] flex items-center gap-8 transition-all duration-300 hover:translate-x-[-10px] hover:bg-white shadow-xl">
                <img src="{{ asset('assets/Val-logo.svg') }}" alt="Local Government Logo" class="w-[60px] h-[60px] object-contain">
                <h3 class="text-[22px] font-bold text-[#0E0F3B]">Local Government of Valenzuela</h3>
            </div>

            <div class="bg-white/90 p-5 px-10 rounded-[20px] flex items-center gap-8 transition-all duration-300 hover:translate-x-[-10px] hover:bg-white shadow-xl">
                <img src="{{ asset('assets/PLV-Logo-2.svg') }}" alt="University Logo" class="w-[60px] h-[60px] object-contain">
                <h3 class="text-[22px] font-bold text-[#0E0F3B]">Pamantasan ng Lungsod ng Valenzuela</h3>
            </div>

            <div class="bg-white/90 p-5 px-10 rounded-[20px] flex items-center gap-8 transition-all duration-300 hover:translate-x-[-10px] hover:bg-white shadow-xl">
                <img src="{{ asset('assets/About Page/plv_alumni_logo.png') }}" alt="Alumni Association Logo" class="w-[60px] h-[60px] object-contain">
                <h3 class="text-[22px] font-bold text-[#0E0F3B]">PLV- Alumni Association</h3>
            </div>
        </div>
    </section>

    <section class=" py-20 px-[10%] text-center">
        <div class="max-w-[700px] mx-auto">
            <h2 class="text-4xl font-bold text-[#0b1b4d] mb-5">Got Questions?</h2>
            <p class="font-['DM_Sans'] text-lg leading-relaxed text-[#222] mb-8">
                For quick answers to common questions about registration, services, and membership, please visit our <strong>Frequently Asked Questions (FAQ)</strong> page or send an inquiry through our <strong>AlumNet Chatbot</strong>!
            </p>
            <div class="flex flex-wrap justify-center gap-5">
                <a href="{{ route('general.faqs') }}" class="px-8 py-3 rounded-md font-semibold border-2 border-[#0b1b4d] text-[#0b1b4d] transition-colors hover:bg-[#0b1b4d] hover:text-white">VIEW FAQs</a>
            </div>
        </div>
    </section>

    <section class="AlumniAssociation w-full mx-auto py-20 px-[10%] flex flex-col lg:flex-row items-start gap-10 lg:gap-[100px] bg-[#f8f9fa]">
        <h2 class="flex-shrink-0 text-5xl font-bold leading-[1.1] text-white lg:self-center">| THE ALUMNI <br>ASSOCIATION</h2>
        <p class="flex-1 font-['DM_Sans'] text-xl leading-relaxed text-white">
            The official PLV Alumni Association serves as the institutional link between the university and its graduates, fostering a lifelong bond among its growing community of alumni. Its primary function is to serve former students by offering professional support and networking opportunities while also strengthening PLV through institutional aid and collective wisdom.
        </p>
    </section>

    <section class="w-full mx-auto py-20 px-[10%] flex flex-col-reverse lg:flex-row items-start gap-10 lg:gap-[60px] bg-[#f8f9fa]">
        <p class="flex-1 font-['DM_Sans'] text-xl leading-relaxed">
            The primary purpose of the PLV AlumNet Web-Based System is to serve as the official, centralized, and intelligent digital hub that connects the PLV alumni community with continuous opportunities for professional growth and institutional support.
        </p>
        <h2 class="flex-shrink-0 text-6xl font-bold leading-[1.1] text-[#0b1b4d] lg:self-center">| PLV - AlumNet’s<br> Mission</h2>
    </section>

    <section class="SystemCore w-full mx-auto py-20 px-[10%] mb-10 flex flex-col lg:flex-row items-start gap-10 lg:gap-[200px] bg-[#f8f9fa]">
        <h2 class="flex-shrink-0 text-6xl font-bold leading-[1.1] text-white lg:self-center">| System's Core<br>Value Proposition</h2>
        <p class="flex-1 font-['DM_Sans'] text-white text-xl leading-relaxed">
            The PLV AlumNet is your essential hub for professional growth and connection. Registration provides exclusive access to the <span class="font-bold">Job Board</span> for career advancement, the <span class="font-bold">Alumni Directory</span> for powerful networking, and the <span class="font-bold">ID/Yearbook Tracker</span> for monitoring requests and fulfillment of your official alumni documents. These dedicated <span class="font-bold">Communication Tools</span> also keep you informed and engaged with the university community.
        </p>
    </section>
    </div>

    @include('partials.footer')

</body>

</html>