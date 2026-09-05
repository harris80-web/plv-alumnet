{{--
    College filter cascades the Course filter (items 12/13) — shared by the
    admin dashboard and its 3 grouped report-detail pages, since the logic
    never references anything page-specific (just the two multiselect-filter
    wrappers rendered by partials/report-filters-form.blade.php).
--}}
<script>
    function applyCollegeCascade() {
        const collegeWrap = document.querySelector('.multiselect-filter[data-name="college"]');
        const programWrap = document.querySelector('.multiselect-filter[data-name="program_id"]');
        if (!collegeWrap || !programWrap) return;

        const checkedColleges = Array.from(collegeWrap.querySelectorAll('.option-checkbox:checked')).map(c => c.value);
        const programPanel = programWrap.querySelector('.multiselect-panel');

        programPanel.querySelectorAll('.option-checkbox').forEach(cb => {
            const label = cb.closest('label');
            const allowed = checkedColleges.length === 0 || checkedColleges.includes(cb.dataset.college);
            label.classList.toggle('hidden', !allowed);
            if (!allowed && cb.checked) {
                cb.checked = false;
            }
        });

        if (window.updateSelectAllState) updateSelectAllState(programPanel);
        if (window.updateMultiselectLabel) updateMultiselectLabel(programPanel);
    }

    document.addEventListener('change', function (e) {
        if (e.target.matches('.multiselect-filter[data-name="college"] .option-checkbox')) {
            applyCollegeCascade();
        }
    });
    document.addEventListener('DOMContentLoaded', applyCollegeCascade);
</script>
