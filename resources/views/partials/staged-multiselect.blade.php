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
    function closeMultiselect(panel) {
        panel.classList.add('hidden');
        panel.closest('.multiselect-filter').classList.remove('is-open');
    }

    function toggleMultiselect(btn) {
        const panel = btn.parentElement.querySelector('.multiselect-panel');
        document.querySelectorAll('.multiselect-panel').forEach(function (p) {
            if (p !== panel) closeMultiselect(p);
        });
        panel.classList.toggle('hidden');
        btn.parentElement.classList.toggle('is-open', !panel.classList.contains('hidden'));
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.multiselect-filter')) {
            document.querySelectorAll('.multiselect-panel').forEach(closeMultiselect);
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

    // Keeps the trigger button's text in sync client-side (the picked
    // options' own labels, comma-joined — same as the server-rendered text
    // in multiselect-filter.blade.php), since the page no longer reloads on
    // every checkbox click.
    function updateMultiselectLabel(panel) {
        const wrap = panel.closest('.multiselect-filter');
        const labelSpan = wrap.querySelector('.multiselect-trigger-label');
        const btn = wrap.querySelector('.multiselect-trigger-btn');
        const placeholder = wrap.dataset.placeholder || '';
        const prefix = wrap.dataset.labelPrefix || '';
        const names = Array.from(panel.querySelectorAll('.option-checkbox:checked')).map(function (o) {
            return o.closest('label').querySelector('.text-xs').textContent.trim();
        });
        const count = names.length;
        labelSpan.textContent = count ? (prefix ? prefix + ': ' : '') + names.join(', ') : placeholder;
        btn.title = count ? placeholder + ': ' + names.join(', ') : placeholder;
        btn.classList.toggle('text-[#0E0F3B]', count > 0);
        btn.classList.toggle('font-semibold', count > 0);
        btn.classList.toggle('text-gray-500', count === 0);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.multiselect-panel').forEach(updateSelectAllState);
    });
</script>
