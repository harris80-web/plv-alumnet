{{--
    Fix for wide admin/employer tables (many columns, `whitespace-nowrap`)
    that need horizontal scrolling inside their `.overflow-x-auto` wrapper.
    This page's own `::-webkit-scrollbar { display: none; }` /
    `* { scrollbar-width: none }` reset (used everywhere else on the page on
    purpose, to hide the outer page scrollbar) also hides that wrapper's
    scrollbar — so a table wider than its container has no visible way to
    scroll to it at all, and the extra columns just look cut off. Same root
    cause already fixed once for chat message panels in
    superAdmin/chatbotMessaging.blade.php (#qt-messages/#ht-messages) — this
    is the same override, applied to a table's horizontal scrollbar instead
    of a panel's vertical one.

    Add the `table-scroll` class to the `.overflow-x-auto` wrapper div
    around the wide table.
--}}
<style>
    .table-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .table-scroll::-webkit-scrollbar {
        display: block;
        height: 6px;
    }
    .table-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
</style>
