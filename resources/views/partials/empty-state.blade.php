{{--
    Shared "nothing here yet" placeholder for empty tables/lists.
    Usage: @include('partials.empty-state', ['icon' => 'fa-inbox', 'message' => 'No applicants yet.'])
    No <td>/wrapper baked in (colspan varies per table) — callers wrap it, e.g.:
        <tr><td colspan="7">@include('partials.empty-state', [...])</td></tr>
--}}
<div class="py-16 text-center text-gray-400">
    <i class="fas {{ $icon ?? 'fa-inbox' }} text-5xl mb-3 block"></i>
    <p class="font-semibold">{{ $message ?? 'Nothing here yet.' }}</p>
</div>
