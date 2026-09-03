{{--
    Approved job-posts table's rows + pagination. Extracted out of
    jobManagement.blade.php so the exact same markup can be reused for both
    the initial full-page render and the AJAX pagination fragment response
    (JobPostingController::jobManagementApprovedFragment) — see
    partials/job-management/pending-table.blade.php for why.
    Expects: $approvedJobs (paginator of JobPosting, with user loaded).
--}}
<div class="overflow-x-auto">
<table class="jobs-table">
    <thead class="bg-[#0E0F3B] text-white">
        <tr>
            <th class="border-r border-slate-700">ID</th>
            <th data-sort class="border-r border-slate-700">Job Title <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Company Name <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Location <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Posted By <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Job Type <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Job Setup <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Recommended Program <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Status <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Posting Date <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th data-sort class="border-r border-slate-700">Closing Date <i data-lucide="chevron-down"
                    class="inline w-3 h-3 ml-0.5 sort-icon"></i></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100" id="jobs-tbody">
        @forelse ($approvedJobs as $j)
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
                            @php
                                $viewModalData = [
                                    'title' => $j->job_posting_title,
                                    'posted' => $j->created_at,
                                    'company' => $j->job_posting_company,
                                    'location' => $j->job_posting_address,
                                    'posted_by' => $j->user->user_first_name . ' ' . $j->user->user_last_name,
                                    'type' => $j->job_posting_employment_type,
                                    'setup' => $j->job_posting_setup,
                                    'program' => $j->programs->pluck('program_name')->join(', '),
                                    'industry' => $j->industry->industry_name ?? 'N/A',
                                    'closing' => $j->job_closing_date,
                                    'description' => $j->job_posting_description,
                                    'status' => 'Approved',
                                    'deleteUrl' => route('jobPosting.delete', $j->job_posting_id),
                                ];
                            @endphp
                            <button onclick='openViewModal({{ $j->job_posting_id }}, @json($viewModalData))'
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
            <td colspan="12" class="text-center py-8 text-slate-400 text-sm">No approved job posts.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
<div id="empty-state" class="hidden text-center py-12 text-slate-400 text-sm">No job posts found.
</div>
<div class="px-4 py-3">
    @include('partials.table-pagination-bar', [
        'id' => 'jobApprovedTable',
        'mode' => 'ajax',
        'paginator' => $approvedJobs,
        'perPageParam' => 'job_approved_per_page',
        'fetchUrl' => route('jobPosting.approvedFragment'),
        'wrapId' => 'jobApprovedTableWrap',
        'reinitFn' => 'applyFilters',
    ])
</div>
