{{--
    Same fields/validation/save endpoint as the resume builder wizard
    (resources/views/alumni/profile.blade.php + ResumeBuilderController::save),
    just laid out to look like the finished resume itself instead of a
    step-by-step wizard. Expects $user, $resumeData, $industries.
--}}
<div id="resumeEditorOverlay" class="hidden fixed inset-0 z-[200] bg-black/60 flex items-start justify-center overflow-y-auto py-8 px-4">
    <div id="resumeEditorPanel" class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl relative">

        <div class="flex justify-between items-center px-8 md:px-12 pt-8">
            <h2 class="text-lg font-bold text-[#0E0F3B] uppercase tracking-wide">Edit Resume</h2>
            <button type="button" id="closeResumeEditorBtn" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>

        {{-- ===== Item 23 — Resume import, same drag & drop UI/JS as the wizard's, ported here so re-importing doesn't require switching modals ===== --}}
        <div class="px-8 md:px-12 pt-4">
            <div id="editorResumeDropzone" onclick="document.getElementById('editorImportResumeFile').click()"
                class="p-6 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 transition cursor-pointer text-center">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-[#C73D1A] mb-2"></i>
                <p id="editorResumeDropzoneText" class="text-sm font-medium text-gray-500">Drag &amp; drop or upload files
                    here</p>
                <input type="file" id="editorImportResumeFile" accept="application/pdf" class="hidden">
            </div>
            <p class="text-center text-xs text-gray-500 uppercase tracking-wide mt-3">or</p>
            <div class="flex flex-wrap items-center justify-center gap-3 mt-3 mb-2">
                <button type="button" id="editorImportResumeBtn"
                    class="text-sm font-medium bg-[#0E0F3B] text-white rounded px-4 py-1.5 hover:bg-[#1D46A4] disabled:opacity-50">
                    Import from PDF
                </button>
                <span id="editorImportResumeStatus" class="text-xs text-gray-500"></span>
            </div>
        </div>

        <div class="border-t border-gray-200 mx-8 md:mx-12"></div>

        <form id="resumeEditorForm" class="p-8 md:p-12 pt-4">
            @csrf

            {{-- ===== Header (name/contact/photo are read-only here; edited on this same page) ===== --}}
            <div class="flex items-center gap-6 border-b-2 border-[#0E0F3B] pb-6 mb-6">
                @if($user->user_profile_picture)
                    <img src="{{ asset('storage/' . $user->user_profile_picture) }}" alt="Profile Photo"
                        class="w-20 h-20 rounded-full object-cover border-2 border-[#0E0F3B] flex-shrink-0">
                @endif
                <div class="flex-1 text-center">
                    <h1 class="text-3xl font-bold text-[#0E0F3B] uppercase tracking-wide">
                        {{ $user->alumnus->resumeFullName() }}
                    </h1>
                    <div class="flex flex-wrap justify-center items-center gap-x-4 gap-y-2 text-sm text-gray-600 mt-3">
                        <span><i class="fa-solid fa-envelope text-[#ED7A07] mr-1"></i>{{ $user->user_email }}</span>
                        @if($user->user_number)
                            <span><i class="fa-solid fa-phone text-[#ED7A07] mr-1"></i>{{ $user->user_number }}</span>
                        @endif
                        <span class="flex items-center gap-1">
                            <i class="fa-brands fa-linkedin text-[#ED7A07]"></i>
                            <input type="url" name="linkedin_url" placeholder="linkedin.com/in/..."
                                value="{{ $resumeData['linkedin_url'] ?? '' }}"
                                class="border border-gray-300 rounded px-2 py-0.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                        </span>
                    </div>
                </div>
            </div>

            {{-- ===== Summary ===== --}}
            <div class="mb-6">
                <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                    Professional Summary</h2>
                <textarea name="resume_summary" rows="3" maxlength="500"
                    placeholder="A short description of who you are and what you're looking for..."
                    class="w-full text-sm text-gray-700 leading-relaxed border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">{{ $resumeData['resume_summary'] ?? '' }}</textarea>
            </div>

            {{-- ===== Education (not part of the resume builder — read-only) ===== --}}
            <div class="mb-6">
                <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                    Education</h2>
                <div class="flex justify-between items-baseline flex-wrap gap-x-2">
                    <p class="font-semibold text-gray-900">{{ $user->alumnus->program->program_name ?? '--' }}</p>
                    @if($user->alumnus->alumnus_batch)
                        <p class="text-xs text-gray-500">Batch {{ $user->alumnus->alumnus_batch->format('Y') }}</p>
                    @endif
                </div>
                @if($user->alumnus->program?->collegeName())
                    <p class="text-xs text-gray-500">{{ $user->alumnus->program->collegeName() }}</p>
                @endif
                <p class="text-xs text-gray-500 italic">Pamantasan ng Lungsod ng Valenzuela (PLV)</p>
            </div>

            {{-- ===== Skills ===== --}}
            <div class="mb-6">
                <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                    Skills</h2>

                <div class="relative mb-3 w-full sm:w-1/2">
                    <input type="text" id="editorSkillSearchInput" autocomplete="off"
                        placeholder="Search or add a skill..."
                        class="w-full rounded border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                    <div id="editorSkillSearchResults"
                        class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg max-h-56 overflow-y-auto"></div>
                </div>

                <div id="editorSkillsList" class="flex flex-wrap gap-2">
                    @foreach(($resumeData['skills'] ?? []) as $i => $skill)
                        <span class="skill-chip flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full pl-3 pr-1 py-1">
                            <span class="skill-name-display">{{ $skill['name'] }}</span>
                            <input type="hidden" name="skills[{{ $i }}][name]" value="{{ $skill['name'] }}">
                            <input type="hidden" name="skills[{{ $i }}][category]" value="{{ $skill['category'] ?? 'domain' }}">
                            <button type="button" class="remove-row text-red-700 px-1">&times;</button>
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- ===== Work Experience ===== --}}
            <div class="mb-6">
                <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                    Work Experience</h2>
                <div id="editorWorkList" class="space-y-4">
                    @foreach(($resumeData['experiences'] ?? []) as $i => $exp)
                        @if($exp['type'] === 'work')
                            <div class="experience-row" data-index="{{ $i }}">
                                <input type="hidden" name="experiences[{{ $i }}][type]" value="work">
                                <input type="text" name="experiences[{{ $i }}][job_title]" value="{{ $exp['job_title'] }}"
                                    placeholder="Job title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm w-full mb-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                {{-- Item 21 — date range replaces a single "duration in months" input --}}
                                <div class="flex items-center gap-2 mb-1 text-xs text-gray-500">
                                    <label>From <input type="date" name="experiences[{{ $i }}][start_date]" value="{{ $exp['start_date'] ?? '' }}"
                                        class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></label>
                                    <label>To <input type="date" name="experiences[{{ $i }}][end_date]" value="{{ $exp['end_date'] ?? '' }}"
                                        {{ ($exp['is_ongoing'] ?? false) ? 'disabled' : '' }}
                                        class="exp-end-date border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4] disabled:bg-gray-100"></label>
                                    <label class="flex items-center gap-1">
                                        <input type="checkbox" class="exp-ongoing-checkbox" name="experiences[{{ $i }}][is_ongoing]" value="1" @checked($exp['is_ongoing'] ?? false)>
                                        Ongoing
                                    </label>
                                </div>
                                <input type="hidden" name="experiences[{{ $i }}][duration_months]" value="{{ $exp['duration_months'] }}">
                                <select name="experiences[{{ $i }}][industry_id]"
                                    class="text-xs text-gray-500 italic border border-gray-300 rounded px-2 py-1 mb-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                    <option value="">Industry (optional)</option>
                                    @foreach($industries as $industry)
                                        <option value="{{ $industry->industry_id }}" @selected(($exp['industry_id'] ?? null) == $industry->industry_id)>{{ $industry->industry_name }}</option>
                                    @endforeach
                                </select>
                                <textarea name="experiences[{{ $i }}][job_description]" rows="2" placeholder="What did you do?"
                                    class="w-full text-sm text-gray-700 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">{{ $exp['job_description'] }}</textarea>
                                <button type="button" class="remove-row text-xs text-red-700 mt-1">Remove</button>
                            </div>
                        @endif
                    @endforeach
                </div>
                <button type="button" id="editorAddWork" class="mt-3 text-xs font-medium border border-[#1D46A4] text-[#1D46A4] rounded px-3 py-1.5 hover:bg-blue-50">
                    + Add work experience
                </button>
            </div>

            {{-- ===== Projects ===== --}}
            <div class="mb-6">
                <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                    Projects</h2>
                <div id="editorProjectList" class="space-y-4">
                    @foreach(($resumeData['experiences'] ?? []) as $i => $exp)
                        @if($exp['type'] === 'project')
                            <div class="experience-row" data-index="{{ $i }}">
                                <input type="hidden" name="experiences[{{ $i }}][type]" value="project">
                                <input type="text" name="experiences[{{ $i }}][job_title]" value="{{ $exp['job_title'] }}"
                                    placeholder="Project title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm w-full mb-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                {{-- Item 21 — date range replaces a single "duration in months" input --}}
                                <div class="flex items-center gap-2 mb-1 text-xs text-gray-500">
                                    <label>From <input type="date" name="experiences[{{ $i }}][start_date]" value="{{ $exp['start_date'] ?? '' }}"
                                        class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></label>
                                    <label>To <input type="date" name="experiences[{{ $i }}][end_date]" value="{{ $exp['end_date'] ?? '' }}"
                                        {{ ($exp['is_ongoing'] ?? false) ? 'disabled' : '' }}
                                        class="exp-end-date border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4] disabled:bg-gray-100"></label>
                                    <label class="flex items-center gap-1">
                                        <input type="checkbox" class="exp-ongoing-checkbox" name="experiences[{{ $i }}][is_ongoing]" value="1" @checked($exp['is_ongoing'] ?? false)>
                                        Ongoing
                                    </label>
                                </div>
                                <input type="hidden" name="experiences[{{ $i }}][duration_months]" value="{{ $exp['duration_months'] }}">
                                <input type="hidden" name="experiences[{{ $i }}][industry_id]" value="">
                                <textarea name="experiences[{{ $i }}][job_description]" rows="2" placeholder="What was this project?"
                                    class="w-full text-sm text-gray-700 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">{{ $exp['job_description'] }}</textarea>
                                <button type="button" class="remove-row text-xs text-red-700 mt-1">Remove</button>
                            </div>
                        @endif
                    @endforeach
                </div>
                <button type="button" id="editorAddProject" class="mt-3 text-xs font-medium border border-[#1D46A4] text-[#1D46A4] rounded px-3 py-1.5 hover:bg-blue-50">
                    + Add project
                </button>
            </div>

            {{-- ===== Certifications & Trainings ===== --}}
            <div class="mb-6">
                <h2 class="text-sm font-bold text-[#0E0F3B] uppercase tracking-widest border-b border-gray-200 pb-1 mb-2">
                    Certifications &amp; Trainings</h2>
                <div id="editorCertList" class="space-y-3">
                    @foreach(($resumeData['certifications'] ?? []) as $i => $cert)
                        <div class="cert-row" data-index="{{ $i }}">
                            <div class="flex gap-4 mb-1 text-xs">
                                <label class="flex items-center gap-1"><input type="radio" name="certifications[{{ $i }}][certification_type]" value="certification" @checked($cert['certification_type'] === 'certification')> Certification</label>
                                <label class="flex items-center gap-1"><input type="radio" name="certifications[{{ $i }}][certification_type]" value="seminar" @checked($cert['certification_type'] === 'seminar')> Seminar</label>
                                <label class="flex items-center gap-1"><input type="radio" name="certifications[{{ $i }}][certification_type]" value="training" @checked($cert['certification_type'] === 'training')> Training</label>
                            </div>
                            <div class="flex justify-between items-baseline gap-2">
                                <input type="text" name="certifications[{{ $i }}][certification_name]" value="{{ $cert['certification_name'] }}"
                                    placeholder="Title" class="font-semibold text-sm border border-gray-300 rounded px-2 py-1 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                <input type="text" name="certifications[{{ $i }}][certification_from]" value="{{ $cert['certification_from'] }}"
                                    placeholder="Issuing organization" class="text-sm border border-gray-300 rounded px-2 py-1 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                <input type="date" name="certifications[{{ $i }}][certification_date]" value="{{ $cert['certification_date'] }}"
                                    class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                            </div>
                            <button type="button" class="remove-row text-xs text-red-700 mt-1">Remove</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="editorAddCert" class="mt-3 text-xs font-medium border border-[#1D46A4] text-[#1D46A4] rounded px-3 py-1.5 hover:bg-blue-50">
                    + Add certification or seminar
                </button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <span id="resumeEditorStatus" class="text-sm text-gray-500 self-center"></span>
                <button type="button" id="cancelResumeEditorBtn" class="px-6 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] font-bold rounded-lg uppercase tracking-widest text-xs hover:bg-[#0E0F3B] hover:text-white">
                    Cancel
                </button>
                <button type="button" id="saveResumeEditorBtn" class="px-6 py-2 bg-[#0E0F3B] text-white font-bold rounded-lg hover:bg-[#1D46A4] transition uppercase tracking-widest text-xs shadow-lg">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== templates JS clones to add new rows ===== --}}
