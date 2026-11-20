{{-- T340a: Appearance & theme tab. Everything here is a live preview only until "Save theme" is pressed. --}}
@php
    use App\Models\SiteSetting;

    $curPalette = old('theme_palette', $setting->theme_palette);
    if ($curPalette !== 'custom' && !isset(SiteSetting::PALETTES[$curPalette])) {
        $curPalette = 'default';
    }
    $curFont = old('font_family', $setting->font_family);
    if (!isset(SiteSetting::FONTS[$curFont])) {
        $curFont = 'roboto';
    }
    $curSidebar = old('sidebar_style', $setting->sidebar_style);
    if (!isset(SiteSetting::SIDEBAR_STYLES[$curSidebar])) {
        $curSidebar = 'light';
    }
    // Custom pickers start from the saved colours (safe fallback to today's look)
    $curPrimary = old('primary_color', $theme['primary']);
    $curAccent = old('accent_color', $theme['accent']);
    $canManage = auth()->user()->can('manage-site-settings');

    $paletteMap = [];
    foreach (SiteSetting::PALETTES as $k => [$label, $p, $a]) {
        $paletteMap[$k] = [$p, $a];
    }
    $fontMap = [];
    foreach (SiteSetting::FONTS as $k => [$label, $stack, $google]) {
        $fontMap[$k] = $stack;
    }
    $googleFamilies = collect(SiteSetting::FONTS)->pluck(2)->filter()->map(fn($f) => 'family=' . $f . ':wght@400;600')->implode('&');
@endphp

