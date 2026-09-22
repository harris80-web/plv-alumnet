<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Central responsive stylesheet + hamburger/sidebar script (general/alumni/employer only). See RESPONSIVE-GUIDE.md. --}}
    @include('partials.responsive-assets', ['rpRole' => 'employer'])
</head>

<body>
    <?php
    // Logic to determine active page
    $current_page = Route::currentRouteName();
    ?>

    {{-- rp-* classes/attributes below are hooks for public/assets/css/responsive-public.css
         (mobile/tablet header + hamburger). On desktop they change nothing. --}}
    <header class="sticky top-0 z-50 w-full bg-[#0E0F3B] font-bold flex justify-between px-[4em] py-[1em] rp-header">
        <a href="{{ route('employer.dashboard') }}" class="flex items-center gap-3 ml-10 rp-header-logo">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="Logo Mark" class="h-12 w-12">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="Letter Mark" class="h-8 w-30">
        </a>

        <nav class="flex items-center justify-center gap-10 text-white flex-1 font-medium text-sm rp-header-nav" aria-label="Main navigation">
            <a href="{{ route('employer.dashboard') }}"
                class="rp-nav-link {{ $current_page === 'employer.dashboard' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                HOME
            </a>

            <a href="{{ route('notices.employerAnnouncements') }}"
                class="rp-nav-link {{ $current_page === 'notices.employerAnnouncements' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                ANNOUNCEMENTS
            </a>

            <a href="{{ route('jobPosting.jobBoard') }}"
                class="rp-nav-link {{ $current_page === 'jobPosting.jobBoard' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                JOB BOARD
            </a>

            <a href="{{ route('jobPosting.myJobPosts', ['id' => auth()->id()]) }}"
                class="rp-nav-link {{ $current_page === 'jobPosting.myJobPosts' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                MY JOB POSTINGS
            </a>

            <div class="flex items-center gap-6 text-white ml-5 relative rp-header-actions">
                <button onclick="toggleNotifications(event)" aria-label="Notifications" class="group relative hover:text-[#ED7A07] transition-colors">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    <span id="notifBadge" class="hidden absolute -top-1.5 -right-1.5 bg-[#C73D1A] text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center"></span>
                    <span class="pointer-events-none absolute top-full left-1/2 -translate-x-1/2 mt-2 whitespace-nowrap bg-[#0E0F3B] text-white text-[10px] font-semibold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">Notifications</span>
                </button>

                <button onclick="toggleSidebar()" aria-label="Profile menu" aria-controls="userSidebar" aria-expanded="false" data-rp-menu-toggle class="group relative hover:text-[#ED7A07] transition-colors focus:outline-none rp-profile-toggle">
                    <i data-lucide="circle-user" class="w-7 h-7"></i>
                    <span class="pointer-events-none absolute top-full left-1/2 -translate-x-1/2 mt-2 whitespace-nowrap bg-[#0E0F3B] text-white text-[10px] font-semibold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">Profile</span>
                </button>
            </div>
        </nav>

        {{-- Hamburger: hidden on desktop, shown <=1023px in place of the profile icon. Opens the SAME #userSidebar. --}}
        @include('partials.rp-hamburger-button')

        @include('partials.user-sidebar')

    </header>

    @include('partials.ui-animations')
    <script>
        lucide.createIcons();
    </script>

    @include('partials.chatbot-widget')
    @include('partials.back-to-top', ['nearChatWidget' => true])
    @include('partials.alert-modal')
</body>

</html>