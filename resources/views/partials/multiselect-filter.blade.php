{{--
    Reusable multi-select checkbox filter dropdown for GET filter bars (Job
    Board's Industry/Job Type/Job Setup filters). Same visual language as the
    single-select dropdowns beside it (rounded-full trigger, grey icon +
    chevron) but opens a checkbox panel instead — checked state is #ED7A07,
    matching the multi-select pattern used on the Alumni Directory.

    Params:
      $name     — input name, rendered as "{name}[]" on each checkbox
      $icon     — Font Awesome class for the trigger's leading icon
      $placeholder — label shown when nothing is selected
      $options  — ['value' => 'label', ...]
      $selected — array of currently-selected values (strings/ints)
      $optionAttrs — optional ['value' => 'data-foo="bar"', ...] raw HTML
                     attributes per checkbox (e.g. the dashboard's Course
                     filter tags each option with data-college so a College
                     filter elsewhere on the same page can cascade it)
      $labelPrefix — optional; once something is picked the trigger reads
                     "{prefix}: {picks}" (e.g. "Year: 2026") instead of
                     the bare picks

    Staged, not auto-submitting — checking/unchecking an option only updates
    this panel's own UI (via partials/staged-multiselect.blade.php's shared
    JS, which the including page must load once). Nothing is sent to the
    server until the enclosing form's own "Apply Filters" button is
    clicked, so several options across several of these dropdowns can be
    picked in one go before the page reloads. The "More Filter" panel
    still defaults open server-side ($moreFiltersActive) whenever a
    selection survives a reload.
--}}
@php
    $selected = collect($selected ?? [])->map(fn ($v) => (string) $v)->all();
    $count = count($selected);
    $optionAttrs = $optionAttrs ?? [];
    // Names the actual picks ("2025, 2026") instead of a bare "2 Selected" —
    // listed in the options' own order, truncated by CSS with the full list
    // in the tooltip. Kept in sync client-side by updateMultiselectLabel().
    $selectedLabels = collect($options)->filter(fn ($label, $value) => in_array((string) $value, $selected, true))->values();
    $labelPrefix = $labelPrefix ?? null;
    $picks = $selectedLabels->implode(', ');
    $triggerText = $count ? ($labelPrefix ? $labelPrefix . ': ' . $picks : $picks) : $placeholder;
@endphp
@once
    <style>
        /* Trigger border: grey at rest, #C73D1A while clicked/focused or its
           panel is open (.is-open, toggled by partials/staged-multiselect).
           Set explicitly so it no longer inherits the text colour — that made
           a filter with picks look navy-bordered. */
        .multiselect-trigger-btn { border-color: #d1d5db; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
        .multiselect-filter.is-open .multiselect-trigger-btn,
        .multiselect-trigger-btn:focus { border-color: #C73D1A; box-shadow: 0 0 0 2px rgba(199, 61, 26, 0.15); }
    </style>
@endonce
<div class="relative multiselect-filter" data-placeholder="{{ $placeholder }}" data-name="{{ $name }}" data-label-prefix="{{ $labelPrefix }}">
    <button type="button" onclick="toggleMultiselect(this)" title="{{ $count ? $placeholder . ': ' . $picks : $placeholder }}"
        class="multiselect-trigger-btn w-full flex items-center pl-11 pr-10 py-1.5 border rounded-full bg-white text-left text-xs {{ $count ? 'text-[#0E0F3B] font-semibold' : 'text-gray-500' }} relative focus:outline-none">
        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs">
            <i class="{{ $icon }}"></i>
        </span>
        <span class="truncate multiselect-trigger-label">{{ $triggerText }}</span>
        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
            <i class="fas fa-chevron-down text-[10px]"></i>
        </span>
    </button>

    <div class="multiselect-panel hidden absolute z-30 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 p-3">
        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer border-b border-gray-100 mb-1 pb-2">
            <span class="relative flex items-center justify-center w-4 h-4 shrink-0">
                <input type="checkbox"
                    class="select-all-checkbox appearance-none w-4 h-4 rounded border border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer"
                    onchange="toggleMultiselectAll(this)">
                <i class="fas fa-check text-white text-[9px] absolute pointer-events-none select-all-check hidden"></i>
            </span>
            <span class="text-xs font-bold text-[#0E0F3B] uppercase">Select All</span>
        </label>
        <div class="max-h-48 overflow-y-auto space-y-0.5">
            @foreach ($options as $value => $label)
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer">
                <span class="relative flex items-center justify-center w-4 h-4 shrink-0">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}"
                        class="option-checkbox peer appearance-none w-4 h-4 rounded border border-gray-300 checked:bg-[#ED7A07] checked:border-[#ED7A07] cursor-pointer"
                        {{ in_array((string) $value, $selected, true) ? 'checked' : '' }}
                        {!! $optionAttrs[$value] ?? '' !!}
                        onchange="onMultiselectOptionChange(this)">
                    <i class="fas fa-check text-white text-[9px] absolute pointer-events-none opacity-0 peer-checked:opacity-100"></i>
                </span>
                <span class="text-xs text-gray-700">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>
</div>
