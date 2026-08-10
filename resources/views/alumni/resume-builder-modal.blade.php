{{--
    The step-by-step resume builder wizard, moved here from profile.blade.php
    and wrapped in a popup — shown on Edit Profile via the "Create Resume"
    button while the resume is incomplete. Same fields/endpoint as
    resume-editor-modal.blade.php (the "Edit Resume" popup shown once
    complete); expects $user, $resumeData, $industries.
--}}
<div id="resumeBuilderOverlay" class="hidden fixed inset-0 z-[200] bg-black/60 flex items-start justify-center overflow-y-auto py-8 px-4">
    <div id="resumeBuilderPanel" class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl relative p-8 md:p-12">

        {{-- ===== Import an existing resume ===== --}}
        <div class="mb-6 p-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            <p class="text-sm font-medium text-gray-700 mb-2">
                <i class="fa-solid fa-file-import text-red-800 mr-1"></i>
                Already have a resume? Import it to fill in the fields below automatically — you can still edit everything before saving.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <input type="file" id="importResumeFile" accept="application/pdf"
                    class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-red-800 file:text-white file:text-xs file:font-medium hover:file:bg-red-900">
                <button type="button" id="importResumeBtn"
                    class="text-sm font-medium bg-[#0E0F3B] text-white rounded px-4 py-1.5 hover:bg-[#1D46A4] disabled:opacity-50">
                    Import from PDF
                </button>
                <span id="importResumeStatus" class="text-xs text-gray-500"></span>
            </div>
        </div>

        {{-- ===== Header + progress ===== --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-2xl font-bold text-gray-900">Build Your Resume</h1>
                <div class="flex items-center gap-3">
                    <span id="step-label" class="text-sm font-medium text-red-800">Step 1 of 4</span>
                    <button type="button" id="closeResumeBuilderBtn" class="text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
            </div>
            {{-- Wizard progress only — how far through the 4 steps you are.
                 Moves with Continue/Back, nothing else. --}}
            <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                <div id="step-progress-bar" class="h-full bg-red-800 rounded-full transition-all duration-300"
                    style="width:0%"></div>
            </div>

            {{-- Actual resume completeness — a different thing from the bar
                 above, only updates after a real save (Save draft/Submit/Import). --}}
            <div class="flex items-center justify-between mt-4">
                <span class="text-xs font-bold text-gray-600 uppercase tracking-wide">Resume completeness</span>
                <span class="text-xs font-medium text-gray-600"><span id="completeness-label">{{ $resumeData['resume_completeness'] ?? 0 }}</span>% saved</span>
            </div>
            <ul id="completeness-breakdown" class="mt-2 space-y-1 text-xs text-gray-500">
                @foreach($user->alumnus->completenessBreakdown() as $item)
                    <li data-key="{{ $item['key'] }}" class="flex items-center gap-2">
                        <i class="fa-solid {{ $item['done'] ? 'fa-circle-check text-green-600' : 'fa-circle text-gray-300' }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="flex gap-4 mt-3 text-sm">
                <button type="button" class="step-tab font-medium text-red-800" data-step="0">1. Summary</button>
                <button type="button" class="step-tab font-medium text-gray-400" data-step="1">2. Skills</button>
                <button type="button" class="step-tab font-medium text-gray-400" data-step="2">3. Experience</button>
                <button type="button" class="step-tab font-medium text-gray-400" data-step="3">4.
                    Certifications</button>
            </div>
        </div>

        <form id="resume-form" class="bg-white rounded-lg shadow p-6">
            @csrf

            {{-- ===== Step 0: Summary ===== --}}
            <section class="wizard-step" data-step="0">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Professional Summary</h2>
                <p class="text-sm text-gray-500 mb-4">A short description of who you are and what you're looking for.
                </p>

                <textarea name="resume_summary" id="resume_summary" rows="4" maxlength="500"
                    placeholder="e.g. Recent BSIT graduate specializing in full-stack web development..."
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">{{ old('resume_summary', $resumeData['resume_summary'] ?? '') }}</textarea>
                <p class="text-xs text-gray-400 text-right mt-1"><span id="summary-count">0</span>/500</p>

                <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">LinkedIn</label>
                <input type="url" name="linkedin_url" placeholder="linkedin.com/in/..."
                    value="{{ old('linkedin_url', $resumeData['linkedin_url'] ?? '') }}"
                    class="w-full sm:w-1/2 rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
            </section>

            {{-- ===== Step 1: Skills ===== --}}
            <section class="wizard-step hidden" data-step="1">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Skills</h2>
                <p class="text-sm text-gray-500 mb-4">Search for skills relevant to the kind of work you're looking for.
                </p>

                <div class="relative mb-4 w-full sm:w-1/2">
                    <input type="text" id="skill-search-input" autocomplete="off"
                        placeholder="Search for a skill (e.g. Laravel, Excel, Lesson Planning)..."
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                    <div id="skill-search-results"
                        class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg max-h-56 overflow-y-auto">
                    </div>
                </div>

                <div id="skills-list" class="flex flex-wrap gap-2 mb-4">
                    @foreach(($resumeData['skills'] ?? []) as $i => $skill)
                        <div
                            class="skill-chip flex items-center gap-1 pl-3 pr-1 py-1 rounded-full border border-gray-300 bg-gray-50 text-sm">
                            <span class="skill-name-display">{{ $skill['name'] }}</span>
                            <input type="hidden" name="skills[{{ $i }}][name]" value="{{ $skill['name'] }}">
                            <button type="button" class="remove-row text-red-700 px-1">&times;</button>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ===== Step 2: Experience & Projects ===== --}}
            <section class="wizard-step hidden" data-step="2">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Experience &amp; Projects</h2>
                <p class="text-sm text-gray-500 mb-4">Add work experience (jobs, internships, OJT) and projects — same
                    form, just pick the type.</p>

                <div id="experience-list">
                    @foreach(($resumeData['experiences'] ?? []) as $i => $exp)
                        <div class="experience-row mb-3 p-3 border border-gray-200 rounded">
                            <div class="flex gap-4 mb-2 text-sm">
                                <label class="flex items-center gap-1"><input type="radio"
                                        name="experiences[{{ $i }}][type]" value="work" @checked($exp['type'] === 'work')>
                                    Work</label>
                                <label class="flex items-center gap-1"><input type="radio"
                                        name="experiences[{{ $i }}][type]" value="project"
                                        @checked($exp['type'] === 'project')> Project</label>
                            </div>

                            <input type="text" name="experiences[{{ $i }}][job_title]" value="{{ $exp['job_title'] }}"
                                placeholder="Job title / Project title"
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                                <input type="number" name="experiences[{{ $i }}][duration_months]"
                                    value="{{ $exp['duration_months'] }}" min="0" max="600" placeholder="Duration (months)"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                                <select name="experiences[{{ $i }}][industry_id]"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                                    <option value="">Industry (optional)</option>
                                    @foreach($industries as $industry)
                                        <option value="{{ $industry->industry_id }}" @selected(($exp['industry_id'] ?? null) == $industry->industry_id)>{{ $industry->industry_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <textarea name="experiences[{{ $i }}][job_description]" rows="3"
                                placeholder="What did you do? Be specific."
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">{{ $exp['job_description'] }}</textarea>

                            <button type="button" class="remove-row text-sm text-red-700">Remove</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-experience"
                    class="text-sm font-medium border border-red-800 text-red-800 rounded px-3 py-1.5 hover:bg-red-50">
                    + Add work or project
                </button>
            </section>

            {{-- ===== Step 3: Certifications & Seminars ===== --}}
            <section class="wizard-step hidden" data-step="3">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Certifications &amp; Seminars</h2>
                <p class="text-sm text-gray-500 mb-4">Board exams, licenses, certifications, seminars, and trainings
                    you've attended.</p>

                <div id="cert-list">
                    @foreach(($resumeData['certifications'] ?? []) as $i => $cert)
                        <div class="cert-row mb-3 p-3 border border-gray-200 rounded">
                            <div class="flex gap-4 mb-2 text-sm">
                                <label class="flex items-center gap-1"><input type="radio"
                                        name="certifications[{{ $i }}][certification_type]" value="certification"
                                        @checked($cert['certification_type'] === 'certification')> Certification</label>
                                <label class="flex items-center gap-1"><input type="radio"
                                        name="certifications[{{ $i }}][certification_type]" value="seminar"
                                        @checked($cert['certification_type'] === 'seminar')> Seminar</label>
                                <label class="flex items-center gap-1"><input type="radio"
                                        name="certifications[{{ $i }}][certification_type]" value="training"
                                        @checked($cert['certification_type'] === 'training')> Training</label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                                <input type="text" name="certifications[{{ $i }}][certification_name]"
                                    value="{{ $cert['certification_name'] }}"
                                    placeholder="Title (e.g. AWS Certified Cloud Practitioner)"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                                <input type="text" name="certifications[{{ $i }}][certification_from]"
                                    value="{{ $cert['certification_from'] }}" placeholder="Issuing organization / speaker"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                            </div>

                            <input type="date" name="certifications[{{ $i }}][certification_date]"
                                value="{{ $cert['certification_date'] }}"
                                class="w-40 rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">

                            <button type="button" class="remove-row text-sm text-red-700">Remove</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-cert"
                    class="text-sm font-medium border border-red-800 text-red-800 rounded px-3 py-1.5 hover:bg-red-50">
                    + Add certification or seminar
                </button>
            </section>

            {{-- ===== Nav buttons ===== --}}
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <button type="button" id="btn-back"
                    class="hidden text-sm font-medium border border-gray-300 text-gray-700 rounded px-4 py-2">Back</button>
                <div class="ml-auto flex items-center gap-3">
                    <span id="draft-status" class="text-xs text-gray-500"></span>
                    <button type="button" id="btn-draft"
                        class="text-sm font-medium border border-gray-300 text-gray-700 rounded px-4 py-2">Save
                        draft</button>
                    <button type="button" id="btn-next"
                        class="text-sm font-medium bg-red-800 text-white rounded px-4 py-2 hover:bg-red-900">Continue</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===== templates JS clones to add new rows ===== --}}
