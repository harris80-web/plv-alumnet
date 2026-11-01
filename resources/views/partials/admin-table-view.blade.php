{{--
    Super Admin / Admin tables — included once by partials/super-admin-header
    (so every admin page gets it; pages without a table are unaffected).

    Desktop (>= 1024px):
      1. Every table in the admin content area is drawn at 80% — at 100% browser
         zoom the tables look the way they did at 80% zoom: rows stay on one
         line, every column fits, more rows show. (CSS `zoom`, so text, badges,
         icons and checkboxes all scale together.)
      2. Row menus (the "⋮" action menus) are positioned in screen pixels by
         their scripts (positionFixedDropdown(), jobManagement's
         initMenuButtons(), …), so they undo the table's zoom (0.8 × 1.25 = 1):
         they open at normal size, exactly under their button.
      3. Frozen column headers: each table's own scroll box is capped to the
         screen height and its header row sticks to the top, so the column
         names stay in view while the rows scroll. Boxes that already set
         their own height (chatbot list, report data table) keep it.
    Phones/tablets keep the tables as they were.
--}}
<style>
    @media (min-width: 1024px) {
        main table { zoom: 0.8; }
        main table :is(.action-dropdown, .dropdown-menu) { zoom: 1.25; }

        main :is(.overflow-x-auto, .report-table-scroll):has(> table):not([class*="max-h-"]):not([style*="max-height"]) {
            max-height: calc(100vh - 180px);
        }
        main :is(.overflow-x-auto, .report-table-scroll):has(> table) {
            overflow-y: auto;
        }
        main :is(.overflow-x-auto, .report-table-scroll) > table > thead {
            position: sticky;
            top: 0;
            z-index: 5;
        }
    }
</style>
