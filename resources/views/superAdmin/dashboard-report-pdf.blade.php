<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a2e;
        }

        h1 {
            font-size: 18px;
            color: #0E0F3B;
            margin-bottom: 2px;
        }

        .meta {
            font-size: 10px;
            color: #555;
            margin-bottom: 16px;
        }

        h2 {
            font-size: 13px;
            color: #ffffff;
            background-color: #0E0F3B;
            padding: 6px 10px;
            margin: 18px 0 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 4px 8px;
            text-align: left;
            font-size: 10px;
        }

        th {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        .stat-table td:first-child {
            font-weight: bold;
            width: 60%;
        }

        .subheading {
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .section {
            page-break-inside: avoid;
        }

        .chart-img {
            width: 100%;
            max-height: 260px;
            object-fit: contain;
            margin-bottom: 6px;
        }
    </style>
</head>

<body>
    <h1>PLV-AlumNet &mdash; Admin Dashboard Report</h1>
    <p class="meta">
        Generated {{ now()->format('M d, Y h:i A') }}<br>
        Filters &mdash; Batch: {{ $batchLabel }} | Program: {{ $programLabel }} | Employment Status: {{ $statusLabel }} | College: {{ $collegeLabel }} | Year: {{ $yearLabel }}
    </p>

    <div class="section">
        <h2>Overview</h2>
        <table class="stat-table">
            <tr>
                <td>Total Alumni Users</td>
                <td>{{ $stats['alumniUsers'] }}</td>
            </tr>
            <tr>
                <td>Employment Rate</td>
                <td>{{ $r['employmentRate'] }}%</td>
            </tr>
            <tr>
                <td>Unemployment Rate</td>
                <td>{{ $r['unemploymentRate'] }}%</td>
            </tr>
            <tr>
                <td>Industry Partners</td>
                <td>{{ $stats['industryPartners'] }}</td>
            </tr>
            <tr>
                <td>Active Job Postings</td>
                <td>{{ $stats['activeJobs'] }}</td>
            </tr>
            <tr>
                <td>Job Placement Rate</td>
                <td>{{ $stats['jobPlacementRate'] }}%</td>
            </tr>
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
            <tr>
                <th>Batch</th>
                <th>Total</th>
                <th>Employed</th>
                <th>Rate</th>
            </tr>
            @foreach ($r['employmentByBatch'] as $batchYear => $row)
                <tr>
                    <td>{{ $batchYear }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['employed'] }}</td>
                    <td>{{ $row['rate'] }}%</td>
                </tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Employment by Month (Jan&ndash;Dec{{ $yearLabel !== 'All' ? ', ' . $yearLabel : ', pooled across employment years' }})</h2>
        <p class="subheading">Batch: {{ $batchLabel }} | Program: {{ $programLabel }}</p>
        @if (!empty($charts['chartEmploymentByMonth']))
            <img class="chart-img" src="{{ $charts['chartEmploymentByMonth'] }}">
        @else
        <table>
            <tr>
                <th>Month</th>
                <th>Alumni Employed</th>
            </tr>
            @foreach ($r['employmentByMonth'] as $month => $count)
                <tr>
                    <td>{{ $month }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Industry Distribution of Employed Alumni</h2>
        <table>
            <tr>
                <th>Industry</th>
                <th>Employed Count</th>
            </tr>
            @foreach ($r['industryDistribution'] as $industry => $count)
                <tr>
                    <td>{{ $industry }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Employment Rate by Gender</h2>
        @if (!empty($charts['chartGender']))
            <img class="chart-img" src="{{ $charts['chartGender'] }}">
        @else
        <table>
            <tr>
                <th>Gender</th>
                <th>Total</th>
                <th>Employed</th>
                <th>Rate</th>
            </tr>
            @foreach ($r['genderEmployment'] as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['employed'] }}</td>
                    <td>{{ $row['rate'] }}%</td>
                </tr>
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
            <tr>
                <th>Program</th>
                <th>Employed</th>
                <th>Aligned</th>
                <th>Rate</th>
            </tr>
            @foreach ($r['programAlignment'] as $program => $row)
                <tr>
                    <td>{{ $program }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['aligned'] }}</td>
                    <td>{{ $row['rate'] }}%</td>
                </tr>
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
            <tr>
                <th>Interval</th>
                <th>Alumni</th>
            </tr>
            @foreach ($r['employmentInterval'] as $bucket => $count)
                <tr>
                    <td>{{ $bucket }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Job Before Graduation &amp; Internships</h2>
        <p class="subheading">Of alumni with a recorded first job</p>
        @if (!empty($charts['chartJobBeforeGrad']))
            <img class="chart-img" src="{{ $charts['chartJobBeforeGrad'] }}">
        @else
        <table class="stat-table">
            <tr>
                <td>Employed Before Graduation</td>
                <td>{{ $r['beforeGraduationCount'] }} ({{ $r['beforeGraduationRate'] }}%)</td>
            </tr>
            <tr>
                <td>First Job Was an Internship</td>
                <td>{{ $r['internshipCount'] }} ({{ $r['internshipRate'] }}%)</td>
            </tr>
            <tr>
                <td>Before Graduation AND an Internship</td>
                <td>{{ $r['beforeGraduationInternshipCount'] }}</td>
            </tr>
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Job Placement &amp; Hiring</h2>
        <table class="stat-table">
            <tr>
                <td>Total Applications</td>
                <td>{{ $r['totalApplications'] }}</td>
            </tr>
            <tr>
                <td>Total Hired</td>
                <td>{{ $r['totalHired'] }}</td>
            </tr>
        </table>
        <p class="subheading">Top {{ $topCompaniesLimit }} Hiring Companies</p>
        <table>
            <tr>
                <th>Company</th>
                <th>Hires</th>
            </tr>
            @foreach ($r['topHiringCompanies'] as $row)
                <tr>
                    <td>{{ $row->job_posting_company }}</td>
                    <td>{{ $row->hires }}</td>
                </tr>
            @endforeach
        </table>
        <p class="subheading">Hires per Month {{ $yearLabel !== 'All' ? '(' . $yearLabel . ')' : '(last ' . $hireMonths . ' months)' }}</p>
        @if (!empty($charts['chartHires']))
            <img class="chart-img" src="{{ $charts['chartHires'] }}">
        @else
        <table>
            <tr>
                <th>Month</th>
                <th>Hires</th>
            </tr>
            @foreach ($r['hiresPerMonth'] as $month => $count)
                <tr>
                    <td>{{ $month }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
        </table>
        @endif
    </div>

    {{-- Employed Alumni Report / Registered & Pending Companies are no
         longer shown on the main dashboard (see reports.companies for their
         own dedicated, filterable, exportable page) — this export mirrors
         exactly what's on screen, so it drops them too. --}}
</body>

</html>
