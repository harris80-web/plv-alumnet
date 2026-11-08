@php
    $alumnus = $user->alumnus;
    $workExperiences = $alumnus->experiences->where('experience_type', 'work');
    $projects = $alumnus->experiences->where('experience_type', 'project');
    $certTypeLabels = \App\Models\Alumnus::certificationTypeLabels();
    $contactParts = $alumnus->resumeContactParts();
@endphp
{{--
    ATS-friendly PDF: a single column of real, selectable text in a standard
    font, standard section headings, and no photo, tables, columns, icons or
    background shapes — applicant tracking systems read it top to bottom.
    Keep it in step with resume-preview.blade.php. ResumeTextParser reads
    this same layout back in when an alumnus imports their exported PDF.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $alumnus->resumeFullName() }} - Resume</title>
    <style>
        @page { margin: 48px 54px; }

        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #222222; font-size: 11px; line-height: 1.4; }

        h1 { font-size: 20px; color: #0E0F3B; margin: 0; }
        .headline { font-size: 11px; color: #333333; margin: 2px 0 0; }
        .contact { font-size: 10px; color: #444444; margin: 4px 0 0; }

        .header { border-bottom: 2px solid #0E0F3B; padding-bottom: 8px; margin-bottom: 14px; }

        h2 { font-size: 12px; color: #0E0F3B; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #bbbbbb; padding-bottom: 2px; margin: 0 0 6px; }

        .section { margin-bottom: 14px; }

        .item { margin-bottom: 9px; }
        h3 { font-size: 11px; font-weight: bold; color: #111111; margin: 0; }
        .meta { font-size: 10px; color: #444444; margin: 1px 0 0; }

        p { margin: 0; }

        ul { margin: 3px 0 0; padding-left: 16px; }
        li { margin: 0 0 2px; }

        ul.plain { list-style: none; padding-left: 0; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $alumnus->resumeFullName() }}</h1>
        @if($alumnus->program?->program_name)
            <p class="headline">{{ $alumnus->program->program_name }} Graduate</p>
        @endif
        <p class="contact">{!! collect($contactParts)->map(fn ($value, $label) => e($label) . ': ' . e($value))->implode(' &nbsp;|&nbsp; ') !!}</p>
    </div>

    @if($alumnus->alumnus_resume_summary)
        <div class="section">
            <h2>Professional Summary</h2>
            <p>{{ $alumnus->alumnus_resume_summary }}</p>
        </div>
    @endif

    @if($alumnus->skills->isNotEmpty())
        @php $skillsByCategory = $alumnus->skills->groupBy('skill_category'); @endphp
        <div class="section">
            <h2>Skills</h2>
            <ul class="plain">
                @foreach(\App\Models\Skill::CATEGORIES as $categoryKey => $categoryLabel)
                    @continue($skillsByCategory->get($categoryKey, collect())->isEmpty())
                    <li><strong>{{ $categoryLabel }}:</strong> {{ $skillsByCategory[$categoryKey]->pluck('skill_name')->implode(', ') }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($workExperiences->isNotEmpty())
        <div class="section">
            <h2>Work Experience</h2>
            @foreach($workExperiences as $exp)
                <div class="item">
                    <h3>{{ $exp->experience_job_title }}</h3>
                    @if($exp->resumeMetaLine())
                        <p class="meta">{{ $exp->resumeMetaLine() }}</p>
                    @endif
                    @if($exp->jobDuties())
                        <ul>
                            @foreach($exp->jobDuties() as $duty)
                                <li>{{ $duty }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($projects->isNotEmpty())
        <div class="section">
            <h2>Projects</h2>
            @foreach($projects as $exp)
                <div class="item">
                    <h3>{{ $exp->experience_job_title }}</h3>
                    @if($exp->resumeMetaLine(false))
                        <p class="meta">{{ $exp->resumeMetaLine(false) }}</p>
                    @endif
                    @if($exp->jobDuties())
                        <ul>
                            @foreach($exp->jobDuties() as $duty)
                                <li>{{ $duty }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="section">
        <h2>Education</h2>
        <h3>{{ $alumnus->program->program_name ?? '--' }}</h3>
        <p class="meta">{{ $alumnus->resumeEducationLine() }}</p>
    </div>

    @if($alumnus->certifications->isNotEmpty())
        <div class="section">
            <h2>Certifications &amp; Trainings</h2>
            <ul class="plain">
                @foreach($alumnus->certifications as $cert)
                    <li><strong>{{ $cert->certification_name }}</strong>{{ $cert->resumeDetailText($certTypeLabels) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

</body>
</html>
