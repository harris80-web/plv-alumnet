<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>PLV-AlumNet | Profile</title>
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

    /* Profile card banner: same campus photo as the Employer Profile card. */
    .profile-banner {
        background:
            url("{{ asset('assets/heroSection.svg') }}") center / cover;
    }
</style>

@php
    $alumnus = $user->alumnus;
    $notProvided = '<span class="font-normal italic text-slate-400">Not provided</span>';
    $employed = (bool) $alumnus->alumnus_employment_status;
    $genderLabel = \App\Models\Alumnus::genderLabels()[$alumnus->alumnus_gender] ?? null;
    $programName = $alumnus->program->program_name ?? null;
    $collegeName = $alumnus->program?->collegeName();
    $batchYear = $alumnus->alumnus_batch?->format('Y');
    $skillNames = $alumnus->skills->pluck('skill_name');
    $resumeComplete = $alumnus->isResumeComplete();
    $resumePercent = (int) ($alumnus->alumnus_resume_completeness ?? 0);
    $workplaceLabel = $alumnus->alumnus_workplace_undisclosed ? 'Not disclosed' : $alumnus->alumnus_workplace;

    $documents = [
        ['label' => 'Resume / CV', 'icon' => 'fa-file-lines', 'path' => $alumnus->alumnus_resume_file_path],
        ['label' => 'Backup Resume / CV', 'icon' => 'fa-copy', 'path' => $alumnus->alumnus_resume_backup_file_path],
        ['label' => 'Cover Letter', 'icon' => 'fa-envelope-open-text', 'path' => $alumnus->alumnus_cover_letter_file_path],
    ];

    // Profile completeness: what an alumnus can fill in on Edit Profile and in the Resume Builder.
    // Job details only count while Employed (they're hidden on Edit Profile otherwise).
    $profileChecklist = [
        'Profile photo' => (bool) $user->user_profile_picture,
        'Contact number' => (bool) $user->user_number,
    ];
    if ($employed) {
        $profileChecklist += [
            'Industry / sector' => (bool) $alumnus->industry_id,
            'Workplace' => $alumnus->alumnus_workplace_undisclosed || $alumnus->alumnus_workplace,
            'Job position' => (bool) $alumnus->alumnus_job_position,
            'Employment date' => (bool) $alumnus->alumnus_employment_date,
            'Date of first job' => (bool) $alumnus->alumnus_first_job_date,
        ];
    }
    $profileChecklist += [
        'Skills' => $skillNames->isNotEmpty(),
        'Resume (Resume Builder)' => $resumeComplete,
        'Resume / CV file' => (bool) $alumnus->alumnus_resume_file_path,
    ];
    $completedCount = count(array_filter($profileChecklist));
    $completionPercent = (int) round($completedCount / count($profileChecklist) * 100);
    $missingItems = array_keys(array_filter($profileChecklist, fn ($done) => ! $done));
@endphp

