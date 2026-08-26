<?php $current_page = 'alumni_id_management'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni ID & Yearbook Management | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <!-- @vite('resources/css/app.css')
    @vite('resources/js/app.js') -->
    <script src="https://cdn.tailwindcss.com"></script>
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

        .ids-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .ids-table thead th {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: normal;
        }

        .ids-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            word-break: break-word;
        }

        .status-choice {
            border: 2px solid #e2e8f0;
            color: #64748b;
            transition: all 0.15s;
        }

        .status-choice.active {
            border-color: #C73D1A;
            background: #C73D1A;
            color: white;
        }

        .page-tab-btn {
            border-bottom: 3px solid transparent;
            color: #64748b;
        }

        .page-tab-btn.active {
            border-color: #C73D1A;
            color: #0E0F3B;
        }

        .modal-tab-btn {
            border-bottom: 2px solid transparent;
            color: #94a3b8;
        }

        .modal-tab-btn.active {
            border-color: #C73D1A;
            color: #0E0F3B;
        }

        #alumniIdViewModal>div,
        #yearbookModal>div {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        #alumniIdViewModal>div::-webkit-scrollbar,
        #yearbookModal>div::-webkit-scrollbar {
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

                <!-- PAGE TABS -->
                <div class="flex gap-6 border-b border-slate-200 mb-6">
                    <button type="button" onclick="switchPageTab('id')" id="pageTabBtn-id" class="page-tab-btn active px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors">
                        Alumni ID
                    </button>
                    <button type="button" onclick="switchPageTab('yearbook')" id="pageTabBtn-yearbook" class="page-tab-btn px-1 pb-3 text-sm font-bold uppercase tracking-wide transition-colors">
                        Alumni Yearbook
                    </button>
                </div>

                <!-- ══════════════════════ ALUMNI ID TAB ══════════════════════ -->
                <div id="pageTab-id">

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $statusCounts['total'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Alumni IDs</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-500">{{ $statusCounts['pending'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Pending</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-blue-500">{{ $statusCounts['under_review'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Under Review</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-purple-500">{{ $statusCounts['ready_to_claim'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Ready to Claim</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">{{ $statusCounts['claimed'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Claimed</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 w-full">

                        <!-- TOOLBAR: search + bulk actions -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <div class="relative w-full md:w-72">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" id="searchInput" onkeyup="filterAlumniIdRows()"
                                        placeholder="Search by name or reference no."
                                        class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                                </div>
                                <button type="button" onclick="toggleSidebar('alumniIdFilterSidebar')"
                                    class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:border-[#C73D1A] transition-all shrink-0">
                                    <i data-lucide="filter" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-semibold text-gray-500"><span id="selectedCount">0</span> selected</span>

                                <form id="bulkStatusForm" action="{{ route('alumniId.bulkUpdateStatus') }}" method="POST" class="hidden">
                                    @csrf
                                    <input type="hidden" name="status" id="bulkStatusInput">
                                </form>

                                @foreach (\App\Models\AlumniId::statusLabels() as $statusKey => $statusLabel)
                                <button type="button" disabled data-status="{{ $statusKey }}"
                                    onclick="submitBulkStatus('{{ $statusKey }}', '{{ $statusLabel }}')"
                                    class="bulk-status-btn {{ \App\Models\AlumniId::statusButtonClasses()[$statusKey] }} disabled:!bg-gray-300 disabled:cursor-not-allowed text-white text-[10px] font-bold px-3 py-2 rounded-md transition-colors uppercase">
                                    Set {{ $statusLabel }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="ids-table">
                                <thead class="bg-[#0E0F3B] text-white">
                                    <tr>
                                        <th class="border-r border-slate-700 w-10">
                                            <input type="checkbox" id="selectAllCheckbox" class="bulk-checkbox" title="Select all">
                                        </th>
                                        <th class="border-r border-slate-700">Reference No.</th>
                                        <th class="border-r border-slate-700">Student Full Name</th>
                                        <th class="border-r border-slate-700">Program</th>
                                        <th class="border-r border-slate-700">Date Submitted</th>
                                        <th class="border-r border-slate-700">ID Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="alumniIdsTbody">
                                    @forelse ($alumniIds as $alumniIdRecord)
                                    @php
                                        $alumnusUser = $alumniIdRecord->alumnus->user;
                                        $fullName = trim($alumnusUser->user_last_name . ', ' . $alumnusUser->user_first_name . ' ' . $alumnusUser->user_middle_name . ' ' . $alumnusUser->user_suffix);
                                        $referenceNo = 'ALID-' . str_pad($alumniIdRecord->id, 6, '0', STR_PAD_LEFT);
                                        $nextStatus = $alumniIdRecord->nextStatus();
                                        $updatedByName = $alumniIdRecord->updatedBy ? trim($alumniIdRecord->updatedBy->user_first_name . ' ' . $alumniIdRecord->updatedBy->user_last_name) : 'N/A';

                                        $modalData = [
                                            'reference' => $referenceNo,
                                            'name' => $fullName,
                                            'program' => $alumniIdRecord->alumnus->program->program_name ?? 'N/A',
                                            'submitted' => $alumniIdRecord->created_at->format('M d, Y h:i A'),
                                            'lastUpdate' => optional($alumniIdRecord->status_updated_at)->format('M d, Y h:i A') ?? 'Never',
                                            'updatedBy' => $updatedByName,
                                            'status' => $alumniIdRecord->status,
                                            'updateUrl' => route('alumniId.updateStatus', $alumniIdRecord->id),
                                        ];
                                    @endphp
                                    <tr class="border-b border-slate-100" data-search="{{ mb_strtolower($fullName . ' ' . $referenceNo) }}"
                                        data-reference="{{ mb_strtolower($referenceNo) }}" data-program="{{ $alumniIdRecord->alumnus->program->program_name ?? '' }}"
                                        data-submitted="{{ $alumniIdRecord->created_at->format('Y-m-d') }}" data-status="{{ $alumniIdRecord->status }}">
                                        <td class="border-r border-slate-100">
                                            <input type="checkbox" class="alumni-id-checkbox bulk-checkbox" value="{{ $alumniIdRecord->id }}">
                                        </td>
                                        <td class="border-r border-slate-100 font-semibold text-[#0E0F3B]">{{ $referenceNo }}</td>
                                        <td class="border-r border-slate-100">{{ $fullName }}</td>
                                        <td class="border-r border-slate-100">{{ $alumniIdRecord->alumnus->program->program_name ?? 'N/A' }}</td>
                                        <td class="border-r border-slate-100">{{ $alumniIdRecord->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="border-r border-slate-100">
                                            <span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase {{ $alumniIdRecord->badgeClass() }}">
                                                {{ $alumniIdRecord->statusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center gap-2">
                                                @if($nextStatus)
                                                <form action="{{ route('alumniId.mark', $alumniIdRecord->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-32 bg-green-600 hover:bg-green-700 text-white text-[10px] font-bold px-3 py-1.5 rounded-md transition-colors uppercase whitespace-nowrap">
                                                        Mark {{ \App\Models\AlumniId::markShortLabels()[$nextStatus] }}
                                                    </button>
                                                </form>
                                                @else
                                                <button type="button" disabled
                                                    class="w-32 bg-gray-200 text-gray-400 text-[10px] font-bold px-3 py-1.5 rounded-md uppercase whitespace-nowrap cursor-not-allowed">
                                                    Claimed
                                                </button>
                                                @endif
                                                <button type="button" onclick='openAlumniIdViewModal({{ $alumniIdRecord->id }}, @json($modalData))'
                                                    class="w-32 bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-[10px] font-bold px-3 py-1.5 rounded-md transition-colors uppercase whitespace-nowrap">
                                                    View
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="py-16 text-center text-gray-400">
                                            <i class="fas fa-id-card text-4xl mb-3 block"></i>
                                            No alumni ID records yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <p id="noSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching alumni IDs.</p>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════ ALUMNI YEARBOOK TAB ══════════════════════ -->
                <div id="pageTab-yearbook" class="hidden">

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-800">{{ $yearbookCounts['total'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Total Yearbooks</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-amber-500">{{ $yearbookCounts['pending'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Pending</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-cyan-500">{{ $yearbookCounts['on_hand'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">On Hand</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-purple-500">{{ $yearbookCounts['ready_to_claim'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Ready to Claim</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-green-500">{{ $yearbookCounts['claimed'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Claimed</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 shadow-sm px-5 py-4">
                            <p class="text-2xl font-bold text-slate-400">{{ $yearbookCounts['not_yet_claimed'] }}</p>
                            <p class="text-xs font-medium text-slate-500 mt-1">Not Yet Claimed</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 w-full">

                        <!-- TOOLBAR: search + bulk edit -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-slate-100">
                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <div class="relative w-full md:w-72">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" id="yearbookSearchInput" onkeyup="filterYearbookRows()"
                                        placeholder="Search by name or reference no."
                                        class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                                </div>
                                <button type="button" onclick="toggleSidebar('yearbookFilterSidebar')"
                                    class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:border-[#C73D1A] transition-all shrink-0">
                                    <i data-lucide="filter" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-semibold text-gray-500"><span id="yearbookSelectedCount">0</span> selected</span>
                                <button type="button" id="yearbookBulkEditBtn" disabled onclick="openYearbookBulkModal()"
                                    class="bg-[#C73D1A] hover:bg-orange-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-[10px] font-bold px-4 py-2 rounded-md transition-colors uppercase">
                                    Bulk Edit
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="ids-table">
                                <thead class="bg-[#0E0F3B] text-white">
                                    <tr>
                                        <th class="border-r border-slate-700 w-10">
                                            <input type="checkbox" id="yearbookSelectAllCheckbox" class="bulk-checkbox" title="Select all">
                                        </th>
                                        <th class="border-r border-slate-700">Reference No.</th>
                                        <th class="border-r border-slate-700">Full Name</th>
                                        <th class="border-r border-slate-700">Batch</th>
                                        <th class="border-r border-slate-700">Distribution Status</th>
                                        <th class="border-r border-slate-700">Distribution Schedule</th>
                                        <th class="border-r border-slate-700">Distribution Location</th>
                                        <th class="border-r border-slate-700">Claiming Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="yearbooksTbody">
                                    @forelse ($yearbooks as $yearbookRecord)
                                    @php
                                        $ybUser = $yearbookRecord->alumnus->user;
                                        $ybFullName = trim($ybUser->user_last_name . ', ' . $ybUser->user_first_name . ' ' . $ybUser->user_middle_name . ' ' . $ybUser->user_suffix);
                                        $ybReferenceNo = 'ALYB-' . str_pad($yearbookRecord->id, 6, '0', STR_PAD_LEFT);
                                        $ybUpdatedByName = $yearbookRecord->updatedBy ? trim($yearbookRecord->updatedBy->user_first_name . ' ' . $yearbookRecord->updatedBy->user_last_name) : 'N/A';

                                        $ybModalData = [
                                            'reference' => $ybReferenceNo,
                                            'name' => $ybFullName,
                                            'batch' => $yearbookRecord->alumnus->alumnus_batch,
                                            'lastUpdate' => optional($yearbookRecord->status_updated_at)->format('M d, Y h:i A') ?? 'Never',
                                            'updatedBy' => $ybUpdatedByName,
                                            'claimingStatus' => $yearbookRecord->claiming_status,
                                            'distributionStatus' => $yearbookRecord->distribution_status,
                                            'scheduledAt' => optional($yearbookRecord->distribution_scheduled_at)->format('Y-m-d\TH:i'),
                                            'building' => $yearbookRecord->distribution_building,
                                            'roomFloor' => $yearbookRecord->distribution_room_floor,
                                            'updateUrl' => route('alumniYearbook.update', $yearbookRecord->id),
                                        ];
                                    @endphp
                                    <tr class="border-b border-slate-100" data-search="{{ mb_strtolower($ybFullName . ' ' . $ybReferenceNo) }}"
                                        data-reference="{{ mb_strtolower($ybReferenceNo) }}" data-batch="{{ $yearbookRecord->alumnus->alumnus_batch }}"
                                        data-distribution="{{ $yearbookRecord->distribution_status }}" data-claiming="{{ $yearbookRecord->claiming_status }}">
                                        <td class="border-r border-slate-100">
                                            <input type="checkbox" class="yearbook-checkbox bulk-checkbox" value="{{ $yearbookRecord->id }}">
                                        </td>
                                        <td class="border-r border-slate-100 font-semibold text-[#0E0F3B]">{{ $ybReferenceNo }}</td>
                                        <td class="border-r border-slate-100">{{ $ybFullName }}</td>
                                        <td class="border-r border-slate-100">{{ $yearbookRecord->alumnus->alumnus_batch }}</td>
                                        <td class="border-r border-slate-100">
                                            <span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase {{ $yearbookRecord->distributionBadgeClass() }}">
                                                {{ $yearbookRecord->distributionStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="border-r border-slate-100">
                                            {{ optional($yearbookRecord->distribution_scheduled_at)->format('M d, Y h:i A') ?? 'Not yet scheduled' }}
                                        </td>
                                        <td class="border-r border-slate-100">{{ $yearbookRecord->locationLabel() }}</td>
                                        <td class="border-r border-slate-100">
                                            <span class="px-2 py-1 rounded-full text-[9px] font-bold uppercase {{ $yearbookRecord->claimingBadgeClass() }}">
                                                {{ $yearbookRecord->claimingStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" onclick='openYearbookModal({{ $yearbookRecord->id }}, @json($ybModalData))'
                                                class="w-24 bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-[10px] font-bold px-3 py-1.5 rounded-md transition-colors uppercase whitespace-nowrap">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="py-16 text-center text-gray-400">
                                            <i class="fas fa-book text-4xl mb-3 block"></i>
                                            No yearbook records yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <p id="yearbookNoSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching yearbook records.</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" onclick="closeOpenSidebar()"
        class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 hidden transition-opacity"></div>

    <!-- ══════════════════════ ALUMNI ID FILTER SIDEBAR ══════════════════════ -->
    <div id="alumniIdFilterSidebar"
        class="fixed top-0 right-0 h-full w-[320px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100">
            <h2 class="text-xl font-bold text-[#1e1b4b]">Filter by</h2>
            <button type="button" onclick="toggleSidebar('alumniIdFilterSidebar')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div>
                <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Reference No.</label>
                <input type="text" id="aidFilterReference" placeholder="Enter Reference No."
                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#C73D1A]">
            </div>
            <div>
                <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Program</label>
                <select id="aidFilterProgram"
                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm text-slate-600 focus:outline-none">
                    <option value="">Select Program</option>
                    @foreach ($programs as $program)
                    <option value="{{ $program->program_name }}">{{ $program->program_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Submission Date</label>
                <input type="date" id="aidFilterDate"
                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#C73D1A]">
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Status</label>
                <div class="space-y-2">
                    @foreach (\App\Models\AlumniId::statusLabels() as $statusKey => $statusLabel)
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium">
                        <input type="checkbox" class="aid-filter-status w-4 h-4 rounded border-slate-300" value="{{ $statusKey }}"> {{ $statusLabel }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-slate-100">
            <button type="button" onclick="filterAlumniIdRows(); toggleSidebar('alumniIdFilterSidebar')"
                class="w-full bg-[#0a0a23] text-white py-3 rounded text-sm font-bold tracking-widest hover:bg-black transition-colors uppercase">
                Apply Filter
            </button>
        </div>
    </div>

    <!-- ══════════════════════ YEARBOOK FILTER SIDEBAR ══════════════════════ -->
    <div id="yearbookFilterSidebar"
        class="fixed top-0 right-0 h-full w-[320px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        <div class="p-6 flex items-center justify-between border-b border-slate-100">
            <h2 class="text-xl font-bold text-[#1e1b4b]">Filter by</h2>
            <button type="button" onclick="toggleSidebar('yearbookFilterSidebar')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div>
                <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Reference No.</label>
                <input type="text" id="ybFilterReference" placeholder="Enter Reference No."
                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#C73D1A]">
            </div>
            <div>
                <label class="text-[13px] font-bold text-[#1e1b4b] block mb-2">Batch</label>
                <select id="ybFilterBatch"
                    class="w-full border border-slate-300 rounded-md px-3 py-2 text-sm text-slate-600 focus:outline-none">
                    <option value="">Select Batch</option>
                    @foreach ($batches as $batchYear)
                    <option value="{{ $batchYear }}">{{ $batchYear }}</option>
                    @endforeach
                </select>
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Distribution Status</label>
                <div class="space-y-2">
                    @foreach (\App\Models\AlumniYearbook::distributionStatusLabels() as $distKey => $distLabel)
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium">
                        <input type="checkbox" class="yb-filter-distribution w-4 h-4 rounded border-slate-300" value="{{ $distKey }}"> {{ $distLabel }}
                    </label>
                    @endforeach
                </div>
            </div>
            <hr class="border-slate-100">
            <div>
                <label class="text-[10px] font-bold text-[#C73D1A] uppercase tracking-wider block mb-3">Claim Status</label>
                <div class="space-y-2">
                    @foreach (\App\Models\AlumniYearbook::claimingStatusLabels() as $claimKey => $claimLabel)
                    <label class="flex items-center gap-3 text-sm text-[#1e1b4b] font-medium">
                        <input type="checkbox" class="yb-filter-claiming w-4 h-4 rounded border-slate-300" value="{{ $claimKey }}"> {{ $claimLabel }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-slate-100">
            <button type="button" onclick="filterYearbookRows(); toggleSidebar('yearbookFilterSidebar')"
                class="w-full bg-[#0a0a23] text-white py-3 rounded text-sm font-bold tracking-widest hover:bg-black transition-colors uppercase">
                Apply Filter
            </button>
        </div>
    </div>

    <!-- VIEW / UPDATE STATUS MODAL (Alumni ID) -->
    <div id="alumniIdViewModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                <h2 class="text-xl font-bold text-white">Alumni ID Details</h2>
                <button onclick="closeAlumniIdViewModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm text-[#0E0F3B]">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Reference No.</p>
                        <p id="aim-reference" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Student Full Name</p>
                        <p id="aim-name" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Program</p>
                        <p id="aim-program" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Date Submitted</p>
                        <p id="aim-submitted" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Last Status Update</p>
                        <p id="aim-lastUpdate" class="font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Updated By</p>
                        <p id="aim-updatedBy" class="font-semibold"></p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Current Status</p>
                    <span id="aim-current-status" class="inline-block px-3 py-1.5 rounded-full text-xs font-bold uppercase"></span>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-3">Update Status</p>
                    <div id="aim-status-choices" class="flex flex-wrap gap-2">
                        @foreach (\App\Models\AlumniId::statusLabels() as $statusKey => $statusLabel)
                        <button type="button" class="status-choice px-4 py-2 rounded-lg text-xs font-bold uppercase" data-status="{{ $statusKey }}" onclick="selectAlumniIdStatus('{{ $statusKey }}')">
                            {{ $statusLabel }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                <button onclick="closeAlumniIdViewModal()" class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
                    Cancel
                </button>
                <form id="alumniIdUpdateForm" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="aim-selected-status">
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-[#C73D1A] rounded-lg hover:bg-orange-700 transition-colors uppercase">
                        Save
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW / UPDATE MODAL (Alumni Yearbook) — shared by single-record View and Bulk Edit -->
    <div id="yearbookModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
            <div class="relative bg-[#0E0F3B] flex items-center justify-between p-6">
                <h2 id="ybm-title" class="text-xl font-bold text-white">Yearbook Details</h2>
                <button type="button" onclick="closeYearbookModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                </button>
            </div>

            <form id="yearbookUpdateForm" method="POST">
                @csrf
                <div class="p-6">

                    <!-- Header info — single-record mode only, hidden entirely for bulk edit -->
                    <div id="ybm-header-info" class="grid grid-cols-2 gap-4 text-sm text-[#0E0F3B] mb-5">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Reference No.</p>
                            <p id="ybm-reference" class="font-semibold"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Full Name</p>
                            <p id="ybm-name" class="font-semibold"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Batch</p>
                            <p id="ybm-batch" class="font-semibold"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Claiming Status</p>
                            <span id="ybm-current-claiming" class="inline-block px-2 py-1 rounded-full text-[9px] font-bold uppercase"></span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Last Status Update</p>
                            <p id="ybm-lastUpdate" class="font-semibold"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Updated By</p>
                            <p id="ybm-updatedBy" class="font-semibold"></p>
                        </div>
                    </div>
                    <p id="ybm-bulk-note" class="hidden text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 mb-5">
                        Editing <span id="ybm-bulk-count" class="font-bold"></span> selected record(s). Only the fields you change here will be applied — anything left as-is stays untouched on each record.
                    </p>

                    <!-- Tab switcher -->
                    <div class="flex gap-5 border-b border-slate-200 mb-4">
                        <button type="button" onclick="switchYearbookModalTab('claiming')" id="ybm-tab-btn-claiming" class="modal-tab-btn active pb-2 text-xs font-bold uppercase tracking-wide transition-colors">
                            Claiming Status
                        </button>
                        <button type="button" onclick="switchYearbookModalTab('distribution')" id="ybm-tab-btn-distribution" class="modal-tab-btn pb-2 text-xs font-bold uppercase tracking-wide transition-colors">
                            Distribution Info
                        </button>
                    </div>

                    <!-- Tab 1: Claiming Status -->
                    <div id="ybm-tab-claiming">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-3">Update Claiming Status</p>
                        <input type="hidden" name="claiming_status" id="ybm-selected-claiming-status">
                        <div id="ybm-claiming-choices" class="flex flex-wrap gap-2">
                            @foreach (\App\Models\AlumniYearbook::claimingStatusLabels() as $claimKey => $claimLabel)
                            <button type="button" class="status-choice px-3 py-2 rounded-lg text-xs font-bold uppercase" data-status="{{ $claimKey }}" onclick="selectYearbookClaimingStatus('{{ $claimKey }}')">
                                {{ $claimLabel }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tab 2: Distribution Info -->
                    <div id="ybm-tab-distribution" class="hidden space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Distribution Status</label>
                            <select name="distribution_status" id="ybm-distribution-status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                                <option value="">— No change —</option>
                                @foreach (\App\Models\AlumniYearbook::distributionStatusLabels() as $distKey => $distLabel)
                                <option value="{{ $distKey }}">{{ $distLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Distribution Schedule</label>
                            <input type="datetime-local" name="distribution_scheduled_at" id="ybm-scheduled-at"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Building Name</label>
                                <input type="text" name="distribution_building" id="ybm-building" placeholder="e.g. Main Building"
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Room & Floor</label>
                                <input type="text" name="distribution_room_floor" id="ybm-room-floor" placeholder="e.g. Room 201, 2nd Floor"
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeYearbookModal()" class="px-6 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition-colors uppercase">
                        Cancel
                    </button>
                    <button type="button" onclick="submitYearbookForm()" class="px-6 py-2 text-sm font-medium text-white bg-[#C73D1A] rounded-lg hover:bg-orange-700 transition-colors uppercase">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });

        // ── PAGE TABS ──
        function switchPageTab(tab) {
            ['id', 'yearbook'].forEach(t => {
                document.getElementById('pageTab-' + t).classList.toggle('hidden', t !== tab);
                document.getElementById('pageTabBtn-' + t).classList.toggle('active', t === tab);
            });
        }

        // ── SHARED FILTER SIDEBARS ──
        function toggleSidebar(id) {
            const sidebar = document.getElementById(id);
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.style.overflow = !sidebar.classList.contains('translate-x-full') ? 'hidden' : '';
        }

        function closeOpenSidebar() {
            ['alumniIdFilterSidebar', 'yearbookFilterSidebar'].forEach(id => {
                const sidebar = document.getElementById(id);
                if (!sidebar.classList.contains('translate-x-full')) toggleSidebar(id);
            });
        }

        // ── ALUMNI ID: SEARCH + SIDEBAR FILTERS ──
        function filterAlumniIdRows() {
            const query = document.getElementById('searchInput').value.trim().toLowerCase();
            const refQuery = document.getElementById('aidFilterReference').value.trim().toLowerCase();
            const program = document.getElementById('aidFilterProgram').value;
            const date = document.getElementById('aidFilterDate').value;
            const statuses = [...document.querySelectorAll('.aid-filter-status:checked')].map(cb => cb.value);

            const rows = document.querySelectorAll('#alumniIdsTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query) &&
                    (!refQuery || row.dataset.reference.includes(refQuery)) &&
                    (!program || row.dataset.program === program) &&
                    (!date || row.dataset.submitted === date) &&
                    (statuses.length === 0 || statuses.includes(row.dataset.status));
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            document.getElementById('noSearchResults').classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // ── ALUMNI ID: BULK SELECT ──
        function getCheckedAlumniIds() {
            return [...document.querySelectorAll('.alumni-id-checkbox:checked')].map(cb => cb.value);
        }

        initBulkCheckboxGroup({
            header: 'selectAllCheckbox',
            rowSelector: '.alumni-id-checkbox',
            onChange: function (checkedValues, checkedCount) {
                document.getElementById('selectedCount').textContent = checkedCount;
                document.querySelectorAll('.bulk-status-btn').forEach(btn => btn.disabled = checkedCount === 0);
            },
        });

        function submitBulkStatus(status, label) {
            const ids = getCheckedAlumniIds();
            if (ids.length === 0) return;
            if (!confirm('Set ' + ids.length + ' selected alumni ID(s) to "' + label + '"?')) return;

            const form = document.getElementById('bulkStatusForm');
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            document.getElementById('bulkStatusInput').value = status;
            form.submit();
        }

        // ── ALUMNI ID: VIEW / UPDATE MODAL ──
        function openAlumniIdViewModal(id, data) {
            document.getElementById('aim-reference').textContent = data.reference;
            document.getElementById('aim-name').textContent = data.name;
            document.getElementById('aim-program').textContent = data.program;
            document.getElementById('aim-submitted').textContent = data.submitted;
            document.getElementById('aim-lastUpdate').textContent = data.lastUpdate;
            document.getElementById('aim-updatedBy').textContent = data.updatedBy;

            const statusClasses = {
                'pending': 'bg-amber-100 text-amber-700',
                'under_review': 'bg-blue-100 text-blue-700',
                'ready_to_claim': 'bg-purple-100 text-purple-700',
                'claimed': 'bg-green-100 text-green-700',
            };
            const statusLabels = {
                'pending': 'Pending',
                'under_review': 'Under Review',
                'ready_to_claim': 'Ready to Claim',
                'claimed': 'Claimed',
            };

            const currentStatusEl = document.getElementById('aim-current-status');
            currentStatusEl.textContent = statusLabels[data.status] ?? data.status;
            currentStatusEl.className = 'inline-block px-3 py-1.5 rounded-full text-xs font-bold uppercase ' + (statusClasses[data.status] ?? 'bg-slate-100 text-slate-500');

            document.getElementById('alumniIdUpdateForm').action = data.updateUrl;
            selectAlumniIdStatus(data.status);

            document.getElementById('alumniIdViewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function selectAlumniIdStatus(status) {
            document.getElementById('aim-selected-status').value = status;
            document.querySelectorAll('#aim-status-choices .status-choice').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.status === status);
            });
        }

        function closeAlumniIdViewModal() {
            document.getElementById('alumniIdViewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // ── ALUMNI YEARBOOK: SEARCH + SIDEBAR FILTERS ──
        function filterYearbookRows() {
            const query = document.getElementById('yearbookSearchInput').value.trim().toLowerCase();
            const refQuery = document.getElementById('ybFilterReference').value.trim().toLowerCase();
            const batch = document.getElementById('ybFilterBatch').value;
            const distributions = [...document.querySelectorAll('.yb-filter-distribution:checked')].map(cb => cb.value);
            const claimings = [...document.querySelectorAll('.yb-filter-claiming:checked')].map(cb => cb.value);

            const rows = document.querySelectorAll('#yearbooksTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query) &&
                    (!refQuery || row.dataset.reference.includes(refQuery)) &&
                    (!batch || row.dataset.batch === batch) &&
                    (distributions.length === 0 || distributions.includes(row.dataset.distribution)) &&
                    (claimings.length === 0 || claimings.includes(row.dataset.claiming));
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            document.getElementById('yearbookNoSearchResults').classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // ── ALUMNI YEARBOOK: BULK SELECT ──
        function getCheckedYearbookIds() {
            return [...document.querySelectorAll('.yearbook-checkbox:checked')].map(cb => cb.value);
        }

        initBulkCheckboxGroup({
            header: 'yearbookSelectAllCheckbox',
            rowSelector: '.yearbook-checkbox',
            onChange: function (checkedValues, checkedCount) {
                document.getElementById('yearbookSelectedCount').textContent = checkedCount;
                document.getElementById('yearbookBulkEditBtn').disabled = checkedCount === 0;
            },
        });

        // ── ALUMNI YEARBOOK: MODAL (single view + bulk edit share this) ──
        function switchYearbookModalTab(tab) {
            ['claiming', 'distribution'].forEach(t => {
                document.getElementById('ybm-tab-' + t).classList.toggle('hidden', t !== tab);
                document.getElementById('ybm-tab-btn-' + t).classList.toggle('active', t === tab);
            });
        }

        function selectYearbookClaimingStatus(status) {
            document.getElementById('ybm-selected-claiming-status').value = status;
            document.querySelectorAll('#ybm-claiming-choices .status-choice').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.status === status);
            });
        }

        function resetYearbookForm() {
            selectYearbookClaimingStatus('');
            document.getElementById('ybm-distribution-status').value = '';
            document.getElementById('ybm-scheduled-at').value = '';
            document.getElementById('ybm-building').value = '';
            document.getElementById('ybm-room-floor').value = '';
            switchYearbookModalTab('claiming');
        }

        const claimingStatusLabels = {
            'pending': 'Pending', 'on_hand': 'On Hand', 'ready_to_claim': 'Ready to Claim',
            'claimed': 'Claimed', 'not_yet_claimed': 'Not Yet Claimed',
        };
        const claimingStatusClasses = {
            'pending': 'bg-amber-100 text-amber-700', 'on_hand': 'bg-cyan-100 text-cyan-700',
            'ready_to_claim': 'bg-purple-100 text-purple-700', 'claimed': 'bg-green-100 text-green-700',
            'not_yet_claimed': 'bg-slate-100 text-slate-500',
        };

        function openYearbookModal(id, data) {
            resetYearbookForm();

            document.getElementById('ybm-title').textContent = 'Yearbook Details';
            document.getElementById('ybm-header-info').classList.remove('hidden');
            document.getElementById('ybm-bulk-note').classList.add('hidden');

            document.getElementById('ybm-reference').textContent = data.reference;
            document.getElementById('ybm-name').textContent = data.name;
            document.getElementById('ybm-batch').textContent = data.batch;
            document.getElementById('ybm-lastUpdate').textContent = data.lastUpdate;
            document.getElementById('ybm-updatedBy').textContent = data.updatedBy;

            const badge = document.getElementById('ybm-current-claiming');
            badge.textContent = claimingStatusLabels[data.claimingStatus] ?? data.claimingStatus;
            badge.className = 'inline-block px-2 py-1 rounded-full text-[9px] font-bold uppercase ' + (claimingStatusClasses[data.claimingStatus] ?? 'bg-slate-100 text-slate-500');

            selectYearbookClaimingStatus(data.claimingStatus);
            document.getElementById('ybm-distribution-status').value = data.distributionStatus ?? '';
            document.getElementById('ybm-scheduled-at').value = data.scheduledAt ?? '';
            document.getElementById('ybm-building').value = data.building ?? '';
            document.getElementById('ybm-room-floor').value = data.roomFloor ?? '';

            const form = document.getElementById('yearbookUpdateForm');
            form.action = data.updateUrl;
            form.dataset.mode = 'single';

            document.getElementById('yearbookModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function openYearbookBulkModal() {
            const ids = getCheckedYearbookIds();
            if (ids.length === 0) return;

            resetYearbookForm();

            document.getElementById('ybm-title').textContent = 'Bulk Edit Yearbook Records';
            document.getElementById('ybm-header-info').classList.add('hidden');
            document.getElementById('ybm-bulk-note').classList.remove('hidden');
            document.getElementById('ybm-bulk-count').textContent = ids.length;

            const form = document.getElementById('yearbookUpdateForm');
            form.action = "{{ route('alumniYearbook.bulkUpdate') }}";
            form.dataset.mode = 'bulk';

            document.getElementById('yearbookModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }

        function submitYearbookForm() {
            const form = document.getElementById('yearbookUpdateForm');
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

            if (form.dataset.mode === 'bulk') {
                const ids = getCheckedYearbookIds();
                if (ids.length === 0) return;
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
            }

            form.submit();
        }

        function closeYearbookModal() {
            document.getElementById('yearbookModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('alumniIdViewModal')) closeAlumniIdViewModal();
            if (event.target === document.getElementById('yearbookModal')) closeYearbookModal();
        });
    </script>
</body>

</html>
