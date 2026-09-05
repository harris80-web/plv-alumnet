
@php
$current_page = 'user_management';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <script src="https://unpkg.com/lucide@latest"></script>
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

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-btn {
            border-bottom: 2px solid transparent;
            color: #64748b;
        }

        .tab-btn.active {
            border-bottom: 2px solid #ea580c;
            color: #ea580c;
            font-weight: 600;
        }

        .tab-btn:hover:not(.active) {
            color: #ea580c;
        }

        .status-active {
            color: #16a34a;
        }

        .status-inactive {
            color: #dc2626;
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
            @include('partials.success')
            @php $isSuperAdmin = Auth::user()->user_role == 'super_admin'; @endphp
            <!-- Tabs -->
            <div class="bg-white px-8 flex gap-8 border-b border-slate-200 shrink-0 shadow-md">
                @if ($isSuperAdmin)
                <button id="tab-admin"
                    class="tab-btn active py-3 px-2 flex items-center gap-2 text-sm transition-colors"
                    onclick="switchTab('admin')">
                    <i data-lucide="user-cog" class="w-4 h-4"></i>
                    Admin
                </button>
                @endif
                <button id="tab-alumni"
                    class="tab-btn py-3 px-2 flex items-center gap-2 text-sm transition-colors {{ $isSuperAdmin ? '' : 'active' }}"
                    onclick="switchTab('alumni')">
                    <i data-lucide="book-user" class="w-4 h-4"></i>
                    Alumni
                </button>
                <button id="tab-employer" class="tab-btn py-3 px-2 flex items-center gap-2 text-sm transition-colors"
                    onclick="switchTab('employer')">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    Employer
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-8">

                @if (Auth::user()->user_role == 'super_admin')
                <!-- ==================== ADMIN TAB ==================== -->
                <div id="content-admin" class="tab-content active">
                    <!-- ==================== METRIC CARDS ==================== -->
                    <div class="grid grid-cols-4 gap-4 mb-6">

                        <!-- Total Admins -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $adminStats['total'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Admin</p>
                        </div>

                        <!-- Active Admins -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">
                                {{ $adminStats['active'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active Admin</p>
                        </div>

                        <!-- Inactive Admins -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-[#C73D1A]">
                                {{ $adminStats['inactive'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Inactive Admin</p>
                        </div>

                        <!-- Super Admin -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-400">1</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Super Admin</p>
                        </div>

                    </div>
                    <!-- ==================== END METRIC CARDS ==================== -->


                    @if ($errors->any())
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4">
                        <i data-lucide="circle-alert" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                            <li class="text-red-600 text-xs font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                        <div class="relative w-64">
                            <i data-lucide="search"
                                class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="adminSearchInput" value="{{ request('admin_search') }}"
                                onkeydown="if(event.key==='Enter'){pvSearchNavigate('admin_search', this.value, 'adminPage')}"
                                placeholder="Search by name"
                                class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-transparent">
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Bulk Actions (visible only when admin rows are checked) -->
                            <div id="bulkAdminActions" class="hidden items-center gap-2">
                                <span id="bulkAdminCount" class="text-xs font-medium text-slate-500 mr-1"></span>
                                <button type="button" onclick="openBulkAdminPermissionsModal()"
                                    class="flex items-center gap-1.5 px-3 py-2 bg-transparent hover:bg-[#1D46A4] text-[#1D46A4] hover:text-white text-xs font-medium rounded-lg border border-[#1D46A4] transition-all uppercase">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Edit Permissions
                                </button>
                            </div>

                            <button onclick="document.getElementById('addAdminModal').classList.add('open')"
                                class="bg-[#1D46A4] hover:bg-[#0E0F3B] text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                ADD ADMIN
                            </button>
                        </div>
                      </div>
                      <div class="overflow-x-auto">
                        <table class="w-full text-left text-[11px] whitespace-nowrap">
                            <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                                <tr>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">
                                        <input type="checkbox" id="selectAllAdmins" class="bulk-checkbox">
                                    </th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Last Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">First Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Middle Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Suffix</th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Address <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Account Status <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Date Created <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Email <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
                                    <th class="px-4 py-4 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-center">
                                @forelse ($admins as $admin)
                                <tr class="hover:bg-slate-50/80 transition-colors font-medium">
                                    <td class="px-4 py-4 border-r border-slate-100">
                                        <input type="checkbox" class="admin-row-checkbox bulk-checkbox" value="{{ $admin->user_id }}">
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->user->user_last_name }}
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->user->user_first_name }}
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->user->user_middle_name }}
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->user->user_suffix }}
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->office_address }}
                                    </td>
                                    <td class="px-4 py-4 text-center border-r border-slate-100">
                                        <span
                                            class="{{ $admin->user->user_active ? 'status-active' : 'status-inactive' }} font-semibold">
                                            {{ $admin->user->user_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->user->created_at->format('m/d/y') }}
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">
                                        {{ $admin->user->user_email }}
                                    </td>
                                    <td class="px-4 py-4 text-center relative">
                                        <div class="inline-block text-left">
                                            <button
                                                class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div
                                                class="dropdown-menu absolute right-4 mt-2 w-48 origin-top-right rounded-md bg-white shadow-xl z-50 hidden">
                                                <div class="py-1">
                                                    <button type="button"
                                                        class="view-admin-modal-btn flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors"
                                                        data-last-name="{{ $admin->user->user_last_name }}"
                                                        data-first-name="{{ $admin->user->user_first_name }}"
                                                        data-middle-name="{{ $admin->user->user_middle_name ?? '' }}"
                                                        data-suffix="{{ $admin->user->user_suffix ?? '' }}"
                                                        data-email="{{ $admin->user->user_email }}"
                                                        data-address="{{ $admin->office_address }}"
                                                        data-status="{{ $admin->user->user_active ? 'Active' : 'Inactive' }}"
                                                        data-joined="{{ $admin->user->created_at->format('M d, Y') }}"
                                                        data-user-id="{{ $admin->user_id }}"
                                                        data-permissions="{{ implode(',', $admin->permissions ?? []) }}">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i>
                                                        View Profile
                                                    </button>
                                                    <button
                                                        data-route="{{ route('offices.deleteAdmin', $admin->user_id) }}"
                                                        data-firstname="{{ $admin->user->user_first_name }}"
                                                        data-lastname="{{ $admin->user->user_last_name }}"
                                                        onclick="openDeleteProfileModal(this.dataset.route, this.dataset.firstname, this.dataset.lastname)"
                                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Delete
                                                        Profile
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-400 text-sm">No admins
                                        found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                      </div>
                      <div class="px-4 pb-4">
                        @include('partials.table-pagination-bar', ['id' => 'adminTable', 'mode' => 'reload', 'paginator' => $admins, 'perPageParam' => 'admin_per_page'])
                      </div>
                    </div>
                </div>
                <!-- END ADMIN TAB -->
                @endif


                <!-- ==================== ALUMNI TAB ==================== -->
                <div id="content-alumni" class="tab-content {{ $isSuperAdmin ? '' : 'active' }}">
                    <!-- ==================== METRIC CARDS ==================== -->
                    <div class="grid grid-cols-4 gap-4 mb-6">

                        <!-- Total Alumni -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $alumniStats['total'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Alumni</p>
                        </div>

                        <!-- Active Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">
                                {{ $alumniStats['active'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active Accounts</p>
                        </div>

                        <!-- Deactivated Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-[#C73D1A]">
                                {{ $alumniStats['deactivated'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Deactivated Accounts</p>
                        </div>

                        <!-- New This Month -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-400">
                                {{ $alumniStats['newThisMonth'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">New This Month</p>
                        </div>

                    </div>
                    <!-- ==================== END METRIC CARDS ==================== -->

                    @if ($errors->any())
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4">
                        <i data-lucide="circle-alert" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                            <li class="text-red-600 text-xs font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                        <div class="flex gap-2">
                            <div class="relative w-64">
                                <i data-lucide="search"
                                    class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" id="alumniSearchInput" onkeyup="filterAlumniRows()" placeholder="Search by name or email"
                                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A]">
                            </div>
                            <button onclick="toggleAlumniFilterSidebar()"
                                class="p-2 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 text-slate-500 transition-colors">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <!-- Bulk Deactivate -->
                            <button type="button" id="bulkDeactivateAlumniBtn" onclick="openBulkDeactivateAlumniModal()"
                                class="hidden bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="user-minus" class="w-4 h-4"></i>
                                DEACTIVATE SELECTED (<span id="bulkDeactivateAlumniCount">0</span>)
                            </button>

                            <!-- Dropdown Container -->
                            <div class="relative inline-block text-left" id="alumniDropdown">
                                <button onclick="toggleDropdown()"
                                    class="bg-[#0E0F3B] hover:bg-[#1a1b5e] text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    ADD ALUMNI
                                    <i data-lucide="chevron-down" class="w-3 h-3"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div id="dropdownMenu"
                                    class="hidden absolute left-0 mt-2 w-52 origin-top-left rounded-md bg-white shadow-lg focus:outline-none z-50 ">
                                    <div class="py-1">
                                        <!-- Option 1: Manual Add -->
                                        <button onclick="openAddModal()"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-100 w-full text-left">
                                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                                            Add Alumni
                                        </button>

                                        <!-- Option 2: Import CSV -->
                                        <button onclick="triggerCSVImport()"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-100 w-full text-left">
                                            <i data-lucide="file-up" class="w-4 h-4"></i>
                                            Import CSV File
                                        </button>

                                        <!-- Option 3: Download Template -->
                                        <a href="{{ route('users.downloadAlumniCsvTemplate') }}"
                                            class="flex items-center gap-2 px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-100 w-full text-left">
                                            <i data-lucide="file-down" class="w-4 h-4"></i>
                                            Download CSV Template
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden form + file input that submits straight to the server on selection -->
                            <form id="csvImportForm" action="{{ route('users.importAlumniCsv') }}" method="POST"
                                enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="csv_file" id="csvFileInput" accept=".csv"
                                    onchange="this.form.submit()">
                            </form>

                            <!-- Export Button -->
                            <a href="{{ route('users.exportAlumniCsv') }}" id="exportBtn"
                                class="bg-[#C73D1A] hover:bg-[#a83215] text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                EXPORT CSV
                            </a>
                        </div>
                      </div>
                      <div class="overflow-x-auto">
                        <table class="w-full text-left text-[10px] whitespace-nowrap">
                            <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                                <tr>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700 w-10">
                                        <input type="checkbox" id="selectAllAlumni" class="bulk-checkbox">
                                    </th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">ID</th>
                                    <th data-sort class="px-3 py-4 font-semibold border-r border-slate-700">Last Name <i
                                            data-lucide="chevron-down" class="inline w-3 h-3 ml-1 sort-icon"></i></th>
                                    <th data-sort class="px-3 py-4 font-semibold border-r border-slate-700">First Name <i
                                            data-lucide="chevron-down" class="inline w-3 h-3 ml-1 sort-icon"></i></th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Middle Name</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Suffix</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Gender</th>
                                    <th data-sort class="px-3 py-4 font-semibold border-r border-slate-700">Program <i
                                            data-lucide="chevron-down" class="inline w-3 h-3 ml-1 sort-icon"></i></th>
                                    {{-- Section column — hidden per request, not important; keep markup for easy restore.
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Section</th>
                                    --}}
                                    <th data-sort class="px-3 py-4 font-semibold border-r border-slate-700">Batch <i
                                            data-lucide="chevron-down" class="inline w-3 h-3 ml-1 sort-icon"></i></th>
                                    <th data-sort class="px-3 py-4 font-semibold border-r border-slate-700">Email <i
                                            data-lucide="chevron-down" class="inline w-3 h-3 ml-1 sort-icon"></i></th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Status</th>
                                    <th class="px-3 py-4 font-semibold text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="alumniTbody" class="divide-y divide-slate-100">
                                @forelse ($alumni as $alumnus)

                                @php
                                $status = $alumnus->user->user_active ? 'Active' : 'Deactivated';
                                $statusClass = $alumnus->user->user_active
                                ? 'bg-green-100 text-green-700 border-green-200'
                                : 'bg-red-100 text-red-700 border-red-200';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors text-center"
                                    data-search="{{ mb_strtolower(trim(($alumnus->user?->user_last_name ?? '') . ' ' . ($alumnus->user?->user_first_name ?? '') . ' ' . ($alumnus->user?->user_middle_name ?? '') . ' ' . ($alumnus->user?->user_email ?? ''))) }}"
                                    data-idno="{{ mb_strtolower((string) $alumnus->user_id) }}"
                                    data-lastname="{{ mb_strtolower($alumnus->user?->user_last_name ?? '') }}"
                                    data-firstname="{{ mb_strtolower($alumnus->user?->user_first_name ?? '') }}"
                                    data-middlename="{{ mb_strtolower($alumnus->user?->user_middle_name ?? '') }}"
                                    data-program="{{ $alumnus->program->program_name ?? '' }}"
                                    data-batch="{{ optional($alumnus->alumnus_batch)->format('Y') }}"
                                    data-status="{{ $status }}">
                                    <td class="px-3 py-3 border-r border-slate-100">
                                        <input type="checkbox" class="alumni-row-checkbox bulk-checkbox"
                                            value="{{ $alumnus->user_id }}">
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user?->user_last_name }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user?->user_first_name }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user?->user_middle_name }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user?->user_suffix ?? '' }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ \App\Models\Alumnus::genderLabels()[$alumnus->alumnus_gender] ?? 'N/A' }}
                                    </td>
                                    <td
                                        class="px-3 py-3 font-medium text-black border-r border-slate-100 leading-tight">
                                        {{ $alumnus->program->program_name ?? 'N/A' }}
                                    </td>
                                    {{-- Section column — hidden per request, not important; keep markup for easy restore.
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->section->section_name ?? 'N/A' }}
                                    </td>
                                    --}}
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ optional($alumnus->alumnus_batch)->format('Y') }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user?->user_email }}
                                    </td>
                                    <td class="px-3 py-3 border-r border-slate-100">
                                        <span
                                            class="px-2 py-1 rounded-full border text-[9px] font-bold {{ $statusClass }}">
                                            {{ strtoupper($status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center relative">
                                        <div class="inline-block text-left">
                                            <button
                                                class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div
                                                class="dropdown-menu absolute right-4 mt-2 w-56 origin-top-right rounded-md bg-white shadow-xl z-50 hidden">
                                                <div class="py-1">
                                                    <a href="{{ route('alumni.show', $alumnus->user_id) }}"
                                                        class="flex items-center w-full px-4 py-2.5 text-sm text-[#0E0F3B] hover:bg-slate-50 transition-colors">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-3"></i> View Alumni
                                                    </a>
                                                    @if ($alumnus->user->user_active)
                                                    <button
                                                        onclick="openDeactivateModal('{{ $alumnus->user->user_first_name }}', '{{ $alumnus->user->user_last_name }}', {{ $alumnus->user_id }})"
                                                        class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="user-minus" class="w-4 h-4 mr-3"></i> Deactivate
                                                        Account
                                                    </button>
                                                    @else
                                                    <button
                                                        onclick="openActivateModal('{{ $alumnus->user->user_first_name }}', '{{ $alumnus->user->user_last_name }}', {{ $alumnus->user_id }})"
                                                        class="flex items-center w-full px-4 py-2.5 text-sm text-green-600 hover:bg-green-50 transition-colors">
                                                        <i data-lucide="user-plus" class="w-4 h-4 mr-3"></i> Activate
                                                        Account
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <div style="display:none;">
                                                <form id="deactivateForm_{{ $alumnus->user_id }}"
                                                    action="{{ route('alumni.deactivateAlumnus', $alumnus->user_id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="deactivate-reason"
                                                        id="deactivateReason_{{ $alumnus->user_id }}">
                                                </form>

                                                <form id="activateForm_{{ $alumnus->user_id }}"
                                                    action="{{ route('alumni.activateAlumnus', $alumnus->user_id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="activate-reason"
                                                        id="activateReason_{{ $alumnus->user_id }}">
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center text-slate-400 text-sm">No alumni
                                        found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <p id="alumniNoSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching alumni.</p>
                      </div>
                      <div class="px-4 pb-4">
                        @include('partials.table-pagination-bar', ['id' => 'alumniTable', 'mode' => 'reload', 'paginator' => $alumni, 'perPageParam' => 'alumni_per_page'])
                      </div>
                    </div>
                </div>
                <!-- END ALUMNI TAB -->

                <!-- ==================== EMPLOYER TAB ==================== -->
                <div id="content-employer" class="tab-content">
                    <!-- ==================== METRIC CARDS ==================== -->
                    <div class="grid grid-cols-4 gap-4 mb-6">

                        <!-- Total Employer -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">
                                {{ $employerStats['total'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Employer</p>
                        </div>

                        <!-- Awaiting Approval -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-400">
                                {{ $employerStats['awaitingApproval'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Awaiting Approval</p>
                        </div>

                        <!-- Active Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">
                                {{ $employerStats['active'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active Employers</p>
                        </div>

                        <!-- Deactivated Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-[#C73D1A]">
                                {{ $employerStats['deactivated'] }}
                            </p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Deactivated Accounts</p>
                        </div>
                    </div>
                    <!-- ==================== END METRIC CARDS ==================== -->

                    <!-- Awaiting Approval -->
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-orange-600 mb-3">Employers Awaiting Approval:</p>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                            <div id="employerPendingTableWrap">
                                @include('partials.user-management.employer-pending-table')
                            </div>
                        </div>
                    </div>

                    <!-- Approved Employers -->
                    <div>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                            <div class="flex gap-2">
                                <div class="relative w-64">
                                    <i data-lucide="search"
                                        class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" id="employerSearchInput" onkeyup="filterEmployerRows()" placeholder="Search by Company Name"
                                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A]">
                                </div>
                                <button onclick="toggleEmployerFilter()"
                                    class="p-2 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 text-slate-500 transition-colors">
                                    <i data-lucide="filter" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <!-- Bulk Deactivate -->
                                <button type="button" id="bulkDeactivateEmployerBtn" onclick="openBulkDeactivateEmployerModal()"
                                    class="hidden bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold items-center gap-2 shadow-sm transition-all">
                                    <i data-lucide="user-minus" class="w-4 h-4"></i>
                                    DEACTIVATE SELECTED (<span id="bulkDeactivateEmployerCount">0</span>)
                                </button>
                                <button onclick="exportEmployersToCSV()"
                                    class="bg-[#C73D1A] hover:bg-[#a83215] text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    EXPORT CSV
                                </button>
                            </div>
                          </div>

                        <div id="employerApprovedTableWrap">
                            @include('partials.user-management.employer-approved-table')
                        </div>
                        </div>
                    </div>
                </div>
                <!-- END EMPLOYER TAB -->

            </div>
        </main>
    </div>

    <!-- ==================== ADD ADMIN MODAL ==================== -->
    <div id="addAdminModal" class="modal-overlay" onclick="if(event.target===this) closeModal('addAdminModal')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <form action="{{ route('users.addAdmin') }}" method="POST">
                @csrf
                <div class="bg-[#0E0F3B] px-8 py-5 flex justify-between items-center">
                    <h2 class="text-white text-lg font-bold">Admin Management</h2>
                    <button type="button" onclick="closeModal('addAdminModal')"
                        class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="px-8 py-6 space-y-3">
                    <div class="pb-1 border-b border-slate-100">
                        <p class="text-[10px] font-bold text-[#C73D1A] uppercase">Personal Information</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_first_name" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">First
                            Name:</label>
                        <input type="text" name="user_first_name" placeholder="Enter First Name here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_middle_name" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Middle
                            Name:</label>
                        <input type="text" name="user_middle_name" placeholder="Enter Middle Name here"
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_last_name" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Last
                            Name:</label>
                        <input type="text" name="user_last_name" placeholder="Enter Last Name here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_suffix"
                            class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Suffix:</label>
                        <input type="text" name="user_suffix" placeholder="e.g. Jr., III"
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="office_address"
                            class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Address:</label>
                        <input type="text" name="office_address" placeholder="Enter Address here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>

                    <div class="pt-2 pb-1 border-b border-slate-100">
                        <p class="text-[10px] font-bold text-[#C73D1A] uppercase">Account Details</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_email"
                            class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Email:</label>
                        <input type="email" name="user_email" placeholder="Enter Email here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_password"
                            class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Password:</label>
                        <div class="flex-1 relative group">
                            <input type="password" name="user_password" id="adminPass" placeholder="••••••••" required
                                class="w-full px-3 py-1.5 border border-[#0E0F3B] group-hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                            <button type="button" onclick="togglePassword('adminPass', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#C73D1A]">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_password_confirmation"
                            class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Confirm Password:</label>
                        <div class="flex-1 relative group">
                            <input type="password" name="user_password_confirmation" id="adminConfirmPass"
                                placeholder="••••••••" required
                                class="w-full px-3 py-1.5 border border-[#0E0F3B] group-hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                            <button type="button" onclick="togglePassword('adminConfirmPass', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#C73D1A]">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2 pb-1 border-b border-slate-100">
                        <p class="text-[10px] font-bold text-[#C73D1A] uppercase">Feature Access</p>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 py-1">
                        @foreach (\App\Models\Office::PERMISSIONS as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-[#0E0F3B]">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" checked>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="px-8 pb-7 flex justify-end gap-4">
                    <button type="button" onclick="closeModal('addAdminModal')"
                        class="px-8 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] rounded-lg text-sm font-bold hover:bg-[#0E0F3B] hover:text-white transition-all uppercase">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-8 py-2 bg-[#0E0F3B] hover:bg-blue-900 text-white rounded-lg text-sm font-bold transition-colors uppercase">
                        Add Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE ADMIN PROFILE HIDDEN FORM — place this outside the foreach -->
    <div style="display:none;">
        <form id="deleteProfileForm" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" name="delete-reason" id="deleteProfileReasonInput">
        </form>
    </div>

    {{-- ===================== DELETE ADMIN PROFILE MODAL ===================== --}}
    <div id="deleteProfileModal"
        class="fixed inset-0 z-[200] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeleteProfileModal()"></div>
        <div id="deleteProfileContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="trash-2" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1 text-center">Delete Admin Account</h3>
                <p id="deleteProfileAdminName" class="text-slate-400 text-xs text-center mb-5"></p>
                <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                    Reason for Deletion <span class="text-red-500">*</span>
                </label>
                <textarea id="deleteProfileReason" rows="4"
                    placeholder="Enter your reason for deleting this admin account..."
                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                <p id="deleteProfileError" class="text-red-500 text-xs mt-1 hidden">
                    Please provide a reason before deleting.
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeDeleteProfileModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button onclick="submitDeleteProfile()"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>


    <!-- ==================== ADD ALUMNI MODAL ==================== -->
    <div id="addAlumniModal" class="modal-overlay" onclick="if(event.target===this) closeModal('addAlumniModal')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden">
            <form action="{{ route('users.addAlumnus') }}" method="POST">
                @csrf
                <div class="bg-[#0E0F3B] px-8 py-5 flex justify-between items-center">
                    <h2 class="text-white text-lg font-bold">Alumni Management</h2>
                    <button type="button" onclick="closeModal('addAlumniModal')"
                        class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">First Name:</label>
                        <input type="text" name="user_first_name" placeholder="Enter First Name here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Middle Name:</label>
                        <input type="text" name="user_middle_name" placeholder="Enter Middle Name here"
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Last Name:</label>
                        <input type="text" name="user_last_name" placeholder="Enter Last Name here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Suffix:</label>
                        <input type="text" name="user_suffix" placeholder="e.g. Jr., III"
                            class="w-44 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Gender:</label>
                        <select name="alumnus_gender" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white transition-all">
                            <option value="" disabled selected>Select Gender</option>
                            @foreach (\App\Models\Alumnus::genderLabels() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Program:</label>
                        <select name="program_id" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white transition-all truncate w-full">
                            <option value="" disabled selected>Select Undergraduate Program</option>
                            @foreach ($programs as $program)
                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Batch:</label>
                        <input type="date" name="alumnus_batch" max="{{ now()->addYear()->toDateString() }}" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Section:</label>
                        <select name="section_id" required
                            class="w-44 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white transition-all">
                            <option value="" disabled selected>Select Section here</option>
                            @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Email:</label>
                        <input type="email" name="user_email" placeholder="Enter Email here" required
                            class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                </div>

                <div class="px-8 pb-7 flex justify-end gap-4">
                    <button type="button" onclick="closeModal('addAlumniModal')"
                        class="px-8 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] rounded-lg text-sm font-bold hover:bg-[#0E0F3B] hover:text-white transition-all uppercase">
                        CANCEL
                    </button>
                    <button type="submit"
                        class="px-8 py-2 bg-[#0E0F3B] hover:bg-blue-900 text-white rounded-lg text-sm font-bold transition-all uppercase">
                        ADD ALUMNI
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ACTIVATE/DEACTIVATE ALUMNI ACCOUNT MODAL CONFIRMATION -->
    <!-- ===== DEACTIVATE ALUMNI MODAL ===== -->
    <div id="deactivateAlumniModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeactivateModal()"></div>
        <div id="deactivateAlumniContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-minus" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1">Deactivate Account</h3>
                <p id="deactivateAlumniName" class="text-slate-400 text-xs font-medium mb-3"></p>
                <p class="text-slate-500 text-sm leading-relaxed mb-4">
                    Are you sure you want to <span class="font-bold text-red-600">deactivate</span> this account?
                </p>
                <div class="text-left">
                    <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                        Reason for Deactivation <span class="text-red-500">*</span>
                    </label>
                    <textarea id="deactivateAlumniReason" rows="3"
                        placeholder="Enter reason for deactivating this account..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                    <div id="deactivateAlumniError"
                        class="hidden mt-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-red-500"></i>
                        <span class="text-red-600 text-xs font-medium">Please provide a reason before
                            deactivating.</span>
                    </div>
                </div>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeDeactivateModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="deactivateAlumniSubmitBtn"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Deactivate
                </button>
            </div>
        </div>
    </div>

    <!-- ===== ACTIVATE ALUMNI MODAL ===== -->
    <div id="activateAlumniModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeActivateModal()"></div>
        <div id="activateAlumniContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-plus" class="w-8 h-8 text-green-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1">Activate Account</h3>
                <p id="activateAlumniName" class="text-slate-400 text-xs font-medium mb-3"></p>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Are you sure you want to <span class="font-bold text-green-600">activate</span> this account?
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeActivateModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="activateAlumniSubmitBtn"
                    class="flex-1 py-2.5 bg-green-600 text-white rounded-lg text-xs font-bold hover:bg-green-700 transition-all uppercase">
                    Yes, Activate
                </button>
            </div>
        </div>
    </div>

    <!-- ===== BULK DEACTIVATE ALUMNI MODAL ===== -->
    <div id="bulkDeactivateAlumniModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeBulkDeactivateAlumniModal()"></div>
        <div id="bulkDeactivateAlumniContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-minus" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1">Deactivate Selected Accounts</h3>
                <p id="bulkDeactivateAlumniName" class="text-slate-400 text-xs font-medium mb-3"></p>
                <p class="text-slate-500 text-sm leading-relaxed mb-4">
                    Are you sure you want to <span class="font-bold text-red-600">deactivate</span> these accounts?
                </p>
                <div class="text-left">
                    <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                        Reason for Deactivation <span class="text-red-500">*</span>
                    </label>
                    <textarea id="bulkDeactivateAlumniReason" rows="3"
                        placeholder="Enter reason for deactivating these accounts..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                    <div id="bulkDeactivateAlumniError"
                        class="hidden mt-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-red-500"></i>
                        <span class="text-red-600 text-xs font-medium">Please provide a reason before
                            deactivating.</span>
                    </div>
                </div>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeBulkDeactivateAlumniModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="bulkDeactivateAlumniSubmitBtn" onclick="submitBulkDeactivateAlumni()"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Deactivate
                </button>
            </div>
        </div>
    </div>

    <form id="bulkDeactivateAlumniForm" action="{{ route('users.bulkDeactivateAlumni') }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <div id="bulkDeactivateAlumniIdsContainer"></div>
        <input type="hidden" name="deactivate-reason" id="bulkDeactivateAlumniReasonInput">
    </form>

    <!-- ===== BULK DEACTIVATE EMPLOYER MODAL ===== -->
    <div id="bulkDeactivateEmployerModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeBulkDeactivateEmployerModal()"></div>
        <div id="bulkDeactivateEmployerContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-minus" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1">Deactivate Selected Accounts</h3>
                <p id="bulkDeactivateEmployerName" class="text-slate-400 text-xs font-medium mb-3"></p>
                <p class="text-slate-500 text-sm leading-relaxed mb-4">
                    Are you sure you want to <span class="font-bold text-red-600">deactivate</span> these accounts?
                </p>
                <div class="text-left">
                    <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                        Reason for Deactivation <span class="text-red-500">*</span>
                    </label>
                    <textarea id="bulkDeactivateEmployerReason" rows="3"
                        placeholder="Enter reason for deactivating these accounts..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                    <div id="bulkDeactivateEmployerError"
                        class="hidden mt-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-red-500"></i>
                        <span class="text-red-600 text-xs font-medium">Please provide a reason before
                            deactivating.</span>
                    </div>
                </div>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeBulkDeactivateEmployerModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="bulkDeactivateEmployerSubmitBtn" onclick="submitBulkDeactivateEmployer()"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Deactivate
                </button>
            </div>
        </div>
    </div>

    <form id="bulkDeactivateEmployerForm" action="{{ route('employers.bulkDeactivateEmployer') }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <div id="bulkDeactivateEmployerIdsContainer"></div>
        <input type="hidden" name="deactivate-reason" id="bulkDeactivateEmployerReasonInput">
    </form>

    <!-- ==================== ALUMNI FILTER SIDEBAR ==================== -->
    <div id="filterSidebar" class="fixed inset-0 z-50 invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" onclick="toggleAlumniFilterSidebar()"></div>

        <div id="sidebarPanel"
            class="absolute right-0 top-0 h-full w-80 bg-white shadow-2xl translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
            <div class="px-6 py-5 flex justify-between items-center border-b border-slate-100">
                <h2 class="text-[#0E0F3B] text-xl font-bold">Filter by</h2>
                <button onclick="toggleAlumniFilterSidebar()"
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Alumni ID No.</label>
                    <input type="text" id="alumniFilterIdNo" placeholder="Enter Alumni ID No."
                        class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Last Name</label>
                    <input type="text" id="alumniFilterLastName" placeholder="Enter Last Name"
                        class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">First Name</label>
                    <input type="text" id="alumniFilterFirstName" placeholder="Enter First Name"
                        class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Middle Name</label>
                    <input type="text" id="alumniFilterMiddleName" placeholder="Enter Middle Name"
                        class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Program</label>
                    <select id="alumniFilterProgram"
                        class="w-full px-3 py-2 border border-slate-200 rounded text-sm text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white">
                        <option value="">Select Undergraduate Program</option>
                        @foreach ($programs as $program)
                        <option value="{{ $program->program_name }}">{{ $program->program_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5 border-b border-slate-100 pb-5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Batch</label>
                    <select id="alumniFilterBatch"
                        class="w-full px-3 py-2 border border-slate-200 rounded text-sm text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white">
                        <option value="">Select Batch</option>
                        @foreach ($alumniBatchYears as $batchYear)
                        <option value="{{ $batchYear }}">{{ $batchYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-3 pt-2">
                    <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider">Account Status</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="alumni-filter-status w-4 h-4 rounded border-slate-300 text-[#0E0F3B] focus:ring-[#0E0F3B]" value="Active">
                            <span class="text-sm font-bold text-[#0E0F3B] group-hover:text-slate-600">Active</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group border-b border-slate-100 pb-4">
                            <input type="checkbox" class="alumni-filter-status w-4 h-4 rounded border-slate-300 text-[#0E0F3B] focus:ring-[#0E0F3B]" value="Deactivated">
                            <span class="text-sm font-bold text-[#0E0F3B] group-hover:text-slate-600">Deactivated</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <button onclick="filterAlumniRows(); toggleAlumniFilterSidebar()"
                    class="w-full py-3 bg-[#0E0F3B] hover:brightness-110 text-white rounded font-bold text-xs tracking-widest uppercase transition-all">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== EMPLOYER FILTER SIDEBAR ==================== -->
    <div id="employerFilterSidebar"
        class="fixed inset-y-0 right-0 w-80 bg-white shadow-2xl z-[100] transform translate-x-full transition-transform duration-300 ease-in-out border-l border-slate-200">
        <div class="flex flex-col h-full">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-[#0E0F3B] text-xl font-bold">Filter by</h2>
                <button onclick="toggleEmployerFilter()" class="text-slate-400 hover:text-[#C73D1A] transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Last Name</label>
                    <input type="text" id="employerFilterLastName" placeholder="Enter Last Name"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">First Name</label>
                    <input type="text" id="employerFilterFirstName" placeholder="Enter First Name"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Middle Name</label>
                    <input type="text" id="employerFilterMiddleName" placeholder="Enter Middle Name"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Company Name</label>
                    <input type="text" id="employerFilterCompany" placeholder="Enter Company Name"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Industry/Sector</label>
                    <select id="employerFilterIndustry"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all text-[#0E0F3B]">
                        <option value="">Select Industry/Sector</option>
                        @foreach ($industries as $industry)
                        <option value="{{ mb_strtolower($industry->industry_name) }}">{{ $industry->industry_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p-6 bg-white border-t border-slate-100">
                <button onclick="filterEmployerRows(); toggleEmployerFilter()"
                    class="w-full py-3 bg-[#0E0F3B] text-white rounded font-bold text-xs uppercase tracking-widest hover:bg-[#1a1b4d] transition-all">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>

    <div id="employerFilterBackdrop"
        class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[90] hidden transition-opacity duration-300 opacity-0"
        onclick="toggleEmployerFilter()"></div>

    <!-- ==================== APPROVE/REJECT EMPLOYER MODAL ==================== -->
    <div id="employerConfirmModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-[#0E0F3B]/40 backdrop-blur-sm" onclick="closeEmployerConfirm()"></div>

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300"
            id="employerConfirmContent">
            <div class="p-8 text-center">
                <div id="empConfirmIconBox"
                    class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i id="empConfirmIcon" data-lucide="help-circle" class="w-8 h-8"></i>
                </div>
                <h3 id="empConfirmTitle" class="text-[#0E0F3B] text-xl font-bold mb-2 tracking-tight">Confirmation</h3>
                <p id="empConfirmMessage" class="text-slate-500 text-sm leading-relaxed px-2">
                    Are you sure you want to proceed?
                </p>
                <div id="empRejectReasonBox" class="hidden text-left mt-4 px-2">
                    <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea id="empRejectReasonText" rows="3"
                        placeholder="Enter reason for rejecting this employer..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 resize-none transition-all"></textarea>
                    <div id="empRejectReasonError"
                        class="hidden mt-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-red-500"></i>
                        <span class="text-red-600 text-xs font-medium">Please provide a reason before rejecting.</span>
                    </div>
                </div>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeEmployerConfirm()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="empConfirmYesBtn"
                    class="flex-1 py-2.5 text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== VIEW ADMIN MODAL ==================== -->
    <div id="viewAdminModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden">
            <div class="bg-[#0E0F3B] px-7 py-4 flex justify-between items-center">
                <h2 class="text-white text-lg font-bold">View Admin</h2>
                <button onclick="closeAdminViewModal()" class="text-gray-300 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="px-7 py-5 space-y-5">
                <section>
                    <div class="flex gap-5">
                        <div
                            class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center border border-gray-200 shrink-0">
                            <i data-lucide="user-round" class="w-10 h-10 text-slate-400"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-3 flex-grow">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Last Name</p>
                                <p id="adminModalLastName" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">First Name</p>
                                <p id="adminModalFirstName" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Middle Name</p>
                                <p id="adminModalMiddleName" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Suffix</p>
                                <p id="adminModalSuffix" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Email</p>
                                <p id="adminModalEmail" class="text-[#0E0F3B] font-medium text-sm break-all">--</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Office / Address</p>
                                <p id="adminModalAddress" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Status</p>
                                <p id="adminModalStatus" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Date Joined</p>
                                <p id="adminModalJoined" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-100">

                <section>
                    <h3 class="text-[#D95D39] font-bold text-xs uppercase mb-3 tracking-wider">Feature Access</h3>
                    <div id="adminModalPermissions" class="grid grid-cols-2 gap-x-4 gap-y-2">
                        @foreach (\App\Models\Office::PERMISSIONS as $key => $label)
                        <label class="flex items-center gap-2 text-sm text-[#0E0F3B]">
                            <input type="checkbox" class="admin-modal-permission-checkbox" value="{{ $key }}">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                    <p id="adminModalPermissionsSaved" class="hidden text-xs font-medium text-green-600 mt-2">Permissions saved.</p>
                </section>
            </div>

            <div class="px-7 py-3.5 bg-gray-50 border-t border-slate-100 flex justify-end gap-2">
                <button onclick="closeAdminViewModal()"
                    class="px-8 py-2 border border-slate-300 text-slate-600 rounded font-bold uppercase text-sm">Close</button>
                <button onclick="saveAdminPermissions()"
                    class="px-8 py-2 bg-[#1D46A4] hover:bg-[#0E0F3B] text-white rounded font-bold uppercase text-sm transition-colors">Save
                    Permissions</button>
            </div>

        </div>
    </div>

    <!-- Hidden form submitted by saveAdminPermissions() / submitBulkAdminPermissions() -->
    <form id="admin-permissions-form" method="POST" class="hidden">
        @csrf
        @method('PUT')
    </form>

    <!-- ==================== BULK EDIT ADMIN PERMISSIONS MODAL ==================== -->
    <div id="bulkAdminPermissionsModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden">
            <div class="bg-[#0E0F3B] px-7 py-4 flex justify-between items-center">
                <h2 class="text-white text-lg font-bold">Bulk Edit Permissions</h2>
                <button onclick="closeBulkAdminPermissionsModal()" class="text-gray-300 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="px-7 py-5 space-y-4">
                <p id="bulkAdminPermissionsCount" class="text-sm text-slate-600"></p>
                <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5">
                    <i data-lucide="circle-alert" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-amber-600"></i>
                    <span class="text-amber-700 text-xs font-medium">This replaces each selected admin's current
                        feature access with exactly what's checked below.</span>
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                    @foreach (\App\Models\Office::PERMISSIONS as $key => $label)
                    <label class="flex items-center gap-2 text-sm text-[#0E0F3B]">
                        <input type="checkbox" class="bulk-admin-permission-checkbox" value="{{ $key }}">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="px-7 py-3.5 bg-gray-50 border-t border-slate-100 flex justify-end gap-2">
                <button onclick="closeBulkAdminPermissionsModal()"
                    class="px-8 py-2 border border-slate-300 text-slate-600 rounded font-bold uppercase text-sm">Cancel</button>
                <button onclick="submitBulkAdminPermissions()"
                    class="px-8 py-2 bg-[#1D46A4] hover:bg-[#0E0F3B] text-white rounded font-bold uppercase text-sm transition-colors">Apply</button>
            </div>

        </div>
    </div>

    <!-- ==================== VIEW EMPLOYER MODAL ==================== -->
    <div id="viewEmployerModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
            <div class="bg-[#0E0F3B] px-7 py-4 flex justify-between items-center">
                <h2 class="text-white text-lg font-bold">View Employer</h2>
                <button onclick="closeModal()" class="text-gray-300 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="px-7 py-5 space-y-5">

                <!-- ===== COMPANY DETAILS (first) ===== -->
                <section>
                    <h3 class="text-[#D95D39] font-bold text-xs uppercase mb-3 tracking-wider">Company Details</h3>
                    <div class="flex gap-5">
                        <div
                            class="w-20 h-20 bg-blue-50 rounded-xl border border-gray-200 flex flex-col items-center justify-center shrink-0">
                            <i data-lucide="image" class="w-7 h-7 text-green-500 mb-0.5"></i>
                            <span class="text-[7px] text-gray-400 font-bold">BUSINESS LOGO</span>
                        </div>
                        <div class="grid grid-cols-3 gap-x-4 gap-y-3 flex-grow">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Company Name</p>
                                <p id="modalCompany" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Year Established</p>
                                <p id="modalYear" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Company Size</p>
                                <p id="modalSize" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Industry/Sector</p>
                                <p id="modalIndustry" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Official Website URL</p>
                                <p id="modalUrl" class="text-blue-600 font-medium text-sm">--</p>
                            </div>
                        </div>
                    </div>
                </section>

                <hr class="border-slate-100">

                <!-- ===== EMPLOYER DETAILS (second) ===== -->
                <section>
                    <h3 class="text-[#D95D39] font-bold text-xs uppercase mb-3 tracking-wider">Employer Details</h3>
                    <div class="flex gap-5">
                        <div
                            class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center border border-gray-200 shrink-0">
                            <i data-lucide="user-round" class="w-10 h-10 text-slate-400"></i>
                        </div>
                        <div class="grid grid-cols-3 gap-x-3 gap-y-8 flex-grow">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Last Name</p>
                                <p id="modalLastName" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">First Name</p>
                                <p id="modalFirstName" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Middle Name</p>
                                <p id="modalMiddleName" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Position</p>
                                <p id="modalPosition" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Email</p>
                                <p id="modalEmail" class="text-[#0E0F3B] font-medium text-sm break-all">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Contact No.</p>
                                <p id="modalContact" class="text-[#0E0F3B] font-medium text-sm">--</p>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <div class="px-7 py-3.5 bg-gray-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeModal()"
                    class="px-8 py-2 bg-[#0E0F3B] text-white rounded font-bold uppercase text-sm">Done</button>
            </div>

        </div>
    </div>

    <!-- ===== DEACTIVATE EMPLOYER MODAL ===== -->
    <div id="deactivateEmployerModal"
        class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEmployerDeactivateModal()"></div>
        <div id="deactivateEmployerContent"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-minus" class="w-8 h-8 text-red-500"></i>
                </div>
                <h3 class="text-[#0E0F3B] text-xl font-bold mb-1">Deactivate Account</h3>
                <p id="deactivateEmployerName" class="text-slate-400 text-xs font-medium mb-3"></p>
                <p class="text-slate-500 text-sm leading-relaxed mb-4">
                    Are you sure you want to <span class="font-bold text-red-600">deactivate</span> this account?
                </p>
                <div class="text-left">
                    <label class="text-xs font-bold text-[#0E0F3B] uppercase tracking-wider block mb-2">
                        Reason for Deactivation <span class="text-red-500">*</span>
                    </label>
                    <textarea id="deactivateEmployerReason" rows="3"
                        placeholder="Enter reason for deactivating this account..."
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 resize-none transition-all"></textarea>
                    <div id="deactivateEmployerError"
                        class="hidden mt-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <i data-lucide="circle-alert" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-red-500"></i>
                        <span class="text-red-600 text-xs font-medium">Please provide a reason before deactivating.</span>
                    </div>
                </div>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeEmployerDeactivateModal()"
                    class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="deactivateEmployerSubmitBtn"
                    class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-all uppercase">
                    Yes, Deactivate
                </button>
            </div>
        </div>
    </div>

    <script>
        /* ── Tab switching ──────────────────────────────────────── */
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('content-' + tab).classList.add('active');
            lucide.createIcons();
            initDropdowns();

            // Keep the URL in sync (no reload) so a pagination link clicked
            // afterward — which DOES reload the page — lands back on this
            // same tab instead of resetting to the default one.
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            history.replaceState(null, '', url);

            // Pagination links were rendered server-side against whatever
            // query string the ORIGINAL page load had — they don't know
            // about a tab switched client-side afterward. Patch every
            // pagination link's own `tab` param to match, so clicking
            // "page 2" from here actually lands back on this tab.
            document.querySelectorAll('nav[aria-label="Pagination"] a[href]').forEach(function(link) {
                const linkUrl = new URL(link.href);
                linkUrl.searchParams.set('tab', tab);
                link.href = linkUrl.toString();
            });
        }

        // Runs once on load too (not just on a manual tab click) — restores
        // whichever tab was active before a pagination-triggered reload if
        // ?tab= is present, and either way makes sure the pagination links'
        // `tab` param above gets patched in from the very first render.
        document.addEventListener('DOMContentLoaded', function() {
            const tabParam = new URLSearchParams(window.location.search).get('tab');
            const activeBtn = document.querySelector('.tab-btn.active');
            const defaultTab = activeBtn ? activeBtn.id.replace('tab-', '') : 'alumni';
            const tab = (tabParam && document.getElementById('tab-' + tabParam)) ? tabParam : defaultTab;
            switchTab(tab);
        });

        /* ── Dropdown menus ─────────────────────────────────────── */
        function initDropdowns() {
            document.querySelectorAll('.menu-button').forEach(button => {
                const newBtn = button.cloneNode(true);
                button.parentNode.replaceChild(newBtn, button);
                newBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const dropdown = newBtn.nextElementSibling;
                    document.querySelectorAll('.dropdown-menu').forEach(m => {
                        if (m !== dropdown) m.classList.add('hidden');
                    });
                    dropdown.classList.toggle('hidden');
                });
            });
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
        });

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            initDropdowns();
        });

        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
        }

        function openAddModal() {
            document.getElementById('addAlumniModal').classList.add('open');
            toggleDropdown(); // Close menu after selection
        }

        function triggerCSVImport() {
            document.getElementById('csvFileInput').click();
            toggleDropdown(); // Close menu after selection
        }

        /* ── Alumni bulk-select + bulk deactivate ─────────────────── */
        initBulkCheckboxGroup({
            header: 'selectAllAlumni',
            rowSelector: '.alumni-row-checkbox',
            onChange: function (checkedValues, checkedCount) {
                const btn = document.getElementById('bulkDeactivateAlumniBtn');
                document.getElementById('bulkDeactivateAlumniCount').textContent = checkedCount;
                btn.classList.toggle('hidden', checkedCount === 0);
                btn.classList.toggle('flex', checkedCount > 0);
            },
        });

        /* ── Employer bulk-select + bulk deactivate ───────────────── */
        function initEmployerBulkCheckboxes() {
            initBulkCheckboxGroup({
                header: 'selectAllEmployers',
                rowSelector: '.employer-row-checkbox',
                onChange: function (checkedValues, checkedCount) {
                    const btn = document.getElementById('bulkDeactivateEmployerBtn');
                    document.getElementById('bulkDeactivateEmployerCount').textContent = checkedCount;
                    btn.classList.toggle('hidden', checkedCount === 0);
                    btn.classList.toggle('flex', checkedCount > 0);
                },
            });
        }
        initEmployerBulkCheckboxes();

        function getSelectedEmployerIds() {
            return Array.from(document.querySelectorAll('.employer-row-checkbox:checked')).map(cb => cb.value);
        }

        function openBulkDeactivateEmployerModal() {
            const selected = getSelectedEmployerIds();
            if (selected.length === 0) return;

            document.getElementById('bulkDeactivateEmployerName').textContent = selected.length + ' account(s) selected';
            document.getElementById('bulkDeactivateEmployerReason').value = '';
            document.getElementById('bulkDeactivateEmployerError').classList.add('hidden');

            const modal = document.getElementById('bulkDeactivateEmployerModal');
            const content = document.getElementById('bulkDeactivateEmployerContent');
            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeBulkDeactivateEmployerModal() {
            const modal = document.getElementById('bulkDeactivateEmployerModal');
            const content = document.getElementById('bulkDeactivateEmployerContent');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }

        function submitBulkDeactivateEmployer() {
            const reason = document.getElementById('bulkDeactivateEmployerReason').value.trim();
            const error = document.getElementById('bulkDeactivateEmployerError');
            if (!reason) {
                error.classList.remove('hidden');
                lucide.createIcons();
                return;
            }

            const container = document.getElementById('bulkDeactivateEmployerIdsContainer');
            container.innerHTML = '';
            getSelectedEmployerIds().forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });

            document.getElementById('bulkDeactivateEmployerReasonInput').value = reason;
            document.getElementById('bulkDeactivateEmployerForm').submit();
        }

        /* ── No-reload (AJAX) pagination ───────────────────────────
           Swaps just one table's wrapper innerHTML on a page-link click
           instead of letting the link do a normal full-page navigation —
           the rest of the page (scroll position, the other tab/table,
           anything the user had open) never moves. Re-binds pagination
           links on the freshly-fetched markup too, since those are new
           DOM nodes with no listeners of their own. */
        function initAjaxTablePagination(wrapId, pageParam, fetchUrl, reinit) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;

            function bind() {
                wrap.querySelectorAll('nav[aria-label="Pagination"] a[href]').forEach(function (link) {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const page = new URL(this.href).searchParams.get(pageParam) || 1;
                        fetch(fetchUrl + '?' + pageParam + '=' + page, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        })
                            .then(function (r) { return r.text(); })
                            .then(function (html) {
                                wrap.innerHTML = html;
                                if (window.lucide) lucide.createIcons();
                                initDropdowns();
                                if (reinit) reinit();
                                bind();
                            });
                    });
                });
            }

            bind();
        }

        // Pagination for these two tables is now handled by the
        // table-pagination-bar partial embedded in each fragment (ajax
        // mode) — it's event-delegated so it keeps working after a swap
        // without needing the old bind()/rebind() dance this used to do.
        window.reinitEmployerApprovedTable = function () {
            initEmployerBulkCheckboxes();
            initViewEmployerButtons();
        };

        /* ── Admin bulk-select + bulk permission edit ─────────────── */
        const BULK_ADMIN_PERMISSIONS_URL = "{{ route('offices.bulkUpdatePermissions') }}";

        initBulkCheckboxGroup({
            header: 'selectAllAdmins',
            rowSelector: '.admin-row-checkbox',
            onChange: function (checkedValues, checkedCount) {
                const bar = document.getElementById('bulkAdminActions');
                document.getElementById('bulkAdminCount').textContent = checkedCount + ' selected';
                bar.classList.toggle('hidden', checkedCount === 0);
                bar.classList.toggle('flex', checkedCount > 0);
            },
        });

        function getSelectedAdminIds() {
            return Array.from(document.querySelectorAll('.admin-row-checkbox:checked')).map(cb => cb.value);
        }

        function openBulkAdminPermissionsModal() {
            const ids = getSelectedAdminIds();
            if (ids.length === 0) return;
            document.getElementById('bulkAdminPermissionsCount').textContent =
                ids.length + ' admin' + (ids.length === 1 ? '' : 's') + ' selected';
            document.querySelectorAll('.bulk-admin-permission-checkbox').forEach(cb => cb.checked = false);
            lucide.createIcons();
            document.getElementById('bulkAdminPermissionsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeBulkAdminPermissionsModal() {
            document.getElementById('bulkAdminPermissionsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function submitBulkAdminPermissions() {
            const ids = getSelectedAdminIds();
            if (ids.length === 0) return;
            const checked = Array.from(document.querySelectorAll('.bulk-admin-permission-checkbox:checked')).map(cb => cb.value);

            const form = document.getElementById('admin-permissions-form');
            form.action = BULK_ADMIN_PERMISSIONS_URL;
            form.querySelectorAll('input[name="permissions[]"], input[name="ids[]"]').forEach(el => el.remove());
            ids.forEach(function(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            checked.forEach(function(val) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'permissions[]';
                input.value = val;
                form.appendChild(input);
            });
            form.submit();
        }

        function getSelectedAlumniIds() {
            return Array.from(document.querySelectorAll('.alumni-row-checkbox:checked')).map(cb => cb.value);
        }

        function openBulkDeactivateAlumniModal() {
            const selected = getSelectedAlumniIds();
            if (selected.length === 0) return;

            document.getElementById('bulkDeactivateAlumniName').textContent = selected.length + ' account(s) selected';
            document.getElementById('bulkDeactivateAlumniReason').value = '';
            document.getElementById('bulkDeactivateAlumniError').classList.add('hidden');

            const modal = document.getElementById('bulkDeactivateAlumniModal');
            const content = document.getElementById('bulkDeactivateAlumniContent');
            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeBulkDeactivateAlumniModal() {
            const modal = document.getElementById('bulkDeactivateAlumniModal');
            const content = document.getElementById('bulkDeactivateAlumniContent');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }

        function submitBulkDeactivateAlumni() {
            const reason = document.getElementById('bulkDeactivateAlumniReason').value.trim();
            const error = document.getElementById('bulkDeactivateAlumniError');
            if (!reason) {
                error.classList.remove('hidden');
                lucide.createIcons();
                return;
            }

            const container = document.getElementById('bulkDeactivateAlumniIdsContainer');
            container.innerHTML = '';
            getSelectedAlumniIds().forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });

            document.getElementById('bulkDeactivateAlumniReasonInput').value = reason;
            document.getElementById('bulkDeactivateAlumniForm').submit();
        }

        // Optional: Close dropdown if user clicks outside of it
        window.onclick = function(event) {
            if (!event.target.closest('#alumniDropdown')) {
                const menu = document.getElementById('dropdownMenu');
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            }
        }

        //DELETE ADMIN MODAL JS
        let _deleteProfileRoute = '';

        // ── open modal ─────────────────────────────────────────────────────
        function openDeleteProfileModal(route, firstName, lastName) {
            _deleteProfileRoute = route;

            document.getElementById('deleteProfileAdminName').textContent = firstName + ' ' + lastName;
            document.getElementById('deleteProfileReason').value = '';
            document.getElementById('deleteProfileError').classList.add('hidden');

            const modal = document.getElementById('deleteProfileModal');
            const content = document.getElementById('deleteProfileContent');

            modal.classList.remove('invisible');
            requestAnimationFrame(() => {
                modal.classList.add('bg-black/30');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            });
        }

        // ── close modal ────────────────────────────────────────────────────
        function closeDeleteProfileModal() {
            const modal = document.getElementById('deleteProfileModal');
            const content = document.getElementById('deleteProfileContent');

            content.classList.remove('scale-100');
            content.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('invisible');
            }, 300);
        }

        // ── submit ───────────────────────────────────────────────────
        function submitDeleteProfile() {
            const reason = document.getElementById('deleteProfileReason').value.trim();
            const error = document.getElementById('deleteProfileError');

            if (!reason) {
                error.classList.remove('hidden');
                return;
            }

            error.classList.add('hidden');

            const form = document.getElementById('deleteProfileForm');
            document.getElementById('deleteProfileReasonInput').value = reason;
            form.action = _deleteProfileRoute;
            form.submit();
        }

        // EMPLOYERS CSV EXPORT JS LOGIC
        function exportEmployersToCSV() {
            const filename = 'approved_employers_' + new Date().toISOString().slice(0, 10) + '.csv';
            const csv = [];

            // #content-employer has TWO tables (pending-approval, then
            // approved) — a bare 'table' selector always grabbed the first
            // one, so this button (labeled/filenamed for approved
            // employers) was silently exporting the pending queue instead.
            // #employerTbody is the approved table's own tbody specifically.
            const table = document.getElementById('employerTbody')?.closest('table') || document.querySelector('table');
            const rows = table.querySelectorAll("tr");

            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll("th, td");

                for (let j = 0; j < cols.length; j++) {
                    // Skip the last column (Actions)
                    if (j === cols.length - 1) continue;

                    // Clean text: remove extra spaces, newlines, and escape double quotes
                    let data = cols[j].innerText.trim();
                    data = data.replace(/"/g, '""'); // Escape double quotes

                    // Wrap in double quotes to handle internal commas
                    row.push('"' + data + '"');
                }
                csv.push(row.join(","));
            }

            // Create the CSV file and trigger download
            const csvFile = new Blob([csv.join("\n")], {
                type: "text/csv;charset=utf-8;"
            });
            const downloadLink = document.createElement("a");
            const url = URL.createObjectURL(csvFile);

            downloadLink.setAttribute("href", url);
            downloadLink.setAttribute("download", filename);
            downloadLink.style.visibility = 'hidden';

            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }

        /* ── Modal helpers ──────────────────────────────────────── */
        function closeModal(id) {
            if (id) {
                document.getElementById(id).classList.remove('open');
            } else {
                document.getElementById('viewEmployerModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        /* ── Password toggle ────────────────────────────────────── */
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('[data-lucide]');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        /* ── Alumni Filter Sidebar ──────────────────────────────── */
        function toggleAlumniFilterSidebar() {
            const sidebar = document.getElementById('filterSidebar');
            const panel = document.getElementById('sidebarPanel');
            if (sidebar.classList.contains('invisible')) {
                sidebar.classList.remove('invisible');
                setTimeout(() => panel.classList.remove('translate-x-full'), 10);
            } else {
                panel.classList.add('translate-x-full');
                setTimeout(() => sidebar.classList.add('invisible'), 300);
            }
        }

        // ── ALUMNI TAB: SEARCH + SIDEBAR FILTERS ──
        function filterAlumniRows() {
            const query = document.getElementById('alumniSearchInput').value.trim().toLowerCase();
            const idNo = document.getElementById('alumniFilterIdNo').value.trim().toLowerCase();
            const lastName = document.getElementById('alumniFilterLastName').value.trim().toLowerCase();
            const firstName = document.getElementById('alumniFilterFirstName').value.trim().toLowerCase();
            const middleName = document.getElementById('alumniFilterMiddleName').value.trim().toLowerCase();
            const program = document.getElementById('alumniFilterProgram').value;
            const batch = document.getElementById('alumniFilterBatch').value;
            const statuses = [...document.querySelectorAll('.alumni-filter-status:checked')].map(cb => cb.value);

            const rows = document.querySelectorAll('#alumniTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query) &&
                    (!idNo || row.dataset.idno.includes(idNo)) &&
                    (!lastName || row.dataset.lastname.includes(lastName)) &&
                    (!firstName || row.dataset.firstname.includes(firstName)) &&
                    (!middleName || row.dataset.middlename.includes(middleName)) &&
                    (!program || row.dataset.program === program) &&
                    (!batch || row.dataset.batch === batch) &&
                    (statuses.length === 0 || statuses.includes(row.dataset.status));
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            const noResults = document.getElementById('alumniNoSearchResults');
            if (noResults) noResults.classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // ── EMPLOYER TAB (Approved list): SEARCH + SIDEBAR FILTERS ──
        function filterEmployerRows() {
            const query = document.getElementById('employerSearchInput').value.trim().toLowerCase();
            const lastName = document.getElementById('employerFilterLastName').value.trim().toLowerCase();
            const firstName = document.getElementById('employerFilterFirstName').value.trim().toLowerCase();
            const middleName = document.getElementById('employerFilterMiddleName').value.trim().toLowerCase();
            const company = document.getElementById('employerFilterCompany').value.trim().toLowerCase();
            const industry = document.getElementById('employerFilterIndustry').value;

            const rows = document.querySelectorAll('#employerTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query) &&
                    (!lastName || row.dataset.lastname.includes(lastName)) &&
                    (!firstName || row.dataset.firstname.includes(firstName)) &&
                    (!middleName || row.dataset.middlename.includes(middleName)) &&
                    (!company || row.dataset.company.includes(company)) &&
                    (!industry || row.dataset.industry === industry);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            const noResults = document.getElementById('employerNoSearchResults');
            if (noResults) noResults.classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        /* ── Alumni Activate and Deactivate Modal ───────────────────────────────── */
        /* ── Deactivate Alumni Modal ────────────────────────────── */
        function openDeactivateModal(firstName, lastName, userId) {
            document.getElementById('deactivateAlumniName').textContent = firstName + ' ' + lastName;
            document.getElementById('deactivateAlumniReason').value = '';
            document.getElementById('deactivateAlumniError').classList.add('hidden');

            const submitBtn = document.getElementById('deactivateAlumniSubmitBtn');
            const newBtn = submitBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newBtn, submitBtn);
            newBtn.id = 'deactivateAlumniSubmitBtn';

            newBtn.onclick = function() {
                const reason = document.getElementById('deactivateAlumniReason').value.trim();
                const error = document.getElementById('deactivateAlumniError');
                if (!reason) {
                    error.classList.remove('hidden');
                    lucide.createIcons();
                    return;
                }
                error.classList.add('hidden');
                document.getElementById('deactivateReason_' + userId).value = reason;
                document.getElementById('deactivateForm_' + userId).submit();
            };

            const modal = document.getElementById('deactivateAlumniModal');
            const content = document.getElementById('deactivateAlumniContent');
            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeDeactivateModal() {
            const modal = document.getElementById('deactivateAlumniModal');
            const content = document.getElementById('deactivateAlumniContent');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }

        /* ── Activate Alumni Modal ──────────────────────────────── */
        function openActivateModal(firstName, lastName, userId) {
            document.getElementById('activateAlumniName').textContent = firstName + ' ' + lastName;

            const submitBtn = document.getElementById('activateAlumniSubmitBtn');
            const newBtn = submitBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newBtn, submitBtn);
            newBtn.id = 'activateAlumniSubmitBtn';

            newBtn.onclick = function() {
                document.getElementById('activateForm_' + userId).submit();
            };

            const modal = document.getElementById('activateAlumniModal');
            const content = document.getElementById('activateAlumniContent');
            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeActivateModal() {
            const modal = document.getElementById('activateAlumniModal');
            const content = document.getElementById('activateAlumniContent');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }

        /* ── Employer Filter Sidebar ────────────────────────────── */
        function toggleEmployerFilter() {
            const sidebar = document.getElementById('employerFilterSidebar');
            const backdrop = document.getElementById('employerFilterBackdrop');
            if (sidebar.classList.contains('translate-x-full')) {
                sidebar.classList.remove('translate-x-full');
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.add('opacity-100'), 10);
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('translate-x-full');
                backdrop.classList.remove('opacity-100');
                setTimeout(() => backdrop.classList.add('hidden'), 300);
                document.body.style.overflow = 'auto';
            }
        }

        /* ── Employer Confirm Modal ─────────────────────────────── */
        let _rejectEmployerUserId = null;

        function openEmployerConfirm(action, companyName, userId) {
            const modal = document.getElementById('employerConfirmModal');
            const content = document.getElementById('employerConfirmContent');
            const iconBox = document.getElementById('empConfirmIconBox');
            const icon = document.getElementById('empConfirmIcon');
            const title = document.getElementById('empConfirmTitle');
            const message = document.getElementById('empConfirmMessage');
            const rejectBox = document.getElementById('empRejectReasonBox');
            const rejectText = document.getElementById('empRejectReasonText');
            const rejectError = document.getElementById('empRejectReasonError');

            // Reset reject fields every open
            rejectBox.classList.add('hidden');
            rejectText.value = '';
            rejectError.classList.add('hidden');

            // Clone yes button to remove stale listeners
            const yesBtn = document.getElementById('empConfirmYesBtn');
            const newBtn = yesBtn.cloneNode(true);
            yesBtn.parentNode.replaceChild(newBtn, yesBtn);
            newBtn.id = 'empConfirmYesBtn';

            if (action === 'approve') {
                title.innerText = "Approve Employer";
                message.innerHTML = `Are you sure you want to <span class="font-bold text-emerald-600">approve</span> <b>${companyName}</b>?`;
                iconBox.className = "w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4 text-emerald-600";
                icon.setAttribute('data-lucide', 'check-circle');
                newBtn.className = "flex-1 py-2.5 bg-emerald-600 text-white rounded-lg text-xs font-bold transition-all uppercase hover:bg-emerald-700";
                newBtn.innerText = "Yes, Approve";

                newBtn.onclick = function() {
                    document.getElementById('approveEmployerForm_' + userId).submit();
                };

            } else {
                _rejectEmployerUserId = userId;
                title.innerText = "Reject Employer";
                message.innerHTML = `Are you sure you want to <span class="font-bold text-orange-600">reject</span> <b>${companyName}</b>?`;
                iconBox.className = "w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-4 text-orange-600";
                icon.setAttribute('data-lucide', 'x-circle');
                newBtn.className = "flex-1 py-2.5 bg-orange-600 text-white rounded-lg text-xs font-bold transition-all uppercase hover:bg-orange-700";
                newBtn.innerText = "Yes, Reject";
                rejectBox.classList.remove('hidden');

                newBtn.onclick = function() {
                    const reason = rejectText.value.trim();
                    if (!reason) {
                        rejectError.classList.remove('hidden');
                        lucide.createIcons();
                        return;
                    }
                    rejectError.classList.add('hidden');
                    document.getElementById('rejectEmployerReason_' + _rejectEmployerUserId).value = reason;
                    document.getElementById('rejectEmployerForm_' + _rejectEmployerUserId).submit();
                };
            }

            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeEmployerConfirm() {
            const modal = document.getElementById('employerConfirmModal');
            const content = document.getElementById('employerConfirmContent');
            content.classList.add('scale-95');
            document.getElementById('empRejectReasonBox').classList.add('hidden');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }

        /* ── View/Edit Admin Modal ──────────────────────────────── */
        const ADMIN_PERMISSIONS_URL_TEMPLATE = "{{ route('offices.updatePermissions', ['id' => '__ID__']) }}";
        let currentAdminUserId = null;

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('viewAdminModal');
            const viewButtons = document.querySelectorAll('.view-admin-modal-btn');

            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const fields = {
                        'adminModalLastName': 'data-last-name',
                        'adminModalFirstName': 'data-first-name',
                        'adminModalMiddleName': 'data-middle-name',
                        'adminModalSuffix': 'data-suffix',
                        'adminModalEmail': 'data-email',
                        'adminModalAddress': 'data-address',
                        'adminModalStatus': 'data-status',
                        'adminModalJoined': 'data-joined'
                    };

                    for (const [id, attribute] of Object.entries(fields)) {
                        const element = document.getElementById(id);
                        if (element) element.textContent = this.getAttribute(attribute) || '--';
                    }

                    currentAdminUserId = this.getAttribute('data-user-id');
                    const granted = (this.getAttribute('data-permissions') || '').split(',').filter(Boolean);
                    document.querySelectorAll('.admin-modal-permission-checkbox').forEach(cb => {
                        cb.checked = granted.includes(cb.value);
                    });
                    document.getElementById('adminModalPermissionsSaved').classList.add('hidden');

                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    const dropdown = this.closest('.dropdown-menu');
                    if (dropdown) dropdown.classList.add('hidden');
                });
            });
        });

        function saveAdminPermissions() {
            if (!currentAdminUserId) return;
            const checked = Array.from(document.querySelectorAll('.admin-modal-permission-checkbox:checked')).map(cb => cb.value);
            const form = document.getElementById('admin-permissions-form');
            form.action = ADMIN_PERMISSIONS_URL_TEMPLATE.replace('__ID__', currentAdminUserId);
            form.querySelectorAll('input[name="permissions[]"]').forEach(el => el.remove());
            checked.forEach(function(val) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'permissions[]';
                input.value = val;
                form.appendChild(input);
            });
            form.submit();
        }

        function closeAdminViewModal() {
            document.getElementById('viewAdminModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        /* ── View Employer Modal ────────────────────────────────── */
        function initViewEmployerButtons() {
            const modal = document.getElementById('viewEmployerModal');
            const viewButtons = document.querySelectorAll('.view-modal-btn');

            viewButtons.forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', function() {
                    const fields = {
                        'modalLastName': 'data-last-name',
                        'modalFirstName': 'data-first-name',
                        'modalMiddleName': 'data-middle-name',
                        'modalEmail': 'data-email',
                        'modalContact': 'data-contact',
                        'modalCompany': 'data-company',
                        'modalIndustry': 'data-industry',
                        'modalPosition': 'data-position',
                        'modalYear': 'data-year',
                        'modalSize': 'data-size',
                        'modalUrl': 'data-url'
                    };

                    for (const [id, attribute] of Object.entries(fields)) {
                        const element = document.getElementById(id);
                        if (element) element.textContent = this.getAttribute(attribute) || '--';
                    }

                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    const dropdown = this.closest('.dropdown-menu');
                    if (dropdown) dropdown.classList.add('hidden');
                });
            });
        }
        document.addEventListener('DOMContentLoaded', initViewEmployerButtons);

        /* ── Employer Deactivate Modal ──────────────────────────── */
        function openEmployerDeactivateModal(firstName, lastName, userId) {
            const modal = document.getElementById('deactivateEmployerModal');
            const content = document.getElementById('deactivateEmployerContent');

            document.getElementById('deactivateEmployerName').textContent = firstName + ' ' + lastName;
            document.getElementById('deactivateEmployerReason').value = '';
            document.getElementById('deactivateEmployerError').classList.add('hidden');

            // Clone submit button to remove any stale listeners
            const submitBtn = document.getElementById('deactivateEmployerSubmitBtn');
            const newBtn = submitBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newBtn, submitBtn);
            newBtn.id = 'deactivateEmployerSubmitBtn';

            newBtn.onclick = function() {
                const reason = document.getElementById('deactivateEmployerReason').value.trim();
                const error = document.getElementById('deactivateEmployerError');
                if (!reason) {
                    error.classList.remove('hidden');
                    lucide.createIcons();
                    return;
                }
                error.classList.add('hidden');
                document.getElementById('employerDeactivateReason_' + userId).value = reason;
                document.getElementById('employerDeactivateForm_' + userId).submit();
            };

            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeEmployerDeactivateModal() {
            const modal = document.getElementById('deactivateEmployerModal');
            const content = document.getElementById('deactivateEmployerContent');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }
    </script>

</body>

</html>