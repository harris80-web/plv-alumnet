{{--
    Shared fix for the per-row 3-dot "action-dropdown"/"dropdown-menu" menus
    used across the admin management tables (Testimonial, FAQ, Notices, User
    Management, Alumni Directory). Those menus are `position: absolute`
    inside a table wrapper that clips overflow (`overflow-x-auto`/
    `overflow-hidden`, needed for horizontal scrolling on wide tables) — so
    for a row near the bottom of the table, the menu opens downward past the
    wrapper's edge and gets invisibly clipped: the click still registers,
    but nothing visible appears, which looks like "the button does nothing."

    positionFixedDropdown() switches the already-unhidden dropdown to
    `position: fixed`, computed from the trigger button's own
    getBoundingClientRect() — fixed positioning escapes ANY ancestor's
    overflow clipping entirely, and it flips the menu upward (and clamps it
    horizontally) when there isn't enough room below/right in the viewport.
    Same approach already used by jobManagement.blade.php's initMenuButtons().

    Call this right after removing the `hidden` class from a dropdown, e.g.:
        dropdown.classList.remove('hidden');
        positionFixedDropdown(btn, dropdown);
--}}
<script>
    function positionFixedDropdown(btn, dropdown) {
        const rect = btn.getBoundingClientRect();
        const width = dropdown.offsetWidth;
        const height = dropdown.offsetHeight;

        let top = rect.bottom + 4;
        if (top + height > window.innerHeight && rect.top - height - 4 > 0) {
            top = rect.top - height - 4;
        }

        let left = rect.right - width;
        if (left < 4) left = 4;
        if (left + width > window.innerWidth - 4) left = window.innerWidth - width - 4;

        dropdown.style.position = 'fixed';
        dropdown.style.top = top + 'px';
        dropdown.style.left = left + 'px';
        dropdown.style.right = 'auto';
        dropdown.style.marginTop = '0';
    }
</script>
