<?php $current_page = 'job_management'; ?>

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
            white-space: nowrap;
        }

        /* Body cells */
        .jobs-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            white-space: nowrap;
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
                        <p class="text-2xl font-bold text-slate-800">{{ $totalJobs }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Total Job Posts</p>
                    </div>
                    <div class="bg-white rounded-lg border shadow-sm px-5 py-4 cursor-pointer transition-all hover:shadow-md {{ ($filters['status'] ?? '') === 'pending' ? 'border-[#ED7A07] ring-2 ring-orange-200' : 'border-slate-200' }}"
                        title="Click to {{ ($filters['status'] ?? '') === 'pending' ? 'clear this filter' : 'filter by Pending' }}"
                        onclick="pvSearchNavigate('status', '{{ ($filters['status'] ?? '') === 'pending' ? '' : 'pending' }}', 'page')">
                        <p class="text-2xl font-bold text-[#ED7A07]">{{ $pendingCount }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Pending Approval</p>
                    </div>
                    <div class="bg-white rounded-lg border shadow-sm px-5 py-4 cursor-pointer transition-all hover:shadow-md {{ ($filters['status'] ?? '') === 'approved' ? 'border-green-500 ring-2 ring-green-200' : 'border-slate-200' }}"
                        title="Click to {{ ($filters['status'] ?? '') === 'approved' ? 'clear this filter' : 'filter by Approved' }}"
                        onclick="pvSearchNavigate('status', '{{ ($filters['status'] ?? '') === 'approved' ? '' : 'approved' }}', 'page')">
                        <p class="text-2xl font-bold text-green-500">{{ $approvedCount }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Approved Job Posts</p>
                    </div>
                    <div class="bg-white rounded-lg border shadow-sm px-5 py-4 cursor-pointer transition-all hover:shadow-md {{ ($filters['status'] ?? '') === 'declined' ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-200' }}"
                        title="Click to {{ ($filters['status'] ?? '') === 'declined' ? 'clear this filter' : 'filter by Declined' }}"
                        onclick="pvSearchNavigate('status', '{{ ($filters['status'] ?? '') === 'declined' ? '' : 'declined' }}', 'page')">
                        <p class="text-2xl font-bold text-red-500">{{ $declinedCount }}</p>
                        <p class="text-xs font-medium text-slate-500 mt-1">Declined Job Posts</p>
                    </div>
                </div>

                <!-- ══ TAB SWITCHER ══ -->
                <div class="flex items-center gap-2 mb-4 border-b border-slate-200">
                    <button type="button" id="jm-tab-btn-jobposts" onclick="jmSwitchTab('jobposts')"
                        class="jm-tab-btn px-4 py-2.5 text-sm font-bold border-b-2 border-[#0E0F3B] text-[#0E0F3B] transition-colors">
                        Job Posts
                    </button>
                    <button type="button" id="jm-tab-btn-applicants" onclick="jmSwitchTab('applicants')"
                        class="jm-tab-btn px-4 py-2.5 text-sm font-bold border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-colors">
                        My Job Postings
                    </button>
                </div>

                <!-- ══ TAB: JOB POSTS (Pending + Approved combined, filterable by status) ══ -->
                <div id="jm-tab-jobposts">
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden w-full">
                  <!-- Search / Status Filter / Filter Sidebar / Export Row -->
                  <form method="GET" action="{{ route('jobPosting.jobManagement') }}" class="flex flex-col md:flex-row md:items-center gap-3 p-4 border-b border-slate-100">
                    <div class="relative flex-1 max-w-md">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="search-input" type="text" placeholder="Search by Job Title or Company Name"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();}"
                            class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="relative shrink-0 w-44">
                        <select name="status" onchange="this.form.submit()"
                            class="w-full pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-full text-sm appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A] transition-all">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="declined" {{ ($filters['status'] ?? '') === 'declined' ? 'selected' : '' }}>Declined</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                    <button type="button" onclick="toggleSidebar('filter-sidebar')"
                        class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:border-[#C73D1A] transition-all shrink-0">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                    <div class="md:ml-auto flex items-center gap-3">
                        <button type="button"
                            onclick="openPostJobModal()"
                            class="flex items-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-4 py-2 rounded-lg font-bold text-xs tracking-widest shadow-lg transition-all">
                            <i class="fas fa-plus text-xs"></i>
                            <span>POST A NEW JOB</span>
                        </button>
                    </div>
                    <button type="button" onclick="exportCSV()"
                        class="shrink-0 flex items-center gap-2 px-5 py-2 bg-[#C73D1A] hover:bg-[#a83215] text-white text-xs font-bold rounded-lg transition-all uppercase">
                        <i data-lucide="download" class="w-4 h-4"></i> EXPORT CSV
                    </button>
                  </form>

                  <div id="jobManagementTableWrap">
                    @include('partials.job-management.jobs-table')
                  </div>
                </div>
                </div>

                <!-- ══ TAB: MY JOB POSTINGS (view a job post's details, or its applicants + hire/decline/shortlist for an approved one) ══ -->
                <div id="jm-tab-applicants" class="hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-[#0E0F3B]">My Job Postings</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Job posts you personally created (see "Post a New Job") — view the post itself, or its applicants once approved.</p>
                        </div>
                        <div id="applicantJobsTableWrap">
                            @include('partials.job-management.applicant-jobs-table', ['applicantJobs' => $applicantJobs])
                        </div>
                    </div>

                    <div id="applicantsPanelWrap" class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden w-full hidden"></div>
                    <div id="applicantsPanelEmpty" class="text-center py-12 text-slate-400 text-sm">
                        <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        Select "Applicants" on an approved job above to see who applied.
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

    @include('partials.confirm-modal')
    @include('partials.table-scroll-fix')

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
                    @foreach ($programs as $program)
                    <option>{{ $program->program_name }}</option>
                    @endforeach
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
            <button onclick="applyFilters(); toggleSidebar('filter-sidebar')"
                class="w-full bg-[#0a0a23] text-white py-3 rounded text-sm font-bold tracking-widest hover:bg-black transition-colors uppercase">
                Apply Filter
            </button>
        </div>
    </div>

    {{-- "Post a New Job" modal — same shared partial the employer/alumni Job
         Board and My Job Postings pages use (see partials/post-job-modal.blade.php),
         so admin gets the identical layout/format instead of a hand-copied
         (and drifted) second version. --}}
    @include('partials.post-job-modal', ['jobPoster' => $users])

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

            // Deep-link from a notification (?job=123) — open that job's
            // View modal automatically if it's on the currently-rendered
            // page of either table. Silently no-ops if it's on a page the
            // pagination hasn't loaded (same tradeoff as the Notices deep
            // link on eventsSeminars.blade.php).
            const openJobId = new URLSearchParams(window.location.search).get('job');
            if (openJobId) {
                const btn = document.querySelector('[data-job-id="' + openJobId + '"]');
                if (btn) btn.click();
            }

            // ?tab=applicants&applicantsJob=123 — where a hire/decline/
            // shortlist action redirects back to (see JobApplicationController
            // ::applicantsRedirectTarget()), so the admin lands back on the
            // same tab + job panel they were just working in, not the Job
            // Posts tab.
            const params = new URLSearchParams(window.location.search);
            if (params.get('tab') === 'applicants') {
                jmSwitchTab('applicants');
                const applicantsJobId = params.get('applicantsJob');
                if (applicantsJobId) japp_openApplicantsPanel(applicantsJobId);
            }
        });

        /* ── Tab switcher (Job Posts / Applicants) ─────────────────── */
        function jmSwitchTab(tab) {
            const isApplicants = tab === 'applicants';
            document.getElementById('jm-tab-jobposts').classList.toggle('hidden', isApplicants);
            document.getElementById('jm-tab-applicants').classList.toggle('hidden', !isApplicants);

            document.getElementById('jm-tab-btn-jobposts').classList.toggle('border-[#0E0F3B]', !isApplicants);
            document.getElementById('jm-tab-btn-jobposts').classList.toggle('text-[#0E0F3B]', !isApplicants);
            document.getElementById('jm-tab-btn-jobposts').classList.toggle('border-transparent', isApplicants);
            document.getElementById('jm-tab-btn-jobposts').classList.toggle('text-slate-400', isApplicants);

            document.getElementById('jm-tab-btn-applicants').classList.toggle('border-[#0E0F3B]', isApplicants);
            document.getElementById('jm-tab-btn-applicants').classList.toggle('text-[#0E0F3B]', isApplicants);
            document.getElementById('jm-tab-btn-applicants').classList.toggle('border-transparent', !isApplicants);
            document.getElementById('jm-tab-btn-applicants').classList.toggle('text-slate-400', !isApplicants);

            if (window.lucide) lucide.createIcons();
        }

        /* ── Applicants tab: load one job's applicant panel ────────── */
        let japp_currentJobId = null;

        function japp_openApplicantsPanel(jobId) {
            japp_currentJobId = jobId;
            fetch('{{ url("/jobManagement/applicants") }}/' + jobId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    const wrap = document.getElementById('applicantsPanelWrap');
                    wrap.innerHTML = html;
                    wrap.classList.remove('hidden');
                    document.getElementById('applicantsPanelEmpty').classList.add('hidden');
                    if (window.lucide) lucide.createIcons();
                    // Initializes the freshly-injected client-mode pagination
                    // bar (its own DOMContentLoaded-driven init already ran
                    // before this fragment existed) and applies any active
                    // status filter's row count.
                    document.dispatchEvent(new CustomEvent('pv:filtered'));
                    wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
        }

        /* ── Applicants panel: row actions dropdown ────────────────── */
        // Stopping propagation is required here — a page-wide click
        // listener further down (registered for the Job Posts tab's own
        // .menu-button dropdowns) unconditionally closes every open
        // .action-dropdown on ANY click, including this same click as it
        // bubbles up. Without this, the dropdown opened one line above
        // immediately got closed again by that handler before the user
        // ever saw it, making every action button look completely dead.
        //
        // Positioned with fixed coords computed from the button's own
        // on-screen rect (same escape trick initMenuButtons() below uses for
        // the Job Posts tab) — the dropdown otherwise inherits .action-dropdown's
        // plain `position: absolute; top: 100%`, which is clipped by the
        // applicants table's own overflow-x-auto scroll container instead of
        // floating over it, especially for a row near the bottom of the table.
        function japp_closeAllActionDropdowns() {
            document.querySelectorAll('.action-dropdown.open').forEach(d => {
                d.classList.remove('open');
                d.style.position = '';
                d.style.top = '';
                d.style.left = '';
                d.style.right = '';
            });
        }

        function japp_toggleDropdown(btn, event) {
            event?.stopPropagation();
            const dropdown = btn.nextElementSibling;
            const isOpen = dropdown.classList.contains('open');
            japp_closeAllActionDropdowns();
            if (isOpen) return;

            const rect = btn.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.left = (rect.right - 160) + 'px';
            dropdown.style.right = 'auto';
            dropdown.classList.add('open');

            // Flip above the button if it would overflow the bottom of the
            // viewport — measured after .open actually renders it.
            const ddRect = dropdown.getBoundingClientRect();
            if (ddRect.bottom > window.innerHeight) {
                dropdown.style.top = (rect.top - ddRect.height - 4) + 'px';
            }
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.relative')) {
                japp_closeAllActionDropdowns();
            }
        });

        /* ── Applicants panel: status filter ────────────────────────── */
        // Same fixed-position escape as japp_toggleDropdown() above — this
        // header dropdown lives inside the same overflow-x-auto table
        // wrapper, so it gets clipped the same way without it. The
        // left-1/2 -translate-x-1/2 classes stay in the markup and keep
        // doing the horizontal centering; only the anchor point (left)
        // needs to move from "under the header cell" to "under the button,
        // in fixed viewport coords".
        function japp_toggleStatusFilter(btn, event) {
            event?.stopPropagation();
            const dropdown = document.getElementById('japp-statusFilterDropdown');
            const isOpen = dropdown.classList.contains('open');
            japp_closeAllActionDropdowns();
            if (isOpen) return;

            const rect = btn.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.left = (rect.left + rect.width / 2) + 'px';
            dropdown.classList.add('open');
        }

        function japp_filterStatus(status) {
            const rows = document.querySelectorAll('#japp-applicants-tbody tr[data-status]');
            rows.forEach(row => {
                row.style.display = (status === 'All' || row.dataset.status === status) ? '' : 'none';
            });
            japp_closeAllActionDropdowns();
            document.dispatchEvent(new CustomEvent('pv:filtered'));
        }

        /* ── Applicants panel: bulk selection + actions ────────────── */
        function japp_getCheckedApplicationIds() {
            return [...document.querySelectorAll('.japp-applicant-checkbox:checked')].map(cb => cb.value);
        }

        function japp_updateBulkActionUI() {
            const count = japp_getCheckedApplicationIds().length;
            document.getElementById('japp-selectedCount').textContent = count;
            document.getElementById('japp-bulkHireBtn').disabled = count === 0;
            document.getElementById('japp-bulkDeclineBtn').disabled = count === 0;
            document.getElementById('japp-bulkShortlistBtn').disabled = count === 0;

            const allCheckboxes = document.querySelectorAll('.japp-applicant-checkbox');
            allCheckboxes.forEach(cb => cb.closest('tr')?.classList.toggle('bg-blue-50', cb.checked));

            // Only count checkboxes japp_toggleSelectAll() actually controls
            // — not disabled (already hired/declined; can never become
            // checked) and not hidden by the current status filter.
            // Comparing against EVERY checkbox meant "select all" could
            // never show as fully checked once any row was disabled, which
            // broke its own toggle: clicking it while it wrongly showed
            // unchecked just re-checked everything instead of clearing it.
            const eligibleCheckboxes = [...document.querySelectorAll('.japp-applicant-checkbox:not(:disabled)')]
                .filter(cb => cb.closest('tr')?.style.display !== 'none');
            const eligibleCheckedCount = eligibleCheckboxes.filter(cb => cb.checked).length;

            const selectAll = document.getElementById('japp-selectAllCheckbox');
            if (selectAll) selectAll.checked = eligibleCheckboxes.length > 0 && eligibleCheckedCount === eligibleCheckboxes.length;
        }

        function japp_toggleSelectAll(source) {
            document.querySelectorAll('#japp-applicants-tbody tr[data-status]').forEach(row => {
                if (row.style.display === 'none') return; // respect the current status filter
                const cb = row.querySelector('.japp-applicant-checkbox');
                if (cb && !cb.disabled) cb.checked = source.checked; // disabled = already hired/declined, not bulk-eligible
            });
            japp_updateBulkActionUI();
        }

        function japp_submitBulkAction(action) {
            const ids = japp_getCheckedApplicationIds();
            if (ids.length === 0) return;

            const remainingSlots = parseInt(document.getElementById('japp-remaining-slots')?.textContent || '0', 10);
            if (action === 'hire' && ids.length > remainingSlots) {
                alert('You can only hire ' + remainingSlots + ' more applicant(s) for this job post. Uncheck some and try again.');
                return;
            }

            if (!confirm('Are you sure you want to ' + action + ' ' + ids.length + ' selected applicant(s)?')) return;

            const formIds = { hire: 'japp-bulkHireForm', decline: 'japp-bulkDeclineForm', shortlist: 'japp-bulkShortlistForm' };
            const form = document.getElementById(formIds[action]);
            form.querySelectorAll('input[name="application_ids[]"]').forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'application_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        }

        /* ── Applicants panel: View Application modal ──────────────── */
        function japp_openApplicationViewModal(data) {
            document.getElementById('japp-avm-name').textContent = data.applicantName;
            document.getElementById('japp-avm-email').textContent = data.applicantEmail || '—';
            document.getElementById('japp-avm-contact').textContent = data.applicantContact || '—';

            const photo = document.getElementById('japp-avm-photo');
            const photoFallback = document.getElementById('japp-avm-photo-fallback');
            if (data.applicantPhoto) {
                photo.src = data.applicantPhoto;
                photo.classList.remove('hidden');
                photoFallback.classList.add('hidden');
            } else {
                photo.classList.add('hidden');
                photoFallback.classList.remove('hidden');
                photoFallback.textContent = (data.applicantName || '?').trim().charAt(0).toUpperCase();
            }

            document.getElementById('japp-avm-applied-at').textContent = data.appliedAt || 'Unknown date';
            document.getElementById('japp-avm-status').textContent = data.status;
            document.getElementById('japp-avm-score').textContent = data.score || '—';
            document.getElementById('japp-avm-resume-source').textContent = data.resumeSourceLabel;

            const resumeBox = document.getElementById('japp-avm-resume-content');
            resumeBox.innerHTML = '';
            if (data.isBuilderResume && data.builderSnapshot) {
                resumeBox.appendChild(japp_buildSnapshotView(data.builderSnapshot));
            } else if (data.resumeUrl) {
                resumeBox.appendChild(japp_buildFileLinkView(data.resumeUrl, 'View resume file'));
            } else {
                resumeBox.innerHTML = '<p class="text-xs text-gray-400">No resume submitted.</p>';
            }

            const coverBox = document.getElementById('japp-avm-cover-letter-content');
            coverBox.innerHTML = '';
            if (data.coverLetterUrl) {
                coverBox.appendChild(japp_buildFileLinkView(data.coverLetterUrl, 'View cover letter file'));
            } else {
                coverBox.innerHTML = '<p class="text-xs text-gray-400">No cover letter submitted.</p>';
            }

            document.getElementById('japp-applicationViewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function japp_closeApplicationViewModal() {
            document.getElementById('japp-applicationViewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function japp_buildFileLinkView(url, label) {
            const div = document.createElement('div');
            div.className = 'border border-gray-200 rounded-xl p-4 flex items-center gap-3';
            div.innerHTML = '<i class="fas fa-file-lines text-[#C73D1A] text-lg"></i>' +
                '<a href="' + url + '" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline">' + label + '</a>';
            return div;
        }

        function japp_buildSnapshotView(snap) {
            const wrap = document.createElement('div');
            wrap.className = 'border border-gray-200 rounded-xl p-4 space-y-4 text-sm';

            if (snap.summary) {
                const p = document.createElement('p');
                p.className = 'text-gray-600 text-xs leading-relaxed';
                p.textContent = snap.summary;
                wrap.appendChild(p);
            }

            if (snap.skills && snap.skills.length) {
                const skillsWrap = document.createElement('div');
                skillsWrap.className = 'flex flex-wrap gap-1.5';
                snap.skills.forEach(s => {
                    const chip = document.createElement('span');
                    chip.className = 'bg-blue-50 text-[#1D46A4] text-[10px] font-semibold px-2.5 py-1 rounded-full';
                    chip.textContent = s.name;
                    skillsWrap.appendChild(chip);
                });
                wrap.appendChild(skillsWrap);
            }

            if (snap.experiences && snap.experiences.length) {
                const expTitle = document.createElement('p');
                expTitle.className = 'text-[10px] font-bold text-gray-400 uppercase mt-2';
                expTitle.textContent = 'Experience';
                wrap.appendChild(expTitle);
                snap.experiences.forEach(e => {
                    const row = document.createElement('div');
                    row.className = 'text-xs border-l-2 border-gray-200 pl-3';
                    row.innerHTML = '<span class="font-bold text-[#0E0F3B]"></span> <span class="text-gray-400"></span><p class="text-gray-500 mt-0.5"></p>';
                    row.querySelector('.font-bold').textContent = e.job_title || '';
                    row.querySelector('.text-gray-400').textContent = e.duration_months ? '(' + e.duration_months + ' mos)' : '';
                    row.querySelector('p').textContent = e.job_description || '';
                    wrap.appendChild(row);
                });
            }

            if (snap.certifications && snap.certifications.length) {
                const certTitle = document.createElement('p');
                certTitle.className = 'text-[10px] font-bold text-gray-400 uppercase mt-2';
                certTitle.textContent = 'Certifications';
                wrap.appendChild(certTitle);
                snap.certifications.forEach(c => {
                    const row = document.createElement('p');
                    row.className = 'text-xs text-gray-600';
                    row.textContent = c.certification_name + (c.certification_from ? ' — ' + c.certification_from : '');
                    wrap.appendChild(row);
                });
            }

            if (!wrap.children.length) {
                wrap.innerHTML = '<p class="text-xs text-gray-400">Empty resume.</p>';
            }

            return wrap;
        }

        // openPostJobModal/closePostModal/previewJobImage/addCourseField/
        // removeCourseField/handleJobSubmit/the confirm→pending submit flow
        // now all live in partials/post-job-modal.blade.php (included above)
        // — same shared "Post a New Job" modal the employer/alumni pages use,
        // so there's nothing admin-specific left to define here.

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

        /* ── No-reload (AJAX) pagination ───────────────────────────
           Swaps just one table's wrapper innerHTML on a page-link click
           instead of letting the link do a normal full-page navigation —
           the rest of the page (scroll position, the other table, filters)
           never moves. Pagination itself is now handled by the
           table-pagination-bar partial embedded in each fragment (ajax
           mode) — it's event-delegated so it keeps working after a swap
           without this needing to bind()/rebind() by hand. */

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

            // Labels wrap across lines in the source HTML (e.g. "Job\n    Setup"),
            // so textContent has an internal line break that plain .trim() doesn't
            // remove — normalize all whitespace runs to a single space before
            // comparing, or these groups silently match zero checkboxes.
            const normalize = (str) => (str || '').replace(/\s+/g, ' ').trim();

            const checkedTypes = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                normalize(cb.closest('.space-y-2')?.previousElementSibling?.textContent) === 'Job Type' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const checkedSetups = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                normalize(cb.closest('.space-y-2')?.previousElementSibling?.textContent) === 'Job Setup' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const checkedDatePosted = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                normalize(cb.closest('.grid')?.previousElementSibling?.textContent) === 'Date Posted' && cb.checked
            ).map(cb => cb.closest('label').textContent.trim().toLowerCase());

            const checkedClosing = [...document.querySelectorAll('#filter-sidebar input[type=checkbox]')].filter(cb =>
                normalize(cb.closest('.grid')?.previousElementSibling?.textContent) === 'Closing Date' && cb.checked
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
                <p><strong>Industry:</strong> ${data.industry ?? 'N/A'}</p>
                <p><strong>Closing Date:</strong> ${data.closing}</p>
                <p><strong>Status:</strong> <span class="px-2 py-1 rounded-full border text-[9px] font-bold ${statusClass}"> ${data.status.toUpperCase()}</span></p>
                ${data.status === 'Declined' ? `<p><strong>Decline Reason:</strong> ${data.declineReason ?? 'N/A'}</p>` : ''}
                <div><strong>Description:</strong></div>
                <div class="job-description-content">${data.description ?? 'N/A'}</div>
            </div>`;

            const approveBtn = document.getElementById('approveBtn');
            const declineBtn = document.getElementById('declineBtn');
            const deleteBtn = document.getElementById('deleteBtn');

            // A pending post the acting staff member posted themselves
            // (My Job Postings tab — data.ownPost) can never be approved/
            // declined here: self-approval isn't allowed. A super_admin
            // approves an admin's posts and vice versa (jobManagementBaseQuery()
            // already only ever shows the OTHER role's postings on the Job
            // Posts tab), so that's the only place Approve/Decline ever appear.
            if (data.status === 'Pending' && !data.ownPost) {
                approveBtn.classList.remove('hidden');
                declineBtn.classList.remove('hidden');
                deleteBtn.classList.add('hidden');
                approveBtn.onclick = () => {
                    closeViewModal();
                    openApprovalModal(data.approveUrl);
                };
                declineBtn.onclick = () => {
                    closeViewModal();
                    openDeclineNotesModal(jobId, data.title);
                };
            } else if (data.status === 'Declined' || (data.status === 'Pending' && data.ownPost)) {
                // View-only: either the job's gone (declined — nothing left
                // to act on) or it's the acting staff member's own pending
                // post, waiting on a DIFFERENT admin/super_admin to approve it.
                approveBtn.classList.add('hidden');
                declineBtn.classList.add('hidden');
                deleteBtn.classList.add('hidden');
            } else {
                approveBtn.classList.add('hidden');
                declineBtn.classList.add('hidden');
                deleteBtn.classList.remove('hidden');
                deleteBtn.onclick = () => {
                    closeViewModal();
                    openDeleteModal(jobId, data.title, data.deleteUrl);
                };
            }

            document.getElementById('viewJobModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function closeViewModal() {
            document.getElementById('viewJobModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        //JOB APPROVAL MODAL JS
        // Fed a real route URL from openViewModal (data.approveUrl) rather than
        // inferring a <form> from a clicked button — the old dropdown-based
        // Approve button lived inside its own <form>, but this modal doesn't,
        // so submission is built here instead.
        let approvalUrl = null;

        function openApprovalModal(url) {
            approvalUrl = url;
            const modal = document.getElementById('approvalModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            lucide.createIcons();
        }

        function closeApprovalModal() {
            const modal = document.getElementById('approvalModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            approvalUrl = null;
        }

        function submitApproval() {
            if (approvalUrl) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = approvalUrl;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
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

            form.action = `{{ url('declineJobPost') }}/` + _declineId; // adjust to match your route('jobPosting.decline', ...)
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

        // handleJobSubmit()/the confirm→pending submit flow now live in
        // partials/post-job-modal.blade.php (included above).
    </script>

</body>

</html>