<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Build Your Resume — PLV Alumnet</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50">

<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- ===== Header + progress ===== --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl font-bold text-gray-900">Build Your Resume</h1>
            <span class="text-sm font-medium text-red-800"><span id="completeness-label">0</span>% complete</span>
        </div>
        <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
            <div id="completeness-bar" class="h-full bg-red-800 rounded-full transition-all duration-300" style="width:0%"></div>
        </div>
        <div class="flex gap-4 mt-3 text-sm">
            <button type="button" class="step-tab font-medium text-red-800" data-step="0">1. Summary</button>
            <button type="button" class="step-tab font-medium text-gray-400" data-step="1">2. Skills</button>
            <button type="button" class="step-tab font-medium text-gray-400" data-step="2">3. Experience</button>
            <button type="button" class="step-tab font-medium text-gray-400" data-step="3">4. Certifications</button>
        </div>
    </div>

    <form id="resume-form" class="bg-white rounded-lg shadow p-6">
        @csrf

        {{-- ===== Step 0: Summary ===== --}}
        <section class="wizard-step" data-step="0">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Professional Summary</h2>
            <p class="text-sm text-gray-500 mb-4">A short description of who you are and what you're looking for.</p>

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
            <p class="text-sm text-gray-500 mb-4">List the skills relevant to the kind of work you're looking for.</p>

            <div id="skills-list" class="flex flex-wrap gap-2 mb-4">
                @foreach(($resumeData['skills'] ?? []) as $i => $skill)
                    <div class="skill-chip flex items-center gap-1 pl-3 pr-1 py-1 rounded-full border border-gray-300 text-sm">
                        <input type="text" name="skills[{{ $i }}][name]" value="{{ $skill['name'] }}" placeholder="e.g. Laravel"
                               class="bg-transparent focus:outline-none w-28">
                        <button type="button" class="remove-row text-red-700 px-1">&times;</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-skill" class="text-sm font-medium border border-red-800 text-red-800 rounded px-3 py-1.5 hover:bg-red-50">
                + Add skill
            </button>
        </section>

        {{-- ===== Step 2: Experience & Projects ===== --}}
        <section class="wizard-step hidden" data-step="2">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Experience &amp; Projects</h2>
            <p class="text-sm text-gray-500 mb-4">Add work experience (jobs, internships, OJT) and projects — same form, just pick the type.</p>

            <div id="experience-list">
                @foreach(($resumeData['experiences'] ?? []) as $i => $exp)
                    <div class="experience-row mb-3 p-3 border border-gray-200 rounded">
                        <div class="flex gap-4 mb-2 text-sm">
                            <label class="flex items-center gap-1"><input type="radio" name="experiences[{{ $i }}][type]" value="work" @checked($exp['type'] === 'work')> Work</label>
                            <label class="flex items-center gap-1"><input type="radio" name="experiences[{{ $i }}][type]" value="project" @checked($exp['type'] === 'project')> Project</label>
                        </div>

                        <input type="text" name="experiences[{{ $i }}][job_title]" value="{{ $exp['job_title'] }}"
                               placeholder="Job title / Project title"
                               class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                            <input type="number" name="experiences[{{ $i }}][duration_months]" value="{{ $exp['duration_months'] }}"
                                   min="0" max="600" placeholder="Duration (months)"
                                   class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                            <select name="experiences[{{ $i }}][industry_id]"
                                    class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                                <option value="">Industry (optional)</option>
                                @foreach($industries as $industry)
                                    <option value="{{ $industry->industry_id }}" @selected(($exp['industry_id'] ?? null) == $industry->industry_id)>{{ $industry->industry_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <textarea name="experiences[{{ $i }}][job_description]" rows="3" placeholder="What did you do? Be specific."
                                  class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">{{ $exp['job_description'] }}</textarea>

                        <button type="button" class="remove-row text-sm text-red-700">Remove</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-experience" class="text-sm font-medium border border-red-800 text-red-800 rounded px-3 py-1.5 hover:bg-red-50">
                + Add work or project
            </button>
        </section>

        {{-- ===== Step 3: Certifications & Seminars ===== --}}
        <section class="wizard-step hidden" data-step="3">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Certifications &amp; Seminars</h2>
            <p class="text-sm text-gray-500 mb-4">Board exams, licenses, certifications, seminars, and trainings you've attended.</p>

            <div id="cert-list">
                @foreach(($resumeData['certifications'] ?? []) as $i => $cert)
                    <div class="cert-row mb-3 p-3 border border-gray-200 rounded">
                        <div class="flex gap-4 mb-2 text-sm">
                            <label class="flex items-center gap-1"><input type="radio" name="certifications[{{ $i }}][certification_type]" value="certification" @checked($cert['certification_type'] === 'certification')> Certification</label>
                            <label class="flex items-center gap-1"><input type="radio" name="certifications[{{ $i }}][certification_type]" value="seminar" @checked($cert['certification_type'] === 'seminar')> Seminar</label>
                            <label class="flex items-center gap-1"><input type="radio" name="certifications[{{ $i }}][certification_type]" value="training" @checked($cert['certification_type'] === 'training')> Training</label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                            <input type="text" name="certifications[{{ $i }}][certification_name]" value="{{ $cert['certification_name'] }}"
                                   placeholder="Title (e.g. AWS Certified Cloud Practitioner)"
                                   class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                            <input type="text" name="certifications[{{ $i }}][certification_from]" value="{{ $cert['certification_from'] }}"
                                   placeholder="Issuing organization / speaker"
                                   class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                        </div>

                        <input type="date" name="certifications[{{ $i }}][certification_date]" value="{{ $cert['certification_date'] }}"
                               class="w-40 rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">

                        <button type="button" class="remove-row text-sm text-red-700">Remove</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-cert" class="text-sm font-medium border border-red-800 text-red-800 rounded px-3 py-1.5 hover:bg-red-50">
                + Add certification or seminar
            </button>
        </section>

        {{-- ===== Nav buttons ===== --}}
        <div class="flex justify-between mt-6 pt-4 border-t border-gray-200">
            <button type="button" id="btn-back" class="hidden text-sm font-medium border border-gray-300 text-gray-700 rounded px-4 py-2">Back</button>
            <div class="ml-auto flex gap-2">
                <button type="button" id="btn-draft" class="text-sm font-medium border border-gray-300 text-gray-700 rounded px-4 py-2">Save draft</button>
                <button type="button" id="btn-next" class="text-sm font-medium bg-red-800 text-white rounded px-4 py-2 hover:bg-red-900">Continue</button>
            </div>
        </div>
    </form>
</div>

{{-- ===== templates JS clones to add new rows ===== --}}
<template id="skill-row-template">
    <div class="skill-chip flex items-center gap-1 pl-3 pr-1 py-1 rounded-full border border-gray-300 text-sm">
        <input type="text" name="skills[__INDEX__][name]" placeholder="e.g. Laravel" class="bg-transparent focus:outline-none w-28">
        <button type="button" class="remove-row text-red-700 px-1">&times;</button>
    </div>
</template>

<template id="experience-row-template">
    <div class="experience-row mb-3 p-3 border border-gray-200 rounded">
        <div class="flex gap-4 mb-2 text-sm">
            <label class="flex items-center gap-1"><input type="radio" name="experiences[__INDEX__][type]" value="work" checked> Work</label>
            <label class="flex items-center gap-1"><input type="radio" name="experiences[__INDEX__][type]" value="project"> Project</label>
        </div>
        <input type="text" name="experiences[__INDEX__][job_title]" placeholder="Job title / Project title" class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
            <input type="number" name="experiences[__INDEX__][duration_months]" min="0" max="600" placeholder="Duration (months)" class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
            <select name="experiences[__INDEX__][industry_id]" class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
                <option value="">Industry (optional)</option>
                @foreach($industries as $industry)
                    <option value="{{ $industry->industry_id }}">{{ $industry->industry_name }}</option>
                @endforeach
            </select>
        </div>
        <textarea name="experiences[__INDEX__][job_description]" rows="3" placeholder="What did you do?" class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800"></textarea>
        <button type="button" class="remove-row text-sm text-red-700">Remove</button>
    </div>
</template>

<template id="cert-row-template">
    <div class="cert-row mb-3 p-3 border border-gray-200 rounded">
        <div class="flex gap-4 mb-2 text-sm">
            <label class="flex items-center gap-1"><input type="radio" name="certifications[__INDEX__][certification_type]" value="certification" checked> Certification</label>
            <label class="flex items-center gap-1"><input type="radio" name="certifications[__INDEX__][certification_type]" value="seminar"> Seminar</label>
            <label class="flex items-center gap-1"><input type="radio" name="certifications[__INDEX__][certification_type]" value="training"> Training</label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
            <input type="text" name="certifications[__INDEX__][certification_name]" placeholder="Title" class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
            <input type="text" name="certifications[__INDEX__][certification_from]" placeholder="Issuing organization / speaker" class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-800">
        </div>
        <input type="date" name="certifications[__INDEX__][certification_date]" class="w-40 rounded border border-gray-300 px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-red-800">
        <button type="button" class="remove-row text-sm text-red-700">Remove</button>
    </div>
</template>

<script>
(function () {
    'use strict';

    var currentStep = 0;
    var totalSteps = 4;

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

    function showStep(idx) {
        currentStep = idx;
        steps.forEach(function (s) { s.classList.toggle('hidden', Number(s.dataset.step) !== idx); });
        tabs.forEach(function (t) { t.className = 'step-tab font-medium ' + (Number(t.dataset.step) <= idx ? 'text-red-800' : 'text-gray-400'); });
        btnBack.classList.toggle('hidden', idx === 0);
        btnNext.textContent = idx === totalSteps - 1 ? 'Submit resume' : 'Continue';
        window.scrollTo({ top: 0, behavior: 'smooth' });
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

    document.getElementById('add-skill').addEventListener('click', function () { addRow('skill-row-template', 'skills-list', 'skills'); recalculate(); });
    document.getElementById('add-experience').addEventListener('click', function () { addRow('experience-row-template', 'experience-list', 'experiences'); recalculate(); });
    document.getElementById('add-cert').addEventListener('click', function () { addRow('cert-row-template', 'cert-list', 'certifications'); recalculate(); });

    form.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.skill-chip, .experience-row, .cert-row').remove();
            recalculate();
        }
    });
    form.addEventListener('input', recalculate);
    form.addEventListener('change', recalculate);

    function recalculate() {
        var summary = document.getElementById('resume_summary').value;
        document.getElementById('summary-count').textContent = summary.length;

        var score = 0;
        if (summary.length > 40) score += 15;
        if (form.querySelector('[name="linkedin_url"]').value) score += 10;

        var skillCount = document.querySelectorAll('#skills-list .skill-chip').length;
        score += Math.min(25, skillCount * 5);

        if (document.querySelectorAll('#experience-list .experience-row').length >= 1) score += 30;
        if (document.querySelectorAll('#cert-list .cert-row').length >= 1) score += 20;

        score = Math.min(100, Math.round(score));
        document.getElementById('completeness-label').textContent = score;
        document.getElementById('completeness-bar').style.width = score + '%';
    }

    function submitForm(isFinal) {
        var formData = new FormData(form);
        formData.append('is_final', isFinal ? '1' : '0');

        fetch('{{ route('resume.save') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        })
        .then(function (res) { if (!res.ok) throw new Error('Save failed'); return res.json(); })
        .then(function (data) {
            document.getElementById('completeness-label').textContent = data.resume_completeness;
            document.getElementById('completeness-bar').style.width = data.resume_completeness + '%';
            if (isFinal) window.location.href = '{{ route('resume.build') }}?saved=1';
        })
        .catch(function () { alert('Could not save your resume. Please check your connection and try again.'); });
    }

    recalculate();
    showStep(0);
})();
</script>

</body>
</html>