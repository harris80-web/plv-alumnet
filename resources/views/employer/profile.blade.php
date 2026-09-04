<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
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
    @php $current_page = 'employer_profile'; @endphp
    @include('partials.header-employer')
    @include('partials.success')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Employer Profile</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="main-content-wrapper min-h-screen bg-gray-50 p-6">
        <div class="w-full max-w-6xl mx-auto space-y-10">

            <!-- EMPLOYER DETAILS -->
            <section>
                <h2 class="w-fit text-2xl md:text-3xl font-bold mb-4 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    Employer Details
                </h2>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-md p-8 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center gap-8">
                        <div class="w-28 h-28 rounded-full bg-cyan-100 flex items-center justify-center shadow-inner border-4 border-white overflow-hidden shrink-0 mx-auto md:mx-0">
                            @if ($user->user_profile_picture)
                            <img src="{{ asset('storage/' . $user->user_profile_picture) }}" class="w-full h-full object-cover">
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-16 gap-y-6 flex-1 text-center md:text-left">
                            <div>
                                <h3 class="text-lg font-bold text-[#0E0F3B]">
                                    {{ $user->user_first_name }} {{ $user->user_last_name }}
                                </h3>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Employer Name</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#0E0F3B]">
                                    {{ $user->employer->employer_position ?? 'Not provided' }}
                                </h3>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Employer Position</p>
                            </div>
                            <div>
                                <p class="text-gray-800 font-medium break-all">{{ $user->user_email }}</p>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Email</p>
                            </div>
                            <div>
                                <p class="text-gray-800 font-medium">
                                    {{ $user->user_number ?? 'Not provided' }}
                                </p>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Contact Number</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BUSINESS DETAILS -->
            <section>
                <h2 class="w-fit text-2xl md:text-3xl font-bold mb-4 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    Business Details
                </h2>

                <div class="bg-white rounded-3xl border border-gray-100 shadow-md p-8 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center gap-8">
                        <div class="flex flex-col items-center shrink-0 w-28 mx-auto md:mx-0">
                            <div class="w-28 h-28 flex items-center justify-center mb-2">
                                @if ($user->employer->employer_company_logo)
                                <img src="{{ asset('storage/'. $user->employer->employer_company_logo) }}" alt="Business Logo" class="max-w-full max-h-full object-contain">
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-[#12123B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect>
                                    <path d="M9 22v-4h6v4"></path>
                                    <path d="M8 6h.01"></path>
                                    <path d="M16 6h.01"></path>
                                    <path d="M8 10h.01"></path>
                                    <path d="M16 10h.01"></path>
                                    <path d="M8 14h.01"></path>
                                    <path d="M16 14h.01"></path>
                                </svg>
                                @endif
                            </div>
                            <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter text-center">Business Logo</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-16 gap-y-6 flex-1 text-center md:text-left">
                            <div>
                                <h3 class="text-lg font-bold text-[#0E0F3B]">
                                    {{ $user->employer->employer_company_name ?? 'Not provided' }}
                                </h3>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Business Name</p>
                            </div>
                            <div>
                                <p class="text-gray-800 font-semibold">
                                    {{ $user->employer->employer_company_size ?? 'Not provided' }}
                                </p>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Company/Business Size</p>
                            </div>
                            <div>
                                <p class="text-gray-800 font-semibold">
                                    {{ $user->employer->employer_year_established ?? 'Not provided' }}
                                </p>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Year Established</p>
                            </div>
                            <div>
                                @if ($user->employer->employer_website_url)
                                <a href="{{ $user->employer->employer_website_url }}" target="_blank" class="text-gray-800 font-semibold underline break-all">
                                    {{ $user->employer->employer_website_url }}
                                </a>
                                @else
                                <p class="text-gray-800 font-semibold">Not provided</p>
                                @endif
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Official Website URL</p>
                            </div>
                            <div>
                                <p class="text-gray-800 font-semibold">
                                    {{ $user->employer->industry->industry_name ?? 'Not specified' }}
                                </p>
                                <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-tighter">Industry / Sector</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    @include('partials.footer-employer')
</body>

</html>