<!--<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body class="flex gap-[20px]">
    <div class="flex flex-col">
        <h1>Superadmin Dashboard</h1>
        <a href="{{ route('superAdmin.dashboard') }}">Dashboard</a>
        <br><br>
        <a href="{{ route('superAdmin.userManagement') }}">User Management</a>
        <br><br>
        <a href="{{ route('jobPosting.jobManagement') }}">Job Management</a>
        <br><br>
        <a href="">Alumni ID and yearbook management</a>
        <br><br>
        <a href="">Notice and events management</a>
        <br><br>
        <a href="">Chatbot and messaging management</a>
        <br><br>
        <a href="{{ route('testimonials.manage') }}">Testimonial management</a>
        <br><br>
        <a href="">Manage faqs</a>
        <br><br>
        <a href="{{ route('user.profile') }}">View Profile</a>
        <br><br><br>
        <form method="POST" action="{{ route('user.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
    <div>
        <h2>SYSTEM OVERVIEW</h2>
        <div class="flex gap-[20px]">
            <div>
                <h3>Job Placement Rate</h3>
                <p>{{ $stats['jobPlacementRate'] }}</p>
            </div>
            <div>
                <h3>Active job postings</h3>
                <p>{{ $stats['activeJobs'] }}</p>
            </div>
            <div>
                <h3>Industry Partners</h3>
                <p>{{ $stats['industryPartners'] }}</p>
            </div>
            <div>
                <h3>Alumni Users</h3>
                <p>{{ $stats['alumniUsers'] }}</p>
            </div>
        </div>
    </div>

</body>

</html>-->

