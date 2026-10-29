{{--
    Shared "Alumni Testimonials" cards + pagination — included by both
    general/home.blade.php and alumni/dashboard.blade.php for the initial
    page load, AND returned as-is by TestimonialController::cardsFragment()
    for the AJAX page-change requests those two pages' JS makes (see
    partials/testimonial-cards-script.blade.php). Keeping one partial for
    both means the fetched fragment can never drift from the server-rendered
    version.

    Layout follows the site's other cards (Job Matches / Campus Events): white
    rounded-2xl cards, round prev/next arrows at the middle of each side (tablet
    and up; under the cards on phones) and page dots underneath. Every page link
    carries data-testimonial-page so the script above can load it in place.
--}}
@php
    $tPrev = $testimonials->onFirstPage() ? null : $testimonials->previousPageUrl();
    $tNext = $testimonials->hasMorePages() ? $testimonials->nextPageUrl() : null;
    $tArrow = 'w-10 h-10 shrink-0 rounded-full bg-white border border-slate-200 shadow-md text-[#1D264F] flex items-center justify-center transition-colors';
@endphp

<div class="relative max-w-6xl mx-auto {{ $testimonials->hasPages() ? 'sm:px-14' : '' }}">
    @if ($testimonials->hasPages())
    {{-- Tablet and up: arrows at the middle of each side, like the carousels --}}
    @if ($tPrev)
    <a href="{{ $tPrev }}" data-testimonial-page aria-label="Previous testimonials"
        class="hidden sm:flex absolute left-0 top-1/2 -translate-y-1/2 z-10 {{ $tArrow }} hover:bg-[#1D264F] hover:text-white">
        <i class="fa-solid fa-chevron-left text-sm"></i>
    </a>
    @else
    <span aria-hidden="true" class="hidden sm:flex absolute left-0 top-1/2 -translate-y-1/2 z-10 {{ $tArrow }} opacity-30"><i class="fa-solid fa-chevron-left text-sm"></i></span>
    @endif
    @if ($tNext)
    <a href="{{ $tNext }}" data-testimonial-page aria-label="Next testimonials"
        class="hidden sm:flex absolute right-0 top-1/2 -translate-y-1/2 z-10 {{ $tArrow }} hover:bg-[#1D264F] hover:text-white">
        <i class="fa-solid fa-chevron-right text-sm"></i>
    </a>
    @else
    <span aria-hidden="true" class="hidden sm:flex absolute right-0 top-1/2 -translate-y-1/2 z-10 {{ $tArrow }} opacity-30"><i class="fa-solid fa-chevron-right text-sm"></i></span>
    @endif
    @endif

    <div class="grid md:grid-cols-2 gap-4 sm:gap-6">
        @forelse ($testimonials as $testimonial)
        @php
            $tUser = $testimonial->alumnus->user;
            $tProgram = $testimonial->alumnus->program;
            $tBatch = optional($testimonial->alumnus->alumnus_batch)->format('Y');
        @endphp
        <article class="group/card h-full bg-white rounded-2xl text-left flex flex-col p-5 sm:p-6 ring-2 ring-transparent hover:ring-[#ED7A07]/60 transition">
            <div class="flex items-start justify-between gap-3">
                <span class="w-10 h-10 shrink-0 rounded-xl bg-orange-50 text-[#ED7A07] flex items-center justify-center"><i class="fa-solid fa-quote-left"></i></span>
                @if ($tBatch)
                <span class="inline-flex items-center gap-1.5 bg-[#1D264F]/5 text-[#1D264F] text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#ED7A07]"></span> Batch {{ $tBatch }}
                </span>
                @endif
            </div>

            <blockquote class="mt-4 flex-1 text-sm text-slate-600 leading-relaxed break-words">
                {{ $testimonial->testimonial_body }}
            </blockquote>

            <footer class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-3">
                <span class="w-12 h-12 shrink-0 rounded-full bg-[#1D264F] flex items-center justify-center overflow-hidden">
                    @if ($tUser->user_profile_picture)
                    <img src="{{ asset('storage/' . $tUser->user_profile_picture) }}" alt="{{ $tUser->user_first_name }}'s profile picture" class="w-full h-full object-cover">
                    @else
                    <i class="fa-solid fa-user text-lg text-white"></i>
                    @endif
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold uppercase text-[#0E0F3B] break-words transition-colors group-hover/card:text-[#C73D1A] group-hover/card:font-extrabold">{{ $tUser->user_first_name }} {{ $tUser->user_last_name }}</p>
                    <p class="text-xs font-semibold text-[#1D46A4] break-words">{{ $tProgram->program_name ?? 'PLV Alumni' }}</p>
                    @if ($tProgram?->collegeName())
                    <p class="text-[11px] font-medium text-[#C73D1A] break-words">{{ $tProgram->collegeName() }}</p>
                    @endif
                </div>
            </footer>
        </article>
        @empty
        {{-- Empty state: brand navy, not gray. --}}
        <div class="md:col-span-2 bg-white rounded-2xl px-6 py-10 text-center">
            <span class="mx-auto w-14 h-14 rounded-2xl bg-[#1D264F]/5 text-[#1D264F] flex items-center justify-center"><i class="fa-solid fa-quote-left text-2xl"></i></span>
            <p class="mt-3 text-base font-bold text-[#0E0F3B]">No testimonials to show yet.</p>
            <p class="mt-1 text-sm text-[#1D264F]">Stories from PLV graduates will appear here once they're published.</p>
        </div>
        @endforelse
    </div>
</div>

@if ($testimonials->hasPages())
{{-- Page dots (plus the arrows on phones, where they'd otherwise sit on top of the cards) --}}
{{-- Own label, not "Pagination": responsive-public.css's phone rule for nav[aria-label="Pagination"]
     hides the middle items (these dots) and forces the first/last items visible. --}}
<nav class="mt-8 sm:mt-10 flex justify-center items-center gap-3" aria-label="Testimonial pages">
    @if ($tPrev)
    <a href="{{ $tPrev }}" data-testimonial-page aria-label="Previous testimonials" class="sm:hidden {{ $tArrow }} hover:bg-[#1D264F] hover:text-white"><i class="fa-solid fa-chevron-left text-sm"></i></a>
    @else
    <span aria-hidden="true" class="sm:hidden {{ $tArrow }} opacity-30"><i class="fa-solid fa-chevron-left text-sm"></i></span>
    @endif

    @if ($testimonials->lastPage() > 8)
    {{-- Too many pages for a row of dots --}}
    <span class="text-sm font-semibold text-white/80">Page {{ $testimonials->currentPage() }} of {{ $testimonials->lastPage() }}</span>
    @else
    <div class="flex items-center gap-2">
        @foreach ($testimonials->getUrlRange(1, $testimonials->lastPage()) as $page => $url)
            @if ($page == $testimonials->currentPage())
            <span aria-current="page" aria-label="Page {{ $page }}" class="w-6 h-2.5 rounded-full bg-[#ED7A07]"></span>
            @else
            <a href="{{ $url }}" data-testimonial-page aria-label="Page {{ $page }}" class="w-2.5 h-2.5 rounded-full bg-white/40 hover:bg-white/80 transition-colors"></a>
            @endif
        @endforeach
    </div>
    @endif

    @if ($tNext)
    <a href="{{ $tNext }}" data-testimonial-page aria-label="Next testimonials" class="sm:hidden {{ $tArrow }} hover:bg-[#1D264F] hover:text-white"><i class="fa-solid fa-chevron-right text-sm"></i></a>
    @else
    <span aria-hidden="true" class="sm:hidden {{ $tArrow }} opacity-30"><i class="fa-solid fa-chevron-right text-sm"></i></span>
    @endif
</nav>
@endif
