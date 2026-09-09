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
        .section { page-break-inside: avoid; }
    </style>
</head>

<body>
    <h1>PLV-AlumNet &mdash; {{ $title }}</h1>
    <p class="meta">Generated {{ now()->format('M d, Y h:i A') }} &mdash; {{ count($tableRows) }} record(s)</p>

    <div class="section">
        <table>
            <tr>
                @foreach ($tableColumns as $col)
                <th>{{ $col['label'] }}</th>
                @endforeach
            </tr>
            @forelse ($tableRows as $row)
            <tr>
                @foreach ($tableColumns as $col)
                <td>{{ $row[$col['key']] ?? '' }}</td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($tableColumns) }}">No records for the current filters.</td></tr>
            @endforelse
        </table>
    </div>
</body>

</html>