@php
    $current_page = 'dashboard';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PLV-AlumNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-text {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        #sidebar:hover .sidebar-text {
            opacity: 1;
            pointer-events: auto;
        }

        /* ── Scrollable content area ── */
        .dash-scroll {
            overflow-y: auto;
            padding: 18px 22px 30px;
            flex: 1;
        }

        /* ── Filter bar ── */
        .filter-bar {
            background: #fff;
            border-radius: 10px;
            padding: 11px 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .filter-bar label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
        }

        .filter-bar input,
        .filter-bar select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 12px;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            color: #374151;
            min-width: 120px;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: #e05c00;
        }

        .btn-export {
            background: #c0392b;
            color: #fff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 7px 14px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            white-space: nowrap;
            margin-left: auto;
            font-family: 'Montserrat', sans-serif;
        }

        .btn-export:hover {
            background: #a93226;
        }

        /* ── Section heading ── */
        .section-heading {
            font-size: 20px;
            font-weight: bold;
        }

        .section-heading span {
            color: #e05c00;
        }

        /* ── Stat Cards ── */
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 18px 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        }

        .stat-card .s-top {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-card .s-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #0E0F3B;
            flex: 1;
        }

        .stat-badge {
            font-size: 11px;
            font-weight: 600;
        }

        .badge-up {
            color: #16a34a;
        }

        .badge-down {
            color: #dc2626;
        }

        .badge-flat {
            color: #6b7280;
        }

        .stat-card .s-value {
            font-size: 30px;
            font-weight: 700;
            color: #C73D1A;
            line-height: 1;
            margin-top: 8px;
        }

        .stat-card .s-icon {
            color: #0E0F3B;
        }

        /* ── Chart Cards ── */
        .chart-card {
            background: #fff;
            border-radius: 10px;
            padding: 16px 18px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        }

        .card-title {
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(to right, #0E0F3B, #C73D1A, #ED7A07);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;

        }

        .card-sub {
            font-size: 10.5px;
            color: #000000;
            margin-top: 2px;
        }

        /* ── Industry bars ── */
        .ind-bar-wrap {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-top: 10px;
        }

        .ind-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ind-label {
            font-size: 10px;
            color: #374151;
            width: 155px;
            flex-shrink: 0;
        }

        .ind-track {
            flex: 1;
            background: #f1f5f9;
            border-radius: 4px;
            height: 7px;
        }

        .ind-fill {
            height: 7px;
            border-radius: 4px;
            background: #e05c00;
        }

        .ind-pct {
            font-size: 10px;
            color: #374151;
            width: 26px;
            text-align: right;
        }

        /* ── Recent Activity ── */
        .activity-section-label {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin: 12px 0 4px;
        }

        .activity-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f1f5f9;
            padding: 8px 0;
            font-size: 12px;
            color: #374151;
        }

        .btn-edit {
            background: #e05c00;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-family: 'Montserrat', sans-serif;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            /* IE / Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">

        @include('partials.super-admin-side-bar')

        <main class="flex-1 flex flex-col overflow-hidden">

            @include('partials.super-admin-header')

            <!-- ════════════ DASHBOARD CONTENT ════════════ -->
            @include('partials.success')
            <div class="dash-scroll">
                <!-- Filter Section Container -->
                <div class="w-full bg-slate-100 px-6 py-4">
                    <div
                        class="flex items-center gap-8 bg-white px-8 py-4 rounded-xl border border-slate-200 shadow-md text-sm font-medium text-slate-700">

                        <!-- Batch Year -->
                        <div class="flex items-center gap-2">
                            <label class="whitespace-nowrap font-[Montserrat] font-semibold text-[#0E0F3B]">Batch
                                Year:</label>
                            <select
                                class="border border-slate-300 rounded-md px-3 py-1.5 w-36 focus:outline-none focus:ring-2 focus:ring-[#C73D1A] bg-white">
                                <option value="" selected disabled>Select Year</option>
                                <option value="2026">2026</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                            </select>
                        </div>

                        <!-- Course -->
                        <div class="flex items-center gap-2">
                            <label
                                class="whitespace-nowrap font-[Montserrat] font-semibold text-[#0E0F3B]">Course:</label>
                            <select
                                class="border border-slate-300 rounded-md px-3 py-1.5 w-72 focus:outline-none focus:ring-2 focus:ring-[#C73D1A] bg-white">
                                <option selected disabled>Select Undergraduate Program</option>

                                <option>Bachelor of Arts in Communication</option>
                                <option>Bachelor of Early Childhood Education</option>
                                <option>Bachelor of Science in Accountancy</option>

                                <optgroup label="BS in Business Administration">
                                    <option>BSBA - Major in Financial Management</option>
                                    <option>BSBA - Major in Human Resource Management</option>
                                    <option>BSBA - Major in Marketing Management</option>
                                </optgroup>

                                <option>BSCE - Bachelor of Science in Civil Engineering</option>
                                <option>BSEE - Bachelor of Science in Electrical Engineering</option>
                                <option>BSIT - Bachelor of Science in Information Technology</option>
                                <option>Bachelor of Science in Psychology</option>
                                <option>Bachelor of Public Administration</option>
                                <option>Bachelor of Science in Social Work</option>

                                <optgroup label="Bachelor of Secondary Education">
                                    <option>BSEd - Major in English</option>
                                    <option>BSEd - Major in Filipino</option>
                                    <option>BSEd - Major in Mathematics</option>
                                    <option>BSEd - Major in Science</option>
                                    <option>BSEd - Major in Social Studies</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Employment Status -->
                        <div class="flex items-center gap-2">
                            <label class="whitespace-nowrap font-[Montserrat] font-semibold text-[#0E0F3B]">Employment
                                Status:</label>
                            <select
                                class="border border-slate-300 rounded-md px-3 py-1.5 w-48 focus:outline-none focus:ring-2 focus:ring-[#C73D1A] bg-white">
                                <option value="" selected disabled>Select Status</option>
                                <option>Employed</option>
                                <option>Unemployed</option>
                            </select>
                        </div>

                        <!-- Export Button -->
                        <button
                            class="ml-auto bg-[#C04828] text-[8px] text-white px-4 py-1.5 rounded-md flex items-center gap-1.5 hover:bg-[#A03D22] transition shadow-sm font-semibold uppercase tracking-wide">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i> EXPORT CSV
                        </button>
                    </div>
                </div>

                <!-- System Overview Heading -->
                <div class="mb-2 ml-1">
                    <span
                        class="section-heading bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">System
                        Overview</span>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-4 gap-4 mb-4">

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                                <path d="M6 12v5c3 3 9 3 12 0v-5" />
                            </svg>
                            <span class="s-label">Total Alumni Users</span>
                            <!-- <span class="stat-badge badge-up">▲ +12.5%</span> -->
                        </div>
                        <div class="s-value">{{ $stats['alumniUsers']}}</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span class="s-label">Industry Partners</span>
                            <!--<span class="stat-badge badge-up">▲ +8.9%</span>-->
                        </div>
                        <div class="s-value">{{ $stats['industryPartners'] }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                <line x1="12" y1="12" x2="12" y2="16" />
                                <line x1="10" y1="14" x2="14" y2="14" />
                            </svg>
                            <span class="s-label">Active Job Postings</span>
                            <!--<span class="stat-badge badge-down">▼ -3.1%</span>-->
                        </div>
                        <div class="s-value">{{ $stats['activeJobs'] }}</div>
                    </div>

                    <div class="stat-card">
                        <div class="s-top">
                            <svg class="s-icon" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                                <circle cx="12" cy="12" r="2" />
                                <path d="M6 12H4M20 12h-2" />
                            </svg>
                            <span class="s-label">Job Placement Rate</span>
                            <!--<span class="stat-badge badge-flat">▶ +0.0%</span>-->
                        </div>
                        <div class="s-value">{{ $stats['jobPlacementRate'] }}%</div>
                    </div>

                </div>

                <!-- Row 2: 4 chart cards -->
                <div class="grid grid-cols-4 gap-4 mb-4">

                    <!-- Alumni Registration -->
                    <div class="chart-card">
                        <div class="card-title">Alumni <span>Registration</span></div>
                        <div class="card-sub">Total number of alumni who registered each month</div>
                        <div style="margin-top:10px; height:130px;">
                            <canvas id="chartRegistration"></canvas>
                        </div>
                    </div>

                    <!-- Networking Activity -->
                    <div class="chart-card">
                        <div class="card-title">Alumni <span>Networking Activity</span></div>
                        <div class="card-sub">Monthly connections & messages — Jan to Jun 2025</div>
                        <div style="margin-top:10px; height:130px;">
                            <canvas id="chartNetworking"></canvas>
                        </div>
                    </div>

                    <!-- Industry Distribution -->
                    <div class="chart-card">
                        <div class="card-title">Industry Distribution of <span>Employed Alumni</span></div>
                        <div class="card-sub">Top sectors where PLV alumni are hired</div>
                        <div class="ind-bar-wrap">
                            <div class="ind-row"><span class="ind-label">Information Technology</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:28%"></div>
                                </div><span class="ind-pct">28%</span>
                            </div>
                            <div class="ind-row"><span class="ind-label">Education & Training</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:21%"></div>
                                </div><span class="ind-pct">21%</span>
                            </div>
                            <div class="ind-row"><span class="ind-label">Healthcare & Medical</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:17%"></div>
                                </div><span class="ind-pct">17%</span>
                            </div>
                            <div class="ind-row"><span class="ind-label">Business & Finance</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:14%"></div>
                                </div><span class="ind-pct">14%</span>
                            </div>
                            <div class="ind-row"><span class="ind-label">Government & Public Service</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:11%"></div>
                                </div><span class="ind-pct">11%</span>
                            </div>
                            <div class="ind-row"><span class="ind-label">Others</span>
                                <div class="ind-track">
                                    <div class="ind-fill" style="width:9%"></div>
                                </div><span class="ind-pct">9%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Employer Approval Status -->
                    <div class="chart-card">
                        <div class="card-title">Employer Approval Status</div>
                        <div class="card-sub">Alumni account verification overview</div>
                        <div style="margin-top:10px; height:140px;">
                            <canvas id="chartApproval"></canvas>
                        </div>
                    </div>

                </div>

                <!-- Row 3: 3 chart cards -->
                <div class="grid grid-cols-3 gap-4 mb-4">

                    <!-- Job Placement Rate per Batch -->
                    <div class="chart-card">
                        <div class="card-title">Job Placement Rate per Batch/Year</div>
                        <div class="card-sub">Stacked employment status across graduation batches</div>
                        <div style="margin-top:10px; height:160px;">
                            <canvas id="chartPlacement"></canvas>
                        </div>
                    </div>

                    <!-- Employment Status Breakdown -->
                    <div class="chart-card">
                        <div class="card-title">Employment Status Breakdown</div>
                        <div class="card-sub">Overall distribution of all alumni and their employment status</div>
                        <div style="margin-top:10px; height:160px;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>

                    <!-- Job-to-Degree Alignment -->
                    <div class="chart-card">
                        <div class="card-title">Job-to-Degree Alignment by Program/Course</div>
                        <div class="card-sub">% of alumni whose job matches their degree field</div>
                        <div style="margin-top:10px; height:160px;">
                            <canvas id="chartAlignment"></canvas>
                        </div>
                    </div>

                </div>

                <!-- Row 4: Alumni ID Reports + Recent Activity -->
                <div class="grid grid-cols-2 gap-4">

                    <!-- Alumni ID & Yearbook Reports -->
                    <div class="chart-card">
                        <div class="card-title">Alumni ID & Yearbook Reports</div>
                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:6px;">Alumni ID
                                    Status</div>
                                <div style="height:130px;"><canvas id="chartAlumniID"></canvas></div>
                                <div style="font-size:9.5px;color:#6b7280;margin-top:8px;line-height:1.7;">
                                    Registered for ID: <strong>6,842 (80%)</strong><br>
                                    IDs Distributed: <strong>5,973 (69%)</strong>
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:6px;">Yearbook
                                    Distribution</div>
                                <div style="height:130px;"><canvas id="chartYearbook"></canvas></div>
                                <div style="font-size:9.5px;color:#6b7280;margin-top:8px;line-height:1.7;">
                                    Distributed: 53.4% &nbsp;<strong>1,247 (54.8%)</strong><br>
                                    Pending: 14.6% &nbsp;&nbsp;&nbsp;<strong>7,295 (15.4%)</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity & Updates -->
                    <div class="chart-card">
                        <div class="card-title">Recent Activity & Updates</div>
                        <div class="activity-section-label">Latest Event Posted:</div>
                        <div class="activity-row">
                            <span>PLV 23rd Founding Anniversary 2025</span>
                            <div class="flex items-center gap-3">
                                <span style="font-size:11px;color:#6b7280;">12-02-2025</span>
                                <button class="btn-edit">Edit</button>
                            </div>
                        </div>
                        <div class="activity-section-label">Recent Directory Updates:</div>
                        <div class="activity-row">
                            <span>John M. Santos (Profile Updated)</span>
                        </div>
                        <div class="activity-row">
                            <span>Mark Garcia (Profile Updated)</span>
                        </div>
                        <div class="activity-row"
                            style="color:#9ca3af;font-style:italic;font-size:11px;border-top:1px solid #f1f5f9;">
                            <span>No more recent updates</span>
                        </div>
                    </div>

                </div>

            </div>
            <!-- ════════════ END DASHBOARD CONTENT ════════════ -->

        </main>
    </div>


    <script>
        lucide.createIcons();

        const fontDef = {
            family: 'Montserrat',
            size: 10
        };
        const gridColor = 'rgba(0,0,0,0.05)';
        Chart.defaults.font = fontDef;
        Chart.defaults.color = '#6b7280';

        // 1. Alumni Registration — line
        new Chart(document.getElementById('chartRegistration'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Registered',
                    data: [12, 18, 14, 22, 30, 28, 20, 25, 32, 27, 19, 22],
                    borderColor: '#e05c00',
                    backgroundColor: 'rgba(224,92,0,.1)',
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            font: fontDef
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            font: fontDef
                        }
                    }
                }
            }
        });

        // 2. Networking Activity — dual line
        new Chart(document.getElementById('chartNetworking'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Connections',
                    data: [120, 180, 160, 230, 290, 340],
                    borderColor: '#e05c00',
                    backgroundColor: 'rgba(224,92,0,.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Messages',
                    data: [200, 250, 220, 300, 380, 450],
                    borderColor: '#1a3a6e',
                    backgroundColor: 'rgba(26,58,110,.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: true,
                    tension: 0.4
                }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: fontDef,
                            boxWidth: 10
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            font: fontDef
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            font: fontDef
                        }
                    }
                }
            }
        });

        // 3. Employer Approval Status — donut
        new Chart(document.getElementById('chartApproval'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [72, 18, 10],
                    backgroundColor: ['#16a34a', '#e05c00', '#dc2626'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            font: fontDef,
                            boxWidth: 10
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // 4. Job Placement Rate per Batch — stacked bar
        new Chart(document.getElementById('chartPlacement'), {
            type: 'bar',
            data: {
                labels: ['2022', '2023', '2024', '2025', '2026'],
                datasets: [{
                    label: 'Unemployed',
                    data: [30, 25, 28, 35, 20],
                    backgroundColor: '#e05c00'
                },
                {
                    label: 'Employed',
                    data: [70, 75, 72, 65, 80],
                    backgroundColor: '#1a3a6e'
                }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: fontDef,
                            boxWidth: 10
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: fontDef
                        }
                    },
                    y: {
                        stacked: true,
                        grid: {
                            color: gridColor
                        },
                        max: 100,
                        ticks: {
                            font: fontDef,
                            callback: v => v + '%'
                        }
                    }
                }
            }
        });

        // 5. Employment Status Breakdown — donut
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: ['Employed', 'Unemployed'],
                datasets: [{
                    data: [68, 32],
                    backgroundColor: ['#1a3a6e', '#94a3b8'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: fontDef,
                            boxWidth: 10
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // 6. Job-to-Degree Alignment — horizontal bar
        new Chart(document.getElementById('chartAlignment'), {
            type: 'bar',
            data: {
                labels: ['BSEd', 'BSN', 'BSBA', 'BSIT', 'BSCS', 'BSPSYCH', 'BSEE'],
                datasets: [{
                    label: 'Alignment %',
                    data: [88, 92, 74, 85, 91, 67, 78],
                    backgroundColor: '#e05c00',
                    borderRadius: 3
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        max: 100,
                        ticks: {
                            font: fontDef,
                            callback: v => v + '%'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: fontDef
                        }
                    }
                }
            }
        });

        // 7. Alumni ID Status — bar
        new Chart(document.getElementById('chartAlumniID'), {
            type: 'bar',
            data: {
                labels: ['Not Registered', 'Registered', 'Distributed'],
                datasets: [{
                    data: [1500, 5973, 6842],
                    backgroundColor: ['#dc2626', '#e05c00', '#16a34a'],
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Montserrat',
                                size: 9
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            font: {
                                family: 'Montserrat',
                                size: 9
                            }
                        }
                    }
                }
            }
        });

        // 8. Yearbook Distribution — donut
        new Chart(document.getElementById('chartYearbook'), {
            type: 'doughnut',
            data: {
                labels: ['Distributed', 'Pending', 'Not Claimed'],
                datasets: [{
                    data: [53.4, 14.6, 32],
                    backgroundColor: ['#16a34a', '#e05c00', '#94a3b8'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Montserrat',
                                size: 9
                            },
                            boxWidth: 8
                        }
                    }
                },
                cutout: '55%'
            }
        });
    </script>

</body>

</html>