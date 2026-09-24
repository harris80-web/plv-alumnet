{{--
    Shared avatar circle: real photo when there is one, generic person icon
    otherwise — same visual convention already used by messages/chat
    (w-N h-N rounded-full bg-[#0E0F3B] ... overflow-hidden, <img> or <i
    class="fas fa-user">). Centralized so the ~15 places across the app that
    show a person's photo (sidebar, directory, messages, applicant lists,
    user management, chatbot admin pages, various modals) can't drift out of
    sync with each other one at a time.

    Props:
      photo      string|null  Already-resolved URL, e.g.
                 asset('storage/'.$user->user_profile_picture) — this
                 partial does not touch the raw DB path, so it works
                 whether the caller has a User model, a plain array (an
                 AJAX/modal data payload), or null.
      size       string  Tailwind size classes, default 'w-10 h-10'.
      iconSize   string  Fallback icon's text size, default 'text-sm'.
      bg         string  Fallback/background color class, default the
                 site's navy (bg-[#0E0F3B]).
--}}
@php
    $avatarSize = $size ?? 'w-10 h-10';
    $avatarIconSize = $iconSize ?? 'text-sm';
    $avatarBg = $bg ?? 'bg-[#0E0F3B]';
@endphp
<div class="{{ $avatarSize }} rounded-full {{ $avatarBg }} flex items-center justify-center overflow-hidden shrink-0">
    @if (!empty($photo))
    <img src="{{ $photo }}" class="w-full h-full object-cover" alt="">
    @else
    <i class="fas fa-user text-white {{ $avatarIconSize }}"></i>
    @endif
</div>
