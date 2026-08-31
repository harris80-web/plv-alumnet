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
    </style>
</head>

<body>
    <h1>PLV-AlumNet &mdash; Admin Dashboard Report</h1>
    <p class="meta">
        Generated {{ now()->format('M d, Y h:i A') }}<br>
        Filters &mdash; Batch: {{ $batchLabel }} | Program: {{ $programLabel }} | Employment Status: {{ $statusLabel }}
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
        <h2>Employment Rate by Batch/Year</h2>
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
    </div>

    <div class="section">
        <h2>Employment by Month (Jan&ndash;Dec, all years)</h2>
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
    </div>

    <div class="section">
        <h2>Job-to-Degree Alignment by Program (Overall: {{ $r['alignmentRate'] }}%)</h2>
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
    </div>

    <div class="section">
        <h2>Employment Interval (Graduation to First Job)</h2>
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
        <p class="subheading">Top Hiring Companies</p>
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
        <p class="subheading">Hires per Month (last {{ $hireMonths }} months)</p>
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
    </div>

    <div class="section">
        <h2>Employed Alumni Report</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Batch</th>
                <th>Program</th>
                <th>Workplace</th>
                <th>Position</th>
                <th>Industry</th>
                <th>Employment Date</th>
                <th>Aligned</th>
            </tr>
            @foreach ($r['employedAlumniTable'] as $a)
                <tr>
                    <td>{{ trim(($a->user->user_first_name ?? '') . ' ' . ($a->user->user_last_name ?? '')) }}</td>
                    <td>{{ optional($a->alumnus_batch)->format('Y-m-d') }}</td>
                    <td>{{ $a->program->program_name ?? 'N/A' }}</td>
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
            <tr>
                <th>Company</th>
                <th>Industry</th>
                <th>Contact</th>
            </tr>
            @foreach ($r['registeredCompanies'] as $employer)
                <tr>
                    <td>{{ $employer->employer_company_name }}</td>
                    <td>{{ $employer->industry->industry_name ?? 'N/A' }}</td>
                    <td>{{ $employer->user->user_email ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Pending / Unregistered Companies ({{ $r['pendingCompanies']->count() }})</h2>
        <table>
            <tr>
                <th>Company</th>
                <th>Industry</th>
                <th>Contact</th>
            </tr>
            @foreach ($r['pendingCompanies'] as $employer)
                <tr>
                    <td>{{ $employer->employer_company_name }}</td>
                    <td>{{ $employer->industry->industry_name ?? 'N/A' }}</td>
                    <td>{{ $employer->user->user_email ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>

</html>
