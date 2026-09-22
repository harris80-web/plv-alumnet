<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Central responsive stylesheet + hamburger/sidebar script (general/alumni/employer only). See RESPONSIVE-GUIDE.md. --}}
    @include('partials.responsive-assets', ['rpRole' => 'general'])
</head>

<body>
    {{-- rp-* classes/attributes below are hooks for public/assets/css/responsive-public.css
         (mobile/tablet header + hamburger). On desktop they change nothing. --}}
    <header class="sticky top-0 z-50 w-full h-20 bg-[#0E0F3B] font-semibold flex justify-between rp-header">
        <a href="{{ route('general.home') }}" class="flex items-center gap-3 ml-10 rp-header-logo">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="" class="h-12 w-12">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="PLV-AlumNet" class="h-8 w-30">
        </a>
        <nav class="flex items-center justify-center gap-10 text-white flex-1 font-medium text-sm rp-header-nav" aria-label="Main navigation">
            <a href="{{ route('general.home') }}"
                class="rp-nav-link {{ request()->routeIs('general.home') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                HOME
            </a>

            <a href="{{ route('general.about') }}"
                class="rp-nav-link {{ request()->routeIs('general.about') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                ABOUT
            </a>

            <a href="{{ route('notices.guestEventsSeminars') }}"
                class="rp-nav-link {{ request()->routeIs('notices.guestEventsSeminars') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                EVENTS
            </a>

            <a href="{{ route('notices.guestAnnouncements') }}"
                class="rp-nav-link {{ request()->routeIs('notices.guestAnnouncements') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                ANNOUNCEMENTS
            </a>

            <a href="{{ route('jobPosting.jobBoard') }}"
                class="rp-nav-link {{ request()->routeIs('jobPosting.jobBoard') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                JOB BOARD
            </a>

            <div class="flex gap-4 p-8 justify-self-end rp-header-actions">
                <a href="{{ route('auth.register') }}"
                class="px-6 py-2 border-2 border-white text-white font-semibold rounded-lg hover:border-[#ED7A07] hover:bg-[#ED7A07] hover:text-white transition-all">
                SIGN UP
            </a>

            <a href="{{ route('auth.login') }}"
                class="px-6 py-2 bg-white text-[#0E0F3B] font-semibold rounded-lg hover:bg-[#0E0F3B] hover:border-2 border-[#ED7A07] hover:text-[#ED7A07] transition-colors">
                LOGIN
            </a>
            </div>


        </nav>

        {{-- Hamburger (hidden on desktop, shown <=1023px) opens the guest menu panel below. --}}
        @include('partials.rp-hamburger-button')

        @include('partials.guest-sidebar')
    </header>
    @include('partials.ui-animations')
</body>

</html>