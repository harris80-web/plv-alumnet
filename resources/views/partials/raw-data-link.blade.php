{{--
    "View Details" arrow link for a clickable dashboard chart (a right-arrow
    button; hovering or focusing it shows a "View Details" tooltip, styled in
    superAdmin/dashboard.blade.php) — the visible cue
    that the chart (whose whole area is also clickable, see goToReport() in
    superAdmin/dashboard.blade.php) opens the one-row-per-record report
    behind it. A real <a>, so it also works for keyboard / middle-click /
    open-in-new-tab, which the chart's own onclick can't.

    Params:
      $url          — the report page (route('reports.show', ...) etc.)
      $inline       — optional; true renders it in the normal flow (for
                      headers that already have a control on the right)
                      instead of pinned to the card's top-right corner
      $keepFilters  — optional, default true; carries the dashboard's current
                      query string over so the raw data matches the chart.
                      Pass false for widgets that ignore the filters and
                      append their own query to $url instead.

    Hidden for admins without the 'reports' permission — same gate as
    goToReport(), since the target page would just 403 for them.
--}}
@if (Auth::user()->canAccessAdminFeature('reports'))
@php
    $query = ($keepFilters ?? true) ? request()->getQueryString() : null;
    $href = $url . ($query ? (str_contains($url, '?') ? '&' : '?') . $query : '');
@endphp
<a href="{{ $href }}" class="raw-data-link {{ !empty($inline) ? 'raw-data-link--inline' : '' }}"
    aria-label="View details" data-tooltip="View Details" onclick="event.stopPropagation()">
    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
</a>
@endif
