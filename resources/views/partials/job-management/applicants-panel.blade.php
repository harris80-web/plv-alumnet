{{--
    Applicant list + hire/decline/shortlist controls for ONE job, loaded via
    fetch() into the Applicants tab's #applicantsPanelWrap (see
    JobPostingController::jobApplicantsFragment() and jobManagement.blade.php's
    japp_openApplicantsPanel()). Mirrors general/jobApplicants.blade.php's own
    "APPLICANTS TABLE" section — same routes, same statuses, same bulk actions
    — just embedded in the admin's own page chrome instead of navigating away
    to the employer-styled page.

    Injected via innerHTML, so this partial carries NO <script> of its own —
    a script tag set via innerHTML never executes. Every japp_* function it
    calls (onclick attributes are resolved at click time, so this is safe)
    lives in jobManagement.blade.php's own <script> block instead.

    Expects: $jobPost (JobPosting, with applicants.user/applicants.program loaded).
--}}
@php
    $hiredCount = $jobPost->hiredApplicantsCount();
    $remainingSlots = $jobPost->remainingHiringSlots();
@endphp

<div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
    <div>
        <h3 class="text-lg font-bold text-[#0E0F3B]">{{ $jobPost->job_posting_title }}</h3>
        <p class="text-xs text-slate-500">{{ $jobPost->job_posting_company }}</p>
    </div>
    <div class="text-xs font-semibold text-gray-600">
        Hiring Limit: <span class="text-[#0E0F3B]">{{ $jobPost->hiring_limit }}</span>
        &nbsp;&middot;&nbsp; Hired: <span class="text-green-600">{{ $hiredCount }}</span>
        &nbsp;&middot;&nbsp; Remaining Slots:
        <span class="{{ $remainingSlots > 0 ? 'text-[#C73D1A]' : 'text-red-500' }}">{{ $remainingSlots }}</span>
    </div>
</div>

<div class="px-6 py-3 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <span class="text-xs font-semibold text-gray-500"><span id="japp-selectedCount">0</span> selected</span>

    <div class="flex items-center gap-2">
        <form id="japp-bulkShortlistForm" action="{{ route('jobApplication.bulkShortlistApplicants', $jobPost->job_posting_id) }}" method="POST" class="hidden">
            @csrf
        </form>
        <form id="japp-bulkDeclineForm" action="{{ route('jobApplication.bulkDeclineApplicants', $jobPost->job_posting_id) }}" method="POST" class="hidden">
            @csrf
        </form>
        <form id="japp-bulkHireForm" action="{{ route('jobApplication.bulkHireApplicants', $jobPost->job_posting_id) }}" method="POST" class="hidden">
            @csrf
        </form>

        <button type="button" id="japp-bulkShortlistBtn" disabled onclick="japp_submitBulkAction('shortlist')"
            class="bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">
            <i data-lucide="star" class="w-3.5 h-3.5 inline-block mr-1"></i> Shortlist
        </button>
        <button type="button" id="japp-bulkDeclineBtn" disabled onclick="japp_submitBulkAction('decline')"
            class="bg-red-500 hover:bg-red-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">
            <i data-lucide="user-x" class="w-3.5 h-3.5 inline-block mr-1"></i> Decline
        </button>
        <button type="button" id="japp-bulkHireBtn" disabled onclick="japp_submitBulkAction('hire')"
            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">
            <i data-lucide="user-check" class="w-3.5 h-3.5 inline-block mr-1"></i> Hire
        </button>
    </div>
</div>

<script id="japp-remaining-slots" type="application/json">{{ $remainingSlots }}</script>

