{{--
    Floating "back to top" button, bottom-right, for any scrollable page.

    Super-admin pages use an `h-screen overflow-hidden` shell with an inner
    scrolling pane (`.dash-scroll` or the tab-content `.overflow-y-auto`
    wrapper) — `window` never scrolls there. Alumni/employer pages scroll
    the window normally. findScrollContainer() picks whichever one is
    actually overflowing, so this same partial works on both without
    per-page wiring.

    Pass ['nearChatWidget' => true] on pages that also include
    partials.chatbot-widget (alumni/employer) — that toggle button sits at
    the same bottom-6 right-6 spot, so this shifts left to sit beside it
    instead of on top of it.
--}}
<button id="backToTopBtn" type="button" aria-label="Back to top"
    class="fixed bottom-6 {{ ($nearChatWidget ?? false) ? 'right-24' : 'right-6' }} z-[200] w-11 h-11 rounded-full bg-[#C73D1A] text-white shadow-lg flex items-center justify-center opacity-0 pointer-events-none translate-y-2 transition-all duration-200 hover:bg-[#a8331a]">
    <i data-lucide="arrow-up" class="w-5 h-5"></i>
</button>

<script>
(function () {
    function findScrollContainer() {
        const candidates = document.querySelectorAll('main > .dash-scroll, main > .overflow-y-auto');
        for (const el of candidates) {
            if (el.scrollHeight > el.clientHeight + 40) return el;
        }
        return window;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('backToTopBtn');
        if (!btn) return;
        if (window.lucide) lucide.createIcons();

        const target = findScrollContainer();
        const listenTarget = target === window ? window : target;

        function currentScrollTop() {
            return target === window ? (window.scrollY || document.documentElement.scrollTop) : target.scrollTop;
        }

        function onScroll() {
            const show = currentScrollTop() > 300;
            btn.classList.toggle('opacity-0', !show);
            btn.classList.toggle('pointer-events-none', !show);
            btn.classList.toggle('translate-y-2', !show);
        }

        listenTarget.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        btn.addEventListener('click', function () {
            target.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
})();
</script>
