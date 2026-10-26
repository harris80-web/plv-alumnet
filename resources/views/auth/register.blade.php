<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Employer Sign Up</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <style>
        body {
            background-image: url("{{ asset('assets/signUpBackground.svg') }}");
            background-size: cover;
            background-position: center;

            font-family: 'Montserrat', sans-serif;
        }

        /* Hide the browser's built-in "reveal password" button (Edge) and autofill key
           button (Chromium) — the form has its own gray show/hide toggle, and the two
           icons were stacking on top of each other. */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
        input::-webkit-credentials-auto-fill-button {
            visibility: hidden;
            pointer-events: none;
            position: absolute;
            right: 0;
        }
    </style>
    {{-- Central responsive stylesheet + nav script (general/alumni/employer only). See RESPONSIVE-GUIDE.md. --}}
    @include('partials.responsive-assets', ['rpRole' => 'general'])
</head>

<body class="min-h-screen relative flex items-center justify-center">

    <!-- Error Toast Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] flex flex-col gap-2 w-[90%] max-w-sm pointer-events-none"></div>

    <div class="absolute inset-0 z-0">
        <img src="{{ asset('assets/signUpBackground.svg') }}" alt="PLV Building" class="w-full h-full object-cover">
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
                <a href="{{ route('general.home') }}" class="text-white text-sm px-3 py-1.5 rounded-full transition-colors hover:bg-white hover:text-[#0E0F3B]">↩ Return to
                    Home</a>
            </div>

            <div class="bg-white rounded-xl shadow-2xl p-8 w-full w-auto h-auto mb-4">


                <div class="text-center mb-6">
                    <span
                        class="inner-text-shadow text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Employer Sign Up </span>
                    <span
                        class=" font-[Inter] text-[#b85c38] font-medium bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent"><br>Let's
                        Get Started!</span>
                </div>


                {{-- Deferred: the toast waits until "Got it" dismisses the post-sign-up modal. --}}
                @include('partials.success', ['deferred' => true])

                @include('partials.error')
                <form action="{{ route('users.storeEmployer') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-4 w-full max-w-md mx-auto h-auto ">
                    @csrf
                    @if (session('success'))
                        <script>
                            window.addEventListener('DOMContentLoaded', () => showSuccessModal());
                        </script>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                        <p class="text-[11px] text-gray-500"><span class="text-red-600 font-bold">*</span> Required field</p>

                        {{-- Alumni help: tooltip is positioned by JS (fixed) so it never overflows the viewport. --}}
                        <div id="alumniHelp" class="relative">
                            <button type="button" id="alumniHelpTrigger" aria-describedby="alumniHelpTip" aria-expanded="false"
                                class="inline-flex items-center gap-1 text-[11px] text-gray-500 hover:text-[#C73D1A] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A] rounded transition-colors">
                                <i class="fas fa-circle-info text-[#1D46A4]" aria-hidden="true"></i>
                                <span class="underline decoration-dotted underline-offset-2">I'm an <strong>alumni</strong>, where do I sign up?</span>
                            </button>
                            <div id="alumniHelpTip" role="tooltip" hidden
                                class="fixed z-[200] opacity-0 transition-opacity duration-150 p-2 w-[min(18rem,calc(100vw-1.5rem))]">
                                <div class="bg-[#0E0F3B] text-white text-[11px] leading-relaxed py-2 px-3 rounded shadow-xl relative">
                                    <p>This page is for employers only. Alumni don't need to sign up, your account credentials have been sent to your email.</p>
                                    <a href="{{ route('auth.login') }}" class="inline-block mt-1.5 font-bold text-white underline hover:text-[#ED7A07] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#ED7A07] rounded">Go to Alumni Login</a>
                                    <span id="alumniHelpArrow" class="absolute w-0 h-0 border-[6px] border-transparent"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-wider border-b border-gray-200 pb-1">
                            Business Details</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="employer_company_name" class="block text-sm font-semibold text-[#0E0F3B] mb-1">Business Name: <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input type="text" id="employer_company_name" name="employer_company_name"
                                class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 {{ $errors->has('employer_company_name') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                                required aria-required="true">
                        </div>
                        <div>
                            <label for="employer_website_url" class="block text-sm font-semibold text-[#0E0F3B] mb-1">Website URL: <span class="text-[10px] font-normal text-gray-400">(Optional)</span></label>
                            <input type="url" id="employer_website_url" name="employer_website_url"
                                class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 {{ $errors->has('employer_website_url') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#0E0F3B] mb-1">Business Verification
                            Document <span class="text-red-600" aria-hidden="true">*</span></label>
                        <p class="text-[10px] text-gray-500 mb-2">Upload a document proving your company's legitimacy
                            <br>(eg. Business Permit, SEC/DTI Registration, BIR Certificate)</p>

                        <div id="documentDropzone" role="button" tabindex="0" aria-describedby="documentInfo documentError"
                            class="w-full border border-dashed rounded-lg bg-slate-50 hover:bg-slate-100 transition cursor-pointer flex items-center justify-center text-center px-4 py-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A] {{ $errors->has('employer_company_document') ? 'border-red-600' : 'border-[#C73D1A]' }}"
                            onclick="document.getElementById('employer_company_document').click()">
                            <span id="documentDropzoneText" class="text-[11px] text-gray-400">Choose file or drag &amp;
                                drop here</span>
                        </div>
                        <input type="file" id="employer_company_document" name="employer_company_document"
                            accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" class="hidden" aria-required="true">

                        <p id="documentError" role="alert" class="hidden text-[11px] text-red-600 mt-1.5"></p>

                        {{-- Limits mirror UserController::storeEmployer(): mimes:pdf,jpg,jpeg,png,webp, max:20000 (KB). --}}
                        <div id="documentInfo" class="mt-2 space-y-1">
                            <p class="flex items-start gap-1.5 text-[10px] text-gray-500">
                                <i class="fas fa-circle-info text-[#1D46A4] mt-px shrink-0" aria-hidden="true"></i>
                                <span>Accepted formats: PDF, JPG, PNG, WEBP. Maximum file size: 20 MB.</span>
                            </p>
                            <p class="flex items-start gap-1.5 text-[10px] text-gray-500">
                                <i class="fas fa-shield-halved text-[#1D46A4] mt-px shrink-0" aria-hidden="true"></i>
                                <span>We ask for this to confirm your business is legitimate and to protect our alumni and students. Only our admin team will review it and is never shown publicly.</span>
                            </p>
                        </div>
                    </div>

                    <div class="pt-1">
                        <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-wider border-b border-gray-200 pb-1">
                            Employer Details</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="user_first_name" class="block text-sm font-semibold text-[#0E0F3B] mb-1">First Name: <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input type="text" id="user_first_name" name="user_first_name"
                                class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 {{ $errors->has('user_first_name') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                                required aria-required="true">
                        </div>
                        <div>
                            <label for="user_last_name" class="block text-sm font-semibold text-[#0E0F3B] mb-1">Last Name: <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input type="text" id="user_last_name" name="user_last_name"
                                class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 {{ $errors->has('user_last_name') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                                required aria-required="true">
                        </div>
                    </div>

                    <div>
                        <label for="user_email" class="block text-sm font-semibold text-[#0E0F3B] mb-1">Email: <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input type="email" id="user_email" name="user_email"
                            class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 {{ $errors->has('user_email') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                            required aria-required="true">
                    </div>

                    <div class="relative">
                        <label for="passwordInput" class="block text-sm font-semibold text-[#0E0F3B] mb-1">Password: <span class="text-red-600" aria-hidden="true">*</span></label>

                        <input id="passwordInput" type="password" name="user_password"
                            class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 pr-10 {{ $errors->has('user_password') ? 'border-red-600 focus:ring-red-600' : 'border-[#C73D1A] focus:ring-[#C73D1A]' }}"
                            required aria-required="true">

                        <button type="button" id="togglePassword" aria-label="Show password" data-toggle-for="passwordInput"
                            class="absolute right-3 top-8 text-gray-400 hover:text-orange-500 focus:outline-none focus-visible:text-orange-500">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <div class="relative">
                        <label for="confirmPasswordInput" class="block text-sm font-semibold text-[#0E0F3B] mb-1">Confirm Password: <span class="text-red-600" aria-hidden="true">*</span></label>

                        <input id="confirmPasswordInput" type="password" name="user_password_confirmation"
                            class="w-full px-4 py-0.5 border rounded focus:outline-none focus:ring-2 pr-10 border-[#C73D1A] focus:ring-[#C73D1A]"
                            required aria-required="true" aria-describedby="confirmPasswordError confirmPasswordSuccess">

                        <button type="button" id="toggleConfirmPassword" aria-label="Show password" data-toggle-for="confirmPasswordInput"
                            class="absolute right-3 top-8 text-gray-400 hover:text-orange-500 focus:outline-none focus-visible:text-orange-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>

                        <p id="confirmPasswordError" role="alert" class="hidden text-[11px] text-red-600 mt-1">Passwords do not match.</p>
                        <p id="confirmPasswordSuccess" role="status" class="hidden items-center gap-1 text-[11px] text-green-600 mt-1">
                            <i class="fas fa-circle-check" aria-hidden="true"></i> Passwords match.
                        </p>
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
                            <input type="checkbox" id="terms-checkbox" class="mt-1 accent-[#C73D1A]" required aria-required="true">
                            <span class="text-[11px] text-black">I agree to the
                                <a href="#" onclick="openModal(event)"
                                    class="text-orange-600 underline font-bold">Terms of Use</a>
                                and
                                <a href="#" onclick="openModal(event)"
                                    class="text-orange-600 underline font-bold">Privacy Policy</a>,
                                and I consent to my information being used to verify my account and run the platform.
                                <span class="text-red-600" aria-hidden="true">*</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" id="rep-checkbox" class="mt-1 accent-[#C73D1A]" required aria-required="true">
                            <span class="text-[11px] text-black">I'm an authorized representative of my company, and I'll
                                handle applicants' data in line with the
                                <a href="#" onclick="openPrivacyModal(event)"
                                    class="text-orange-600 underline font-bold">Data Privacy Act</a>.
                                <span class="text-red-600" aria-hidden="true">*</span>
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden opacity-0 transition-opacity duration-200 font-[Montserrat]">
        <div id="termsModalPanel" class="bg-white rounded-xl shadow-2xl w-[90%] max-w-lg overflow-hidden opacity-0 scale-95 transition-all duration-200">
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden opacity-0 transition-opacity duration-200 font-[Montserrat]">
        <div id="dataPrivacyModalPanel" class="bg-white rounded-xl shadow-2xl w-[90%] max-w-lg overflow-hidden opacity-0 scale-95 transition-all duration-200">
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
    {{-- style: responsive-public.css sets `overscroll-behavior: contain` on this overlay at <=1023px,
         which stops scrolling on the backdrop from reaching the page; this modal should let it through. --}}
    <div id="successModal" style="overscroll-behavior: auto;"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 hidden opacity-0 transition-opacity duration-200 font-[Montserrat]">
        <div id="successModalPanel" role="dialog" aria-modal="true" aria-labelledby="successModalTitle" aria-describedby="successModalBody"
            class="bg-white rounded-xl shadow-2xl w-[90%] max-w-md max-h-[90vh] overflow-y-auto transform transition-all duration-200 opacity-0 scale-95 relative">
            <button onclick="closeSuccessModal()" aria-label="Close and return to home"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A] rounded z-10">
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
                <h2 id="successModalTitle" class="text-2xl font-bold mb-2">
                    <span
                        class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        Thank you<br>for signing up!
                    </span>
                </h2>
                <p id="successModalBody"
                    class="text-sm leading-relaxed mb-8 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    Your account is now <strong>pending review</strong>.<br> Our team will verify your business information
                    and documents. You'll receive an email once your account is <strong>approved</strong>, which may
                    take {{ config('app.employer_review_timeframe') }}.
                </p>
                <a id="successModalConfirm" href="{{ route('general.home') }}" onclick="event.preventDefault(); closeSuccessModal()"
                    class="block w-full bg-[#0E0F3B] text-white py-3 rounded-md font-bold uppercase tracking-widest hover:bg-blue-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A] focus-visible:ring-offset-2 transition-colors shadow-lg text-center">
                    Got it
                </a>
            </div>
        </div>
    </div>

    @include('partials.ui-animations')
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

        // Business Verification Document dropzone
        const documentDropzone = document.getElementById('documentDropzone');
        const documentInput = document.getElementById('employer_company_document');
        const documentDropzoneText = document.getElementById('documentDropzoneText');

        function updateDocumentDropzoneText() {
            if (documentInput.files.length > 0) {
                documentDropzoneText.textContent = documentInput.files[0].name;
                documentDropzoneText.classList.remove('text-gray-400');
                documentDropzoneText.classList.add('text-[#0E0F3B]', 'font-semibold');
            } else {
                documentDropzoneText.textContent = 'Choose file or drag & drop here';
                documentDropzoneText.classList.add('text-gray-400');
                documentDropzoneText.classList.remove('text-[#0E0F3B]', 'font-semibold');
            }
        }

        // Mirrors UserController::storeEmployer(): `mimes:pdf|max:20000` (KB).
        const DOCUMENT_MAX_BYTES = 20000 * 1024;
        const documentError = document.getElementById('documentError');

        function setDocumentError(message) {
            documentError.textContent = message || '';
            documentError.classList.toggle('hidden', !message);
            documentDropzone.classList.toggle('border-red-600', !!message);
            documentDropzone.classList.toggle('border-[#C73D1A]', !message);
            documentDropzone.setAttribute('aria-invalid', message ? 'true' : 'false');
        }

        // Returns an error message, or '' when the file is acceptable.
        function documentProblem(file) {
            // Extension is the primary check (file.type can be empty on some systems); MIME is a fallback.
            const allowedExt = /\.(pdf|jpe?g|png|webp)$/i.test(file.name);
            const allowedMime = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'].includes(file.type);
            if (!allowedExt && !allowedMime) return 'Invalid file type. Please upload a PDF, JPG, PNG, or WEBP file.';
            if (file.size > DOCUMENT_MAX_BYTES) return 'File is too large. The maximum size is 20 MB.';
            return '';
        }

        // Validates whatever is currently in the input; clears it if it is unusable.
        function checkDocumentInput() {
            if (documentInput.files.length === 0) {
                updateDocumentDropzoneText();
                return false;
            }
            const problem = documentProblem(documentInput.files[0]);
            if (problem) {
                documentInput.value = '';
                updateDocumentDropzoneText();
                setDocumentError(problem);
                return false;
            }
            setDocumentError('');
            updateDocumentDropzoneText();
            return true;
        }

        documentInput.addEventListener('change', checkDocumentInput);

        documentDropzone.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                documentInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            documentDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                documentDropzone.classList.add('bg-slate-100');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            documentDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                documentDropzone.classList.remove('bg-slate-100');
            });
        });

        documentDropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length > 0) {
                // The input is single-file; keep only the first dropped file.
                const dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                documentInput.files = dt.files;
                checkDocumentInput();
            }
        });

        // Show/hide password toggles (Password + Confirm Password)
        const EYE_OPEN = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        const EYE_SLASH = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            `;

        document.querySelectorAll('[data-toggle-for]').forEach(function (toggleBtn) {
            const input = document.getElementById(toggleBtn.dataset.toggleFor);
            const icon = toggleBtn.querySelector('svg');

            toggleBtn.addEventListener('click', function () {
                const show = input.getAttribute('type') === 'password';
                input.setAttribute('type', show ? 'text' : 'password');
                icon.innerHTML = show ? EYE_SLASH : EYE_OPEN;
                toggleBtn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });

        // Confirm Password: must match Password (checked on blur, while typing once touched, and on submit)
        const passwordInput = document.getElementById('passwordInput');
        const confirmInput = document.getElementById('confirmPasswordInput');
        const confirmError = document.getElementById('confirmPasswordError');
        let confirmTouched = false;

        const confirmSuccess = document.getElementById('confirmPasswordSuccess');

        // The "match" message shows live as soon as the values agree; the "do not match"
        // error waits until the field has been blurred/submitted so it doesn't nag mid-typing.
        function validateConfirmPassword() {
            const filled = confirmInput.value !== '';
            const mismatch = filled && confirmInput.value !== passwordInput.value;
            const match = filled && !mismatch;
            const showError = mismatch && confirmTouched;

            confirmError.classList.toggle('hidden', !showError);
            confirmSuccess.classList.toggle('hidden', !match);
            confirmSuccess.classList.toggle('flex', match);
            confirmInput.setAttribute('aria-invalid', showError ? 'true' : 'false');
            confirmInput.classList.toggle('border-red-600', showError);
            confirmInput.classList.toggle('focus:ring-red-600', showError);
            confirmInput.classList.toggle('border-[#C73D1A]', !showError);
            confirmInput.classList.toggle('focus:ring-[#C73D1A]', !showError);
            return !mismatch;
        }

        confirmInput.addEventListener('blur', function () {
            confirmTouched = true;
            validateConfirmPassword();
        });
        confirmInput.addEventListener('input', validateConfirmPassword);
        passwordInput.addEventListener('input', validateConfirmPassword);

        // Native `required` checks run first; this blocks what they can't see
        // (the hidden file input and the password match).
        document.querySelector('form[action="{{ route('users.storeEmployer') }}"]').addEventListener('submit', function (e) {
            confirmTouched = true;
            const passwordsMatch = validateConfirmPassword();
            const hasDocument = checkDocumentInput();

            if (!hasDocument && !documentError.textContent) {
                setDocumentError('Please upload your business verification document.');
            }
            if (!passwordsMatch || !hasDocument) {
                e.preventDefault();
                (!hasDocument ? documentDropzone : confirmInput).focus();
            }
        });

        // Terms & Privacy Modal
        function openModal(event) {
            event.preventDefault();
            openAnimatedModal(document.getElementById('termsModal'), document.getElementById('termsModalPanel'));
        }

        function closeModal() {
            closeAnimatedModal(document.getElementById('termsModal'), document.getElementById('termsModalPanel'));
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
            openAnimatedModal(document.getElementById('dataPrivacyModal'), document.getElementById('dataPrivacyModalPanel'));
        }

        function closePrivacyModal() {
            closeAnimatedModal(document.getElementById('dataPrivacyModal'), document.getElementById('dataPrivacyModalPanel'));
        }

        function agreePrivacyAndClose() {
            document.getElementById('rep-checkbox').checked = true;
            closePrivacyModal();
        }

        document.getElementById('dataPrivacyModal').addEventListener('click', function (e) {
            if (e.target === this) closePrivacyModal();
        });

        // Post-sign-up modal. Every way of dismissing it (Got it, X, ESC, backdrop) ends on
        // the home page: the account can't log in until an admin approves it, so the login
        // page would just bounce the employer back.
        const successModal = document.getElementById('successModal');
        const successModalPanel = document.getElementById('successModalPanel');
        const successHomeUrl = document.getElementById('successModalConfirm').href;

        function showSuccessModal() {
            openAnimatedModal(successModal, successModalPanel);
            // openAnimatedModal locks body scroll; this modal is informational, so keep the
            // page behind it scrollable (wheel/touch on the backdrop scrolls the document).
            document.body.style.overflow = '';
            document.getElementById('successModalConfirm').focus({ preventScroll: true });
        }

        // Flow: modal first -> (Got it / X / ESC / backdrop) -> modal closes -> success toast
        // appears -> a moment later the user lands on the home page.
        const TOAST_READ_MS = 2500;
        let successClosing = false;

        function closeSuccessModal() {
            if (successClosing) return;
            successClosing = true;
            closeAnimatedModal(successModal, successModalPanel, null, function () {
                if (window.showSuccessToast) window.showSuccessToast();
                setTimeout(function () { window.location.href = successHomeUrl; }, TOAST_READ_MS);
            });
        }

        successModal.addEventListener('click', function (e) {
            if (e.target === this) closeSuccessModal();
        });

        document.addEventListener('keydown', function (e) {
            if (successModal.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                closeSuccessModal();
                return;
            }

            // Focus trap: keep Tab/Shift+Tab inside the dialog.
            if (e.key === 'Tab') {
                const focusable = successModalPanel.querySelectorAll('a[href], button:not([disabled])');
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        // "I'm an alumni, where do I sign up?" tooltip.
        // Hover (mouse), keyboard focus, and tap (touch/pen) all open it; ESC, outside tap,
        // or leaving/blurring closes it. Positioned with `position: fixed` and clamped to the
        // viewport so it can flip above/below and never overflows at ~375px.
        (function () {
            const wrap = document.getElementById('alumniHelp');
            const trigger = document.getElementById('alumniHelpTrigger');
            const tip = document.getElementById('alumniHelpTip');
            const arrow = document.getElementById('alumniHelpArrow');
            const MARGIN = 12;       // min distance from the viewport edge
            const CLOSE_DELAY = 200; // lets the pointer travel from the trigger into the tooltip
            let closeTimer = null;
            let hideTimer = null;

            function position() {
                const t = trigger.getBoundingClientRect();
                const tipW = tip.offsetWidth;
                const tipH = tip.offsetHeight;
                const vw = document.documentElement.clientWidth;
                const vh = window.innerHeight;

                // Prefer below the trigger (so it sits over the form, not the header); flip above if it won't fit.
                const spaceBelow = vh - t.bottom;
                const placeBelow = spaceBelow >= tipH || spaceBelow >= t.top;
                const top = placeBelow ? t.bottom : t.top - tipH;
                const left = Math.min(Math.max(t.left + t.width / 2 - tipW / 2, MARGIN), vw - tipW - MARGIN);

                tip.style.top = Math.max(top, 0) + 'px';
                tip.style.left = left + 'px';

                // Point the arrow at the trigger's centre. The visible box sits inside `tip`'s 8px
                // padding (which doubles as the hover bridge), and the arrow is 12px wide.
                const boxW = tipW - 16;
                const arrowLeft = t.left + t.width / 2 - (left + 8) - 6;
                arrow.style.left = Math.min(Math.max(arrowLeft, 8), boxW - 20) + 'px';
                // Inline colours: Tailwind's `border-transparent` would otherwise win the cascade.
                arrow.classList.toggle('bottom-full', placeBelow);
                arrow.classList.toggle('top-full', !placeBelow);
                arrow.style.borderBottomColor = placeBelow ? '#1f2937' : 'transparent'; // gray-800, same as the bg
                arrow.style.borderTopColor = placeBelow ? 'transparent' : '#1f2937';
            }

            function open() {
                clearTimeout(closeTimer);
                clearTimeout(hideTimer);
                if (!tip.hidden && tip.classList.contains('opacity-100')) return position();
                tip.hidden = false;
                position();
                trigger.setAttribute('aria-expanded', 'true');
                // Force a reflow so the opacity transition actually plays.
                tip.getBoundingClientRect();
                tip.classList.remove('opacity-0');
                tip.classList.add('opacity-100');
            }

            function close() {
                clearTimeout(closeTimer);
                trigger.setAttribute('aria-expanded', 'false');
                tip.classList.remove('opacity-100');
                tip.classList.add('opacity-0');
                hideTimer = setTimeout(function () { tip.hidden = true; }, 150); // matches duration-150
            }

            function closeSoon() {
                clearTimeout(closeTimer);
                closeTimer = setTimeout(close, CLOSE_DELAY);
            }

            // Mouse hover over either the trigger or the tooltip keeps it open.
            wrap.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') open(); });
            wrap.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse') closeSoon(); });

            // Tap / click / Enter / Space on the trigger. A mouse click leaves it open (hover already did).
            let lastPointerType = '';
            trigger.addEventListener('pointerdown', function (e) { lastPointerType = e.pointerType; });
            trigger.addEventListener('click', function () {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';
                const viaMouse = lastPointerType === 'mouse';
                lastPointerType = ''; // a later keyboard "click" has no pointerdown, so it must toggle
                if (viaMouse || !isOpen) open(); else close();
            });

            // Keyboard focus only (not the focus a tap or click also causes).
            let ignoreFocus = false;
            wrap.addEventListener('focusin', function () {
                if (ignoreFocus) return;
                if (trigger.matches(':focus-visible') || tip.contains(document.activeElement)) open();
            });
            wrap.addEventListener('focusout', function (e) {
                if (!wrap.contains(e.relatedTarget)) closeSoon();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') {
                    close();
                    if (wrap.contains(document.activeElement)) {
                        // Hand focus back to the trigger without the focus handler re-opening the tip.
                        ignoreFocus = true;
                        trigger.focus();
                        ignoreFocus = false;
                    }
                }
            });

            // Tap outside closes it (touch has no hover-out).
            document.addEventListener('pointerdown', function (e) {
                if (!wrap.contains(e.target)) close();
            });

            window.addEventListener('resize', function () { if (!tip.hidden) position(); });
            window.addEventListener('scroll', function () { if (!tip.hidden) position(); }, { passive: true });
        })();
    </script>
</body>

</html>