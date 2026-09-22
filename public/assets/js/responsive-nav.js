/* ==========================================================================
   responsive-nav.js — hamburger / sidebar behaviour for the GENERAL, ALUMNI
   and EMPLOYER views.

   Loaded (defer) by resources/views/partials/responsive-assets.blade.php,
   which is included ONLY by the general/alumni/employer header partials and
   the auth pages — never by admin / super-admin views.

   WHAT THIS FILE DOES
   It does NOT open/close the menu itself. The open/close mechanism and its
   animation are the existing ones, untouched:
       toggleSidebar()  (partials/user-sidebar.blade.php)   → alumni + employer
       toggleSidebar()  (fallback defined below)            → guest menu
   Both flip `translate-x-full` on #userSidebar and `hidden` on #menuOverlay
   (slide-in, `duration-300 ease-in-out`). This file only ADDS around that:
     1. the hamburger button click  → calls the same toggleSidebar()
     2. ESC to close, and focus returned to the button that opened it
     3. close when a link inside the panel is clicked
     4. `html.rp-menu-open` (CSS uses it, <=1023px only, to lock page scroll
        and lift the header above the floating chat / back-to-top buttons)
     5. aria-expanded / aria-hidden / inert bookkeeping
     6. a clean reset when the window crosses the desktop breakpoint, and when
        the page is restored from the back/forward cache with the menu open

   HOW TO EDIT
   - Breakpoint: COMPACT_QUERY below MUST match the "compact" breakpoint
     (max-width: 1023px) used in public/assets/css/responsive-public.css.
   - CLOSE_MS is the sidebar's existing `duration-300` — it is only used to
     keep the scroll-lock / z-index lift until the slide-out has finished.
     It does NOT change any animation or timeout.
   ========================================================================== */