<template id="editorWorkRowTemplate">
    <div class="experience-row" data-index="__INDEX__">
        <input type="hidden" name="experiences[__INDEX__][type]" value="work">
        <input type="text" name="experiences[__INDEX__][job_title]" placeholder="Job title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm w-full mb-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
        <div class="flex items-center gap-2 mb-1 text-xs text-gray-500">
            <label>From <input type="date" name="experiences[__INDEX__][start_date]" class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></label>
            <label>To <input type="date" name="experiences[__INDEX__][end_date]" class="exp-end-date border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4] disabled:bg-gray-100"></label>
            <label class="flex items-center gap-1">
                <input type="checkbox" class="exp-ongoing-checkbox" name="experiences[__INDEX__][is_ongoing]" value="1">
                Ongoing
            </label>
        </div>
        <input type="hidden" name="experiences[__INDEX__][duration_months]" value="">
        <select name="experiences[__INDEX__][industry_id]" class="text-xs text-gray-500 italic border border-gray-300 rounded px-2 py-1 mb-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
            <option value="">Industry (optional)</option>
            @foreach($industries as $industry)
                <option value="{{ $industry->industry_id }}">{{ $industry->industry_name }}</option>
            @endforeach
        </select>
        <textarea name="experiences[__INDEX__][job_description]" rows="2" placeholder="What did you do?" class="w-full text-sm text-gray-700 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></textarea>
        <button type="button" class="remove-row text-xs text-red-700 mt-1">Remove</button>
    </div>
