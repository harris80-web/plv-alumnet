<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Employer Sign Up</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-image:
                url("{{ asset('assets/loginBackground.svg') }}");
            background-size: cover;
            background-position: center;

            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen relative flex items-center justify-center">

    <div class="absolute inset-0 z-0">
        <img src="{{ asset('assets/alumnetBackground.svg') }}" alt="PLV Building" class="w-full h-full object-cover">
        <!-- <div class="absolute inset-0 bg-overlay"></div> -->
    </div>

    <div
        class="relative z-10 container mx-auto px-6 lg:px-5 flex flex-col lg:flex-row items-center justify-evenly gap-2">

        <div class="text-white max-w-lg m-7">
            <div class="flex items-center gap-1 mb-2">
                <div class="w-20 h-20 flex items-center justify-center">
                    <img src="{{ asset('assets/PLV-Logo-2.svg') }}" alt="" class="h-[90px] w-auto">
                </div>
                <div class="flex flex-col items-center h-auto w-auto">
                    <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt="" class="h-auto w-auto ">
                    <p class="font-[Montserrat] font-regular text-xs tracking-widest uppercase text-center">Pamantasan
                        ng Lungsod ng Valenzuela</p>
                    <p class="text-xs text-center opacity-80 uppercase">Alumni Connect</p>
                </div>
                <div class="flex items-center justify-center">
                    <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_WHITE.svg') }}" alt="" class="h-[90px] w-auto">
                </div>
            </div>
            <h2 class=" font-light text-center">Honoring the Past. Shaping the Future.</h2>
        </div>


        <div>
            <div class="text-left mb-4 mt-5">
                <a href="{{ route('general.home') }}" class="text-white text-sm hover:text-[#0E0F3B]">↩ Return to Home</a>
            </div>

            <div class="bg-white rounded-xl shadow-2xl p-8 w-full w-auto h-auto mb-4">


                <div class="text-center mb-6">
                    <span
                        class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Sign Up </span>
                    <span
                        class=" font-[Inter] text-[#b85c38] font-medium bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent"><br>Let's
                        Get Started!</span>
                </div>

                @if ($errors->any())
                    <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-sm">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.storeEmployer') }}" method="POST" enctype="multipart/form-data" class="space-y-4 w-full max-w-md mx-auto h-auto ">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Business Name:</label>
                        <input type="text" name="employer_company_name"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Website URL:</label>
                        <input type="url" name="employer_website_url"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">First Name:</label>
                            <input type="text" name="user_first_name"
                                class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Last Name:</label>
                            <input type="text" name="user_last_name"
                                class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Email:</label>
                        <input type="email" name="user_email"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                            required>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Password:</label>

                        <input id="passwordInput" type="password" name="user_password"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A] pr-10"
                            required>

                        <button type="button" id="togglePassword"
                            class="absolute right-3 top-8 text-gray-400 hover:text-orange-500 focus:outline-none">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Hidden for now but required by backend -->
                    <!--<div class="hidden">
                        <input type="file" name="employer_company_id_picture">
                        <input type="file" name="employer_company_id_picture_selfie">
                        <input type="file" name="employer_id_picture">
                        <input type="file" name="employer_id_picture_selfie">
                    </div>-->

                    <div class="space-y-2 py-2">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" class="mt-1 accent-[#C73D1A]" required>
                            <span class="text-[11px] text-black">I agree to the <a href="#"
                                    class="text-orange-600 underline font-bold">Terms of Use</a> and <a href="#"
                                    class="text-orange-600 underline font-bold">Privacy Policy</a>.</span>
                        </label>
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" class="mt-1 accent-[#C73D1A]" required>
                            <span class="text-[11px] text-black">I confirm that I am an authorized representative of my
                                company and agree to handle all applicant data in compliance with the <a href="#"
                                    class="text-orange-600 underline font-bold">Data Privacy Act</a>.</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full bg-[#0E0F3B] text-[16px] text-white py-3 rounded-md font-[Montserrat] font-bold hover:bg-blue-900 transition-colors uppercase tracking-widest shadow-lg">
                        Sign Up
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600">
                    Already have an account? <br>
                    <a href="{{ route('auth.login') }}" class="text-orange-600 font-bold hover:underline">Log In</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    const passwordInput = document.getElementById('passwordInput');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = passwordInput.getAttribute('type') === 'user_password' ? 'text' : 'user_password';
        passwordInput.setAttribute('type', type);
        
        // Change the Icon (Optional: Swaps to "Eye with Slash" when visible)
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            `;
        } else {
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        }
    });
</script>

</body>

</html>