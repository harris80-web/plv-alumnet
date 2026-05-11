<!--<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body>
    <h1>SUPERADMIN PROFILE</h1>
    <div>

        <div class="flex">
            <img src="{{ asset('storage/' . $user->user_profile_picture) ?? "No image set" }}" alt="Profile Picture" class="h-[100px] w-auto">
            <div class="flex flex-col">
                <h5>{{ $user->user_first_name }} {{ $user->user_middle_name }} {{ $user->user_last_name }} {{ $user->user_suffix }}</h5>
                <br>
                <h4>{{ $user->user_role }}</h4>
                <br>
                <h4>{{ $user->office->office_address }}</h4>
            </div>
        </div>
        <div>
            <h2>PERSONAL INFORMATION</h2>
            <div>
                <h5>FIRST NAME: {{ $user->user_first_name }}</h5>
            </div>
            <div>
                <h5>MIDDLE NAME: {{ $user->user_middle_name }}</h5>
            </div>
            <div>
                <h5>LAST NAME: {{ $user->user_last_name }}</h5>
            </div>
            <div>
                <h5>SUFFIX: {{ $user->user_suffix }}</h5>
            </div>
            <div>
                <h5>BIRTH DATE: {{ $user->office->office_birth_date }}</h5>
            </div>
            <div>
                <h5>EMAIL: {{ $user->user_email }}</h5>
            </div>
            <div>
                <h5>NUMBER: {{ $user->user_number }}</h5>
            </div>
            <div>
                <h5>ADDRESS: {{ $user->office->office_address }}</h5>
            </div>
        </div>
        <div>
            <h2>SYSTEM & SECURITY</h2>
            <div>
                <h5>USER ROLE: {{ $user->user_role }}</h5>
            </div>
            <div>
                <h5>STATUS: {{ $user->user_active ? 'Active' : 'Inactive' }}</h5>
            </div>
            <div>
                <h5>DATE CREATED: {{ $user->created_at }}</h5>
            </div>
        </div>
        <a href="{{ route('users.editProfile') }}">Edit Profile</a>
</body>

</html>-->

