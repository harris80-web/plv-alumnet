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
            <div class="text-left mb-4 mt-5">
                <a href="{{ route('general.home') }}" class="text-white text-sm hover:text-[#0E0F3B]">↩ Return to
                    Home</a>
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


                @include('partials.success')

                <form action="{{ route('users.storeEmployer') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-4 w-full max-w-md mx-auto h-auto ">
                    @csrf
                    @if (session('success'))
                        <script>
                            window.addEventListener('DOMContentLoaded', () => showSuccessModal());
                        </script>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Business Name:</label>
                        <input type="text" name="employer_company_name"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                            required>
                        @if($errors->has('employer_company_name'))
                            <div class="alert alert-danger">
                                {{ $errors->first('employer_company_name') }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Website URL:</label>
                        <input type="url" name="employer_website_url"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
                        @if($errors->has('employer_website_url'))
                            <div class="alert alert-danger">
                                {{ $errors->first('employer_website_url') }}
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">First Name:</label>
                            <input type="text" name="user_first_name"
                                class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                                required>
                            @if($errors->has('user_first_name'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('user_first_name') }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Last Name:</label>
                            <input type="text" name="user_last_name"
                                class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                                required>
                            @if($errors->has('user_last_name'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('user_last_name') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Email:</label>
                        <input type="email" name="user_email"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A]"
                            required>
                            @if($errors->has('user_email'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('user_email') }}
                                </div>
                            @endif
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Password:</label>

                        <input id="passwordInput" type="password" name="user_password"
                            class="w-full px-4 py-0.5 border border-[#C73D1A] rounded focus:outline-none focus:ring-2 focus:ring-[#C73D1A] pr-10"
                            required>
                            @if($errors->has('user_password'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('user_password') }}
                                </div>
                            @endif

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
                            <input type="checkbox" id="terms-checkbox" class="mt-1 accent-[#C73D1A]" required>
                            <span class="text-[11px] text-black">I agree to the
                                <a href="#" onclick="openModal(event)" class="text-orange-600 underline font-bold">Terms
                                    of Use</a>
                                and
                                <a href="#" onclick="openModal(event)"
                                    class="text-orange-600 underline font-bold">Privacy Policy</a>.
                            </span>
                        </label>
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" id="rep-checkbox" class="mt-1 accent-[#C73D1A]" required>
                            <span class="text-[11px] text-black">I confirm that I am an authorized representative of my
                                company and agree to handle all applicant data in compliance with the
                                <a href="#" onclick="openPrivacyModal(event)"
                                    class="text-orange-600 underline font-bold">Data Privacy Act</a>.
                            </span>
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

    <!-- Terms & Privacy Modal -->
    <div id="termsModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden font-[Montserrat]">
        <div class="bg-white rounded-xl shadow-2xl w-[90%] max-w-lg overflow-hidden">
            <div class="bg-[#0E0F3B] px-4 py-2 flex items-center justify-between">
                <span class="text-white text-[10px] font-semibold uppercase tracking-widest">Terms of Use & Privacy
                    Policy</span>
                <button onclick="closeModal()"
                    class="text-white text-xl leading-none hover:text-gray-300 focus:outline-none">&times;</button>
            </div>
            <div class="px-6 py-5 overflow-y-auto max-h-[75vh] text-center">
                <div class="flex items-center justify-center gap-3 mb-1">
                    <div class="text-center">
                        <img src="{{ asset('assets/PLV-AlumNet LETTERMARK_COLORED 2.png') }}" alt=""
                            class="h-auto w-36 ml-9">
                        <p class="text-[9px] uppercase tracking-tight font-medium text-[#0E0F3B]">Pamantasan ng Lungsod
                            ng Valenzuela <br>Alumni Connect</p>
                    </div>
                </div>
                <h2 class="text-xl font-bold mt-3 mb-3">
                    <span
                        class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Terms
                        of Use</span>
                </h2>
                <p class="text-[11px] text-[#0E0F3B] leading-relaxed mb-6">
                    By using <strong>PLV-AlumNet</strong>, you agree to provide accurate information and use this
                    platform strictly for professional networking, job placement, and official university-related
                    activities. Any form of harassment, spamming, or posting of fraudulent content is prohibited and may
                    result in the immediate suspension of your account. We reserve the right to verify all user
                    identities and company details through the PLV Alumni Affairs Office to maintain a secure and
                    professional environment for the entire community.
                </p>
                <h2 class="text-xl font-bold mb-3">
                    <span
                        class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Privacy
                        Policy</span>
                </h2>
                <p class="text-[11px] text-[#0E0F3B] leading-relaxed mb-6">
                    In compliance with the <strong>Data Privacy Act of 2012 (RA 10173)</strong>, we are committed to
                    protecting your personal information. We collect and process your data: including your name,
                    identification details, and contact info, solely for account verification and platform security. We
                    implement strict technical measures to prevent unauthorized access and will never share your
                    information with third parties without your explicit consent, except as required by law. You
                    maintain the right to access, update, or request the permanent deletion of your records at any time.
                </p>
                <button onclick="agreeAndClose()"
                    class="bg-[#0E0F3B] text-white text-[13px] font-bold uppercase tracking-widest px-12 py-2.5 rounded-md hover:bg-blue-900 transition-colors">
                    I Agree
                </button>
            </div>
        </div>
    </div>

    <!-- Data Privacy Act Modal -->
    <div id="dataPrivacyModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden font-[Montserrat]">
        <div class="bg-white rounded-xl shadow-2xl w-[90%] max-w-lg overflow-hidden">
            <div class="bg-[#0E0F3B] px-4 py-2 flex items-center justify-between">
                <span class="text-white text-[10px] font-semibold uppercase tracking-widest">Data Privacy Act</span>
                <button onclick="closePrivacyModal()"
                    class="text-white text-xl leading-none hover:text-gray-300 focus:outline-none">&times;</button>
            </div>
            <div class="px-6 py-5 overflow-y-auto max-h-[75vh] text-center">
                <div class="flex items-center justify-center gap-3 mb-1">
                    <div class="text-center">
                        <img src="{{ asset('assets/PLV-AlumNet LETTERMARK_COLORED 2.png') }}" alt=""
                            class="h-auto w-36 ml-9">
                        <p class="text-[9px] uppercase tracking-tight font-medium text-[#0E0F3B]">Pamantasan ng Lungsod
                            ng Valenzuela <br>Alumni Connect</p>
                    </div>
                </div>
                <h2 class="text-xl font-bold mt-3 mb-3">
                    <span
                        class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Data
                        Privacy Act of 2012</span>
                </h2>
                <p class="text-[11px] font-medium text-[#0E0F3B] leading-relaxed mb-4">
                    In accordance with the Data Privacy Act of 2012 (RA 10173), <strong>Pamantasan ng Lungsod ng
                        Valenzuela (PLV)</strong> is committed to protecting the confidentiality and security of your
                    personal information.
                </p>
                <p class="text-[11px] font-medium text-[#0E0F3B] leading-relaxed mb-4">
                    By registering for <strong>PLV-AlumNet</strong>, you acknowledge and consent to the collection and
                    processing of your personal data, including your full name, student identification number, and
                    professional details, for the sole purpose of identity verification through the University
                    Registrar, and facilitating official alumni networking and job placement services.
                </p>
                <p class="text-[11px] font-medium text-[#0E0F3B] leading-relaxed mb-4">
                    We implement strict technical and organizational security measures to ensure your information is
                    protected against unauthorized access or misuse, and we guarantee that your data will not be shared
                    with third parties without your explicit consent, except as required by law.
                </p>
                <p class="text-[11px] font-medium text-[#0E0F3B] leading-relaxed mb-4">
                    As a data subject, you maintain the right to access, rectify, or request the deletion of your
                    records at any time by contacting the PLV Alumni Affairs Office.
                </p>
                <p class="text-[11px] font-medium text-[#0E0F3B] leading-relaxed mb-6">
                    By clicking <strong>"I Agree"</strong>, you confirm that you have read and understood these terms
                    and voluntarily authorize PLV-AlumNet to manage your information in accordance with these privacy
                    standards.
                </p>
                <button onclick="agreePrivacyAndClose()"
                    class="bg-[#0E0F3B] text-white text-[13px] font-bold uppercase tracking-widest px-12 py-2.5 rounded-md hover:bg-blue-900 transition-colors">
                    I Agree
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 hidden font-[Montserrat]">
        <div class="bg-white rounded-xl shadow-2xl w-[90%] max-w-md overflow-hidden transform transition-all relative">
            <button onclick="closeSuccessModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none z-10">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            <div class="p-8 text-center pt-12">
                <div class="flex justify-center mb-6">
                    <div class="bg-[#0E0F3B] w-20 h-20 rounded-full flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-bold mb-2">
                    <span
                        class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Account Pending<br>for Approval
                    </span>
                </h2>
                <p
                    class="text-sm leading-relaxed mb-8 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    Your account has been successfully registered and is currently awaiting <strong>administrative
                        review</strong>. Access will be granted once your account has been <strong>approved</strong>.
                </p>
                <a href="{{ route('general.home') }}"
                    class="block w-full bg-[#0E0F3B] text-white py-3 rounded-md font-bold uppercase tracking-widest hover:bg-blue-900 transition-colors shadow-lg text-center">
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('passwordInput');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
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

        // Terms & Privacy Modal
        function openModal(event) {
            event.preventDefault();
            document.getElementById('termsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('termsModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function agreeAndClose() {
            document.getElementById('terms-checkbox').checked = true;
            closeModal();
        }

        document.getElementById('termsModal').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        // Data Privacy Modal
        function openPrivacyModal(event) {
            event.preventDefault();
            document.getElementById('dataPrivacyModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePrivacyModal() {
            document.getElementById('dataPrivacyModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function agreePrivacyAndClose() {
            document.getElementById('rep-checkbox').checked = true;
            closePrivacyModal();
        }

        document.getElementById('dataPrivacyModal').addEventListener('click', function (e) {
            if (e.target === this) closePrivacyModal();
        });

        function showSuccessModal() {
            document.getElementById('successModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>
</body>

</html>