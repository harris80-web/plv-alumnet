{{--
    Powers the "Alumni Testimonials" pagination without a full page reload.
    The arrows and page dots rendered by partials/testimonial-cards.blade.php
    are plain <a href> tags (so they still work with JS disabled), but this
    intercepts clicks inside #testimonial-cards-wrap and fetches just the
    next page's cards from TestimonialController::cardsFragment() instead —
    same partial, so the swapped-in markup is identical to a real page load.

    Neighbouring pages are prefetched in the background, so Next/Prev usually
    swaps instantly; when a page isn't ready yet a "Loading…" overlay shows
    (a request can take several seconds on `php artisan serve`, which handles
    one request at a time — the cards used to just sit faded with no feedback).
--}}
<script>
    (function () {
        const wrap = document.getElementById('testimonial-cards-wrap');
        if (!wrap) return;

        const endpoint = {!! json_encode(route('testimonials.cardsFragment')) !!};
        const cache = new Map(); // page -> Promise<html>
        let loading = false;

        // Loading overlay — lives outside the swapped markup, so it survives each swap.
        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 z-20 hidden items-center justify-center';
        overlay.innerHTML = '<span class="inline-flex items-center gap-2 bg-white text-[#1D264F] text-sm font-semibold px-4 py-2 rounded-full shadow-lg">'
            + '<i class="fa-solid fa-spinner fa-spin"></i> Loading testimonials…</span>';
        // Keep the overlay positioned over the wrap: both go in one relative box.
        const box = document.createElement('div');
        box.style.position = 'relative';
        wrap.parentNode.insertBefore(box, wrap);
        box.appendChild(wrap);
        box.appendChild(overlay);

        function fetchPage(page) {
            if (!cache.has(page)) {
                const url = new URL(endpoint);
                url.searchParams.set('page', page);
                url.searchParams.set('path', window.location.pathname);
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 15000);
                const req = fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
                    .then(res => { if (!res.ok) throw new Error('Request failed: ' + res.status); return res.text(); })
                    .finally(() => clearTimeout(timer));
                req.catch(() => cache.delete(page)); // don't keep a failed request around
                cache.set(page, req);
            }
            return cache.get(page);
        }

        function pageOf(link) {
            return new URL(link.href, window.location.href).searchParams.get('page') || '1';
        }

        // Warm up the pages the arrows point to.
        function prefetchNeighbours() {
            wrap.querySelectorAll('a[data-testimonial-page][aria-label$="testimonials"]').forEach(a => fetchPage(pageOf(a)).catch(() => {}));
        }

        document.addEventListener('click', function (e) {
            // Side arrows, phone arrows and page dots all carry data-testimonial-page.
            const link = e.target.closest('#testimonial-cards-wrap a[data-testimonial-page]');
            if (!link) return;
            e.preventDefault();
            if (!loading) loadTestimonialsPage(pageOf(link), link.href);
        });

        async function loadTestimonialsPage(page, fallbackHref) {
            loading = true;
            // Only show the loading state if the page isn't ready almost immediately.
            const showLoading = setTimeout(() => {
                wrap.style.transition = 'opacity 150ms ease';
                wrap.style.opacity = '0.6';
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }, 120);
            wrap.classList.add('pointer-events-none');

            try {
                wrap.innerHTML = await fetchPage(page);

                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('page', page);
                history.replaceState(null, '', newUrl.pathname + newUrl.search + '#alumni-testimonials');

                // Only scroll when the top of the cards has gone off screen (e.g. the phone arrows under the cards).
                if (wrap.getBoundingClientRect().top < 0) wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                prefetchNeighbours();
            } catch (err) {
                // Fetch failed or timed out — fall back to a real navigation
                // rather than leaving the section stuck.
                window.location.href = fallbackHref;
                return;
            } finally {
                clearTimeout(showLoading);
                wrap.style.opacity = '';
                wrap.classList.remove('pointer-events-none');
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                loading = false;
            }
        }

        // Prefetch once the page itself has finished loading, so it never competes with it.
        if (document.readyState === 'complete') prefetchNeighbours();
        else window.addEventListener('load', prefetchNeighbours);
    })();
</script>
