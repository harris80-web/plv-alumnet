<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
    <div>
        <h2>JOB</h2>
        <p>{{ $jobPost->job_posting_title }}</p>
        <p>{{ $jobPost->job_posting_company }}</p>
        <p>{{ $jobPost->job_posting_address }}</p>
        <p>{{ $jobPost->job_posting_employment_type }}</p>
        <p>{{ $jobPost->job_posting_setup }}</p>
        <p>{{ $jobPost->job_posting_description }}</p>
        <p>{{ $jobPost->job_closing_date }}</p>
        @foreach ($jobPost->programs as $program)
            <p>{{ $program->program_name }}</p>
        @endforeach
        
        <img src="{{ asset("storage/" . $jobPost->job_posting_image) }}" alt="" class="w-[100px] h-[100px] object-cover">
    </div>

    <div>
        <h2>JOB APPLICANTS</h2>
        @foreach ($jobPost->applicants as $applicant)
            <p>{{ $applicant->user->user_first_name }} {{ $applicant->user->user_middle_name }} {{ $applicant->user->user_last_name }} {{ $applicant->user->user_suffix }} </p>
            <p>{{ $applicant->program->program_name }}</p>
            
            <a href="{{ asset("storage/" . $applicant->alumnus_resume) ?? '#' }}">View Resume</a>
            <p>{{ $applicant->pivot->application_status }}</p>
            <form action="{{ route('jobApplication.hireApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Hire</button>
            </form>
            <form action="{{ route('jobApplication.declineApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Decline</button>
            </form>
            <form action="{{ route('jobApplication.shortlistApplicant', $jobPost->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">Shortlist</button>
            </form>
        @endforeach
    </div>
    
