{{--
    Same idea as partials/avatar.blade.php (real photo when there is one),
    but falls back to a colored circle showing the person's first initial
    instead of a generic person icon — the convention chatbotMessaging.blade.php
    already used everywhere (Active AI sessions, Live Agent Queue, Chatbot
    History) before any of those spots showed a real photo at all.

    Props:
      photo     string|null  Already-resolved URL, or null/empty for the
                initial fallback.
      name      string  Used for the fallback initial (first character) —
                pass the person's first name, or '?' already baked in by
                the caller if it can be missing.
      size      string  Tailwind size classes, default 'w-7 h-7'.
      textSize  string  Fallback initial's text size, default 'text-[10px]'.
      bg        string  Fallback background color class, default the site's
                navy (bg-[#0E0F3B]).
--}}
@php
    $aiSize = $size ?? 'w-7 h-7';
    $aiTextSize = $textSize ?? 'text-[10px]';
    $aiBg = $bg ?? 'bg-[#0E0F3B]';
@endphp
<div class="{{ $aiSize }} rounded-full {{ $aiBg }} flex items-center justify-center text-white {{ $aiTextSize }} font-bold shrink-0 overflow-hidden">
    @if (!empty($photo))
    <img src="{{ $photo }}" class="w-full h-full object-cover" alt="">
    @else
    {{ mb_substr($name ?? '?', 0, 1) }}
    @endif
</div>
