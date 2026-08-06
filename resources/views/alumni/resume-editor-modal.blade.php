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

        <form id="resumeEditorForm" class="p-8 md:p-12 pt-4">
            @csrf

            {{-- ===== Header (name/contact are read-only here; edited on this same page) ===== --}}
            <div class="text-center border-b-2 border-[#0E0F3B] pb-6 mb-6">
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
                        <p class="text-xs text-gray-500">Batch {{ $user->alumnus->alumnus_batch }}</p>
                    @endif
                </div>
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
                                <div class="flex justify-between items-baseline gap-2 mb-1">
                                    <input type="text" name="experiences[{{ $i }}][job_title]" value="{{ $exp['job_title'] }}"
                                        placeholder="Job title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                    <input type="number" name="experiences[{{ $i }}][duration_months]" value="{{ $exp['duration_months'] }}" min="0" max="600"
                                        placeholder="Months" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-1 w-24 text-right focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                </div>
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
                                <div class="flex justify-between items-baseline gap-2 mb-1">
                                    <input type="text" name="experiences[{{ $i }}][job_title]" value="{{ $exp['job_title'] }}"
                                        placeholder="Project title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                    <input type="number" name="experiences[{{ $i }}][duration_months]" value="{{ $exp['duration_months'] }}" min="0" max="600"
                                        placeholder="Months" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-1 w-24 text-right focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
                                </div>
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
        <div class="flex justify-between items-baseline gap-2 mb-1">
            <input type="text" name="experiences[__INDEX__][job_title]" placeholder="Job title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
            <input type="number" name="experiences[__INDEX__][duration_months]" min="0" max="600" placeholder="Months" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-1 w-24 text-right focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
        </div>
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
        <div class="flex justify-between items-baseline gap-2 mb-1">
            <input type="text" name="experiences[__INDEX__][job_title]" placeholder="Project title" class="font-semibold text-gray-900 border border-gray-300 rounded px-2 py-1 text-sm flex-1 focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
            <input type="number" name="experiences[__INDEX__][duration_months]" min="0" max="600" placeholder="Months" class="text-xs text-gray-500 border border-gray-300 rounded px-2 py-1 w-24 text-right focus:outline-none focus:ring-2 focus:ring-[#1D46A4]">
        </div>
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
                addSkillChip(skill.skill_name);
                closeSkillResults();
            });
            skillResults.appendChild(item);
        });

        if (!hasExactMatch && added.indexOf(query.toLowerCase()) === -1) {
            var addNew = document.createElement('div');
            addNew.className = 'px-3 py-2 text-sm text-[#1D46A4] font-medium hover:bg-blue-50 cursor-pointer border-t border-gray-100';
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

        var chip = document.createElement('span');
        chip.className = 'skill-chip flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full pl-3 pr-1 py-1';

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
})();
</script>
