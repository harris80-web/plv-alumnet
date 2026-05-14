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
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="main-content-wrapper min-h-screen bg-gray-50 flex items-center justify-center p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-6xl">

            <!-- EMPLOYER PERSONAL INFO -->
            <section class="bg-white rounded-3xl border border-gray-100 shadow-md p-10 flex flex-col items-center">
                <h2 class="text-2xl font-bold mb-8 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    Employer Profile
                </h2>

                <div class="w-40 h-40 rounded-full bg-cyan-100 mb-6 flex items-center justify-center shadow-inner border-4 border-white overflow-hidden">
                    @if ($user->user_profile_picture)
                    <img src="{{ asset('storage/' . $user->user_profile_picture) }}" class="w-full h-full object-cover">
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    @endif
                </div>

                <div class="text-center space-y-6">
                    <div>
                        <h3 class="text-2xl font-extrabold text-black">
                            {{ $user->user_first_name }} {{ $user->user_last_name }}
                        </h3>
                        <p class="text-[11px] font-bold text-[#12123B] uppercase tracking-tighter">Employer Name</p>
                    </div>

                    <div>
                        <p class="text-lg font-medium text-gray-800">
                            {{ $user->employer->employer_position ?? 'Not provided' }}
                        </p>
                        <p class="text-[11px] font-bold text-[#12123B] uppercase tracking-tighter">Employer Position</p>
                    </div>

                    <div class="space-y-4 pt-4">
                        <div>
                            <p class="text-gray-800 font-medium">{{ $user->user_email }}</p>
                            <p class="text-[11px] font-bold text-[#12123B] uppercase tracking-tighter">Email</p>
                        </div>
                        <div>
                            <p class="text-gray-800 font-medium">
                                {{ $user->user_number ?? 'Not provided' }}
                            </p>
                            <p class="text-[11px] font-bold text-[#12123B] uppercase tracking-tighter">Contact Number</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BUSINESS DETAILS -->
            <section class="bg-white rounded-3xl border border-gray-100 shadow-md p-10 flex flex-col items-center">
                <h2 class="text-2xl font-bold mb-8 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    Business Details
                </h2>

                <div class="w-40 h-40 flex flex-col items-center justify-center mb-4">
                    @if ($user->employer->employer_company_logo)
                    <img src="{{ asset('storage/'. $user->employer->employer_company_logo) }}" alt="Company Logo">
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-[#12123B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
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
                <p class="text-[11px] font-bold text-[#12123B] uppercase tracking-tighter mb-8">Business Logo</p>

                <div class="text-center mb-10">
                    <h3 class="text-3xl font-bold text-black">
                        {{ $user->employer->employer_company_name ?? 'Not provided' }}
                    </h3>
                    <p class="text-[11px] font-bold text-[#12123B] uppercase tracking-tighter">Business Name</p>
                </div>

                <!-- REPLACE the entire grid div WITH: -->
                    <div class="grid grid-cols-2 gap-x-12 gap-y-10 w-full text-center">
                        <div>
                            <p class="text-gray-800 font-semibold">
                                {{ $user->employer->employer_year_established ?? 'Not provided' }}
                            </p>
                            <p class="text-[10px] font-bold text-[#12123B] uppercase tracking-tighter leading-none">Year Established</p>
                        </div>
                        <div>
                            <p class="text-gray-800 font-semibold">
                                {{ $user->employer->employer_company_size ?? 'Not provided' }}
                            </p>
                            <p class="text-[10px] font-bold text-[#12123B] uppercase tracking-tighter leading-none">Company/Business Size</p>
                        </div>
                        <div>
                            <p class="text-gray-800 font-semibold uppercase">
                                {{ $user->employer->industry->industry_name ?? 'None' }}
                            </p>
                            <p class="text-[10px] font-bold text-[#12123B] uppercase tracking-tighter leading-none">Industry / Sector</p>
                        </div>
                        <div>
                            @if ($user->employer->employer_website_url)
                            <a href="{{ $user->employer->employer_website_url }}" target="_blank" class="text-gray-800 font-semibold underline block break-all">
                                {{ $user->employer->employer_website_url }}
                            </a>
                            @else
                            <p class="text-gray-800 font-semibold">Not provided</p>
                            @endif
                            <p class="text-[10px] font-bold text-[#12123B] uppercase tracking-tighter leading-none">Official Website URL</p>
                        </div>
                    </div>
            </section>

        </div>
    </main>

    @include('partials.footer-employer')
</body>

</html>