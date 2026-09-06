<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; }
        h1 { font-size: 18px; color: #0E0F3B; margin-bottom: 2px; }
        .meta { font-size: 10px; color: #555; margin-bottom: 16px; }
        h2 { font-size: 13px; color: #fff; background-color: #0E0F3B; padding: 6px 10px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 8px; text-align: left; font-size: 10px; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .stat-table td:first-child { font-weight: bold; width: 60%; }
        .subheading { font-weight: bold; margin-top: 8px; margin-bottom: 4px; }
        .section { page-break-inside: avoid; }
        .chart-img { width: 100%; max-height: 260px; object-fit: contain; margin-bottom: 6px; }
    </style>
</head>

<body>
    @php
        $titles = [
            'employment' => 'Employment & Alignment Report',
            'placement' => 'Job Placement & Hiring Report',
            'companies' => 'Alumni & Company Registration Reports',
            'alumniid' => 'Alumni ID & Yearbook Report',
            'networking' => 'Networking Activity Report',
        ];
    @endphp
    <h1>PLV-AlumNet &mdash; {{ $titles[$group] }}</h1>
    <p class="meta">
        Generated {{ now()->format('M d, Y h:i A') }}<br>
        @if (in_array($group, ['employment', 'placement', 'companies']))
        Filters &mdash; Batch: {{ $batchLabel }} | Program: {{ $programLabel }} | Employment Status: {{ $statusLabel }} | College: {{ $collegeLabel }} | Year: {{ $yearLabel }}
        @elseif ($group === 'alumniid')
        Filters &mdash; Batch: {{ $batchLabel }} | Program: {{ $programLabel }} | College: {{ $collegeLabel }}
        @elseif ($group === 'networking')
        Range &mdash; Last {{ $networkMonths }} months
        @endif
    </p>

    @if ($group === 'employment')
    <div class="section">
        <h2>Overview</h2>
        <table class="stat-table">
            <tr><td>Employment Rate</td><td>{{ $r['employmentRate'] }}%</td></tr>
            <tr><td>Unemployment Rate</td><td>{{ $r['unemploymentRate'] }}%</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Employment Status Breakdown</h2>
        @if (!empty($charts['chartStatus']))
            <img class="chart-img" src="{{ $charts['chartStatus'] }}">
        @else
            <table>
                <tr><th>Status</th><th>Count</th></tr>
                <tr><td>Employed</td><td>{{ $r['employedCount'] }}</td></tr>
                <tr><td>Unemployed</td><td>{{ $r['totalAlumni'] - $r['employedCount'] }}</td></tr>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Employment Rate by Batch/Year</h2>
        @if (!empty($charts['chartPlacement']))
            <img class="chart-img" src="{{ $charts['chartPlacement'] }}">
        @else
        <table>
            <tr><th>Batch</th><th>Total</th><th>Employed</th><th>Rate</th></tr>
            @foreach ($r['employmentByBatch'] as $batchYear => $row)
                <tr><td>{{ $batchYear }}</td><td>{{ $row['total'] }}</td><td>{{ $row['employed'] }}</td><td>{{ $row['rate'] }}%</td></tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Employment by Month</h2>
        @if (!empty($charts['chartEmploymentByMonth']))
            <img class="chart-img" src="{{ $charts['chartEmploymentByMonth'] }}">
        @else
        <table>
            <tr><th>Month</th><th>Alumni Employed</th></tr>
            @foreach ($r['employmentByMonth'] as $month => $count)
                <tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Industry Distribution of Employed Alumni</h2>
        <table>
            <tr><th>Industry</th><th>Employed Count</th></tr>
            @foreach ($r['industryDistribution'] as $industry => $count)
                <tr><td>{{ $industry }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Employment Rate by Gender</h2>
        @if (!empty($charts['chartGender']))
            <img class="chart-img" src="{{ $charts['chartGender'] }}">
        @else
        <table>
            <tr><th>Gender</th><th>Total</th><th>Employed</th><th>Rate</th></tr>
            @foreach ($r['genderEmployment'] as $row)
                <tr><td>{{ $row['label'] }}</td><td>{{ $row['total'] }}</td><td>{{ $row['employed'] }}</td><td>{{ $row['rate'] }}%</td></tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Job-to-Degree Alignment by Program (Overall: {{ $r['alignmentRate'] }}%)</h2>
        @if (!empty($charts['chartAlignment']))
            <img class="chart-img" src="{{ $charts['chartAlignment'] }}">
        @else
        <table>
            <tr><th>Program</th><th>Employed</th><th>Aligned</th><th>Rate</th></tr>
            @foreach ($r['programAlignment'] as $program => $row)
                <tr><td>{{ $program }}</td><td>{{ $row['total'] }}</td><td>{{ $row['aligned'] }}</td><td>{{ $row['rate'] }}%</td></tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Employment Interval (Graduation to First Job)</h2>
        @if (!empty($charts['chartInterval']))
            <img class="chart-img" src="{{ $charts['chartInterval'] }}">
        @else
        <table>
            <tr><th>Interval</th><th>Alumni</th></tr>
            @foreach ($r['employmentInterval'] as $bucket => $count)
                <tr><td>{{ $bucket }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Job Before Graduation &amp; Internships</h2>
        @if (!empty($charts['chartJobBeforeGrad']))
            <img class="chart-img" src="{{ $charts['chartJobBeforeGrad'] }}">
        @else
        <table class="stat-table">
            <tr><td>Employed Before Graduation</td><td>{{ $r['beforeGraduationCount'] }} ({{ $r['beforeGraduationRate'] }}%)</td></tr>
            <tr><td>First Job Was an Internship</td><td>{{ $r['internshipCount'] }} ({{ $r['internshipRate'] }}%)</td></tr>
            <tr><td>Before Graduation AND an Internship</td><td>{{ $r['beforeGraduationInternshipCount'] }}</td></tr>
        </table>
        @endif
    </div>
    @endif

    @if ($group === 'placement')
    <div class="section">
        <h2>Overview</h2>
        <table class="stat-table">
            <tr><td>Job Placement Rate</td><td>{{ $stats['jobPlacementRate'] }}%</td></tr>
            <tr><td>Total Applications</td><td>{{ $r['totalApplications'] }}</td></tr>
            <tr><td>Total Hired</td><td>{{ $r['totalHired'] }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Top {{ $topCompaniesLimit }} Hiring Companies</h2>
        <table>
            <tr><th>Company</th><th>Hires</th></tr>
            @foreach ($r['topHiringCompanies'] as $row)
                <tr><td>{{ $row->job_posting_company }}</td><td>{{ $row->hires }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Hires per Month {{ $yearLabel !== 'All' ? '(' . $yearLabel . ')' : '(last ' . $hireMonths . ' months)' }}</h2>
        @if (!empty($charts['chartHires']))
            <img class="chart-img" src="{{ $charts['chartHires'] }}">
        @else
        <table>
            <tr><th>Month</th><th>Hires</th></tr>
            @foreach ($r['hiresPerMonth'] as $month => $count)
                <tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </table>
        @endif
    </div>
    @endif

    @if ($group === 'companies')
    <div class="section">
        <h2>Employed Alumni Report</h2>
        <table>
            <tr>
                <th>Name</th><th>Batch</th><th>Program</th><th>College</th><th>Workplace</th><th>Position</th><th>Industry</th><th>Employment Date</th><th>Aligned</th>
            </tr>
            @foreach ($r['employedAlumniTable'] as $a)
                <tr>
                    <td>{{ trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')) }}</td>
                    <td>{{ optional($a->alumnus_batch)->format('Y-m-d') }}</td>
                    <td>{{ $a->program->program_name ?? 'N/A' }}</td>
                    <td>{{ $a->program?->collegeName() ?? 'N/A' }}</td>
                    <td>{{ $a->alumnus_workplace_undisclosed ? 'Undisclosed' : ($a->alumnus_workplace ?? 'N/A') }}</td>
                    <td>{{ $a->alumnus_job_position ?? 'N/A' }}</td>
                    <td>{{ $a->industry->industry_name ?? 'N/A' }}</td>
                    <td>{{ optional($a->alumnus_employment_date)->format('M d, Y') ?? 'N/A' }}</td>
                    <td>{{ $a->alumnus_employment_status ? ($a->hasCourseAlignedJob() ? 'Aligned' : 'Not Aligned') : '' }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Registered Companies ({{ $r['registeredCompanies']->count() }})</h2>
        <table>
            <tr><th>Company</th><th>Industry</th><th>Contact</th></tr>
            @foreach ($r['registeredCompanies'] as $employer)
                <tr><td>{{ $employer->employer_company_name }}</td><td>{{ $employer->industry->industry_name ?? 'N/A' }}</td><td>{{ $employer->user->user_email ?? 'N/A' }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Pending / Unregistered Companies ({{ $r['pendingCompanies']->count() }})</h2>
        <table>
            <tr><th>Company</th><th>Industry</th><th>Contact</th></tr>
            @foreach ($r['pendingCompanies'] as $employer)
                <tr><td>{{ $employer->employer_company_name }}</td><td>{{ $employer->industry->industry_name ?? 'N/A' }}</td><td>{{ $employer->user->user_email ?? 'N/A' }}</td></tr>
            @endforeach
        </table>
    </div>
    @endif

    @if ($group === 'alumniid')
    <div class="section">
        <h2>Alumni ID Status</h2>
        @if (!empty($charts['chartAlumniID']))
            <img class="chart-img" src="{{ $charts['chartAlumniID'] }}">
        @endif
        <table class="stat-table">
            @foreach ($r['alumniIdCounts'] as $status => $count)
                <tr><td>{{ ucwords(str_replace('_', ' ', $status)) }}</td><td>{{ $count }} ({{ $r['alumniIdTotal'] > 0 ? round($count / $r['alumniIdTotal'] * 100) : 0 }}%)</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Yearbook Claiming Status</h2>
        @if (!empty($charts['chartYearbook']))
            <img class="chart-img" src="{{ $charts['chartYearbook'] }}">
        @endif
        <table class="stat-table">
            @foreach ($r['yearbookCounts'] as $status => $count)
                <tr><td>{{ ucwords(str_replace('_', ' ', $status)) }}</td><td>{{ $count }} ({{ $r['alumniIdTotal'] > 0 ? round($count / $r['alumniIdTotal'] * 100) : 0 }}%)</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Alumni ID Status by Batch</h2>
        <table>
            <tr><th>Batch</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
            @foreach ($r['alumniIdByBatch'] as $batch => $row)
                <tr><td>{{ $batch }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Yearbook Status by Batch</h2>
        <table>
            <tr><th>Batch</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
            @foreach ($r['yearbookByBatch'] as $batch => $row)
                <tr><td>{{ $batch }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Alumni ID Status by Program</h2>
        <table>
            <tr><th>Program</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
            @foreach ($r['alumniIdByProgram'] as $program => $row)
                <tr><td>{{ $program }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Yearbook Status by Program</h2>
        <table>
            <tr><th>Program</th><th>Total</th><th>Pending</th><th>Ready to Claim</th><th>Claimed</th></tr>
            @foreach ($r['yearbookByProgram'] as $program => $row)
                <tr><td>{{ $program }}</td><td>{{ $row['total'] }}</td><td>{{ $row['pending'] }}</td><td>{{ $row['ready_to_claim'] }}</td><td>{{ $row['claimed'] }}</td></tr>
            @endforeach
        </table>
    </div>
    @endif

    @if ($group === 'networking')
    <div class="section">
        <h2>Overview</h2>
        <table class="stat-table">
            <tr><td>Total Conversations</td><td>{{ $r['totalConversations'] }}</td></tr>
            <tr><td>Total Messages</td><td>{{ $r['totalMessages'] }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Monthly Activity (Last {{ $networkMonths }} Months)</h2>
        @if (!empty($charts['chartNetworking']))
            <img class="chart-img" src="{{ $charts['chartNetworking'] }}">
        @endif
        <table>
            <tr><th>Month</th><th>New Conversations</th><th>Messages Sent</th></tr>
            @foreach ($r['monthlyConversations'] as $month => $count)
                <tr><td>{{ $month }}</td><td>{{ $count }}</td><td>{{ $r['monthlyMessages'][$month] ?? 0 }}</td></tr>
            @endforeach
        </table>
    </div>
    @endif
</body>

</html>
