{{-- T340a Round 2: saved Site Settings theme applied to the whole portal. Include AFTER the template CSS so these overrides win.
     Same --ss-* var names as admin/site_setting/partials/theme_tab (live preview sets them on <html>).
     With the default theme (#008cff, roboto, light) every rule below resolves to today's look. --}}
@php
    $ssSetting = $schoolBrand ?? new \App\Models\SiteSetting(\App\Models\SiteSetting::THEME_DEFAULTS); // null-safe: no row / no composer data = defaults
    $ssT = $ssSetting->theme;
    [$ssR, $ssG, $ssB] = sscanf(ltrim($ssT['primary'], '#'), '%2x%2x%2x');
    $ssRgb = $ssR . ',' . $ssG . ',' . $ssB;
    $ssLin = fn ($c) => ($c /= 255) <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
    $ssLum = 0.2126 * $ssLin($ssR) + 0.7152 * $ssLin($ssG) + 0.0722 * $ssLin($ssB);
    // text on solid primary surfaces: white unless white has less than 3:1 contrast
    $ssOnSolid = (1.05 / ($ssLum + 0.05)) >= 3 ? '#ffffff' : '#000000';
    // text/icon/border-only primary: = primary when white has >=3:1 (default + built-in palettes unchanged), else darkened toward black until >=4.5:1 on white
    $ssTx = [$ssR, $ssG, $ssB];
    $ssLumOf = fn ($c) => 0.2126 * $ssLin($c[0]) + 0.7152 * $ssLin($c[1]) + 0.0722 * $ssLin($c[2]);
    if ((1.05 / ($ssLum + 0.05)) < 3) {
        for ($ssK = 1; $ssK <= 40 && (1.05 / ($ssLumOf($ssTx) + 0.05)) < 4.5; $ssK++) { $ssTx = array_map(fn ($c) => (int) round($c * 0.92), $ssTx); }
    }
    $ssPrimaryText = sprintf('#%02x%02x%02x', ...$ssTx);
    $ssIsDefault = $ssT['primary'] === '#008cff';
    $ssIsRoboto = $ssT['font_stack'] === 'Roboto, sans-serif';
@endphp
@if ($ssIsRoboto)
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
@elseif ($ssT['font_url'])
    <link href="{{ $ssT['font_url'] }}" rel="stylesheet">
@endif
<style id="site-theme">
    :root {
        --ss-primary: {{ $ssT['primary'] }};
        --ss-accent: {{ $ssT['accent'] }};
        --ss-on: {{ $ssT['on_primary'] }};
        --ss-on-solid: {{ $ssOnSolid }};
        --ss-primary-text: {{ $ssPrimaryText }};
        --ss-primary-rgb: {{ $ssRgb }};
        --ss-font: {!! $ssT['font_stack'] !!};
    }
    html.dark-theme { --ss-primary-text: var(--ss-primary); }
    body { font-family: var(--ss-font); }

    /* links, text/bg/border helpers, active states */
    /* old: a, .btn-link { color: var(--ss-primary); } */
    a, .btn-link { color: var(--ss-primary-text); }
    /* old: .text-primary { color: var(--ss-primary) !important; } */
    .text-primary { color: var(--ss-primary-text) !important; }
    .bg-primary { background-color: var(--ss-primary) !important; }
    /* old: .border-primary { border-color: var(--ss-primary) !important; } */
    .border-primary { border-color: var(--ss-primary-text) !important; }
    .progress-bar { background-color: var(--ss-primary); }
    .list-group-item.active { background-color: var(--ss-primary); border-color: var(--ss-primary); }
    .dropdown-item.active, .dropdown-item:active { background-color: var(--ss-primary); }
    .form-check-input:checked, .form-check-input[type=checkbox]:indeterminate { background-color: var(--ss-primary); border-color: var(--ss-primary); }

    /* buttons */
    .btn-primary, .btn-primary:disabled, .btn-primary.disabled { color: var(--ss-on-solid); background-color: var(--ss-primary); border-color: var(--ss-primary); }
    /* old: .btn-outline-primary, .btn-outline-primary:disabled, .btn-outline-primary.disabled { color: var(--ss-primary); border-color: var(--ss-primary); } */
    .btn-outline-primary, .btn-outline-primary:disabled, .btn-outline-primary.disabled { color: var(--ss-primary-text); border-color: var(--ss-primary-text); }
    .btn-outline-primary:hover,
    .btn-check:active + .btn-outline-primary, .btn-check:checked + .btn-outline-primary,
    .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show, .btn-outline-primary:active { color: var(--ss-on-solid); background-color: var(--ss-primary); border-color: var(--ss-primary); }
    .btn-outline-primary:disabled, .btn-outline-primary.disabled { background-color: transparent; }
    /* old: .btn-inverse-primary, .btn-inverse-primary:hover, .btn-inverse-primary:focus { color: var(--ss-primary); background-color: rgba(var(--ss-primary-rgb), .18); } */
    .btn-inverse-primary, .btn-inverse-primary:hover, .btn-inverse-primary:focus { color: var(--ss-primary-text); background-color: rgba(var(--ss-primary-rgb), .18); }

    /* sidebar (light style) */
    /* old: .logo-text, .toggle-icon { color: var(--ss-primary); } */
    .logo-text, .toggle-icon { color: var(--ss-primary-text); }
    /* old: .sidebar-wrapper .metismenu .mm-active>a, .sidebar-wrapper .metismenu a:active, .sidebar-wrapper .metismenu a:focus, .sidebar-wrapper .metismenu a:hover { color: var(--ss-primary); background-color: rgba(var(--ss-primary-rgb), .05); } */
    .sidebar-wrapper .metismenu .mm-active>a, .sidebar-wrapper .metismenu a:active, .sidebar-wrapper .metismenu a:focus, .sidebar-wrapper .metismenu a:hover { color: var(--ss-primary-text); background-color: rgba(var(--ss-primary-rgb), .05); }

    /* top header + misc */
    /* old: .topbar .navbar .navbar-nav .nav-link:hover, .topbar .navbar .navbar-nav .nav-link:focus { color: var(--ss-primary); } */
    .topbar .navbar .navbar-nav .nav-link:hover, .topbar .navbar .navbar-nav .nav-link:focus { color: var(--ss-primary-text); }
    /* old: .dropdown-large .msg-header-badge { background-color: rgba(var(--ss-primary-rgb), .12); color: var(--ss-primary); } */
    .dropdown-large .msg-header-badge { background-color: rgba(var(--ss-primary-rgb), .12); color: var(--ss-primary-text); }
    /* old: .notification-bell-read { color: var(--ss-primary); } */
    .notification-bell-read { color: var(--ss-primary-text); }
    .back-to-top { color: var(--ss-on-solid); background-color: var(--ss-primary); }
    /* old: .nav-primary.nav-tabs .nav-link.active { color: var(--ss-primary); border-color: var(--ss-primary) var(--ss-primary) #fff; } */
    .nav-primary.nav-tabs .nav-link.active { color: var(--ss-primary-text); border-color: var(--ss-primary-text) var(--ss-primary-text) #fff; }

@unless ($ssIsDefault)
    /* text on solid primary badges/backgrounds (light custom primary) */
    .bg-primary, .badge.bg-primary { color: var(--ss-on-solid); }
    /* dark mode: btn-primary text also follows --ss-on-solid (the dark theme keeps it white; unreadable on a light primary) */
    html.dark-theme .btn-primary, html.dark-theme .btn-primary:hover, html.dark-theme .btn-primary:focus, html.dark-theme .btn-primary:active, html.dark-theme .btn-primary.active, html.dark-theme .btn-primary:disabled, html.dark-theme .btn-primary.disabled, html.dark-theme .btn-check:focus + .btn-primary, html.dark-theme .btn-check:active + .btn-primary, html.dark-theme .btn-check:checked + .btn-primary, html.dark-theme .show > .btn-primary.dropdown-toggle { color: var(--ss-on-solid); }
    /* shade rules: only for a non-default primary (the template's own shades are not a pure function of #008cff) */
    .btn-primary:hover, .btn-check:focus + .btn-primary, .btn-primary:focus,
    .btn-check:active + .btn-primary, .btn-check:checked + .btn-primary, .btn-primary.active, .btn-primary:active, .show > .btn-primary.dropdown-toggle { color: var(--ss-on-solid); background-color: color-mix(in srgb, var(--ss-primary) 88%, #000); border-color: color-mix(in srgb, var(--ss-primary) 88%, #000); }
    .btn-check:focus + .btn-primary, .btn-primary:focus, .btn-check:active + .btn-primary:focus, .btn-check:checked + .btn-primary:focus, .btn-primary.active:focus, .btn-primary:active:focus, .show > .btn-primary.dropdown-toggle:focus { box-shadow: 0 0 0 .25rem rgba(var(--ss-primary-rgb), .5); }
    .btn-check:focus + .btn-outline-primary, .btn-outline-primary:focus { box-shadow: 0 0 0 .25rem rgba(var(--ss-primary-rgb), .5); }
    .btn-inverse-primary { border-color: color-mix(in srgb, var(--ss-primary) 20%, #fff); }
    .topbar .navbar .navbar-nav .nav-link:hover, .topbar .navbar .navbar-nav .nav-link:focus { background-color: color-mix(in srgb, var(--ss-primary) 8%, #fff); }
    /* old: .page-link { color: var(--ss-primary); } */
    .page-link { color: var(--ss-primary-text); }
    .page-item.active .page-link { color: var(--ss-on-solid); background-color: var(--ss-primary); border-color: var(--ss-primary); }
    .form-control:focus, .form-select:focus { border-color: color-mix(in srgb, var(--ss-primary) 50%, #fff); box-shadow: 0 0 0 .25rem rgba(var(--ss-primary-rgb), .25); }
@endunless

    /* Login + reset pages: school icon above the title (was width=60 attribute; now ~96px tall, aspect kept) and the auth/login school logo cap (app.css: 80px) */
    /* old: .login-school-icon { height: 96px; width: auto; max-width: 100%; } */
    .login-school-icon { height: 120px; width: auto; max-width: 100%; }
    /* old: .login-school-logo { max-height: 80px; } (app.css) */
    /* old: .login-school-logo { max-height: 96px; } */
    .login-school-logo { max-height: 120px; }

    /* Round the sidebar school icon in dark mode (the uploaded image has its own square background) */
    html.dark-theme .logo-icon, html.semi-dark .logo-icon, html.color-sidebar .logo-icon { border-radius: 8px; }

    /* Small school name / tagline line under the sidebar role text (span inside .logo-text: inherits its colour, hides with it when collapsed) */
    .logo-text .logo-sub { display: block; /* old: max-width: 125px; */ max-width: 100%; margin-top: 1px; font-size: 11px; font-weight: 400; letter-spacing: 0; line-height: 1.2; opacity: .7; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* sidebar style "School colour" (html.color-sidebar from the template + html.ss-sidebar-brand) */
    html.ss-sidebar-brand .sidebar-wrapper { background-color: var(--ss-primary); }
    /* old: html.ss-sidebar-brand .sidebar-header { border-bottom-color: color-mix(in srgb, var(--ss-on-solid) 15%, transparent); } */
    html.ss-sidebar-brand .sidebar-header { border-bottom-color: color-mix(in srgb, var(--ss-on-solid) 15%, transparent); }
    /* dark mode: the template dark-theme rule keeps the header dark (#12181a); match the brand sidebar so --ss-on-solid text stays readable */
    html.ss-sidebar-brand.dark-theme .sidebar-wrapper .sidebar-header { background: var(--ss-primary); border-right-color: transparent; border-bottom-color: color-mix(in srgb, var(--ss-on-solid) 15%, transparent); }
    html.ss-sidebar-brand.dark-theme .sidebar-wrapper .sidebar-header .toggle-btn { color: var(--ss-on-solid); }
    html.ss-sidebar-brand .menu-label { color: color-mix(in srgb, var(--ss-on-solid) 65%, transparent); }
    html.ss-sidebar-brand .sidebar-wrapper .sidebar-header .logo-text, html.ss-sidebar-brand .sidebar-wrapper .sidebar-header .toggle-icon { color: var(--ss-on-solid); }
    html.ss-sidebar-brand .sidebar-wrapper .metismenu a { color: color-mix(in srgb, var(--ss-on-solid) 85%, transparent); }
    html.ss-sidebar-brand .sidebar-wrapper .metismenu .mm-active>a, html.ss-sidebar-brand .sidebar-wrapper .metismenu a:active, html.ss-sidebar-brand .sidebar-wrapper .metismenu a:focus, html.ss-sidebar-brand .sidebar-wrapper .metismenu a:hover { color: var(--ss-on-solid); background: color-mix(in srgb, var(--ss-on-solid) 15%, transparent); }
</style>
<script>
    // Sidebar style on <html> before first paint: light = no change, dark = semi-dark, school colour = color-sidebar + ss-sidebar-brand
    (function () {
        var c = @json($ssT['sidebar_class']);
        if (c) { c.split(' ').forEach(function (n) { document.documentElement.classList.add(n); }); }
    })();
</script>
