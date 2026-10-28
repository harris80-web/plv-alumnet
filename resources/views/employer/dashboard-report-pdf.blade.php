<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    {{-- Same look as superAdmin/dashboard-report-pdf.blade.php (DomPDF). --}}
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

        .num {
            text-align: right;
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

        .muted {
            color: #6b7280;
        }
    </style>
</head>

<body>
    <h1>PLV-AlumNet &mdash; Employer Dashboard Report</h1>
    <p class="meta">
        {{ $employer->employer_company_name ?? 'Your Company' }}<br>
        Generated {{ now(config('app.local_timezone'))->format('M d, Y h:i A') }}
    </p>

    <div class="section">
        <h2>Overview</h2>
        <table class="stat-table">
            <tr><td>Active Job Postings</td><td>{{ $stats['activePostings'] }}</td></tr>
            <tr><td>Total Applicants</td><td>{{ $stats['totalApplicants'] }} ({{ $stats['unreadApplicants'] }} unread)</td></tr>
            <tr><td>Shortlisted</td><td>{{ $stats['shortlisted'] }}</td></tr>
            <tr><td>Hired via AlumNet</td><td>{{ $stats['hired'] }}</td></tr>
            <tr><td>Pending Review</td><td>{{ $stats['pending'] }}</td></tr>
            <tr><td>Job Postings Expiring Soon (next 7 days)</td><td>{{ $stats['expiringSoon'] }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Applicant Traffic by Job Posting</h2>
        @if ($jobPostings->isEmpty())
            <p class="muted">No job postings yet.</p>
        @else
        <table>
            <tr>
                <th>Job Posting</th>
                <th>Status</th>
                <th>Posted</th>
                <th class="num">Applicants</th>
            </tr>
            @foreach ($jobPostings as $job)
            <tr>
                <td>{{ $job->job_posting_title }}</td>
                <td>{{ $job->myPostStatusLabel() }}</td>
                <td>{{ $job->created_at->format('M d, Y') }}</td>
                <td class="num">{{ $job->applications_count }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Application Status Breakdown</h2>
        @if (!empty($charts['chartApplicationStatus']))
            <img class="chart-img" src="{{ $charts['chartApplicationStatus'] }}">
        @endif
        <table>
            <tr><th>Status</th><th class="num">Applicants</th><th class="num">Share</th></tr>
            @php $statusTotal = array_sum($statusBreakdown); @endphp
            @foreach ($statusBreakdown as $label => $count)
            <tr>
                <td>{{ $label }}</td>
                <td class="num">{{ $count }}</td>
                <td class="num">{{ $statusTotal ? round($count / $statusTotal * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h2>Applications &amp; Hires per Month (last 6 months)</h2>
        @if (!empty($charts['chartMonthlyActivity']))
            <img class="chart-img" src="{{ $charts['chartMonthlyActivity'] }}">
        @endif
        <table>
            <tr><th>Month</th><th class="num">Applications Received</th><th class="num">Applicants Hired</th></tr>
            @foreach ($monthlyActivity['labels'] as $i => $month)
            <tr>
                <td>{{ $month }}</td>
                <td class="num">{{ $monthlyActivity['applications'][$i] }}</td>
                <td class="num">{{ $monthlyActivity['hires'][$i] }}</td>
            </tr>
            @endforeach
        </table>
    </div>
</body>

</html>
