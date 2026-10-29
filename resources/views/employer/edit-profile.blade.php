<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Edit Company Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

    /* Same company card banner as the Company Profile page. */
    .company-banner {
        background:
            url("{{ asset('assets/heroSection.svg') }}") center / cover;
    }

    /* One look for every field on this form (same navy border / orange focus as the job post forms). */
    .profile-field {
        width: 100%;
        padding: 0.55rem 0.85rem;
        background: #ffffff;
        border: 1px solid #0E0F3B;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #0E0F3B;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .profile-field::placeholder { color: #94a3b8; font-weight: 400; }
    .profile-field:focus { border-color: #C73D1A; box-shadow: 0 0 0 3px rgba(199, 61, 26, 0.12); }
    /* A select still showing its placeholder ("Select") reads like an empty field. */
    .profile-field.is-placeholder { color: #94a3b8; font-weight: 400; }
    .profile-label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #C73D1A;
    }
    /* Required / optional markers next to the labels. */
    .req-mark { margin-left: 2px; color: #C73D1A; }
    .opt-tag {
        display: inline-block;
        margin-left: 0.4rem;
        padding: 1px 6px;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
        vertical-align: 1px;
    }
</style>

@php
    $employer = $user->employer;
@endphp

<body class="min-h-screen flex flex-col bg-gray-50">
    @php $current_page = 'employer_edit'; @endphp
    @include('partials.header-employer')

    {{-- Hero banner hidden on this page (the page title + company card replace it).
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>
    --}}

    <main class="w-full max-w-6xl mx-auto px-6 py-8 flex-1 font-['Montserrat']">
        @include('partials.error-toast')
        @include('partials.success')

        @include('partials.error')

        <form id="employerProfileForm" action="{{ route('employers.updateProfile', $user->user_id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Title row: same layout as the Company Profile page, with Cancel / Save on the right.
                 Phones: the buttons drop under the subtitle and share the row. --}}
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mb-5 sm:mb-6">
                <h1 class="inline-block text-xl sm:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    EDIT EMPLOYER PROFILE
                </h1>
                <div class="order-3 sm:order-2 w-full sm:w-auto flex items-center gap-2 sm:gap-3">
                    {{-- An <a>, not a <button>: a plain button inside this form would submit it. --}}
                    <a href="{{ route('user.profile') }}"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg border border-[#0E0F3B] text-[#0E0F3B] font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest hover:bg-[#0E0F3B] hover:text-white transition-colors whitespace-nowrap">
                        CANCEL
                    </a>
                    <button type="submit"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-floppy-disk"></i> SAVE CHANGES
                    </button>
                </div>
                <p class="order-2 sm:order-3 w-full -mt-2 text-xs sm:text-sm text-slate-500">Update your company and contact details. Alumni see these on your job posts. <span class="whitespace-nowrap">Fields marked <span class="font-bold text-[#C73D1A]">*</span> are required.</span></p>
            </div>

            {{-- ===== Company card (logo upload) ===== --}}
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" aria-label="Company logo">
                <div class="company-banner h-24 sm:h-28"></div>
                <div class="px-5 sm:px-8 pb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                        <div class="relative -mt-12 w-24 h-24 sm:w-28 sm:h-28 shrink-0">
                            <button type="button" onclick="document.getElementById('companyLogoInput').click()" title="Change company logo"
                                class="w-full h-full rounded-2xl bg-white border-4 border-white shadow-md overflow-hidden flex items-center justify-center group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C73D1A]">
                                <img id="companyLogoPreview"
                                    src="{{ $employer?->employer_company_logo ? asset('storage/' . $employer->employer_company_logo) : '' }}"
                                    alt="Business Logo"
                                    class="w-full h-full object-contain bg-white {{ $employer?->employer_company_logo ? '' : 'hidden' }}"
                                    style="{{ $employer?->employer_company_logo ? '' : 'display:none' }}">
                                <span id="companyLogoPlaceholder"
                                    class="w-full h-full rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 group-hover:bg-slate-100 flex flex-col items-center justify-center text-[#1D264F] transition {{ $employer?->employer_company_logo ? 'hidden' : '' }}"
                                    style="{{ $employer?->employer_company_logo ? 'display:none' : '' }}">
                                    <i class="fa-solid fa-plus text-lg"></i>
                                    <span class="text-[9px] font-bold uppercase mt-1">Add Logo</span>
                                </span>
                            </button>
                            <span class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-[#1D264F] text-white border-2 border-white shadow flex items-center justify-center text-xs pointer-events-none">
                                <i class="fa-solid fa-camera"></i>
                            </span>
                            <input type="file" name="employer_company_logo" id="companyLogoInput" accept="image/*" class="hidden"
                                onchange="previewImageInput(this, 'companyLogoPreview', 'companyLogoPlaceholder'); document.getElementById('companyLogoViewBtn').classList.remove('hidden');">
                        </div>

                        <div class="flex-1 min-w-0 sm:pt-4">
                            <h2 id="companyNamePreview" class="text-xl sm:text-2xl font-bold text-[#0E0F3B] break-words">
                                {{ $employer?->employer_company_name ?: 'Your company name' }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500"><span class="font-semibold text-[#1D264F]">Company logo</span><span class="opt-tag">Optional</span><span class="block sm:inline sm:ml-1">PNG, JPG, GIF or SVG, up to 10 MB. Click the logo to change it.</span></p>
                            <button type="button" id="companyLogoViewBtn" onclick="openImageLightbox(document.getElementById('companyLogoPreview').src)"
                                class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-[#1D46A4] hover:underline {{ $employer?->employer_company_logo ? '' : 'hidden' }}">
                                <i class="fa-solid fa-eye"></i> View current logo
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- ===== Left: business details + addresses ===== --}}
                <div class="lg:col-span-2 space-y-6">
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-building"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Business Details</h3>
                                <p class="text-xs text-slate-500">Registered information about your company.</p>
                            </div>
                        </header>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 p-6">
                            <div class="sm:col-span-2">
                                <label for="employer_company_name" class="profile-label">Business Name<span class="req-mark" aria-hidden="true">*</span></label>
                                <input type="text" id="employer_company_name" name="employer_company_name" required value="{{ old('employer_company_name', $employer?->employer_company_name) }}"
                                    placeholder="Enter registered business name" class="profile-field"
                                    oninput="document.getElementById('companyNamePreview').textContent = this.value.trim() || 'Your company name'">
                            </div>
                            <div>
                                <label for="industry_id" class="profile-label">Industry / Sector<span class="opt-tag">Optional</span></label>
                                @php $currentIndustry = old('industry_id', $employer?->industry_id); @endphp
                                <select id="industry_id" name="industry_id" class="profile-field">
                                    <option value="">Select industry</option>
                                    @foreach ($industries as $industry)
                                    <option value="{{ $industry->industry_id }}" @selected($currentIndustry == $industry->industry_id)>{{ $industry->industry_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="employer_company_size" class="profile-label">Company / Business Size<span class="opt-tag">Optional</span></label>
                                <input type="number" id="employer_company_size" name="employer_company_size" min="1" step="1" inputmode="numeric"
                                    value="{{ old('employer_company_size', $employer?->employer_company_size) }}" placeholder="Number of employees, e.g. 50" class="profile-field">
                            </div>
                            <div>
                                <label for="employer_year_established" class="profile-label">Year Established<span class="opt-tag">Optional</span></label>
                                <input type="text" id="employer_year_established" name="employer_year_established" inputmode="numeric" maxlength="4"
                                    value="{{ old('employer_year_established', $employer?->employer_year_established) }}" placeholder="YYYY" class="profile-field">
                            </div>
                            <div>
                                <label for="employer_website_url" class="profile-label">Official Website<span class="opt-tag">Optional</span></label>
                                <input type="url" id="employer_website_url" name="employer_website_url"
                                    value="{{ old('employer_website_url', $employer?->employer_website_url) }}" placeholder="https://www.yourcompany.com" class="profile-field">
                            </div>
                        </div>
                    </section>

                    {{-- Company Addresses — as many as the employer wants (head office,
                         branches, etc.); picked from a dropdown when posting a job
                         instead of retyping the address every time. --}}
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Company Addresses<span class="opt-tag">Optional</span></h3>
                                <p class="text-xs text-slate-500">Office and branch locations you can pick from when posting a job.</p>
                            </div>
                        </header>
                        <div class="p-6">
                            {{-- Always present (even when every real row is removed) so a
                                 "delete all addresses" save actually persists as empty,
                                 instead of the field being missing from the request entirely. --}}
                            <input type="hidden" name="addresses[]" value="">
                            <div id="companyAddressList" class="space-y-2">
                                @foreach (old('addresses', $employer?->addresses?->pluck('address')->all() ?? []) as $address)
                                @continue($address === null || $address === '')
                                <div class="flex items-center gap-2 company-address-row">
                                    <span class="w-8 h-8 shrink-0 rounded-full bg-orange-50 text-[#C73D1A] flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                                    <input type="text" name="addresses[]" value="{{ $address }}" placeholder="e.g. 123 Rizal St., Valenzuela City" aria-label="Company address" class="profile-field">
                                    <button type="button" onclick="this.closest('.company-address-row').remove()" title="Remove this address" aria-label="Remove this address"
                                        class="shrink-0 w-9 h-9 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition flex items-center justify-center">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" onclick="addCompanyAddressRow()"
                                class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#1D264F] border border-[#1D264F] rounded-lg px-4 py-2 hover:bg-[#1D264F] hover:text-white transition">
                                <i class="fa-solid fa-plus"></i> Add Address
                            </button>
                        </div>
                    </section>
                </div>

                {{-- ===== Right: contact person ===== --}}
                <div class="space-y-6">
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-user-tie"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Contact Person</h3>
                                <p class="text-xs text-slate-500">Who represents your company.</p>
                            </div>
                        </header>
                        <div class="p-6 space-y-5">
                            <div class="flex items-center gap-4">
                                <div class="relative shrink-0">
                                    <div class="w-16 h-16 rounded-full bg-[#1D264F] flex items-center justify-center overflow-hidden">
                                        <img id="profileImagePreview"
                                            src="{{ $user->user_profile_picture ? asset('storage/' . $user->user_profile_picture) : '' }}"
                                            alt="Profile Picture"
                                            class="w-full h-full object-cover {{ $user->user_profile_picture ? '' : 'hidden' }}"
                                            style="{{ $user->user_profile_picture ? '' : 'display:none' }}">
                                        <i id="profileImagePlaceholderIcon" class="fa-solid fa-user text-2xl text-white {{ $user->user_profile_picture ? 'hidden' : '' }}"
                                            style="{{ $user->user_profile_picture ? 'display:none' : '' }}"></i>
                                    </div>
                                    <button type="button" onclick="togglePhotoOptions(event)" title="Change profile photo" aria-label="Change profile photo"
                                        class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-[#C73D1A] text-white border-2 border-white shadow flex items-center justify-center text-[10px] hover:bg-[#a83215] transition">
                                        <i class="fa-solid fa-camera"></i>
                                    </button>

                                    <div id="photoOptions" class="hidden absolute top-full mt-2 left-0 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                        <button type="button" onclick="openImageLightbox(document.getElementById('profileImagePreview').src)" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-50 flex items-center gap-3 transition">
                                            <i class="fa-solid fa-image text-sm"></i> View Profile Image
                                        </button>
                                        <label for="imageUpload" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-50 flex items-center gap-3 cursor-pointer mb-0 transition">
                                            <i class="fa-solid fa-upload text-sm"></i> Upload an Image
                                            <input type="file" name="user_profile_picture" id="imageUpload" class="hidden" accept="image/*"
                                                onchange="previewImageInput(this, 'profileImagePreview', 'profileImagePlaceholderIcon')">
                                        </label>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[#0E0F3B]">Profile Photo<span class="opt-tag">Optional</span></p>
                                    <p class="text-xs text-slate-500">PNG or JPG, up to 10 MB.</p>
                                </div>
                            </div>

                            {{-- Desktop: this card is a narrow sidebar, so First / Middle Name take a full row
                                 (keeps "Middle Name  Optional" on one line); Last Name + Suffix still share one. --}}
                            <div class="grid grid-cols-2 gap-3 items-end">
                                <div class="lg:col-span-2">
                                    <label for="user_first_name" class="profile-label">First Name<span class="req-mark" aria-hidden="true">*</span></label>
                                    <input type="text" id="user_first_name" name="user_first_name" required value="{{ old('user_first_name', $user->user_first_name) }}" placeholder="First name" class="profile-field">
                                </div>
                                <div class="lg:col-span-2">
                                    <label for="user_middle_name" class="profile-label">Middle Name<span class="opt-tag">Optional</span></label>
                                    <input type="text" id="user_middle_name" name="user_middle_name" value="{{ old('user_middle_name', $user->user_middle_name) }}" placeholder="Middle name" class="profile-field">
                                </div>
                                <div>
                                    <label for="user_last_name" class="profile-label">Last Name<span class="req-mark" aria-hidden="true">*</span></label>
                                    <input type="text" id="user_last_name" name="user_last_name" required value="{{ old('user_last_name', $user->user_last_name) }}" placeholder="Last name" class="profile-field">
                                </div>
                                <div>
                                    <label for="user_suffix" class="profile-label">Suffix<span class="opt-tag">Optional</span></label>
                                    @php $currentSuffix = old('user_suffix', $user->user_suffix); @endphp
                                    <select id="user_suffix" name="user_suffix" class="profile-field {{ $currentSuffix ? '' : 'is-placeholder' }}"
                                        onchange="this.classList.remove('is-placeholder')">
                                        {{-- Disabled placeholder, as before: an empty suffix is ignored on save (pick N/A for none). --}}
                                        <option value="" disabled {{ $currentSuffix ? '' : 'selected' }}>Select</option>
                                        @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'N/A'] as $suffix)
                                        <option value="{{ $suffix }}" @selected($currentSuffix === $suffix)>{{ $suffix }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="employer_position" class="profile-label">Position<span class="opt-tag">Optional</span></label>
                                <input type="text" id="employer_position" name="employer_position" value="{{ old('employer_position', $employer?->employer_position) }}" placeholder="e.g. HR Manager" class="profile-field">
                            </div>
                            <div>
                                <label for="user_email" class="profile-label">Email<span class="req-mark" aria-hidden="true">*</span></label>
                                <input type="email" id="user_email" name="user_email" required value="{{ old('user_email', $user->user_email) }}" placeholder="example@domain.com" class="profile-field">
                            </div>
                            <div>
                                <label for="user_number" class="profile-label">Contact Number<span class="opt-tag">Optional</span></label>
                                <input type="tel" id="user_number" name="user_number" value="{{ old('user_number', $user->user_number) }}" placeholder="+63 XXX XXX XXXX" class="profile-field">
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>

    @include('partials.footer-employer')
    @include('partials.image-lightbox')

</body>

<script>
    function addCompanyAddressRow() {
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2 company-address-row';
        row.innerHTML = `
            <span class="w-8 h-8 shrink-0 rounded-full bg-orange-50 text-[#C73D1A] flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
            <input type="text" name="addresses[]" placeholder="e.g. 123 Rizal St., Valenzuela City" aria-label="Company address" class="profile-field">
            <button type="button" onclick="this.closest('.company-address-row').remove()" title="Remove this address" aria-label="Remove this address"
                class="shrink-0 w-9 h-9 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 transition flex items-center justify-center">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        document.getElementById('companyAddressList').appendChild(row);
        row.querySelector('input').focus();
    }

    function togglePhotoOptions(event) {
        event.stopPropagation(); // Prevents the window click listener from immediately closing it
        document.getElementById('photoOptions').classList.toggle('hidden');
    }

    // Close the photo menu when clicking anywhere else on the page.
    window.addEventListener('click', function (e) {
        const options = document.getElementById('photoOptions');
        if (options && !options.contains(e.target) && !options.classList.contains('hidden')) {
            options.classList.add('hidden');
        }
    });
</script>

</html>
