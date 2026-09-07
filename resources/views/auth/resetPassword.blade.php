<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Reset Password</title>
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
    <style>
        body {
            background-image: url("{{ asset('assets/loginBackground.svg') }}");
            background-size: cover;
            background-position: center;

            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen relative flex items-center justify-center">

    <!-- Error Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 w-[90%] max-w-sm pointer-events-none"></div>

    <div class="absolute inset-0 z-0">
        <img src="{{ asset('assets/alumnetBackground.svg') }}" alt="PLV Building" class="w-full h-full object-cover">
    </div>

    <div class="relative z-10 container mx-auto px-6 lg:px-5 flex flex-col lg:flex-row items-center justify-evenly">

        <div class="text-white max-w-lg m-7">
            <div class="flex items-center gap-1 mb-2">
                <div class="w-20 h-20 flex items-center justify-center">
                    <img src="{{ asset('assets/PLV-Logo-2.svg') }}" alt="" class="h-[90px] w-auto">
                </div>
                <div class="flex flex-col items-center h-auto w-auto">
                    <img src="{{ asset('assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png') }}" alt=""
                        class="h-auto w-auto ">
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
            <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md mb-4">

                <div class="text-center mb-6">
                    <span
                        class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Reset Password </span>
                    <span
                        class=" font-[Inter] text-sm text-[#b85c38] font-medium bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent"><br>
                        Create a new password below</span>
                </div>

                @include('partials.success')

                <form method="POST" action="{{ route('passReset.updatePassword') }}"
                    class="space-y-4 w-full max-w-md mx-auto h-auto">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Email:</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full px-4 py-1.5 border rounded focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                            required>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">New Password:</label>
                        <input id="passwordInput" type="password" name="user_password"
                            class="w-full px-4 py-1.5 border rounded focus:outline-none focus:ring-2 pr-10 {{ $errors->has('user_password') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                            required>
                        <button type="button" id="togglePassword"
                            class="absolute right-3 top-8 text-gray-400 hover:text-orange-500 focus:outline-none">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Confirm New Password:</label>
                        <input id="passwordConfirmInput" type="password" name="user_password_confirmation"
                            class="w-full px-4 py-1.5 border rounded focus:outline-none focus:ring-2 pr-10 {{ $errors->has('user_password_confirmation') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                            required>
                        <button type="button" id="togglePasswordConfirm"
                            class="absolute right-3 top-8 text-gray-400 hover:text-orange-500 focus:outline-none">
                            <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <button type="submit"
                        class="w-full bg-[#0E0F3B] text-white my-4 py-3 rounded-md font-[Montserrat] font-bold hover:bg-blue-900 transition-colors uppercase tracking-widest text-sm shadow-lg">
                        Reset Password
                    </button>
                </form>

                <div class="mt-2 text-center">
                    <a href="{{ route('auth.login') }}"
                        class="text-[#0E0F3B] font-semibold underline decoration-1 underline-offset-4 hover:text-orange-700 text-sm transition-colors">
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
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

            // Force a layout flush so the opacity-0/-translate-y-2 starting state is
            // actually painted before we transition away from it, otherwise the
            // browser can collapse both class changes into a single frame and the
            // toast just snaps in/out instead of animating.
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

        function setupPasswordToggle(inputId, buttonId, iconId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = document.getElementById(iconId);

            button.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);

                if (type === 'text') {
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    `;
                } else {
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    `;
                }
            });
        }

        setupPasswordToggle('passwordInput', 'togglePassword', 'eyeIcon');
        setupPasswordToggle('passwordConfirmInput', 'togglePasswordConfirm', 'eyeIconConfirm');
    </script>

</body>

</html>
