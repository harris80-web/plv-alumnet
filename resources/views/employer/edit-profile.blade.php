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
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
        background-size: cover;
        background-position: center;
    }
</style>

<body>
    @include('partials.header-employer')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="main-content-wrapper min-h-screen bg-gray-50 flex flex-col items-center justify-center p-6">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <form action="{{ route('employers.updateProfile', $user->user_id) }}" method="post" enctype="multipart/form-data">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-6xl font-['Montserrat']">

                @csrf
                @method('PUT')
                <section class="bg-white rounded-3xl border border-gray-100 shadow-md p-10 flex flex-col items-center">
                    <h2 class="text-2xl font-bold mb-8 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Edit Employer Profile
                    </h2>

                    <div class="relative mb-6">
                        <div class="w-40 h-40 rounded-full bg-cyan-100 flex items-center justify-center shadow-inner border-4 border-white overflow-hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>

                        <div class="absolute bottom-1 right-1">
                            <button type="button" onclick="togglePhotoOptions(event)" class="bg-gray-600 text-white p-2 rounded-full border-2 border-white hover:bg-gray-800 transition z-10 shadow-md">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </button>

                            <div id="photoOptions" class="hidden absolute left-full ml-2 top-0 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                <button type="button" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] tracking-tighter text-[#0E0F3B] hover:font-bold hover:bg-gray-50 flex items-center gap-3 transition">
                                    <i class="fa-solid fa-image text-[#0E0F3B] text-sm"></i> View Profile Image
                                </button>

                                <label for="imageUpload" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] tracking-tighter text-[#0E0F3B] hover:font-bold hover:bg-gray-50 flex items-center gap-3 cursor-pointer mb-0 transition">
                                    <i class="fa-solid fa-upload text-[#0E0F3B] text-sm"></i> Upload an Image
                                    <input type="file" name="user_profile_picture" id="imageUpload" class="hidden" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="w-full space-y-6">
                        <div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input type="text" name="user_first_name" placeholder="First Name" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-lg font-medium text-black focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400">
                                </div>
                                <div>
                                    <input type="text" name="user_middle_name" placeholder="Middle Name" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-lg font-medium text-black focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400">
                                </div>
                                <div>
                                    <input type="text" name="user_last_name" placeholder="Last Name" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-lg font-medium text-black focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400">
                                </div>
                                <div>
                                    <select name="user_suffix" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-lg font-medium text-black focus:border-[#C73D1A] focus:ring-0 outline-none transition bg-white">
                                        <option value="" disabled selected>Suffix</option>
                                        <option value="Jr.">Jr.</option>
                                        <option value="Sr.">Sr.</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                        <option value="V">V</option>
                                        <option value="N/A">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter mt-1">Employer Name</p>
                        </div>

                        <div>
                            <input type="text" name="employer_position" placeholder="e.g. HR Manager" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-md font-medium text-gray-800 focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400">
                            <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter mt-1">Employer Position</p>
                        </div>

                        <div class="space-y-4 pt-2">
                            <div>
                                <input type="email" name="user_email" placeholder="example@domain.com" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-gray-800 text-md font-medium focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400">
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter mt-1">Email Address</p>
                            </div>
                            <div>
                                <input type="tel" name="user_number" placeholder="+63 XXX XXX XXXX" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-gray-800 text-md font-medium focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400">
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter mt-1">Contact Number</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-3xl border border-gray-100 shadow-md p-10 flex flex-col items-center">
                    <h2 class="text-2xl font-bold mb-8 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Edit Business Details
                    </h2>

                    <div class="w-40 h-40 flex flex-col items-center justify-center mb-4 border border-[#0E0F3B] border-dashed rounded-2xl bg-slate-50 hover:bg-slate-100 transition cursor-pointer"
                        onclick="document.getElementById('companyLogoInput').click()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#0E0F3B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-[10px] font-bold text-[#0E0F3B] uppercase mt-2">Update Logo</span>
                        <input type="file" name="employer_company_logo" id="companyLogoInput" accept="image/*" class="hidden">
                    </div>
                    <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter mb-8">Business Logo</p>

                    <div class="w-full mb-8">
                        <input type="text" name="employer_company_name" placeholder="Enter registered business name..." class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg font-medium focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400 text-center">
                        <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter mt-1 text-center">Business Name</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 w-full">
                        <div>
                            <input type="text" name="employer_year_established" placeholder="YYYY" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-gray-800 text-md font-medium focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400 text-center">
                            <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-tighter leading-none mt-2">Year Established</p>
                        </div>
                        <div>
                            <input type="text" name="employer_company_size" placeholder="e.g. 50-100" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-gray-800 text-md font-medium focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400 text-center">
                            <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-tighter leading-none mt-2">Company Size</p>
                        </div>
                        <div>
                            <select type="text" name="employer_industry" placeholder="E.G. TECHNOLOGY" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-gray-800 text-md font-medium uppercase focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400 text-center">
                                <option value="" disabled selected>Select Industry</option>
                                @foreach($industries as $industry)
                                    <option value="{{ $industry->industry_id }}" {{ old('employer_industry') == $industry->industry_id ? 'selected' : '' }}>
                                        {{ $industry->industry_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-tighter leading-none mt-2">Industry / Sector</p>
                        </div>
                        <div>
                            <input type="url" name="employer_website_url" placeholder="www.website.com" class="w-full px-4 py-1.5 border border-[#0E0F3B] rounded-lg text-gray-800 text-md font-medium focus:border-[#C73D1A] focus:ring-0 outline-none transition placeholder-gray-400 text-center">
                            <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-tighter leading-none mt-2">Website URL</p>
                        </div>
                    </div>
                </section>

            </div>

            <div class="mt-8 flex gap-4 w-full max-w-6xl justify-end">
                <button class="px-8 py-3 rounded-xl border border-[#0E0F3B]  text-[#0E0F3B] font-bold hover:bg-[#0E0F3B] hover:text-white transition uppercase text-xs tracking-widest">
                    Cancel
                </button>
                <button class="px-10 py-3 rounded-xl bg-[#0D0D2B] text-white font-bold hover:bg-blue-900 transition uppercase text-xs tracking-widest shadow-lg">
                    Save Changes
                </button>
            </div>
        </form>
    </main>

    @include('partials.footer-employer')

</body>

<script>
    function togglePhotoOptions(event) {
        event.stopPropagation(); // Prevents the window click listener from immediately closing it
        const options = document.getElementById('photoOptions');
        options.classList.toggle('hidden');
    }

    // Close the dropdown when clicking anywhere else on the page
    window.addEventListener('click', function(e) {
        const options = document.getElementById('photoOptions');
        const button = event.target.closest('button');

        // If the click is outside the options menu and not on the toggle button, hide it
        if (!options.contains(e.target) && options.classList.contains('hidden') === false) {
            options.classList.add('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('dropdown-toggle');
        const menu = document.getElementById('dropdown-menu');

        // Toggle menu visibility
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent immediate closing from the window listener
            menu.classList.toggle('hidden');
        });

        // Close menu when clicking anywhere else on the screen
        window.addEventListener('click', (e) => {
            if (!menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });

        // Prevent closing when clicking inside the menu itself
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
</script>

</html>