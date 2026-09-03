{{--
    Pending job-posts table's rows + pagination. Extracted out of
    jobManagement.blade.php so the exact same markup can be reused for both
    the initial full-page render and the AJAX pagination fragment response
    (JobPostingController::jobManagementPendingFragment) — swapping just
    this table's #jobPendingTableWrap innerHTML on a page-link click means
    the rest of the page (scroll position, the approved table, filters)
    never moves. Expects: $pendingJobs (paginator of JobPosting, with user loaded).
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
    <tbody class="divide-y divide-slate-100">
        @forelse ($pendingJobs as $j)

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
                        'status' => 'Pending',
                        'approveUrl' => route('jobPosting.approve', $j->job_posting_id),
                    ];
                @endphp
                {{-- Approve/Decline now live inside the View modal itself
                     (see openViewModal()'s #approveBtn/#declineBtn), so this
                     is just a direct view trigger — no dropdown needed for a
                     single action. --}}
                <button onclick='openViewModal({{ $j->job_posting_id }}, @json($viewModalData))'
                    title="View"
                    class="p-1.5 hover:bg-blue-50 rounded-full transition-colors">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="text-center py-8 text-slate-400 text-sm">No pending job posts.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
<div class="px-4 py-3">
    @include('partials.table-pagination-bar', [
        'id' => 'jobPendingTable',
        'mode' => 'ajax',
        'paginator' => $pendingJobs,
        'perPageParam' => 'job_pending_per_page',
        'fetchUrl' => route('jobPosting.pendingFragment'),
        'wrapId' => 'jobPendingTableWrap',
    ])
</div>
