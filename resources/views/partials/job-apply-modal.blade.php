{{--
    "Apply for this Job" review modal — a real 2-step flow. Shared by every
    Apply trigger on the job board (job-post-card, job-detail-modal) so
    there's exactly one place this flow lives.

    Step 1 (#applyStep1): pick a resume source (uploaded profile file /
    one-off upload / Resume Builder profile) and a cover letter source.
    Step 2 (#applyStep2): review what will actually be sent — profile
    picture/name/contact/email/LinkedIn in one block, then the resume (a
    file link, or — if Resume Builder was chosen — a fully editable inline
    copy of it), then the cover letter link if any, then Submit. Both steps
    live in the same <form> the whole time (just toggled via .hidden) so
    the file inputs picked in Step 1 are still present when the form
    actually submits from Step 2.

    The Resume Builder option's Step-2 editor is a genuine scratch copy:
    edits here are collected into one JSON blob (builder_resume_snapshot_json,
    decoded server-side in JobApplicationController::applyJob()) and never
    touch the alumnus's real alumni/experiences/skills rows — editing a
    skill or experience here has zero effect on their saved profile.

    openApplyModal(jobId, hasUploadedResumeFile, hasBuilderResume, hasProfileCoverLetter)
    is called from every Apply button/card — the three booleans come from
    Alumnus::hasUploadedResumeFile()/hasBuilderResume() and
    !empty($alumnus->alumnus_cover_letter_file_path).
--}}
@php
    $applyAlumnus = $user->alumnus ?? null;
    $applyResumeData = $applyAlumnus ? $applyAlumnus->toResumeFormArray() : null;
    $certTypeLabels = \App\Models\Alumnus::certificationTypeLabels();
@endphp
<div id="jobApplyModal" class="fixed inset-0 z-[130] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">

    <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl relative my-8 max-h-[90vh] overflow-y-auto">

        <form id="jobApplyForm" method="POST" action="" enctype="multipart/form-data">
            @csrf

            <button type="button" onclick="closeApplyModal()" class="absolute top-6 right-6 text-gray-300 hover:text-gray-500 transition-colors z-10">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>

            <div class="pt-10 px-8 md:px-10 text-center">
                <h2 id="applyModalHeading" class="inline-block text-2xl md:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">
                    REVIEW YOUR APPLICATION
                </h2>
                <p id="applyModalSubheading" class="text-gray-500 text-sm mt-1">Choose the documents to submit for this job, then confirm below.</p>
            </div>

            {{-- ══════════ STEP 1 — pick resume / cover letter ══════════ --}}
            <div id="applyStep1" class="p-8 md:px-10 pt-6 space-y-6">

                <!-- RESUME / CV -->
                <div>
                    <label class="text-[10px] font-bold text-[#1D264F] uppercase block mb-2">Resume / CV <span class="text-red-500">*</span></label>

                    <label id="applyResumeProfileOption" class="flex items-start gap-3 border rounded-xl p-4 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50">
                        <span class="relative flex items-center justify-center w-4 h-4 shrink-0 mt-1">
                            <input type="radio" name="resume_source" value="profile" class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                            <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-[#0E0F3B]">Use my uploaded resume file</span>
                            <span id="applyResumeProfileHint" class="block text-xs text-gray-500 mt-0.5"></span>
                        </span>
                    </label>

                    <label id="applyResumeBuilderOption" class="flex items-start gap-3 border rounded-xl p-4 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50 mt-2">
                        <span class="relative flex items-center justify-center w-4 h-4 shrink-0 mt-1">
                            <input type="radio" name="resume_source" value="builder" class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                            <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-[#0E0F3B]">Use my Resume Builder resume</span>
                            <span id="applyResumeBuilderHint" class="block text-xs text-gray-500 mt-0.5">You'll be able to tweak it just for this application in the next step — it won't change your saved profile.</span>
                        </span>
                    </label>

                    <div id="applyNoResumePrompt" class="hidden mt-2 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl p-3">
                        You don't have a resume on file yet.
                        <a href="{{ route('users.editProfile') }}?openResume=1" target="_blank" class="font-bold underline">Create one now</a>
                        or upload one for this job below.
                    </div>

                    <label class="flex items-start gap-3 border rounded-xl p-4 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50 mt-2">
                        <span class="relative flex items-center justify-center w-4 h-4 shrink-0 mt-1">
                            <input type="radio" name="resume_source" value="upload" class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                            <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                        </span>
                        <span class="flex-1">
                            <span class="block text-sm font-bold text-[#0E0F3B]">Upload a resume for this job only</span>
                            <span class="block text-xs text-gray-500 mt-0.5">Won't be saved to your profile.</span>
                        </span>
                    </label>

                    <div id="applyResumeUploadField" class="overflow-hidden max-h-0 transition-all duration-300 ease-in-out">
                        <div class="pt-3">
                            <div class="relative w-full h-24 rounded-lg overflow-hidden border-2 border-dashed border-[#0E0F3B] bg-slate-100 flex items-center justify-center transition-colors"
                                ondragover="handleDropzoneDragOver(event)" ondragleave="handleDropzoneDragLeave(event)" ondrop="handleDropzoneDrop(event, 'applyResumeFileInput')">
                                <div id="applyResumeUploadPlaceholder" class="text-slate-400 text-xs flex flex-col items-center gap-1 pointer-events-none">
                                    <i class="fas fa-cloud-arrow-up text-2xl text-[#C73D1A]"></i>
                                    <span>Drag & drop, or click Upload</span>
                                </div>
                                <p id="applyResumeFileName" class="hidden text-xs font-semibold text-[#0E0F3B] px-6 text-center truncate w-full"></p>
                                <input type="file" name="resume_file" id="applyResumeFileInput" accept=".pdf,.doc,.docx" class="hidden"
                                    onchange="previewApplyDocument(this, 'applyResumeUploadPlaceholder', 'applyResumeFileName'); updateApplyModalState()">
                                <button type="button" onclick="document.getElementById('applyResumeFileInput').click()"
                                    class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-[#0E0F3B] text-[10px] font-bold px-3 py-1.5 rounded-full shadow uppercase">
                                    Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COVER LETTER -->
                <div>
                    <label class="text-[10px] font-bold text-[#1D264F] uppercase block mb-2">Cover Letter <span class="normal-case text-gray-400 font-normal">(optional)</span></label>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 border rounded-xl p-3 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50">
                            <span class="relative flex items-center justify-center w-4 h-4 shrink-0">
                                <input type="radio" name="cover_letter_source" value="none" checked class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                                <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                            </span>
                            <span class="text-xs font-semibold text-[#0E0F3B]">Don't include</span>
                        </label>
                        <label id="applyCoverLetterProfileOption" class="hidden items-center gap-2 border rounded-xl p-3 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50">
                            <span class="relative flex items-center justify-center w-4 h-4 shrink-0">
                                <input type="radio" name="cover_letter_source" value="profile" class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                                <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                            </span>
                            <span class="text-xs font-semibold text-[#0E0F3B]">Use my saved one</span>
                        </label>
                        <label class="flex items-center gap-2 border rounded-xl p-3 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50">
                            <span class="relative flex items-center justify-center w-4 h-4 shrink-0">
                                <input type="radio" name="cover_letter_source" value="upload" class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                                <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                            </span>
                            <span class="text-xs font-semibold text-[#0E0F3B]">Upload one</span>
                        </label>
                    </div>

                    <div id="applyCoverLetterUploadField" class="overflow-hidden max-h-0 transition-all duration-300 ease-in-out">
                        <div class="pt-3">
                            <div class="relative w-full h-24 rounded-lg overflow-hidden border-2 border-dashed border-[#0E0F3B] bg-slate-100 flex items-center justify-center transition-colors"
                                ondragover="handleDropzoneDragOver(event)" ondragleave="handleDropzoneDragLeave(event)" ondrop="handleDropzoneDrop(event, 'applyCoverLetterFileInput')">
                                <div id="applyCoverLetterUploadPlaceholder" class="text-slate-400 text-xs flex flex-col items-center gap-1 pointer-events-none">
                                    <i class="fas fa-cloud-arrow-up text-2xl text-[#C73D1A]"></i>
                                    <span>Drag & drop, or click Upload</span>
                                </div>
                                <p id="applyCoverLetterFileName" class="hidden text-xs font-semibold text-[#0E0F3B] px-6 text-center truncate w-full"></p>
                                <input type="file" name="cover_letter_file" id="applyCoverLetterFileInput" accept=".pdf,.doc,.docx" class="hidden"
                                    onchange="previewApplyDocument(this, 'applyCoverLetterUploadPlaceholder', 'applyCoverLetterFileName'); updateApplyModalState()">
                                <button type="button" onclick="document.getElementById('applyCoverLetterFileInput').click()"
                                    class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-[#0E0F3B] text-[10px] font-bold px-3 py-1.5 rounded-full shadow uppercase">
                                    Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="applyModalErrors" class="hidden flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 shadow-sm">
                    <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                    <p id="applyModalErrorText" class="text-xs"></p>
                </div>

                <div class="flex justify-end gap-4 pt-2">
                    <button type="button" onclick="closeApplyModal()" class="px-8 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-md font-bold text-sm hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        CANCEL
                    </button>
                    <button type="button" id="applyNextBtn" onclick="goToApplyStep2()" disabled
                        class="px-10 py-2 bg-gray-300 text-white rounded-md font-bold text-sm cursor-not-allowed transition-colors">
                        NEXT
                    </button>
                </div>
            </div>

            {{-- ══════════ STEP 2 — review what will be sent ══════════ --}}
            <div id="applyStep2" class="hidden p-8 md:px-10 pt-6 space-y-6">

                <!-- PROFILE INFO (one container, per request) -->
                <div class="border border-gray-200 rounded-xl p-4 flex items-center gap-4">
                    <img id="applyReviewPicture" src="" class="w-16 h-16 rounded-full object-cover bg-slate-200 shrink-0" alt="">
                    <div class="min-w-0">
                        <p id="applyReviewName" class="text-sm font-bold text-[#0E0F3B] truncate"></p>
                        <p id="applyReviewContact" class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone w-4"></i> <span></span></p>
                        <p id="applyReviewEmail" class="text-xs text-gray-500"><i class="fas fa-envelope w-4"></i> <span></span></p>
                        <p id="applyReviewLinkedin" class="text-xs text-gray-500"><i class="fab fa-linkedin w-4"></i> <a href="#" target="_blank" class="text-[#1D46A4] hover:underline truncate"></a></p>
                    </div>
                </div>

                <!-- RESUME / CV REVIEW -->
                <div>
                    <label class="text-[10px] font-bold text-[#1D264F] uppercase block mb-2">Resume / CV</label>
                    <div id="applyResumeReviewContent"></div>
                </div>

                <!-- COVER LETTER REVIEW -->
                <div id="applyCoverLetterReviewWrap" class="hidden">
                    <label class="text-[10px] font-bold text-[#1D264F] uppercase block mb-2">Cover Letter</label>
                    <div id="applyCoverLetterReviewContent"></div>
                </div>

                <input type="hidden" name="builder_resume_snapshot_json" id="applyBuilderSnapshotInput">

                <div id="applyStep2Errors" class="hidden flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 shadow-sm">
                    <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                    <p id="applyStep2ErrorText" class="text-xs"></p>
                </div>

                <div class="flex justify-end gap-4 pt-2">
                    <button type="button" onclick="backToApplyStep1()" class="px-8 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-md font-bold text-sm hover:bg-[#0E0F3B] hover:text-white transition-colors">
                        BACK
                    </button>
                    <button type="button" onclick="handleApplySubmit()" class="px-10 py-2 bg-[#0E0F3B] hover:bg-blue-900 text-white rounded-md font-bold text-sm transition-colors">
                        SUBMIT APPLICATION
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Template for one editable skill chip in the Resume Builder review editor — cloned by JS, never rendered directly. --}}
<template id="applySkillChipTemplate">
    <span class="apply-skill-chip inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-[#1D46A4] text-xs font-semibold px-3 py-1.5 rounded-full">
        <span class="apply-skill-name"></span>
        <button type="button" class="text-blue-400 hover:text-red-500" onclick="this.closest('.apply-skill-chip').remove()">
            <i class="fas fa-times"></i>
        </button>
    </span>
</template>

{{-- Template for one editable experience row — cloned by JS. --}}
<template id="applyExperienceRowTemplate">
    <div class="apply-exp-row border border-gray-200 rounded-lg p-3 space-y-2 relative">
        <button type="button" class="absolute top-2 right-2 text-gray-300 hover:text-red-500" onclick="this.closest('.apply-exp-row').remove()">
            <i class="fas fa-times-circle"></i>
        </button>
        <div class="flex gap-2 pr-6">
            <select class="apply-exp-type text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                <option value="work">Work</option>
                <option value="project">Project</option>
            </select>
            <input type="text" placeholder="Job/Project title" class="apply-exp-title flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
            <input type="number" min="0" max="600" placeholder="Months" class="apply-exp-duration w-20 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
        </div>
        <textarea rows="2" placeholder="Description (optional)" class="apply-exp-desc w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5"></textarea>
    </div>
</template>

{{-- Template for one editable certification row — cloned by JS. --}}
<template id="applyCertRowTemplate">
    <div class="apply-cert-row border border-gray-200 rounded-lg p-3 space-y-2 relative">
        <button type="button" class="absolute top-2 right-2 text-gray-300 hover:text-red-500" onclick="this.closest('.apply-cert-row').remove()">
            <i class="fas fa-times-circle"></i>
        </button>
        <div class="flex gap-2 pr-6">
            <select class="apply-cert-type text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                @foreach ($certTypeLabels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" placeholder="Name" class="apply-cert-name flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
        </div>
        <div class="flex gap-2">
            <input type="text" placeholder="From (issuer)" class="apply-cert-from flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
            <input type="date" class="apply-cert-date text-xs border border-gray-200 rounded-lg px-2 py-1.5">
        </div>
    </div>
</template>

<script>
    const JOB_APPLY_URL_TMPL = @json(route('jobApplication.apply', 999999999));
    let applyModalHasUploadedResumeFile = false;
    let applyModalHasBuilderResume = false;

    // Static per-page data for the alumnus currently logged in — the same
    // person applies to every job on this page, so this is embedded once
    // here rather than re-passed through every single openApplyModal() call.
    const APPLY_PROFILE = {
        picture: @json($user->user_profile_picture ? asset('storage/' . $user->user_profile_picture) : null),
        name: @json($applyAlumnus ? $applyAlumnus->resumeFullName() : trim(($user->user_first_name ?? '') . ' ' . ($user->user_last_name ?? ''))),
        contact: @json($user->user_number ?? ''),
        email: @json($user->user_email ?? ''),
        linkedin: @json($applyAlumnus->linkedin_url ?? null),
        uploadedResumeUrl: @json(($applyAlumnus && $applyAlumnus->alumnus_resume_file_path) ? asset('storage/' . $applyAlumnus->alumnus_resume_file_path) : null),
        uploadedCoverLetterUrl: @json(($applyAlumnus && $applyAlumnus->alumnus_cover_letter_file_path) ? asset('storage/' . $applyAlumnus->alumnus_cover_letter_file_path) : null),
    };
    const APPLY_BUILDER_RESUME = @json($applyResumeData);
    const APPLY_CERT_TYPE_LABELS = @json($certTypeLabels);

    function openApplyModal(jobId, hasUploadedResumeFile, hasBuilderResume, hasProfileCoverLetter) {
        document.getElementById('jobApplyForm').action = JOB_APPLY_URL_TMPL.replace('999999999', jobId);
        applyModalHasUploadedResumeFile = !!hasUploadedResumeFile;
        applyModalHasBuilderResume = !!hasBuilderResume;

        // Reset state from any previous job's selections.
        document.querySelectorAll('#jobApplyForm input[type=radio]').forEach(r => r.checked = false);
        document.getElementById('applyResumeFileInput').value = '';
        document.getElementById('applyCoverLetterFileInput').value = '';
        resetApplyDocumentPreview('applyResumeUploadPlaceholder', 'applyResumeFileName');
        resetApplyDocumentPreview('applyCoverLetterUploadPlaceholder', 'applyCoverLetterFileName');
        document.querySelector('#jobApplyForm input[name=cover_letter_source][value=none]').checked = true;

        const profileResumeRadio = document.querySelector('#jobApplyForm input[name=resume_source][value=profile]');
        const profileResumeOption = document.getElementById('applyResumeProfileOption');
        const builderResumeRadio = document.querySelector('#jobApplyForm input[name=resume_source][value=builder]');
        const builderResumeOption = document.getElementById('applyResumeBuilderOption');
        const noResumePrompt = document.getElementById('applyNoResumePrompt');
        const resumeHint = document.getElementById('applyResumeProfileHint');

        profileResumeRadio.disabled = !applyModalHasUploadedResumeFile;
        profileResumeOption.classList.toggle('opacity-50', !applyModalHasUploadedResumeFile);
        profileResumeOption.classList.toggle('cursor-not-allowed', !applyModalHasUploadedResumeFile);
        resumeHint.textContent = applyModalHasUploadedResumeFile ? "We'll send the resume/CV file you already have on file." : '';

        builderResumeRadio.disabled = !applyModalHasBuilderResume;
        builderResumeOption.classList.toggle('opacity-50', !applyModalHasBuilderResume);
        builderResumeOption.classList.toggle('cursor-not-allowed', !applyModalHasBuilderResume);

        if (applyModalHasUploadedResumeFile) {
            profileResumeRadio.checked = true;
        } else if (applyModalHasBuilderResume) {
            builderResumeRadio.checked = true;
        } else {
            document.querySelector('#jobApplyForm input[name=resume_source][value=upload]').checked = true;
        }
        noResumePrompt.classList.toggle('hidden', applyModalHasUploadedResumeFile || applyModalHasBuilderResume);

        const coverLetterProfileOption = document.getElementById('applyCoverLetterProfileOption');
        const coverLetterProfileRadio = coverLetterProfileOption.querySelector('input');
        if (hasProfileCoverLetter) {
            coverLetterProfileOption.classList.remove('hidden');
            coverLetterProfileOption.classList.add('flex');
            coverLetterProfileRadio.disabled = false;
        } else {
            coverLetterProfileOption.classList.add('hidden');
            coverLetterProfileOption.classList.remove('flex');
            coverLetterProfileRadio.disabled = true;
        }

        document.getElementById('applyModalErrors').classList.add('hidden');
        showApplyStep1();
        // Unhide the modal *before* measuring scrollHeight below — an
        // element inside a display:none ancestor always reports 0, which
        // silently collapsed the resume dropzone (pre-selected to "upload"
        // whenever there's no profile resume) until some later interaction
        // called updateApplyModalState() again after the modal was visible.
        document.getElementById('jobApplyModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateApplyModalState();
    }

    function closeApplyModal() {
        document.getElementById('jobApplyModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', function (e) {
        if (e.target === document.getElementById('jobApplyModal')) closeApplyModal();
    });

    // DRAG & DROP — same dashed-border dropzone pattern as
    // partials/post-job-modal.blade.php's Thumbnail upload.
    function handleDropzoneDragOver(event) {
        event.preventDefault();
        event.currentTarget.classList.add('border-[#C73D1A]', 'bg-orange-50');
    }

    function handleDropzoneDragLeave(event) {
        event.currentTarget.classList.remove('border-[#C73D1A]', 'bg-orange-50');
    }

    function handleDropzoneDrop(event, inputId) {
        event.preventDefault();
        event.currentTarget.classList.remove('border-[#C73D1A]', 'bg-orange-50');
        const input = document.getElementById(inputId);
        if (event.dataTransfer.files.length) {
            input.files = event.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    }

    // Document upload preview — same "Thumbnail" frame pattern as
    // partials/post-job-modal.blade.php's job image upload, swapping the
    // image preview for a filename since these are PDFs/docs, not images.
    function previewApplyDocument(input, placeholderId, fileNameId) {
        const placeholder = document.getElementById(placeholderId);
        const fileName = document.getElementById(fileNameId);

        if (input.files && input.files[0]) {
            fileName.textContent = input.files[0].name;
            fileName.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            resetApplyDocumentPreview(placeholderId, fileNameId);
        }
    }

    function resetApplyDocumentPreview(placeholderId, fileNameId) {
        document.getElementById(fileNameId).classList.add('hidden');
        document.getElementById(fileNameId).textContent = '';
        document.getElementById(placeholderId).classList.remove('hidden');
    }

    // Smoothly expands/collapses the matching file-upload field and keeps
    // the Next button's enabled state in sync with the current choices.
    function updateApplyModalState() {
        const resumeSource = document.querySelector('#jobApplyForm input[name=resume_source]:checked')?.value;
        const coverLetterSource = document.querySelector('#jobApplyForm input[name=cover_letter_source]:checked')?.value;
        const resumeFileInput = document.getElementById('applyResumeFileInput');

        const resumeUploadField = document.getElementById('applyResumeUploadField');
        resumeUploadField.style.maxHeight = resumeSource === 'upload' ? resumeUploadField.scrollHeight + 'px' : '0px';

        const coverLetterUploadField = document.getElementById('applyCoverLetterUploadField');
        coverLetterUploadField.style.maxHeight = coverLetterSource === 'upload' ? coverLetterUploadField.scrollHeight + 'px' : '0px';

        const resumeValid = resumeSource === 'profile' ? applyModalHasUploadedResumeFile
            : resumeSource === 'builder' ? applyModalHasBuilderResume
            : resumeSource === 'upload' ? resumeFileInput.files.length > 0
            : false;

        const nextBtn = document.getElementById('applyNextBtn');
        nextBtn.disabled = !resumeValid;
        nextBtn.classList.toggle('bg-gray-300', !resumeValid);
        nextBtn.classList.toggle('cursor-not-allowed', !resumeValid);
        nextBtn.classList.toggle('bg-[#0E0F3B]', resumeValid);
        nextBtn.classList.toggle('hover:bg-blue-900', resumeValid);
        nextBtn.classList.toggle('cursor-pointer', resumeValid);
    }

    function showApplyStep1() {
        document.getElementById('applyStep1').classList.remove('hidden');
        document.getElementById('applyStep2').classList.add('hidden');
        document.getElementById('applyModalHeading').textContent = 'REVIEW YOUR APPLICATION';
        document.getElementById('applyModalSubheading').textContent = 'Choose the documents to submit for this job, then confirm below.';
    }

    function backToApplyStep1() {
        showApplyStep1();
    }

    // Client-side mirror of applyJob()'s validation, same rationale as
    // post-job-modal.blade.php's handleJobSubmit() — gates moving to Step 2.
    function goToApplyStep2() {
        const resumeSource = document.querySelector('#jobApplyForm input[name=resume_source]:checked')?.value;
        const coverLetterSource = document.querySelector('#jobApplyForm input[name=cover_letter_source]:checked')?.value;
        const errors = [];

        if (!resumeSource) errors.push('Please choose a resume/CV option.');
        if (resumeSource === 'upload' && document.getElementById('applyResumeFileInput').files.length === 0) {
            errors.push('Please upload a resume file.');
        }
        if (coverLetterSource === 'upload' && document.getElementById('applyCoverLetterFileInput').files.length === 0) {
            errors.push('Please upload a cover letter file, or choose a different cover letter option.');
        }

        if (errors.length) {
            document.getElementById('applyModalErrorText').textContent = errors.join(' ');
            const box = document.getElementById('applyModalErrors');
            box.classList.remove('hidden');
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        document.getElementById('applyModalErrors').classList.add('hidden');
        populateApplyReviewStep(resumeSource, coverLetterSource);

        document.getElementById('applyStep1').classList.add('hidden');
        document.getElementById('applyStep2').classList.remove('hidden');
        document.getElementById('applyModalHeading').textContent = 'CONFIRM & SUBMIT';
        document.getElementById('applyModalSubheading').textContent = "Here's exactly what the employer will see.";
        document.getElementById('jobApplyModal').scrollTop = 0;
    }

    function populateApplyReviewStep(resumeSource, coverLetterSource) {
        // Profile info block
        const pic = document.getElementById('applyReviewPicture');
        if (APPLY_PROFILE.picture) {
            pic.src = APPLY_PROFILE.picture;
            pic.classList.remove('hidden');
        } else {
            pic.classList.add('hidden');
        }
        document.getElementById('applyReviewName').textContent = APPLY_PROFILE.name || '';
        document.querySelector('#applyReviewContact span').textContent = APPLY_PROFILE.contact || 'Not provided';
        document.querySelector('#applyReviewEmail span').textContent = APPLY_PROFILE.email || '';
        const linkedinRow = document.getElementById('applyReviewLinkedin');
        const linkedinLink = linkedinRow.querySelector('a');
        if (APPLY_PROFILE.linkedin) {
            linkedinLink.href = APPLY_PROFILE.linkedin;
            linkedinLink.textContent = APPLY_PROFILE.linkedin;
            linkedinRow.classList.remove('hidden');
        } else {
            linkedinRow.classList.add('hidden');
        }

        // Resume section
        const resumeContent = document.getElementById('applyResumeReviewContent');
        resumeContent.innerHTML = '';
        if (resumeSource === 'builder') {
            resumeContent.appendChild(buildApplyBuilderEditor());
        } else if (resumeSource === 'profile') {
            resumeContent.appendChild(buildApplyFileLink(APPLY_PROFILE.uploadedResumeUrl, 'Your uploaded resume file'));
        } else {
            const fileName = document.getElementById('applyResumeFileName').textContent || 'Selected file';
            resumeContent.appendChild(buildApplyFileNamePreview(fileName));
        }

        // Cover letter section
        const coverWrap = document.getElementById('applyCoverLetterReviewWrap');
        const coverContent = document.getElementById('applyCoverLetterReviewContent');
        coverContent.innerHTML = '';
        if (coverLetterSource === 'profile') {
            coverContent.appendChild(buildApplyFileLink(APPLY_PROFILE.uploadedCoverLetterUrl, 'Your saved cover letter'));
            coverWrap.classList.remove('hidden');
        } else if (coverLetterSource === 'upload') {
            const fileName = document.getElementById('applyCoverLetterFileName').textContent || 'Selected file';
            coverContent.appendChild(buildApplyFileNamePreview(fileName));
            coverWrap.classList.remove('hidden');
        } else {
            coverWrap.classList.add('hidden');
        }
    }

    function buildApplyFileLink(url, label) {
        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-xl p-4 flex items-center gap-3';
        div.innerHTML = '<i class="fas fa-file-lines text-[#C73D1A] text-lg"></i>' +
            '<a href="' + (url || '#') + '" target="_blank" class="text-sm font-semibold text-[#1D46A4] hover:underline truncate">' + label + '</a>';
        return div;
    }

    function buildApplyFileNamePreview(fileName) {
        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-xl p-4 flex items-center gap-3';
        div.innerHTML = '<i class="fas fa-file-lines text-[#C73D1A] text-lg"></i>' +
            '<span class="text-sm font-semibold text-[#0E0F3B] truncate"></span>';
        div.querySelector('span').textContent = fileName;
        return div;
    }

    // Builds the fully editable Resume Builder review — a scratch copy
    // seeded from APPLY_BUILDER_RESUME. Nothing here is wired to
    // resume.save; the DOM built here is only ever read back by
    // collectApplyBuilderSnapshot() right before submit.
    function buildApplyBuilderEditor() {
        const wrap = document.createElement('div');
        wrap.id = 'applyBuilderEditor';
        wrap.className = 'border border-gray-200 rounded-xl p-4 space-y-5';
        wrap.innerHTML = `
            <p class="text-[10px] text-gray-400 -mt-1">Edits here are just for this application — your saved Resume Builder profile won't change.</p>
            <div>
                <label class="text-xs font-bold text-[#0E0F3B] block mb-1">Summary</label>
                <textarea id="applySnapSummary" rows="3" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2"></textarea>
            </div>
            <div>
                <label class="text-xs font-bold text-[#0E0F3B] block mb-1">Skills</label>
                <div id="applySnapSkills" class="flex flex-wrap gap-2 mb-2"></div>
                <div class="relative">
                    <input type="text" id="applySnapSkillInput" placeholder="Add a skill and press Enter"
                        class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
                    <div id="applySnapSkillResults" class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-[#0E0F3B]">Work Experience / Projects</label>
                    <button type="button" onclick="addApplyExperienceRow()" class="text-[10px] font-bold text-[#1D46A4] hover:underline">+ Add</button>
                </div>
                <div id="applySnapExperiences" class="space-y-2"></div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-[#0E0F3B]">Certifications / Seminars / Trainings</label>
                    <button type="button" onclick="addApplyCertRow()" class="text-[10px] font-bold text-[#1D46A4] hover:underline">+ Add</button>
                </div>
                <div id="applySnapCerts" class="space-y-2"></div>
            </div>
        `;

        // Defer seeding until after this fragment is actually in the DOM
        // (resumeContent.appendChild happens right after this returns).
        setTimeout(() => seedApplyBuilderEditor(), 0);
        return wrap;
    }

    function seedApplyBuilderEditor() {
        const data = APPLY_BUILDER_RESUME || {};
        document.getElementById('applySnapSummary').value = data.resume_summary || '';

        const skillsBox = document.getElementById('applySnapSkills');
        skillsBox.innerHTML = '';
        (data.skills || []).forEach(s => addApplySkillChip(s.name));

        const expBox = document.getElementById('applySnapExperiences');
        expBox.innerHTML = '';
        if ((data.experiences || []).length) {
            data.experiences.forEach(e => addApplyExperienceRow(e));
        } else {
            addApplyExperienceRow();
        }

        const certBox = document.getElementById('applySnapCerts');
        certBox.innerHTML = '';
        (data.certifications || []).forEach(c => addApplyCertRow(c));

        const skillInput = document.getElementById('applySnapSkillInput');
        let skillDebounce = null;
        skillInput.addEventListener('input', () => {
            clearTimeout(skillDebounce);
            const q = skillInput.value.trim();
            const results = document.getElementById('applySnapSkillResults');
            if (q.length < 2) { results.classList.add('hidden'); results.innerHTML = ''; return; }
            skillDebounce = setTimeout(() => {
                fetch("{{ route('skills.search') }}?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(list => {
                        results.innerHTML = '';
                        list.forEach(skill => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'block w-full text-left px-3 py-2 text-xs hover:bg-gray-50';
                            item.textContent = skill.skill_name;
                            item.onclick = () => { addApplySkillChip(skill.skill_name); skillInput.value = ''; results.classList.add('hidden'); };
                            results.appendChild(item);
                        });
                        const addNew = document.createElement('button');
                        addNew.type = 'button';
                        addNew.className = 'block w-full text-left px-3 py-2 text-xs text-[#1D46A4] font-semibold hover:bg-gray-50 border-t border-gray-100';
                        addNew.textContent = '+ Add "' + q + '" as a new skill';
                        addNew.onclick = () => { addApplySkillChip(q); skillInput.value = ''; results.classList.add('hidden'); };
                        results.appendChild(addNew);
                        results.classList.remove('hidden');
                    });
            }, 250);
        });
        skillInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = skillInput.value.trim();
                if (q) { addApplySkillChip(q); skillInput.value = ''; }
                document.getElementById('applySnapSkillResults').classList.add('hidden');
            }
        });
    }

    function addApplySkillChip(name) {
        if (!name) return;
        const existing = Array.from(document.querySelectorAll('#applySnapSkills .apply-skill-name'))
            .some(el => el.textContent.toLowerCase() === name.toLowerCase());
        if (existing) return;

        const tpl = document.getElementById('applySkillChipTemplate');
        const chip = tpl.content.firstElementChild.cloneNode(true);
        chip.querySelector('.apply-skill-name').textContent = name;
        document.getElementById('applySnapSkills').appendChild(chip);
    }

    function addApplyExperienceRow(prefill) {
        const tpl = document.getElementById('applyExperienceRowTemplate');
        const row = tpl.content.firstElementChild.cloneNode(true);
        if (prefill) {
            row.querySelector('.apply-exp-type').value = prefill.type || 'work';
            row.querySelector('.apply-exp-title').value = prefill.job_title || '';
            row.querySelector('.apply-exp-duration').value = prefill.duration_months || '';
            row.querySelector('.apply-exp-desc').value = prefill.job_description || '';
        }
        document.getElementById('applySnapExperiences').appendChild(row);
    }

    function addApplyCertRow(prefill) {
        const tpl = document.getElementById('applyCertRowTemplate');
        const row = tpl.content.firstElementChild.cloneNode(true);
        if (prefill) {
            row.querySelector('.apply-cert-type').value = prefill.certification_type || 'certification';
            row.querySelector('.apply-cert-name').value = prefill.certification_name || '';
            row.querySelector('.apply-cert-from').value = prefill.certification_from || '';
            row.querySelector('.apply-cert-date').value = prefill.certification_date || '';
        }
        document.getElementById('applySnapCerts').appendChild(row);
    }

    // Walks the (real, live) editor DOM to build the JSON snapshot posted
    // as builder_resume_snapshot_json — this is the only place that data
    // is ever assembled; nothing is kept in a parallel JS state object.
    function collectApplyBuilderSnapshot() {
        const summary = document.getElementById('applySnapSummary')?.value || '';
        const skills = Array.from(document.querySelectorAll('#applySnapSkills .apply-skill-name'))
            .map(el => ({ name: el.textContent }));
        const experiences = Array.from(document.querySelectorAll('#applySnapExperiences .apply-exp-row'))
            .map(row => ({
                type: row.querySelector('.apply-exp-type').value,
                job_title: row.querySelector('.apply-exp-title').value,
                job_description: row.querySelector('.apply-exp-desc').value,
                duration_months: parseInt(row.querySelector('.apply-exp-duration').value, 10) || 0,
            }))
            .filter(e => e.job_title.trim() !== '');
        const certifications = Array.from(document.querySelectorAll('#applySnapCerts .apply-cert-row'))
            .map(row => ({
                certification_type: row.querySelector('.apply-cert-type').value,
                certification_name: row.querySelector('.apply-cert-name').value,
                certification_from: row.querySelector('.apply-cert-from').value,
                certification_date: row.querySelector('.apply-cert-date').value || null,
            }))
            .filter(c => c.certification_name.trim() !== '');

        return { summary, skills, experiences, certifications };
    }

    // Step 2's real submit — the review step itself is the deliberate
    // confirmation now, so this goes straight to submitting (no extra
    // generic "are you sure?" dialog on top of it).
    function handleApplySubmit() {
        const resumeSource = document.querySelector('#jobApplyForm input[name=resume_source]:checked')?.value;

        if (resumeSource === 'builder') {
            const snapshot = collectApplyBuilderSnapshot();
            if (snapshot.experiences.length === 0 && snapshot.skills.length === 0 && !snapshot.summary.trim()) {
                document.getElementById('applyStep2ErrorText').textContent = 'Your resume looks empty — add at least a summary, a skill, or an experience before submitting.';
                document.getElementById('applyStep2Errors').classList.remove('hidden');
                return;
            }
            document.getElementById('applyBuilderSnapshotInput').value = JSON.stringify(snapshot);
        }

        document.getElementById('applyStep2Errors').classList.add('hidden');
        document.getElementById('jobApplyModal').classList.add('hidden');
        document.getElementById('jobApplySuccessModal').classList.remove('hidden');
    }

    function closeApplySuccessModal() {
        document.getElementById('jobApplySuccessModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('jobApplyForm').submit();
    }
</script>

<!-- SUCCESSFULLY APPLIED MODAL — styled after post-job-modal.blade.php's #pendingModal -->
<div id="jobApplySuccessModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full relative text-center">
        <div class="flex justify-center mb-6">
            <div class="bg-[#0E0F3B] rounded-full p-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-2">
            Successfully Applied!
        </h2>
        <p class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-sm mb-8 leading-relaxed">
            Your application and documents have been submitted. The employer will review them and get back to you.
        </p>
        <button onclick="closeApplySuccessModal()" class="w-full bg-[#0E0F3B] text-white py-3 rounded-md font-bold hover:bg-blue-900 transition-colors uppercase tracking-wider">
            Done
        </button>
    </div>
</div>
