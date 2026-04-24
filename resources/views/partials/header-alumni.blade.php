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
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <?php $current_page = basename($_SERVER['PHP_SELF'], ".php");  ?>

    <header class="sticky top-0 z-50 w-full bg-[#0E0F3B] font-bold flex justify-between px-[4em] py-[1em]">
        <div class="flex items-center gap-3 ml-10">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="" class="h-12 w-12">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="" class="h-8 w-30">
        </div>
        <nav class="flex items-center justify-center gap-10 text-white flex-1 font-medium text-sm">
            <a href="{{ route('alumni.dashboard') }}"
                class="<?php echo ($current_page == 'index_alumni') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]'; ?>">
                HOME
            </a>

            <a href="events.php"
                class="<?php echo ($current_page == 'events') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]'; ?>">
                EVENTS
            </a>

            <a href="announcements.php"
                class="<?php echo ($current_page == 'announcements') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]'; ?>">
                ANNOUNCEMENTS
            </a>

            <a href="alumni_job_board.php"
                class="<?php echo ($current_page == 'jobs') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]'; ?>">
                JOB BOARD
            </a>

            <a href="alumni_directory.php"
                class="<?php echo ($current_page == 'alumni_directory') ? 'text-[#ED7A07]' : 'hover:text-[#ED7A07]'; ?>">
                DIRECTORY
            </a>

            <div class="flex items-center gap-6 text-white ml-5">
                <button class="hover:text-[#ED7A07] transition-colors">
                    <i data-lucide="messages-square" class="w-6 h-6"></i>
                </button>
                <button onclick="toggleNotifications()" class="hover:text-[#ED7A07] transition-colors">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                </button>
                <button onclick="toggleSidebar()" class="hover:text-[#ED7A07] transition-colors">
                    <i data-lucide="circle-user" class="w-7 h-7"></i>
                </button>
            </div>

        </nav>
    </header>
    <script src="script.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>