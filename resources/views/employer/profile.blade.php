<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Company Profile</title>
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

    /* Company card banner: brand gradient over the campus photo. */
    .company-banner {
        background:
            url("{{ asset('assets/heroSection.svg') }}") center / cover;
    }
</style>

@php
    $employer = $user->employer;
    $notProvided = '<span class="font-normal italic text-slate-400">Not provided</span>';
    $websiteUrl = $employer?->employer_website_url;
    $websiteLabel = $websiteUrl ? preg_replace('#^https?://(www\.)?#i', '', rtrim($websiteUrl, '/')) : null;
    $addresses = $employer?->addresses ?? collect();
    $fullName = trim(implode(' ', array_filter([$user->user_first_name, $user->user_middle_name, $user->user_last_name, $user->user_suffix && $user->user_suffix !== 'N/A' ? $user->user_suffix : null])));

    // Profile completeness: everything an employer can fill in on Edit Company Profile.
    $profileChecklist = [
        'Company logo' => (bool) $employer?->employer_company_logo,
        'Business name' => (bool) $employer?->employer_company_name,
        'Industry / sector' => (bool) $employer?->industry_id,
        'Company size' => (bool) $employer?->employer_company_size,
        'Year established' => (bool) $employer?->employer_year_established,
        'Official website' => (bool) $websiteUrl,
        'Company address' => $addresses->isNotEmpty(),
        'Profile photo' => (bool) $user->user_profile_picture,
        'Position' => (bool) $employer?->employer_position,
        'Contact number' => (bool) $user->user_number,
    ];
    $completedCount = count(array_filter($profileChecklist));
    $completionPercent = (int) round($completedCount / count($profileChecklist) * 100);
    $missingItems = array_keys(array_filter($profileChecklist, fn ($done) => ! $done));
@endphp

