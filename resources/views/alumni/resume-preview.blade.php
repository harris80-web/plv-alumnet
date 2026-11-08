@php
    $alumnus = $user->alumnus;
    $workExperiences = $alumnus->experiences->where('experience_type', 'work');
    $projects = $alumnus->experiences->where('experience_type', 'project');
    $certTypeLabels = \App\Models\Alumnus::certificationTypeLabels();
    $contactParts = $alumnus->resumeContactParts();
@endphp

{{--
    ATS-friendly layout: one column, plain-text contact details, standard
    section headings, no photo, icons, chips or side-by-side columns — so
    applicant tracking systems (and copy/paste) read it top to bottom.
    Keep it in step with resume-pdf.blade.php.
    "Save as PDF" lives in the ALUMNI RESUME header on alumni/profile.blade.php (this partial's only user).
--}}
<div id="resume-paper" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sm:p-8 md:p-12 text-gray-800 font-sans">

    {{-- ===== Header ===== --}}
    <header class="border-b-2 border-[#0E0F3B] pb-4 mb-5">
        <h1 class="text-xl sm:text-3xl font-bold text-[#0E0F3B] break-words">{{ $alumnus->resumeFullName() }}</h1>
        @if($alumnus->program?->program_name)
            <p class="text-sm text-gray-700 mt-1">{{ $alumnus->program->program_name }} Graduate</p>
        @endif
        <p class="text-xs sm:text-sm text-gray-600 mt-2 break-words">
            @foreach($contactParts as $label => $value)
                {{ $label }}: @if($label === 'LinkedIn')<a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="hover:text-[#1D46A4] hover:underline">{{ $value }}</a>@else{{ $value }}@endif
                @if(!$loop->last)<span class="text-gray-400 mx-1">|</span>@endif
            @endforeach
        </p>
    </header>

    {{-- ===== Summary ===== --}}
    @if($alumnus->alumnus_resume_summary)
        <section class="mb-5">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wider border-b border-gray-300 pb-1 mb-2">Professional Summary</h2>
            <p class="text-sm leading-relaxed">{{ $alumnus->alumnus_resume_summary }}</p>
        </section>
    @endif

    {{-- ===== Skills (one plain-text line per category) ===== --}}
    @if($alumnus->skills->isNotEmpty())
        @php $skillsByCategory = $alumnus->skills->groupBy('skill_category'); @endphp
        <section class="mb-5">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wider border-b border-gray-300 pb-1 mb-2">Skills</h2>
            <ul class="text-sm leading-relaxed space-y-0.5">
                @foreach(\App\Models\Skill::CATEGORIES as $categoryKey => $categoryLabel)
                    @continue($skillsByCategory->get($categoryKey, collect())->isEmpty())
                    <li><span class="font-semibold">{{ $categoryLabel }}:</span> {{ $skillsByCategory[$categoryKey]->pluck('skill_name')->implode(', ') }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ===== Work Experience ===== --}}
    @if($workExperiences->isNotEmpty())
        <section class="mb-5">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wider border-b border-gray-300 pb-1 mb-2">Work Experience</h2>
            <div class="space-y-4">
                @foreach($workExperiences as $exp)
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $exp->experience_job_title }}</h3>
                        @if($exp->resumeMetaLine())
                            <p class="text-xs sm:text-sm text-gray-600">{{ $exp->resumeMetaLine() }}</p>
                        @endif
                        @if($exp->jobDuties())
                            <ul class="text-sm leading-relaxed list-disc list-outside ml-5 mt-1 space-y-0.5">
                                @foreach($exp->jobDuties() as $duty)
                                    <li>{{ $duty }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== Projects ===== --}}
    @if($projects->isNotEmpty())
        <section class="mb-5">
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wider border-b border-gray-300 pb-1 mb-2">Projects</h2>
            <div class="space-y-4">
                @foreach($projects as $exp)
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $exp->experience_job_title }}</h3>
                        @if($exp->resumeMetaLine(false))
                            <p class="text-xs sm:text-sm text-gray-600">{{ $exp->resumeMetaLine(false) }}</p>
                        @endif
                        @if($exp->jobDuties())
                            <ul class="text-sm leading-relaxed list-disc list-outside ml-5 mt-1 space-y-0.5">
                                @foreach($exp->jobDuties() as $duty)
                                    <li>{{ $duty }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== Education ===== --}}
    <section class="mb-5">
        <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wider border-b border-gray-300 pb-1 mb-2">Education</h2>
        <h3 class="font-semibold text-gray-900">{{ $alumnus->program->program_name ?? '--' }}</h3>
        <p class="text-xs sm:text-sm text-gray-600">{{ $alumnus->resumeEducationLine() }}</p>
    </section>

    {{-- ===== Certifications & Trainings ===== --}}
    @if($alumnus->certifications->isNotEmpty())
        <section>
            <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wider border-b border-gray-300 pb-1 mb-2">Certifications &amp; Trainings</h2>
            <ul class="text-sm leading-relaxed space-y-1">
                @foreach($alumnus->certifications as $cert)
                    <li>
                        <span class="font-semibold">{{ $cert->certification_name }}</span>{{ $cert->resumeDetailText($certTypeLabels) }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
