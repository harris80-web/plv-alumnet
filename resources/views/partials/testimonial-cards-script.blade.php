{{--
    Powers the "Alumni Testimonials" pagination without a full page reload.
    The links rendered by partials/pagination-light.blade.php are plain
    <a href> tags (so they still work with JS disabled), but this
    intercepts clicks inside #testimonial-cards-wrap and fetches just the
    next page's cards from TestimonialController::cardsFragment() instead —
    same partial, so the swapped-in markup is identical to a real page load.
--}}
<script>
    (function () {
        const wrap = document.getElementById('testimonial-cards-wrap');
        if (!wrap) return;

        document.addEventListener('click', function (e) {
            const link = e.target.closest('#testimonial-cards-wrap nav[aria-label="Pagination"] a');
            if (!link) return;
            e.preventDefault();

            const page = new URL(link.href).searchParams.get('page') || '1';
            loadTestimonialsPage(page, link.href);
        });

        async function loadTestimonialsPage(page, fallbackHref) {
            wrap.classList.add('opacity-50', 'pointer-events-none');
            wrap.style.transition = 'opacity 150ms ease';

            try {
                const url = new URL({!! json_encode(route('testimonials.cardsFragment')) !!});
                url.searchParams.set('page', page);
                url.searchParams.set('path', window.location.pathname);

                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Request failed: ' + res.status);

                wrap.innerHTML = await res.text();

                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('page', page);
                history.replaceState(null, '', newUrl.pathname + newUrl.search + '#alumni-testimonials');

                wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (err) {
                // Fetch failed for any reason — fall back to a real navigation
                // rather than leaving the section stuck.
                window.location.href = fallbackHref;
                return;
            } finally {
                wrap.classList.remove('opacity-50', 'pointer-events-none');
            }
        }
    })();
</script>
