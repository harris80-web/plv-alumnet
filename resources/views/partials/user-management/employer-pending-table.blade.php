{{--
    Awaiting-approval employer table's rows + pagination. Extracted out of
    userManagement.blade.php so the exact same markup can be reused for both
    the initial full-page render and the AJAX pagination fragment response
    (UserController::employerPendingFragment) — swapping just this table's
    #employerPendingTableWrap innerHTML on a page-link click means the rest
    of the page (scroll position, other tabs, the toolbar) never moves.
    Expects: $pendingEmployers (paginator of Employer, with user/industry loaded).
--}}
<div class="overflow-x-auto">
<table class="w-full text-left text-[10px] whitespace-nowrap">
    <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
        <tr>
            <th class="px-4 py-4 font-semibold border-r border-slate-700 w-10">#</th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Company Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Full Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Email <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Industry <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th class="px-4 py-4 font-semibold border-r border-slate-700">Document</th>
            <th class="px-4 py-4 font-semibold border-r border-slate-700">Official Website
                URL</th>
            <th class="px-4 py-4 font-semibold text-center">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @forelse ($pendingEmployers as $employer)
        <tr class="hover:bg-slate-50/80 transition-colors text-center">
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $loop->iteration }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->employer_company_name }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->user?->user_first_name }}
                {{ $employer->user?->user_middle_name }}
                {{ $employer->user?->user_last_name }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->user?->user_email }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->industry?->industry_name ?? 'N/A' }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                <a href="{{ asset('storage/' . $employer->employer_company_document) }}" target="_blank" class="text-blue-500 hover:underline">
                    {{ asset('storage/' . $employer->employer_company_document) ? 'View Document' : 'Not Uploaded' }}
                </a>
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->employer_website_url ?? 'N/A' }}
            </td>
            <td class="px-4 py-3 text-center relative">
                <div class="inline-block text-left">
                    <button
                        class="menu-button p-2 hover:bg-slate-100 rounded-full transition-colors">
                        <i data-lucide="more-vertical" class="w-4 h-4 text-slate-500"></i>
                    </button>
                    <div
                        class="dropdown-menu absolute right-4 mt-2 w-48 origin-top-right rounded-md bg-white shadow-xl ring-1 ring-black ring-opacity-5 z-50 hidden">
                        <div class="py-1">
                            <button
                                onclick="openEmployerConfirm('approve', '{{ $employer->employer_company_name }}', {{ $employer->user_id }})"
                                class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-emerald-50 transition-colors whitespace-nowrap">
                                <i data-lucide="check-circle"
                                    class="w-4 h-4 mr-3 text-emerald-500"></i> Approve
                                Employer
                            </button>
                            <button
                                onclick="openEmployerConfirm('reject', '{{ $employer->employer_company_name }}', {{ $employer->user_id }})"
                                class="flex items-center w-full px-4 py-2 text-sm text-[#0E0F3B] hover:bg-orange-50 transition-colors whitespace-nowrap">
                                <i data-lucide="x-circle"
                                    class="w-4 h-4 mr-3 text-orange-500"></i> Reject
                                Employer
                            </button>
                        </div>
                    </div>
                    <div style="display:none;">
                        <form id="approveEmployerForm_{{ $employer->user_id }}"
                            action="{{ route('users.approveEmployer', $employer->user_id) }}"
                            method="POST">
                            @csrf
                        </form>

                        <form id="rejectEmployerForm_{{ $employer->user_id }}"
                            action="{{ route('users.rejectEmployer', $employer->user_id) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="reject-reason"
                                id="rejectEmployerReason_{{ $employer->user_id }}">
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">No
                employers awaiting approval.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
<div class="px-4 py-3">
    @include('partials.table-pagination-bar', [
        'id' => 'employerPendingTable',
        'mode' => 'ajax',
        'paginator' => $pendingEmployers,
        'perPageParam' => 'employer_pending_per_page',
        'fetchUrl' => route('users.employerPendingFragment'),
        'wrapId' => 'employerPendingTableWrap',
    ])
</div>
