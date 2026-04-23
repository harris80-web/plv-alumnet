<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background:
            url('assets/heroSectionBackground.png');
        background-size: cover;
        background-position: center;
    }
</style>

<body>
    @include('partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="max-w-5xl mx-auto mt-10 mb-12 px-4">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden p-8 md:p-12">

            <h2 class="w-fit mx-auto text-center text-3xl font-bold mb-10 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                ALUMNI PROFILE
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">

                <div class="md:col-span-3 flex justify-center md:justify-start">
                    <div class="w-40 h-40 bg-slate-900 rounded-full flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                        <svg class="w-25 h-25 text-white mt-8" fill="currentColor" viewBox="0 0 20 20">
                            @if ($user->user_profile_picture)
                                <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                            @else
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            @endif
                        </svg>
                    </div>
                </div>

                <div class="md:col-span-9">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-y-6 gap-x-4">

                        <!-- OPTIONAL LANG TO TE DI KO SURE KUNG NILAGAY BA NI HARRIS TO-->
                        <div class="md:col-span-4">
                            <p class="text-xs font-black text-slate-900 uppercase">Alumni ID No.</p>
                            <p class="text-lg text-gray-600 font-medium">--</p>
                        </div>

                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase">Last Name</p>
                            <p class="text-lg text-gray-600 font-medium">{{ $user->user_last_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase">First Name</p>
                            <p class="text-lg text-gray-600 font-medium">{{ $user->user_first_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase">Middle Name</p>
                            <p class="text-lg text-gray-600 font-medium">{{ $user->user_middle_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase">Suffix</p>
                            <p class="text-lg text-gray-600 font-medium">{{ $user->user_suffix ?? '--' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase">Program</p>
                            <p class="text-lg text-gray-600 font-medium">{{ $user->alumnus->program->program_name ?? '--' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase">Batch</p>
                            <p class="text-lg text-gray-600 font-medium">{{ $user->alumnus->alumnus_batch ?? '--' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12 border-t pt-8">

                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase">Employment Status</p>
                        <p class="text-md text-gray-600 font-medium">{{ $user->alumnus->alumnus_employment_status == 0 ? 'Unemployed' : 'Employed' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase">Email</p>
                        <p class="text-md text-gray-600 font-medium">{{ $user->user_email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase">Contact No.</p>
                        <p class="text-md text-gray-600 font-medium">{{ $user->user_number ?? '--' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase">Skills</p>
                        <p class="text-md text-gray-600 font-medium">{{ $user->alumnus->alumnus_skills ?? '--' }}</p>
                    </div>
                </div>

                <div class="flex flex-col justify-start items-end space-y-3">
                    <div class="w-full md:w-64 flex items-center justify-between">
                        <span class="text-xs font-black text-slate-900 uppercase">Resume</span>
                        <button class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-40">
                            View Resume
                        </button>
                    </div>

                    <div class="w-full md:w-64 flex justify-end">
                        <button class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-40">
                            View File
                        </button>
                    </div>

                    <div class="w-full md:w-64 flex justify-end">
                        <button class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-40">
                            View File
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <div id="menuOverlay" class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity duration-300"></div>

    <div id="notificationPopup" class="fixed top-20 right-[250px] w-72 bg-white rounded-xl shadow-2xl z-[70] hidden transform origin-top-right transition-all duration-300 scale-95 opacity-0">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-[#0E0F3B] font-bold">Notifications</h3>
            <button onclick="toggleNotifications()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-10 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm">No notifications</p>
        </div>
    </div>

    <div id="userSidebar" class="fixed top-0 right-0 h-full w-80 bg-white z-[70] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="p-6 relative flex flex-col h-full">
            <button onclick="toggleSidebar()" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <div class="flex flex-col items-center mt-8 mb-6">
                <div class="w-24 h-24 bg-[#0E0F3B] rounded-full flex items-center justify-center mb-4 overflow-hidden">
                    <i class="fa-solid fa-user text-5xl text-white"></i>
                </div>
                <h2 class="text-[#ED7A07] font-semibold text-xl uppercase tracking-wider">User</h2>
                <div class="w-full border-b mt-4"></div>
            </div>

            <nav class="space-y-4 flex-grow">
                <a href="{{ route('user.profile') }}" class="flex items-center gap-4 bg-[#ED7A07] text-white p-3 w-full transition font-bold rounded-sm">
                    <i class="fa-solid fa-address-card w-6"></i> View Profile
                </a>
                <a href="{{ route('users.editProfile') }}" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white p-3 w-full transition font-regular hover:font-semibold">
                    <i class="fa-solid fa-user-pen w-6"></i> Edit Profile
                </a>
                <a href="{{ route('alumni.changePassword') }}" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white p-3 w-full transition font-regular hover:font-semibold">
                    <i class="fa-solid fa-lock w-6"></i> Change Password
                </a>
            </nav>

            <form action="{{ route('user.logout') }}" method="POST" class="border-t pt-6">
                @csrf
                <button type="submit" class="flex items-center gap-4 text-[#0E0F3B] hover:text-[#ED7A07] p-3 transition font-bold">
                    <i class="fa-solid fa-right-from-bracket"></i> Log out
                </button>
            </form>
        </div>
    </div>

    @include('partials.footer-alumni')
</body>

<!--TRIGGER FOR ALERT NOTIFICATION AND USER PROFILE SIDE BAR-->
<script>
    const sidebar = document.getElementById('userSidebar');
    const notification = document.getElementById('notificationPopup');
    const overlay = document.getElementById('menuOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('translate-x-full');
        overlay.classList.toggle('hidden');
        // Close notifications if sidebar opens
        notification.classList.add('hidden');
    }

    function toggleNotifications() {
        const isHidden = notification.classList.contains('hidden');

        if (isHidden) {
            notification.classList.remove('hidden');
            // Small timeout to allow transition
            setTimeout(() => {
                notification.classList.remove('scale-95', 'opacity-0');
            }, 10);
        } else {
            notification.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 300);
        }
    }

    // Close everything when clicking the darkened overlay
    overlay.addEventListener('click', () => {
        sidebar.classList.add('translate-x-full');
        notification.classList.add('hidden');
        overlay.classList.add('hidden');
    });
</script>

</html>