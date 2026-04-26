<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Edit Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
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
        <form action="{{ route('alumni.updateProfile', $user->user_id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden p-8 md:p-12">
            @csrf
            @method('PUT')
            <h2 class="w-fit mx-auto text-center text-3xl font-bold mb-10 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                EDIT PROFILE
            </h2>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-3 flex justify-center md:justify-start">
                    <div class="relative w-40 h-40">

                        <div class="w-full h-full bg-[#0E0F3B] rounded-full flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                            @if ($user->user_profile_picture)
                            <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                            @else
                            <i class="fa-solid fa-user text-7xl text-white mt-4"></i>
                            @endif

                        </div>

                        <div class="absolute bottom-1 right-1">
                            <button type="button" onclick="togglePhotoOptions(event)" class="bg-gray-600 text-white p-2 rounded-full border-2 border-white hover:bg-gray-800 transition z-10 shadow-md">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </button>

                            <div id="photoOptions" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                <button class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:font-bold hover:bg-gray-100 flex items-center gap-3">
                                    <i class="fa-solid fa-image text-[#0E0F3B]"></i> View Profile Image
                                </button>
                                <label for="user_profile_picture" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:font-bold hover:bg-gray-100 flex items-center gap-3 cursor-pointer mb-0">
                                    <i class="fa-solid fa-upload text-[#0E0F3B]"></i> Upload an Image
                                    <input type="file" name="user_profile_picture" id="user_profile_picture" class="hidden">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-9">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-y-6 gap-x-6">
                        <div class="md:col-span-4">
                            <p class="text-xs font-bold text-orange-600 uppercase">Alumni ID No.</p>
                            <h3 class="text-xl font-black text-[#0E0F3B] uppercase">Alumni ID No.</h3>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-orange-600 uppercase">Last Name</p>
                            <h3 class="text-lg font-black text-[#0E0F3B] uppercase">{{ $user->user_last_name }}</h3>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-orange-600 uppercase">First Name</p>
                            <h3 class="text-lg font-black text-[#0E0F3B] uppercase">{{ $user->user_first_name }}</h3>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-orange-600 uppercase">Middle Name</p>
                            <h3 class="text-lg font-black text-[#0E0F3B] uppercase">{{ $user->user_middle_name }}</h3>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-orange-600 uppercase">Suffix</p>
                            <h3 class="text-lg font-black text-[#0E0F3B] uppercase">{{ $user->user_suffix }}</h3>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-orange-600 uppercase">Program</p>
                            <h3 class="text-lg font-black text-[#0E0F3B] uppercase">{{ $user->alumnus->program->program_name ?? 'Not specified' }}</h3>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-bold text-orange-600 uppercase">Batch</p>
                            <h3 class="text-lg font-black text-[#0E0F3B] uppercase">{{ $user->alumnus->alumnus_batch ?? 'Not specified' }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12">

                <div class="space-y-4">
                    <div>
                        <label for="alumnus_employment_status" class="text-xs font-bold text-orange-600 uppercase block mb-1">Employment Status</label>
                        <select name="alumnus_employment_status" class="w-full md:w-3/4 py-1.5 px-2 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                            <option value="1" {{ $user->alumnus->alumnus_employment_status == 1 ? 'selected' : '' }}>Employed</option>
                            <option value="0" {{ $user->alumnus->alumnus_employment_status == 0 ? 'selected' : '' }}>Unemployed</option>
                        </select>
                    </div>
                    <div>
                        <label for="user_email" class="text-xs font-bold text-orange-600 uppercase block mb-1">Email</label>
                        <input type="email" name="user_email" placeholder="example@email.com" value="{{ $user->user_email }}" class="w-full md:w-3/4 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                    </div>

                    <div>
                        <label for="user_number" class="text-xs font-bold text-orange-600 uppercase block mb-1">Contact No.</label>
                        <input type="text" name="user_number" placeholder="09XXXXXXXXX" value="{{ $user->user_number }}" class="w-full md:w-3/4 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                    </div>

                    <div>
                        <label for="alumnus_skills" class="text-xs font-bold text-orange-600 uppercase block mb-1">Skills</label>
                        <textarea rows="4" name="alumnus_skills" placeholder="e.g. Web Development, UI/UX Design, Data Analysis..." class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">{{ $user->alumnus->alumnus_skills }}</textarea>
                    </div>
                </div>

                <div class="flex flex-col justify-start items-end space-y-4">
                    <div class="w-full md:w-72 flex items-center justify-between">
                        <span class="text-xs font-bold text-orange-600 uppercase">Resume</span>
                        <label for="alumnus_resume" class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-44">
                            Upload Resume
                        </label>
                        <input type="file" name="alumnus_resume" id="alumnus_resume" class="hidden">
                    </div>
                    <div class="w-full md:w-72 flex justify-end">
                        <button class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-44">
                            Upload File
                        </button>
                    </div>
                    <div class="w-full md:w-72 flex justify-end">
                        <button class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-44">
                            Upload File
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12">
                <button class="px-10 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold rounded-lg transition-all duration-200 uppercase tracking-widest text-sm hover:bg-[#0E0F3B] hover:text-white active:scale-95">
                    Cancel
                </button>
                <button type="submit" class="px-10 py-2 bg-[#0E0F3B] text-white font-bold rounded-lg hover:bg-[#1D46A4] transition uppercase tracking-widest text-sm shadow-lg">
                    Save
                </button>
            </div>

        </form>
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
                <a href="alumni_profile.php" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white  p-3 w-full transition font-semibold rounded-sm">
                    <i class="fa-solid fa-address-card w-6"></i> View Profile
                </a>
                <a href="alumni_edit.php" class="flex items-center gap-4  p-3 w-full transition font-bold bg-[#ED7A07] text-white hover:font-bold">
                    <i class="fa-solid fa-user-pen w-6"></i> Edit Profile
                </a>
                <a href="alumni_change_password.php" class="flex items-center gap-4 text-[#0E0F3B] hover:bg-[#ED7A07] hover:text-white p-3 w-full transition font-regular hover:font-semibold">
                    <i class="fa-solid fa-lock w-6"></i> Change Password
                </a>
            </nav>

            <div class="border-t pt-6">
                <a href="logout.php" class="flex items-center gap-4 text-[#0E0F3B] hover:text-[#ED7A07] p-3 transition font-bold">
                    <i class="fa-solid fa-right-from-bracket"></i> Log out
                </a>
            </div>
        </div>
    </div>

    @include('partials.footer-alumni')
    
</body>

<script>
    //TRIGGER FOR ALERT NOTIFICATION AND USER PROFILE SIDE BAR
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

    overlay.addEventListener('click', () => {
        sidebar.classList.add('translate-x-full');
        notification.classList.add('hidden');
        overlay.classList.add('hidden');
    });


    //user profile view image/upload image
    function togglePhotoOptions(event) {

        event.stopPropagation();
        const menu = document.getElementById('photoOptions');
        menu.classList.toggle('hidden');

    }

    window.addEventListener('click', function(e) {
        const menu = document.getElementById('photoOptions');
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });
</script>

</html>