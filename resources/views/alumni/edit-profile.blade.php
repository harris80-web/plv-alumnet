<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Edit Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }

    /* Same profile card banner as the Alumni Profile page. */
    .profile-banner {
        background:
            url("{{ asset('assets/heroSection.svg') }}") center / cover;
    }

    /* One look for every field on this form (same as Edit Employer Profile). */
    .profile-field {
        width: 100%;
        padding: 0.55rem 0.85rem;
        background: #ffffff;
        border: 1px solid #0E0F3B;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #0E0F3B;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .profile-field::placeholder { color: #94a3b8; font-weight: 400; }
    .profile-field:focus { border-color: #C73D1A; box-shadow: 0 0 0 3px rgba(199, 61, 26, 0.12); }
    .profile-field:disabled { background: #f1f5f9; border-color: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
    .profile-label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #C73D1A;
    }
    /* Required / optional markers next to the labels. */
    .req-mark { margin-left: 2px; color: #C73D1A; }
    .opt-tag {
        display: inline-block;
        margin-left: 0.4rem;
        padding: 1px 6px;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
        vertical-align: 1px;
    }
    .profile-help { margin-top: 0.35rem; font-size: 11px; line-height: 1.45; color: #64748b; }
</style>

@php
    $alumnus = $user->alumnus;
    $readOnlyDetails = [
        'Last Name' => $user->user_last_name,
        'First Name' => $user->user_first_name,
        'Middle Name' => $user->user_middle_name,
        'Suffix' => $user->user_suffix,
        'Gender' => \App\Models\Alumnus::genderLabels()[$alumnus->alumnus_gender] ?? null,
        'Batch' => $alumnus->alumnus_batch?->format('Y'),
        'Program' => $alumnus->program->program_name ?? null,
        'College' => $alumnus->program?->collegeName(),
    ];
    $lockedTag = '<span class="opt-tag"><i class="fa-solid fa-lock text-[8px] mr-0.5"></i> Locked</span>';
    $optionalTag = '<span class="opt-tag">Optional</span>';
    $requiredMark = '<span class="req-mark" aria-hidden="true">*</span>';
@endphp

<body class="min-h-screen flex flex-col bg-gray-50">
    @include('partials.header-alumni')

    {{-- Hero banner hidden on this page (the page title + profile card replace it).
    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Edit Alumni Profile</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>
    --}}

    <main class="w-full max-w-6xl mx-auto px-6 py-8 flex-1 font-['Montserrat']">
        @include('partials.error-toast')

        {{-- Client-side-only validation failures (e.g. Industry left
             unset while Employed is selected) — see the form's submit
             listener below, which toggles this. Fixed to the top of the
             viewport so it's visible wherever the page is scrolled. --}}
        <div id="clientValidationError" class="hidden fixed inset-x-0 top-0 z-[300] flex justify-center pt-6 px-4 pointer-events-none">
            <div class="pointer-events-auto flex items-center gap-3 bg-red-50 border border-red-300 text-red-700 rounded-xl px-5 py-4 shadow-lg max-w-md w-full">
                <i class="fa-solid fa-circle-exclamation text-red-500 text-base shrink-0"></i>
                <p class="text-sm font-semibold flex-1"><span id="clientValidationErrorText"></span></p>
                <button type="button" onclick="document.getElementById('clientValidationError').classList.add('hidden')" class="text-red-400 hover:text-red-600 shrink-0">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        @include('partials.success')

        @include('partials.error')

        <form id="alumniProfileForm" action="{{ route('alumni.updateProfile', $user->user_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Title row: same layout as the Alumni Profile page, with Cancel / Save on the right.
                 Phones: the buttons drop under the subtitle and share the row. --}}
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mb-5 sm:mb-6">
                <h1 class="inline-block text-xl sm:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                    EDIT ALUMNI PROFILE
                </h1>
                <div class="order-3 sm:order-2 w-full sm:w-auto flex items-center gap-2 sm:gap-3">
                    {{-- An <a>, not a <button>: a plain button inside this form would submit it. --}}
                    <a href="{{ route('user.profile') }}"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg border border-[#0E0F3B] text-[#0E0F3B] font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest hover:bg-[#0E0F3B] hover:text-white transition-colors whitespace-nowrap">
                        CANCEL
                    </a>
                    <button type="submit"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-[#1D264F] hover:bg-blue-900 text-white px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg font-bold text-[11px] sm:text-xs tracking-wider sm:tracking-widest shadow-lg transition-colors whitespace-nowrap">
                        <i class="fa-solid fa-floppy-disk"></i> SAVE CHANGES
                    </button>
                </div>
                <p class="order-2 sm:order-3 w-full -mt-2 text-xs sm:text-sm text-slate-500">Update your employment, contact details and documents. <span class="whitespace-nowrap">Fields marked <span class="font-bold text-[#C73D1A]">*</span> are required.</span></p>
            </div>

            {{-- ===== Profile card (photo + details from your PLV records) ===== --}}
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100" aria-label="Profile photo and alumni details">
                <div class="profile-banner h-24 sm:h-28 rounded-t-2xl"></div>
                <div class="px-5 sm:px-8 pb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                        <div class="relative -mt-12 w-24 h-24 sm:w-28 sm:h-28 shrink-0">
                            <div class="w-full h-full bg-[#0E0F3B] rounded-full flex items-center justify-center border-4 border-white shadow-md overflow-hidden">
                                <img id="profileImagePreview"
                                    src="{{ $user->user_profile_picture ? asset('storage/' . $user->user_profile_picture) : '' }}"
                                    alt="Profile Picture"
                                    class="w-full h-full object-cover {{ $user->user_profile_picture ? '' : 'hidden' }}"
                                    style="{{ $user->user_profile_picture ? '' : 'display:none' }}">
                                <i id="profileImagePlaceholderIcon" class="fa-solid fa-user text-4xl sm:text-5xl text-white mt-2 {{ $user->user_profile_picture ? 'hidden' : '' }}"
                                    style="{{ $user->user_profile_picture ? 'display:none' : '' }}"></i>
                            </div>

                            <button type="button" onclick="togglePhotoOptions(event)" title="Change profile photo" aria-label="Change profile photo"
                                class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#C73D1A] text-white border-2 border-white shadow flex items-center justify-center text-xs hover:bg-[#a83215] transition">
                                <i class="fa-solid fa-camera"></i>
                            </button>

                            <div id="photoOptions" class="hidden absolute top-full mt-2 left-0 w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                <button type="button" onclick="openImageLightbox(document.getElementById('profileImagePreview').src)" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-50 flex items-center gap-3 transition">
                                    <i class="fa-solid fa-image text-sm"></i> View Profile Image
                                </button>
                                <label for="user_profile_picture" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:bg-gray-50 flex items-center gap-3 cursor-pointer mb-0 transition">
                                    <i class="fa-solid fa-upload text-sm"></i> Upload an Image
                                    <input type="file" name="user_profile_picture" id="user_profile_picture" accept="image/*" class="hidden"
                                        onchange="previewImageInput(this, 'profileImagePreview', 'profileImagePlaceholderIcon')">
                                </label>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0 sm:pt-4">
                            <h2 class="text-lg sm:text-2xl font-bold text-[#0E0F3B] uppercase break-words">{{ $alumnus->formalName() }}</h2>
                            <p class="text-xs sm:text-sm font-semibold text-[#ED7A07] uppercase mt-1 rp-profile-meta">
                                <span class="rp-profile-course">{{ $alumnus->program->program_name ?? 'Not specified' }}</span><span class="rp-profile-sep"> &bull; </span><span class="rp-profile-batch">Alumni Batch {{ $alumnus->alumnus_batch?->format('Y') ?? '--' }}</span>
                            </p>
                            <p class="mt-2 text-sm text-slate-500"><span class="font-semibold text-[#1D264F]">Profile photo</span>{!! $optionalTag !!}<span class="block sm:inline sm:ml-1">PNG, JPG or GIF, up to 2 MB. Use the camera button to change it.</span></p>
                        </div>
                    </div>

                    {{-- Identity details come from PLV's records — shown here, not editable. --}}
                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-4">
                            <p class="text-[11px] font-bold text-[#C73D1A] uppercase tracking-wide">Alumni Details</p>
                            <p class="text-[11px] text-slate-500"><i class="fa-solid fa-lock text-[9px] mr-1"></i>From your PLV records — these can't be edited here.</p>
                        </div>
                        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-4">
                            @foreach ($readOnlyDetails as $label => $value)
                            <div class="min-w-0 {{ in_array($label, ['Program', 'College']) ? 'col-span-2' : '' }}">
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-[#C73D1A]">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-[#0E0F3B] uppercase break-words">{!! $value ? e($value) : '<span class="text-slate-300">—</span>' !!}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </section>

            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                {{-- ===== Left: employment + documents ===== --}}
                <div class="lg:col-span-2 space-y-6">
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-briefcase"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Employment Details</h3>
                                <p class="text-xs text-slate-500">Your current work status and job information.</p>
                            </div>
                        </header>
                        <div class="p-5 sm:p-6">
                            <div class="sm:w-1/2 sm:pr-3">
                                <label for="alumnus_employment_status" class="profile-label">Employment Status{!! $requiredMark !!}</label>
                                <select name="alumnus_employment_status" id="alumnus_employment_status" required onchange="toggleEmploymentFields(this.value)" class="profile-field">
                                    <option value="1" {{ $alumnus->alumnus_employment_status == 1 ? 'selected' : '' }}>Employed</option>
                                    <option value="0" {{ $alumnus->alumnus_employment_status == 0 ? 'selected' : '' }}>Unemployed</option>
                                </select>
                            </div>

                            {{-- Only relevant once "Employed" is picked above. --}}
                            <div id="employment-fields" class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 {{ $alumnus->alumnus_employment_status ? '' : 'hidden' }}">
                                <div>
                                    <label for="industry_id" class="profile-label">Industry / Sector{!! $requiredMark !!}</label>
                                    {{-- Not a native `required` attribute — this field is hidden while
                                         Unemployed, and the browser's own bubble can silently fail to
                                         appear for it. The submit listener below enforces it instead
                                         and always shows a visible reason. --}}
                                    <select name="industry_id" id="industry_id" class="profile-field">
                                        <option value="" disabled {{ $alumnus->industry_id ? '' : 'selected' }}>Select Industry / Sector</option>
                                        @foreach($industries as $industry)
                                        <option value="{{ $industry->industry_id }}" {{ $alumnus->industry_id == $industry->industry_id ? 'selected' : '' }}>
                                            {{ $industry->industry_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <p class="profile-help">
                                        Required while Employed. Set automatically when you're hired through a job post here — change it yourself if your job didn't come from the system.
                                    </p>
                                </div>

                                <div>
                                    <label for="alumnus_workplace" class="profile-label">Where Do You Work?{!! $optionalTag !!}</label>
                                    <input type="hidden" name="alumnus_workplace_undisclosed" id="alumnus_workplace_undisclosed" value="{{ $alumnus->alumnus_workplace_undisclosed ? 1 : 0 }}">
                                    <input type="text" name="alumnus_workplace" id="alumnus_workplace" placeholder="Company / Organization name"
                                        value="{{ $alumnus->alumnus_workplace }}"
                                        {{ $alumnus->alumnus_workplace_undisclosed ? 'disabled' : '' }}
                                        class="profile-field">
                                    {{-- Under the input (not beside the label) so this field lines up with Industry / Sector. --}}
                                    <button type="button" id="workplaceUndisclosedBtn" onclick="toggleWorkplaceUndisclosed()"
                                        class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1 rounded border border-[#0E0F3B] text-[#0E0F3B] hover:bg-[#0E0F3B] hover:text-white transition">
                                        <i class="fa-solid fa-eye-slash"></i>
                                        <span id="workplaceUndisclosedLabel">{{ $alumnus->alumnus_workplace_undisclosed ? 'Disclose Workplace' : "Don't Disclose" }}</span>
                                    </button>
                                    <p class="profile-help">
                                        Set automatically when you're hired through a job post here — only fill this in yourself if your job didn't come from the system. You can also mark it undisclosed and just keep your industry set.
                                    </p>
                                </div>

                                <div>
                                    @if($alumnus->alumnus_employed_via_platform)
                                    <label class="profile-label">Job Position{!! $lockedTag !!}</label>
                                    <input type="text" value="{{ $alumnus->alumnus_job_position }}" disabled aria-label="Job Position" class="profile-field">
                                    <p class="profile-help">
                                        Set from the job posting you were hired through here — not editable while that's your current job.
                                    </p>
                                    @else
                                    <label for="alumnus_job_position" class="profile-label">Job Position{!! $optionalTag !!}</label>
                                    <input type="text" name="alumnus_job_position" id="alumnus_job_position" placeholder="e.g. Software Engineer"
                                        value="{{ $alumnus->alumnus_job_position }}" class="profile-field">
                                    <p class="profile-help">
                                        Set automatically when you're hired through a job post here — fill this in yourself if your job didn't come from the system.
                                    </p>
                                    @endif
                                </div>

                                <div>
                                    @if($alumnus->alumnus_employed_via_platform)
                                    <label class="profile-label">Employment Date{!! $lockedTag !!}</label>
                                    <input type="date" value="{{ optional($alumnus->alumnus_employment_date)->format('Y-m-d') }}" disabled aria-label="Employment Date" class="profile-field">
                                    <p class="profile-help">
                                        Set when you were hired through this job post — not editable while that's your current job.
                                    </p>
                                    @else
                                    <label for="alumnus_employment_date" class="profile-label">Employment Date{!! $optionalTag !!}</label>
                                    <input type="date" name="alumnus_employment_date" id="alumnus_employment_date" max="{{ now()->format('Y-m-d') }}"
                                        value="{{ optional($alumnus->alumnus_employment_date)->format('Y-m-d') }}" class="profile-field">
                                    <p class="profile-help">
                                        When you started your current job — not the same as your date of first job below. Set automatically when hired through a job post here.
                                    </p>
                                    @endif
                                </div>

                                <div>
                                    @if($alumnus->alumnus_first_job_date)
                                    <label class="profile-label">Date of First Job{!! $lockedTag !!}</label>
                                    <input type="date" value="{{ $alumnus->alumnus_first_job_date->format('Y-m-d') }}" disabled aria-label="Date of First Job" class="profile-field">
                                    <p class="profile-help">
                                        Already recorded — this can only be set once.
                                    </p>
                                    @if($alumnus->alumnus_first_job_is_internship)
                                    <p class="text-[11px] font-bold text-[#C73D1A] mt-1">
                                        <i class="fas fa-check-circle"></i> Recorded as an internship.
                                    </p>
                                    @endif
                                    @else
                                    <label for="alumnus_first_job_date" class="profile-label">Date of First Job{!! $optionalTag !!}</label>
                                    <input type="date" name="alumnus_first_job_date" id="alumnus_first_job_date" max="{{ now()->format('Y-m-d') }}" class="profile-field">
                                    <p class="profile-help">
                                        Set automatically when you're hired through a job post here — only fill this in yourself if your first job didn't come from the system. You can only set this once.
                                    </p>
                                    <label class="flex items-center gap-2 mt-2 text-xs text-[#0E0F3B]">
                                        <input type="checkbox" name="alumnus_first_job_is_internship" value="1" class="rounded border-[#0E0F3B]">
                                        This was an internship / OJT, not a regular job.
                                    </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Documents — own uploaded Resume/CV and an optional Cover
                         Letter, distinct from the Resume Builder (which generates
                         a PDF from profile data). These are the actual files
                         offered as "Use my AlumNet Profile" when applying to a
                         job — see partials/job-apply-modal.blade.php. --}}
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-folder-open"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Documents</h3>
                                <p class="text-xs text-slate-500">Upload your own Resume/CV and an optional Cover Letter. These can be submitted straight to employers when you apply for a job.</p>
                            </div>
                        </header>

                        <div class="p-5 sm:p-6 space-y-5">
                            <div>
                                <label for="alumnusResumeFileInput" class="profile-label">Resume / CV{!! $optionalTag !!}</label>
                                <div class="flex flex-wrap items-center justify-between gap-3 border border-slate-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <i class="fas fa-file-lines text-[#C73D1A] text-xl shrink-0"></i>
                                        <div class="min-w-0">
                                            <div id="resumeFileCurrent">
                                                @if ($alumnus->alumnus_resume_file_path)
                                                <a href="{{ asset('storage/' . $alumnus->alumnus_resume_file_path) }}" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate block">
                                                    {{ basename($alumnus->alumnus_resume_file_path) }}
                                                </a>
                                                @else
                                                <p class="text-sm font-medium text-[#1D264F]">No resume uploaded yet</p>
                                                @endif
                                            </div>
                                            <div id="resumeFileRemoveNotice" class="hidden items-center gap-2">
                                                <p class="text-sm text-red-600 font-semibold">Will be removed when you save</p>
                                                <button type="button" onclick="undoDocumentRemoval('resume')" class="text-[10px] font-bold text-[#1D46A4] hover:underline">Undo</button>
                                            </div>
                                            <p id="resumeFileSelected" class="text-sm text-gray-500 font-semibold break-words"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" onclick="document.getElementById('alumnusResumeFileInput').click()"
                                            class="shrink-0 bg-[#1D264F] text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-[#0E0F3B] transition-colors">
                                            {{ $alumnus->alumnus_resume_file_path ? 'Replace' : 'Upload' }}
                                        </button>
                                        @if ($alumnus->alumnus_resume_file_path)
                                        <button type="button" id="removeResumeFileBtn" onclick="stageDocumentRemoval('resume')"
                                            class="shrink-0 border border-red-300 text-red-600 text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-red-50 transition-colors">
                                            Remove
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="remove_alumnus_resume_file" id="removeAlumnusResumeFileFlag" value="0">
                                <input type="file" name="alumnus_resume_file" id="alumnusResumeFileInput" accept=".pdf,.doc,.docx" class="hidden" onchange="previewDocumentName(this, 'resumeFileSelected', 'resume')">
                                <p class="profile-help">PDF, DOC, or DOCX — up to 5MB.</p>
                            </div>

                            <div>
                                <label for="alumnusResumeBackupFileInput" class="profile-label">Backup Resume / CV{!! $optionalTag !!}</label>
                                <div class="flex flex-wrap items-center justify-between gap-3 border border-slate-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <i class="fas fa-copy text-[#C73D1A] text-xl shrink-0"></i>
                                        <div class="min-w-0">
                                            <div id="resumeBackupFileCurrent">
                                                @if ($alumnus->alumnus_resume_backup_file_path)
                                                <a href="{{ asset('storage/' . $alumnus->alumnus_resume_backup_file_path) }}" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate block">
                                                    {{ basename($alumnus->alumnus_resume_backup_file_path) }}
                                                </a>
                                                @else
                                                <p class="text-sm font-medium text-[#1D264F]">No backup resume uploaded yet</p>
                                                @endif
                                            </div>
                                            <div id="resumeBackupFileRemoveNotice" class="hidden items-center gap-2">
                                                <p class="text-sm text-red-600 font-semibold">Will be removed when you save</p>
                                                <button type="button" onclick="undoDocumentRemoval('resumeBackup')" class="text-[10px] font-bold text-[#1D46A4] hover:underline">Undo</button>
                                            </div>
                                            <p id="resumeBackupFileSelected" class="text-sm text-gray-500 font-semibold break-words"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" onclick="document.getElementById('alumnusResumeBackupFileInput').click()"
                                            class="shrink-0 bg-[#1D264F] text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-[#0E0F3B] transition-colors">
                                            {{ $alumnus->alumnus_resume_backup_file_path ? 'Replace' : 'Upload' }}
                                        </button>
                                        @if ($alumnus->alumnus_resume_backup_file_path)
                                        <button type="button" id="removeResumeBackupFileBtn" onclick="stageDocumentRemoval('resumeBackup')"
                                            class="shrink-0 border border-red-300 text-red-600 text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-red-50 transition-colors">
                                            Remove
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="remove_alumnus_resume_backup_file" id="removeAlumnusResumeBackupFileFlag" value="0">
                                <input type="file" name="alumnus_resume_backup_file" id="alumnusResumeBackupFileInput" accept=".pdf,.doc,.docx" class="hidden" onchange="previewDocumentName(this, 'resumeBackupFileSelected', 'resumeBackup')">
                                <p class="profile-help">A spare copy in case your primary resume is unavailable — PDF, DOC, or DOCX, up to 5MB.</p>
                            </div>

                            <div>
                                <label for="alumnusCoverLetterFileInput" class="profile-label">Cover Letter{!! $optionalTag !!}</label>
                                <div class="flex flex-wrap items-center justify-between gap-3 border border-slate-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <i class="fas fa-envelope-open-text text-[#C73D1A] text-xl shrink-0"></i>
                                        <div class="min-w-0">
                                            <div id="coverLetterFileCurrent">
                                                @if ($alumnus->alumnus_cover_letter_file_path)
                                                <a href="{{ asset('storage/' . $alumnus->alumnus_cover_letter_file_path) }}" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate block">
                                                    {{ basename($alumnus->alumnus_cover_letter_file_path) }}
                                                </a>
                                                @else
                                                <p class="text-sm font-medium text-[#1D264F]">No cover letter uploaded yet</p>
                                                @endif
                                            </div>
                                            <div id="coverLetterFileRemoveNotice" class="hidden items-center gap-2">
                                                <p class="text-sm text-red-600 font-semibold">Will be removed when you save</p>
                                                <button type="button" onclick="undoDocumentRemoval('coverLetter')" class="text-[10px] font-bold text-[#1D46A4] hover:underline">Undo</button>
                                            </div>
                                            <p id="coverLetterFileSelected" class="text-sm text-gray-500 font-semibold break-words"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" onclick="document.getElementById('alumnusCoverLetterFileInput').click()"
                                            class="shrink-0 bg-[#1D264F] text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-[#0E0F3B] transition-colors">
                                            {{ $alumnus->alumnus_cover_letter_file_path ? 'Replace' : 'Upload' }}
                                        </button>
                                        @if ($alumnus->alumnus_cover_letter_file_path)
                                        <button type="button" id="removeCoverLetterFileBtn" onclick="stageDocumentRemoval('coverLetter')"
                                            class="shrink-0 border border-red-300 text-red-600 text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-red-50 transition-colors">
                                            Remove
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="remove_alumnus_cover_letter_file" id="removeAlumnusCoverLetterFileFlag" value="0">
                                <input type="file" name="alumnus_cover_letter_file" id="alumnusCoverLetterFileInput" accept=".pdf,.doc,.docx" class="hidden" onchange="previewDocumentName(this, 'coverLetterFileSelected', 'coverLetter')">
                                <p class="profile-help">PDF, DOC, or DOCX — up to 5MB.</p>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- ===== Right: contact, resume, profile settings ===== --}}
                <div class="space-y-6">
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-address-book"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Contact Details</h3>
                                <p class="text-xs text-slate-500">How PLV-AlumNet can reach you.</p>
                            </div>
                        </header>
                        <div class="p-5 sm:p-6 space-y-5">
                            <div>
                                <label for="user_email" class="profile-label">Email{!! $requiredMark !!}</label>
                                <input type="email" name="user_email" id="user_email" required placeholder="example@email.com" value="{{ $user->user_email }}" class="profile-field">
                            </div>
                            <div>
                                <label for="user_number" class="profile-label">Contact No.{!! $optionalTag !!}</label>
                                <input type="text" name="user_number" id="user_number" placeholder="09XXXXXXXXX" value="{{ $user->user_number }}" class="profile-field">
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-orange-50 text-[#C73D1A] flex items-center justify-center"><i class="fa-solid fa-file-lines"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Resume{!! $optionalTag !!}</h3>
                                <p class="text-xs text-slate-500">Built with the Resume Builder.</p>
                            </div>
                        </header>
                        <div class="p-5 sm:p-6">
                            {{-- Skills are managed in the Resume Builder (a many-to-many
                                 relation, not a field this form posts) — shown read-only here. --}}
                            <p class="profile-label">Skills</p>
                            @if ($alumnus->skills->isNotEmpty())
                            <ul class="flex flex-wrap gap-1.5">
                                @foreach ($alumnus->skills->pluck('skill_name') as $skill)
                                <li class="text-xs font-semibold text-[#1D264F] bg-[#1D264F]/5 border border-[#1D264F]/10 rounded-full px-3 py-1">{{ $skill }}</li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-sm font-medium text-[#1D264F]">Add skills in the Resume Builder.</p>
                            @endif

                            @php $resumePercent = (int) ($alumnus->alumnus_resume_completeness ?? 0); @endphp
                            <div class="mt-5 flex items-end justify-between">
                                <span class="profile-label !mb-0">Resume Strength</span>
                                <span class="text-sm font-bold text-[#0E0F3B]">{{ $resumePercent }}%</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-label="Resume strength"
                                aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $resumePercent }}">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07]" style="width: {{ $resumePercent }}%"></div>
                            </div>

                            <button type="button" id="openResumeEditorBtn"
                                class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2.5 px-6 rounded-lg shadow-md transition duration-200 uppercase tracking-wider">
                                <i class="fa-solid fa-pen-to-square"></i> {{ $alumnus->isResumeComplete() ? 'Edit Resume' : 'Create Resume' }}
                            </button>
                        </div>
                    </section>

                    {{-- Profile Settings — per-field directory visibility. Purely a
                         display-layer toggle on the alumni.updateProfile form; it
                         doesn't touch the underlying skills/email/linkedin data,
                         just whether the Alumni Directory's "View Profile" modal
                         shows them to other alumni. --}}
                    <section class="bg-white rounded-2xl shadow-sm border border-slate-100">
                        <header class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-eye"></i></span>
                            <div>
                                <h3 class="inline-block text-sm font-bold uppercase tracking-wide bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Profile Settings</h3>
                                <p class="text-xs text-slate-500">Choose what other alumni can see on your profile in the Alumni Directory. Your information is never shown to employers, and staff can always see it.</p>
                            </div>
                        </header>

                        <div class="px-5 sm:px-6 py-2 divide-y divide-slate-100">
                            <div class="flex items-center justify-between py-3">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-[#0E0F3B]">Show Skills</p>
                                    <p class="text-xs text-slate-500">Let other alumni see the skills listed on your resume.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="alumnus_show_skills" value="1"
                                        {{ old('alumnus_show_skills', $alumnus->alumnus_show_skills) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0E0F3B] transition-colors
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full relative"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-[#0E0F3B]">Show Email</p>
                                    <p class="text-xs text-slate-500">Let other alumni see your email address.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="alumnus_show_email" value="1"
                                        {{ old('alumnus_show_email', $alumnus->alumnus_show_email) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0E0F3B] transition-colors
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full relative"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div class="pr-4">
                                    <p class="text-sm font-semibold text-[#0E0F3B]">Show LinkedIn</p>
                                    <p class="text-xs text-slate-500">Let other alumni see your LinkedIn profile link.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="alumnus_show_linkedin" value="1"
                                        {{ old('alumnus_show_linkedin', $alumnus->alumnus_show_linkedin) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0E0F3B] transition-colors
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full relative"></div>
                                </label>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>

    @if($alumnus->isResumeComplete())
        @include('alumni.resume-editor-modal', ['user' => $user, 'resumeData' => $resumeData, 'industries' => $industries])
    @else
        @include('alumni.resume-builder-modal', ['user' => $user, 'resumeData' => $resumeData, 'industries' => $industries])
    @endif

    @include('partials.footer-alumni')
    @include('partials.image-lightbox')

</body>

<script>
    // Industry / first-job-date only make sense once the alumnus says
    // they're employed — keep them hidden otherwise, live as the select
    // changes during editing (not just on initial page load).
    function toggleEmploymentFields(status) {
        document.getElementById('employment-fields').classList.toggle('hidden', status !== '1');
    }

    function toggleWorkplaceUndisclosed() {
        const hiddenField = document.getElementById('alumnus_workplace_undisclosed');
        const workplaceInput = document.getElementById('alumnus_workplace');
        const label = document.getElementById('workplaceUndisclosedLabel');
        const undisclosed = hiddenField.value !== '1';

        hiddenField.value = undisclosed ? '1' : '0';
        workplaceInput.disabled = undisclosed;
        if (undisclosed) {
            workplaceInput.value = '';
        }
        label.textContent = undisclosed ? 'Disclose Workplace' : "Don't Disclose";
    }

    //user profile view image/upload image
    function togglePhotoOptions(event) {
        event.stopPropagation();
        const menu = document.getElementById('photoOptions');
        menu.classList.toggle('hidden');

    }

    window.addEventListener('click', function(e) {
        const menu = document.getElementById('photoOptions');
        if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });

    // Document "Remove"/"Replace"/"Upload" — all staged-until-Save, same as
    // everything else on this form (an industry change, etc.): nothing
    // actually uploads or deletes until the whole form is submitted. See
    // AlumnusController::updateAlumniProfile() for the remove_alumnus_*_file
    // flags stageDocumentRemoval() sets.
    const DOCUMENT_SLOTS = {
        resume: { flag: 'removeAlumnusResumeFileFlag', current: 'resumeFileCurrent', notice: 'resumeFileRemoveNotice', removeBtn: 'removeResumeFileBtn' },
        resumeBackup: { flag: 'removeAlumnusResumeBackupFileFlag', current: 'resumeBackupFileCurrent', notice: 'resumeBackupFileRemoveNotice', removeBtn: 'removeResumeBackupFileBtn' },
        coverLetter: { flag: 'removeAlumnusCoverLetterFileFlag', current: 'coverLetterFileCurrent', notice: 'coverLetterFileRemoveNotice', removeBtn: 'removeCoverLetterFileBtn' },
    };

    // Clears a staged "Remove" (flag + its notice banner + Remove button
    // visibility) WITHOUT touching slot.current's visibility — that's the
    // caller's call, since undoDocumentRemoval() and previewDocumentName()
    // each want a different outcome for it (see both below).
    function clearRemovalNotice(kind) {
        const slot = DOCUMENT_SLOTS[kind];
        document.getElementById(slot.flag).value = '0';
        document.getElementById(slot.notice).classList.add('hidden');
        document.getElementById(slot.notice).classList.remove('flex');
        document.getElementById(slot.removeBtn)?.classList.remove('hidden');
    }

    // Picking a file hides the current-state block ("No resume uploaded
    // yet" / the existing file) and shows the picked file as the single,
    // clear state instead — same treatment as the Remove notice.
    function previewDocumentName(input, targetId, kind) {
        const target = document.getElementById(targetId);
        const slot = DOCUMENT_SLOTS[kind];
        const file = input.files && input.files[0];

        // Picking a new file always wins over a pending "Remove" on the
        // same slot — same priority the server applies.
        clearRemovalNotice(kind);

        document.getElementById(slot.current).classList.toggle('hidden', !!file);
        target.textContent = file ? 'Selected: ' + file.name + ' — will be saved when you click Save' : '';
        target.classList.toggle('text-[#1D46A4]', !!file);
        target.classList.toggle('text-gray-500', !file);
    }

    function stageDocumentRemoval(kind) {
        const slot = DOCUMENT_SLOTS[kind];
        document.getElementById(slot.flag).value = '1';
        document.getElementById(slot.current).classList.add('hidden');
        document.getElementById(slot.notice).classList.remove('hidden');
        document.getElementById(slot.notice).classList.add('flex');
        document.getElementById(slot.removeBtn)?.classList.add('hidden');
    }

    function undoDocumentRemoval(kind) {
        clearRemovalNotice(kind);
        document.getElementById(DOCUMENT_SLOTS[kind].current).classList.remove('hidden');
    }

    // Landing here with ?openResume=1 (e.g. from the job apply modal's
    // "Create one now" link, or the Alumni Profile's resume prompt) opens
    // the Resume Builder modal automatically instead of leaving them to find
    // the button themselves.
    if (new URLSearchParams(window.location.search).get('openResume') === '1') {
        document.getElementById('openResumeEditorBtn')?.click();
    }

    // industry_id is required only while Employed is selected (see
    // toggleEmploymentFields() above). Enforced here with an explicit,
    // impossible-to-miss message instead of trusting the browser's native
    // bubble on a field that's sometimes hidden.
    (function () {
        var profileForm = document.getElementById('alumniProfileForm');
        var industrySelect = document.getElementById('industry_id');
        var errorBox = document.getElementById('clientValidationError');
        var errorText = document.getElementById('clientValidationErrorText');
        if (!profileForm || !industrySelect || !errorBox) return;

        function industryIsRequiredAndEmpty() {
            var visible = !document.getElementById('employment-fields').classList.contains('hidden');
            return visible && !industrySelect.value;
        }

        profileForm.addEventListener('submit', function (e) {
            if (!industryIsRequiredAndEmpty()) {
                errorBox.classList.add('hidden');
                return;
            }

            e.preventDefault();
            errorText.textContent = 'Please select your Industry / Sector before saving — it\'s required while your employment status is set to Employed.';
            errorBox.classList.remove('hidden');
            // The banner itself is fixed to the top of the viewport (always
            // visible) — this scroll is for the field itself, so the user
            // lands right on what to fix.
            industrySelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
            industrySelect.focus();
        });

        industrySelect.addEventListener('change', function () {
            if (!industryIsRequiredAndEmpty()) errorBox.classList.add('hidden');
        });
    })();
</script>

</html>