</body>
</html>-->

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Job Applicants</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSectionBackground.png') }}");
        background-size: cover;
        background-position: center;
    }

    html::-webkit-scrollbar,
    body::-webkit-scrollbar {
        display: none;
    }

    html,
    body {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .action-dropdown {
        display: none;
        position: absolute;
        right: 0;
        z-index: 50;
        min-width: 160px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(14, 15, 59, 0.18);
        overflow: hidden;
        top: 110%;
        bottom: auto;
    }

    .action-dropdown.drop-up {
        top: auto;
        bottom: 110%;
    }

    .action-dropdown button {
        width: 100%;
        text-align: left;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.15s;
    }

    .action-dropdown button:hover {
        background: #f0f4ff;
    }

    .action-dropdown.open {
        display: block;
    }

    .badge {
        display: inline-block;
        padding: 2px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .badge-hired { background: #dcfce7; color: #16a34a; }
    .badge-declined { background: #fee2e2; color: #dc2626; }
    .badge-shortlisted { background: #fef9c3; color: #ca8a04; }
    .badge-pending { background: #e0e7ff; color: #3730a3; }

    #statusFilterDropdown {
        top: 130%;
        bottom: auto;
        min-width: 140px;
        z-index: 9999;
    }

    tbody tr { transition: background 0.12s; }
    tbody tr:hover { background: #f0f4ff; }
    ::-webkit-scrollbar { display: none; }
</style>

<body>
    @php $current_page = 'employer_job_postings'; @endphp
    @include('partials.header-employer')
    @include('partials.success')
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Welcome to PLV-AlumNet!</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <!-- BACK BUTTON -->
    <div class="max-w-5xl mx-auto px-6 pt-6">
        <a href="{{ route('jobPosting.myJobPosts', ['id' => auth()->user()->user_id]) }}"
            class="inline-flex items-center gap-2 text-[#0E0F3B] font-bold text-sm hover:text-[#C73D1A] transition-colors">
            <i class="fas fa-arrow-left"></i> RETURN
        </a>
    </div>

    <main class="max-w-5xl mx-auto px-6 pb-12">

        <!-- JOB DETAILS CARD -->
        <div class="bg-white rounded-3xl shadow-md overflow-hidden flex flex-col md:flex-row mt-4 mb-8">

            <!-- Image -->
            <div class="md:w-1/4 h-48 md:h-auto relative overflow-hidden">
                <img src="{{ asset('storage/' . $jobPost->job_posting_image) }}"
                    class="object-cover w-full h-full opacity-60">
                <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>
            </div>

            <!-- Info -->
            <div class="p-6 flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold uppercase text-[#0E0F3B]">{{ $jobPost->job_posting_title }}</h2>
                            <p class="text-gray-600">{{ $jobPost->job_posting_company }}</p>
                            <p class="text-gray-500 text-sm">{{ $jobPost->job_posting_address }}</p>
                        </div>
                        <p class="text-xs text-gray-400 flex items-center">
                            <i class="far fa-calendar-alt mr-1"></i> {{ $jobPost->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-semibold">
                        <div>
                            <p class="text-[#0E0F3B]">Job Type: <span class="font-normal">{{ $jobPost->job_posting_employment_type }}</span></p>
                            <p class="text-[#0E0F3B]">Job Setup: <span class="font-normal">{{ $jobPost->job_posting_setup }}</span></p>
                        </div>
                        <div>
                            <p class="text-blue-900">Recommended Course/Program:
                                <span class="font-normal text-black">
                                    @foreach ($jobPost->programs as $program)
                                        {{ $program->program_name }}<br>
                                    @endforeach
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="font-bold text-sm text-[#0E0F3B]">Job Description:</p>
                        <p class="text-gray-500 text-xs line-clamp-2">{{ $jobPost->job_posting_description }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center text-xs text-gray-400">
                    <i class="far fa-calendar-check mr-1"></i> Valid until
                    <span class="font-semibold text-gray-500 ml-1">{{ $jobPost->job_closing_date }}</span>
                </div>
            </div>
        </div>

        <!-- APPLICANTS TABLE -->
        <div class="bg-white rounded-3xl shadow-md">

            <div class="px-8 py-5 border-b">
                <h2 class="text-2xl font-bold tracking-tight text-center">
                    <span class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">JOB APPLICANTS</span>
                </h2>
            </div>

            <div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#1D264F] text-white text-xs uppercase tracking-wider">
                            <th class="px-6 py-3 text-center font-semibold w-12">#</th>
                            <th class="px-6 py-3 text-center font-semibold">Applicant Name</th>
                            <th class="px-6 py-3 text-center font-semibold">Program</th>
                            <th class="px-6 py-3 text-center font-semibold">Resume</th>
                            <th class="px-6 py-3 text-center font-semibold">
                                <div class="relative inline-block">
                                    <button onclick="toggleStatusFilter(this)" class="flex items-center gap-1 mx-auto hover:text-yellow-300 transition-colors">
                                        STATUS <i class="fas fa-chevron-down text-[10px]"></i>
                                    </button>
                                    <div id="statusFilterDropdown" class="action-dropdown left-1/2 -translate-x-1/2 right-auto text-left">
                                        <button onclick="filterStatus('All')" class="text-gray-700"><i class="fas fa-list w-4"></i> All</button>
                                        <button onclick="filterStatus('Pending')" class="text-indigo-600"><i class="fas fa-clock w-4"></i> Pending</button>
                                        <button onclick="filterStatus('Hired')" class="text-green-600"><i class="fas fa-user-check w-4"></i> Hired</button>
                                        <button onclick="filterStatus('Declined')" class="text-red-500"><i class="fas fa-user-times w-4"></i> Declined</button>
                                        <button onclick="filterStatus('Shortlisted')" class="text-yellow-600"><i class="fas fa-star w-4"></i> Shortlisted</button>
                                    </div>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                        @forelse ($jobPost->applicants as $index => $applicant)
                        @php
                            $status = $applicant->pivot->application_status ?? 'Pending';
                            $badgeClass = match(strtolower($status)) {
                                'hired'       => 'badge-hired',
                                'declined'    => 'badge-declined',
                                'shortlisted' => 'badge-shortlisted',
                                default       => 'badge-pending',
                            };
                        @endphp
                        <tr data-id="{{ $index + 1 }}" data-status="{{ $status }}">
                            <td class="px-6 py-4 text-center text-gray-400 font-semibold">{{ $index + 1 }}</td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($applicant->user->user_first_name . '+' . $applicant->user->user_last_name) }}&background=1D264F&color=fff&size=36"
                                        class="w-9 h-9 rounded-full">
                                    <span class="font-semibold text-[#0E0F3B]">
                                        {{ $applicant->user->user_last_name }}, {{ $applicant->user->user_first_name }} {{ $applicant->user->user_middle_name }} {{ $applicant->user->user_suffix }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center text-gray-500 text-xs">
                                {{ $applicant->program->program_name }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($applicant->alumnus_resume)
                                <a href="{{ asset('storage/' . $applicant->alumnus_resume) }}" target="_blank"
                                    class="bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-xs font-bold px-4 py-1.5 rounded-md transition-colors inline-block">
                                    View Document
                                </a>
                                @else
                                <span class="text-gray-400 text-xs">No resume</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="badge {{ $badgeClass }} status-badge">{{ ucfirst($status) }}</span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="relative inline-block">
                                    <button onclick="toggleDropdown(this)"
                                        class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                                        <i class="fas fa-ellipsis-v text-gray-500"></i>
                                    </button>
                                    <div class="action-dropdown">
                                        <form action="{{ route('jobApplication.hireApplicant', $jobPost->job_posting_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-green-600">
                                                <i class="fas fa-user-check w-4"></i> Hire
                                            </button>
                                        </form>
                                        <form action="{{ route('jobApplication.declineApplicant', $jobPost->job_posting_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-red-500">
                                                <i class="fas fa-user-times w-4"></i> Decline
                                            </button>
                                        </form>
                                        <form action="{{ route('jobApplication.shortlistApplicant', $jobPost->job_posting_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-yellow-600">
                                                <i class="fas fa-star w-4"></i> Shortlist
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-gray-400">
                                <i class="fas fa-inbox text-5xl mb-3 block"></i>
                                <p class="font-semibold">No applicants yet.</p>
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </main>

    @include('partials.footer-employer')

</body>

<script>
    function toggleDropdown(btn) {
        const dropdown = btn.nextElementSibling;
        const isOpen = dropdown.classList.contains('open');
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        if (!isOpen) {
            dropdown.classList.add('open');
            const rect = dropdown.getBoundingClientRect();
            if (rect.bottom > window.innerHeight) {
                dropdown.classList.add('drop-up');
            } else {
                dropdown.classList.remove('drop-up');
            }
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        }
    });

    function toggleStatusFilter(btn) {
        const dropdown = document.getElementById('statusFilterDropdown');
        const isOpen = dropdown.classList.contains('open');
        document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        if (!isOpen) dropdown.classList.add('open');
    }

    function filterStatus(status) {
        const rows = document.querySelectorAll('tbody tr[data-status]');
        rows.forEach(row => {
            if (status === 'All' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        document.getElementById('statusFilterDropdown').classList.remove('open');

        const visibleRows = [...rows].filter(r => r.style.display !== 'none');
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            if (visibleRows.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }
    }
</script>

</html>