<?php $current_page = 'alumni_id_management'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni ID Management | PLV-AlumNet</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
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

        #alumniIdViewModal>div {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        #alumniIdViewModal>div::-webkit-scrollbar {
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
                        <div class="relative w-full md:w-72">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input type="text" id="searchInput" onkeyup="filterAlumniIdRows()"
                                placeholder="Search by name or reference no."
                                class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#C73D1A]/20 focus:border-[#C73D1A]">
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
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllAlumniIds(this)" class="w-4 h-4 cursor-pointer" title="Select all">
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

                                    $modalData = [
                                        'reference' => $referenceNo,
                                        'name' => $fullName,
                                        'program' => $alumniIdRecord->alumnus->program->program_name ?? 'N/A',
                                        'submitted' => $alumniIdRecord->created_at->format('M d, Y h:i A'),
                                        'lastUpdate' => optional($alumniIdRecord->status_updated_at)->format('M d, Y h:i A') ?? 'Never',
                                        'status' => $alumniIdRecord->status,
                                        'updateUrl' => route('alumniId.updateStatus', $alumniIdRecord->id),
                                    ];
                                @endphp
                                <tr class="border-b border-slate-100" data-search="{{ mb_strtolower($fullName . ' ' . $referenceNo) }}">
                                    <td class="border-r border-slate-100">
                                        <input type="checkbox" class="alumni-id-checkbox w-4 h-4 accent-[#1D264F] cursor-pointer" value="{{ $alumniIdRecord->id }}" onchange="updateAlumniIdBulkUI()">
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
        </main>
    </div>

    <!-- VIEW / UPDATE STATUS MODAL -->
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
                    <div class="col-span-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase">Last Status Update</p>
                        <p id="aim-lastUpdate" class="font-semibold"></p>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });

        // ── SEARCH ──
        function filterAlumniIdRows() {
            const query = document.getElementById('searchInput').value.trim().toLowerCase();
            const rows = document.querySelectorAll('#alumniIdsTbody tr[data-search]');
            let visibleCount = 0;
            rows.forEach(row => {
                const match = row.dataset.search.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            document.getElementById('noSearchResults').classList.toggle('hidden', visibleCount !== 0 || rows.length === 0);
        }

        // ── BULK SELECT ──
        function getCheckedAlumniIds() {
            return [...document.querySelectorAll('.alumni-id-checkbox:checked')].map(cb => cb.value);
        }

        function updateAlumniIdBulkUI() {
            const count = getCheckedAlumniIds().length;
            document.getElementById('selectedCount').textContent = count;
            document.querySelectorAll('.bulk-status-btn').forEach(btn => btn.disabled = count === 0);

            const rows = document.querySelectorAll('#alumniIdsTbody tr[data-search]');
            const visibleCheckboxes = [...rows].filter(r => r.style.display !== 'none').map(r => r.querySelector('.alumni-id-checkbox')).filter(Boolean);
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) {
                selectAll.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
            }
        }

        function toggleSelectAllAlumniIds(source) {
            document.querySelectorAll('#alumniIdsTbody tr[data-search]').forEach(row => {
                if (row.style.display === 'none') return; // respect the current search filter
                const cb = row.querySelector('.alumni-id-checkbox');
                if (cb) cb.checked = source.checked;
            });
            updateAlumniIdBulkUI();
        }

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

        // ── VIEW / UPDATE MODAL ──
        function openAlumniIdViewModal(id, data) {
            document.getElementById('aim-reference').textContent = data.reference;
            document.getElementById('aim-name').textContent = data.name;
            document.getElementById('aim-program').textContent = data.program;
            document.getElementById('aim-submitted').textContent = data.submitted;
            document.getElementById('aim-lastUpdate').textContent = data.lastUpdate;

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

        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('alumniIdViewModal')) closeAlumniIdViewModal();
        });
    </script>
</body>

</html>