<template id="skill-row-template">
    <div class="skill-chip flex items-center gap-1 pl-3 pr-1 py-1 rounded-full border border-gray-300 text-sm">
        <input type="text" name="skills[__INDEX__][name]" placeholder="e.g. Laravel"
            class="bg-transparent focus:outline-none w-28">
        <button type="button" class="remove-row text-red-700 px-1">&times;</button>
    </div>
</template>

<template id="experience-row-template">
    <div class="experience-row mb-3 p-3 border border-gray-200 rounded">
        <div class="flex gap-4 mb-2 text-sm">
            <label class="flex items-center gap-1"><input type="radio" name="experiences[__INDEX__][type]"
                    value="work" checked> Work</label>
            <label class="flex items-center gap-1"><input type="radio" name="experiences[__INDEX__][type]"
                    value="project"> Project</label>
        </div>
        <input type="text" name="experiences[__INDEX__][job_title]" placeholder="Job title / Project title"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
            <input type="number" name="experiences[__INDEX__][duration_months]" min="0" max="600"
                placeholder="Duration (months)"
                class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
            <select name="experiences[__INDEX__][industry_id]"
                class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                <option value="">Industry (optional)</option>
                @foreach($industries as $industry)
                    <option value="{{ $industry->industry_id }}">{{ $industry->industry_name }}</option>
                @endforeach
            </select>
        </div>
        <textarea name="experiences[__INDEX__][job_description]" rows="3" placeholder="What did you do?"
            class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800"></textarea>
        <button type="button" class="remove-row text-sm text-red-700">Remove</button>
    </div>
