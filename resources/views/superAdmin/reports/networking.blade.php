@php
    $current_page = 'reports';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Networking Activity Report | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .dash-scroll { overflow-y: auto; padding: 18px 22px 30px; flex: 1; }
        .stat-card { background: #fff; border-radius: 10px; padding: 16px 18px 14px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .stat-card .s-label { font-size: 12.5px; font-weight: 600; color: #0E0F3B; }
        .stat-card .s-value { font-size: 30px; font-weight: 700; color: #C73D1A; line-height: 1; margin-top: 8px; }
        .chart-card { background: #fff; border-radius: 10px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-title { font-size: 13px; font-weight: 700; background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block; }
        .card-sub { font-size: 10.5px; color: #000; margin-top: 2px; }
        table.report-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; }
        table.report-table th, table.report-table td { border-bottom: 1px solid #f1f5f9; padding: 6px 8px; text-align: left; }
        table.report-table th { color: #0E0F3B; font-weight: 700; background: #f8fafc; }
        ::-webkit-scrollbar { display: none; }
        * { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden">
            @include('partials.super-admin-header')

            <div class="dash-scroll">
                <div class="mb-4 mt-3 flex items-center justify-between flex-wrap gap-3">
                    <a href="{{ route('superAdmin.dashboard') }}" class="text-xs font-normal text-[#C73D1A] border border-[#C73D1A] rounded-lg px-3 py-1 flex items-center gap-1 transition-colors duration-200 hover:bg-[#C73D1A] hover:text-white">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Return to Dashboard
                    </a>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="exportPdfWithCharts()"
                            class="shrink-0 whitespace-nowrap bg-[#C04828] text-[10px] text-white px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#A03D22] transition shadow-sm font-semibold uppercase tracking-wide">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 shrink-0"></i> EXPORT PDF
                        </button>
                        <a href="{{ route('reports.exportCsv', ['group' => 'networking', 'network_months' => $networkMonths]) }}"
                            class="shrink-0 whitespace-nowrap border border-[#0E0F3B] text-[#0E0F3B] text-[10px] px-3 py-1.5 rounded-md flex items-center gap-1 hover:bg-[#0E0F3B] hover:text-white transition shadow-sm font-semibold uppercase tracking-wide">
                            <i data-lucide="download" class="w-3.5 h-3.5 shrink-0"></i> EXPORT CSV
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 my-4">
                    <div class="stat-card">
                        <div class="s-label">Total Conversations</div>
                        <div class="s-value">{{ number_format($r['totalConversations']) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="s-label">Total Messages</div>
                        <div class="s-value">{{ number_format($r['totalMessages']) }}</div>
                    </div>
                </div>

                <div class="chart-card mb-4">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <div class="card-title">Alumni Networking Activity</div>
                            <div class="card-sub">New conversation threads started & messages sent, by month</div>
                        </div>
                        <select id="networkRangeSelect" onchange="updateNetworkRange(this.value)"
                            style="border:1px solid #d1d5db; border-radius:6px; padding:5px 10px; font-size:12px; font-family:'Montserrat',sans-serif;">
                            <option value="3" {{ $networkMonths === 3 ? 'selected' : '' }}>Last 3 months</option>
                            <option value="6" {{ $networkMonths === 6 ? 'selected' : '' }}>Last 6 months</option>
                            <option value="12" {{ $networkMonths === 12 ? 'selected' : '' }}>Last 12 months</option>
                            <option value="24" {{ $networkMonths === 24 ? 'selected' : '' }}>Last 24 months</option>
                        </select>
                    </div>
                    <div style="margin-top:10px; height:260px;"><canvas id="chartNetworking"></canvas></div>
                </div>

                <div class="chart-card mb-4">
                    <div class="card-title">Monthly Breakdown</div>
                    <table class="report-table">
                        <tr><th>Month</th><th>New Conversations</th><th>Messages Sent</th></tr>
                        @foreach ($r['monthlyConversations'] as $month => $count)
                        <tr><td>{{ $month }}</td><td>{{ $count }}</td><td>{{ $r['monthlyMessages'][$month] ?? 0 }}</td></tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        const fontDef = { family: 'Montserrat', size: 10 };
        const gridColor = 'rgba(0,0,0,0.05)';
        Chart.defaults.font = fontDef;
        Chart.defaults.color = '#6b7280';

        const monthlyConversations = @json($r['monthlyConversations']);
        const monthlyMessages = @json($r['monthlyMessages']);
        new Chart(document.getElementById('chartNetworking'), {
            type: 'line',
            data: {
                labels: Object.keys(monthlyConversations),
                datasets: [
                    { label: 'New Conversations', data: Object.values(monthlyConversations), borderColor: '#1D46A4', backgroundColor: 'rgba(29,70,164,.08)', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.3 },
                    { label: 'Messages Sent', data: Object.values(monthlyMessages), borderColor: '#e05c00', backgroundColor: 'rgba(224,92,0,.08)', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.3 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { font: { family: 'Montserrat', size: 10 }, boxWidth: 10 } } },
                scales: { x: { grid: { color: gridColor } }, y: { grid: { color: gridColor }, ticks: { precision: 0 } } },
            },
        });

        function updateNetworkRange(months) {
            const url = new URL(window.location.href);
            url.searchParams.set('network_months', months);
            window.location.href = url.toString();
        }

        const EXPORTABLE_CHART_IDS = ['chartNetworking'];
        function exportPdfWithCharts() {
            const charts = {};
            EXPORTABLE_CHART_IDS.forEach(id => {
                const canvas = document.getElementById(id);
                if (canvas) { try { charts[id] = canvas.toDataURL('image/png'); } catch (e) {} }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = {!! json_encode(route('reports.exportPdf.post', ['group' => 'networking'])) !!};
            form.style.display = 'none';
            const addField = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = value; form.appendChild(i); };
            addField('_token', '{{ csrf_token() }}');
            addField('charts', JSON.stringify(charts));
            addField('network_months', document.getElementById('networkRangeSelect')?.value || '{{ $networkMonths }}');
            document.body.appendChild(form);
            form.submit();
            form.remove();
        }
    </script>
</body>

</html>
