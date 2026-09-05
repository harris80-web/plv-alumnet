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

    Every checkbox auto-submits its enclosing <form> on change (onchange),
    matching the existing single-select filters' onchange="this.form.submit()"
    behavior — the "More Filter" panel defaults open server-side
    ($moreFiltersActive) whenever a selection survives a reload.
--}}
@php
    $selected = collect($selected ?? [])->map(fn ($v) => (string) $v)->all();
    $count = count($selected);
@endphp
<div class="relative multiselect-filter">
    <button type="button" onclick="toggleMultiselect(this)"
        class="w-full flex items-center pl-11 pr-10 py-1.5 border rounded-full bg-white text-left text-xs {{ $count ? 'text-[#0E0F3B] font-semibold' : 'text-gray-500' }} relative focus:outline-none focus:ring-2 focus:ring-[#C73D1A]">
        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none text-xs">
            <i class="{{ $icon }}"></i>
        </span>
        <span class="truncate">{{ $count ? $count . ' Selected' : $placeholder }}</span>
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
                        onchange="onMultiselectOptionChange(this)">
                    <i class="fas fa-check text-white text-[9px] absolute pointer-events-none opacity-0 peer-checked:opacity-100"></i>
                </span>
                <span class="text-xs text-gray-700">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>
</div>
