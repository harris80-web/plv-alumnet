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

    <!-- Error Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 w-[90%] max-w-sm pointer-events-none"></div>

    @if(auth()->user()->user_role === 'employer')
        @include('partials.header-employer')
    @else
        @include('partials.header-alumni')
    @endif

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

            @if (session('must_change_password_notice'))
            <div class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-4">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                <p class="text-sm">{{ session('must_change_password_notice') }}</p>
            </div>
            @endif

            @include('partials.success')
            <form class="space-y-6" method="POST" action="{{ route('users.changePassword') }}">
                @csrf
                @method('PUT')
                <div class="w-full">
                    <label class="block text-sm font-bold text-[#12123B] mb-2">Old Password:</label>
                    <div class="relative">
                        <input type="password" name="current_password" class="pw-input w-full p-3 border rounded focus:ring-2 focus:ring-[#C73D1A] outline-none transition shadow-inner {{ $errors->has('current_password') ? 'border-red-600' : 'border-orange-300' }}">
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
                        <input type="password" name="new_password" class="pw-input w-full p-3 border rounded focus:ring-2 focus:ring-[#C73D1A] outline-none transition shadow-inner {{ $errors->has('new_password') ? 'border-red-600' : 'border-orange-300' }}">
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
                        <input type="password" name="new_password_confirmation" class="pw-input w-full p-3 border rounded focus:ring-2 focus:ring-[#C73D1A] outline-none transition shadow-inner {{ $errors->has('new_password_confirmation') ? 'border-red-600' : 'border-orange-300' }}">
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

    @if(auth()->user()->user_role === 'employer')
        @include('partials.footer-employer')
    @else
        @include('partials.footer-alumni')
    @endif

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

        // Error toasts (red), populated from server-side validation errors
        function showToast(message) {
            const container = document.getElementById('toastContainer');

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto flex items-start gap-2 bg-red-50 text-red-700 border border-red-500 text-[12px] font-medium px-4 py-3 rounded-md shadow-lg opacity-0 -translate-y-2 transition-all duration-300 ease-out';

            const text = document.createElement('span');
            text.className = 'flex-1';
            text.textContent = message;

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'text-red-500 hover:text-red-700 leading-none text-lg';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => dismissToast(toast);

            toast.appendChild(text);
            toast.appendChild(closeBtn);
            container.appendChild(toast);

            toast.getBoundingClientRect();
            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', '-translate-y-2');
            });

            setTimeout(() => dismissToast(toast), 8000);
        }

        function dismissToast(toast) {
            if (!toast.isConnected) return;
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }

        @if ($errors->any())
            window.addEventListener('DOMContentLoaded', () => {
                const messages = @json($errors->all());
                messages.forEach(message => showToast(message));
            });
        @endif
    </script>
</body>

</html>