<div class="overflow-x-auto table-scroll">
    <table class="w-full text-sm whitespace-nowrap">
        <thead>
            <tr class="bg-[#1D264F] text-white text-xs uppercase tracking-wider">
                <th class="px-4 py-3 text-center font-semibold w-10">
                    <input type="checkbox" id="japp-selectAllCheckbox" onchange="japp_toggleSelectAll(this)"
                        class="w-4 h-4 cursor-pointer" title="Select all">
                </th>
                <th class="px-4 py-3 text-center font-semibold w-10">#</th>
                <th class="px-4 py-3 text-center font-semibold">Applicant Name</th>
                <th class="px-4 py-3 text-center font-semibold">
                    <div class="relative inline-block">
                        <button type="button" onclick="japp_toggleStatusFilter(this, event)" class="flex items-center gap-1 mx-auto hover:text-yellow-300 transition-colors">
                            STATUS <i class="fas fa-chevron-down text-[10px]"></i>
                        </button>
                        <div id="japp-statusFilterDropdown" class="action-dropdown left-1/2 -translate-x-1/2 right-auto text-left bg-white border border-slate-200 rounded-md shadow-xl">
                            <button type="button" onclick="japp_filterStatus('All')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-slate-50"><i class="fas fa-list w-4 mr-2"></i> All</button>
                            <button type="button" onclick="japp_filterStatus('Pending')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-indigo-600 hover:bg-slate-50"><i class="fas fa-clock w-4 mr-2"></i> Pending</button>
                            <button type="button" onclick="japp_filterStatus('Hired')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-green-600 hover:bg-slate-50"><i class="fas fa-user-check w-4 mr-2"></i> Hired</button>
                            <button type="button" onclick="japp_filterStatus('Declined')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-red-500 hover:bg-slate-50"><i class="fas fa-user-times w-4 mr-2"></i> Declined</button>
                            <button type="button" onclick="japp_filterStatus('Shortlisted')" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-yellow-600 hover:bg-slate-50"><i class="fas fa-star w-4 mr-2"></i> Shortlisted</button>
                        </div>
                    </div>
                </th>
                <th class="px-4 py-3 text-center font-semibold">Application</th>
                <th class="px-4 py-3 text-center font-semibold">Program</th>
                <th class="px-4 py-3 text-center font-semibold">College</th>
                <th class="px-4 py-3 text-center font-semibold">Compatibility</th>
                <th class="px-4 py-3 text-center font-semibold">Cover Letter</th>
                <th class="px-4 py-3 text-center font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody id="japp-applicants-tbody" class="divide-y divide-gray-100">
            @forelse ($jobPost->applicants as $index => $applicant)
            @php
                $status = $applicant->pivot->application_status ?? 'Pending';
                $badgeClasses = match(strtolower($status)) {
                    'hired' => 'bg-green-100 text-green-600',
                    'declined' => 'bg-red-100 text-red-600',
                    'shortlisted' => 'bg-yellow-100 text-yellow-700',
                    default => 'bg-indigo-100 text-indigo-700',
                };

                $resumeUrl = null;
                $isBuilderResume = $applicant->pivot->resume_source === 'builder';
                if ($applicant->pivot->resume_source === 'upload' && $applicant->pivot->resume_path) {
                    $resumeUrl = asset('storage/' . $applicant->pivot->resume_path);
                } elseif ($isBuilderResume) {
                    // handled below via the View Application modal
                } elseif ($applicant->alumnus_resume_file_path) {
                    $resumeUrl = asset('storage/' . $applicant->alumnus_resume_file_path);
                } elseif (($applicant->alumnus_resume_completeness ?? 0) > 0) {
                    $resumeUrl = route('resume.viewApplicant', $applicant->user_id);
                }

                $coverLetterUrl = null;
                if ($applicant->pivot->cover_letter_source === 'upload' && $applicant->pivot->cover_letter_path) {
                    $coverLetterUrl = asset('storage/' . $applicant->pivot->cover_letter_path);
                } elseif ($applicant->pivot->cover_letter_source === 'profile' && $applicant->alumnus_cover_letter_file_path) {
                    $coverLetterUrl = asset('storage/' . $applicant->alumnus_cover_letter_file_path);
                }

                $resumeSourceLabels = ['profile' => 'Uploaded profile resume', 'upload' => 'Uploaded just for this job', 'builder' => 'Resume Builder profile'];
                $coverLetterSourceLabels = ['none' => "Didn't include one", 'profile' => 'Saved cover letter', 'upload' => 'Uploaded just for this job'];

                $applicationData = [
                    'applicantName' => trim($applicant->user->user_first_name . ' ' . $applicant->user->user_last_name),
                    'applicantEmail' => $applicant->user->user_email,
                    'applicantContact' => $applicant->user->user_number,
                    'applicantPhoto' => $applicant->user->user_profile_picture ? asset('storage/' . $applicant->user->user_profile_picture) : null,
                    'appliedAt' => optional($applicant->pivot->application_date ?? $applicant->pivot->created_at)->format('M d, Y h:i A'),
                    'status' => ucfirst($status),
                    'score' => $applicant->pivot->application_score !== null ? $applicant->pivot->application_score . '%' : null,
                    'resumeSourceLabel' => $resumeSourceLabels[$applicant->pivot->resume_source] ?? $applicant->pivot->resume_source,
                    'resumeUrl' => $resumeUrl,
                    'isBuilderResume' => $isBuilderResume,
                    'builderSnapshot' => $isBuilderResume
                        ? (is_array($applicant->pivot->builder_resume_snapshot)
                            ? $applicant->pivot->builder_resume_snapshot
                            : json_decode($applicant->pivot->builder_resume_snapshot ?? '', true))
                        : null,
                    'coverLetterSourceLabel' => $coverLetterSourceLabels[$applicant->pivot->cover_letter_source ?? 'none'] ?? $applicant->pivot->cover_letter_source,
                    'coverLetterUrl' => $coverLetterUrl,
                ];
            @endphp
            @php $isBulkEligible = in_array(strtolower($status), ['pending', 'shortlisted']); @endphp
            <tr data-status="{{ $status }}" data-application-id="{{ $applicant->pivot->application_id }}"
                data-name="{{ strtolower(trim($applicant->user->user_first_name . ' ' . $applicant->user->user_last_name)) }}">
                <td class="px-4 py-4 text-center">
                    <input type="checkbox" class="japp-applicant-checkbox w-4 h-4 accent-[#1D264F] cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                        value="{{ $applicant->pivot->application_id }}"
                        {{ $isBulkEligible ? '' : 'disabled' }}
                        title="{{ $isBulkEligible ? '' : 'Already ' . strtolower($status) . ' — no bulk action left to take' }}"
                        onchange="japp_updateBulkActionUI()">
                </td>
                <td class="px-4 py-4 text-center text-gray-400 font-semibold">{{ $index + 1 }}</td>
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($applicant->user->user_first_name . '+' . $applicant->user->user_last_name) }}&background=1D264F&color=fff&size=36"
                            class="w-9 h-9 rounded-full">
                        <span class="font-semibold text-[#0E0F3B]">
                            {{ $applicant->user->user_last_name }}, {{ $applicant->user->user_first_name }} {{ $applicant->user->user_middle_name }} {{ $applicant->user->user_suffix }}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-4 text-center">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $badgeClasses }}">{{ ucfirst($status) }}</span>
                </td>
                <td class="px-4 py-4 text-center">
                    <button type="button" onclick='japp_openApplicationViewModal(@json($applicationData))'
                        class="border-2 border-[#1D264F] text-[#1D264F] hover:bg-[#1D264F] hover:text-white text-xs font-bold px-4 py-1.5 rounded-md transition-colors">
                        <i class="fas fa-eye mr-1"></i> View
                    </button>
                </td>
                <td class="px-4 py-4 text-center text-gray-500 text-xs">{{ $applicant->program->program_name ?? 'N/A' }}</td>
                <td class="px-4 py-4 text-center text-gray-500 text-xs">{{ $applicant->program?->collegeName() ?? 'N/A' }}</td>
                <td class="px-4 py-4 text-center">
                    @php $score = $applicant->pivot->application_score; @endphp
                    @if($score !== null)
                        <span class="text-xs font-bold px-2 py-1 rounded-full
                            {{ $score >= 70 ? 'bg-green-100 text-green-700' : ($score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $score }}%
                        </span>
                    @else
                        <span class="text-gray-400 text-xs">&mdash;</span>
                    @endif
                </td>
                <td class="px-4 py-4 text-center">
                    @if ($coverLetterUrl)
                    <a href="{{ $coverLetterUrl }}" target="_blank"
                        class="bg-[#1D264F] hover:bg-[#0E0F3B] text-white text-xs font-bold px-4 py-1.5 rounded-md transition-colors inline-block">
                        View
                    </a>
                    @else
                    <span class="text-gray-400 text-xs">N/A</span>
                    @endif
                </td>
                <td class="px-4 py-4 text-center">
                    <div class="relative inline-block">
                        <button type="button" onclick="japp_toggleDropdown(this, event)"
                            class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                            <i class="fas fa-ellipsis-v text-gray-500"></i>
                        </button>
                        <div class="action-dropdown bg-white border border-slate-200 rounded-md shadow-xl">
                            @if($remainingSlots > 0)
                            <form action="{{ route('jobApplication.hireApplicant', $applicant->pivot->application_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-green-600 hover:bg-green-50">
                                    <i class="fas fa-user-check w-4 mr-2"></i> Hire
                                </button>
                            </form>
                            @else
                            <button type="button" disabled class="flex items-center w-full px-4 py-2 text-xs font-semibold text-gray-300 cursor-not-allowed" title="Hiring limit reached">
                                <i class="fas fa-user-check w-4 mr-2"></i> Hire
                            </button>
                            @endif
                            <form action="{{ route('jobApplication.declineApplicant', $applicant->pivot->application_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-red-500 hover:bg-red-50">
                                    <i class="fas fa-user-times w-4 mr-2"></i> Decline
                                </button>
                            </form>
                            <form action="{{ route('jobApplication.shortlistApplicant', $applicant->pivot->application_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-xs font-semibold text-yellow-600 hover:bg-yellow-50">
                                    <i class="fas fa-star w-4 mr-2"></i> Shortlist
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="py-16 text-center text-gray-400">
                    <i class="fas fa-inbox text-5xl mb-3 block"></i>
                    <p class="font-semibold">No applicants yet.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="px-6 py-4">
    @include('partials.table-pagination-bar', [
        'id' => 'japp-applicantsTable',
        'mode' => 'client',
        'rowSelector' => '#japp-applicants-tbody tr[data-status]',
        'totalItems' => $jobPost->applicants->count(),
    ])
</div>

{{-- "View Application" modal — everything the alumnus submitted when applying. --}}
<div id="japp-applicationViewModal" class="fixed inset-0 z-[210] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative max-h-[90vh] overflow-y-auto my-8 p-8">
        <button type="button" onclick="japp_closeApplicationViewModal()" class="absolute top-6 right-6 text-gray-300 hover:text-gray-500 transition-colors">
            <i class="fas fa-times-circle text-2xl"></i>
        </button>

        <h2 class="text-2xl font-bold text-[#1D264F] mb-4">Application Details</h2>

        <div class="flex items-center gap-4 bg-gray-50 rounded-2xl p-4 mb-6">
            <img id="japp-avm-photo" src="" class="hidden w-16 h-16 rounded-full object-cover border-2 border-white shadow">
            <div id="japp-avm-photo-fallback" class="w-16 h-16 rounded-full bg-[#1D264F] text-white flex items-center justify-center text-lg font-bold shrink-0"></div>
            <div class="min-w-0">
                <p id="japp-avm-name" class="font-bold text-[#0E0F3B] text-lg truncate"></p>
                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5"><i class="fas fa-envelope text-[#1D46A4]"></i> <span id="japp-avm-email"></span></p>
                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5"><i class="fas fa-phone text-[#1D46A4]"></i> <span id="japp-avm-contact"></span></p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold">Status</p>
                <p id="japp-avm-status" class="text-sm font-bold text-[#0E0F3B] mt-1"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold">Compatibility</p>
                <p id="japp-avm-score" class="text-sm font-bold text-[#0E0F3B] mt-1"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold">Applied</p>
                <p id="japp-avm-applied-at" class="text-sm font-bold text-[#0E0F3B] mt-1"></p>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-xs font-bold text-[#1D264F] uppercase mb-2">Resume / CV</h3>
            <p id="japp-avm-resume-source" class="text-xs text-gray-400 mb-2"></p>
            <div id="japp-avm-resume-content"></div>
        </div>

        <div>
            <h3 class="text-xs font-bold text-[#1D264F] uppercase mb-2">Cover Letter</h3>
            <div id="japp-avm-cover-letter-content"></div>
        </div>
    </div>
</div>
