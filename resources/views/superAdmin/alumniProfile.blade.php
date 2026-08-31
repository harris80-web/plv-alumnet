@php
    $current_page = 'user_management';
    $user = $alumnus->user;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->user_first_name }} {{ $user->user_last_name }} | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden">
            @include('partials.super-admin-header')
            <div class="flex-1 overflow-y-auto p-8">
                @include('partials.success')

                <a href="{{ route('superAdmin.userManagement') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#0E0F3B] mb-6 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to User Management
                </a>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- LEFT: identity card -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 text-center lg:col-span-1">
                        <div class="w-24 h-24 rounded-full bg-[#0E0F3B] mx-auto mb-4 flex items-center justify-center overflow-hidden">
                            @if ($user->user_profile_picture)
                            <img src="{{ asset('storage/' . $user->user_profile_picture) }}" class="w-full h-full object-cover">
                            @else
                            <i class="fa-solid fa-user text-4xl text-white"></i>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-[#0E0F3B]">
                            {{ trim($user->user_first_name . ' ' . $user->user_middle_name . ' ' . $user->user_last_name . ' ' . $user->user_suffix) }}
                        </h2>
                        <p class="text-sm text-slate-500">{{ $alumnus->program->program_name ?? 'Program not set' }}</p>
                        <p class="text-xs text-slate-400 mt-1">Batch {{ optional($alumnus->alumnus_batch)->format('Y') }} &middot; {{ $alumnus->section->section_name ?? 'N/A' }}</p>

                        <span class="inline-block mt-4 px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $user->user_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->user_active ? 'Active' : 'Deactivated' }}
                        </span>

                        <div class="text-left mt-6 space-y-3 border-t border-slate-100 pt-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Email</p>
                                <p class="text-sm text-slate-700 break-all">{{ $user->user_email }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Contact Number</p>
                                <p class="text-sm text-slate-700">{{ $user->user_number ?? 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Gender</p>
                                <p class="text-sm text-slate-700">{{ \App\Models\Alumnus::genderLabels()[$alumnus->alumnus_gender] ?? 'N/A' }}</p>
                            </div>
                            @if ($alumnus->linkedin_url)
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">LinkedIn</p>
                                <a href="{{ $alumnus->linkedin_url }}" target="_blank" class="text-sm text-blue-600 hover:underline break-all">{{ $alumnus->linkedin_url }}</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- RIGHT: employment, alumni id/yearbook, skills, experience, certifications -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Employment -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-4">Employment</h3>

                            <div class="flex items-center gap-2 mb-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $alumnus->alumnus_employment_status ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $alumnus->alumnus_employment_status ? 'Employed' : 'Unemployed' }}
                                </span>
                                @if ($alumnus->hasCourseAlignedJob())
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-bold uppercase px-2.5 py-1 rounded-full">
                                    <i class="fa-solid fa-circle-check"></i> Aligned with Course
                                </span>
                                @endif
                            </div>

                            @if ($alumnus->alumnus_employment_status)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Industry / Sector</p>
                                    <p class="text-slate-700">{{ $alumnus->industry->industry_name ?? 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Workplace</p>
                                    <p class="text-slate-700">{{ $alumnus->alumnus_workplace_undisclosed ? 'Not disclosed' : ($alumnus->alumnus_workplace ?? 'Not specified') }}</p>
                                </div>
                                @if ($alumnus->alumnus_job_position)
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Job Position</p>
                                    <p class="text-slate-700">{{ $alumnus->alumnus_job_position }}</p>
                                </div>
                                @endif
                                @if ($alumnus->alumnus_employment_date)
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Employment Date</p>
                                    <p class="text-slate-700">{{ $alumnus->alumnus_employment_date->format('F j, Y') }}</p>
                                </div>
                                @endif
                            </div>
                            @endif

                            @if ($alumnus->alumnus_first_job_date)
                            <div class="mt-4 pt-4 border-t border-slate-100 text-sm">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Date of First Job</p>
                                <p class="text-slate-700">{{ $alumnus->alumnus_first_job_date->format('F j, Y') }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Alumni ID / Yearbook status -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-4">Membership Status</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Alumni ID</p>
                                    @if ($alumnus->alumniId)
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $alumnus->alumniId->badgeClass() }}">
                                        {{ $alumnus->alumniId->statusLabel() }}
                                    </span>
                                    @else
                                    <span class="text-slate-400">No record</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Yearbook</p>
                                    @if ($alumnus->yearbook)
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $alumnus->yearbook->claimingBadgeClass() }}">
                                        {{ $alumnus->yearbook->claimingStatusLabel() }}
                                    </span>
                                    @else
                                    <span class="text-slate-400">No record</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Skills -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-4">Skills</h3>
                            @if ($alumnus->skills->isEmpty())
                            <p class="text-sm text-slate-400">No skills listed.</p>
                            @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($alumnus->skills as $skill)
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">{{ $skill->skill_name }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Experience -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-4">Experience &amp; Projects</h3>
                            @if ($alumnus->experiences->isEmpty())
                            <p class="text-sm text-slate-400">No experience or projects listed.</p>
                            @else
                            <div class="space-y-4">
                                @foreach ($alumnus->experiences as $exp)
                                <div class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-start">
                                        <p class="font-bold text-sm text-slate-700">{{ $exp->experience_job_title }}</p>
                                        <span class="text-[10px] uppercase font-bold text-slate-400">{{ $exp->experience_type }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500">
                                        {{ \App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months) ?? 'Duration not specified' }}
                                        @if($exp->industry) &middot; {{ $exp->industry->industry_name }} @endif
                                    </p>
                                    @if ($exp->experience_job_description)
                                    <p class="text-xs text-slate-600 mt-1">{{ $exp->experience_job_description }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Certifications -->
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-4">Certifications, Seminars &amp; Trainings</h3>
                            @if ($alumnus->certifications->isEmpty())
                            <p class="text-sm text-slate-400">No certifications, seminars, or trainings listed.</p>
                            @else
                            <div class="space-y-3">
                                @foreach ($alumnus->certifications as $cert)
                                <div class="border-b border-slate-100 pb-2 last:border-0 last:pb-0">
                                    <p class="font-bold text-sm text-slate-700">{{ $cert->certification_name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ \App\Models\Alumnus::certificationTypeLabels()[$cert->certification_type] ?? $cert->certification_type }}
                                        @if($cert->certification_from) &middot; {{ $cert->certification_from }} @endif
                                        @if($cert->certification_date) &middot; {{ optional($cert->certification_date)->format('M Y') }} @endif
                                    </p>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
