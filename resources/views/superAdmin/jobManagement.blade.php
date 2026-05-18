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
    <div class="flex flex-col w-full items-center">
        <h1>Job Management</h1>
        <br><br><br>
        @foreach($jobPostings as $job)
        @if ($job->job_approved == 0)


        <div class="flex w-[80vw]">
            <h2>{{ $job->job_posting_title }}</h2>
            <p><strong>Company:</strong> {{ $job->job_posting_company }}</p>
            <p><strong>Location:</strong> {{ $job->job_posting_address }}</p>
            <p><strong>Posted by:</strong> {{ $job->user->user_first_name }} {{ $job->user->user_last_name }}</p>
            <p><strong>Job type:</strong> {{ $job->job_posting_employment_type }}</p>
            <p><strong>Job setup:</strong> {{ $job->job_posting_setup }}</p>
            <p><strong>Recommended program:</strong>
                @foreach ($job->programs as $program)
                {{ $program->program_name }}
                <br><br>
                @endforeach
            </p>
            <p><strong>Description:</strong> {{ $job->job_posting_description }}</p>
            <p><strong>Valid until:</strong> {{ $job->job_closing_date }}</p>

            <br><br><br>
            <form action="{{ route('jobPosting.approve', $job->job_posting_id) }}" method="POST">
                @csrf
                <button type="submit">
                    Approve
                </button>
            </form>
        </div>
        @endif
        @endforeach
        <br><br><br>

        <br><br><br>


</body>

</html>-->

<?php $current_page = 'job_management'; ?>

