@php
    $alumnus = $user->alumnus;
    $workExperiences = $alumnus->experiences->where('experience_type', 'work');
    $projects = $alumnus->experiences->where('experience_type', 'project');
    $certTypeLabels = \App\Models\Alumnus::certificationTypeLabels();
@endphp

<div class="flex justify-end mb-6">
    <a href="{{ route('resume.pdf') }}"
        class="flex items-center gap-2 bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-4 rounded shadow-md transition duration-200 uppercase">
        <i class="fa-solid fa-file-arrow-down"></i> Save as PDF
    </a>
</div>

<div id="resume-paper" class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden p-8 md:p-12 mb-8">

    {{-- ===== Header ===== --}}
    <div class="flex items-center gap-6 border-b-2 border-[#0E0F3B] pb-6 mb-6">
        @if($user->user_profile_picture)
            <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Photo"
                class="w-24 h-24 rounded-full object-cover border-2 border-[#0E0F3B] flex-shrink-0">
        @endif
        <div class="flex-1 text-center">
            <h1 class="text-3xl font-bold text-[#0E0F3B] uppercase tracking-wide">{{ $alumnus->resumeFullName() }}</h1>
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm text-gray-600 mt-3">
                <span><i class="fa-solid fa-envelope text-[#ED7A07] mr-1"></i>{{ $user->user_email }}</span>
                @if($user->user_number)
                    <span><i class="fa-solid fa-phone text-[#ED7A07] mr-1"></i>{{ $user->user_number }}</span>
                @endif
                @if($alumnus->linkedin_url)
                    <a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="hover:text-[#1D46A4]">
                        <i class="fa-brands fa-linkedin text-[#ED7A07] mr-1"></i>{{ preg_replace('~^https?://(www\.)?~i', '', $alumnus->linkedin_url) }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Summary ===== --}}
    @if($alumnus->alumnus_resume_summary)
        <div class="mb-6">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                Professional Summary</h2>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $alumnus->alumnus_resume_summary }}</p>
        </div>
    @endif

    {{-- ===== Education ===== --}}
    <div class="mb-6">
        <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
            Education</h2>
        <div class="flex justify-between items-baseline flex-wrap gap-x-2">
            <p class="font-semibold text-gray-900">{{ $alumnus->program->program_name ?? '--' }}</p>
            @if($alumnus->alumnus_batch)
                <p class="text-xs text-gray-500">Batch {{ $alumnus->alumnus_batch }}</p>
            @endif
        </div>
        <p class="text-xs text-gray-500 italic">Pamantasan ng Lungsod ng Valenzuela (PLV)</p>
    </div>

    {{-- ===== Skills ===== --}}
    @if($alumnus->skills->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                Skills</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($alumnus->skills as $skill)
                    <span class="text-xs font-medium bg-gray-100 text-gray-700 rounded-full px-3 py-1">{{ $skill->skill_name }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== Work Experience ===== --}}
    @if($workExperiences->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                Work Experience</h2>
            <div class="space-y-4">
                @foreach($workExperiences as $exp)
                    <div>
                        <div class="flex justify-between items-baseline flex-wrap gap-x-2">
                            <p class="font-semibold text-gray-900">{{ $exp->experience_job_title }}</p>
                            @if(\App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months))
                                <p class="text-xs text-gray-500">{{ \App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months) }}</p>
                            @endif
                        </div>
                        @if($exp->industry)
                            <p class="text-xs text-gray-500 italic mb-1">{{ $exp->industry->industry_name }}</p>
                        @endif
                        @if($exp->experience_job_description)
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $exp->experience_job_description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== Projects ===== --}}
    @if($projects->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                Projects</h2>
            <div class="space-y-4">
                @foreach($projects as $exp)
                    <div>
                        <div class="flex justify-between items-baseline flex-wrap gap-x-2">
                            <p class="font-semibold text-gray-900">{{ $exp->experience_job_title }}</p>
                            @if(\App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months))
                                <p class="text-xs text-gray-500">{{ \App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months) }}</p>
                            @endif
                        </div>
                        @if($exp->experience_job_description)
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $exp->experience_job_description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== Certifications & Trainings ===== --}}
    @if($alumnus->certifications->isNotEmpty())
        <div>
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                Certifications &amp; Trainings</h2>
            <div class="space-y-2">
                @foreach($alumnus->certifications as $cert)
                    <div class="flex justify-between items-baseline flex-wrap gap-x-2">
                        <p class="text-sm text-gray-800">
                            <span class="font-semibold">{{ $cert->certification_name }}</span>
                            @if($cert->certification_from)
                                &mdash; {{ $cert->certification_from }}
                            @endif
                            <span class="text-xs text-gray-500">({{ $certTypeLabels[$cert->certification_type] ?? ucfirst($cert->certification_type) }})</span>
                        </p>
                        @if($cert->certification_date)
                            <p class="text-xs text-gray-500 whitespace-nowrap">{{ $cert->certification_date?->format('M Y') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
