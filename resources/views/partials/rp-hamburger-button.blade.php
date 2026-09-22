{{--
    Hamburger toggle for the responsive header (general / alumni / employer).
    Hidden on desktop (>=1024px); shown <=1023px. Clicking it runs the same
    toggleSidebar() the desktop profile icon uses, wired up in
    public/assets/js/responsive-nav.js (data-rp-hamburger). Styling lives in
    public/assets/css/responsive-public.css under [HEADER / NAVBAR].
    Inline SVG (not an icon font) so it renders instantly, with no CDN wait.
--}}
<button type="button" class="rp-hamburger" data-rp-hamburger data-rp-menu-toggle
    aria-label="Open menu" aria-controls="userSidebar" aria-expanded="false">
    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" aria-hidden="true" focusable="false">
        <path d="M4 7h16M4 12h16M4 17h16" />
    </svg>
</button>
