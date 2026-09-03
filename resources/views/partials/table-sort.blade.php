{{--
    Shared client-side column sort for admin/superAdmin tables. Included once
    via partials/super-admin-header.blade.php so every page gets it
    automatically — no per-page JS needed.

    Usage: add a `data-sort` attribute to any <th> that should be sortable
    (the existing decorative chevron-down <i class="sort-icon"> stays as-is;
    this script rotates it and highlights the active column). Sorting is
    click-driven (ascending, click again for descending), reorders whichever
    rows are currently in the table's <tbody> (composes with both server- and
    client-paginated tables), and dispatches `pv:filtered` afterwards so any
    table-pagination-bar on the same table resets to page 1 in the new order.

    Event-delegated (not bound at parse time) so it keeps working after an
    AJAX fragment swap replaces the table's rows/headers with fresh markup.
--}}
@once
    <style>
        th[data-sort] { cursor: pointer; user-select: none; }
        th[data-sort] .sort-icon { transition: transform 0.15s ease; }
        /* Data cells only — the header/title never gets this highlight.
           The second rule forces every plain nested text/icon to white too
           (Tailwind color utilities on a child otherwise win over the
           inherited color above) — excluding .rounded-full badges/pills,
           which already carry their own readable background+text pairing
           that orange showing through their padding would only hurt. */
        td.pv-sort-active { background-color: #ED7A07 !important; color: #ffffff !important; }
        td.pv-sort-active *:not(.rounded-full) { color: #ffffff !important; }
    </style>
    <script>
        (function () {
            if (window.__pvSortBound) return;
            window.__pvSortBound = true;

            function cellText(cell) {
                if (!cell) return '';
                return (cell.dataset.sortValue ?? cell.textContent).trim();
            }

            function compareValues(a, b) {
                var an = parseFloat(a), bn = parseFloat(b);
                if (a !== '' && b !== '' && !isNaN(an) && !isNaN(bn)) {
                    return an - bn;
                }
                return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
            }

            document.addEventListener('click', function (e) {
                const th = e.target.closest('th[data-sort]');
                if (!th) return;

                const table = th.closest('table');
                const tbody = table && table.querySelector('tbody');
                if (!tbody) return;

                const colIndex = Array.prototype.indexOf.call(th.parentNode.children, th);
                const dir = th.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                const mult = dir === 'asc' ? 1 : -1;

                const rows = Array.prototype.slice.call(tbody.querySelectorAll(':scope > tr'))
                    .filter(function (row) { return row.children.length > colIndex; });

                rows.sort(function (rowA, rowB) {
                    return compareValues(cellText(rowA.children[colIndex]), cellText(rowB.children[colIndex])) * mult;
                });
                rows.forEach(function (row) { tbody.appendChild(row); });

                // Reset every header's sort direction/icon (the header itself
                // is never highlighted — only the column's data cells below).
                table.querySelectorAll('th[data-sort]').forEach(function (h) {
                    h.removeAttribute('data-sort-dir');
                    const icon = h.querySelector('.sort-icon');
                    if (icon) icon.style.transform = '';
                });
                th.dataset.sortDir = dir;
                const activeIcon = th.querySelector('.sort-icon');
                if (activeIcon) activeIcon.style.transform = dir === 'desc' ? 'rotate(180deg)' : '';

                // Highlight the sorted column's data cells (not the header).
                table.querySelectorAll('tbody > tr > *').forEach(function (cell) {
                    cell.classList.remove('pv-sort-active');
                });
                rows.forEach(function (row) {
                    if (row.children[colIndex]) row.children[colIndex].classList.add('pv-sort-active');
                });

                document.dispatchEvent(new CustomEvent('pv:filtered'));
            });

            // Clicking anywhere outside the sorted column's own header/cells
            // clears the highlight (the sort order itself is left alone —
            // this only resets the visual indicator, not the data).
            document.addEventListener('click', function (e) {
                if (e.target.closest('th[data-sort]') || e.target.closest('.pv-sort-active')) return;
                document.querySelectorAll('th[data-sort-dir]').forEach(function (h) {
                    h.removeAttribute('data-sort-dir');
                    const icon = h.querySelector('.sort-icon');
                    if (icon) icon.style.transform = '';
                });
                document.querySelectorAll('.pv-sort-active').forEach(function (cell) {
                    cell.classList.remove('pv-sort-active');
                });
            });
        })();
    </script>
@endonce
