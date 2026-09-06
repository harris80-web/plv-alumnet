{{--
    Awaiting-approval employer table's rows + pagination. Extracted out of
    userManagement.blade.php so the exact same markup can be reused for both
    the initial full-page render and the AJAX pagination fragment response
    (UserController::employerPendingFragment) — swapping just this table's
    #employerPendingTableWrap innerHTML on a page-link click means the rest
    of the page (scroll position, other tabs, the toolbar) never moves.

    Kept deliberately compact (# + Company Name + Actions only) now that this
    table sits side-by-side with the Approved table — full details (contact
    person, email, industry, document, website) live in #viewPendingEmployerModal,
    opened via the eye icon below, where Approve/Decline also live so the
    table itself never has to grow wider than these three columns.

    Expects: $pendingEmployers (paginator of Employer, with user/industry loaded).
--}}
<div class="overflow-x-auto">
<table class="w-full text-left text-[10px] whitespace-nowrap">
    <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
        <tr>
            <th class="px-4 py-4 font-semibold border-r border-slate-700 w-10">#</th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Company Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th class="px-4 py-4 font-semibold text-center">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100" id="employerPendingTbody">
        @forelse ($pendingEmployers as $employer)
        <tr class="hover:bg-slate-50/80 transition-colors text-center" data-employer-id="{{ $employer->user_id }}"
            data-search="{{ mb_strtolower($employer->employer_company_name ?? '') }}">
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $loop->iteration }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->employer_company_name }}
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" class="view-pending-modal-btn p-2 hover:bg-slate-100 rounded-full transition-colors"
                    data-full-name="{{ $employer->user?->formalNameWithSuffix() ?? 'N/A' }}"
                    data-email="{{ $employer->user?->user_email }}"
                    data-company="{{ $employer->employer_company_name }}"
                    data-industry="{{ $employer->industry?->industry_name ?? 'N/A' }}"
                    data-website="{{ $employer->employer_website_url ?? 'N/A' }}"
                    data-document="{{ $employer->employer_company_document ? asset('storage/' . $employer->employer_company_document) : '' }}"
                    data-user-id="{{ $employer->user_id }}">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i>
                </button>
                {{-- Hidden forms openEmployerConfirm() submits by id — see userManagement.blade.php --}}
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
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="px-4 py-8 text-center text-slate-400 text-sm">No
                employers awaiting approval.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
<p id="employerPendingNoSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching employers.</p>
<div class="px-4 py-3">
    @include('partials.table-pagination-bar', [
        'id' => 'employerPendingTable',
        'mode' => 'ajax',
        'paginator' => $pendingEmployers,
        'perPageParam' => 'employer_pending_per_page',
        'fetchUrl' => route('users.employerPendingFragment'),
        'wrapId' => 'employerPendingTableWrap',
        'reinitFn' => 'reinitEmployerPendingTable',
    ])
</div>