@php
$pending_jobs = $jobPostings->where('job_approved', 0);
$approved_jobs = $jobPostings->where('job_approved', 1);
$total_jobs = $jobPostings->count();
$pending_count = $pending_jobs->count();
$approved_count = $approved_jobs->count();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Placement Management | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        ::-webkit-scrollbar {
            display: none;
        }

        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* ── SHARED TABLE STYLES ── */
        .jobs-table {
            width: 100%;
            min-width: 1100px;
            /* table will never shrink below this */
            border-collapse: collapse;
            font-size: 11px;
        }

        /* Header cells */
        .jobs-table thead th {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1.3;
            white-space: normal;
            word-break: normal;
        }

        /* Body cells */
        .jobs-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            word-break: break-word;
            white-space: normal;
            line-height: 1.3;
        }

        /* Action dropdown */
        .action-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            z-index: 50;
            min-width: 160px;
        }

        .action-dropdown.open {
            display: block;
        }

        /* Filter sidebar */
        .filter-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 50;
            min-width: 160px;
        }

        .filter-dropdown-menu.open {
            display: block;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        th .sort-icon {
            opacity: 0.55;
        }


        #postJobModal>div {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        #postJobModal>div::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-slate-100">
    <div class="flex h-screen overflow-hidden">
        @include('partials.super-admin-side-bar')
        <main class="flex-1 flex flex-col overflow-hidden min-w-0">
            @include('partials.super-admin-header')
            <div class="flex-1 overflow-y-auto p-6">

                @include('partials.success')
                <!-- Stat Cards -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-slate-800">{{ $total_jobs }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Total Job Posts</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-[#ED7A07]">{{ $pending_count }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Pending Approval</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-2xl font-bold text-green-500">{{ $approved_count }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Approved Job Posts</p>
                    </div>
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                        <p class="text-xs font-medium text-slate-500 mt-1">Declined Job Posts</p>
                    </div>
                </div>

                <!-- ══ PENDING TABLE ══ -->
                <p class="text-xs font-bold text-[#C73D1A] mb-3">Job Posts pending for approval:</p>
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6 w-full">
                    <table class="jobs-table">
                        <thead class="bg-[#0E0F3B] text-white">
                            <tr>
                                <th class="border-r border-slate-700">ID</th>
                                <th class="border-r border-slate-700">Job Title</th>
                                <th class="border-r border-slate-700">Company Name <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Location <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Posted By <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Job Type <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Job Setup <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Recommended Program <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Status <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Posting Date</th>
                                <th class="border-r border-slate-700">Closing Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pending_jobs as $j)

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="font-medium text-black border-r border-slate-100">{{ $loop->iteration }}</td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_title }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->job_posting_company }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->job_posting_address }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->user->user_first_name }} {{ $j->user->user_last_name }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->job_posting_employment_type }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_setup }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    @foreach ($j->programs as $program)
                                    {{ $program->program_name }}<br>
                                    @endforeach
                                </td>
                                <td class="border-r border-slate-100">
                                    <span
                                        class="px-2 py-1 rounded-full border text-[7px] font-bold bg-amber-100 text-amber-600 border-amber-200 inline-block whitespace-nowrap ">
                                        PENDING
                                    </span>
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_date }} </td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_closing_date }}
                                </td>
                                <td class="text-center relative">
                                    <div class="inline-block text-left relative">
                                        <button
                                            class="menu-button p-1.5 hover:bg-slate-100 rounded-full transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                        </button>
                                        <div class="action-dropdown bg-white border border-slate-200 rounded-md shadow-xl">
                                            <div class="py-1">
                                                <form action="{{ route('jobPosting.approve', $j->job_posting_id) }}"
                                                    method="POST" class="w-full">
                                                    @csrf
                                                    <button type="button"
                                                        onclick="openApprovalModal(this)"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-green-50">
                                                        <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-green-500"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button"
                                                    onclick="openDeclineNotesModal({{ $j->job_posting_id }}, '{{ addslashes($j->job_posting_title) }}')"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                    <i data-lucide="x-circle" class="w-4 h-4 mr-3"></i> Decline
                                                </button>

                                                <button onclick="openViewModal({{ $j->job_posting_id }}, {{ json_encode([
                                                                                            'title' => $j->job_posting_title,
                                                                                            'posted' => $j->created_at,
                                                                                            'company' => $j->job_posting_company,
                                                                                            'location' => $j->job_posting_address,
                                                                                            'posted_by' => $j->user->user_first_name . ' ' . $j->user->user_last_name,
                                                                                            'type' => $j->job_posting_employment_type,
                                                                                            'setup' => $j->job_posting_setup,
                                                                                            'program' => $j->programs->pluck('program_name')->join(', '),
                                                                                            'closing' => $j->job_closing_date,
                                                                                            'description' => $j->job_posting_description,
                                                                                            'status' => 'Pending',
                                                                                        ]) }})"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 border-t border-slate-100">
                                                    <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-slate-400 text-sm">No pending job posts.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Search / Filter / Export Row -->
                <div class="flex items-center mb-4 gap-3">
                    <div class="relative flex-1 max-w-md">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="search-input" type="text" placeholder="Search by Job Title or Company Name"
                            class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                    </div>
                    <button onclick="toggleSidebar('filter-sidebar')"
                        class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:border-[#C73D1A] transition-all shrink-0">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                    <div class="flex justify-end p-4">
                        <button
                            onclick="openPostJobModal()"
                            class="flex items-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-4 py-2 rounded-lg font-bold text-xs tracking-widest shadow-lg transition-all">
                            <i class="fas fa-plus text-xs"></i>
                            <span>POST A NEW JOB</span>
                        </button>
                    </div>
                    <button onclick="exportCSV()"
                        class="shrink-0 flex items-center gap-2 px-5 py-2 bg-[#C73D1A] hover:bg-[#a83215] text-white text-xs font-bold rounded-lg transition-all uppercase">
                        <i data-lucide="download" class="w-4 h-4"></i> EXPORT CSV
                    </button>
                </div>

                <!-- ══ ALL JOBS TABLE ══ -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 w-full">
                    <table class="jobs-table">
                        <thead class="bg-[#0E0F3B] text-white">
                            <tr>
                                <th class="border-r border-slate-700">ID</th>
                                <th class="border-r border-slate-700">Job Title</th>
                                <th class="border-r border-slate-700">Company Name <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Location <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Posted By <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Job Type <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Job Setup <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Recommended Program <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Status <i data-lucide="chevron-down"
                                        class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
                                <th class="border-r border-slate-700">Posting Date</th>
                                <th class="border-r border-slate-700">Closing Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="jobs-tbody">
                            @forelse ($approved_jobs as $j)
                            <tr class="hover:bg-slate-50/80 transition-colors"

                                data-title="{{ strtolower($j->job_posting_title) }}"
                                data-company="{{ strtolower($j->job_posting_company) }}"
                                data-type="{{ $j->job_posting_employment_type }}"
                                data-setup="{{ $j->job_posting_setup }}"
                                data-program="{{ $j->programs->pluck('program_name')->join(', ') }}"
                                data-datetime="{{ $j->created_at }}" data-closing="{{ $j->job_closing_date }}">
                                <td class="font-medium text-black border-r border-slate-100">{{ $loop->iteration }}</td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_title }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->job_posting_company }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->job_posting_address }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->user->user_first_name }} {{ $j->user->user_last_name }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    {{ $j->job_posting_employment_type }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_setup }}
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">
                                    @foreach ($j->programs as $program)
                                    {{ $program->program_name }}<br>
                                    @endforeach
                                </td>
                                <td class="border-r border-slate-100">
                                    <span
                                        class="px-2 py-1 rounded-full border text-[6px] font-semibold bg-green-100 text-green-600 border-green-200 inline-block whitespace-nowrap">
                                        APPROVED
                                    </span>
                                </td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_date }}</td>
                                <td class="font-medium text-black border-r border-slate-100">{{ $j->job_closing_date }}
                                </td>
                                <td class="text-center relative">
                                    <div class="inline-block text-left relative">
                                        <button
                                            class="menu-button p-1.5 hover:bg-slate-100 rounded-full transition-colors">
                                            <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                        </button>
                                        <div class="action-dropdown bg-white border border-slate-200 rounded-md shadow-xl">
                                            <div class="py-1">
                                                <button onclick="openViewModal({{ $j->job_posting_id }}, {{ json_encode([
                                                                                                    'title' => $j->job_posting_title,
                                                                                                    'posted' => $j->created_at,
                                                                                                    'company' => $j->job_posting_company,
                                                                                                    'location' => $j->job_posting_address,
                                                                                                    'posted_by' => $j->user->user_first_name . ' ' . $j->user->user_last_name,
                                                                                                    'type' => $j->job_posting_employment_type,
                                                                                                    'setup' => $j->job_posting_setup,
                                                                                                    'program' => $j->programs->pluck('program_name')->join(', '),
                                                                                                    'closing' => $j->job_closing_date,
                                                                                                    'description' => $j->job_posting_description,
                                                                                                    'status' => 'Approved',
                                                                                                ]) }})"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50">
                                                    <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> View
                                                </button>
                                                <button type="button"
                                                    onclick="openDeleteModal({{ $j->job_posting_id }}, '{{ addslashes($j->job_posting_title) }}', '{{ route('jobPosting.delete', $j->job_posting_id) }}')"
                                                    class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-slate-400 text-sm">No approved job posts.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div id="empty-state" class="hidden text-center py-12 text-slate-400 text-sm">No job posts found.
                    </div>
                </div>

            </div><!-- end overflow-y-auto -->
        </main>
    </div><!-- end flex h-screen -->

    <!-- View Job Modal -->
    <div id="viewJobModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60  backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="relative h-32 bg-slate-800 flex items-end p-6 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&q=80"
                    class="absolute inset-0 w-full h-full object-cover opacity-40" alt="Background">
                <h2 class="relative text-2xl font-bold text-white">View Job Details</h2>
                <button onclick="closeViewModal()"
                    class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>
            <div id="modalContent" class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                <div class="animate-pulse space-y-3">
                    <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                    <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                </div>
            </div>
            <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                <button onclick="closeViewModal()"
                    class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
                    Cancel
                </button>
                <button id="deleteBtn"
                    class="hidden flex items-center px-6 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors uppercase">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete
                </button>
                <button id="declineBtn"
                    class="flex items-center px-6 py-2 text-sm font-medium text-white bg-[#D34120] rounded-lg hover:bg-red-700 transition-colors uppercase">
                    <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Decline
                </button>
                <button id="approveBtn"
                    class="flex items-center px-6 py-2 text-sm font-medium text-white bg-[#10B981] rounded-lg hover:bg-[#059669] transition-colors uppercase">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Approve
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div id="confirmModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-[#0E0F3B]/40 backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <div id="confirmContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div id="confirmIconContainer"
                    class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i id="confirmIcon" data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <h3 id="confirmTitle" class="text-[#0E0F3B] text-xl font-bold mb-2">Confirmation</h3>
                <p id="confirmMessage" class="text-slate-500 text-sm leading-relaxed">Are you sure you want to proceed?
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeConfirmModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="confirmYesBtn"
                    class="flex-1 py-2.5 text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" onclick="toggleSidebar('filter-sidebar')"
        class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 hidden transition-opacity"></div>

    <!-- Filter Sidebar -->
    <div id="filter-sidebar"
        class="fixed top-0 right-0 h-full w-[320px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100">
            <h2 class="text-xl font-bold text-[#1e1b4b]">Filter by</h2>
            <button onclick="toggleSidebar('filter-sidebar')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
                <div>
                    <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Job Title</label>
                    <input type="text" placeholder="Enter Job Title"
                        class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#C73D1A]">
                </div>
                <div>
                    <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Company Name</label>
                    <input type="text" placeholder="Enter Company Name"
                        class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#C73D1A]">
                </div>
            </div>
            <div>
                <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Recommended Program</label>
                <select
                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm text-slate-600 focus:outline-none">
                    <option>Select Program</option>
                </select>
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Job Type</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium"><input type="checkbox"
                            class="w-4 h-4 rounded border-slate-300"> Part-time</label>
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium"><input type="checkbox"
                            class="w-4 h-4 rounded border-slate-300"> Full-time</label>
                </div>
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Job
                    Setup</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium"><input type="checkbox"
                            class="w-4 h-4 rounded border-slate-300"> On-site</label>
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium"><input type="checkbox"
                            class="w-4 h-4 rounded border-slate-300"> Remote</label>
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium"><input type="checkbox"
                            class="w-4 h-4 rounded border-slate-300"> Hybrid</label>
                </div>
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Date
                    Posted</label>
                <div class="grid grid-cols-2 gap-y-2">
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        Today</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        Last 6 months</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        This week</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        This Year</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        This month</label>
                </div>
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Closing
                    Date</label>
                <div class="grid grid-cols-2 gap-y-2">
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        Today</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        Last 6 months</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        This week</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        This Year</label>
                    <label class="flex items-center gap-2 text-xs font-medium text-[#1e1b4b]"><input type="checkbox">
                        This month</label>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-slate-100">
            <button onclick="applyFilters()"
                class="w-full bg-[#0a0a23] text-white py-3 rounded text-sm font-bold tracking-widest hover:bg-black transition-colors uppercase">
                Apply Filter
            </button>
        </div>
    </div>

    <!-- Post a new job modal -->
    <div id="postJobModal" class="fixed inset-0 z-[110] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-3">

        <div class="bg-white w-full max-w-3xl rounded-[1.8rem] shadow-2xl relative">

            <form action="{{ route('jobPosting.addJobPost', ['id' => $users->user_id]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- CLOSE -->
                <button type="button" onclick="closePostModal()"
                    class="absolute top-5 right-5 text-gray-300 hover:text-gray-500 transition-colors z-10">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>

                <!-- TITLE -->
                <div class="w-full pt-6 text-center">
                    <h2 class="inline-block text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">
                        POST A NEW JOB
                    </h2>
                </div>

                <!-- CONTENT -->
                <div class="p-5 text-sm">

                    <!-- TOP GRID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- LEFT -->
                        <div class="space-y-2">

                            <!-- JOB TITLE -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Job Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="job_posting_title"
                                    placeholder="e.g., Senior Full Stack Developer"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <!-- BUSINESS NAME -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Business Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="job_posting_company"
                                    placeholder="Enter the registered name"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <!-- TYPE + SETUP -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                        Job Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="job_posting_employment_type"
                                        class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] appearance-none bg-no-repeat bg-[right_0.5rem_center] bg-[length:1em_1em]">
                                        <option>Select Type (e.g., Full-time)</option>
                                        <option>Full-Time</option>
                                        <option>Part-Time</option>
                                        <option>Freelance</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                        Job Setup <span class="text-red-500">*</span>
                                    </label>
                                    <select name="job_posting_setup"
                                        class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] appearance-none">
                                        <option>Select Setup (e.g., Remote)</option>
                                        <option>Remote</option>
                                        <option>On-Site</option>
                                        <option>Hybrid</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ADDRESS -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Business Address <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="job_posting_address"
                                    placeholder="Enter business address"
                                    class="w-full border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A]">
                            </div>

                            <!-- DATE -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Closing / Validity Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="job_closing_date"
                                    class="w-full border border-[#0E0F3B] uppercase rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] text-gray-400"
                                    onchange="this.classList.remove('text-gray-400'); this.classList.add('text-black');">
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="space-y-2">

                            <!-- IMAGE -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Upload Image <span class="text-red-500">*</span>
                                </label>

                                <div id="imageFrame"
                                    class="w-full h-[150px] border-2 border-dashed border-[#1D264F] rounded-2xl flex flex-col items-center justify-center bg-[#F8FAFC] relative overflow-hidden">

                                    <div id="uploadPlaceholder"
                                        class="flex flex-col items-center justify-center text-center px-4">
                                        <div class="w-12 h-12 rounded-full bg-[#1D264F] flex items-center justify-center mb-2 shadow-md">
                                            <i class="fas fa-cloud-upload-alt text-white text-lg"></i>
                                        </div>
                                        <p class="text-[11px] text-gray-500 mb-2">Upload job image</p>
                                        <button type="button" onclick="document.getElementById('jobImageInput').click()"
                                            class="bg-[#0E0F3B] text-white px-4 py-1.5 rounded-lg font-semibold text-[10px] tracking-wide hover:bg-blue-900 transition-all shadow-sm">
                                            CHOOSE FILE
                                        </button>
                                    </div>

                                    <img id="jobImagePreview" src="#" class="hidden w-full h-full object-cover" />

                                    <input type="file" name="job_posting_image" id="jobImageInput"
                                        accept="image/*" class="hidden" onchange="previewJobImage(this)">

                                    <button id="changeImgBtn" type="button"
                                        onclick="document.getElementById('jobImageInput').click()"
                                        class="hidden absolute bottom-2 right-2 bg-white/90 backdrop-blur-sm text-[#1D264F] px-3 py-1 rounded-lg font-bold text-[9px] hover:bg-white transition-all shadow-md">
                                        CHANGE IMAGE
                                    </button>
                                </div>
                            </div>

                            <!-- COURSE -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                                    Recommended Course/Program <span class="text-red-500">*</span>
                                </label>

                                <div id="course-input-container" class="space-y-2">
                                    <div class="flex items-center gap-2 course-row">
                                        <select name="program[]"
                                            class="flex-1 border border-[#0E0F3B] rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:border-[#C73D1A] bg-white w-full">
                                            <option selected disabled>Select Undergraduate Program</option>
                                            @foreach ($programs as $program)
                                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                                            @endforeach
                                        </select>

                                        <button type="button" onclick="addCourseField()"
                                            class="bg-[#1D264F] text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#0E0F3B] transition-colors shadow-sm">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>

                                <p id="course-limit-msg" class="text-[9px] text-gray-400 italic hidden">
                                    Maximum of 3 programs reached.
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="space-y-1 mt-4">
                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">
                            Job Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="job_posting_description" rows="3"
                            placeholder="Describe the roles, responsibilities, and specific skills required for this position..."
                            class="w-full border border-[#0E0F3B] rounded-xl px-3 py-2 text-xs resize-none focus:outline-none focus:border-[#C73D1A]"></textarea>
                    </div>

                    <div id="adminModalErrors" class="hidden flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 mt-5 mb-7 shadow-sm">
                        <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                        <ul id="adminModalErrorList" class="text-xs space-y-0.5 list-disc list-inside text-red-600"></ul>
                    </div>

                    @if ($errors->any())
                    <div style="display: flex; gap: 12px; background: var(--color-background-danger); border: 0.5px solid var(--color-border-danger); border-radius: var(--border-radius-md); padding: 12px 16px; margin-bottom: 1rem;">
                        <i data-lucide="alert-circle" style="width: 18px; height: 18px; color: var(--color-text-danger); flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <ul style="margin: 0; padding-left: 16px; display: flex; flex-direction: column; gap: 2px;">
                                @foreach ($errors->all() as $error)
                                <li style="font-size: 13px; color: var(--color-text-danger);">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <!-- BUTTONS -->
                    <div class="flex justify-end gap-3 mt-5">
                        <button type="button" onclick="closePostModal()"
                            class="px-6 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-lg font-bold text-xs hover:bg-[#0E0F3B] hover:text-white transition-colors">
                            CANCEL
                        </button>
                        <button type="button" onclick="handleJobSubmit()"
                            class="px-7 py-2 bg-[#0E0F3B] text-white rounded-lg font-bold text-xs hover:bg-blue-900 transition-colors shadow-md">
                            POST
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <!--JOB APPROVAL CONFIRMATION MODAL-->
    <!-- Confirmation Modal -->
    <div id="approvalModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center justify-center w-14 h-14 rounded-full bg-green-100 mb-4">
                    <i data-lucide="check-circle" class="w-7 h-7 text-green-500"></i>
                </div>
                <h2 class="text-lg font-semibold text-[#0E0F3B] mb-1">Confirm Approval</h2>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to approve this request? This action cannot be undone.</p>
                <div class="flex gap-3 w-full">
                    <button onclick="closeApprovalModal()"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button onclick="submitApproval()"
                        class="flex-1 px-4 py-2 rounded-lg bg-green-500 text-white text-sm font-medium hover:bg-green-600 transition">
                        Yes, Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!--DECLINE JOB MODAL WITH NOTES/REASON-->
    <form id="declineForm" action="" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="decline-reason" id="declineReasonInput">
    </form>

    <!-- Decline Notes Modal -->
    <div id="declineNotesModal" class="fixed inset-0 z-[200] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeclineNotesModal()"></div>
        <div id="declineNotesContent" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1 text-center">Decline Job Post</h3>
                <p id="declineNotesJobTitle" class="text-slate-400 text-xs text-center mb-5"></p>
                <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                    Reason for Declining <span class="text-red-500">*</span>
                </label>
                <textarea id="declineNotesText" rows="4"
                    placeholder="Enter your notes or reason for declining this job post..."
                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                <p id="declineNotesError" class="text-red-500 text-xs mt-1 hidden">
                    Please provide a reason before declining.
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeDeclineNotesModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button onclick="submitDecline()"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Decline
                </button>
            </div>
        </div>
    </div>

    <!-- POST JOB CONFIRMATION MODAL (admin) -->
    <div id="postConfirmModal" class="fixed inset-0 z-[210] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-paper-plane text-[#1D46A4] text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold text-[#0E0F3B] mb-2">Post this Job?</h2>
            <p class="text-gray-500 text-sm mb-6">Please confirm that all job details are correct before submitting.</p>
            <div class="flex gap-3">
                <button onclick="cancelAdminPostConfirm()" class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-100 transition-colors">
                    CANCEL
                </button>
                <button onclick="confirmAdminPostJob()" class="flex-1 bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-[#1D46A4] transition-colors">
                    YES, POST IT
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden Delete Form --}}
    <form id="deleteForm" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
        <input type="hidden" name="delete-reason" id="deleteReasonInput">
    </form>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-[200] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div id="deleteModalContent" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="trash-2" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1 text-center">Delete Job Post</h3>
                <p id="deleteModalJobTitle" class="text-slate-400 text-xs text-center mb-5"></p>
                <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                    Reason for Deleting <span class="text-red-500">*</span>
                </label>
                <textarea id="deleteReasonText" rows="4"
                    placeholder="Enter your reason for deleting this job post..."
                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                <p id="deleteReasonError" class="text-red-500 text-xs mt-1 hidden">
                    Please provide a reason before deleting.
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeDeleteModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button onclick="submitDelete()"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            initMenuButtons();
            document.getElementById('search-input').addEventListener('input', applyFilters);
        });

        //POST A NEW JOB MODAL
        function openPostJobModal() {
            const modal = document.getElementById('postJobModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePostModal() {
            const modal = document.getElementById('postJobModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Ensure clicking outside the modal content closes it
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('postJobModal');
            if (e.target === modal) {
                closePostModal();
            }
        });

        //POST A NEW JOB MODAL - UPLOAD DOCUMENT
        function previewJobImage(input) {
            const frame = document.getElementById('imageFrame');
            const placeholder = document.getElementById('uploadPlaceholder');
            const preview = document.getElementById('jobImagePreview');
            const changeBtn = document.getElementById('changeImgBtn');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // Set image source
                    preview.src = e.target.result;

                    // Show image and "Change" button, hide placeholder
                    preview.classList.remove('hidden');
                    changeBtn.classList.remove('hidden');
                    placeholder.classList.add('hidden');

                    // Remove padding from frame to make image full-size
                    frame.classList.remove('p-6');
                    frame.classList.add('p-0');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        //POST A NEW JOB MODAL - ADD COURSE/PROGRAM INPUT FIELD
        function addCourseField() {
            const container = document.getElementById('course-input-container');
            const rows = container.getElementsByClassName('course-row');

            if (rows.length < 3) {
                // Clone the first row
                const newRow = rows[0].cloneNode(true);

                // Reset the selection in the new row
                const select = newRow.querySelector('select');
                select.selectedIndex = 0;

                // Change the button on the NEW row to a "minus" button
                const btn = newRow.querySelector('button');
                btn.innerHTML = '<i class="fas fa-minus text-xs"></i>';
                btn.classList.replace('bg-[#1D264F]', 'bg-red-500');
                btn.setAttribute('onclick', 'removeCourseField(this)');

                // Append the row
                container.appendChild(newRow);

                // Hide add button on the original row if limit is reached
                if (rows.length === 3) {
                    document.getElementById('course-limit-msg').classList.remove('hidden');
                    document.getElementById('add-course-btn').classList.add('opacity-50', 'pointer-events-none');
                }
            }
        }

        function removeCourseField(button) {
            const row = button.closest('.course-row');
            row.remove();

            // Re-enable the add button and hide limit message
            document.getElementById('course-limit-msg').classList.add('hidden');
            document.getElementById('add-course-btn').classList.remove('opacity-50', 'pointer-events-none');
        }

        function initMenuButtons() {
            document.querySelectorAll('.menu-button').forEach(btn => {
                const fresh = btn.cloneNode(true);
                btn.parentNode.replaceChild(fresh, btn);
                fresh.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dd = fresh.nextElementSibling;

                    // Close all others first
                    document.querySelectorAll('.action-dropdown.open').forEach(d => {
                        d.classList.remove('open');
                        d.style.top = '';
                        d.style.left = '';
                        d.style.position = '';
                    });

                    if (dd.classList.contains('open')) {
                        dd.classList.remove('open');
                        return;
                    }

                    // Position dropdown using fixed coords to escape overflow:hidden/auto
                    const rect = fresh.getBoundingClientRect();
                    dd.style.position = 'fixed';
                    dd.style.top = (rect.bottom + 4) + 'px';
                    dd.style.left = (rect.right - 160) + 'px'; // 160 = min-width of dropdown
                    dd.style.right = 'auto';
                    dd.classList.add('open');
                });
            });
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
        });

        function toggleSidebar(id) {
            const sidebar = document.getElementById(id);
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.style.overflow = !sidebar.classList.contains('translate-x-full') ? 'hidden' : '';
        }

        function applyFilters() {
            const search = document.getElementById('search-input').value.toLowerCase();
            const sideTitle = document.querySelector('#filter-sidebar input[placeholder="Enter Job Title"]')?.value.toLowerCase() || '';
            const sideCompany = document.querySelector('#filter-sidebar input[placeholder="Enter Company Name"]')?.value.toLowerCase() || '';
            const sideProgram = document.querySelector('#filter-sidebar select')?.value.toLowerCase() || '';

            const checkedTypes = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                cb.closest('.space-y-2')?.previousElementSibling?.textContent.trim() === 'Job Type' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const checkedSetups = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                cb.closest('.space-y-2')?.previousElementSibling?.textContent.trim() === 'Job Setup' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const checkedDatePosted = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                cb.closest('.grid')?.previousElementSibling?.textContent.trim() === 'Date Posted' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const checkedClosing = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                cb.closest('.grid')?.previousElementSibling?.textContent.trim() === 'Closing Date' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const now = new Date();

            function matchesDateRange(dateStr, ranges) {
                if (ranges.length === 0) return true;
                const date = new Date(dateStr);
                if (isNaN(date)) return true;
                return ranges.some(range => {
                    if (range === 'today') return date.toDateString() === now.toDateString();
                    if (range === 'this week') {
                        const s = new Date(now);
                        s.setDate(now.getDate() - now.getDay());
                        s.setHours(0, 0, 0, 0);
                        return date >= s;
                    }
                    if (range === 'this month') return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();
                    if (range === 'last 6 months') {
                        const s = new Date(now);
                        s.setMonth(now.getMonth() - 6);
                        return date >= s;
                    }
                    if (range === 'this year') return date.getFullYear() === now.getFullYear();
                    return true;
                });
            }

            let visible = 0;
            document.querySelectorAll('#jobs-tbody tr').forEach(row => {
                const title = row.dataset.title || '';
                const company = row.dataset.company || '';
                const rType = (row.dataset.type || '').toLowerCase();
                const rSetup = (row.dataset.setup || '').toLowerCase();
                const rProgram = (row.dataset.program || '').toLowerCase();
                const rDatetime = row.dataset.datetime || '';
                const rClosing = row.dataset.closing || '';

                const show =
                    (!search || title.includes(search) || company.includes(search)) &&
                    (!sideTitle || title.includes(sideTitle)) &&
                    (!sideCompany || company.includes(sideCompany)) &&
                    (!sideProgram || sideProgram === 'select program' || rProgram.includes(sideProgram)) &&
                    (checkedTypes.length === 0 || checkedTypes.some(t => rType.includes(t))) &&
                    (checkedSetups.length === 0 || checkedSetups.some(s => rSetup.includes(s))) &&
                    matchesDateRange(rDatetime, checkedDatePosted) &&
                    matchesDateRange(rClosing, checkedClosing);

                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('empty-state').classList.toggle('hidden', visible > 0);
        }

        function exportCSV() {
            const headers = ['Job Title', 'Company Name', 'Location', 'Posted By', 'Job Type', 'Job Setup', 'Recommended Program', 'Status', 'Closing Date'];
            const rows = [];
            document.querySelectorAll('#jobs-tbody tr').forEach(row => {
                if (row.style.display === 'none') return;
                const cells = row.querySelectorAll('td');
                if (cells.length < 9) return;
                rows.push([...Array(9)].map((_, i) => cells[i].textContent.trim()));
            });
            const csv = [headers, ...rows].map(r => r.map(v => `"${v.replace(/"/g, '""')}"`).join(',')).join('\n');
            const a = Object.assign(document.createElement('a'), {
                href: URL.createObjectURL(new Blob([csv], {
                    type: 'text/csv;charset=utf-8;'
                })),
                download: 'job_posts.csv'
            });
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function openViewModal(jobId, data) {
            if (!data) return;
            const statusClass = {
                'Approved': 'bg-green-100 text-green-600 border-green-200',
                'Declined': 'bg-red-100 text-red-600 border-red-200',
                'Pending': 'bg-amber-100 text-amber-600 border-amber-200',
            } [data.status] || 'bg-slate-100 text-slate-500 border-slate-200';

            document.getElementById('modalContent').innerHTML = `
            <div class="space-y-4 text-sm text-[#0E0F3B]">
                <p><strong>Job Title:</strong> ${data.title}</p>
                <p><strong>Date &amp; Time Posted:</strong> ${data.posted}</p>
                <p><strong>Company Name:</strong> ${data.company}</p>
                <p><strong>Location:</strong> ${data.location}</p>
                <p><strong>Posted By:</strong> ${data.posted_by}</p>
                <p><strong>Job Type:</strong> ${data.type}</p>
                <p><strong>Job Setup:</strong> ${data.setup}</p>
                <p><strong>Recommended Program:</strong> ${data.program}</p>
                <p><strong>Closing Date:</strong> ${data.closing}</p>
                <p><strong>Status:</strong> <span class="px-2 py-1 rounded-full border text-[9px] font-bold ${statusClass}"> ${data.status.toUpperCase()}</span></p>
                <p><strong>Description:</strong> ${data.description ?? 'N/A'}</p>
            </div>`;

            const approveBtn = document.getElementById('approveBtn');
            const declineBtn = document.getElementById('declineBtn');
            const deleteBtn = document.getElementById('deleteBtn');

            if (data.status === 'Pending') {
                approveBtn.classList.remove('hidden');
                declineBtn.classList.remove('hidden');
                deleteBtn.classList.add('hidden');
                approveBtn.onclick = () => {
                    closeViewModal();
                    openConfirmAction(jobId, 'approve', data.title);
                };
                declineBtn.onclick = () => {
                    closeViewModal();
                    openConfirmAction(jobId, 'decline', data.title);
                };
            } else {
                approveBtn.classList.add('hidden');
                declineBtn.classList.add('hidden');
                deleteBtn.classList.remove('hidden');
                deleteBtn.onclick = () => {
                    closeViewModal();
                    openConfirmAction(jobId, 'delete', data.title);
                };
            }

            document.getElementById('viewJobModal').classList.remove('hidden');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeViewModal() {
            document.getElementById('viewJobModal').classList.add('hidden');
            document.getElementById('viewJobModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function openConfirmModal({
            title,
            message,
            iconName,
            iconBg,
            iconColor,
            btnBg,
            btnText,
            onConfirm
        }) {
            const content = document.getElementById('confirmContent');
            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmMessage').innerHTML = message;
            document.getElementById('confirmIconContainer').className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${iconBg}`;
            const icon = document.getElementById('confirmIcon');
            icon.setAttribute('data-lucide', iconName);
            icon.className = `w-8 h-8 ${iconColor}`;
            const yesBtn = document.getElementById('confirmYesBtn');
            yesBtn.className = `flex-1 py-2.5 ${btnBg} text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110`;
            yesBtn.innerText = btnText;
            yesBtn.onclick = () => {
                onConfirm();
                closeConfirmModal();
            };
            lucide.createIcons();
            document.getElementById('confirmModal').classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeConfirmModal() {
            const content = document.getElementById('confirmContent');
            content.classList.add('scale-95');
            setTimeout(() => document.getElementById('confirmModal').classList.add('invisible'), 200);
        }

        function openConfirmAction(id, action, title) {
            document.querySelectorAll('.action-dropdown.open').forEach(d => d.classList.remove('open'));
            const configs = {
                approve: {
                    title: 'Approve Job Post',
                    message: `Are you sure you want to <span class="font-bold text-green-600">approve</span> Job Post titled <b>${title}</b>?`,
                    iconName: 'check-circle',
                    iconBg: 'bg-green-100',
                    iconColor: 'text-green-600',
                    btnBg: 'bg-green-600',
                    btnText: 'Yes, Approve',
                    href: `admin_job_action.php?action=approve&id=${id}`
                },
                decline: {
                    title: 'Decline Job Post',
                    message: `Are you sure you want to <span class="font-bold text-red-600">decline</span> Job Post titled <b>${title}</b>?`,
                    iconName: 'x-circle',
                    iconBg: 'bg-red-100',
                    iconColor: 'text-red-600',
                    btnBg: 'bg-red-600',
                    btnText: 'Yes, Decline',
                    href: `admin_job_action.php?action=decline&id=${id}`
                },
                delete: {
                    title: 'Delete Job Post',
                    message: `Are you sure you want to <span class="font-bold text-red-600">delete</span> Job Post titled <b>${title}</b>?`,
                    iconName: 'trash-2',
                    iconBg: 'bg-red-100',
                    iconColor: 'text-red-600',
                    btnBg: 'bg-red-600',
                    btnText: 'Yes, Delete',
                    href: `admin_delete_job.php?id=${id}`
                }
            };
            const c = configs[action];
            openConfirmModal({
                ...c,
                onConfirm: () => {
                    window.location.href = c.href;
                }
            });
        }

        //JOB APPROVAL MODAL JS
        let approvalForm = null;

        function openApprovalModal(btn) {
            approvalForm = btn.closest('form');
            const modal = document.getElementById('approvalModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            lucide.createIcons();
        }

        function closeApprovalModal() {
            const modal = document.getElementById('approvalModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            approvalForm = null;
        }

        function submitApproval() {
            if (approvalForm) {
                approvalForm.submit();
            }
            closeApprovalModal();
        }

        // Close on backdrop click
        document.getElementById('approvalModal').addEventListener('click', function(e) {
            if (e.target === this) closeApprovalModal();
        });


        //JOB DECLINE NOTES MODAL
        let _declineId = '',
            _declineTitle = '';

        function openDeclineNotesModal(id, title) {
            _declineId = id;
            _declineTitle = title;
            document.getElementById('declineNotesText').value = '';
            document.getElementById('declineNotesError').classList.add('hidden');
            document.getElementById('declineNotesJobTitle').textContent = title;

            const modal = document.getElementById('declineNotesModal');
            const content = document.getElementById('declineNotesContent');
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
            lucide.createIcons();
        }

        function closeDeclineNotesModal() {
            const content = document.getElementById('declineNotesContent');
            content.classList.add('scale-95');
            setTimeout(() => document.getElementById('declineNotesModal').classList.add('invisible'), 200);
        }

        function submitDecline() {
            const notes = document.getElementById('declineNotesText').value.trim();
            if (!notes) {
                document.getElementById('declineNotesError').classList.remove('hidden');
                return;
            }
            document.getElementById('declineNotesError').classList.add('hidden');

            // Point the hidden form to the correct Laravel route, then submit
            const form = document.getElementById('declineForm');
            form.action = `/job-postings/${_declineId}/decline`; // adjust to match your route('jobPosting.decline', ...)
            document.getElementById('declineReasonInput').value = notes;
            form.submit();
        }

        //DELETE JOB POST MODAL WITH REASON/NOTE
        let _deleteId = '',
            _deleteTitle = '',
            _deleteUrl = '';

        function openDeleteModal(id, title, url) {
            _deleteId = id;
            _deleteTitle = title;
            _deleteUrl = url;
            document.getElementById('deleteReasonText').value = '';
            document.getElementById('deleteReasonError').classList.add('hidden');
            document.getElementById('deleteModalJobTitle').textContent = title;

            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
            lucide.createIcons();
        }

        function closeDeleteModal() {
            const content = document.getElementById('deleteModalContent');
            content.classList.add('scale-95');
            setTimeout(() => document.getElementById('deleteModal').classList.add('invisible'), 200);
        }

        function submitDelete() {
            const reason = document.getElementById('deleteReasonText').value.trim();
            if (!reason) {
                document.getElementById('deleteReasonError').classList.remove('hidden');
                return;
            }
            document.getElementById('deleteReasonError').classList.add('hidden');

            const form = document.getElementById('deleteForm');
            form.action = _deleteUrl;
            document.getElementById('deleteReasonInput').value = reason;
            form.submit();
        }

        function handleJobSubmit() {
            const form = document.querySelector('#postJobModal form');
            const errors = [];

            const title = form.querySelector('[name="job_posting_title"]').value.trim();
            const company = form.querySelector('[name="job_posting_company"]').value.trim();
            const address = form.querySelector('[name="job_posting_address"]').value.trim();
            const description = form.querySelector('[name="job_posting_description"]').value.trim();
            const employmentType = form.querySelector('[name="job_posting_employment_type"]').value;
            const setup = form.querySelector('[name="job_posting_setup"]').value;
            const closingDate = form.querySelector('[name="job_closing_date"]').value;
            const image = form.querySelector('[name="job_posting_image"]').files.length;
            const programs = form.querySelectorAll('[name="program[]"]');

            if (!title) errors.push('Job title is required.');
            if (!company) errors.push('Business name is required.');
            if (employmentType.startsWith('Select')) errors.push('Please select a job type.');
            if (setup.startsWith('Select')) errors.push('Please select a job setup.');
            if (!address) errors.push('Business address is required.');
            if (!closingDate) errors.push('Closing / validity date is required.');
            if (!image) errors.push('Please upload a job image.');
            if (!description) errors.push('Job description is required.');

            let programSelected = false;
            programs.forEach(select => {
                if (select.selectedIndex > 0) programSelected = true;
            });
            if (!programSelected) errors.push('Please select at least one recommended program.');

            if (errors.length > 0) {
                const errorBox = document.getElementById('adminModalErrors');
                const errorList = document.getElementById('adminModalErrorList');
                errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
                return;
            }

            document.getElementById('adminModalErrors').classList.add('hidden');
            document.getElementById('postJobModal').classList.add('hidden');
            document.getElementById('postConfirmModal').classList.remove('hidden');
        }

        function cancelAdminPostConfirm() {
            document.getElementById('postConfirmModal').classList.add('hidden');
            document.getElementById('postJobModal').classList.remove('hidden');
        }

        function confirmAdminPostJob() {
            document.getElementById('postConfirmModal').classList.add('hidden');
            document.querySelector('#postJobModal form').submit();
        }
    </script>

</body>

</html>