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
        <div class="flex items-center gap-3 ml-10">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="Logo Mark" class="h-12 w-12">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="Letter Mark" class="h-8 w-30">
        </div>

        <nav class="flex items-center justify-center gap-10 text-white flex-1 font-medium text-sm">
            <a href="{{ route('employer.dashboard') }}"
                class="{{ $current_page === 'employer.dashboard' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
                HOME
            </a>

            <a href="announcements_employer.php"
                class="{{ $current_page === 'general.announcements' ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]' }}">
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
                <button onclick="toggleNotifications(event)" class="hover:text-[#ED7A07] transition-colors">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                </button>

                <button onclick="toggleSidebar()" class="hover:text-[#ED7A07] transition-colors focus:outline-none">
                    <i data-lucide="circle-user" class="w-7 h-7"></i>
                </button>
            </div>
        </nav>

        @include('partials.user-sidebar')

    </header>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>