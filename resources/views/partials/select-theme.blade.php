{{--
    Brand colour for native <select> dropdown lists: the highlighted / chosen
    option is #ED7A07 (white text) instead of the browser's grey.

    A normal <select>'s open list is drawn by the browser, which ignores page
    CSS — so the script below opts selects into the customizable-select mode
    (appearance: base-select, Chrome/Edge 135+) by adding .pv-brand-select.
    Browsers without it (Firefox, Safari today) never get the class and keep
    their stock dropdown untouched.

    Keeping the closed select looking exactly as before:
      - each page's own classes (border, padding, background, font) still apply;
      - line-height/min-height are reset — native selects ignore Tailwind's
        text-* line-height, base-select doesn't, which made some 3px taller;
      - base-select sizes to the CHOSEN option (native sizes to the widest),
        so selects without their own width (w-full, flex-1, …) get a
        min-width fitted to their longest option — measured once visible, and
        re-measured when their options change (e.g. a College → Course cascade);
      - selects that draw their own chevron (Tailwind `appearance-none`) get
        the built-in one hidden so it isn't doubled.
    Quill's toolbar selects (ql-*) are skipped — Quill swaps in its own picker.

    Included once from partials/super-admin-header (admin pages) and
    partials/responsive-assets (general / alumni / employer / auth pages).
--}}
@once
    <style>
        @supports (appearance: base-select) {
            select.pv-brand-select,
            select.pv-brand-select::picker(select) {
                appearance: base-select;
            }

            /* Match the native closed select: one line, clipped (not wrapped),
               vertically centred even when min-height (set by the script) is
               taller than the text. */
            select.pv-brand-select {
                line-height: normal !important;
                min-height: 0;
                white-space: nowrap;
                overflow: hidden;
                align-items: center;
                align-content: center;
            }

            /* Thin chevron like the native arrow, instead of base-select's solid triangle. */
            select.pv-brand-select::picker-icon {
                content: "";
                width: 0.75em;
                height: 0.75em;
                margin-inline-start: auto;
                background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") center / contain no-repeat;
            }

            select.pv-brand-select::picker(select) {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.18);
                padding: 4px 0;
            }

            select.pv-brand-select option {
                padding: 6px 12px;
                color: #0E0F3B;
                background: transparent;
            }

            select.pv-brand-select option::checkmark {
                display: none;
            }

            /* The chosen option, and whichever option the pointer/keyboard is on. */
            select.pv-brand-select option:checked,
            select.pv-brand-select option:hover,
            select.pv-brand-select option:focus-visible {
                background: #ED7A07;
                color: #ffffff;
            }

            /* Like the native list: while another option is hovered, only that one is highlighted. */
            select.pv-brand-select:has(option:hover) option:checked:not(:hover) {
                background: transparent;
                color: #0E0F3B;
            }

            select.pv-brand-select option:disabled {
                color: #94a3b8;
            }

            /* Pages that draw their own chevron already hide the native arrow. */
            select.pv-brand-select.appearance-none::picker-icon {
                display: none;
            }
        }
    </style>
    <script>
        (function () {
            if (window.__pvSelectTheme || !window.CSS || !CSS.supports('appearance', 'base-select')) return;
            window.__pvSelectTheme = true;

            const resizeObserver = new ResizeObserver(function (entries) {
                entries.forEach(function (entry) { fit(entry.target); });
            });

            function eligible(sel) {
                return !sel.multiple && !(sel.size > 1) && !/(^|\s)ql-/.test(sel.className);
            }

            // Lock in the select's NATIVE box: briefly drop the class, read the
            // size the browser draws, then keep that as min-height under
            // base-select — and as min-width too, but only when that native
            // width came from its options (equal to its max-content width).
            // A width set by the page/layout (w-full, flex-1 in a flex row,
            // grid stretch, …) is left alone so it can still shrink and grow.
            function fit(sel) {
                if (sel.dataset.pvFitted || !sel.offsetWidth) return; // hidden: ResizeObserver retries once shown
                sel.classList.remove('pv-brand-select');
                sel.style.minWidth = '';
                sel.style.minHeight = '';
                const native = sel.getBoundingClientRect();
                const inlineWidth = sel.style.width;
                sel.style.width = 'max-content';
                const contentSized = Math.abs(sel.getBoundingClientRect().width - native.width) < 1;
                sel.style.width = inlineWidth;
                sel.classList.add('pv-brand-select');
                sel.style.minHeight = native.height + 'px';
                if (contentSized) sel.style.minWidth = native.width + 'px';
                sel.dataset.pvFitted = '1';
            }

            function brand(sel) {
                if (sel.classList.contains('pv-brand-select') || !eligible(sel)) return;
                sel.classList.add('pv-brand-select');
                resizeObserver.observe(sel);
                fit(sel);
            }

            function scan(root) {
                if (root.tagName === 'SELECT') brand(root);
                else if (root.querySelectorAll) root.querySelectorAll('select').forEach(brand);
            }

            // The native size depends on things that can land after
            // DOMContentLoaded — the web font, the Tailwind CDN's generated
            // classes, a breakpoint change — so measure again when they settle.
            function refitAll() {
                document.querySelectorAll('select.pv-brand-select').forEach(function (sel) {
                    delete sel.dataset.pvFitted;
                    fit(sel);
                });
            }
            window.addEventListener('load', function () {
                refitAll();
                if (document.fonts && document.fonts.ready) document.fonts.ready.then(refitAll);
            });
            let resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(refitAll, 150);
            });

            document.addEventListener('DOMContentLoaded', function () {
                scan(document);
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        const owner = m.target.closest && m.target.closest('select.pv-brand-select');
                        if (owner) { delete owner.dataset.pvFitted; fit(owner); } // options changed
                        m.addedNodes.forEach(function (n) { if (n.nodeType === 1) scan(n); });
                    });
                }).observe(document.body, { childList: true, subtree: true });
            });
        })();
    </script>
@endonce
