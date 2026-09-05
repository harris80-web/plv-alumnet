{{--
    "Apply for this Job" review modal — replaces the old one-click Apply
    form. Shared by every Apply trigger on the job board (job-post-card,
    job-detail-modal) so there's exactly one place this flow lives.

    Static content only, no modal-on-modal: the resume/cover-letter choices
    are plain sections inside this one modal. Submitting hides this modal
    and shows #jobApplySuccessModal (styled like partials/post-job-modal.blade.php's
    #pendingModal, per request) — closing that ("Done") is what actually
    submits the underlying form, same optimistic-confirm pattern already
    used there.

    openApplyModal(jobId, hasProfileResume, hasProfileCoverLetter) is called
    from every Apply button/card — hasProfileResume/hasProfileCoverLetter
    come from Alumnus::hasProfileResume() / alumnus_cover_letter_file_path.
--}}
<div id="jobApplyModal" class="fixed inset-0 z-[130] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">

    <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl relative my-8 max-h-[90vh] overflow-y-auto">

        <form id="jobApplyForm" method="POST" action="" enctype="multipart/form-data">
            @csrf

            <button type="button" onclick="closeApplyModal()" class="absolute top-6 right-6 text-gray-300 hover:text-gray-500 transition-colors z-10">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>

            <div class="pt-10 px-8 md:px-10 text-center">
                <h2 class="inline-block text-2xl md:text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">
                    REVIEW YOUR APPLICATION
                </h2>
                <p class="text-gray-500 text-sm mt-1">Choose the documents to submit for this job, then confirm below.</p>
            </div>

            <div class="p-8 md:px-10 pt-6 space-y-6">

                <!-- RESUME / CV -->
                <div>
                    <label class="text-[10px] font-bold text-[#1D264F] uppercase block mb-2">Resume / CV <span class="text-red-500">*</span></label>

                    <label id="applyResumeProfileOption" class="flex items-start gap-3 border rounded-xl p-4 cursor-pointer transition-colors border-gray-200 has-[:checked]:border-[#1D46A4] has-[:checked]:bg-blue-50">
                        <span class="relative flex items-center justify-center w-4 h-4 shrink-0 mt-1">
                            <input type="radio" name="resume_source" value="profile" class="peer appearance-none w-4 h-4 rounded-full border-2 border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer" onchange="updateApplyModalState()">
                            <span class="absolute w-1.5 h-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 pointer-events-none"></span>
                        </span>
                        <span>
                            <span class="block text-sm font-bold text-[#0E0F3B]">Use my AlumNet Profile</span>
                            <span id="applyResumeProfileHint" class="block text-xs text-gray-500 mt-0.5"></span>
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
                    <button type="button" id="applySubmitBtn" onclick="handleApplySubmit()" disabled
                        class="px-10 py-2 bg-gray-300 text-white rounded-md font-bold text-sm cursor-not-allowed transition-colors">
                        SUBMIT APPLICATION
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- APPLY CONFIRMATION MODAL — styled after post-job-modal.blade.php's #postConfirmModal -->
<div id="jobApplyConfirmModal" class="fixed inset-0 z-[210] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-paper-plane text-[#1D46A4] text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-[#0E0F3B] mb-2">Submit this Application?</h2>
        <p class="text-gray-500 text-sm mb-6">Please confirm you want to apply with the documents you selected — you won't be able to change them afterward.</p>
        <div class="flex gap-3">
            <button onclick="cancelApplyConfirm()" class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-100 transition-colors">
                CANCEL
            </button>
            <button onclick="confirmApplySubmit()" class="flex-1 bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-[#1D46A4] transition-colors">
                YES, APPLY
            </button>
        </div>
    </div>
</div>

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