@php $current_page = 'super_admin_profile'; @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Profile | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dash-scroll {
            overflow-y: auto;
            flex: 1;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .dash-scroll::-webkit-scrollbar {
            display: none;
        }

        .card-shadow {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        /* Custom PLV Utilities */
        .text-plv-orange {
            color: #D97706;
        }

        .bg-plv-orange {
            background-color: #F59E0B;
        }

        .border-plv-orange {
            border-color: #F59E0B;
        }

        .text-info {
            color: #C73D1A;
        }

        .plv-gradient-text {
            background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')

        <main class="flex-1 flex flex-col overflow-hidden">
            @include('partials.super-admin-header')

            <div class="dash-scroll bg-[#e9eff6] p-6 lg:p-10">

                @if (session('success'))
                    <div
                        class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm mb-4 max-w-6xl mx-auto">
                        {{ session('success') }}
                    </div>
                    <script>
                        setTimeout(() => {
                            const alert = document.getElementById('successAlert');
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500); // remove after fade
                        }, 5000);
                    </script>
                @endif

                @if ($errors->any())
                    <div
                        class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm mb-4 max-w-6xl mx-auto">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @include('partials.success')

                <!-- FORM WRAPPER START -->
                <form id="profileForm" action="{{ route('offices.updateProfile', $user->user_id) }}" method="POST"
                    enctype="multipart/form-data" class="max-w-6xl mx-auto space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Profile Header Card -->
                    <div class="bg-white rounded-3xl p-8 flex items-center gap-8 card-shadow relative">
                        <div class="relative group">
                            <div
                                class="w-32 h-32 bg-[#0a0f2c] rounded-full flex items-center justify-center overflow-hidden">
                                @if ($user->user_profile_picture)
                                    <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Picture"
                                        class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="user" class="w-20 h-20 text-white opacity-20"></i>
                                @endif
                            </div>
                            <!-- Camera Icon for Edit Mode -->
                            <button type="button" id="cameraBtn"
                                class="hidden absolute bottom-0 right-0 bg-slate-200 p-2 rounded-full border-2 border-white hover:bg-slate-300 transition shadow-sm">
                                <i data-lucide="camera" class="w-4 h-4 text-slate-700"></i>
                            </button>
                            <!-- Photo Tooltip (Context Menu) -->
                            <!-- Positioned relative to the profile image container -->
                            <div id="photoMenu"
                                class="hidden absolute top-full mt-2 left-1/2 -translate-x-1/2 md:left-32 md:top-10 md:translate-x-0 bg-white border rounded-xl shadow-xl p-2 w-48 z-20 text-[#0E0F3B] font-medium">
                                <button type="button"
                                    class="flex items-center gap-3 w-full p-2.5 hover:bg-slate-100 rounded-lg text-sm transition-colors">
                                    <i data-lucide="user" class="w-4 h-4 text-slate-500"></i>
                                    See Profile Image
                                </button>
                                <label
                                    class="flex items-center gap-3 w-full p-2.5 hover:bg-slate-100 rounded-lg text-sm cursor-pointer transition-colors">
                                    <i data-lucide="image" class="w-4 h-4 text-slate-500"></i>
                                    Choose Profile Image
                                    <input type="file" name="user_profile_picture" class="hidden">
                                </label>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-[#0a0f2c]">{{ $user->user_first_name }}
                                {{ $user->user_last_name }}</h2>
                            <p class="text-[#C73D1A] font-semibold">
                                {{ ucwords(str_replace('_', ' ', $user->user_role)) }}</p>
                            <p class="text-slate-400 text-sm">{{ $user->office->office_address ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Personal Information Card -->
                    <div class="bg-white rounded-3xl p-8 card-shadow">
                        <div class="flex justify-between items-center mb-6 border-b border-[#ED7A07] pb-4">
                            <h3 class="text-xl font-bold plv-gradient-text">Personal Information</h3>

                            <div id="viewActions">
                                <button type="button" onclick="toggleEdit(true)"
                                    class="bg-plv-orange text-white px-6 py-1.5 rounded-lg flex items-center gap-2 hover:bg-orange-600 transition text-sm">
                                    Edit <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div id="editActions" class="hidden flex gap-3">
                                <button type="button" onclick="toggleEdit(false)"
                                    class="border border-plv-orange text-plv-orange px-6 py-1.5 rounded-lg hover:bg-orange-50 transition text-sm">Cancel</button>
                                <button type="submit"
                                    class="bg-plv-orange text-white px-6 py-1.5 rounded-lg hover:bg-orange-600 transition text-sm">Save</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-y-8 gap-x-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">First Name</label>
                                <span class="view-mode font-semibold text-info">{{ $user->user_first_name }}</span>
                                <input type="text" name="user_first_name" value="{{ $user->user_first_name }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Middle Name</label>
                                <span
                                    class="view-mode font-semibold text-info">{{ $user->user_middle_name ?? '—' }}</span>
                                <input type="text" name="user_middle_name" value="{{ $user->user_middle_name }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Last Name</label>
                                <span class="view-mode font-semibold text-info">{{ $user->user_last_name }}</span>
                                <input type="text" name="user_last_name" value="{{ $user->user_last_name }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Suffix</label>
                                <span class="view-mode font-semibold text-info">{{ $user->user_suffix ?? 'N/A' }}</span>
                                <input type="text" name="user_suffix" value="{{ $user->user_suffix }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Date of Birth</label>
                                <span
                                    class="view-mode font-semibold text-info">{{ $user->office->office_birth_date ?? '—' }}</span>
                                <input type="date" name="office_birth_date"
                                    value="{{ $user->office->office_birth_date }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1 md:col-span-1">
                                <label class="text-slate-500 text-sm">Email Address</label>
                                <span class="view-mode font-semibold text-info">{{ $user->user_email }}</span>
                                <input type="email" name="user_email" value="{{ $user->user_email }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Phone Number</label>
                                <span class="view-mode font-semibold text-info">{{ $user->user_number ?? '—' }}</span>
                                <input type="text" name="user_number" value="{{ $user->user_number }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Address</label>
                                <span
                                    class="view-mode font-semibold text-info">{{ $user->office->office_address ?? '—' }}</span>
                                <input type="text" name="office_address" value="{{ $user->office->office_address }}"
                                    class="edit-mode hidden border border-plv-orange/50 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-[#C73D1A] text-[#0a0f2c]">
                            </div>
                        </div>
                    </div>

                    <!-- System & Security Card -->
                    <div class="bg-white rounded-3xl p-8 card-shadow">
                        <!-- Header with full-width border -->
                        <div class="border-b border-[#ED7A07] mb-6 pb-4">
                            <h3 class="text-xl font-bold plv-gradient-text">System & Security</h3>
                        </div>

                        <!-- Grid Content -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">User Role</label>
                                <span
                                    class="font-semibold text-info">{{ ucwords(str_replace('_', ' ', $user->user_role)) }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Account Status</label>
                                <span
                                    class="font-semibold text-info">{{ $user->user_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Date Created</label>
                                <span class="font-semibold text-info">{{ $user->created_at->format('m/d/y') }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-slate-500 text-sm">Last Login</label>
                                <span class="font-semibold text-info">20 mins ago</span>
                            </div>
                        </div>
                    </div>

                </form>
                <!-- FORM WRAPPER END -->
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        const cameraBtn = document.getElementById('cameraBtn');
        const photoMenu = document.getElementById('photoMenu');

        function toggleEdit(isEdit) {
            document.getElementById('viewActions').classList.toggle('hidden', isEdit);
            document.getElementById('editActions').classList.toggle('hidden', !isEdit);
            document.querySelectorAll('.view-mode').forEach(el => el.classList.toggle('hidden', isEdit));
            document.querySelectorAll('.edit-mode').forEach(el => el.classList.toggle('hidden', !isEdit));
            cameraBtn.classList.toggle('hidden', !isEdit);
            if (!isEdit) photoMenu.classList.add('hidden');
        }

        cameraBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            photoMenu.classList.toggle('hidden');
        });

        window.addEventListener('click', () => photoMenu.classList.add('hidden'));
    </script>
</body>

</html>