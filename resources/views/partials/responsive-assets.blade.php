{{--
    Responsive layer for the GENERAL, ALUMNI and EMPLOYER views ONLY.

    Loads the ONE central responsive stylesheet + the hamburger/sidebar
    script, and tags <html> with the classes every rule in that stylesheet is
    scoped under:  rp-scope  +  role-general | role-alumni | role-employer.

    Included from: partials/header-general, header-alumni, header-employer
    (which every in-scope page already pulls in) and from the auth pages
    (login / register / forgot / reset), which have no header partial. It is NEVER included by super-admin/admin views, and every rule is
    scoped under `html.rp-scope`, so admin output cannot change even though it
    shares other partials (toasts, pagination, ...) with these views.

    Usage: @include('partials.responsive-assets', ['rpRole' => 'alumni'])
--}}
@php
    $rpRole = $rpRole ?? 'general';
    $rpCssVer = @filemtime(public_path('assets/css/responsive-public.css')) ?: 1;
    $rpJsVer = @filemtime(public_path('assets/js/responsive-nav.js')) ?: 1;
@endphp
<script>document.documentElement.classList.add('rp-scope', 'role-{{ $rpRole }}');</script>
<link rel="stylesheet" href="{{ asset('assets/css/responsive-public.css') }}?v={{ $rpCssVer }}">
<script src="{{ asset('assets/js/responsive-nav.js') }}?v={{ $rpJsVer }}" defer></script>
