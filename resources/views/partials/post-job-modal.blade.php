{{--
    "Post a New Job" modal — shared by the Job Board (general/jobBoard.blade.php)
    and My Job Postings (general/jobPostings.blade.php) pages so the two can
    never drift apart again (they used to be two hand-copied forms; one was
    missing the required hiring_limit field entirely, so posting from the Job
    Board page always failed server validation with no way to fix it).

    Params: $jobPoster (the employer/alumni User posting the job — matches
    addJobPost()'s validation against $programs/$industries, both already
    loaded by both pages' controllers).

    Bundles its own JS (image preview, add/remove program row, client-side
    validation, and the confirm → pending-approval submit flow) and the two
    follow-up modals (#postConfirmModal, #pendingModal) — including this
    partial is everything a page needs for "Post a New Job" to work.
--}}
<div id="postJobModal" class="fixed inset-0 z-[110] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">

    <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl relative flex flex-col min-h-[600px] max-h-[90vh] overflow-y-auto my-8">

        <form action="{{ route('jobPosting.addJobPost', ['id' => $jobPoster->user_id]) }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1">
            @csrf

            <button type="button" onclick="closePostModal()" class="absolute top-11 right-8 text-gray-300 hover:text-gray-500 transition-colors z-10">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>

            <div class="w-full pt-12 text-center">
                <h2 class="inline-block text-3xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent tracking-tight">
                    POST A NEW JOB
                </h2>
            </div>

            <div class="flex flex-col md:flex-row flex-1">

                <!-- IMAGE UPLOAD -->
                <div class="md:w-1/3 flex flex-col items-center justify-center p-8 bg-white">
                    <div id="imageFrame" class="w-full aspect-square border-4 border-[#1D264F] rounded-[2rem] flex flex-col items-center justify-center p-2 shadow-sm relative overflow-hidden">

                        <div id="uploadPlaceholder" class="flex flex-col items-center justify-center">
                            <i class="fas fa-upload text-6xl text-[#1D264F] mb-4"></i>
                            <button type="button" onclick="document.getElementById('jobImageInput').click()" class="bg-[#1D264F] text-white px-8 py-2 rounded-full font-bold text-xs tracking-widest hover:bg-[#0E0F3B] transition-colors">
                                UPLOAD
                            </button>
                        </div>

                        <img id="jobImagePreview" src="#" class="hidden w-full h-full object-cover rounded-[1.6rem]" />

                        <input type="file" name="job_posting_image" id="jobImageInput" accept="image/*" class="hidden" onchange="previewJobImage(this)">

                        <button id="changeImgBtn" type="button" onclick="document.getElementById('jobImageInput').click()" class="hidden absolute bottom-4 bg-white/80 backdrop-blur-sm text-[#1D264F] px-4 py-1 rounded-full font-bold text-[10px] hover:bg-white transition-all">
                            CHANGE IMAGE
                        </button>
                    </div>
                    <p class="text-[10px] font-bold text-[#1D264F] uppercase mt-3">Upload Image <span class="text-red-500">*</span></p>
                </div>

                <!-- FORM FIELDS -->
                <div class="md:w-2/3 p-10 pt-6 space-y-4">

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Title <span class="text-red-500">*</span></label>
                            <input type="text" name="job_posting_title" placeholder="e.g., Senior Full Stack Developer"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="job_posting_company" value="@if ($jobPoster->user_role == 'alumni'){{ $jobPoster->employer_company_name }}@else{{ $jobPoster->employer->employer_company_name }}@endif" readonly
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm bg-gray-50 focus:outline-none focus:border-[#C73D1A]">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">Company Address <span class="text-red-500">*</span></label>
                        <input type="text" name="job_posting_address" placeholder="Enter company address"
                            class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Type <span class="text-red-500">*</span></label>
                            <select name="job_posting_employment_type"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none bg-white">
                                <option>Select Type (e.g., Full-time)</option>
                                <option>Full-Time</option>
                                <option>Part-Time</option>
                                <option>Freelance</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Setup <span class="text-red-500">*</span></label>
                            <select name="job_posting_setup"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] appearance-none bg-white">
                                <option>Select Setup (e.g., Remote)</option>
                                <option>Remote</option>
                                <option>On-Site</option>
                                <option>Hybrid</option>
                            </select>
                        </div>
                    </div>

                    <!-- COURSE -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">Recommended Course/Program <span class="text-red-500">*</span></label>

                        <div id="course-input-container" class="space-y-2">
                            <div class="flex items-center gap-3 course-row">
                                <select name="program[]"
                                    class="w-full flex-1 border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white">
                                    <option selected disabled>Select Undergraduate Program</option>
                                    @foreach ($programs as $program)
                                    <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                                    @endforeach
                                </select>

                                <button type="button" onclick="addCourseField()"
                                    class="bg-[#1D264F] text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#0E0F3B] transition-colors shrink-0">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <p id="course-limit-msg" class="text-[9px] text-gray-400 italic hidden">
                            Maximum of 3 programs reached.
                        </p>
                    </div>

                    <!-- INDUSTRY -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">Industry / Sector</label>
                        <select name="industry_id"
                            class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] bg-white">
                            <option value="">Select Industry</option>
                            @foreach ($industries as $industry)
                            <option value="{{ $industry->industry_id }}">{{ $industry->industry_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @include('partials.job-posting-skills-field', ['uid' => 'create'])

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-[#1D264F] uppercase">Job Description <span class="text-red-500">*</span></label>
                        @include('partials.rich-text-editor', ['uid' => 'create', 'fieldName' => 'job_posting_description'])
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Closing / Validity Date <span class="text-red-500">*</span></label>
                            <input type="date" name="job_closing_date" id="job_closing_date"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A] text-gray-400"
                                onchange="this.classList.remove('text-gray-400'); this.classList.add('text-black')">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-[#1D264F] uppercase">Hiring Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="hiring_limit" min="1" value="1" placeholder="e.g., 2"
                                class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
                        </div>
                    </div>

                    <div id="modalErrors" class="hidden flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 shadow-sm">
                        <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                        <div>
                            <ul id="modalErrorList" class="text-xs space-y-0.5 list-disc list-inside text-red-600"></ul>
                        </div>
                    </div>

                    @if ($errors->any())
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 shadow-sm">
                        <i class="fas fa-circle-exclamation mt-0.5 text-red-500 text-base shrink-0"></i>
                        <div>
                            <ul class="text-xs space-y-0.5 list-disc list-inside text-red-600">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => openPostJobModal());
                    </script>
                    @endif

                    <!-- ACTION BUTTONS -->
                    <div class="flex justify-end gap-4 mt-8">
                        <button type="button" onclick="closePostModal()"
                            class="px-10 py-2 border-2 border-[#1D264F] text-[#1D264F] rounded-md font-bold text-sm hover:bg-[#0E0F3B] hover:text-white transition-colors">
                            CANCEL
                        </button>

                        <button type="button" onclick="handleJobSubmit()"
                            class="px-12 py-2 bg-[#0E0F3B] text-white rounded-md font-bold text-sm hover:bg-blue-900 transition-colors">
                            POST
                        </button>
                    </div>

                </div>
            </div>

        </form>
    </div>
</div>

<!-- POST JOB CONFIRMATION MODAL -->
<div id="postConfirmModal" class="fixed inset-0 z-[210] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-paper-plane text-[#1D46A4] text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-[#0E0F3B] mb-2">Post this Job?</h2>
        <p class="text-gray-500 text-sm mb-6">Please confirm that all job details are correct before submitting for admin approval.</p>
        <div class="flex gap-3">
            <button onclick="cancelPostConfirm()" class="flex-1 border border-gray-300 text-gray-600 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-100 transition-colors">
                CANCEL
            </button>
            <button onclick="confirmPostJob()" class="flex-1 bg-[#0E0F3B] text-white py-2.5 rounded-lg font-bold text-sm hover:bg-[#1D46A4] transition-colors">
                YES, POST IT
            </button>
        </div>
    </div>
</div>

<!-- JOB POST PENDING APPROVAL MODAL -->
<div id="pendingModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full relative text-center">
        <button onclick="closePendingModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <div class="flex justify-center mb-6">
            <div class="bg-[#0E0F3B] rounded-full p-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent mb-2">
            Job Post is now Pending for Approval
        </h2>
        <p class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-sm mb-8 leading-relaxed">
            Your job post has been queued for review by the PLV-AlumNet Admin. We'll notify you as soon as it is approved.
        </p>
        <button onclick="closePendingModal()" class="w-full bg-[#0E0F3B] text-white py-3 rounded-md font-bold hover:bg-blue-900 transition-colors uppercase tracking-wider">
            Done
        </button>
    </div>
</div>

<script>
    // POST A NEW JOB MODAL
    function openPostJobModal() {
        document.getElementById('postJobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePostModal() {
        document.getElementById('postJobModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', (e) => {
        if (e.target === document.getElementById('postJobModal')) closePostModal();
    });

    // JOB POST IMAGE UPLOAD PREVIEW — frameId/placeholderId/previewId/changeBtnId
    // default to this modal's own ids so a plain onchange="previewJobImage(this)"
    // works unchanged; the per-job Edit Job Post form (jobPostings.blade.php)
    // passes its own previewId explicitly so editing one job's image can never
    // silently update this (hidden) create form's preview instead.
    function previewJobImage(input, frameId = 'imageFrame', placeholderId = 'uploadPlaceholder', previewId = 'jobImagePreview', changeBtnId = 'changeImgBtn') {
        const frame = frameId ? document.getElementById(frameId) : null;
        const placeholder = placeholderId ? document.getElementById(placeholderId) : null;
        const preview = document.getElementById(previewId);
        const changeBtn = changeBtnId ? document.getElementById(changeBtnId) : null;

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (changeBtn) changeBtn.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                if (frame) {
                    frame.classList.remove('p-6');
                    frame.classList.add('p-0');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ADD/REMOVE PROGRAM ROW — containerId/msgId default to this modal's own
    // ids so onclick="addCourseField()" (no args) works unchanged; the Edit
    // Job Post form passes its own ids explicitly for the same reason as
    // previewJobImage() above.
    function addCourseField(containerId = 'course-input-container', msgId = 'course-limit-msg') {
        const container = document.getElementById(containerId);
        const rows = container.getElementsByClassName('course-row');

        if (rows.length < 3) {
            const newRow = rows[0].cloneNode(true);
            const select = newRow.querySelector('select');
            select.selectedIndex = 0;
            const btn = newRow.querySelector('button');
            btn.innerHTML = '<i class="fas fa-minus text-xs"></i>';
            btn.classList.replace('bg-[#1D264F]', 'bg-red-500');
            btn.setAttribute('onclick', "removeCourseField(this, '" + msgId + "')");
            container.appendChild(newRow);

            if (rows.length === 3) {
                document.getElementById(msgId)?.classList.remove('hidden');
            }
        }
    }

    function removeCourseField(button, msgId = 'course-limit-msg') {
        button.closest('.course-row').remove();
        document.getElementById(msgId)?.classList.add('hidden');
    }

    // CLIENT-SIDE VALIDATION — mirrors addJobPost()'s server rules so a
    // missing/invalid field is caught before the confirm step instead of
    // round-tripping to the server (image, program, and description are
    // still ultimately enforced server-side too).
    function handleJobSubmit() {
        const form = document.querySelector('#postJobModal form');
        const errors = [];

        const title = form.querySelector('[name="job_posting_title"]').value.trim();
        const address = form.querySelector('[name="job_posting_address"]').value.trim();
        const description = form.querySelector('[name="job_posting_description"]').value.trim();
        const employmentType = form.querySelector('[name="job_posting_employment_type"]').value;
        const setup = form.querySelector('[name="job_posting_setup"]').value;
        const closingDate = form.querySelector('[name="job_closing_date"]').value;
        const hiringLimit = form.querySelector('[name="hiring_limit"]').value;
        const image = form.querySelector('[name="job_posting_image"]').files.length;
        const programs = form.querySelectorAll('[name="program[]"]');

        if (!title) errors.push('Job title is required.');
        if (employmentType.startsWith('Select Type')) errors.push('Please select a job type.');
        if (setup.startsWith('Select Setup')) errors.push('Please select a job setup.');
        if (!address) errors.push('Company address is required.');
        if (!closingDate) errors.push('Closing / validity date is required.');
        if (!hiringLimit || Number(hiringLimit) < 1) errors.push('Hiring limit must be at least 1.');
        if (!image) errors.push('Please upload a job image.');
        if (!description) errors.push('Job description is required.');

        let programSelected = false;
        programs.forEach(select => {
            if (select.selectedIndex > 0) programSelected = true;
        });
        if (!programSelected) errors.push('Please select at least one recommended program.');

        if (errors.length > 0) {
            const errorBox = document.getElementById('modalErrors');
            const errorList = document.getElementById('modalErrorList');

            errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
            errorBox.classList.remove('hidden');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        document.getElementById('modalErrors').classList.add('hidden');
        document.getElementById('postJobModal').classList.add('hidden');
        document.getElementById('postConfirmModal').classList.remove('hidden');
    }

    function cancelPostConfirm() {
        document.getElementById('postConfirmModal').classList.add('hidden');
        document.getElementById('postJobModal').classList.remove('hidden');
    }

    function confirmPostJob() {
        document.getElementById('postConfirmModal').classList.add('hidden');
        document.getElementById('pendingModal').classList.remove('hidden');
    }

    function closePendingModal() {
        document.getElementById('pendingModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.querySelector('#postJobModal form').submit();
    }
</script>