<body class="min-h-screen flex flex-col bg-gray-50">
    @php $current_page = 'employer_profile'; @endphp
    @include('partials.header-employer')
    @include('partials.success')

    @include('partials.error')
    {{-- Hero banner hidden on this page (the page title + company card replace it).
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>
    --}}

    <main class="w-full max-w-6xl mx-auto px-6 py-8 flex-1 font-['Montserrat']">

        {{-- Title row: page title on the left, EDIT COMPANY PROFILE on the right, on one line.
             Phones: the button drops under the subtitle at full width. --}}
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mb-5 sm:mb-6">
            <h1 class="inline-block text-xl sm:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                EMPLOYER PROFILE
            </h1>
            <a href="{{ route('users.editProfile') }}"
                class="order-3 sm:order-2 w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 bg-[#ED7A07] hover:bg-[#C73D1A] text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-regular text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition-colors whitespace-nowrap">
                <i class="fa-solid fa-pen-to-square"></i> EDIT PROFILE
            </a>
            <p class="order-2 sm:order-3 w-full -mt-2 text-xs sm:text-sm text-slate-500">How your company appears to PLV-AlumNet alumni and administrators.</p>
        </div>

        {{-- ===== Company card ===== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" aria-label="Company overview">
            <div class="company-banner h-24 sm:h-28"></div>
            <div class="px-5 sm:px-8 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                    <div class="-mt-12 w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-2xl bg-white border-4 border-white shadow-md overflow-hidden flex items-center justify-center">
                        @if ($employer?->employer_company_logo)
                        <img src="{{ asset('storage/' . $employer->employer_company_logo) }}" alt="{{ $employer->employer_company_name }} logo" class="w-full h-full object-contain bg-white">
                        @else
                        <div class="w-full h-full bg-slate-50 flex items-center justify-center rounded-xl">
                            <i class="fa-solid fa-building text-4xl text-[#1D264F]/70"></i>
                        </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 sm:pt-4">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <h2 class="text-xl sm:text-2xl font-bold text-[#0E0F3B] break-words">
                                {{ $employer?->employer_company_name ?: 'Your company name' }}
                            </h2>
                            @if ($employer?->employer_approved)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 text-[10px] font-bold uppercase tracking-wide"
                                title="Approved by the PLV-AlumNet administrators">
                                <i class="fa-solid fa-circle-check"></i> Verified Employer
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold uppercase tracking-wide">
                                <i class="fa-solid fa-hourglass-half"></i> Pending Verification
                            </span>
                            @endif
                        </div>

                        <ul class="mt-2.5 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-slate-500">
                            <li class="inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-industry text-xs text-slate-400"></i>
                                {{ $employer?->industry?->industry_name ?? 'Industry not specified' }}
                            </li>
                            @if ($employer?->employer_company_size)
                            <li class="inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-users text-xs text-slate-400"></i>
                                {{ number_format($employer->employer_company_size) }} {{ \Illuminate\Support\Str::plural('employee', $employer->employer_company_size) }}
                            </li>
                            @endif
                            @if ($employer?->employer_year_established)
                            <li class="inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-calendar text-xs text-slate-400"></i>
                                Est. {{ $employer->employer_year_established }}
                            </li>
                            @endif
                            @if ($addresses->isNotEmpty())
                            <li class="inline-flex items-center gap-1.5 min-w-0">
                                <i class="fa-solid fa-location-dot text-xs text-slate-400"></i>
                                <span class="truncate">{{ $addresses->first()->address }}</span>
                            </li>
                            @endif
                            @if ($websiteUrl)
                            <li class="inline-flex items-center gap-1.5 min-w-0">
                                <i class="fa-solid fa-globe text-xs text-slate-400"></i>
                                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="truncate text-[#1D46A4] hover:underline">{{ $websiteLabel }}</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <dl class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-3 gap-3 text-center sm:text-left">
                    <div class="sm:flex sm:items-center sm:gap-3">
                        <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-[#1D264F]/5 text-[#1D264F] items-center justify-center"><i class="fa-solid fa-briefcase"></i></span>
                        <div>
                            <dd class="text-lg sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $profileStats['jobPosts'] }}</dd>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ \Illuminate\Support\Str::plural('Job Post', $profileStats['jobPosts']) }}</dt>
                        </div>
                    </div>
                    <div class="sm:flex sm:items-center sm:gap-3">
                        <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-amber-50 text-amber-500 items-center justify-center"><i class="fa-solid fa-star"></i></span>
                        <div>
                            <dd class="text-lg sm:text-xl font-bold text-[#0E0F3B] leading-tight">
                                @if ($profileStats['rating'] !== null)
                                {{ number_format($profileStats['rating'], 1) }}<span class="text-sm font-semibold text-slate-400"> / 5</span>
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </dd>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                {{ $profileStats['ratingCount'] ? $profileStats['ratingCount'] . ' ' . \Illuminate\Support\Str::plural('Rating', $profileStats['ratingCount']) : 'No ratings yet' }}
                            </dt>
                        </div>
                    </div>
                    <div class="sm:flex sm:items-center sm:gap-3">
                        <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-orange-50 text-[#C73D1A] items-center justify-center"><i class="fa-solid fa-calendar-check"></i></span>
                        <div>
                            <dd class="text-lg sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $user->created_at?->format('M Y') ?? '—' }}</dd>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Partner Since</dt>
                        </div>
                    </div>
                </dl>
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
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 p-6">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Business Name</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B] break-words">{!! $employer?->employer_company_name ? e($employer->employer_company_name) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Industry / Sector</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $employer?->industry ? e($employer->industry->industry_name) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Company / Business Size</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $employer?->employer_company_size ? e(number_format($employer->employer_company_size) . ' ' . \Illuminate\Support\Str::plural('employee', $employer->employer_company_size)) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Year Established</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $employer?->employer_year_established ? e($employer->employer_year_established) : $notProvided !!}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Official Website</dt>
                            <dd class="mt-1 text-sm font-semibold break-all">
                                @if ($websiteUrl)
                                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="text-[#1D46A4] hover:underline">
                                    {{ $websiteUrl }} <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-0.5"></i>
                                </a>
                                @else
                                {!! $notProvided !!}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-location-dot"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Company Addresses</h3>
                            <p class="text-xs text-slate-500">Office and branch locations you can pick from when posting a job.</p>
                        </div>
                    </header>
                    <div class="p-6">
                        @forelse ($addresses as $address)
                        <div class="flex items-start gap-3 py-3 {{ $loop->first ? 'pt-0' : 'border-t border-slate-100' }} {{ $loop->last ? 'pb-0' : '' }}">
                            <span class="w-7 h-7 shrink-0 rounded-full bg-orange-50 text-[#C73D1A] flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                            <p class="text-sm font-medium text-[#0E0F3B] pt-1 break-words min-w-0">{{ $address->address }}</p>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fa-solid fa-map-location-dot text-3xl text-[#1D264F]"></i>
                            <p class="mt-2 text-sm font-medium text-[#1D264F]">No addresses saved yet.</p>
                            <a href="{{ route('users.editProfile') }}" class="mt-1 inline-block text-xs font-bold text-[#C73D1A] hover:underline">Add an address</a>
                        </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- ===== Right: contact person + completeness ===== --}}
            <div class="space-y-6">
                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-user-tie"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Contact Person</h3>
                            <p class="text-xs text-slate-500">Who represents your company.</p>
                        </div>
                    </header>
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            @include('partials.avatar', [
                                'photo' => $user->user_profile_picture ? asset('storage/' . $user->user_profile_picture) : null,
                                'size' => 'w-16 h-16',
                                'iconSize' => 'text-2xl',
                                'bg' => 'bg-[#1D264F]',
                            ])
                            <div class="min-w-0">
                                <p class="font-bold text-[#0E0F3B] break-words">{{ $fullName ?: 'Name not provided' }}</p>
                                <p class="text-sm text-slate-500 break-words">{!! $employer?->employer_position ? e($employer->employer_position) : $notProvided !!}</p>
                            </div>
                        </div>

                        <dl class="mt-5 space-y-3">
                            <div class="flex items-start gap-3">
                                <span class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 text-slate-500 flex items-center justify-center text-xs"><i class="fa-solid fa-envelope"></i></span>
                                <div class="min-w-0">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Email</dt>
                                    <dd class="text-sm font-semibold break-all"><a href="mailto:{{ $user->user_email }}" class="text-[#0E0F3B] hover:text-[#C73D1A]">{{ $user->user_email }}</a></dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-8 h-8 shrink-0 rounded-lg bg-slate-50 text-slate-500 flex items-center justify-center text-xs"><i class="fa-solid fa-phone"></i></span>
                                <div class="min-w-0">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Contact Number</dt>
                                    <dd class="text-sm font-semibold text-[#0E0F3B]">{!! $user->user_number ? e($user->user_number) : $notProvided !!}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-orange-50 text-[#C73D1A] flex items-center justify-center"><i class="fa-solid fa-list-check"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Profile Completeness</h3>
                            <p class="text-xs text-slate-500">A complete profile builds trust with applicants.</p>
                        </div>
                    </header>
                    <div class="p-6">
                        <div class="flex items-end justify-between">
                            <span class="text-3xl font-bold text-[#0E0F3B]">{{ $completionPercent }}%</span>
                            <span class="text-xs font-semibold text-slate-500">{{ $completedCount }} of {{ count($profileChecklist) }} complete</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-label="Profile completeness"
                            aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $completionPercent }}">
                            <div class="h-full rounded-full bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07]" style="width: {{ $completionPercent }}%"></div>
                        </div>

                        @if ($missingItems)
                        <p class="mt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Still missing</p>
                        <ul class="mt-2 space-y-1.5">
                            @foreach ($missingItems as $item)
                            <li class="flex items-center gap-2 text-sm text-slate-600">
                                <i class="fa-regular fa-circle text-[10px] text-slate-300"></i> {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('users.editProfile') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#C73D1A] hover:underline">
                            Complete your profile <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                        @else
                        <p class="mt-4 flex items-center gap-2 text-sm font-semibold text-green-700">
                            <i class="fa-solid fa-circle-check"></i> Your company profile is complete.
                        </p>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </main>

    @include('partials.footer-employer')
</body>

</html>
