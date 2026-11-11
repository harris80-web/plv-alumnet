{{--
    Phone (< 640px) prev/next for the card carousels (general home "Events", alumni dashboard
    "Job Matches" and "Events", employer dashboard "Job Posting"): the same round buttons as the
    Alumni Testimonials, centred under the cards, either side of the dots. The side arrows are
    hidden on phones (they sat on top of the card images).

    Each phone button carries data-carousel-proxy="<id of the side arrow it stands in for>": a tap
    clicks that arrow, so the carousel's own script does the scrolling, and the button copies the
    arrow's state (dimmed at either end, gone when there's nothing to scroll). The row hides itself
    when nothing in it is showing.

    Usage, right under a carousel:
        @include('partials.carousel-mobile-nav', ['prev' => 'eventsCarouselPrev', 'next' => 'eventsCarouselNext',
            'dotsId' => 'eventsCarouselDots', 'label' => 'events'])
    dotsId is optional (the employer carousel has no dots).
--}}
@php
    $cmnArrow = 'w-10 h-10 shrink-0 rounded-full bg-white border border-slate-200 shadow-md text-[#1D264F] flex items-center justify-center transition-colors hover:bg-[#1D264F] hover:text-white disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-[#1D264F]';
@endphp
{{-- No dots: the row only holds the phone buttons, so it's phones-only too. --}}
<div class="mt-6 flex justify-center items-center gap-3 {{ empty($dotsId) ? 'sm:hidden' : '' }}" data-carousel-nav>
    <button type="button" data-carousel-proxy="{{ $prev }}" aria-label="Previous {{ $label }}" class="sm:hidden {{ $cmnArrow }}">
        <i class="fa-solid fa-chevron-left text-sm"></i>
    </button>
    @if (!empty($dotsId))
    <div id="{{ $dotsId }}" class="flex justify-center gap-2"></div>
    @endif
    <button type="button" data-carousel-proxy="{{ $next }}" aria-label="Next {{ $label }}" class="sm:hidden {{ $cmnArrow }}">
        <i class="fa-solid fa-chevron-right text-sm"></i>
    </button>
</div>

@once
<script>
    (function () {
        // An arrow counts as "off" when its carousel has nothing to scroll: the shared carousel
        // script fades it with .opacity-0, the employer carousel sets visibility:hidden.
        function isOff(arrow) {
            return !arrow || arrow.classList.contains('opacity-0') || arrow.style.visibility === 'hidden';
        }

        function sync(nav) {
            let anyShown = false;
            nav.querySelectorAll('[data-carousel-proxy]').forEach(function (btn) {
                const arrow = document.getElementById(btn.dataset.carouselProxy);
                const off = isOff(arrow);
                btn.disabled = !arrow || arrow.disabled;
                // inline style, not the hidden attribute: Tailwind's .flex would win over [hidden]
                btn.style.display = off ? 'none' : '';
                if (!off) anyShown = true;
            });
            const dots = nav.querySelector('[id$="Dots"]');
            if (dots && !dots.classList.contains('hidden') && dots.children.length) anyShown = true;
            nav.style.display = anyShown ? '' : 'none';
        }

        function init() {
            document.querySelectorAll('[data-carousel-nav]').forEach(function (nav) {
                nav.querySelectorAll('[data-carousel-proxy]').forEach(function (btn) {
                    const arrow = document.getElementById(btn.dataset.carouselProxy);
                    if (!arrow) return;
                    btn.addEventListener('click', function () { arrow.click(); });
                    // Follow the side arrow's state as the carousel scrolls or resizes.
                    new MutationObserver(function () { sync(nav); }).observe(arrow, { attributes: true, attributeFilter: ['disabled', 'class', 'style'] });
                });
                const dots = nav.querySelector('[id$="Dots"]');
                if (dots) new MutationObserver(function () { sync(nav); }).observe(dots, { attributes: true, attributeFilter: ['class'], childList: true });
                sync(nav);
            });
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
</script>
@endonce
