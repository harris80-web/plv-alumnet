<style>
    .carousel-track {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .carousel-track::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    // Reusable horizontal card carousel: scroll-snap track + prev/next
    // buttons + dot pagination. Cards themselves stay responsive via plain
    // CSS width classes (w-full on mobile, fractional widths on larger
    // screens) — this script never re-groups cards into JS-computed
    // "slides", so it doesn't have to recompute anything on resize. Dots
    // are based on a fixed logical page size (itemsPerPage, matching the
    // desktop column count), so a dot jumps to the start of its group of
    // cards; buttons remain the precise one-screen-at-a-time control.
    function initCardCarousel(opts) {
        const track = document.getElementById(opts.trackId);
        const prevBtn = document.getElementById(opts.prevId);
        const nextBtn = document.getElementById(opts.nextId);
        const dotsWrap = opts.dotsId ? document.getElementById(opts.dotsId) : null;
        if (!track) return;

        const cards = Array.from(track.children);
        if (cards.length === 0) {
            prevBtn?.classList.add('hidden');
            nextBtn?.classList.add('hidden');
            dotsWrap?.classList.add('hidden');
            return;
        }

        const perPage = opts.itemsPerPage || 3;
        const pageCount = Math.max(1, Math.ceil(cards.length / perPage));

        if (dotsWrap) {
            dotsWrap.innerHTML = '';
            for (let i = 0; i < pageCount; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                dot.className = 'w-2.5 h-2.5 rounded-full transition-colors bg-slate-300';
                dot.addEventListener('click', () => {
                    track.scrollTo({ left: i * perPage * (track.scrollWidth / cards.length), behavior: 'smooth' });
                });
                dotsWrap.appendChild(dot);
            }
        }

        function dots() {
            return dotsWrap ? Array.from(dotsWrap.children) : [];
        }

        function updateActiveDot() {
            const d = dots();
            if (!d.length) return;
            const approxCardWidth = track.scrollWidth / cards.length;
            const currentCardIndex = Math.round(track.scrollLeft / approxCardWidth);
            const page = Math.min(d.length - 1, Math.floor(currentCardIndex / perPage));
            d.forEach((dot, i) => {
                dot.classList.toggle('bg-[#C73D1A]', i === page);
                dot.classList.toggle('bg-slate-300', i !== page);
            });
        }

        function updateButtons() {
            if (prevBtn) prevBtn.disabled = track.scrollLeft <= 4;
            if (nextBtn) nextBtn.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 4;
        }

        // Buttons keep their responsive hidden/md:flex base classes (arrows
        // are desktop-only) — this only fades them out when there's nothing
        // to scroll, it never touches `hidden` or it'd fight that base rule.
        function checkOverflow() {
            const hasOverflow = track.scrollWidth > track.clientWidth + 4;
            [prevBtn, nextBtn].forEach(btn => {
                if (!btn) return;
                btn.classList.toggle('opacity-0', !hasOverflow);
                btn.classList.toggle('pointer-events-none', !hasOverflow);
            });
            dotsWrap?.classList.toggle('hidden', !hasOverflow);
        }

        prevBtn?.addEventListener('click', () => track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' }));
        nextBtn?.addEventListener('click', () => track.scrollBy({ left: track.clientWidth, behavior: 'smooth' }));

        let scrollTimer;
        track.addEventListener('scroll', () => {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => { updateActiveDot(); updateButtons(); }, 80);
        }, { passive: true });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => { checkOverflow(); updateActiveDot(); updateButtons(); }, 120);
        });

        checkOverflow();
        updateActiveDot();
        updateButtons();
    }
</script>
