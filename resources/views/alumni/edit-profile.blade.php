<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Edit Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/PLV-AlumNet LOGO.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<style>
    .HeroSection {
        background: url("{{ asset('assets/heroSection.svg') }}");
        background-size: cover;
        background-position: center;
    }
</style>

<body>
    @include('partials.header-alumni')

    <section class="HeroSection h-[200px] flex items-end text-white shadow-lg">
        <div class="max-w-6xl  w-full my-7 ml-10">
            <h1 class="text-5xl font-bold mb-2">Edit Alumni Profile</h1>
            <p class="text-xl font-light">PLV-AlumNet: Honoring the Past. Shaping the Future.</p>
        </div>
    </section>

    <main class="max-w-5xl mx-auto mt-10 mb-12 px-4">
        <form action="{{ route('alumni.updateProfile', $user->user_id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden p-8 md:p-12">
            @csrf
            @method('PUT')
            <h2 class="w-fit mx-auto text-center text-3xl font-bold mb-10 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">
                EDIT PROFILE
            </h2>
            @include('partials.error-toast')

            {{-- Client-side-only validation failures (e.g. Industry left
                 unset while Employed is selected) — see the form's submit
                 listener below, which toggles this. Fixed to the top of the
                 viewport (not inline in the form) since Save sits at the
                 bottom of a long page — an inline message up here would be
                 scrolled out of view exactly when it's needed. --}}
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
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <div class="md:col-span-3 flex justify-center md:justify-start">
                    <div class="relative w-40 h-40">

                        <div class="w-full h-full bg-[#0E0F3B] rounded-full flex items-center justify-center border-4 border-white shadow-lg overflow-hidden">
                            <img id="profileImagePreview"
                                src="{{ $user->user_profile_picture ? asset('storage/' . $user->user_profile_picture) : '' }}"
                                alt="Profile Picture"
                                class="w-full h-full object-cover {{ $user->user_profile_picture ? '' : 'hidden' }}"
                                style="{{ $user->user_profile_picture ? '' : 'display:none' }}">
                            <i id="profileImagePlaceholderIcon" class="fa-solid fa-user text-7xl text-white mt-4 {{ $user->user_profile_picture ? 'hidden' : '' }}"
                                style="{{ $user->user_profile_picture ? 'display:none' : '' }}"></i>

                        </div>

                        <div class="absolute bottom-1 right-1">
                            <button type="button" onclick="togglePhotoOptions(event)" class="bg-gray-600 text-white p-2 rounded-full border-2 border-white hover:bg-gray-800 transition z-10 shadow-md">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </button>

                            <div id="photoOptions" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50">
                                <button type="button" onclick="openImageLightbox(document.getElementById('profileImagePreview').src)" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:font-bold hover:bg-gray-100 flex items-center gap-3">
                                    <i class="fa-solid fa-image text-[#0E0F3B]"></i> View Profile Image
                                </button>
                                <label for="user_profile_picture" class="w-full text-left px-4 py-2 text-sm text-[#0E0F3B] hover:font-bold hover:bg-gray-100 flex items-center gap-3 cursor-pointer mb-0">
                                    <i class="fa-solid fa-upload text-[#0E0F3B]"></i> Upload an Image
                                    <input type="file" name="user_profile_picture" id="user_profile_picture" accept="image/*" class="hidden"
                                        onchange="previewImageInput(this, 'profileImagePreview', 'profileImagePlaceholderIcon')">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-9 space-y-4">
                    {{-- Row 1: identity fields, 4 across --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Last Name</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_last_name }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">First Name</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_first_name }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Middle Name</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_middle_name }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Suffix</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->user_suffix }}</h3>
                        </div>
                    </div>

                    {{-- Row 2: Gender and Batch --}}
                    <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-orange-600 uppercase">Gender</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">
                                {{ \App\Models\Alumnus::genderLabels()[$user->alumnus->alumnus_gender] ?? 'Not specified' }}
                            </h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-orange-600 uppercase">Batch</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate">{{ $user->alumnus->alumnus_batch?->format('Y') ?? 'Not specified' }}</h3>
                        </div>
                    </div>

                    {{-- Row 3: Program and College --}}
                    <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-orange-600 uppercase">Program</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate"
                                title="{{ $user->alumnus->program->program_name ?? 'Not specified' }}">
                                {{ $user->alumnus->program->program_name ?? 'Not specified' }}</h3>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-orange-600 uppercase">College</p>
                            <h3 class="text-sm font-semibold text-[#0E0F3B] uppercase truncate"
                                title="{{ $user->alumnus->program?->collegeName() ?? 'Not specified' }}">
                                {{ $user->alumnus->program?->collegeName() ?? 'Not specified' }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="alumnus_employment_status" class="text-xs font-bold text-orange-600 uppercase block mb-1">Employment Status</label>
                        <select name="alumnus_employment_status" id="alumnus_employment_status" onchange="toggleEmploymentFields(this.value)" class="w-full py-1.5 px-2 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                            <option value="1" {{ $user->alumnus->alumnus_employment_status == 1 ? 'selected' : '' }}>Employed</option>
                            <option value="0" {{ $user->alumnus->alumnus_employment_status == 0 ? 'selected' : '' }}>Unemployed</option>
                        </select>
                    </div>
                    <div>
                        <label for="user_email" class="text-xs font-bold text-orange-600 uppercase block mb-1">Email</label>
                        <input type="email" name="user_email" placeholder="example@email.com" value="{{ $user->user_email }}" class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                    </div>
                </div>

                {{-- Only relevant once "Employed" is picked above — laid out as a
                     neat 2-column grid of its own instead of one long stacked
                     column, so it doesn't dominate the page when expanded. --}}
                <div id="employment-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ $user->alumnus->alumnus_employment_status ? '' : 'hidden' }}">
                    <div>
                        <label for="industry_id" class="text-xs font-bold text-orange-600 uppercase block mb-1">Industry / Sector</label>
                        {{-- Not a native `required` attribute — a required-but-hidden-by-default
                             field like this is exactly the case where the
                             browser's own validation bubble can silently fail
                             to appear (wrong scroll position, a hidden
                             ancestor at some point in the toggle, etc.), which
                             is what made Save look like it did nothing. The
                             submit listener below enforces this explicitly
                             and always shows a visible reason instead. --}}
                        <select name="industry_id" id="industry_id" class="w-full py-1.5 px-2 border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                            <option value="" disabled {{ $user->alumnus->industry_id ? '' : 'selected' }}>Select Industry / Sector</option>
                            @foreach($industries as $industry)
                            <option value="{{ $industry->industry_id }}" {{ $user->alumnus->industry_id == $industry->industry_id ? 'selected' : '' }}>
                                {{ $industry->industry_name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — change it yourself if your job didn't come from the system.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="alumnus_workplace" class="text-xs font-bold text-orange-600 uppercase">Where Do You Work?</label>
                            <button type="button" id="workplaceUndisclosedBtn" onclick="toggleWorkplaceUndisclosed()"
                                class="text-[10px] font-bold uppercase px-2 py-1 rounded border border-[#0E0F3B] text-[#0E0F3B] hover:bg-[#0E0F3B] hover:text-white transition shrink-0">
                                <span id="workplaceUndisclosedLabel">{{ $user->alumnus->alumnus_workplace_undisclosed ? 'Disclose Workplace' : "Don't Disclose" }}</span>
                            </button>
                        </div>
                        <input type="hidden" name="alumnus_workplace_undisclosed" id="alumnus_workplace_undisclosed" value="{{ $user->alumnus->alumnus_workplace_undisclosed ? 1 : 0 }}">
                        <input type="text" name="alumnus_workplace" id="alumnus_workplace" placeholder="Company / Organization name"
                            value="{{ $user->alumnus->alumnus_workplace }}"
                            {{ $user->alumnus->alumnus_workplace_undisclosed ? 'disabled' : '' }}
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — only fill this in yourself if your job didn't come from the system. Optional, and you can mark it undisclosed and just leave your industry above set.
                        </p>
                    </div>

                    <div>
                        <label for="alumnus_job_position" class="text-xs font-bold text-orange-600 uppercase block mb-1">Job Position</label>
                        @if($user->alumnus->alumnus_employed_via_platform)
                        <input type="text" value="{{ $user->alumnus->alumnus_job_position }}" disabled
                            class="w-full border border-[#0E0F3B] rounded-md p-2 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set from the job posting you were hired through here — not editable while that's your current job.
                        </p>
                        @else
                        <input type="text" name="alumnus_job_position" id="alumnus_job_position" placeholder="e.g. Software Engineer"
                            value="{{ $user->alumnus->alumnus_job_position }}"
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — fill this in yourself if your job didn't come from the system.
                        </p>
                        @endif
                    </div>

                    <div>
                        <label for="alumnus_employment_date" class="text-xs font-bold text-orange-600 uppercase block mb-1">Employment Date</label>
                        @if($user->alumnus->alumnus_employed_via_platform)
                        <input type="date" value="{{ optional($user->alumnus->alumnus_employment_date)->format('Y-m-d') }}" disabled
                            class="w-full border border-[#0E0F3B] rounded-md p-2 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set when you were hired through this job post — not editable while that's your current job.
                        </p>
                        @else
                        <input type="date" name="alumnus_employment_date" id="alumnus_employment_date" max="{{ now()->format('Y-m-d') }}"
                            value="{{ optional($user->alumnus->alumnus_employment_date)->format('Y-m-d') }}"
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                        <p class="text-[10px] text-gray-400 mt-1">
                            When you started your current job — not the same as your date of first job below. Set automatically when hired through a job post here.
                        </p>
                        @endif
                    </div>

                    <div>
                        <label for="alumnus_first_job_date" class="text-xs font-bold text-orange-600 uppercase block mb-1">Date of First Job</label>
                        @if($user->alumnus->alumnus_first_job_date)
                        <input type="date" value="{{ $user->alumnus->alumnus_first_job_date->format('Y-m-d') }}" disabled
                            class="w-full border border-[#0E0F3B] rounded-md p-2 bg-gray-100 text-gray-400 cursor-not-allowed">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Already recorded — this can only be set once.
                        </p>
                        @if($user->alumnus->alumnus_first_job_is_internship)
                        <p class="text-[10px] font-bold text-orange-600 mt-1">
                            <i class="fas fa-check-circle"></i> Recorded as an internship.
                        </p>
                        @endif
                        @else
                        <input type="date" name="alumnus_first_job_date" id="alumnus_first_job_date" max="{{ now()->format('Y-m-d') }}"
                            class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                        <p class="text-[10px] text-gray-400 mt-1">
                            Set automatically when you're hired through a job post here — only fill this in yourself if your first job didn't come from the system. You can only set this once.
                        </p>
                        <label class="flex items-center gap-2 mt-2 text-xs text-[#0E0F3B]">
                            <input type="checkbox" name="alumnus_first_job_is_internship" value="1" class="rounded border-[#0E0F3B]">
                            This was an internship / OJT, not a regular job.
                        </label>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="user_number" class="text-xs font-bold text-orange-600 uppercase block mb-1">Contact No.</label>
                        <input type="text" name="user_number" placeholder="09XXXXXXXXX" value="{{ $user->user_number }}" class="w-full border border-[#0E0F3B] rounded-md p-2 focus:outline-none focus:border-[#C73D1A] transition">
                    </div>
                    <div>
                        {{-- Skills are managed via the chip-search UI in the Resume
                             section below (a many-to-many relation, not a plain
                             field this form posts) — shown read-only here just to
                             mirror the mockup's layout, same treatment as
                             Program/Batch above. --}}
                        <p class="text-xs font-bold text-orange-600 uppercase block mb-1">Skills</p>
                        <p class="w-full border border-gray-200 bg-gray-50 rounded-md p-2 text-sm text-[#0E0F3B] min-h-[38px]">
                            {{ $user->alumnus->skills->pluck('skill_name')->join(', ') ?: 'Add skills from the Resume section below' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between md:w-1/2">
                    <span class="text-xs font-bold text-orange-600 uppercase">Resume</span>
                    <button type="button" id="openResumeEditorBtn"
                        class="bg-[#1D46A4] hover:bg-gradient-to-t from-[#0E0F3B] to-[#1D46A4] text-white text-xs font-bold py-2 px-8 rounded shadow-md transition duration-200 uppercase w-44">
                        {{ $user->alumnus->isResumeComplete() ? 'Edit Resume' : 'Create Resume' }}
                    </button>
                </div>

                {{-- Documents — own uploaded Resume/CV and an optional Cover
                     Letter, distinct from the Resume Builder above (which
                     generates a PDF from profile data). These are the actual
                     files offered as "Use my AlumNet Profile" when applying
                     to a job — see partials/job-apply-modal.blade.php. --}}
                <div class="border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-1">Documents</h3>
                    <p class="text-xs text-gray-400 mb-5">
                        Upload your own Resume/CV and an optional Cover Letter. These can be submitted straight to employers when you apply for a job.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-[#1D264F] uppercase block mb-2">Resume / CV</label>
                            <div class="flex items-center justify-between gap-3 border border-gray-200 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <i class="fas fa-file-lines text-[#C73D1A] text-xl shrink-0"></i>
                                    <div class="min-w-0">
                                        <div id="resumeFileCurrent">
                                            @if ($user->alumnus->alumnus_resume_file_path)
                                            <a href="{{ asset('storage/' . $user->alumnus->alumnus_resume_file_path) }}" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate block">
                                                {{ basename($user->alumnus->alumnus_resume_file_path) }}
                                            </a>
                                            @else
                                            <p class="text-sm text-gray-400">No resume uploaded yet</p>
                                            @endif
                                        </div>
                                        <div id="resumeFileRemoveNotice" class="hidden items-center gap-2">
                                            <p class="text-sm text-red-600 font-semibold">Will be removed when you save</p>
                                            <button type="button" onclick="undoDocumentRemoval('resume')" class="text-[10px] font-bold text-[#1D46A4] hover:underline">Undo</button>
                                        </div>
                                        <p id="resumeFileSelected" class="text-[11px] text-gray-500 font-semibold"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" onclick="document.getElementById('alumnusResumeFileInput').click()"
                                        class="shrink-0 bg-[#1D264F] text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-[#0E0F3B] transition-colors">
                                        {{ $user->alumnus->alumnus_resume_file_path ? 'Replace' : 'Upload' }}
                                    </button>
                                    @if ($user->alumnus->alumnus_resume_file_path)
                                    <button type="button" id="removeResumeFileBtn" onclick="stageDocumentRemoval('resume')"
                                        class="shrink-0 border border-red-300 text-red-600 text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-red-50 transition-colors">
                                        Remove
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="remove_alumnus_resume_file" id="removeAlumnusResumeFileFlag" value="0">
                            <input type="file" name="alumnus_resume_file" id="alumnusResumeFileInput" accept=".pdf,.doc,.docx" class="hidden" onchange="previewDocumentName(this, 'resumeFileSelected', 'resume')">
                            <p class="text-[10px] text-gray-400 mt-1">PDF, DOC, or DOCX — up to 5MB.</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-[#1D264F] uppercase block mb-2">Backup Resume / CV <span class="normal-case text-gray-400 font-normal">(optional)</span></label>
                            <div class="flex items-center justify-between gap-3 border border-gray-200 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <i class="fas fa-file-shield text-[#C73D1A] text-xl shrink-0"></i>
                                    <div class="min-w-0">
                                        <div id="resumeBackupFileCurrent">
                                            @if ($user->alumnus->alumnus_resume_backup_file_path)
                                            <a href="{{ asset('storage/' . $user->alumnus->alumnus_resume_backup_file_path) }}" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate block">
                                                {{ basename($user->alumnus->alumnus_resume_backup_file_path) }}
                                            </a>
                                            @else
                                            <p class="text-sm text-gray-400">No backup resume uploaded yet</p>
                                            @endif
                                        </div>
                                        <div id="resumeBackupFileRemoveNotice" class="hidden items-center gap-2">
                                            <p class="text-sm text-red-600 font-semibold">Will be removed when you save</p>
                                            <button type="button" onclick="undoDocumentRemoval('resumeBackup')" class="text-[10px] font-bold text-[#1D46A4] hover:underline">Undo</button>
                                        </div>
                                        <p id="resumeBackupFileSelected" class="text-[11px] text-gray-500 font-semibold"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" onclick="document.getElementById('alumnusResumeBackupFileInput').click()"
                                        class="shrink-0 bg-[#1D264F] text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-[#0E0F3B] transition-colors">
                                        {{ $user->alumnus->alumnus_resume_backup_file_path ? 'Replace' : 'Upload' }}
                                    </button>
                                    @if ($user->alumnus->alumnus_resume_backup_file_path)
                                    <button type="button" id="removeResumeBackupFileBtn" onclick="stageDocumentRemoval('resumeBackup')"
                                        class="shrink-0 border border-red-300 text-red-600 text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-red-50 transition-colors">
                                        Remove
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="remove_alumnus_resume_backup_file" id="removeAlumnusResumeBackupFileFlag" value="0">
                            <input type="file" name="alumnus_resume_backup_file" id="alumnusResumeBackupFileInput" accept=".pdf,.doc,.docx" class="hidden" onchange="previewDocumentName(this, 'resumeBackupFileSelected', 'resumeBackup')">
                            <p class="text-[10px] text-gray-400 mt-1">A spare copy in case your primary resume is unavailable — PDF, DOC, or DOCX, up to 5MB.</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-[#1D264F] uppercase block mb-2">Cover Letter <span class="normal-case text-gray-400 font-normal">(optional)</span></label>
                            <div class="flex items-center justify-between gap-3 border border-gray-200 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <i class="fas fa-envelope-open-text text-[#C73D1A] text-xl shrink-0"></i>
                                    <div class="min-w-0">
                                        <div id="coverLetterFileCurrent">
                                            @if ($user->alumnus->alumnus_cover_letter_file_path)
                                            <a href="{{ asset('storage/' . $user->alumnus->alumnus_cover_letter_file_path) }}" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate block">
                                                {{ basename($user->alumnus->alumnus_cover_letter_file_path) }}
                                            </a>
                                            @else
                                            <p class="text-sm text-gray-400">No cover letter uploaded yet</p>
                                            @endif
                                        </div>
                                        <div id="coverLetterFileRemoveNotice" class="hidden items-center gap-2">
                                            <p class="text-sm text-red-600 font-semibold">Will be removed when you save</p>
                                            <button type="button" onclick="undoDocumentRemoval('coverLetter')" class="text-[10px] font-bold text-[#1D46A4] hover:underline">Undo</button>
                                        </div>
                                        <p id="coverLetterFileSelected" class="text-[11px] text-gray-500 font-semibold"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" onclick="document.getElementById('alumnusCoverLetterFileInput').click()"
                                        class="shrink-0 bg-[#1D264F] text-white text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-[#0E0F3B] transition-colors">
                                        {{ $user->alumnus->alumnus_cover_letter_file_path ? 'Replace' : 'Upload' }}
                                    </button>
                                    @if ($user->alumnus->alumnus_cover_letter_file_path)
                                    <button type="button" id="removeCoverLetterFileBtn" onclick="stageDocumentRemoval('coverLetter')"
                                        class="shrink-0 border border-red-300 text-red-600 text-[10px] font-bold px-4 py-2 rounded-full uppercase hover:bg-red-50 transition-colors">
                                        Remove
                                    </button>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="remove_alumnus_cover_letter_file" id="removeAlumnusCoverLetterFileFlag" value="0">
                            <input type="file" name="alumnus_cover_letter_file" id="alumnusCoverLetterFileInput" accept=".pdf,.doc,.docx" class="hidden" onchange="previewDocumentName(this, 'coverLetterFileSelected', 'coverLetter')">
                            <p class="text-[10px] text-gray-400 mt-1">PDF, DOC, or DOCX — up to 5MB.</p>
                        </div>
                    </div>
                </div>

                {{-- Profile Settings — per-field directory visibility. Purely a
                     display-layer toggle on the alumni.updateProfile form; it
                     doesn't touch the underlying skills/email/linkedin data,
                     just whether the Alumni Directory's "View Profile" modal
                     shows them to other alumni. --}}
                <div class="border border-gray-200 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-wide mb-1">Profile Settings</h3>
                    <p class="text-xs text-gray-400 mb-5">
                        Choose what other alumni can see on your profile in the Alumni Directory. Your information is never shown to employers, and staff can always see it.
                    </p>

                    <div class="divide-y divide-gray-100">
                        <div class="flex items-center justify-between py-3">
                            <div class="pr-4">
                                <p class="text-sm font-semibold text-[#0E0F3B]">Show Skills</p>
                                <p class="text-xs text-gray-400">Let other alumni see the skills listed on your resume.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="alumnus_show_skills" value="1"
                                    {{ old('alumnus_show_skills', $user->alumnus->alumnus_show_skills) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0E0F3B] transition-colors
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full relative"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between py-3">
                            <div class="pr-4">
                                <p class="text-sm font-semibold text-[#0E0F3B]">Show Email</p>
                                <p class="text-xs text-gray-400">Let other alumni see your email address.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="alumnus_show_email" value="1"
                                    {{ old('alumnus_show_email', $user->alumnus->alumnus_show_email) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0E0F3B] transition-colors
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full relative"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between py-3">
                            <div class="pr-4">
                                <p class="text-sm font-semibold text-[#0E0F3B]">Show LinkedIn</p>
                                <p class="text-xs text-gray-400">Let other alumni see your LinkedIn profile link.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="alumnus_show_linkedin" value="1"
                                    {{ old('alumnus_show_linkedin', $user->alumnus->alumnus_show_linkedin) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0E0F3B] transition-colors
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full relative"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12">
                {{-- Was a plain <button> with no type — inside a <form>, that
                     defaults to type="submit", so Cancel was actually submitting
                     the (unchanged) profile form and showing the "Profile
                     updated successfully!" message. An <a> tag can't submit a
                     form, so it can't regress the same way. --}}
                <a href="{{ route('user.profile') }}"
                    class="px-10 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold rounded-lg transition-all duration-200 uppercase tracking-widest text-sm hover:bg-[#0E0F3B] hover:text-white active:scale-95 inline-flex items-center justify-center">
                    Cancel
                </a>
                <button type="submit" class="px-10 py-2 bg-[#0E0F3B] text-white font-bold rounded-lg hover:bg-[#1D46A4] transition uppercase tracking-widest text-sm shadow-lg">
                    Save
                </button>
            </div>

        </form>
    </main>

    @if($user->alumnus->isResumeComplete())
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

    // Was just a small "Selected: x.pdf" line added BELOW the untouched
    // "No resume uploaded yet" / existing-file text — so right after
    // picking a file it looked like nothing had happened, since the stale
    // placeholder was still the most prominent thing on screen. Now hides
    // that current-state block entirely and shows the picked file as the
    // clear, singular state instead, same treatment as the Remove notice.
    //
    // Bug fixed here: this used to call undoDocumentRemoval(kind) to clear
    // a pending Remove, but that function also unconditionally re-shows
    // slot.current — undoing the .toggle('hidden', true) two lines above
    // in the same tick, so "No resume uploaded yet" and "Selected: ..."
    // both stayed visible together. clearRemovalNotice() does the same
    // flag/notice cleanup without touching slot.current's visibility,
    // which is decided by `file` right below instead.
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
    // "Create one now" link, when an alumnus has no resume yet) opens the
    // Resume Builder modal automatically instead of leaving them to find
    // the button themselves.
    if (new URLSearchParams(window.location.search).get('openResume') === '1') {
        document.getElementById('openResumeEditorBtn')?.click();
    }

    // industry_id is the only `required` field on this form, and it's
    // required only while Employed is selected (see toggleEmploymentFields()
    // above). The browser's own native "please fill this in" bubble should
    // block submission on its own, but it gives no cue at all once the
    // click just silently does nothing — a scroll position, a focus quirk,
    // anything, and there's zero feedback. Backstop it with an explicit,
    // impossible-to-miss check instead of trusting that native UI alone.
    (function () {
        var profileForm = document.querySelector('form[action*="alumni"]');
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
            // visible, scroll position doesn't matter) — this scroll is for
            // the field itself, so the user lands right on what to fix.
            industrySelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
            industrySelect.focus();
        });

        industrySelect.addEventListener('change', function () {
            if (!industryIsRequiredAndEmpty()) errorBox.classList.add('hidden');
        });
    })();
</script>

</html>