{{-- autocomplete off: a plain reload must show the saved theme, not browser-restored radio state --}}
{{-- old: <form action="{{ route('site_setting.theme.update') }}" method="POST" id="themeForm" autocomplete="off"> --}}
<form action="{{ route('site_setting.theme.update') }}" method="POST" id="themeForm" autocomplete="off" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-lg-8">

            {{-- Colours --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semi-bold text-primary mb-0">Colours</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(SiteSetting::PALETTES as $key => [$label, $p, $a])
                        <div class="col-6 col-md-4">
                            <label class="w-100 mb-0">
                                <input type="radio" name="theme_palette" value="{{ $key }}" class="ss-radio" {{ $curPalette === $key ? 'checked' : '' }} {{ $canManage ? '' : 'disabled' }}>
                                <span class="ss-opt d-block border rounded p-2">
                                    <span class="ss-strip d-flex rounded overflow-hidden mb-2">
                                        <span style="flex: 3; background: {{ $p }};"></span>
                                        <span style="flex: 1; background: {{ $a }};"></span>
                                    </span>
                                    <span class="small fw-semibold">{{ $label }}</span>
                                </span>
                            </label>
                        </div>
                        @endforeach

                        {{-- Custom --}}
                        <div class="col-12 col-md-4">
                            <div class="position-relative h-100">
                                <label class="w-100 mb-0 h-100">
                                    <input type="radio" name="theme_palette" value="custom" class="ss-radio" id="paletteCustom" {{ $curPalette === 'custom' ? 'checked' : '' }} {{ $canManage ? '' : 'disabled' }}>
                                    <span class="ss-opt d-block border rounded p-2 h-100">
                                        <span class="ss-strip d-flex rounded overflow-hidden mb-2" id="customStrip">
                                            <span style="flex: 3; background: {{ $curPrimary }};"></span>
                                            <span style="flex: 1; background: {{ $curAccent }};"></span>
                                        </span>
                                        <span class="small fw-semibold d-block mb-2">Custom</span>
                                        <span class="d-flex gap-3">
                                            <span class="small text-muted">Primary<br>
                                                <input type="color" name="primary_color" id="customPrimary" value="{{ $curPrimary }}" class="form-control form-control-color p-0 border-0" {{ $canManage ? '' : 'disabled' }}>
                                            </span>
                                            <span class="small text-muted">Accent<br>
                                                <input type="color" name="accent_color" id="customAccent" value="{{ $curAccent }}" class="form-control form-control-color p-0 border-0" {{ $canManage ? '' : 'disabled' }}>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Font --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semi-bold text-primary mb-0">Font</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(SiteSetting::FONTS as $key => [$label, $stack, $google])
                        <div class="col-md-6">
                            <label class="w-100 mb-0">
                                <input type="radio" name="font_family" value="{{ $key }}" class="ss-radio" {{ $curFont === $key ? 'checked' : '' }} {{ $canManage ? '' : 'disabled' }}>
                                <span class="ss-opt d-block border rounded p-2" style="font-family: {{ $stack }};">
                                    <span class="d-block fw-semibold">{{ $label }}</span>
                                    <span class="d-block small text-muted">Modern School System - Rs 12,450 - 98%</span>
                                </span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sidebar style --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semi-bold text-primary mb-0">Sidebar style</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(['dark' => 'Dark', 'light' => 'Light', 'brand' => 'School colour'] as $key => $label)
                        <div class="col-4">
                            <label class="w-100 mb-0">
                                <input type="radio" name="sidebar_style" value="{{ $key }}" class="ss-radio" {{ $curSidebar === $key ? 'checked' : '' }} {{ $canManage ? '' : 'disabled' }}>
                                <span class="ss-opt d-block border rounded p-2">
                                    <span class="ss-mock d-flex rounded overflow-hidden border mb-2">
                                        <span class="ss-mock-bar ss-sb-{{ $key }}"></span>
                                        <span class="flex-grow-1 p-1">
                                            <span class="d-block ss-line mb-1" style="width: 70%;"></span>
                                            <span class="d-block ss-line mb-1" style="width: 90%;"></span>
                                            <span class="d-block ss-line" style="width: 50%;"></span>
                                        </span>
                                    </span>
                                    <span class="small fw-semibold">{{ $label }}</span>
                                </span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- T340b: Login page (heading, message, background picture; saved with Save theme) --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-semi-bold text-primary mb-0">Login page</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="login_heading" class="form-label">Heading</label>
                            <input type="text" name="login_heading" id="login_heading" maxlength="150"
                                placeholder="e.g. Welcome to Mount Carmel School"
                                class="form-control form-control-sm @error('login_heading') is-invalid @enderror"
                                value="{{ old('login_heading', $setting->login_heading) }}" {{ $canManage ? '' : 'disabled' }}>
                            @error('login_heading')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="login_message" class="form-label">Message</label>
                            <textarea name="login_message" id="login_message" rows="2" maxlength="255"
                                placeholder="e.g. Log in to see attendance, results and fee updates."
                                class="form-control form-control-sm @error('login_message') is-invalid @enderror" {{ $canManage ? '' : 'disabled' }}>{{ old('login_message', $setting->login_message) }}</textarea>
                            @error('login_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="login_background" class="form-label">Background picture</label>
                            <input type="file" name="login_background" id="login_background" accept="image/*"
                                class="form-control form-control-sm @error('login_background') is-invalid @enderror" {{ $canManage ? '' : 'disabled' }}>
                            <small class="text-muted d-block">Wide picture, recommended 1600x1200. JPG, PNG or WebP, max 2MB. Shown on the left side of the role login pages.</small>
                            @error('login_background')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <img id="loginBgPreview" src="{{ $setting->login_background_url }}" class="img-fluid rounded border mt-2 {{ $setting->login_background_url ? '' : 'd-none' }}" style="max-height: 160px;" alt="Login background">
                            @if($setting->login_background_url && $canManage)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_login_background" id="remove_login_background" value="1">
                                <label class="form-check-label" for="remove_login_background">Remove background</label>
                            </div>
                            @endif
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">If these are empty, the login pages keep the default look. Saved with the Save theme button.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="position-sticky" style="top: 80px;">
                {{-- Live preview (JS only, saves nothing) --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="fw-semi-bold text-primary mb-0">Live preview</h5>
                    </div>
                    <div class="card-body">
                        {{-- old: style="--p: primary; --a: accent; --on: on_primary; font-family: stack;" (now driven by the page-level --ss-* vars) --}}
                        <div id="ssPreview" class="border rounded overflow-hidden d-flex">
                            <div class="ssp-side" data-sb="{{ $curSidebar }}">
                                <span class="ssp-dot"></span>
                                <span class="ssp-item ssp-item-active"></span>
                                <span class="ssp-item"></span>
                                <span class="ssp-item"></span>
                            </div>
                            <div class="flex-grow-1 ssp-main">
                                <div class="ssp-banner">
                                    <div class="fw-semibold">Good morning, Admin</div>
                                    <div class="ssp-banner-sub">This is how page headers look</div>
                                </div>
                                <div class="p-2">
                                    <div class="d-flex gap-2 mb-2">
                                        <div class="ssp-stat ssp-stat-p">
                                            <div class="ssp-stat-label">STUDENTS</div>
                                            <div class="ssp-stat-val">{{ number_format($counts['students']) }}</div>
                                        </div>
                                        <div class="ssp-stat ssp-stat-a">
                                            <div class="ssp-stat-label">COLLECTED</div>
                                            <div class="ssp-stat-val">Rs. 8.2L</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <button type="button" class="btn btn-sm ssp-btn">Primary</button>
                                        <span class="badge ssp-badge">Badge</span>
                                        <span class="badge rounded-pill ssp-pill">Accent</span>
                                    </div>
                                    <div class="d-flex gap-3 ssp-tabs small">
                                        <span class="ssp-tab ssp-tab-active">Active tab</span>
                                        <span class="ssp-tab">Other tab</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        @if($canManage)
                        <div class="d-flex gap-2">
                            {{-- old: class="btn btn-sm btn-success px-4" --}}
                            <button type="submit" class="btn btn-sm btn-success px-4 ss-save">Save theme</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="themeResetBtn">Reset to default</button>
                        </div>
                        @endif
                        <small class="text-muted d-block mt-2">Changes apply to every user, including portals.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@can('manage-site-settings')
<form action="{{ route('site_setting.theme.reset') }}" method="POST" id="themeResetForm" class="d-none">
    @csrf
</form>
@endcan

@push('styles')
{{-- Preview only: loads every font on this page so each option shows in its own font --}}
<link href="https://fonts.googleapis.com/css2?{{ $googleFamilies }}&display=swap" rel="stylesheet">
<style>
    .ss-radio { position: absolute; opacity: 0; pointer-events: none; }
    .ss-opt { cursor: pointer; background: var(--bs-body-bg, #fff); transition: border-color .15s, box-shadow .15s; }
    .ss-radio:disabled + .ss-opt { cursor: default; opacity: .75; }
    /* old: .ss-radio:checked + .ss-opt { border-color: var(--bs-primary) !important; box-shadow: 0 0 0 2px rgb(var(--bs-primary-rgb) / 25%); } */
    /* old: .ss-radio:checked + .ss-opt { border-color: var(--ss-primary) !important; box-shadow: 0 0 0 2px rgb(var(--ss-primary-rgb) / 25%); } */
    .ss-radio:checked + .ss-opt { border-color: var(--ss-primary) !important; box-shadow: 0 0 0 2px rgba(var(--ss-primary-rgb), .25); }
    /* old: .ss-radio:focus-visible + .ss-opt { box-shadow: 0 0 0 3px rgb(var(--bs-primary-rgb) / 40%); } */
    /* old: .ss-radio:focus-visible + .ss-opt { box-shadow: 0 0 0 3px rgb(var(--ss-primary-rgb) / 40%); } */
    .ss-radio:focus-visible + .ss-opt { box-shadow: 0 0 0 3px rgba(var(--ss-primary-rgb), .4); }
    .ss-opt { position: relative; }
    /* selected badge: small round check in the top-right corner */
    .ss-radio:checked + .ss-opt::after {
        content: ""; position: absolute; top: -8px; right: -8px; width: 20px; height: 20px; border-radius: 50%;
        background-color: var(--ss-primary); border: 2px solid #fff; box-shadow: 0 1px 3px rgb(0 0 0 / 30%);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M4 8.5l2.5 2.5L12 5.5' fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-size: 14px 14px; background-position: center; background-repeat: no-repeat;
    }
    html.dark-theme .ss-radio:checked + .ss-opt::after { border-color: #1b2326; }
    #themeForm .ss-save { background: var(--ss-primary); border-color: var(--ss-primary); color: var(--ss-on); }
    #themeForm .ss-save:hover { filter: brightness(.92); }
    .ss-strip { height: 28px; }
    .ss-mock { height: 64px; background: #f7f7ff; }
    .ss-mock-bar { width: 26%; }
    .ss-sb-dark { background: #1f2937; }
    .ss-sb-light { background: #fff; border-right: 1px solid #e4e4e4; }
    /* old: .ss-sb-brand { background: var(--ss-mock-brand, #008cff); } */
    .ss-sb-brand { background: var(--ss-primary, #008cff); }
    .ss-line { height: 5px; border-radius: 3px; background: #d5d9e0; }
    /* live preview */
    #ssPreview { min-height: 190px; background: #fff; color: #4c5258; font-family: var(--ss-font); }
    .ssp-side { width: 54px; padding: 8px 6px; display: flex; flex-direction: column; gap: 6px; }
    .ssp-side[data-sb="light"] { background: #fff; border-right: 1px solid #e4e4e4; --ssp-item: #d5d9e0; }
    .ssp-side[data-sb="dark"] { background: #1f2937; --ssp-item: rgb(255 255 255 / 30%); }
    .ssp-side[data-sb="brand"] { background: var(--ss-primary); --ssp-item: color-mix(in srgb, var(--ss-on) 45%, transparent); }
    .ssp-dot { width: 18px; height: 18px; border-radius: 50%; background: var(--ss-primary); margin-bottom: 4px; }
    .ssp-side[data-sb="brand"] .ssp-dot { background: var(--ss-on); }
    .ssp-item { height: 6px; border-radius: 3px; background: var(--ssp-item); }
    .ssp-item-active { background: var(--ss-primary); }
    .ssp-side[data-sb="brand"] .ssp-item-active { background: var(--ss-on); }
    /* old: .ssp-banner { padding: 10px; font-size: 12px; color: var(--ss-on); background: linear-gradient(135deg, var(--ss-primary) 0%, var(--ss-primary) 45%, var(--ss-accent) 100%); } */
    .ssp-banner { padding: 10px; font-size: 12px; color: #fff; text-shadow: 0 1px 2px rgb(0 0 0 / 35%); background: linear-gradient(rgba(0,0,0,.15), rgba(0,0,0,.15)), linear-gradient(135deg, var(--ss-primary), var(--ss-accent)); }
    .ssp-banner-sub { font-size: 10px; opacity: .85; }
    .ssp-stat { flex: 1; border-radius: 8px; padding: 6px 8px; }
    /* old: .ssp-stat-p { ...primary -> primary 70% #000; color: var(--ss-on); } .ssp-stat-a { ...accent -> accent 70% #000; color: #000; } */
    .ssp-stat-p { background: linear-gradient(135deg, var(--ss-primary), color-mix(in srgb, var(--ss-primary) 70%, #000)); color: #fff; text-shadow: 0 1px 2px rgb(0 0 0 / 35%); }
    .ssp-stat-a { background: linear-gradient(135deg, var(--ss-accent), color-mix(in srgb, var(--ss-accent) 65%, #000)); color: #fff; text-shadow: 0 1px 2px rgb(0 0 0 / 35%); }
    .ssp-stat-label { font-size: 9px; letter-spacing: .5px; opacity: .85; }
    .ssp-stat-val { font-size: 15px; font-weight: 700; line-height: 1.2; }
    .ssp-pill { background: color-mix(in srgb, var(--ss-accent) 28%, transparent); color: #4c5258; }
    .ssp-tabs { border-bottom: 1px solid #e4e4e4; }
    .ssp-tab { padding: 3px 0; color: #8a9097; border-bottom: 2px solid transparent; margin-bottom: -1px; }
    /* old: .ssp-tab-active { color: var(--ss-primary); border-bottom-color: var(--ss-primary); font-weight: 600; } */
    .ssp-tab-active { color: var(--ss-primary-text); border-bottom-color: var(--ss-primary); font-weight: 600; }
    .ss-opt { color: #4c5258; }
    .ss-opt .text-muted { color: #6c757d !important; }
    /* dark mode: option cards use the dark card background and readable text */
    html.dark-theme .ss-opt { background: #1b2326; color: #e4e5e6; border-color: rgb(255 255 255 / 16%) !important; }
    html.dark-theme .ss-opt .text-muted { color: #a8adb0 !important; }
    html.dark-theme .ss-mock { background: #12181a; border-color: rgb(255 255 255 / 16%) !important; }
    html.dark-theme .ss-line { background: rgb(255 255 255 / 25%); }
    html.dark-theme .ss-sb-light { background: #e4e5e6; }
    /* old: html.dark-theme .ss-radio:checked + .ss-opt { border-color: var(--bs-primary) !important; } */
    html.dark-theme .ss-radio:checked + .ss-opt { border-color: var(--ss-primary) !important; }
    .ssp-top { border-bottom: 1px solid #e4e4e4; }
    /* old: .ssp-btn { background: var(--p); color: var(--on); border-color: var(--p); } .ssp-badge { background: var(--a); color: #000; } */
    .ssp-btn { background: var(--ss-primary); color: var(--ss-on); border-color: var(--ss-primary); }
    /* old: .ssp-badge { background: rgb(var(--ss-primary-rgb) / 15%); color: var(--ss-primary); } */
    /* old: .ssp-badge { background: rgba(var(--ss-primary-rgb), .15); color: var(--ss-primary); } */
    .ssp-badge { background: rgba(var(--ss-primary-rgb), .15); color: var(--ss-primary-text); }
    html.dark-theme #ssPreview { background: #12181a; color: #cfd3d6; }
    /* old: html.dark-theme .ssp-badge { background: rgb(var(--ss-primary-rgb) / 30%); color: color-mix(in srgb, var(--ss-primary) 45%, #fff); } */
    html.dark-theme .ssp-badge { background: rgba(var(--ss-primary-rgb), .3); color: color-mix(in srgb, var(--ss-primary) 45%, #fff); }
    html.dark-theme .ssp-pill { background: color-mix(in srgb, var(--ss-accent) 35%, transparent); color: #e4e5e6; }
    html.dark-theme .ssp-tabs { border-color: rgb(255 255 255 / 14%); }
    html.dark-theme .ssp-tab { color: #a8adb0; }
    html.dark-theme .ssp-tab-active { color: color-mix(in srgb, var(--ss-primary) 55%, #fff); border-bottom-color: var(--ss-primary); }
    html.dark-theme .ssp-top { border-color: rgb(255 255 255 / 12%); }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        var PALETTES = @json($paletteMap);
        var FONTS = @json($fontMap);
        var form = document.getElementById('themeForm');
        var prev = document.getElementById('ssPreview');
        var cp = document.getElementById('customPrimary');
        var ca = document.getElementById('customAccent');
        var customRadio = document.getElementById('paletteCustom');
        var customStrip = document.getElementById('customStrip');

        // black or white text, whichever contrasts better with the hex (same WCAG rule as HasCardColors::contrastText)
        function onColor(hex) {
            var lin = function (h) { var c = parseInt(h, 16) / 255; return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); };
            var l = 0.2126 * lin(hex.substr(1, 2)) + 0.7152 * lin(hex.substr(3, 2)) + 0.0722 * lin(hex.substr(5, 2));
            return ((l + 0.05) / 0.05) > (1.05 / (l + 0.05)) ? '#000000' : '#ffffff';
        }
        // text on solid primary surfaces (buttons, sidebar): white unless white has less than 3:1 contrast (same rule as partials/site_theme)
        function onSolid(hex) {
            var lin = function (h) { var c = parseInt(h, 16) / 255; return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); };
            var l = 0.2126 * lin(hex.substr(1, 2)) + 0.7152 * lin(hex.substr(3, 2)) + 0.0722 * lin(hex.substr(5, 2));
            return (1.05 / (l + 0.05)) >= 3 ? '#ffffff' : '#000000';
        }
        // text/icon/border-only primary: = primary when white has >=3:1, else darkened toward black until >=4.5:1 on white (same rule as partials/site_theme)
        function primaryText(hex) {
            var lin = function (c) { c = c / 255; return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); };
            var rgb = [parseInt(hex.substr(1, 2), 16), parseInt(hex.substr(3, 2), 16), parseInt(hex.substr(5, 2), 16)];
            var lum = function (v) { return 0.2126 * lin(v[0]) + 0.7152 * lin(v[1]) + 0.0722 * lin(v[2]); };
            if (1.05 / (lum(rgb) + 0.05) >= 3) { return hex; }
            for (var i = 0; i < 40 && 1.05 / (lum(rgb) + 0.05) < 4.5; i++) { rgb = rgb.map(function (c) { return Math.round(c * 0.92); }); }
            return '#' + rgb.map(function (c) { return (c < 16 ? '0' : '') + c.toString(16); }).join('');
        }
        function checked(name) {
            var el = form.querySelector('input[name="' + name + '"]:checked');
            return el ? el.value : null;
        }

        function refresh() {
            var pal = checked('theme_palette');
            var p, a;
            if (pal === 'custom' || !PALETTES[pal]) { p = cp.value; a = ca.value; }
            else { p = PALETTES[pal][0]; a = PALETTES[pal][1]; }

            /* old: prev.style.setProperty('--p', p); '--a', a; '--on', onColor(p); prev.style.fontFamily = ...; form.style.setProperty('--ss-mock-brand', p); */
            // Live vars on <html>: this page uses them now; the Round 2 global partial will use the same names
            var root = document.documentElement.style;
            root.setProperty('--ss-primary', p);
            root.setProperty('--ss-accent', a);
            root.setProperty('--ss-on', onColor(p));
            root.setProperty('--ss-on-solid', onSolid(p));
            /* old: root.setProperty('--ss-primary-text', primaryText(p)); */
            root.setProperty('--ss-primary-text', document.documentElement.classList.contains('dark-theme') ? p : primaryText(p)); // dark mode: primary itself (same as site_theme)
            root.setProperty('--ss-primary-rgb', parseInt(p.substr(1, 2), 16) + ',' + parseInt(p.substr(3, 2), 16) + ',' + parseInt(p.substr(5, 2), 16));
            root.setProperty('--ss-font', FONTS[checked('font_family')] || FONTS.roboto);
            prev.querySelector('.ssp-side').setAttribute('data-sb', checked('sidebar_style') || 'light');

            customStrip.children[0].style.background = cp.value;
            customStrip.children[1].style.background = ca.value;
        }

        form.addEventListener('change', refresh);
        [cp, ca].forEach(function (el) {
            el.addEventListener('input', function () {
                if (customRadio && !customRadio.disabled) { customRadio.checked = true; }
                refresh();
            });
        });
        refresh();

        // T340b: preview the picked login background before saving
        var bgInput = document.getElementById('login_background');
        var bgPrev = document.getElementById('loginBgPreview');
        if (bgInput && bgPrev) {
            // remember the saved preview so it can be restored when a non-image is picked
            var bgOrigSrc = bgPrev.getAttribute('src') || '';
            var bgOrigHidden = bgPrev.classList.contains('d-none');
            bgInput.addEventListener('change', function () {
                var f = bgInput.files && bgInput.files[0];
                if (!f) { return; }
                // only FileReader-preview real images; otherwise keep/restore the current preview
                if (!f.type || f.type.indexOf('image/') !== 0) {
                    if (bgOrigSrc && !bgOrigHidden) {
                        bgPrev.src = bgOrigSrc;
                        bgPrev.classList.remove('d-none');
                    } else {
                        bgPrev.removeAttribute('src');
                        bgPrev.classList.add('d-none');
                    }
                    return;
                }
                var rd = new FileReader();
                rd.onload = function (e) { bgPrev.src = e.target.result; bgPrev.classList.remove('d-none'); };
                rd.readAsDataURL(f);
            });
            // old (T340b first version):
            // bgInput.addEventListener('change', function () {
            //     var f = bgInput.files && bgInput.files[0];
            //     if (!f) { return; }
            //     var rd = new FileReader();
            //     rd.onload = function (e) { bgPrev.src = e.target.result; bgPrev.classList.remove('d-none'); };
            //     rd.readAsDataURL(f);
            // });
        }

        var resetBtn = document.getElementById('themeResetBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                Swal.fire({
                    title: 'Reset theme?',
                    text: 'Colours, font and sidebar go back to the default look for every user.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-sm btn-success me-2', cancelButton: 'btn btn-sm btn-danger' }
                }).then(function (r) {
                    if (r.isConfirmed) { document.getElementById('themeResetForm').submit(); }
                });
            });
        }
    })();
</script>
@endpush
