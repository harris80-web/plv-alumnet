{{--
    Hamburger menu for GUEST (logged-out) visitors on <=1023px — the general-view
    counterpart of partials/user-sidebar.blade.php. Same panel design, spacing,
    typography and slide-in animation (#userSidebar / #menuOverlay, translate-x-full,
    duration-300 ease-in-out), reusing the same ids so public/assets/js/responsive-nav.js
    drives both. Links mirror partials/header-general.blade.php (same routes, same
    active-page logic) — keep the two lists in sync. Never rendered on desktop
    (the toggle is hidden >=1024px and the panel starts off-screen).
--}}
<div id="menuOverlay" class="fixed inset-0 bg-black/50 z-[60] hidden transition-opacity duration-300"></div>

<div id="userSidebar" aria-label="Site menu"
    class="rp-sidebar fixed top-0 right-0 h-full w-80 bg-white z-[70] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <button type="button" onclick="toggleSidebar()" aria-label="Close menu" class="absolute top-5 right-5 text-gray-400 hover:text-gray-600">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" aria-hidden="true" focusable="false">
            <path d="M6 6l12 12M18 6L6 18" />
        </svg>
    </button>

    <div class="p-6 flex flex-col flex-1 overflow-y-auto">
        {{-- Logo --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <img src="{{ asset('assets/PLV-AlumNet LOGOMARK_BLUE 1.png') }}" alt="" class="h-14 w-auto">
            <img src="{{ asset('assets/PLV-AlumNet LETTERMARK_COLORED 2.png') }}" alt="PLV-AlumNet" class="h-9 w-auto">
        </div>

        <div class="border-t border-gray-100 mb-4"></div>

        {{-- Guest actions --}}
        <div class="flex gap-3 pb-4 border-b border-gray-100 mb-4 rp-guest-actions">
            <a href="{{ route('auth.register') }}"
                class="flex-1 flex items-center justify-center px-4 py-2 border-2 border-[#0E0F3B] text-[#0E0F3B] bg-white font-bold text-xs rounded-lg transition hover:bg-[#0E0F3B] hover:text-white">
                SIGN UP
            </a>
            <a href="{{ route('auth.login') }}"
                class="flex-1 flex items-center justify-center px-4 py-2 border-2 border-[#0E0F3B] bg-[#0E0F3B] text-white font-bold text-xs rounded-lg transition hover:bg-[#ED7A07] hover:border-[#ED7A07]">
                LOG IN
            </a>
        </div>

        {{-- Main navigation (same links/order as the desktop header) --}}
        <nav class="space-y-1 flex-grow rp-menu-nav-always" aria-label="Main navigation">
            @php
                $rpGuestLinks = [
                    ['general.home', 'HOME'],
                    ['general.about', 'ABOUT'],
                    ['notices.guestEventsSeminars', 'EVENTS'],
                    ['notices.guestAnnouncements', 'ANNOUNCEMENTS'],
                    ['jobPosting.jobBoard', 'JOB BOARD'],
                ];
            @endphp
            @foreach ($rpGuestLinks as [$rpRoute, $rpLabel])
                <a href="{{ route($rpRoute) }}"
                    class="rp-menu-item -mx-6 px-6 py-2.5 flex items-center text-sm transition {{ request()->routeIs($rpRoute) ? 'rp-menu-item--active bg-[#ED7A07] text-white font-bold' : 'text-[#0E0F3B] font-semibold hover:bg-[#ED7A07] hover:text-white' }}"
                    @if (request()->routeIs($rpRoute)) aria-current="page" @endif>
                    {{ $rpLabel }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
