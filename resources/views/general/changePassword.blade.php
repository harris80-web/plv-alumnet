<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Change Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .HeroSection {
            background: url("{{ asset('assets/heroSectionBackground.png') }}");
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-white">

    @include('partials.header-employer')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">PLV-AlumNet</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl">
            <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-2">
                Change Password
            </h2>

            <p class="text-sm bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-center mb-10">
                Your new password must be different from previously used passwords.
            </p>

            @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form class="space-y-6" method="POST" action="{{ route('users.changePassword') }}">
                @csrf
                @method('PUT')
                <div class="w-full">
                    <label class="block text-sm font-bold text-[#12123B] mb-2">Old Password:</label>
                    <div class="relative">
                        <input type="password" name="current_password" class="pw-input w-full p-3 border border-orange-300 rounded focus:ring-2 focus:ring-[#C73D1A] outline-none transition shadow-inner">
                        <button type="button" class="toggle-pw absolute right-4 top-3.5 text-gray-400 hover:text-orange-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-sm font-bold text-[#12123B] mb-2">New Password:</label>
                    <div class="relative">
                        <input type="password" name="new_password" class="pw-input w-full p-3 border border-orange-300 rounded focus:ring-2 focus:ring-[#C73D1A] outline-none transition shadow-inner">
                        <button type="button" class="toggle-pw absolute right-4 top-3.5 text-gray-400 hover:text-orange-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-orange-600 mt-2">Minimum 8 characters, including a number and a symbol.</p>
                </div>

                <div class="w-full">
                    <label class="block text-sm font-bold text-[#12123B] mb-2">Confirm New Password:</label>
                    <div class="relative">
                        <input type="password" name="new_password_confirmation" class="pw-input w-full p-3 border border-orange-300 rounded focus:ring-2 focus:ring-[#C73D1A] outline-none transition shadow-inner">
                        <button type="button" class="toggle-pw absolute right-4 top-3.5 text-gray-400 hover:text-orange-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#0D0D2B] text-white py-4 rounded font-bold tracking-[0.2em] hover:bg-blue-900 transition duration-200 uppercase text-sm mt-4 shadow-md">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full p-8 relative text-center">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition">
                <i class="fa-solid fa-circle-xmark text-2xl"></i>
            </button>
            <div class="flex justify-center mb-6">
                <div class="bg-[#0D0D2B] w-20 h-20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-check text-white text-4xl"></i>
                </div>
            </div>
            <h3 class="text-3xl font-bold mb-4">
                <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Password Updated</span>
                <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Successfully</span>
            </h3>
            <p class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-sm mb-8 leading-relaxed">
                Your account security <span class="font-medium">has been updated</span>. Please use your new password the next time you log in.
            </p>
            <button onclick="closeModal()" class="w-full max-w-[150px] bg-[#0D0D2B] text-white py-3 rounded font-bold tracking-widest hover:bg-blue-900 transition duration-200 uppercase text-sm">
                DONE
            </button>
        </div>
    </div>

    @include('partials.footer-employer')

    <script>
        // Use querySelectorAll to handle all password fields on the page
        document.querySelectorAll('.toggle-pw').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('.pw-input');
                const eyeIcon = this.querySelector('.eye-icon');

                // Toggle the type attribute
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');

                // Update the SVG icon
                if (isPassword) {
                    // Eye Slash Icon
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    `;
                    this.classList.add('text-orange-500');
                } else {
                    // Normal Eye Icon
                    eyeIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                    this.classList.remove('text-orange-500');
                }
            });
        });

        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        @if(session('success'))
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('successModal').classList.remove('hidden');
        });
        @endif
    </script>
</body>

</html>