</template>

<template id="cert-row-template">
    <div class="cert-row mb-3 p-3 border border-gray-200 rounded">
        <div class="flex gap-4 mb-2 text-sm">
            <label class="flex items-center gap-1"><input type="radio"
                    name="certifications[__INDEX__][certification_type]" value="certification" checked>
                Certification</label>
            <label class="flex items-center gap-1"><input type="radio"
                    name="certifications[__INDEX__][certification_type]" value="seminar"> Seminar</label>
            <label class="flex items-center gap-1"><input type="radio"
                    name="certifications[__INDEX__][certification_type]" value="training"> Training</label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
            <input type="text" name="certifications[__INDEX__][certification_name]" placeholder="Title"
                class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
            <input type="text" name="certifications[__INDEX__][certification_from]"
                placeholder="Issuing organization / speaker"
                class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
        </div>
        <input type="date" name="certifications[__INDEX__][certification_date]"
            class="w-40 rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">
        <button type="button" class="remove-row text-sm text-red-700">Remove</button>
    </div>
</template>

<script>
(function () {

    'use strict';

    var currentStep = 0;
    var totalSteps = 4;

    var overlay = document.getElementById('resumeBuilderOverlay');

    var counters = {
        skills: document.querySelectorAll('#skills-list .skill-chip').length,
        experiences: document.querySelectorAll('#experience-list .experience-row').length,
        certifications: document.querySelectorAll('#cert-list .cert-row').length,
    };

    var form = document.getElementById('resume-form');
    var steps = document.querySelectorAll('.wizard-step');
    var tabs = document.querySelectorAll('.step-tab');
    var btnNext = document.getElementById('btn-next');
    var btnBack = document.getElementById('btn-back');
    var btnDraft = document.getElementById('btn-draft');

    document.getElementById('openResumeEditorBtn').addEventListener('click', function () {
        overlay.classList.remove('hidden');
    });

    function closeBuilder() {
        overlay.classList.add('hidden');
    }

    document.getElementById('closeResumeBuilderBtn').addEventListener('click', closeBuilder);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeBuilder();
    });

    function showStep(idx) {
        currentStep = idx;
        steps.forEach(function (s) { s.classList.toggle('hidden', Number(s.dataset.step) !== idx); });
        tabs.forEach(function (t) { t.className = 'step-tab font-medium ' + (Number(t.dataset.step) <= idx ? 'text-red-800' : 'text-gray-400'); });
        btnBack.classList.toggle('hidden', idx === 0);
        btnNext.textContent = idx === totalSteps - 1 ? 'Submit resume' : 'Continue';

        // Pure wizard-progress indicator — moves forward/backward with
        // Continue/Back only, never tied to what's typed or saved. The
        // separate "Resume completeness" number below only ever comes from
        // the server (see submitForm) — the two are deliberately not the
        // same thing.
        document.getElementById('step-label').textContent = 'Step ' + (idx + 1) + ' of ' + totalSteps;
        document.getElementById('step-progress-bar').style.width = Math.round((idx / (totalSteps - 1)) * 100) + '%';
    }

    tabs.forEach(function (t) { t.addEventListener('click', function () { showStep(Number(t.dataset.step)); }); });
    btnBack.addEventListener('click', function () { if (currentStep > 0) showStep(currentStep - 1); });
    btnNext.addEventListener('click', function () {
        if (currentStep < totalSteps - 1) { showStep(currentStep + 1); } else { submitForm(true); }
    });
    btnDraft.addEventListener('click', function () { submitForm(false); });

    function addRow(templateId, listId, counterKey) {
        var tpl = document.getElementById(templateId).content.cloneNode(true);
        var html = tpl.firstElementChild.outerHTML.split('__INDEX__').join(counters[counterKey]);
        document.getElementById(listId).insertAdjacentHTML('beforeend', html);
        counters[counterKey]++;
    }

    document.getElementById('add-experience').addEventListener('click', function () { addRow('experience-row-template', 'experience-list', 'experiences'); });
    document.getElementById('add-cert').addEventListener('click', function () { addRow('cert-row-template', 'cert-list', 'certifications'); });

    form.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.skill-chip, .experience-row, .cert-row').remove();
        }
    });

    // Character counter only — purely informational, doesn't touch the
    // completeness bar. Keeping this live is fine since it's not a score.
    document.getElementById('resume_summary').addEventListener('input', function () {
        document.getElementById('summary-count').textContent = this.value.length;
    });

    /* ---- skill search (unchanged from before) ---- */
    var skillSearchInput = document.getElementById('skill-search-input');
    var skillResults = document.getElementById('skill-search-results');
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
        return Array.from(document.querySelectorAll('#skills-list .skill-name-display'))
            .map(function (el) { return el.textContent.trim().toLowerCase(); });
    }

    function renderSkillResults(skills, query) {
        skillResults.innerHTML = '';
        var added = alreadyAddedSkillNames();
        var hasExactMatch = false;

        skills.forEach(function (skill) {
            if (skill.skill_name.toLowerCase() === query.toLowerCase()) hasExactMatch = true;
            if (added.indexOf(skill.skill_name.toLowerCase()) !== -1) return;

            var item = document.createElement('div');
            item.className = 'px-3 py-2 text-sm hover:bg-red-50 cursor-pointer';
            item.textContent = skill.skill_name;
            item.addEventListener('click', function () {
                addSkillChip(skill.skill_name);
                closeSkillResults();
            });
            skillResults.appendChild(item);
        });

        if (!hasExactMatch && added.indexOf(query.toLowerCase()) === -1) {
            var addNew = document.createElement('div');
            addNew.className = 'px-3 py-2 text-sm text-red-800 font-medium hover:bg-red-50 cursor-pointer border-t border-gray-100';
            addNew.textContent = '+ Add "' + query + '" as a new skill';
            addNew.addEventListener('click', function () {
                addSkillChip(query);
                closeSkillResults();
            });
            skillResults.appendChild(addNew);
        }

        skillResults.classList.toggle('hidden', skillResults.children.length === 0);
    }

    function closeSkillResults() {
        skillResults.classList.add('hidden');
        skillResults.innerHTML = '';
        skillSearchInput.value = '';
    }

    function addSkillChip(name) {
        var idx = counters.skills++;

        var chip = document.createElement('div');
        chip.className = 'skill-chip flex items-center gap-1 pl-3 pr-1 py-1 rounded-full border border-gray-300 bg-gray-50 text-sm';

        var span = document.createElement('span');
        span.className = 'skill-name-display';
        span.textContent = name;

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'skills[' + idx + '][name]';
        hidden.value = name;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'remove-row text-red-700 px-1';
        btn.innerHTML = '&times;';

        chip.appendChild(span);
        chip.appendChild(hidden);
        chip.appendChild(btn);
        document.getElementById('skills-list').appendChild(chip);
    }

    document.addEventListener('click', function (e) {
        if (e.target !== skillSearchInput && !skillResults.contains(e.target)) {
            skillResults.classList.add('hidden');
        }
    });

    /* ---- submit: this is the ONLY place resume completeness updates ---- */
    function submitForm(isFinal) {
        document.querySelectorAll('#skills-list .skill-chip').forEach(function (chip) {
            var display = chip.querySelector('.skill-name-display');
            if (!display || !display.textContent.trim()) chip.remove();
        });
        document.querySelectorAll('#experience-list .experience-row').forEach(function (row) {
            var title = row.querySelector('input[name*="[job_title]"]');
            if (!title.value.trim()) row.remove();
        });
        document.querySelectorAll('#cert-list .cert-row').forEach(function (row) {
            var name = row.querySelector('input[name*="[certification_name]"]');
            if (!name.value.trim()) row.remove();
        });

        var formData = new FormData(form);
        formData.append('is_final', isFinal ? '1' : '0');

        var draftStatus = document.getElementById('draft-status');
        if (!isFinal) {
            draftStatus.textContent = 'Saving...';
            btnDraft.disabled = true;
        }

        fetch('{{ route('resume.save') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
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
        .then(function (data) {
            // This number only ever moves here — after the server confirms
            // what was actually saved, never from client-side guessing.
            // The step bar above is separate and unaffected by this.
            document.getElementById('completeness-label').textContent = data.resume_completeness;

            (data.breakdown || []).forEach(function (item) {
                var li = document.querySelector('#completeness-breakdown li[data-key="' + item.key + '"]');
                if (!li) return;
                li.querySelector('i').className = 'fa-solid ' + (item.done ? 'fa-circle-check text-green-600' : 'fa-circle text-gray-300');
            });

            // On final submit, go to the profile page — it'll show the
            // finished resume if this reached 100%, or a prompt to keep
            // building if it didn't.
            if (isFinal) {
                window.location.href = "{{ route('user.profile') }}";
            } else {
                // Incomplete sections are expected here — a draft is
                // allowed to be partial. This just confirms the save
                // actually happened, which was otherwise invisible.
                draftStatus.textContent = 'Draft saved — ' + data.resume_completeness + '% complete.';
                btnDraft.disabled = false;
                setTimeout(function () { draftStatus.textContent = ''; }, 4000);
            }
        })
        .catch(function (err) {
            if (!isFinal) {
                draftStatus.textContent = 'Could not save draft.';
                btnDraft.disabled = false;
            }
            console.error(err);
            alert('Could not save your resume. Please check your connection and try again.');
        });
    }

    /* ---- import: prefill from an uploaded PDF, nothing saved yet ---- */
    function addExperienceRowWithData(exp) {
        addRow('experience-row-template', 'experience-list', 'experiences');
        var row = document.getElementById('experience-list').lastElementChild;
        row.querySelector('input[value="' + exp.type + '"]').checked = true;
        row.querySelector('[name$="[job_title]"]').value = exp.job_title || '';
        row.querySelector('[name$="[job_description]"]').value = exp.job_description || '';
        row.querySelector('[name$="[duration_months]"]').value = exp.duration_months || '';
        if (exp.industry_id) row.querySelector('[name$="[industry_id]"]').value = exp.industry_id;
    }

    function addCertRowWithData(cert) {
        addRow('cert-row-template', 'cert-list', 'certifications');
        var row = document.getElementById('cert-list').lastElementChild;
        row.querySelector('input[value="' + cert.certification_type + '"]').checked = true;
        row.querySelector('[name$="[certification_name]"]').value = cert.certification_name || '';
        row.querySelector('[name$="[certification_from]"]').value = cert.certification_from || '';
        row.querySelector('[name$="[certification_date]"]').value = cert.certification_date || '';
    }

    var importBtn = document.getElementById('importResumeBtn');
    var importFile = document.getElementById('importResumeFile');
    var importStatus = document.getElementById('importResumeStatus');

    importBtn.addEventListener('click', function () {
        if (!importFile.files.length) {
            importStatus.textContent = 'Choose a PDF file first.';
            return;
        }

        importBtn.disabled = true;
        importStatus.textContent = 'Reading your PDF...';

        var formData = new FormData();
        formData.append('resume_file', importFile.files[0]);
        formData.append('_token', document.querySelector('#resume-form input[name="_token"]').value);

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
            document.getElementById('resume_summary').value = data.resume_summary || document.getElementById('resume_summary').value;
            document.getElementById('summary-count').textContent = document.getElementById('resume_summary').value.length;

            var linkedinInput = form.querySelector('[name="linkedin_url"]');
            if (data.linkedin_url) linkedinInput.value = data.linkedin_url;

            document.getElementById('skills-list').innerHTML = '';
            counters.skills = 0;
            (data.skills || []).forEach(function (s) { addSkillChip(s.name); });

            document.getElementById('experience-list').innerHTML = '';
            counters.experiences = 0;
            (data.experiences || []).forEach(addExperienceRowWithData);

            document.getElementById('cert-list').innerHTML = '';
            counters.certifications = 0;
            (data.certifications || []).forEach(addCertRowWithData);

            var found = (data.skills || []).length + (data.experiences || []).length + (data.certifications || []).length;
            var method = data.parsed_with === 'ai' ? ' (AI-assisted)' : '';
            importStatus.textContent = found > 0
                ? 'Imported' + method + ' — review the fields below, then save.'
                : 'Imported, but couldn\'t find much structured data — please fill in manually.';
            showStep(0);

            // Import only fills the form — it never saves anything, so the
            // completeness display would otherwise sit stale at whatever it
            // was before the import. Save as a draft right away so it
            // reflects what was actually imported.
            submitForm(false);
        })
        .catch(function (err) {
            console.error(err);
            importStatus.textContent = err.message || 'Could not read that PDF.';
        })
        .finally(function () {
            importBtn.disabled = false;
        });
    });

    showStep(0);
})();
</script>
