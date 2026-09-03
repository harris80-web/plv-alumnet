{{--
    Approved/active employer table's rows + pagination. Extracted out of
    userManagement.blade.php so the exact same markup can be reused for both
    the initial full-page render and the AJAX pagination fragment response
    (UserController::employerApprovedFragment) — see
    partials/user-management/employer-pending-table.blade.php for why.
    Expects: $approvedEmployers (paginator of Employer, with user/industry loaded).
--}}
<div class="overflow-x-auto">
<table class="w-full text-left text-[10px] whitespace-nowrap">
    <thead class="bg-[#0E0F3B] text-white uppercase tracking-wider text-center">
        <tr>
            <th class="px-3 py-4 font-semibold border-r border-slate-700 w-10">
                <input type="checkbox" id="selectAllEmployers" class="bulk-checkbox">
            </th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Company Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Full Name <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Email <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th data-sort class="px-4 py-4 font-semibold border-r border-slate-700">Industry <i class="fas fa-chevron-down text-[9px] ml-0.5 sort-icon"></i></th>
            <th class="px-4 py-4 font-semibold border-r border-slate-700">Contact</th>
            <th class="px-4 py-4 font-semibold border-r border-slate-700">Official Website
                URL</th>
            <th class="px-4 py-4 font-semibold text-center">Actions</th>
        </tr>
    </thead>

    <tbody id="employerTbody" class="divide-y divide-slate-100">
        @forelse ($approvedEmployers as $employer)
        <tr class="hover:bg-slate-50/80 transition-colors text-center"
            data-search="{{ mb_strtolower($employer->employer_company_name ?? '') }}"
            data-lastname="{{ mb_strtolower($employer->user?->user_last_name ?? '') }}"
            data-firstname="{{ mb_strtolower($employer->user?->user_first_name ?? '') }}"
            data-middlename="{{ mb_strtolower($employer->user?->user_middle_name ?? '') }}"
            data-company="{{ mb_strtolower($employer->employer_company_name ?? '') }}"
            data-industry="{{ mb_strtolower($employer->industry->industry_name ?? '') }}">
            <td class="px-3 py-3 border-r border-slate-100">
                <input type="checkbox" class="employer-row-checkbox bulk-checkbox"
                    value="{{ $employer->user_id }}">
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->employer_company_name }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->user?->user_first_name }}
                {{ $employer->user?->user_middle_name }}
                {{ $employer->user?->user_last_name }}
                {{ $employer->user?->user_suffix ?? '' }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->user?->user_email }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->industry->industry_name ?? 'N/A' }}
            </td>
            <td class="px-4 py-3 font-medium text-black border-r border-slate-100">
                {{ $employer->employer_contact_number ?? 'N/A' }}
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
                        class="dropdown-menu absolute right-4 mt-2 w-48 origin-top-right rounded-md bg-white shadow-xl z-50 hidden">
                        <div class="py-1">
                            <button type="button"
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
                            <button
                                onclick="openEmployerDeactivateModal('{{ $employer->user->user_first_name }}', '{{ $employer->user->user_last_name }}', {{ $employer->user_id }})"
                                class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i data-lucide="user-minus" class="w-4 h-4 mr-3"></i>
                                Deactivate Account
                            </button>
                        </div>

                    </div>
                    <div style="display:none;">
                        <form id="employerDeactivateForm_{{ $employer->user_id }}"
                            action="{{ route('employers.deactivateEmployer', $employer->user_id) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="deactivate-reason"
                                id="employerDeactivateReason_{{ $employer->user_id }}">
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="px-4 py-8 text-center text-slate-400 text-sm">No approved
                employers yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
<p id="employerNoSearchResults" class="hidden text-center text-gray-400 py-10 text-xs">No matching employers.</p>
<div class="px-4 py-3">
    @include('partials.table-pagination-bar', [
        'id' => 'employerApprovedTable',
        'mode' => 'ajax',
        'paginator' => $approvedEmployers,
        'perPageParam' => 'employer_approved_per_page',
        'fetchUrl' => route('users.employerApprovedFragment'),
        'wrapId' => 'employerApprovedTableWrap',
        'reinitFn' => 'reinitEmployerApprovedTable',
    ])
</div>
