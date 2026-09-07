{{--
    Shared JS for partials/multiselect-filter.blade.php's checkbox dropdowns.
    "Staged" — a checkbox click only updates the panel's own UI (label text,
    select-all state); nothing submits until the enclosing form's own
    "Apply Filters" button is clicked. This lets someone check several
    Industry + Job Type + Job Setup options in one go before the page
    actually reloads, instead of reloading on every single click.

    Included once per page that uses partials/multiselect-filter.blade.php
    (currently general/jobBoard.blade.php; superAdmin/dashboard.blade.php
    will include it too once its filters move to this same component).
--}}
<script>
    function toggleMultiselect(btn) {
        const panel = btn.parentElement.querySelector('.multiselect-panel');
        document.querySelectorAll('.multiselect-panel').forEach(function (p) {
            if (p !== panel) p.classList.add('hidden');
        });
        panel.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.multiselect-filter')) {
            document.querySelectorAll('.multiselect-panel').forEach(function (p) {
                p.classList.add('hidden');
            });
        }
    });

    function toggleMultiselectAll(selectAllCheckbox) {
        const panel = selectAllCheckbox.closest('.multiselect-panel');
        panel.querySelectorAll('.option-checkbox').forEach(function (o) {
            o.checked = selectAllCheckbox.checked;
        });
        updateSelectAllState(panel);
        updateMultiselectLabel(panel);
    }

    function onMultiselectOptionChange(checkbox) {
        const panel = checkbox.closest('.multiselect-panel');
        updateSelectAllState(panel);
        updateMultiselectLabel(panel);
    }

    function updateSelectAllState(panel) {
        const options = Array.from(panel.querySelectorAll('.option-checkbox'));
        const selectAll = panel.querySelector('.select-all-checkbox');
        const selectAllCheck = panel.querySelector('.select-all-check');
        const checkedCount = options.filter(function (o) { return o.checked; }).length;

        selectAll.checked = options.length > 0 && checkedCount === options.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < options.length;
        selectAllCheck.classList.toggle('hidden', !selectAll.checked);
    }

    // Keeps the trigger button's "N Selected"/placeholder text in sync
    // client-side, since the page no longer reloads on every checkbox click.
    function updateMultiselectLabel(panel) {
        const wrap = panel.closest('.multiselect-filter');
        const labelSpan = wrap.querySelector('.multiselect-trigger-label');
        const placeholder = wrap.dataset.placeholder || '';
        const count = panel.querySelectorAll('.option-checkbox:checked').length;
        labelSpan.textContent = count ? count + ' Selected' : placeholder;
        wrap.querySelector('.multiselect-trigger-btn').classList.toggle('text-[#0E0F3B]', count > 0);
        wrap.querySelector('.multiselect-trigger-btn').classList.toggle('font-semibold', count > 0);
        wrap.querySelector('.multiselect-trigger-btn').classList.toggle('text-gray-500', count === 0);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.multiselect-panel').forEach(updateSelectAllState);
    });
</script>
