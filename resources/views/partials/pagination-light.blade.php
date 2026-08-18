{{--
    Same $paginator/$elements contract as partials/pagination.blade.php, but
    in light-on-dark colors — for sections like "Alumni Testimonials" that
    sit on a dark background image, where the default component's
    text-gray-500/text-black styling would be unreadable.
--}}
@if ($paginator->hasPages())
<nav class="mt-12 flex justify-center items-center gap-2 sm:gap-4 text-white/60 font-medium text-sm" aria-label="Pagination">

    @if ($paginator->onFirstPage())
        <span class="opacity-30"><i class="fas fa-chevron-left text-xs"></i></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="hover:text-[#ED7A07] transition-colors">
            <i class="fas fa-chevron-left text-xs"></i>
        </a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="px-1 text-white/40">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="text-white font-bold px-1 border-b-2 border-[#ED7A07]">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="hover:text-[#ED7A07] transition-colors px-1">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="hover:text-[#ED7A07] transition-colors">
            <i class="fas fa-chevron-right text-xs"></i>
        </a>
    @else
        <span class="opacity-30"><i class="fas fa-chevron-right text-xs"></i></span>
    @endif

</nav>
@endif
