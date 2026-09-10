{{--
    Job list for the My Job Postings tab (Job Posting Management) — the
    acting admin's OWN job posts, any status (see
    JobPostingController::applicantJobsBaseQuery()), each with its live
    applicant/hired count. "View" opens the same job-details modal the Job
    Posts tab uses; "View Applicants" only actually opens for an approved
    post (a pending one can't have applicants yet — see
    JobApplicationController::applyJob()). Same swap-just-this-wrapper AJAX
    pagination pattern as jobs-table.blade.php (see
    JobPostingController::jobApplicantsPickerFragment()).

    Expects: $applicantJobs (paginator of JobPosting, own + applications_count + programs/industry/user loaded).
--}}
<div class="overflow-x-auto table-scroll">
<table class="jobs-table">
    <thead class="bg-[#0E0F3B] text-white">
        <tr>
            <th class="border-r border-slate-700">ID</th>
            <th class="border-r border-slate-700">Job Title</th>
            <th class="border-r border-slate-700">Company Name</th>
            <th class="border-r border-slate-700">Status</th>
            <th class="border-r border-slate-700">Applicants</th>
            <th class="border-r border-slate-700">Hired / Limit</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="applicant-jobs-tbody">
        @forelse ($applicantJobs as $j)
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
                'status' => $j->job_approved ? 'Approved' : 'Pending',
                'declineReason' => null,
                'approveUrl' => route('jobPosting.approve', $j->job_posting_id),
                'deleteUrl' => route('jobPosting.delete', $j->job_posting_id),
                'image' => $j->thumbnailUrl(),
                'usesDefaultImage' => $j->usesDefaultThumbnail(),
                'imageOverlayColor' => $j->defaultThumbnailOverlay()['color'],
                'imageOverlayOpacity' => $j->defaultThumbnailOverlay()['overlayOpacity'],
                'imageOpacity' => $j->defaultThumbnailOverlay()['imageOpacity'],
                // Tells openViewModal() this is the acting staff member's own
                // post — self-approval is never allowed, so a pending row
                // here renders view-only instead of showing Approve/Decline
                // (see JobPostingController::jobManagementBaseQuery(), which
                // only ever surfaces the OTHER role's postings on the Job
                // Posts tab for that reason).
                'ownPost' => true,
            ];
        @endphp
        <tr class="hover:bg-slate-50/80 transition-colors" data-job-picker-id="{{ $j->job_posting_id }}">
            <td class="font-medium text-black border-r border-slate-100">{{ $loop->iteration }}</td>
            <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_title }}</td>
            <td class="font-medium text-black border-r border-slate-100">{{ $j->job_posting_company }}</td>
            <td class="border-r border-slate-100">
                @if ($j->job_approved)
                <span class="px-2 py-1 rounded-full border text-[6px] font-semibold bg-green-100 text-green-600 border-green-200 inline-block whitespace-nowrap">
                    APPROVED
                </span>
                @else
                <span class="px-2 py-1 rounded-full border text-[7px] font-bold bg-amber-100 text-amber-600 border-amber-200 inline-block whitespace-nowrap">
                    PENDING
                </span>
                @endif
            </td>
            <td class="font-medium text-black border-r border-slate-100">{{ $j->applications_count }}</td>
            <td class="font-medium text-black border-r border-slate-100">{{ $j->hiredApplicantsCount() }} / {{ $j->hiring_limit }}</td>
            <td class="text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <button type="button" onclick='openViewModal({{ $j->job_posting_id }}, @json($viewModalData))'
                        data-job-id="{{ $j->job_posting_id }}"
                        title="View job post"
                        class="p-1.5 hover:bg-blue-50 rounded-full transition-colors">
                        <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i>
                    </button>
                    @if ($j->job_approved)
                    <button type="button" onclick="japp_openApplicantsPanel({{ $j->job_posting_id }})"
                        data-applicants-job-id="{{ $j->job_posting_id }}"
                        class="px-3 py-1.5 bg-[#1D264F] hover:bg-blue-900 text-white rounded-md text-[10px] font-bold uppercase tracking-wide transition-colors">
                        <i data-lucide="users" class="w-3 h-3 inline-block mr-1"></i> Applicants
                    </button>
                    @else
                    <button type="button" disabled title="Still pending approval — no applicants yet"
                        class="px-3 py-1.5 bg-slate-200 text-slate-400 rounded-md text-[10px] font-bold uppercase tracking-wide cursor-not-allowed">
                        <i data-lucide="users" class="w-3 h-3 inline-block mr-1"></i> Applicants
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center py-8 text-slate-400 text-sm">You haven't posted any jobs yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>
<div class="px-4 py-3">
    @include('partials.table-pagination-bar', [
        'id' => 'applicantJobsTable',
        'mode' => 'ajax',
        'paginator' => $applicantJobs,
        'perPageParam' => 'job_applicants_per_page',
        'fetchUrl' => route('jobPosting.jobApplicantsPickerFragment'),
        'wrapId' => 'applicantJobsTableWrap',
    ])
</div>