</template>

<template id="editorProjectRowTemplate">
    <div class="experience-row" data-index="__INDEX__">
        <input type="hidden" name="experiences[__INDEX__][type]" value="project">
        <input type="text" name="experiences[__INDEX__][job_title]" placeholder="Project title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm w-full mb-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
        <div class="flex items-center gap-2 mb-1 text-xs text-gray-500">
            <label>From <input type="date" name="experiences[__INDEX__][start_date]" class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></label>
            <label>To <input type="date" name="experiences[__INDEX__][end_date]" class="exp-end-date border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4] disabled:bg-gray-100"></label>
            <label class="flex items-center gap-1">
                <input type="checkbox" class="exp-ongoing-checkbox" name="experiences[__INDEX__][is_ongoing]" value="1">
                Ongoing
            </label>
        </div>
        <input type="hidden" name="experiences[__INDEX__][duration_months]" value="">
        <input type="hidden" name="experiences[__INDEX__][industry_id]" value="">
        <textarea name="experiences[__INDEX__][job_description]" rows="2" placeholder="What was this project?" class="w-full text-sm text-gray-700 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]"></textarea>
        <button type="button" class="remove-row text-xs text-red-700 mt-1">Remove</button>
    </div>
</template>

<template id="editorCertRowTemplate">
    <div class="cert-row" data-index="__INDEX__">
        <div class="flex gap-4 mb-1 text-xs">
            <label class="flex items-center gap-1"><input type="radio" name="certifications[__INDEX__][certification_type]" value="certification" checked> Certification</label>
            <label class="flex items-center gap-1"><input type="radio" name="certifications[__INDEX__][certification_type]" value="seminar"> Seminar</label>
            <label class="flex items-center gap-1"><input type="radio" name="certifications[__INDEX__][certification_type]" value="training"> Training</label>
        </div>
        <div class="flex justify-between items-baseline gap-2">
            <input type="text" name="certifications[__INDEX__][certification_name]" placeholder="Title" class="font-semibold text-sm border border-gray-300 rounded px-2 py-1 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
            <input type="text" name="certifications[__INDEX__][certification_from]" placeholder="Issuing organization" class="text-sm border border-gray-300 rounded px-2 py-1 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
            <input type="date" name="certifications[__INDEX__][certification_date]" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
        </div>
        <button type="button" class="remove-row text-xs text-red-700 mt-1">Remove</button>
    </div>