<body class="min-h-screen flex flex-col bg-gray-50">
    @include('partials.header-alumni')
    @include('partials.error-toast')
    @include('partials.success')

    @include('partials.error')
    {{-- Hero banner hidden on this page (the page title + profile card replace it).
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Alumni Profile</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>
    --}}

    <main class="w-full max-w-6xl mx-auto px-6 py-8 flex-1 font-['Montserrat']">

        {{-- Title row: page title on the left, EDIT PROFILE on the right, on one line.
             Phones: the button drops under the subtitle at full width. --}}
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mb-5 sm:mb-6">
            <h1 class="inline-block text-xl sm:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                ALUMNI PROFILE
            </h1>
            <a href="{{ route('users.editProfile') }}"
                class="order-3 sm:order-2 w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 bg-[#ED7A07] hover:bg-[#C73D1A] text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-regular text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition-colors whitespace-nowrap">
                <i class="fa-solid fa-pen-to-square"></i> EDIT PROFILE
            </a>
            <p class="order-2 sm:order-3 w-full -mt-2 text-xs sm:text-sm text-slate-500">Your alumni details, documents and resume in one place.</p>
        </div>

        {{-- ===== Profile card ===== --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-100" aria-label="Alumni overview">
            <div class="profile-banner h-24 sm:h-28 rounded-t-2xl"></div>
            <div class="px-5 sm:px-8 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                    <div class="-mt-12 w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-full bg-[#0E0F3B] border-4 border-white shadow-md overflow-hidden flex items-center justify-center">
                        @if ($user->user_profile_picture)
                        <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Picture" class="w-full h-full object-cover">
                        @else
                        <i class="fa-solid fa-user text-4xl sm:text-5xl text-white mt-2"></i>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0 sm:pt-4">
                        <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-wide">Alumni Details</p>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <h2 class="text-lg sm:text-2xl font-bold text-[#0E0F3B] uppercase break-words">
                                {{ $alumnus->formalName() }}
                            </h2>
                            @if ($employed)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 text-[10px] font-bold uppercase tracking-wide">
                                <i class="fa-solid fa-briefcase"></i> Employed
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 border border-slate-200 text-[10px] font-bold uppercase tracking-wide">
                                <i class="fa-solid fa-briefcase"></i> Unemployed
                            </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-[#ED7A07] uppercase mt-1 rp-profile-meta">
                            <span class="rp-profile-course">{{ $programName ?? 'Not specified' }}</span><span class="rp-profile-sep"> &bull; </span><span class="rp-profile-batch">Alumni Batch {{ $batchYear ?? '--' }}</span>
                        </p>

                        <ul class="mt-2.5 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-slate-500">
                            @if ($collegeName)
                            <li class="inline-flex items-center gap-1.5 min-w-0">
                                <i class="fa-solid fa-building-columns text-xs text-slate-400"></i>
                                <span class="break-words">{{ $collegeName }}</span>
                            </li>
                            @endif
                            @if ($employed && $workplaceLabel)
                            <li class="inline-flex items-center gap-1.5 min-w-0">
                                <i class="fa-solid fa-building text-xs text-slate-400"></i>
                                <span class="break-words">{{ $alumnus->alumnus_job_position ? $alumnus->alumnus_job_position . ' · ' : '' }}{{ $workplaceLabel }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <dl class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-3 gap-2 sm:gap-3 text-center sm:text-left">
                    <div class="sm:flex sm:items-center sm:gap-3">
                        <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-[#1D264F]/5 text-[#1D264F] items-center justify-center"><i class="fa-solid fa-paper-plane"></i></span>
                        <div>
                            <dd class="text-lg sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $profileStats['applications'] }}</dd>
                            <dt class="text-[11px] font-semibold uppercase max-[359px]:normal-case tracking-normal sm:tracking-wide text-slate-400">{{ \Illuminate\Support\Str::plural('Application', $profileStats['applications']) }}</dt>
                        </div>
                    </div>
                    <div class="sm:flex sm:items-center sm:gap-3">
                        <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-amber-50 text-amber-500 items-center justify-center"><i class="fa-solid fa-file-lines"></i></span>
                        <div>
                            <dd class="text-lg sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $resumePercent }}%</dd>
                            <dt class="text-[11px] font-semibold uppercase max-[359px]:normal-case tracking-normal sm:tracking-wide text-slate-400">Resume Strength</dt>
                        </div>
                    </div>
                    <div class="sm:flex sm:items-center sm:gap-3">
                        <span class="hidden sm:flex w-10 h-10 shrink-0 rounded-xl bg-orange-50 text-[#C73D1A] items-center justify-center"><i class="fa-solid fa-calendar-check"></i></span>
                        <div>
                            <dd class="text-lg sm:text-xl font-bold text-[#0E0F3B] leading-tight">{{ $user->created_at?->format('M Y') ?? '—' }}</dd>
                            <dt class="text-[11px] font-semibold uppercase max-[359px]:normal-case tracking-normal sm:tracking-wide text-slate-400">Member Since</dt>
                        </div>
                    </div>
                </dl>
            </div>
        </section>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- ===== Left: personal + employment details ===== --}}
            <div class="lg:col-span-2 space-y-6">
                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-user"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Personal Details</h3>
                            <p class="text-xs text-slate-500">How PLV-AlumNet can reach you.</p>
                        </div>
                    </header>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 p-5 sm:p-6">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Email</dt>
                            <dd class="mt-1 text-sm font-semibold break-all"><a href="mailto:{{ $user->user_email }}" class="text-[#0E0F3B] hover:text-[#C73D1A]">{{ $user->user_email }}</a></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Contact No.</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $user->user_number ? e($user->user_number) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Gender</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $genderLabel ? e($genderLabel) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Batch</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $batchYear ? e($batchYear) : $notProvided !!}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Skills</dt>
                            <dd class="mt-1.5">
                                @if ($skillNames->isNotEmpty())
                                <ul class="flex flex-wrap gap-1.5">
                                    @foreach ($skillNames as $skill)
                                    <li class="text-xs font-semibold text-[#1D264F] bg-[#1D264F]/5 border border-[#1D264F]/10 rounded-full px-3 py-1">{{ $skill }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-sm">{!! $notProvided !!}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-briefcase"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Employment Details</h3>
                            <p class="text-xs text-slate-500">Your current work status and job information.</p>
                        </div>
                    </header>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 p-5 sm:p-6">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Employment Status</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B] flex flex-wrap items-center gap-2">
                                {{ $employed ? 'Employed' : 'Unemployed' }}
                                @if ($alumnus->hasCourseAlignedJob())
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold uppercase px-2 py-1 rounded-full">
                                    <i class="fa-solid fa-circle-check"></i> Aligned with Course
                                </span>
                                @endif
                            </dd>
                        </div>
                        @if ($employed)
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Industry / Sector</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $alumnus->industry ? e($alumnus->industry->industry_name) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Where I Work</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B] break-words">{!! $workplaceLabel ? e($workplaceLabel) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Job Position</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B] break-words">{!! $alumnus->alumnus_job_position ? e($alumnus->alumnus_job_position) : $notProvided !!}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Employment Date</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $alumnus->alumnus_employment_date ? e($alumnus->alumnus_employment_date->format('F j, Y')) : $notProvided !!}</dd>
                        </div>
                        @endif
                        @if ($employed || $alumnus->alumnus_first_job_date)
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">Date of First Job</dt>
                            <dd class="mt-1 text-sm font-semibold text-[#0E0F3B]">{!! $alumnus->alumnus_first_job_date ? e($alumnus->alumnus_first_job_date->format('F j, Y')) : $notProvided !!}</dd>
                        </div>
                        @endif
                    </dl>
                </section>
            </div>

            {{-- ===== Right: completeness + documents ===== --}}
            <div class="space-y-6">
                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-orange-50 text-[#C73D1A] flex items-center justify-center"><i class="fa-solid fa-list-check"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Profile Completeness</h3>
                            <p class="text-xs text-slate-500">A complete profile makes your job applications stronger.</p>
                        </div>
                    </header>
                    <div class="p-5 sm:p-6">
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
                            <i class="fa-solid fa-circle-check"></i> Your alumni profile is complete.
                        </p>
                        @endif
                    </div>
                </section>

                {{-- ===== DOCUMENTS: uploaded Resume/CV and Cover Letter, read-only.
                     Distinct from the Resume Builder below — these are the actual
                     files offered as "Use my AlumNet Profile" when applying to a job.
                     Edited from the Edit Profile page's "Documents" card. --}}
                <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                    <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                        <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-folder-open"></i></span>
                        <div>
                            <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Documents</h3>
                            <p class="text-xs text-slate-500">Files you can send when you apply for a job.</p>
                        </div>
                    </header>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($documents as $document)
                        <li class="flex items-center gap-3 px-5 sm:px-6 py-3.5">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-orange-50 text-[#C73D1A] flex items-center justify-center"><i class="fa-solid {{ $document['icon'] }}"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">{{ $document['label'] }}</p>
                                @if ($document['path'])
                                <a href="{{ asset('storage/' . $document['path']) }}" target="_blank" rel="noopener"
                                    class="block truncate text-sm font-semibold text-[#1D46A4] hover:underline" title="{{ basename($document['path']) }}">{{ basename($document['path']) }}</a>
                                @else
                                <p class="text-sm font-medium text-[#1D264F]">Not uploaded yet</p>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </section>
            </div>
        </div>

        {{-- ===== RESUME: finished view, or a prompt to go build one ===== --}}
        <section class="mt-10" aria-labelledby="alumniResumeHeading">
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mb-4 sm:mb-5">
                <div>
                    <h2 id="alumniResumeHeading" class="inline-block text-lg sm:text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                        ALUMNI RESUME
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500">Generated from the details in your Resume Builder.</p>
                </div>
                @if ($resumeComplete)
                <a href="{{ route('resume.pdf') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition duration-200 uppercase whitespace-nowrap">
                    <i class="fa-solid fa-file-arrow-down"></i> Save as PDF
                </a>
                @endif
            </div>

            @if ($resumeComplete)
                @include('alumni.resume-preview', ['user' => $user])
            @else
                {{-- Empty state: brand navy, not gray. --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-5 py-10 sm:p-12 text-center">
                    <span class="mx-auto w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center">
                        <i class="fa-solid fa-file-signature text-2xl sm:text-3xl"></i>
                    </span>
                    @if ($resumePercent > 0)
                    <h3 class="mt-4 text-base sm:text-xl font-bold text-[#0E0F3B]">Your resume is {{ $resumePercent }}% complete</h3>
                    <p class="mt-1 text-sm text-[#1D264F]">Finish it in the Resume Builder and it will show up here, ready to save as PDF.</p>
                    @else
                    <h3 class="mt-4 text-base sm:text-xl font-bold text-[#0E0F3B]">You haven't built your resume yet</h3>
                    <p class="mt-1 text-sm text-[#1D264F]">Head to Edit Profile to create your resume — it only takes a few minutes.</p>
                    @endif
                    <a href="{{ route('users.editProfile', ['openResume' => 1]) }}"
                        class="mt-5 inline-flex items-center justify-center gap-2 bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white px-5 sm:px-8 py-2.5 sm:py-3 rounded-lg font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-md transition duration-200 uppercase">
                        <i class="fa-solid fa-pen-to-square"></i> {{ $resumePercent > 0 ? 'Finish Your Resume' : 'Create Your Resume' }}
                    </a>
                </div>
            @endif
        </section>
    </main>

    @include('partials.footer-alumni')
</body>

</html>
