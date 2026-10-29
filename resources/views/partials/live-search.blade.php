{{--
    Search-as-you-type for any search box marked data-live-search — runs the
    search once the user stops typing, with every filter that's currently
    set, instead of waiting for Enter. Include once per page (it guards
    against double init).

    What gets searched:
      - Input inside a <form>: the whole form (so every filter in it — selects,
        checkboxes, hidden carried-over filters — is applied), empty fields
        dropped and back to page 1.
      - data-live-search-param="name" (no form): the current URL with just
        that param swapped — every other filter already in the URL stays.
        data-live-search-page-param="adminPage" resets that page to 1.

    How results appear:
      - data-live-search-target="#id[, #id2]": fetches the new page in the
        background and swaps just those elements' contents — no reload, the
        cursor never leaves the box. Falls back to a normal navigation if the
        fetch or swap fails.
      - No target: navigates to the new URL (for pages whose results can't be
        safely swapped in place, e.g. rows with their own modals), then puts
        the cursor back in the search box where it was.
--}}
<style>
    [data-live-search-busy] { opacity: .55; transition: opacity .15s; pointer-events: none; }
</style>
<script>
(function () {
    if (window.__liveSearchInit) return;
    window.__liveSearchInit = true;

    const SWAP_DELAY = 350;    // same as the Alumni Directory's existing live search
    const RELOAD_DELAY = 600;  // a full reload interrupts typing, so wait a bit longer
    const RESTORE_KEY = 'liveSearch:restore';
    const timers = new WeakMap();
    let inFlight = null;

    function targetsOf(input) {
        return (input.dataset.liveSearchTarget || '').split(',').map(s => s.trim()).filter(Boolean);
    }

    function buildUrl(input) {
        if (input.dataset.liveSearchParam) {
            const url = new URL(window.location.href);
            const value = input.value.trim();
            if (value) url.searchParams.set(input.dataset.liveSearchParam, value);
            else url.searchParams.delete(input.dataset.liveSearchParam);
            if (input.dataset.liveSearchPageParam) url.searchParams.delete(input.dataset.liveSearchPageParam);
            return url;
        }
        const form = input.form;
        const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        url.search = '';
        new FormData(form).forEach(function (value, key) {
            if (typeof value === 'string' && value.trim() !== '') url.searchParams.append(key, value.trim());
        });
        return url;
    }

    function inputSelector(input) {
        return input.id ? '#' + CSS.escape(input.id) : 'input[name="' + input.name + '"][data-live-search]';
    }

    function reloadTo(input, url) {
        try {
            sessionStorage.setItem(RESTORE_KEY, JSON.stringify({
                path: url.pathname, selector: inputSelector(input), caret: input.selectionStart,
            }));
        } catch (e) { /* storage blocked — just no cursor restore */ }
        window.location.href = url.toString();
    }

    function swapIn(input, url, targets) {
        if (inFlight) inFlight.abort();
        inFlight = new AbortController();
        const current = targets.map(sel => document.querySelector(sel));
        current.forEach(el => el && el.setAttribute('data-live-search-busy', ''));

        fetch(url.toString(), { signal: inFlight.signal, credentials: 'same-origin', headers: { 'Accept': 'text/html' } })
            .then(r => r.ok ? r.text() : Promise.reject(new Error('HTTP ' + r.status)))
            .then(function (html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                targets.forEach(function (sel, i) {
                    const fresh = doc.querySelector(sel);
                    if (!fresh || !current[i]) throw new Error('live search: ' + sel + ' not found');
                    current[i].innerHTML = fresh.innerHTML;
                });
                window.history.replaceState(window.history.state, '', url.toString());
                if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
                document.dispatchEvent(new CustomEvent('live-search:updated', { detail: { input: input, url: url } }));
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                console.warn(err);
                reloadTo(input, url);
            })
            .finally(function () {
                current.forEach(el => el && el.removeAttribute('data-live-search-busy'));
            });
    }

    function run(input) {
        const url = buildUrl(input);
        if (url.toString() === (input.dataset.liveSearchLast || window.location.href)) return;
        input.dataset.liveSearchLast = url.toString();
        const targets = targetsOf(input);
        if (targets.length) swapIn(input, url, targets);
        else reloadTo(input, url);
    }

    document.addEventListener('input', function (e) {
        const input = e.target.closest && e.target.closest('input[data-live-search]');
        if (!input || e.isComposing) return;
        clearTimeout(timers.get(input));
        timers.set(input, setTimeout(() => run(input), targetsOf(input).length ? SWAP_DELAY : RELOAD_DELAY));
    });

    // Enter searches right away. Reload-mode boxes keep their own Enter
    // behavior (a form submit / their inline handler) — just drop the pending timer.
    document.addEventListener('keydown', function (e) {
        const input = e.target.closest && e.target.closest('input[data-live-search]');
        if (!input || e.key !== 'Enter') return;
        clearTimeout(timers.get(input));
        if (targetsOf(input).length) {
            e.preventDefault();
            run(input);
        }
    });

    // After a reload-mode search, put the cursor back where it was.
    document.addEventListener('DOMContentLoaded', function () {
        let saved = null;
        try {
            saved = JSON.parse(sessionStorage.getItem(RESTORE_KEY) || 'null');
            sessionStorage.removeItem(RESTORE_KEY);
        } catch (e) { /* ignore */ }
        if (!saved || saved.path !== window.location.pathname) return;
        const el = document.querySelector(saved.selector);
        if (!el) return;
        el.focus();
        const pos = Math.min(saved.caret ?? el.value.length, el.value.length);
        try { el.setSelectionRange(pos, pos); } catch (e) { /* not a text input */ }
    });
})();
</script>