</template>

<script>
(function () {
    'use strict';

    var overlay = document.getElementById('resumeEditorOverlay');
    var form = document.getElementById('resumeEditorForm');
    var statusEl = document.getElementById('resumeEditorStatus');

    var counters = {
        skills: document.querySelectorAll('#editorSkillsList .skill-chip').length,
        experiences: (function () {
            var all = document.querySelectorAll('#editorWorkList .experience-row, #editorProjectList .experience-row');
            var max = -1;
            all.forEach(function (row) { max = Math.max(max, Number(row.dataset.index)); });
            return max + 1;
        })(),
        certifications: document.querySelectorAll('#editorCertList .cert-row').length,
    };

    document.getElementById('openResumeEditorBtn').addEventListener('click', function () {
        overlay.classList.remove('hidden');
    });

    function closeEditor() {
        overlay.classList.add('hidden');
    }

    document.getElementById('closeResumeEditorBtn').addEventListener('click', closeEditor);
    document.getElementById('cancelResumeEditorBtn').addEventListener('click', closeEditor);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeEditor();
    });

    function addRow(templateId, listId, counterKey) {
        var tpl = document.getElementById(templateId).content.cloneNode(true);
        var html = tpl.firstElementChild.outerHTML.split('__INDEX__').join(counters[counterKey]);
        document.getElementById(listId).insertAdjacentHTML('beforeend', html);
        counters[counterKey]++;
    }

    document.getElementById('editorAddWork').addEventListener('click', function () { addRow('editorWorkRowTemplate', 'editorWorkList', 'experiences'); });
    document.getElementById('editorAddProject').addEventListener('click', function () { addRow('editorProjectRowTemplate', 'editorProjectList', 'experiences'); });
    document.getElementById('editorAddCert').addEventListener('click', function () { addRow('editorCertRowTemplate', 'editorCertList', 'certifications'); });

    form.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.skill-chip, .experience-row, .cert-row').remove();
        }
    });

    // Item 21 — "Ongoing" disables & clears the End date field for that row.
    form.addEventListener('change', function (e) {
        if (e.target.classList.contains('exp-ongoing-checkbox')) {
            var endDateInput = e.target.closest('.experience-row').querySelector('.exp-end-date');
            endDateInput.disabled = e.target.checked;
            if (e.target.checked) endDateInput.value = '';
        }
    });

    /* ---- skill search (same pattern as the resume builder wizard) ---- */
    var skillSearchInput = document.getElementById('editorSkillSearchInput');
    var skillResults = document.getElementById('editorSkillSearchResults');
    var skillSearchTimeout;

    skillSearchInput.addEventListener('input', function () {
        clearTimeout(skillSearchTimeout);
        var q = this.value.trim();

        if (q.length < 2) {
            skillResults.classList.add('hidden');
            skillResults.innerHTML = '';
            return;
        }

        skillSearchTimeout = setTimeout(function () {
            fetch('{{ route('skills.search') }}?q=' + encodeURIComponent(q))
                .then(function (res) { return res.json(); })
                .then(function (skills) { renderSkillResults(skills, q); })
                .catch(function () { skillResults.classList.add('hidden'); });
        }, 250);
    });

    function alreadyAddedSkillNames() {
        return Array.from(document.querySelectorAll('#editorSkillsList .skill-name-display'))
            .map(function (el) { return el.textContent.trim().toLowerCase(); });
    }

    // Item 22 — a skill picked from the master list already has a real
    // category; a brand-new one needs the alumnus to pick one before it's
    // added, since nothing in the system knows what it is yet.
    var SKILL_CATEGORIES = @json(\App\Models\Skill::CATEGORIES);

    function renderSkillResults(skills, query) {
        skillResults.innerHTML = '';
        var added = alreadyAddedSkillNames();
        var hasExactMatch = false;

        skills.forEach(function (skill) {
            if (skill.skill_name.toLowerCase() === query.toLowerCase()) hasExactMatch = true;
            if (added.indexOf(skill.skill_name.toLowerCase()) !== -1) return;

            var item = document.createElement('div');
            item.className = 'px-3 py-2 text-sm hover:bg-blue-50 cursor-pointer';
            item.textContent = skill.skill_name;
            item.addEventListener('click', function () {
                addSkillChip(skill.skill_name, skill.skill_category);
                closeSkillResults();
            });
            skillResults.appendChild(item);
        });

        if (!hasExactMatch && added.indexOf(query.toLowerCase()) === -1) {
            var addNewWrap = document.createElement('div');
            addNewWrap.className = 'px-3 py-2 border-t border-gray-100 flex items-center gap-2 flex-wrap';

            var label = document.createElement('span');
            label.className = 'text-sm text-[#1D46A4] font-medium';
            label.textContent = 'Add "' + query + '" as:';
            addNewWrap.appendChild(label);

            var categorySelect = document.createElement('select');
            categorySelect.className = 'text-xs border border-gray-300 rounded px-2 py-1';
            Object.keys(SKILL_CATEGORIES).forEach(function (key) {
                var opt = document.createElement('option');
                opt.value = key;
                opt.textContent = SKILL_CATEGORIES[key];
                categorySelect.appendChild(opt);
            });
            addNewWrap.appendChild(categorySelect);

            var addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'text-xs font-bold bg-[#1D46A4] text-white rounded px-3 py-1 hover:bg-[#163a82]';
            addBtn.textContent = 'Add';
            addBtn.addEventListener('click', function () {
                addSkillChip(query, categorySelect.value);
                closeSkillResults();
            });
            addNewWrap.appendChild(addBtn);

            skillResults.appendChild(addNewWrap);
        }

        skillResults.classList.toggle('hidden', skillResults.children.length === 0);
    }

    function closeSkillResults() {
        skillResults.classList.add('hidden');
        skillResults.innerHTML = '';
        skillSearchInput.value = '';
    }

    function addSkillChip(name, category) {
        var idx = counters.skills++;

        var chip = document.createElement('span');
        chip.className = 'skill-chip flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full pl-3 pr-1 py-1';

        var span = document.createElement('span');
        span.className = 'skill-name-display';
        span.textContent = name;

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'skills[' + idx + '][name]';
        hidden.value = name;

        var hiddenCategory = document.createElement('input');
        hiddenCategory.type = 'hidden';
        hiddenCategory.name = 'skills[' + idx + '][category]';
        hiddenCategory.value = category || 'domain';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'remove-row text-red-700 px-1';
        btn.innerHTML = '&times;';

        chip.appendChild(span);
        chip.appendChild(hidden);
        chip.appendChild(hiddenCategory);
        chip.appendChild(btn);
        document.getElementById('editorSkillsList').appendChild(chip);
    }

    document.addEventListener('click', function (e) {
        if (e.target !== skillSearchInput && !skillResults.contains(e.target)) {
            skillResults.classList.add('hidden');
        }
    });

    /* ---- save (AJAX to the same endpoint the wizard uses) ---- */
    document.getElementById('saveResumeEditorBtn').addEventListener('click', function () {
        document.querySelectorAll('#editorSkillsList .skill-chip').forEach(function (chip) {
            var display = chip.querySelector('.skill-name-display');
            if (!display || !display.textContent.trim()) chip.remove();
        });
        document.querySelectorAll('.experience-row').forEach(function (row) {
            var title = row.querySelector('input[name*="[job_title]"]');
            if (!title.value.trim()) row.remove();
        });
        document.querySelectorAll('.cert-row').forEach(function (row) {
            var name = row.querySelector('input[name*="[certification_name]"]');
            if (!name.value.trim()) row.remove();
        });

        statusEl.textContent = 'Saving...';

        fetch('{{ route('resume.save') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        })
        .then(function (res) {
            return res.text().then(function (text) {
                if (!res.ok) {
                    console.error('Status:', res.status, 'Body:', text);
                    throw new Error('Save failed with status ' + res.status);
                }
                return JSON.parse(text);
            });
        })
        .then(function () {
            statusEl.textContent = 'Saved!';
            setTimeout(function () {
                statusEl.textContent = '';
                closeEditor();
            }, 600);
        })
        .catch(function (err) {
            console.error(err);
            statusEl.textContent = '';
            alert('Could not save your resume. Please check your connection and try again.');
        });
    });

    /* ---- import: prefill from an uploaded PDF, nothing saved yet (item 23) ---- */
    function addExperienceRowWithData(exp) {
        var isWork = exp.type === 'work';
        addRow(isWork ? 'editorWorkRowTemplate' : 'editorProjectRowTemplate', isWork ? 'editorWorkList' : 'editorProjectList', 'experiences');
        var list = document.getElementById(isWork ? 'editorWorkList' : 'editorProjectList');
        var row = list.lastElementChild;
        row.querySelector('[name$="[job_title]"]').value = exp.job_title || '';
        row.querySelector('[name$="[job_description]"]').value = exp.job_description || '';
        if (isWork && exp.industry_id) row.querySelector('[name$="[industry_id]"]').value = exp.industry_id;

        // Item 21 — the PDF parser only ever extracts a duration in months,
        // never exact dates, so that's carried through via the hidden
        // fallback field until the alumnus fills in real dates themselves.
        row.querySelector('[name$="[duration_months]"]').value = exp.duration_months || '';
        if (exp.start_date) row.querySelector('[name$="[start_date]"]').value = exp.start_date;
        if (exp.end_date) row.querySelector('[name$="[end_date]"]').value = exp.end_date;
        if (exp.is_ongoing) {
            var ongoingBox = row.querySelector('.exp-ongoing-checkbox');
            ongoingBox.checked = true;
            ongoingBox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function addCertRowWithData(cert) {
        addRow('editorCertRowTemplate', 'editorCertList', 'certifications');
        var row = document.getElementById('editorCertList').lastElementChild;
        row.querySelector('input[value="' + cert.certification_type + '"]').checked = true;
        row.querySelector('[name$="[certification_name]"]').value = cert.certification_name || '';
        row.querySelector('[name$="[certification_from]"]').value = cert.certification_from || '';
        row.querySelector('[name$="[certification_date]"]').value = cert.certification_date || '';
    }

    var editorImportBtn = document.getElementById('editorImportResumeBtn');
    var editorImportFile = document.getElementById('editorImportResumeFile');
    var editorImportStatus = document.getElementById('editorImportResumeStatus');
    var editorResumeDropzone = document.getElementById('editorResumeDropzone');
    var editorResumeDropzoneText = document.getElementById('editorResumeDropzoneText');

    function updateEditorResumeDropzoneText() {
        var has = editorImportFile.files.length > 0;
        editorResumeDropzoneText.textContent = has ? editorImportFile.files[0].name : 'Drag & drop or upload files here';
        editorResumeDropzoneText.classList.toggle('text-gray-500', !has);
        editorResumeDropzoneText.classList.toggle('text-[#0E0F3B]', has);
        editorResumeDropzoneText.classList.toggle('font-semibold', has);
    }
    editorImportFile.addEventListener('change', updateEditorResumeDropzoneText);

    ['dragenter', 'dragover'].forEach(function (ev) {
        editorResumeDropzone.addEventListener(ev, function (e) {
            e.preventDefault(); e.stopPropagation();
            editorResumeDropzone.classList.add('bg-gray-100');
        });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
        editorResumeDropzone.addEventListener(ev, function (e) {
            e.preventDefault(); e.stopPropagation();
            editorResumeDropzone.classList.remove('bg-gray-100');
        });
    });
    editorResumeDropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length > 0) {
            editorImportFile.files = e.dataTransfer.files;
            updateEditorResumeDropzoneText();
        }
    });

    editorImportBtn.addEventListener('click', function () {
        if (!editorImportFile.files.length) {
            editorImportStatus.textContent = 'Choose a PDF file first.';
            return;
        }

        editorImportBtn.disabled = true;
        editorImportStatus.textContent = 'Reading your PDF...';

        var formData = new FormData();
        formData.append('resume_file', editorImportFile.files[0]);
        formData.append('_token', form.querySelector('input[name="_token"]').value);

        fetch('{{ route('resume.import') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        })
        .then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) throw new Error(data.message || 'Import failed');
                return data;
            });
        })
        .then(function (data) {
            var summaryEl = form.querySelector('[name="resume_summary"]');
            if (data.resume_summary) summaryEl.value = data.resume_summary;

            var linkedinInput = form.querySelector('[name="linkedin_url"]');
            if (data.linkedin_url) linkedinInput.value = data.linkedin_url;

            document.getElementById('editorSkillsList').innerHTML = '';
            counters.skills = 0;
            (data.skills || []).forEach(function (s) { addSkillChip(s.name); });

            document.getElementById('editorWorkList').innerHTML = '';
            document.getElementById('editorProjectList').innerHTML = '';
            counters.experiences = 0;
            (data.experiences || []).forEach(addExperienceRowWithData);

            document.getElementById('editorCertList').innerHTML = '';
            counters.certifications = 0;
            (data.certifications || []).forEach(addCertRowWithData);

            var found = (data.skills || []).length + (data.experiences || []).length + (data.certifications || []).length;
            var method = data.parsed_with === 'ai' ? ' (AI-assisted)' : '';
            editorImportStatus.textContent = found > 0
                ? 'Imported' + method + ' — review the fields below, then save.'
                : 'Imported, but couldn\'t find much structured data — please fill in manually.';
        })
        .catch(function (err) {
            console.error(err);
            editorImportStatus.textContent = err.message || 'Could not read that PDF.';
        })
        .finally(function () {
            editorImportBtn.disabled = false;
        });
    });
})();
</script>
