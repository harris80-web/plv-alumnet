<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | FAQs</title>
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

    .faq-item.active .chevron {
        transform: rotate(180deg);
    }
</style>

<body>
    @include('partials.header-employer')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-4">
            <h1 class="text-5xl font-bold">Frequently Asked Questions (FAQs)</h1>
            <p class="text-xl font-light">PLV-AlumNet:Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <section id="faq-section" class="max-w-3xl mx-auto font-[Montserrat]">

        <header class="text-center mt-5 mb-10">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-wide">
                PLV-AlumNet<br>Frequently Asked Questions (FAQs)
            </h1>
            <p class="text-black mt-4 text-sm max-w-lg mx-auto font-medium">
                Answers to common questions for logged-in PLV registered employer members regarding account management and services.
            </p>
        </header>

        <div class="space-y-6 mb-10">

            <!-- FAQ ITEM -->
            <section class="faq-item group">
                <button class="faq-toggle w-full flex items-center justify-between bg-white p-5 rounded-t-xl shadow-md border-b border-gray-100 transition-all outline-none focus:outline-none focus:ring-0">
                    <div class="flex items-center gap-4">
                        <span class="bg-orange-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold">?</span>
                        <span class="text-lg font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-left">
                            Why is my account pending verification?
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                </button>

                <div class="faq-content grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out overflow-hidden">
                    <div class="min-h-0">
                        <div class="faq-gradient p-6 rounded-b-xl shadow-lg bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07]">
                            <p class="text-white text-center text-sm leading-relaxed">
                                The PLV-AlumNet Administrators needs time to manually cross-reference the data you provided. This process usually takes <strong>24-48 hours</strong>. You will be notified via email once approved.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ ITEM -->
            <section class="faq-item group">
                <button class="faq-toggle w-full flex items-center justify-between bg-white p-5 rounded-t-xl shadow-md border-b border-gray-100 transition-all outline-none focus:outline-none focus:ring-0">
                    <div class="flex items-center gap-4">
                        <span class="bg-orange-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold">?</span>
                        <span class="text-lg font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-left">
                            I am an employer. How do I post a job?
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                </button>

                <div class="faq-content grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out overflow-hidden">
                    <div class="min-h-0">
                        <div class="faq-gradient p-6 rounded-b-xl shadow-lg bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07]">
                            <p class="text-white text-center text-sm leading-relaxed">
                                By clicking the <strong>”Sign Up” button </strong> and register your company profile. Once your company is approved by our Admins, you will gain access to the Job Board where you can post, manage job postings and applicants.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ ITEM -->
            <section class="faq-item group">
                <button class="faq-toggle w-full flex items-center justify-between bg-white p-5 rounded-t-xl shadow-md border-b border-gray-100 transition-all outline-none focus:outline-none focus:ring-0">
                    <div class="flex items-center gap-4">
                        <span class="bg-orange-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold">?</span>
                        <span class="text-lg font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-left">
                            How long does it take for a job posting to go live?
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                </button>

                <div class="faq-content grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out overflow-hidden">
                    <div class="min-h-0">
                        <div class="faq-gradient p-6 rounded-b-xl shadow-lg bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07]">
                            <p class="text-white text-center text-sm leading-relaxed">
                                Job postings typically go live immediately after submission once approved by the system's administrator. However, PLV reserves the right to review new employer accounts or flag unusual postings, which may delay publication by up to [Hours/days] .
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ ITEM -->
            <section class="faq-item group mb-5">
                <button class="faq-toggle w-full flex items-center justify-between bg-white p-5 rounded-t-xl shadow-md border-b border-gray-100 transition-all outline-none focus:outline-none focus:ring-0">
                    <div class="flex items-center gap-4">
                        <span class="bg-orange-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold">?</span>
                        <span class="text-lg font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-left">
                            Where can I find the university's official contact information?
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                </button>

                <div class="faq-content grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-in-out overflow-hidden">
                    <div class="min-h-0">
                        <div class="faq-gradient p-6 rounded-b-xl shadow-lg bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07]">
                            <p class="text-white text-center text-sm leading-relaxed">
                                Please refer to the <strong>footer</strong> section of the website for the Official Facebook Page, Address and Email of the University.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </section>

    @include('partials.footer-employer')

</body>

<script>
    document.querySelectorAll('.faq-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const faqItem = button.closest('.faq-item');
            const content = faqItem.querySelector('.faq-content');
            const isOpen = faqItem.classList.contains('active');

            // Close all
            document.querySelectorAll('.faq-item').forEach(el => {
                el.classList.remove('active');
                el.querySelector('.faq-content')
                    .classList.replace('grid-rows-[1fr]', 'grid-rows-[0fr]');
            });

            // Open clicked
            if (!isOpen) {
                faqItem.classList.add('active');
                content.classList.replace('grid-rows-[0fr]', 'grid-rows-[1fr]');
            }
        });
    });
</script>

</html>