(function () {
    'use strict';

    var COMPACT_QUERY = '(max-width: 1023px)';
    var CLOSE_MS = 300;

    var root = document.documentElement;
    var mq = window.matchMedia(COMPACT_QUERY);
    var sidebar = null;
    var overlay = null;
    var lastToggle = null;
    var releaseTimer = null;

    function isOpen() {
        return !!sidebar && !sidebar.classList.contains('translate-x-full');
    }

    // Guest pages have no user-sidebar partial, so define the same toggle it
    // provides. (On alumni/employer pages the existing function wins.)
    if (typeof window.toggleSidebar !== 'function') {
        window.toggleSidebar = function () {
            var s = document.getElementById('userSidebar');
            var o = document.getElementById('menuOverlay');
            if (!s || !o) return;
            s.classList.toggle('translate-x-full');
            o.classList.toggle('hidden');
        };
    }

    function syncToggles(open) {
        var toggles = document.querySelectorAll('[data-rp-menu-toggle]');
        for (var i = 0; i < toggles.length; i++) {
            toggles[i].setAttribute('aria-expanded', open ? 'true' : 'false');
            if (toggles[i].hasAttribute('data-rp-hamburger')) {
                toggles[i].setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            }
        }
    }

    // Runs whenever #userSidebar's class list changes, i.e. after ANY caller
    // (hamburger, profile icon, X button, overlay click, ESC) has toggled it.
    function applyState() {
        var open = isOpen();
        clearTimeout(releaseTimer);
        if (open) {
            root.classList.add('rp-menu-open');
        } else {
            // Hold the scroll-lock / lifted header until the existing
            // slide-out transition has finished.
            releaseTimer = setTimeout(function () {
                root.classList.remove('rp-menu-open');
            }, CLOSE_MS);
        }
        sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
        if ('inert' in sidebar) sidebar.inert = !open; // off-screen links must not be tab-reachable
        syncToggles(open);

        if (open) {
            var closeBtn = sidebar.querySelector('button');
            if (closeBtn && document.activeElement && document.activeElement.hasAttribute &&
                document.activeElement.hasAttribute('data-rp-menu-toggle')) {
                closeBtn.focus({ preventScroll: true });
            }
        }
    }

    // Instant close with no animation — used for breakpoint changes and
    // back/forward-cache restores, where nothing should be left half-open.
    function forceClose() {
        if (!sidebar) return;
        sidebar.classList.add('translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        clearTimeout(releaseTimer);
        root.classList.remove('rp-menu-open');
        syncToggles(false);
    }

    function init() {
        sidebar = document.getElementById('userSidebar');
        overlay = document.getElementById('menuOverlay');
        if (!sidebar) return;

        applyState();
        clearTimeout(releaseTimer); // initial closed state: nothing to release
        root.classList.remove('rp-menu-open');

        new MutationObserver(applyState).observe(sidebar, { attributes: true, attributeFilter: ['class'] });

        // 1. Hamburger → the existing toggle.
        document.addEventListener('click', function (e) {
            var t = e.target.closest ? e.target.closest('[data-rp-menu-toggle]') : null;
            if (!t) return;
            lastToggle = t;
            if (t.hasAttribute('data-rp-hamburger')) {
                e.preventDefault();
                window.toggleSidebar();
            }
            // The desktop profile icon keeps its own inline onclick="toggleSidebar()".
        }, true);

        // Overlay click closes (user-sidebar already does this on alumni/employer;
        // this covers the guest menu and is a harmless no-op when already closed).
        if (overlay) {
            overlay.addEventListener('click', function () {
                if (isOpen()) window.toggleSidebar();
            });
        }

        // 2. ESC closes and returns focus to the opener.
        document.addEventListener('keydown', function (e) {
            if ((e.key === 'Escape' || e.key === 'Esc') && isOpen()) {
                window.toggleSidebar();
                if (lastToggle && lastToggle.offsetParent !== null) lastToggle.focus({ preventScroll: true });
            }
        });

        // 3. Following a link inside the panel closes it (plain clicks only —
        //    ctrl/cmd/shift-click opens a new tab and should leave it alone).
        sidebar.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (!a || e.defaultPrevented) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (isOpen()) window.toggleSidebar();
        });

        // 6. Cross the desktop/compact breakpoint with the menu open → reset.
        var onBreakpoint = function () { if (isOpen()) forceClose(); };
        if (mq.addEventListener) mq.addEventListener('change', onBreakpoint);
        else if (mq.addListener) mq.addListener(onBreakpoint);

        // Restored from bfcache (Back button) with the menu still open → reset.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted && isOpen()) forceClose();
        });
    }

    /* ------------------------------------------------------------------
       ACTION DROPDOWNS  (per-row 3-dot menus, e.g. alumni directory)
       positionFixedDropdown() (partials/action-dropdown-fix) pins an open
       menu to its button with `position: fixed` + pixel coordinates. When
       the page scrolls / resizes / rotates the button moves but the menu
       does not, so on compact screens close any open menu at that moment.
       (Each page keeps its own outside-click close.) Desktop is untouched.
       ------------------------------------------------------------------ */
    function closeOpenActionMenus(e) {
        if (!mq.matches) return;
        if (e && e.target && e.target.closest && e.target.closest('.action-dropdown')) return; // scrolling inside the menu
        var menus = document.querySelectorAll('.action-dropdown');
        for (var i = 0; i < menus.length; i++) {
            if (getComputedStyle(menus[i]).display === 'none') continue;
            if (menus[i].classList.contains('open')) menus[i].classList.remove('open'); // jobApplicants-style menus
            else menus[i].classList.add('hidden');                                      // directory-style menus
        }
    }
    window.addEventListener('scroll', closeOpenActionMenus, { passive: true, capture: true });
    window.addEventListener('resize', closeOpenActionMenus);

    /* ------------------------------------------------------------------
       MESSAGE STACKING  (compact screens only)
       Success / session-error / validation toasts are separate fixed
       overlays that all sit at the same top offset, so two at once would
       overlap. Give each later toast a margin-top equal to the heights of the
       ones above it. Only spacing changes — the show/hide animation classes
       and the auto-dismiss timeouts (5000 / 7000 / 8000 ms) are untouched.
       ------------------------------------------------------------------ */
    var TOAST_SELECTOR = '#successToast, #sessionErrorToast, #errorToast';

    function stackToasts() {
        var toasts = document.querySelectorAll(TOAST_SELECTOR);
        var offset = 0;
        for (var i = 0; i < toasts.length; i++) {
            var box = toasts[i].firstElementChild;
            if (!box) continue;
            box.style.marginTop = mq.matches ? offset + 'px' : '';
            offset += (box.offsetHeight || 0) + 8;
        }
    }

    function watchToasts() {
        if (!document.body) return;
        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var nodes = Array.prototype.slice.call(mutations[i].addedNodes).concat(Array.prototype.slice.call(mutations[i].removedNodes));
                for (var j = 0; j < nodes.length; j++) {
                    if (nodes[j].nodeType === 1 && nodes[j].matches && nodes[j].matches(TOAST_SELECTOR)) { stackToasts(); return; }
                }
            }
        }).observe(document.body, { childList: true });
        window.addEventListener('resize', stackToasts);
        stackToasts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); watchToasts(); });
    } else {
        init();
        watchToasts();
    }
})();
