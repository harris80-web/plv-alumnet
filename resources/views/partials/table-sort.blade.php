{{--
    Shared client-side column sort + table layout rules for admin/superAdmin
    tables. Included once via partials/super-admin-header.blade.php so every
    page gets it automatically — no per-page JS needed.

    Sorting: add a `data-sort` attribute to any <th> that should be sortable.
    Whatever per-page `.sort-icon` (chevron) the header carries is hidden and
    replaced by an up/down arrow pair — the up arrow turns #ED7A07 while the
    column is ascending, the down arrow while descending. Sorting is
    click-driven (ascending, click again for descending), reorders whichever
    rows are currently in the table's <tbody> (composes with both server- and
    client-paginated tables), and dispatches `pv:filtered` afterwards so any
    table-pagination-bar on the same table resets to page 1 in the new order.

    Layout: every column is classified from its header text —
      status/date columns  -> centered, never wrapped or cut (pv-col-center);
                              any other column can opt in with <th data-col-center>
      action/checkbox cols -> centered controls (pv-col-ctrl)
      everything else      -> left-aligned, long text wraps (pv-col-left)
    The per-page table styles (text-center, whitespace-nowrap, truncate,
    clamp-2 …) differ from page to page, hence the !important overrides.

    Event-delegated + MutationObserver-driven (not bound at parse time) so it
    keeps working after an AJAX fragment swap replaces the table's
    rows/headers with fresh markup.
--}}
@once
    <style>
        th[data-sort] { cursor: pointer; user-select: none; }
        th[data-sort] .sort-icon { display: none !important; }
        .pv-sort-arrows { display: inline-block; vertical-align: middle; margin-left: 4px; flex-shrink: 0; }
        .pv-sort-arrows path { opacity: 0.55; transition: stroke 0.15s ease, opacity 0.15s ease; }
        th[data-sort-dir="asc"] .pv-sort-arrows .pv-up,
        th[data-sort-dir="desc"] .pv-sort-arrows .pv-down { stroke: #ED7A07; opacity: 1; }

        /* Data cells only — the header/title never gets this highlight.
           The second rule forces every plain nested text/icon to white too
           (Tailwind color utilities on a child otherwise win over the
           inherited color above) — excluding .rounded-full badges/pills,
           which already carry their own readable background+text pairing
           that orange showing through their padding would only hurt. */
        td.pv-sort-active { background-color: #ED7A07 !important; color: #ffffff !important; }
        td.pv-sort-active *:not(.rounded-full) { color: #ffffff !important; }

        /* Left-aligned content columns: wrap long text instead of cutting it. */
        th.pv-col-left, td.pv-col-left { text-align: left !important; }
        td.pv-col-left { white-space: normal !important; overflow-wrap: break-word; }
        /* Direct wrapper only — a nested .justify-center (e.g. the company-logo
           tile centering its fallback icon) must keep its own centering. */
        td.pv-col-left > .justify-center { justify-content: flex-start !important; }
        td.pv-col-left :is(.truncate, .whitespace-nowrap):not(.rounded-full) {
            white-space: normal !important; overflow: visible !important; text-overflow: clip !important; max-width: none !important;
        }
        td.pv-col-left :is(.clamp-2, [class*="line-clamp"]) {
            display: block !important; -webkit-line-clamp: unset !important; line-clamp: unset !important; overflow: visible !important;
        }

        /* Status / date columns: centered, kept on one line, never truncated. */
        th.pv-col-center, td.pv-col-center, th.pv-col-ctrl, td.pv-col-ctrl { text-align: center !important; }
        td.pv-col-center { white-space: nowrap !important; }
        td.pv-col-center .truncate { overflow: visible !important; text-overflow: clip !important; max-width: none !important; }
        td.pv-col-center > .flex, td.pv-col-ctrl > .flex { justify-content: center !important; }
    </style>
    <script>
        (function () {
            if (window.__pvSortBound) return;
            window.__pvSortBound = true;

            const ARROWS = '<svg class="pv-sort-arrows" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path class="pv-up" d="m7 9 5-5 5 5"/><path class="pv-down" d="m7 15 5 5 5-5"/></svg>';
            const CENTER_HEADER = /\b(status|date|schedule|month)\b/i;
            const CTRL_HEADER = /^(actions?)$/i;

            function columnKind(th) {
                const label = th.textContent.replace(/\s+/g, ' ').trim();
                if (!label || CTRL_HEADER.test(label) || th.querySelector('input[type="checkbox"]')) return 'pv-col-ctrl';
                if (th.hasAttribute('data-col-center')) return 'pv-col-center'; // per-column opt-in

                return CENTER_HEADER.test(label) ? 'pv-col-center' : 'pv-col-left';
            }

            // Idempotent: safe to re-run on every DOM change.
            function enhance() {
                document.querySelectorAll('th[data-sort]:not([data-pv-arrows])').forEach(function (th) {
                    th.setAttribute('data-pv-arrows', '');
                    th.insertAdjacentHTML('beforeend', ARROWS);
                });

                document.querySelectorAll('table').forEach(function (table) {
                    const headerRow = Array.prototype.find.call(table.rows, function (r) { return r.querySelector('th'); });
                    if (!headerRow) return;
                    const kinds = Array.prototype.map.call(headerRow.cells, function (th) {
                        const kind = columnKind(th);
                        if (!th.classList.contains(kind)) {
                            th.classList.remove('pv-col-left', 'pv-col-center', 'pv-col-ctrl');
                            th.classList.add(kind);
                        }
                        return kind;
                    });
                    Array.prototype.forEach.call(table.rows, function (row) {
                        // Skip the header and colspan rows (empty states, group headings).
                        if (row === headerRow || row.cells.length !== kinds.length) return;
                        Array.prototype.forEach.call(row.cells, function (cell, i) {
                            if (!cell.classList.contains(kinds[i])) cell.classList.add(kinds[i]);
                        });
                    });
                });
            }

            let scheduled = false;
            function scheduleEnhance() {
                if (scheduled) return;
                scheduled = true;
                requestAnimationFrame(function () { scheduled = false; enhance(); });
            }

            document.addEventListener('DOMContentLoaded', function () {
                enhance();
                new MutationObserver(scheduleEnhance).observe(document.body, { childList: true, subtree: true });
            });

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

                // Only the clicked header carries a direction; its data-sort-dir
                // drives which arrow is highlighted (see the CSS above).
                table.querySelectorAll('th[data-sort]').forEach(function (h) {
                    h.removeAttribute('data-sort-dir');
                });
                th.dataset.sortDir = dir;

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
                });
                document.querySelectorAll('.pv-sort-active').forEach(function (cell) {
                    cell.classList.remove('pv-sort-active');
                });
            });
        })();
    </script>
@endonce
