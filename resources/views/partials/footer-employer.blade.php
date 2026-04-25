<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <footer class="bg-[#0E0F3B] text-white flex flex-col">
        <div class="flex px-5 py-5 justify-between ">

            <div class="flex gap-5 items-center font-[Montserrat] text-semibold">
                <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="" class="h-[93px] w-auto">
                <div class="flex flex-col gap-1">
                    <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="" class="h-12 w-60">
                    <p class="font-semibold text-[11px] tracking-tight">PAMANTASAN NG LUNGSOD NG VALENZUELA - ALUMNI
                        CONNECT
                    </p>
                    <p class="text-[13px]">Honoring the Past. Shaping the Future.</p>
                </div>
            </div>

            <div class="flex gap-4 font-[Montserrat]">
                <div class="w-[1.5px] bg-white "></div>
                <nav id="footer-nav" class="flex flex-col justify-center text-[15px] font-semibold gap-1">
                    <a href="{{ route('employer.dashboard') }}" class="hover:text-[#ED7A07]">HOME</a>
                    <a href="{{ route('employer.about') }}" class="hover:text-[#ED7A07]">ABOUT</a>
                    <a href="announcements_employer.php" class="hover:text-[#ED7A07]">ANNOUNCEMENTS</a>
                </nav>
            </div>

            <div class="flex gap-4 font-[Montserrat] ">
                <div class="w-[1.5px] bg-white "></div>
                <nav id="footer-nav" class="flex flex-col justify-center text-[15px] font-semibold gap-1">
                    <a href="{{ route('employer.privacy-policy') }}" class="hover:text-[#ED7A07]">PRIVACY POLICY</a>
                    <a href="{{ route('employer.tou') }}" class="hover:text-[#ED7A07]">TERMS OF USE</a>
                    <a href="{{ route('employer.faqs') }}" class="hover:text-[#ED7A07]">FAQs</a>
                </nav>
            </div>

            <div class="flex gap-4">
                <div class="w-[1.5px] bg-white "></div>
                <div class="flex items-end gap-4">
                    <div class="flex flex-col gap-1 max-w-[200px]">
                        <p class="font-bold text-[14px] leading-tight">PAMANTASAN NG LUNGSOD NG VALENZUELA</p>
                        <p class="font-[Montserrat] text-[9px] leading-relaxed">
                            <span class="font-bold">Address:
                            </span>
                            Maysan Road corner Tongco Street, Maysan, Valenzuela City, Philippines
                        </p>
                        <div class="flex items-center gap-3 mt-2">
                            <a href="https://www.facebook.com/PamantasanNgLungsodNgValenzuelaOfficialAccount/" target="_blank" class="group">
                                <div
                                    class="flex items-center justify-center w-8 h-8 bg-white border border-white rounded-full transition-all duration-300 group-hover:border-[#ED7A07] group-hover:bg-[#ED7A07]">
                                    <i
                                        class="fa-brands fa-facebook-f text-sm text-[#0E0F3B] transition-colors duration-300 group-hover:text-white"></i>
                                </div>
                            </a>
                            <a href="mailto:your@email.com" class="group">
                                <div
                                    class="flex items-center justify-center w-8 h-8 bg-white border border-white rounded-full transition-all duration-300 group-hover:border-[#ED7A07] group-hover:bg-[#ED7A07]">
                                    <i
                                        class="fa-solid fa-envelope text-sm text-[#0E0F3B] transition-colors duration-300 group-hover:text-white"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                    <img src="{{ asset('assets/PLV-Logo-2.svg') }}" alt="" class="h-[100px] w-auto">
                </div>
            </div>
        </div>

        <div class="bg-[#ED7A07] w-full text-center py-1.5">
            <p class="font-[Montserrat] font-semibold text-[15px]">©2025 PLV-AlumNet | All Rights Reserved</p>
        </div>
    </footer>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        lucide.createIcons();

        const navLinks = document.querySelectorAll('#footer-nav a');
        const currentUrl = window.location.pathname;

        navLinks.forEach(link => {
            // Check if the link's href matches the current page URL
            if (link.getAttribute('href') !== "" && currentUrl.includes(link.getAttribute('href'))) {
                link.classList.add('text-[#ED7A07]');
                link.classList.remove('text-white');
            }
        });

    </script>
</body>

</html>