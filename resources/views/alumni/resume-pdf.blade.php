@php
    $alumnus = $user->alumnus;
    $workExperiences = $alumnus->experiences->where('experience_type', 'work');
    $projects = $alumnus->experiences->where('experience_type', 'project');
    $certTypeLabels = \App\Models\Alumnus::certificationTypeLabels();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $alumnus->resumeFullName() }} - Resume</title>
    <style>
        @page { margin: 42px 46px; }

        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333333; font-size: 11px; }

        h1 { font-size: 22px; color: #0E0F3B; text-align: center; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px; }

        .header { border-bottom: 2px solid #0E0F3B; padding-bottom: 12px; margin-bottom: 18px; }

        .contact { text-align: center; color: #555555; font-size: 10px; }
        .contact span { margin: 0 8px; }

        h2 { font-size: 12px; color: #0E0F3B; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #dddddd; padding-bottom: 4px; margin: 0 0 8px; }

        .section { margin-bottom: 16px; }

        .row { display: flex; justify-content: space-between; }

        .item { margin-bottom: 10px; }
        .item-title { font-weight: bold; color: #111111; }
        .item-sub { font-style: italic; color: #666666; font-size: 10px; margin: 2px 0; }
        .item-date { color: #666666; font-size: 10px; white-space: nowrap; }

        p { margin: 4px 0 0; line-height: 1.4; }

        .skill {
            display: inline-block;
            background: #f0f0f0;
            border-radius: 10px;
            padding: 3px 10px;
            margin: 0 4px 4px 0;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $alumnus->resumeFullName() }}</h1>
        <div class="contact">
            <span>{{ $user->user_email }}</span>
            @if($user->user_number)
                <span>&bull; {{ $user->user_number }}</span>
            @endif
            @if($alumnus->linkedin_url)
                <span>&bull; {{ preg_replace('~^https?://(www\.)?~i', '', $alumnus->linkedin_url) }}</span>
            @endif
        </div>
    </div>

    @if($alumnus->alumnus_resume_summary)
        <div class="section">
            <h2>Professional Summary</h2>
            <p>{{ $alumnus->alumnus_resume_summary }}</p>
        </div>
    @endif

    <div class="section">
        <h2>Education</h2>
        <div class="row">
            <span class="item-title">{{ $alumnus->program->program_name ?? '--' }}</span>
            @if($alumnus->alumnus_batch)
                <span class="item-date">Batch {{ $alumnus->alumnus_batch }}</span>
            @endif
        </div>
        <p class="item-sub">Pamantasan ng Lungsod ng Valenzuela (PLV)</p>
    </div>

    @if($alumnus->skills->isNotEmpty())
        <div class="section">
            <h2>Skills</h2>
            <div>
                @foreach($alumnus->skills as $skill)
                    <span class="skill">{{ $skill->skill_name }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if($workExperiences->isNotEmpty())
        <div class="section">
            <h2>Work Experience</h2>
            @foreach($workExperiences as $exp)
                <div class="item">
                    <div class="row">
                        <span class="item-title">{{ $exp->experience_job_title }}</span>
                        @if(\App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months))
                            <span class="item-date">{{ \App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months) }}</span>
                        @endif
                    </div>
                    @if($exp->industry)
                        <p class="item-sub">{{ $exp->industry->industry_name }}</p>
                    @endif
                    @if($exp->experience_job_description)
                        <p>{{ $exp->experience_job_description }}</p>
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
                    <div class="row">
                        <span class="item-title">{{ $exp->experience_job_title }}</span>
                        @if(\App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months))
                            <span class="item-date">{{ \App\Models\Alumnus::formatExperienceDuration($exp->experience_duration_months) }}</span>
                        @endif
                    </div>
                    @if($exp->experience_job_description)
                        <p>{{ $exp->experience_job_description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($alumnus->certifications->isNotEmpty())
        <div class="section">
            <h2>Certifications &amp; Trainings</h2>
            @foreach($alumnus->certifications as $cert)
                <div class="row">
                    <span>
                        <span class="item-title">{{ $cert->certification_name }}</span>
                        @if($cert->certification_from)
                            &mdash; {{ $cert->certification_from }}
                        @endif
                        ({{ $certTypeLabels[$cert->certification_type] ?? ucfirst($cert->certification_type) }})
                    </span>
                    @if($cert->certification_date)
                        <span class="item-date">{{ $cert->certification_date?->format('M Y') }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</body>
</html>
