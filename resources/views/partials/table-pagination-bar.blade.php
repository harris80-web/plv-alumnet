{{--
    Shared table pagination bar: rows-per-page select (left) + item count and
    first/prev/next/last icon nav (right). Works in three modes without any
    page-specific JS — behavior is driven entirely by the data-pv-* attributes
    below, read by the single delegated script at the bottom (defined once no
    matter how many bars are @include'd on one page).

    Props:
      id             (string, required)  Unique per bar instance.
      mode           'reload' (default) | 'ajax' | 'client'
      paginator      Laravel paginator — required for 'reload'/'ajax'.
      totalItems     int  — required for 'client' (no real paginator).
      rowSelector    string — required for 'client', e.g. '#tbodyId tr[data-search]'.
      perPageOptions array<int>, default [5, 10, 20, 50].
      currentPerPage int  — required for 'client' (no paginator to read it from).
      perPageParam   string, default 'per_page'. Give each bar on a page with
                     multiple paginators its own name (mirrors the existing
                     adminPage/alumniPage/employerPendingPage convention).
      fetchUrl       string — 'ajax' mode only: the *Fragment route to fetch.
      wrapId         string — 'ajax' mode only: id of the element whose
                     innerHTML gets replaced with the fetch response.
      reinitFn       string — 'ajax' mode only, optional: name of a global JS
                     function to call after the swap (e.g. to re-bind
                     checkboxes) — unrelated to this bar's own state, which
                     re-binds itself via event delegation automatically.
--}}
@php
    $pvMode = $mode ?? 'reload';
    $pvPerPageOptions = $perPageOptions ?? [5, 10, 20, 50];
    $pvPerPageParam = $perPageParam ?? 'per_page';
    $pvPageParam = $pvMode === 'client' ? 'page' : $paginator->getPageName();
    $pvCurrentPerPage = $pvMode === 'client' ? ($currentPerPage ?? 10) : $paginator->perPage();
    $pvTotal = $pvMode === 'client' ? ($totalItems ?? 0) : $paginator->total();
    $pvCurrentPage = $pvMode === 'client' ? 1 : $paginator->currentPage();
    $pvLastPage = $pvMode === 'client' ? max(1, (int) ceil(($totalItems ?? 0) / max(1, $pvCurrentPerPage))) : max(1, $paginator->lastPage());
@endphp

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 text-sm"
    data-pv-pagination
    data-pv-id="{{ $id }}"
    data-pv-mode="{{ $pvMode }}"
    data-pv-page-param="{{ $pvPageParam }}"
    data-pv-per-page-param="{{ $pvPerPageParam }}"
    data-pv-current-page="{{ $pvCurrentPage }}"
    data-pv-last-page="{{ $pvLastPage }}"
    @if ($pvMode === 'ajax')
        data-pv-fetch-url="{{ $fetchUrl }}"
        data-pv-wrap-id="{{ $wrapId }}"
        @if (!empty($reinitFn)) data-pv-reinit-fn="{{ $reinitFn }}" @endif
    @endif
    @if ($pvMode === 'client')
        data-pv-row-selector="{{ $rowSelector }}"
    @endif
>
    <!-- Rows per page -->
    <div class="flex items-center gap-2 text-[#0E0F3B]">
        <span class="text-slate-500">Rows per page:</span>
        <div class="relative">
            <select data-pv-per-page
                class="pl-4 pr-8 py-1.5 border border-slate-200 rounded-full bg-white appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#C73D1A] text-[#0E0F3B] font-medium">
                @foreach ($pvPerPageOptions as $opt)
                    <option value="{{ $opt }}" {{ (int) $pvCurrentPerPage === (int) $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 pointer-events-none">
                <i class="fas fa-chevron-down text-[10px]"></i>
            </span>
        </div>
    </div>

    <!-- Item count + nav -->
    <div class="flex items-center gap-4 text-[#0E0F3B]">
        <span data-pv-items-text class="text-slate-500 font-medium">
            {{ number_format($pvTotal) }} {{ Str::plural('item', $pvTotal) }}
        </span>
        <div class="flex items-center gap-1">
            <button type="button" data-pv-first title="First page"
                class="w-7 h-7 flex items-center justify-center rounded-md text-[#0E0F3B] hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-colors"
                {{ $pvCurrentPage <= 1 ? 'disabled' : '' }}>
                <i class="fas fa-angles-left text-xs"></i>
            </button>
            <button type="button" data-pv-prev title="Previous page"
                class="w-7 h-7 flex items-center justify-center rounded-md text-[#0E0F3B] hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-colors"
                {{ $pvCurrentPage <= 1 ? 'disabled' : '' }}>
                <i class="fas fa-chevron-left text-xs"></i>
            </button>
            <span data-pv-page-text class="px-2 font-semibold whitespace-nowrap">
                Page {{ $pvCurrentPage }} of {{ $pvLastPage }}
            </span>
            <button type="button" data-pv-next title="Next page"
                class="w-7 h-7 flex items-center justify-center rounded-md text-[#0E0F3B] hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-colors"
                {{ $pvCurrentPage >= $pvLastPage ? 'disabled' : '' }}>
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
            <button type="button" data-pv-last title="Last page"
                class="w-7 h-7 flex items-center justify-center rounded-md text-[#0E0F3B] hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-colors"
                {{ $pvCurrentPage >= $pvLastPage ? 'disabled' : '' }}>
                <i class="fas fa-angles-right text-xs"></i>
            </button>
        </div>
    </div>
</div>

@once
    <style>
        .pv-hidden-by-page { display: none !important; }
    </style>
    <script>
        (function () {
            // Guards against this block running twice if the partial is
            // included more than once on the same page — Blade's once
            // directive already prevents that per-render, this is just
            // belt-and-suspenders for AJAX-swapped fragments that might
            // re-embed this script block.
            if (window.__pvPaginationBound) return;
            window.__pvPaginationBound = true;

            const clientState = {}; // id -> { page, perPage }

            function bar(el) {
                return el.closest('[data-pv-pagination]');
            }

            function setDisabled(el, disabled) {
                if (!el) return;
                el.disabled = disabled;
            }

            function updateNavUI(root, page, lastPage) {
                const pageText = root.querySelector('[data-pv-page-text]');
                if (pageText) pageText.textContent = 'Page ' + page + ' of ' + lastPage;
                setDisabled(root.querySelector('[data-pv-first]'), page <= 1);
                setDisabled(root.querySelector('[data-pv-prev]'), page <= 1);
                setDisabled(root.querySelector('[data-pv-next]'), page >= lastPage);
                setDisabled(root.querySelector('[data-pv-last]'), page >= lastPage);
            }

            function navigate(root, page, perPage) {
                const url = new URL(window.location.href);
                url.searchParams.set(root.dataset.pvPerPageParam, perPage);
                url.searchParams.set(root.dataset.pvPageParam, page);
                window.location.href = url.toString();
            }

            // Shared by any reload-mode search box: sets/clears a query
            // param and resets its table's page number back to 1, so a new
            // search never lands on a now out-of-range page.
            window.pvSearchNavigate = function (param, value, pageParam) {
                const url = new URL(window.location.href);
                if (value) {
                    url.searchParams.set(param, value);
                } else {
                    url.searchParams.delete(param);
                }
                if (pageParam) url.searchParams.set(pageParam, 1);
                window.location.href = url.toString();
            };

            // Same, but for an ajax-mode table: fetches the fragment with
            // the search param instead of navigating.
            window.pvSearchAjax = function (param, value, root) {
                const url = new URL(window.location.href);
                if (value) {
                    url.searchParams.set(param, value);
                } else {
                    url.searchParams.delete(param);
                }
                window.history.replaceState({}, '', url.toString());
                ajaxLoad(root, 1, parseInt(root.querySelector('[data-pv-per-page]').value, 10));
            };

            function ajaxLoad(root, page, perPage) {
                const wrap = document.getElementById(root.dataset.pvWrapId);
                if (!wrap) return;
                // Carries over whatever's currently in the address bar (e.g. a
                // search param set via pvSearchAjax's history.replaceState)
                // so paging/per-page changes don't drop an active search.
                const params = new URLSearchParams(window.location.search);
                params.set(root.dataset.pvPageParam, page);
                params.set(root.dataset.pvPerPageParam, perPage);
                const url = root.dataset.pvFetchUrl + '?' + params.toString();
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        wrap.innerHTML = html;
                        if (window.lucide) lucide.createIcons();
                        // Re-bind any action-menu buttons the fragment brought in —
                        // most admin tables define this the same way (clone-and-
                        // replace to drop stale listeners, then re-attach), just
                        // under one of these two names depending on the page.
                        if (typeof window.initDropdowns === 'function') window.initDropdowns();
                        if (typeof window.initMenuButtons === 'function') window.initMenuButtons();
                        const reinitName = root.dataset.pvReinitFn;
                        if (reinitName && typeof window[reinitName] === 'function') {
                            window[reinitName]();
                        }
                    });
            }

            function clientRows(root) {
                return Array.from(document.querySelectorAll(root.dataset.pvRowSelector));
            }

            function clientEligibleRows(root) {
                // "Eligible" = not hidden by the page's own search/filter
                // logic (style.display), which owns that property — we
                // never touch it, only layer pv-hidden-by-page on top.
                return clientRows(root).filter(function (row) {
                    return row.style.display !== 'none';
                });
            }

            function applyClientSlice(root, page) {
                const id = root.dataset.pvId;
                const perPage = clientState[id] ? clientState[id].perPage : parseInt(root.querySelector('[data-pv-per-page]').value, 10);
                const eligible = clientEligibleRows(root);
                const lastPage = Math.max(1, Math.ceil(eligible.length / perPage));
                page = Math.min(Math.max(1, page), lastPage);
                clientState[id] = { page: page, perPage: perPage };

                clientRows(root).forEach(function (row) { row.classList.remove('pv-hidden-by-page'); });
                eligible.forEach(function (row, i) {
                    if (i < (page - 1) * perPage || i >= page * perPage) {
                        row.classList.add('pv-hidden-by-page');
                    }
                });

                const itemsText = root.querySelector('[data-pv-items-text]');
                if (itemsText) itemsText.textContent = eligible.length.toLocaleString() + (eligible.length === 1 ? ' item' : ' items');
                updateNavUI(root, page, lastPage);
            }

            function initClientBar(root) {
                const id = root.dataset.pvId;
                const perPage = parseInt(root.querySelector('[data-pv-per-page]').value, 10);
                clientState[id] = { page: 1, perPage: perPage };
                applyClientSlice(root, 1);
            }

            document.querySelectorAll('[data-pv-pagination][data-pv-mode="client"]').forEach(initClientBar);

            document.addEventListener('pv:filtered', function (e) {
                const scope = (e.detail && e.detail.scope) ? document.querySelector(e.detail.scope) : document;
                (scope || document).querySelectorAll('[data-pv-pagination][data-pv-mode="client"]').forEach(function (root) {
                    applyClientSlice(root, 1);
                });
            });

            document.addEventListener('change', function (e) {
                const select = e.target.closest('[data-pv-per-page]');
                if (!select) return;
                const root = bar(select);
                const perPage = parseInt(select.value, 10);
                const mode = root.dataset.pvMode;

                if (mode === 'reload') {
                    navigate(root, 1, perPage);
                } else if (mode === 'ajax') {
                    ajaxLoad(root, 1, perPage);
                } else if (mode === 'client') {
                    clientState[root.dataset.pvId] = { page: 1, perPage: perPage };
                    applyClientSlice(root, 1);
                }
            });

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-pv-first], [data-pv-prev], [data-pv-next], [data-pv-last]');
                if (!btn || btn.disabled) return;
                const root = bar(btn);
                const mode = root.dataset.pvMode;
                const current = parseInt(root.dataset.pvCurrentPage, 10);
                const last = parseInt(root.dataset.pvLastPage, 10);
                const perPage = parseInt(root.querySelector('[data-pv-per-page]').value, 10);

                let target = current;
                if (btn.hasAttribute('data-pv-first')) target = 1;
                else if (btn.hasAttribute('data-pv-prev')) target = Math.max(1, current - 1);
                else if (btn.hasAttribute('data-pv-next')) target = Math.min(last, current + 1);
                else if (btn.hasAttribute('data-pv-last')) target = last;

                if (mode === 'reload') {
                    navigate(root, target, perPage);
                } else if (mode === 'ajax') {
                    ajaxLoad(root, target, perPage);
                } else if (mode === 'client') {
                    applyClientSlice(root, target);
                }
            });
        })();
    </script>
@endonce
