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
</head>

<body>
    <?php
    // Logic to determine active page
    $current_page = Route::currentRouteName();
    ?>

    <header class="sticky top-0 z-50 w-full bg-[#0E0F3B] font-bold flex justify-between px-[4em] py-[1em]">
        <a href="{{ route('employer.dashboard') }}" class="flex items-center gap-3 ml-10">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="Logo Mark" class="h-12 w-12">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="Letter Mark" class="h-8 w-30">
        </a>

        <nav class="flex items-center justify-center gap-10 text-white flex-1 font-medium text-sm">
            <a href="{{ route('employer.dashboard') }}"
                class="{{ $current_page === 'employer.dashboard' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                HOME
            </a>

            <a href="{{ route('notices.employerAnnouncements') }}"
                class="{{ $current_page === 'notices.employerAnnouncements' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                ANNOUNCEMENTS
            </a>

            <a href="{{ route('jobPosting.jobBoard') }}"
                class="{{ $current_page === 'jobPosting.jobBoard' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                JOB BOARD
            </a>

            <a href="{{ route('jobPosting.myJobPosts', ['id' => auth()->id()]) }}"
                class="{{ $current_page === 'jobPosting.myJobPosts' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                MY JOB POSTINGS
            </a>

            <div class="flex items-center gap-6 text-white ml-5 relative">
                <button onclick="toggleNotifications(event)" class="group relative hover:text-[#ED7A07] transition-colors">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    <span id="notifBadge" class="hidden absolute -top-1.5 -right-1.5 bg-[#C73D1A] text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center"></span>
                    <span class="pointer-events-none absolute top-full left-1/2 -translate-x-1/2 mt-2 whitespace-nowrap bg-[#0E0F3B] text-white text-[10px] font-semibold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">Notifications</span>
                </button>

                <button onclick="toggleSidebar()" class="group relative hover:text-[#ED7A07] transition-colors focus:outline-none">
                    <i data-lucide="circle-user" class="w-7 h-7"></i>
                    <span class="pointer-events-none absolute top-full left-1/2 -translate-x-1/2 mt-2 whitespace-nowrap bg-[#0E0F3B] text-white text-[10px] font-semibold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">Profile</span>
                </button>
            </div>
        </nav>

        @include('partials.user-sidebar')

    </header>

    @include('partials.ui-animations')
    <script>
        lucide.createIcons();
    </script>

    @include('partials.chatbot-widget')
</body>

</html>