<script>
    const JOB_APPLY_URL_TMPL = @json(route('jobApplication.apply', 999999999));
    let applyModalHasProfileResume = false;

    function openApplyModal(jobId, hasProfileResume, hasProfileCoverLetter) {
        document.getElementById('jobApplyForm').action = JOB_APPLY_URL_TMPL.replace('999999999', jobId);
        applyModalHasProfileResume = !!hasProfileResume;

        // Reset state from any previous job's selections.
        document.querySelectorAll('#jobApplyForm input[type=radio]').forEach(r => r.checked = false);
        document.getElementById('applyResumeFileInput').value = '';
        document.getElementById('applyCoverLetterFileInput').value = '';
        resetApplyDocumentPreview('applyResumeUploadPlaceholder', 'applyResumeFileName');
        resetApplyDocumentPreview('applyCoverLetterUploadPlaceholder', 'applyCoverLetterFileName');
        document.querySelector('#jobApplyForm input[name=cover_letter_source][value=none]').checked = true;

        const profileResumeRadio = document.querySelector('#jobApplyForm input[name=resume_source][value=profile]');
        const profileResumeOption = document.getElementById('applyResumeProfileOption');
        const noResumePrompt = document.getElementById('applyNoResumePrompt');
        const resumeHint = document.getElementById('applyResumeProfileHint');

        if (applyModalHasProfileResume) {
            profileResumeRadio.disabled = false;
            profileResumeOption.classList.remove('opacity-50', 'cursor-not-allowed');
            resumeHint.textContent = "We'll send the resume/CV you already have on file.";
            noResumePrompt.classList.add('hidden');
            profileResumeRadio.checked = true;
        } else {
            profileResumeRadio.disabled = true;
            profileResumeOption.classList.add('opacity-50', 'cursor-not-allowed');
            resumeHint.textContent = '';
            noResumePrompt.classList.remove('hidden');
            document.querySelector('#jobApplyForm input[name=resume_source][value=upload]').checked = true;
        }

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
    // the Submit button's enabled state in sync with the current choices.
    function updateApplyModalState() {
        const resumeSource = document.querySelector('#jobApplyForm input[name=resume_source]:checked')?.value;
        const coverLetterSource = document.querySelector('#jobApplyForm input[name=cover_letter_source]:checked')?.value;
        const resumeFileInput = document.getElementById('applyResumeFileInput');

        const resumeUploadField = document.getElementById('applyResumeUploadField');
        resumeUploadField.style.maxHeight = resumeSource === 'upload' ? resumeUploadField.scrollHeight + 'px' : '0px';

        const coverLetterUploadField = document.getElementById('applyCoverLetterUploadField');
        coverLetterUploadField.style.maxHeight = coverLetterSource === 'upload' ? coverLetterUploadField.scrollHeight + 'px' : '0px';

        const resumeValid = resumeSource === 'profile' ? applyModalHasProfileResume
            : resumeSource === 'upload' ? resumeFileInput.files.length > 0
            : false;

        const submitBtn = document.getElementById('applySubmitBtn');
        submitBtn.disabled = !resumeValid;
        submitBtn.classList.toggle('bg-gray-300', !resumeValid);
        submitBtn.classList.toggle('cursor-not-allowed', !resumeValid);
        submitBtn.classList.toggle('bg-[#0E0F3B]', resumeValid);
        submitBtn.classList.toggle('hover:bg-blue-900', resumeValid);
        submitBtn.classList.toggle('cursor-pointer', resumeValid);
    }

    // Client-side mirror of applyJob()'s validation, same rationale as
    // post-job-modal.blade.php's handleJobSubmit().
    function handleApplySubmit() {
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
        document.getElementById('jobApplyModal').classList.add('hidden');
        document.getElementById('jobApplyConfirmModal').classList.remove('hidden');
    }

    // "Cancel" on the confirmation step goes back to the review modal
    // (their selections are untouched) rather than closing the whole flow —
    // same back-and-forth as post-job-modal.blade.php's cancelPostConfirm().
    function cancelApplyConfirm() {
        document.getElementById('jobApplyConfirmModal').classList.add('hidden');
        document.getElementById('jobApplyModal').classList.remove('hidden');
    }

    function confirmApplySubmit() {
        document.getElementById('jobApplyConfirmModal').classList.add('hidden');
        document.getElementById('jobApplySuccessModal').classList.remove('hidden');
    }

    function closeApplySuccessModal() {
        document.getElementById('jobApplySuccessModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('jobApplyForm').submit();
    }
</script>
