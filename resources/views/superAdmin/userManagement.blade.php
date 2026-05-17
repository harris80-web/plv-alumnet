<!--DOCTYPE html>
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
        <h1>User Management</h1>
        <br><br><br>
        <div>
            <h2 class="flex justify-center font-bold">Employer management</h2>
            @foreach ($employers as $employer)
            

                <div class="flex gap-4 border-2 border-gray-300 rounded-lg p-4 mb-4">
                    <div>{{ $loop->iteration }}</div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Company name:</h2>
                        <p>{{ $employer->employer_company_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Website URL:</h2>
                        <p>{{ $employer->employer_website_url ?? 'N/A' }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>First name:</h2>
                        <p>{{ $employer->user->user_first_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Last name:</h2>
                        <p>{{ $employer->user->user_last_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Email:</h2>
                        <p>{{ $employer->user->user_email }}</p>
                    </div>
                    <form action="{{ route('users.approveEmployer', $employer->user_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Approve
                        </button>
                    </form>

                </div>
           
            @endforeach

        </div>
        <br><br><br>
        <div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <h2 class="font-bold">Alumni management</h2>
            <div>
                <form action="{{ route('users.addAlumnus') }}" method="POST">
                    @csrf
                    <div>
                        <label for="user_first_name">First Name:</label>
                        <input type="text" id="user_first_name" name="user_first_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_last_name">Last Name:</label>
                        <input type="text" id="user_last_name" name="user_last_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_middle_name">Middle Name:</label>
                        <input type="text" id="user_middle_name" name="user_middle_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_suffix">Suffix:</label>
                        <input type="text" id="user_suffix" name="user_suffix" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="program_id">Program:</label>
                        <select name="program_id" id="program_id" class="border-2 border-gray-300 rounded-lg p-1">
                            @foreach ($programs as $program)
                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="alumnus_batch">Batch:</label>
                        <input type="number" id="alumnus_batch" name="alumnus_batch" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="alumnus_section">Section:</label>
                        <select name="section_id" id="alumnus_section" class="border-2 border-gray-300 rounded-lg p-1">
                            @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Alumni
                    </button>
                </form>
            </div>
            <div class="flex flex-col">
                @foreach ($alumni as $alumnus)
                <div class="flex gap-4 border-2 border-gray-300 rounded-lg p-4 mb-4">
                    <div>{{ $loop->iteration }}</div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>First name:</h2>
                        <p>{{ $alumnus->user->user_first_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Last name:</h2>
                        <p>{{ $alumnus->user->user_last_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Middle name:</h2>
                        <p>{{ $alumnus->user->user_middle_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Suffix:</h2>
                        <p>{{ $alumnus->user->user_suffix }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Program:</h2>
                        <p>{{ $alumnus->program->program_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Batch:</h2>
                        <p>{{ $alumnus->alumnus_batch }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Section:</h2>
                        <p>{{ $alumnus->section->section_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Email:</h2>
                        <p>{{ $alumnus->user->user_email }}</p>
                    </div>

                </div>
                @endforeach
            </div>

        </div>
        <br><br><br>
        <div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <h2 class="font-bold">Admin management</h2>
            <div>
                <form action="{{ route('users.addAdmin') }}" method="POST">
                    @csrf
                    <div>
                        <label for="user_first_name">First Name:</label>
                        <input type="text" id="user_first_name" name="user_first_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_last_name">Last Name:</label>
                        <input type="text" id="user_last_name" name="user_last_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_middle_name">Middle Name:</label>
                        <input type="text" id="user_middle_name" name="user_middle_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_suffix">Suffix:</label>
                        <input type="text" id="user_suffix" name="user_suffix" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="office_address">Address:</label>
                        <input type="text" name="office_address" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_password">Password:</label>
                        <input type="password" id="user_password" name="user_password" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_password_confirmation">Confirm password:</label>
                        <input type="password" id="user_password_confirmation" name="user_password_confirmation" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Admin
                    </button>
                </form>
            </div>
            <div class="flex flex-col">
                @foreach ($admins as $admin)
                <div class="flex gap-4 border-2 border-gray-300 rounded-lg p-4 mb-4">
                    <div>{{ $loop->iteration }}</div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>First name:</h2>
                        <p>{{ $admin->user->user_first_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Last name:</h2>
                        <p>{{ $admin->user->user_last_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Middle name:</h2>
                        <p>{{ $admin->user->user_middle_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Suffix:</h2>
                        <p>{{ $admin->user->user_suffix }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Address:</h2>
                        <p>{{ $admin->office_address }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Account status:</h2>
                        <p>{{ $admin->user->user_active }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Date created:</h2>
                        <p>{{ $admin->user->created_at->format('Y-m-d') }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Email:</h2>
                        <p>{{ $admin->user->user_email }}</p>
                    </div>

                </div>
                @endforeach
            </div>

        </div>

</body>

</html>-->

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
            <!-- Tabs -->
            <div class="bg-white px-8 flex gap-8 border-b border-slate-200 shrink-0 shadow-md">
                <button id="tab-admin" class="tab-btn active py-3 px-2 flex items-center gap-2 text-sm transition-colors" onclick="switchTab('admin')">
                    <i data-lucide="user-cog" class="w-4 h-4"></i>
                    Admin
                </button>
                <button id="tab-alumni" class="tab-btn py-3 px-2 flex items-center gap-2 text-sm transition-colors" onclick="switchTab('alumni')">
                    <i data-lucide="book-user" class="w-4 h-4"></i>
                    Alumni
                </button>
                <button id="tab-employer" class="tab-btn py-3 px-2 flex items-center gap-2 text-sm transition-colors" onclick="switchTab('employer')">
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
                            <p class="text-2xl font-bold text-slate-800">{{ $admins->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Admin</p>
                        </div>

                        <!-- Active Admins -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">{{ $admins->filter(fn($a) => $a->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active Admin</p>
                        </div>

                        <!-- Inactive Admins -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-[#C73D1A]">{{ $admins->filter(fn($a) => !$a->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Inactive Admin</p>
                        </div>

                        <!-- Super Admin -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-400">1</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Super Admin</p>
                        </div>

                    </div>
                    <!-- ==================== END METRIC CARDS ==================== -->

                    <div class="flex justify-between items-center mb-6">
                        <div class="relative w-64">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text"
                                placeholder="Search"
                                class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-transparent">
                        </div>
                        <button onclick="document.getElementById('addAdminModal').classList.add('open')"
                            class="bg-[#1D46A4] hover:bg-[#0E0F3B] text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            ADD ADMIN
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                                <tr>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Last Name</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">First Name</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Middle Name</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Suffix</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Address</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Account Status</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Date Created</th>
                                    <th class="px-4 py-4 font-semibold border-r border-slate-700">Email</th>
                                    <th class="px-4 py-4 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-center">
                                @forelse ($admins as $admin)
                                @if ($admin->user->user_role == "admin")
                                <tr class="hover:bg-slate-50/80 transition-colors font-medium">
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->user->user_last_name }}</td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->user->user_first_name }}</td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->user->user_middle_name }}</td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->user->user_suffix }}</td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->office_address }}</td>
                                    <td class="px-4 py-4 text-center border-r border-slate-100">
                                        <span class="{{ $admin->user->user_active ? 'status-active' : 'status-inactive' }} font-semibold">
                                            {{ $admin->user->user_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->user->created_at->format('m/d/y') }}</td>
                                    <td class="px-4 py-4 text-black border-r border-slate-100">{{ $admin->user->user_email }}</td>
                                    <td class="px-4 py-4 text-center relative">
                                        <div class="inline-block text-left">
                                            <button class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div class="dropdown-menu absolute right-4 mt-2 w-48 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                                                <div class="py-1">
                                                    <button class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors">
                                                        <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i> View Profile
                                                    </button>
                                                    <button onclick="" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Delete Profile
                                                    </button>

                                                </div>

                                            </div>
                                            <div id="delete-message">
                                                <form action="{{ route('offices.deleteAdmin', $admin->user_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="text" name="delete-reason" placeholder="Enter reason...">
                                                    <input type="submit" value="Delete">
                                                </form>
                                            </div>
                                        </div>

                                    </td>

                                </tr>
                                @endif

                                @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-slate-400 text-sm">No admins found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END ADMIN TAB -->
                @endif


                <!-- ==================== ALUMNI TAB ==================== -->
                <div id="content-alumni" class="tab-content">
                    <!-- ==================== METRIC CARDS ==================== -->
                    <div class="grid grid-cols-4 gap-4 mb-6">

                        <!-- Total Alumni -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $alumni->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Alumni</p>
                        </div>

                        <!-- Active Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">{{ $alumni->filter(fn($a) => $a->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active Accounts</p>
                        </div>

                        <!-- Deactivated Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-[#C73D1A]">{{ $alumni->filter(fn($a) => !$a->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Deactivated Accounts</p>
                        </div>

                        <!-- New This Month -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-400"> {{ $alumni->filter(fn($a) => $a->user->created_at->month == now()->month && $a->user->created_at->year == now()->year)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">New This Month</p>
                        </div>

                    </div>
                    <!-- ==================== END METRIC CARDS ==================== -->

                    <div class="flex justify-between items-center mb-6">
                        <div class="flex gap-2">
                            <div class="relative w-64">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text"
                                    placeholder="Search"
                                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A]">
                            </div>
                            <button onclick="toggleAlumniFilterSidebar()" class="p-2 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 text-slate-500 transition-colors">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="flex gap-2">
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
                                    class="hidden absolute left-0 mt-2 w-48 origin-top-left rounded-md bg-white shadow-lg focus:outline-none z-50 ">
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
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden File Input for CSV -->
                            <input type="file" id="csvFileInput" accept=".csv" class="hidden" onchange="handleFileSelect(this)">

                            <!-- Export Button -->
                            <button id="exportBtn" onclick="exportAlumniToCSV()"
                                class="bg-[#C73D1A] hover:brightness-130 text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                EXPORT CSV
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                        <table class="w-full text-left text-[10px]">
                            <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                                <tr>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Alumni ID No. <i data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Last Name <i data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">First Name</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Middle Name</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Suffix</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Program</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Section</th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Batch <i data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Email <i data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                    <th class="px-3 py-4 font-semibold border-r border-slate-700">Status</th>
                                    <th class="px-3 py-4 font-semibold text-center">Action</th>
                                </tr>
                            </thead>
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($alumni as $alumnus)
                                @php
                                $status = $alumnus->user->user_active ? 'Active' : 'Deactivated';
                                $statusClass = $alumnus->user->user_active
                                ? 'bg-green-100 text-green-700 border-green-200'
                                : 'bg-red-100 text-red-700 border-red-200';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors text-center">
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->alumnus_id ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user->user_last_name }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user->user_first_name }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user->user_middle_name }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user->user_suffix ?? '' }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100 leading-tight">
                                        {{ $alumnus->program->program_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->section->section_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->alumnus_batch }}
                                    </td>
                                    <td class="px-3 py-3 font-medium text-black border-r border-slate-100">
                                        {{ $alumnus->user->user_email }}
                                    </td>
                                    <td class="px-3 py-3 border-r border-slate-100">
                                        <span class="px-2 py-1 rounded-full border text-[9px] font-bold {{ $statusClass }}">
                                            {{ strtoupper($status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center relative">
                                        <div class="inline-block text-left">
                                            <button class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                            </button>
                                            <div class="dropdown-menu absolute right-4 mt-2 w-56 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                                                <div class="py-1">
                                                    @if ($alumnus->user->user_active)
                                                    <button onclick="openConfirmModal('deactivate', '{{ $alumnus->user->user_last_name }}')"
                                                        class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                        <i data-lucide="user-minus" class="w-4 h-4 mr-3"></i> Deactivate Account
                                                    </button>
                                                    @else
                                                    <button onclick="openConfirmModal('activate', '{{ $alumnus->user->user_last_name }}')"
                                                        class="flex items-center w-full px-4 py-2.5 text-sm text-green-600 hover:bg-green-50 transition-colors">
                                                        <i data-lucide="user-plus" class="w-4 h-4 mr-3"></i> Activate Account
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-8 text-center text-slate-400 text-sm">No alumni found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END ALUMNI TAB -->

                <!-- ==================== EMPLOYER TAB ==================== -->
                <div id="content-employer" class="tab-content">
                    <!-- ==================== METRIC CARDS ==================== -->
                    <div class="grid grid-cols-4 gap-4 mb-6">

                        <!-- Total Employer -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $employers->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Employer</p>
                        </div>

                        <!-- Awaiting Approval -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-400">{{ $employers->filter(fn($e) => !$e->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Awaiting Approval</p>
                        </div>

                        <!-- Active Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">{{ $employers->filter(fn($e) => $e->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Active Employers</p>
                        </div>

                        <!-- Deactivated Accounts -->
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-[#C73D1A]">{{ $employers->filter(fn($e) => !$e->user->user_active)->count() }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Deactivated Accounts</p>
                        </div>
                    </div>
                    <!-- ==================== END METRIC CARDS ==================== -->

                    <!-- Awaiting Approval -->
                    <div class="mb-6">
                        <p class="text-sm font-semibold text-orange-600 mb-3">Employers Awaiting Approval:</p>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                            <table class="w-full text-left text-[10px]">
                                <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                                    <tr>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Last Name <i data-lucide="chevron-down" class="inline w-3 h-3 ml-1 opacity-50"></i></th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">First Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Middle Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Email</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Business Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Official Website URL</th>
                                        <th class="px-4 py-4 font-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($employers->filter(fn($e) => !$e->user->user_active) as $employer)
                                    <tr class="hover:bg-slate-50/80 transition-colors text-center">
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_last_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_first_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_middle_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_email }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->employer_company_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->employer_website_url ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-center relative">
                                            <div class="inline-block text-left">
                                                <button class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                    <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                                </button>
                                                <div class="dropdown-menu absolute right-4 mt-2 w-48 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                                                    <div class="py-1">
                                                        <form action="{{ route('users.approveEmployer', $employer->user_id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-emerald-50 transition-colors whitespace-nowrap">
                                                                <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-emerald-500"></i> Approve Employer
                                                            </button>
                                                        </form>
                                                        <button onclick="openEmployerConfirm('reject', '{{ $employer->employer_company_name }}')" class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-orange-50 transition-colors whitespace-nowrap">
                                                            <i data-lucide="x-circle" class="w-4 h-4 mr-3 text-orange-500"></i> Reject Employer
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No employers awaiting approval.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Approved Employers -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex gap-2">
                                <div class="relative w-64">
                                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text"
                                        placeholder="Search by Company Name"
                                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-full text-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#C73D1A] focus:border-[#C73D1A]">
                                </div>
                                <button onclick="toggleEmployerFilter()" class="p-2 border border-slate-200 bg-white rounded-lg hover:bg-slate-50 text-slate-500 transition-colors">
                                    <i data-lucide="filter" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <button onclick="exportEmployersToCSV()" class="bg-[#C73D1A] hover:brightness-130 text-white px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                EXPORT CSV
                            </button>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                            <table class="w-full text-left text-[10px]">
                                <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
                                    <tr>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Last Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">First Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Middle Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Suffix</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Email</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Contact No.</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Company Name</th>
                                        <th class="px-4 py-4 font-semibold border-r border-slate-700">Industry / Sector</th>
                                        <th class="px-4 py-4 font-semibold text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($employers->filter(fn($e) => $e->user->user_active) as $employer)
                                    <tr class="hover:bg-slate-50/80 transition-colors text-center">
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_last_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_first_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_middle_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_suffix ?? '' }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->user->user_email }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->employer_contact_number ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->employer_company_name }}</td>
                                        <td class="px-4 py-3 font-medium text-black border-r border-slate-100">{{ $employer->industry->industry_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-center relative">
                                            <div class="inline-block text-left">
                                                <button class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                                                    <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                                                </button>
                                                <div class="dropdown-menu absolute right-4 mt-2 w-48 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                                                    <div class="py-1">
                                                        <button
                                                            type="button"
                                                            class="view-modal-btn flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-blue-50 transition-colors"
                                                            data-last-name="{{ $employer->user->user_last_name }}"
                                                            data-first-name="{{ $employer->user->user_first_name }}"
                                                            data-middle-name="{{ $employer->user->user_middle_name }}"
                                                            data-suffix="{{ $employer->user->user_suffix ?? '' }}"
                                                            data-email="{{ $employer->user->user_email }}"
                                                            data-contact="{{ $employer->employer_contact_number ?? 'N/A' }}"
                                                            data-company="{{ $employer->employer_company_name }}"
                                                            data-industry="{{ $employer->industry->industry_name ?? 'N/A' }}"
                                                            data-position="{{ $employer->employer_position ?? 'N/A' }}"
                                                            data-year="{{ $employer->employer_year_established ?? 'N/A' }}"
                                                            data-size="{{ $employer->employer_company_size ?? 'N/A' }}"
                                                            data-url="{{ $employer->employer_website_url ?? 'N/A' }}">
                                                            <i data-lucide="eye" class="w-4 h-4 mr-3 text-blue-500"></i>
                                                            View Employer
                                                        </button>
                                                        <hr class="my-1 border-slate-100">
                                                        <button class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                            <i data-lucide="user-minus" class="w-4 h-4 mr-3"></i>
                                                            Deactivate Account
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-slate-400 text-sm">No approved employers yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
                    <button type="button" onclick="closeModal('addAdminModal')" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="px-8 py-6 space-y-3">
                    <div class="pb-1 border-b border-slate-100">
                        <p class="text-[10px] font-bold text-[#C73D1A] uppercase">Personal Information</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_first_name" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">First Name:</label>
                        <input type="text" name="user_first_name" placeholder="Enter First Name here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_middle_name" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Middle Name:</label>
                        <input type="text" name="user_middle_name" placeholder="Enter Middle Name here" class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_last_name" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Last Name:</label>
                        <input type="text" name="user_last_name" placeholder="Enter Last Name here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_suffix" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Suffix:</label>
                        <input type="text" name="user_suffix" placeholder="e.g. Jr., III" class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="office_address" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Address:</label>
                        <input type="text" name="office_address" placeholder="Enter Address here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>

                    <div class="pt-2 pb-1 border-b border-slate-100">
                        <p class="text-[10px] font-bold text-[#C73D1A] uppercase">Account Details</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_email" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Email:</label>
                        <input type="email" name="user_email" placeholder="Enter Email here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_password" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Password:</label>
                        <div class="flex-1 relative group">
                            <input type="password" name="user_password" id="adminPass" placeholder="••••••••" required class="w-full px-3 py-1.5 border border-[#0E0F3B] group-hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                            <button type="button" onclick="togglePassword('adminPass', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#C73D1A]">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="user_password_confirmation" class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Confirm Password:</label>
                        <div class="flex-1 relative group">
                            <input type="password" name="user_password_confirmation" id="adminConfirmPass" placeholder="••••••••" required class="w-full px-3 py-1.5 border border-[#0E0F3B] group-hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 transition-colors">
                            <button type="button" onclick="togglePassword('adminConfirmPass', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#C73D1A]">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-8 pb-7 flex justify-end gap-4">
                    <button type="button" onclick="closeModal('addAdminModal')" class="px-8 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] rounded-lg text-sm font-bold hover:bg-[#0E0F3B] hover:text-white transition-all uppercase">
                        Cancel
                    </button>
                    <button type="submit" class="px-8 py-2 bg-[#0E0F3B] hover:bg-blue-900 text-white rounded-lg text-sm font-bold transition-colors uppercase">
                        Add Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== ADD ALUMNI MODAL ==================== -->
    <div id="addAlumniModal" class="modal-overlay" onclick="if(event.target===this) closeModal('addAlumniModal')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden">
            <form action="{{ route('users.addAlumnus') }}" method="POST">
                @csrf
                <div class="bg-[#0E0F3B] px-8 py-5 flex justify-between items-center">
                    <h2 class="text-white text-lg font-bold">Alumni Management</h2>
                    <button type="button" onclick="closeModal('addAlumniModal')" class="w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="px-8 py-6 space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">First Name:</label>
                        <input type="text" name="user_first_name" placeholder="Enter First Name here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Middle Name:</label>
                        <input type="text" name="user_middle_name" placeholder="Enter Middle Name here" class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Last Name:</label>
                        <input type="text" name="user_last_name" placeholder="Enter Last Name here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Suffix:</label>
                        <input type="text" name="user_suffix" placeholder="e.g. Jr., III" class="w-44 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Program:</label>
                        <select name="program_id" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white transition-all truncate w-full">
                            <option value="" disabled selected>Select Undergraduate Program</option>
                            @foreach ($programs as $program)
                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Batch:</label>
                        <input type="number" name="alumnus_batch" placeholder="Enter Batch here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Section:</label>
                        <select name="section_id" required class="w-44 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white transition-all">
                            <option value="" disabled selected>Select Section here</option>
                            @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-semibold text-[#0E0F3B] w-32 shrink-0">Email:</label>
                        <input type="email" name="user_email" placeholder="Enter Email here" required class="flex-1 px-3 py-1.5 border border-[#0E0F3B] hover:border-[#C73D1A] rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                    </div>
                </div>

                <div class="px-8 pb-7 flex justify-end gap-4">
                    <button type="button" onclick="closeModal('addAlumniModal')" class="px-8 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] rounded-lg text-sm font-bold hover:bg-[#0E0F3B] hover:text-white transition-all uppercase">
                        CANCEL
                    </button>
                    <button type="submit" class="px-8 py-2 bg-[#0E0F3B] hover:bg-blue-900 text-white rounded-lg text-sm font-bold transition-all uppercase">
                        ADD ALUMNI
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ACTIVATE/DEACTIVATE ALUMNI ACCOUNT MODAL CONFIRMATION -->
    <div id="confirmModal" class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-[#0E0F3B]/40 backdrop-blur-sm" onclick="closeConfirmModal()"></div>

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300" id="confirmContent">
            <div class="p-8 text-center">
                <div id="confirmIconContainer" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i id="confirmIcon" data-lucide="alert-triangle" class="w-8 h-8"></i>
                </div>
                <h3 id="confirmTitle" class="text-[#0E0F3B] text-xl font-bold mb-2">Confirmation</h3>
                <p id="confirmMessage" class="text-slate-500 text-sm leading-relaxed">
                    Are you sure you want to proceed with this action?
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="confirmYesBtn" class="flex-1 py-2.5 text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== ALUMNI FILTER SIDEBAR ==================== -->
    <div id="filterSidebar" class="fixed inset-0 z-50 invisible transition-all duration-300">
        <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" onclick="toggleAlumniFilterSidebar()"></div>

        <div id="sidebarPanel" class="absolute right-0 top-0 h-full w-80 bg-white shadow-2xl translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
            <div class="px-6 py-5 flex justify-between items-center border-b border-slate-100">
                <h2 class="text-[#0E0F3B] text-xl font-bold">Filter by</h2>
                <button onclick="toggleAlumniFilterSidebar()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Alumni ID No.</label>
                    <input type="text" placeholder="Enter Alumni ID No." class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Last Name</label>
                    <input type="text" placeholder="Enter Last Name" class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">First Name</label>
                    <input type="text" placeholder="Enter First Name" class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Middle Name</label>
                    <input type="text" placeholder="Enter Middle Name" class="w-full px-3 py-2 border border-slate-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] transition-all">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Program</label>
                    <select class="w-full px-3 py-2 border border-slate-200 rounded text-sm text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white">
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
                <div class="space-y-1.5 border-b border-slate-100 pb-5">
                    <label class="text-xs font-bold text-[#0E0F3B]">Batch</label>
                    <select class="w-full px-3 py-2 border border-slate-200 rounded text-sm text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/30 focus:border-[#C73D1A] bg-white">
                        <option selected disabled>Select Batch</option>
                        <option>2024</option>
                        <option>2023</option>
                    </select>
                </div>
                <div class="space-y-3 pt-2">
                    <p class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider">Account Status</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#0E0F3B] focus:ring-[#0E0F3B]">
                            <span class="text-sm font-bold text-[#0E0F3B] group-hover:text-slate-600">Active</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group border-b border-slate-100 pb-4">
                            <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#0E0F3B] focus:ring-[#0E0F3B]">
                            <span class="text-sm font-bold text-[#0E0F3B] group-hover:text-slate-600">Deactivated</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <button class="w-full py-3 bg-[#0E0F3B] hover:brightness-110 text-white rounded font-bold text-xs tracking-widest uppercase transition-all">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== EMPLOYER FILTER SIDEBAR ==================== -->
    <div id="employerFilterSidebar" class="fixed inset-y-0 right-0 w-80 bg-white shadow-2xl z-[100] transform translate-x-full transition-transform duration-300 ease-in-out border-l border-slate-200">
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
                    <input type="text" placeholder="Enter Last Name" class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">First Name</label>
                    <input type="text" placeholder="Enter First Name" class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Middle Name</label>
                    <input type="text" placeholder="Enter Middle Name" class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Company Name</label>
                    <input type="text" placeholder="Enter Company Name" class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#0E0F3B] mb-1">Industry/Sector</label>
                    <input type="text" placeholder="Enter Industry/Sector" class="w-full px-3 py-2 bg-white border border-slate-200 rounded text-sm focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A] outline-none transition-all placeholder:text-slate-400 text-[#0E0F3B]">
                </div>
            </div>
            <div class="p-6 bg-white border-t border-slate-100">
                <button class="w-full py-3 bg-[#0E0F3B] text-white rounded font-bold text-xs uppercase tracking-widest hover:bg-[#1a1b4d] transition-all">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>

    <div id="employerFilterBackdrop" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[90] hidden transition-opacity duration-300 opacity-0" onclick="toggleEmployerFilter()"></div>

    <!-- ==================== APPROVE/REJECT EMPLOYER MODAL ==================== -->
    <div id="employerConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center invisible transition-all duration-300">
        <div class="absolute inset-0 bg-[#0E0F3B]/40 backdrop-blur-sm" onclick="closeEmployerConfirm()"></div>

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden relative z-10 transform scale-95 transition-transform duration-300" id="employerConfirmContent">
            <div class="p-8 text-center">
                <div id="empConfirmIconBox" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i id="empConfirmIcon" data-lucide="help-circle" class="w-8 h-8"></i>
                </div>
                <h3 id="empConfirmTitle" class="text-[#0E0F3B] text-xl font-bold mb-2 tracking-tight">Confirmation</h3>
                <p id="empConfirmMessage" class="text-slate-500 text-sm leading-relaxed px-2">
                    Are you sure you want to proceed?
                </p>
            </div>
            <div class="px-8 pb-8 flex gap-3">
                <button onclick="closeEmployerConfirm()" class="flex-1 py-2.5 border-2 border-slate-200 text-slate-500 rounded-lg text-xs font-bold hover:bg-slate-50 transition-all uppercase">
                    Cancel
                </button>
                <button id="empConfirmYesBtn" class="flex-1 py-2.5 text-white rounded-lg text-xs font-bold transition-all uppercase hover:brightness-110">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== VIEW EMPLOYER MODAL ==================== -->
    <div id="viewEmployerModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50 px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden">
            <div class="bg-[#0E0F3B] p-4 flex justify-between items-center">
                <h2 class="text-white text-xl font-bold">View Employer</h2>
                <button onclick="closeModal()" class="text-gray-300 hover:text-white">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <section>
                    <h3 class="text-[#D95D39] font-bold text-sm uppercase mb-4">Employer Details</h3>
                    <div class="flex gap-6">
                        <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center border border-gray-200 shrink-0">
                            <i data-lucide="user-round" class="w-12 h-12 text-slate-400"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-4 flex-grow">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Last Name</p>
                                <p id="modalLastName" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">First Name</p>
                                <p id="modalFirstName" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Middle Name</p>
                                <p id="modalMiddleName" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Employer Position</p>
                                <p id="modalPosition" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Email</p>
                                <p id="modalEmail" class="text-[#0E0F3B] font-medium break-all">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Contact No.</p>
                                <p id="modalContact" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                        </div>
                    </div>
                </section>
                <section>
                    <h3 class="text-[#D95D39] font-bold text-sm uppercase mb-4">Company Details</h3>
                    <div class="flex gap-6">
                        <div class="w-24 h-24 bg-blue-50 rounded-xl border border-gray-200 flex flex-col items-center justify-center shrink-0">
                            <i data-lucide="image" class="w-8 h-8 text-green-500 mb-1"></i>
                            <span class="text-[8px] text-gray-400 font-bold">BUSINESS LOGO</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 flex-grow">
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Company Name</p>
                                <p id="modalCompany" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Year Established</p>
                                <p id="modalYear" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Company Size</p>
                                <p id="modalSize" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Industry/Sector</p>
                                <p id="modalIndustry" class="text-[#0E0F3B] font-medium">--</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Official Website URL</p>
                                <p id="modalUrl" class="text-blue-600 font-medium">--</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="p-4 bg-gray-50 flex justify-end gap-3">
                <button onclick="closeModal()" class="px-8 py-2 bg-[#0E0F3B] text-white rounded font-bold uppercase text-sm">Done</button>
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
        }

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

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                console.log("File selected:", input.files[0].name);
                // Add your CSV processing logic here
            }
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

        //Alumni EXPORT CSV FUNCTION
        function exportAlumniToCSV() {
            // 1. Define your data (Usually this comes from your database or an array)
            const data = [
                ["Name", "Course", "Batch", "Email"], // Headers
                ["Juan Dela Cruz", "BSIT", "2024", "juan@example.com"],
                ["Maria Clara", "BSCS", "2023", "maria@example.com"]
            ];

            // 2. Convert array to CSV string
            let csvContent = "data:text/csv;charset=utf-8," +
                data.map(row => row.join(",")).join("\n");

            // 3. Create a temporary hidden link to trigger the download
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "alumni_list.csv");
            document.body.appendChild(link); // Required for Firefox

            // 4. Trigger click and cleanup
            link.click();
            document.body.removeChild(link);
        }

        // EMPLOYERS CSV EXPORT JS LOGIC 
        function exportEmployersToCSV() {
            const filename = 'approved_employers_' + new Date().toISOString().slice(0, 10) + '.csv';
            const csv = [];

            // Find the table within the employer section
            // If you use tabs, ensure the selector finds the visible table
            const table = document.querySelector('#content-employer table') || document.querySelector('table');
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

        /* ── Alumni Confirm Modal ───────────────────────────────── */
        function openConfirmModal(action, lastName) {
            const modal = document.getElementById('confirmModal');
            const content = document.getElementById('confirmContent');
            const iconContainer = document.getElementById('confirmIconContainer');
            const icon = document.getElementById('confirmIcon');
            const title = document.getElementById('confirmTitle');
            const message = document.getElementById('confirmMessage');
            const yesBtn = document.getElementById('confirmYesBtn');

            if (action === 'deactivate') {
                title.innerText = "Deactivate Account";
                message.innerHTML = `Are you sure you want to <span class="font-bold text-red-600">deactivate</span> the account of <b>${lastName}</b>?`;
                iconContainer.className = "w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4";
                icon.setAttribute('data-lucide', 'user-minus');
                yesBtn.className = "flex-1 py-2.5 bg-red-600 text-white rounded-lg text-xs font-bold transition-all uppercase hover:bg-red-700";
                yesBtn.innerText = "Yes, Deactivate";
            } else {
                title.innerText = "Activate Account";
                message.innerHTML = `Are you sure you want to <span class="font-bold text-green-600">activate</span> the account of <b>${lastName}</b>?`;
                iconContainer.className = "w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4";
                icon.setAttribute('data-lucide', 'user-plus');
                yesBtn.className = "flex-1 py-2.5 bg-green-600 text-white rounded-lg text-xs font-bold transition-all uppercase hover:bg-green-700";
                yesBtn.innerText = "Yes, Activate";
            }

            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmModal');
            const content = document.getElementById('confirmContent');
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
        function openEmployerConfirm(action, companyName) {
            const modal = document.getElementById('employerConfirmModal');
            const content = document.getElementById('employerConfirmContent');
            const iconBox = document.getElementById('empConfirmIconBox');
            const icon = document.getElementById('empConfirmIcon');
            const title = document.getElementById('empConfirmTitle');
            const message = document.getElementById('empConfirmMessage');
            const yesBtn = document.getElementById('empConfirmYesBtn');

            if (action === 'approve') {
                title.innerText = "Approve Employer";
                message.innerHTML = `Are you sure you want to <span class="font-bold text-emerald-600">approve</span> <b>${companyName}</b>?`;
                iconBox.className = "w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4 text-emerald-600";
                icon.setAttribute('data-lucide', 'check-circle');
                yesBtn.className = "flex-1 py-2.5 bg-emerald-600 text-white rounded-lg text-xs font-bold transition-all uppercase hover:bg-emerald-700";
                yesBtn.innerText = "Yes, Approve";
            } else {
                title.innerText = "Reject Employer";
                message.innerHTML = `Are you sure you want to <span class="font-bold text-orange-600">reject</span> <b>${companyName}</b>?`;
                iconBox.className = "w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-4 text-orange-600";
                icon.setAttribute('data-lucide', 'x-circle');
                yesBtn.className = "flex-1 py-2.5 bg-orange-600 text-white rounded-lg text-xs font-bold transition-all uppercase hover:bg-orange-700";
                yesBtn.innerText = "Yes, Reject";
            }

            lucide.createIcons();
            modal.classList.remove('invisible');
            setTimeout(() => content.classList.remove('scale-95'), 10);
        }

        function closeEmployerConfirm() {
            const modal = document.getElementById('employerConfirmModal');
            const content = document.getElementById('employerConfirmContent');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('invisible'), 200);
        }

        /* ── View Employer Modal ────────────────────────────────── */
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('viewEmployerModal');
            const viewButtons = document.querySelectorAll('.view-modal-btn');

            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
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
        });
    </script>

</body>

</html>