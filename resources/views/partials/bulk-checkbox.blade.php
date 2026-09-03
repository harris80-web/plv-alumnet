{{--
    Shared "select all with partial state" checkbox system for superAdmin
    bulk-action tables. Included once via partials/super-admin-header.blade.php
    so every superAdmin page — current and future — gets it automatically.

    Usage: give the header "select all" checkbox and every row checkbox the
    `bulk-checkbox` class, then wire them together with:

        initBulkCheckboxGroup({
            header: 'someSelectAllCheckboxId',
            rowSelector: '.some-row-checkbox',
            onChange: function (checkedValues, checkedCount, visibleCount) {
                // update your bulk-action toolbar/button/count text here
            },
        });

    See userManagement.blade.php, testimonialManagement.blade.php,
    faqManagement.blade.php, or alumniIdManagement.blade.php for working
    examples (the last one calls this twice — once per tab).
--}}
<style>
    input[type="checkbox"].bulk-checkbox {
        appearance: none;
        -webkit-appearance: none;
        width: 1rem;
        height: 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background-color: white;
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    }

    input[type="checkbox"].bulk-checkbox:active {
        transform: scale(0.88);
    }

    input[type="checkbox"].bulk-checkbox:checked {
        background-color: #ED7A07;
        border-color: #ED7A07;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.485 1.431a1.473 1.473 0 0 0-2.072 0L6.133 7.952 4.342 5.985a1.473 1.473 0 1 0-2.172 1.992l2.86 3.06a1.473 1.473 0 0 0 2.134.018l6.366-6.77a1.473 1.473 0 0 0-.045-2.834z'/%3E%3C/svg%3E");
        background-size: 10px;
        background-position: center;
        background-repeat: no-repeat;
    }

    /* Partial state — some but not all rows checked. Same fill color as
       fully-checked, dash glyph instead of a checkmark. */
    input[type="checkbox"].bulk-checkbox:indeterminate {
        background-color: #ED7A07;
        border-color: #ED7A07;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Crect x='3' y='7' width='10' height='2' rx='1'/%3E%3C/svg%3E");
        background-size: 10px;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>

<script>
    /**
     * Wires a header "select all" checkbox to a group of row checkboxes with
     * proper tri-state syncing (empty / partial-via-native-indeterminate /
     * all), and skips rows currently hidden by a client-side search/filter
     * (display:none) so "select all" only ever selects what's actually
     * visible on screen.
     *
     * @param {Object} options
     * @param {string|HTMLElement} options.header - header checkbox id or element
     * @param {string} options.rowSelector - CSS selector matching every row checkbox
     * @param {function} [options.onChange] - (checkedValues, checkedCount, visibleCount) => void
     * @param {boolean} [options.respectFilter=true] - skip rows hidden (display:none) by a filter
     * @returns {Object} an object with sync() and getChecked() methods
     */
    window.initBulkCheckboxGroup = function (options) {
        var headerEl = typeof options.header === 'string' ? document.getElementById(options.header) : options.header;
        var rowSelector = options.rowSelector;
        var onChange = options.onChange;
        var respectFilter = options.respectFilter !== false;

        function rows() {
            var all = Array.prototype.slice.call(document.querySelectorAll(rowSelector));
            return respectFilter ? all.filter(function (cb) { return cb.offsetParent !== null; }) : all;
        }

        function sync() {
            var visible = rows();
            var checked = visible.filter(function (cb) { return cb.checked; });
            if (headerEl) {
                headerEl.checked = visible.length > 0 && checked.length === visible.length;
                headerEl.indeterminate = checked.length > 0 && checked.length < visible.length;
            }
            // Light-blue highlight on any row whose checkbox is checked —
            // covers both a direct click on the row checkbox and rows
            // toggled indirectly via the header "select all" checkbox.
            visible.forEach(function (cb) {
                var tr = cb.closest('tr');
                if (tr) tr.classList.toggle('bg-blue-50', cb.checked);
            });
            if (onChange) onChange(checked.map(function (cb) { return cb.value; }), checked.length, visible.length);
            return checked;
        }

        if (headerEl) {
            headerEl.addEventListener('change', function () {
                rows().forEach(function (cb) { cb.checked = headerEl.checked; });
                sync();
            });
        }

        document.querySelectorAll(rowSelector).forEach(function (cb) {
            cb.addEventListener('change', sync);
        });

        sync();

        return {
            sync: sync,
            getChecked: function () { return rows().filter(function (cb) { return cb.checked; }); },
        };
    };
</script>
