{{--
    Required-skills picker for a job posting form. Same search-and-add-chip
    pattern as the alumni resume builder's skill picker (reusing the live
    GET /skills/search endpoint), just scoped with $uid so this can be
    included more than once on one page (e.g. a create modal plus one edit
    modal per job posting) without id collisions.

    Params: $uid (string, unique per instance), $selectedSkills (Collection
    of Skill models already on the job, for the edit case — omit for create).
--}}
@php
    $uid = $uid ?? 'new';
    $selectedSkills = $selectedSkills ?? collect();
@endphp

<div class="space-y-1">
    <label class="text-[10px] font-bold text-[#1D264F] uppercase">Required Skills</label>
    <div class="relative">
        <input type="text" id="jobSkillSearch-{{ $uid }}" autocomplete="off"
            placeholder="Search or add a required skill..."
            class="w-full border border-[#0E0F3B] rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#C73D1A]">
        <div id="jobSkillResults-{{ $uid }}"
            class="hidden absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div>
    </div>
    <div id="jobSkillsList-{{ $uid }}" class="flex flex-wrap gap-2 mt-2">
        @foreach($selectedSkills as $skill)
            <span class="skill-chip flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full pl-3 pr-1 py-1">
                <span class="skill-name-display">{{ $skill->skill_name }}</span>
                <input type="hidden" name="skills[]" value="{{ $skill->skill_name }}">
                <button type="button" class="remove-row text-red-700 px-1">&times;</button>
            </span>
        @endforeach
    </div>
</div>

<script>
(function () {
    'use strict';

    var uid = @json($uid);
    var input = document.getElementById('jobSkillSearch-' + uid);
    var results = document.getElementById('jobSkillResults-' + uid);
    var list = document.getElementById('jobSkillsList-' + uid);
    var searchTimeout;

    function alreadyAdded() {
        return Array.from(list.querySelectorAll('.skill-name-display'))
            .map(function (el) { return el.textContent.trim().toLowerCase(); });
    }

    function addChip(name) {
        if (alreadyAdded().indexOf(name.toLowerCase()) !== -1) return;

        var chip = document.createElement('span');
        chip.className = 'skill-chip flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full pl-3 pr-1 py-1';

        var span = document.createElement('span');
        span.className = 'skill-name-display';
        span.textContent = name;

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'skills[]';
        hidden.value = name;

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'remove-row text-red-700 px-1';
        btn.innerHTML = '&times;';

        chip.appendChild(span);
        chip.appendChild(hidden);
        chip.appendChild(btn);
        list.appendChild(chip);
    }

    list.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.skill-chip').remove();
        }
    });

    function closeResults() {
        results.classList.add('hidden');
        results.innerHTML = '';
        input.value = '';
    }

    input.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        var q = this.value.trim();

        if (q.length < 2) {
            results.classList.add('hidden');
            results.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(function () {
            fetch('{{ route('skills.search') }}?q=' + encodeURIComponent(q))
                .then(function (res) { return res.json(); })
                .then(function (skills) {
                    results.innerHTML = '';
                    var added = alreadyAdded();
                    var hasExactMatch = false;

                    skills.forEach(function (skill) {
                        if (skill.skill_name.toLowerCase() === q.toLowerCase()) hasExactMatch = true;
                        if (added.indexOf(skill.skill_name.toLowerCase()) !== -1) return;

                        var item = document.createElement('div');
                        item.className = 'px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer';
                        item.textContent = skill.skill_name;
                        item.addEventListener('click', function () { addChip(skill.skill_name); closeResults(); });
                        results.appendChild(item);
                    });

                    if (!hasExactMatch && added.indexOf(q.toLowerCase()) === -1) {
                        var addNew = document.createElement('div');
                        addNew.className = 'px-3 py-2 text-sm text-red-700 font-medium hover:bg-gray-100 cursor-pointer border-t border-gray-100';
                        addNew.textContent = '+ Add "' + q + '" as a new skill';
                        addNew.addEventListener('click', function () { addChip(q); closeResults(); });
                        results.appendChild(addNew);
                    }

                    results.classList.toggle('hidden', results.children.length === 0);
                })
                .catch(function () { results.classList.add('hidden'); });
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (e.target !== input && !results.contains(e.target)) {
            closeResults();
        }
    });
})();
</script>
