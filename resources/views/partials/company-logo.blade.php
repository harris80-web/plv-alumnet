{{--
    Shared company logo tile: real logo when there is one, generic building
    icon otherwise — mirrors partials/avatar.blade.php for company_logo
    instead of user_profile_picture, matching the existing rounded-square
    convention (not a circle — companyReviews.blade.php's own logo block).

    Props:
      logo      string|null  Already-resolved URL, e.g.
                asset('storage/'.$employer->employer_company_logo).
      size      string  Tailwind size classes, default 'w-16 h-16'.
      iconSize  string  Fallback icon's text size, default 'text-2xl'.
--}}
@php
    $logoSize = $size ?? 'w-16 h-16';
    $logoIconSize = $iconSize ?? 'text-2xl';
@endphp
@if (!empty($logo))
<img src="{{ $logo }}" class="{{ $logoSize }} rounded-2xl object-cover border shrink-0" alt="">
@else
<div class="{{ $logoSize }} rounded-2xl bg-gray-100 flex items-center justify-center text-gray-300 shrink-0">
    <i class="fas fa-building {{ $logoIconSize }}"></i>
</div>
@endif
