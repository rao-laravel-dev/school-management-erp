{{-- Modern design: solid colour band with the school name, clean sans-serif body. Band colour = theme primary (fallback #0e3b78). --}}
@php
    $mdPrimary = (is_array($schoolBrand->theme ?? null) && !empty($schoolBrand->theme['primary'])) ? $schoolBrand->theme['primary'] : '#0e3b78';
    $mdAccent = (is_array($schoolBrand->theme ?? null) && !empty($schoolBrand->theme['accent'])) ? $schoolBrand->theme['accent'] : '#ffb020';
    // old: $mdOn = theme['on_primary'] (picked black on the default blue). White text unless white has less than 3:1 contrast (same rule as --ss-on-solid)
    $mdLum = function ($hex) {
        $h = ltrim($hex, '#');
        $c = [hexdec(substr($h, 0, 2)) / 255, hexdec(substr($h, 2, 2)) / 255, hexdec(substr($h, 4, 2)) / 255];
        foreach ($c as &$v) { $v = $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4); }
        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    };
    $mdOn = (1.05 / ($mdLum($mdPrimary) + 0.05)) >= 3
        ? '#ffffff'
        : ((is_array($schoolBrand->theme ?? null) && !empty($schoolBrand->theme['on_primary'])) ? $schoolBrand->theme['on_primary'] : '#000000');
@endphp
<style>
    .cert-modern { position: absolute; inset: 0; font-family: "Segoe UI", Arial, Helvetica, sans-serif; color: #1f2937; background: #fff; display: flex; flex-direction: column; }
    .cert-modern .md-band { background: {{ $mdPrimary }}; color: {{ $mdOn }}; padding: 9mm 14mm 7mm; display: grid; grid-template-columns: 45mm minmax(0, 1fr) 45mm; align-items: center; gap: 6mm; } /* old: display: flex */
    .cert-modern .md-slot { display: flex; align-items: center; justify-content: flex-start; }
    .cert-page.landscape .cert-modern .md-band { grid-template-columns: 60mm minmax(0, 1fr) 60mm; }
    /* old: .md-logo-box padding 1.5mm; .md-logo max-height 18mm, max-width 45mm (landscape 13mm) */
    .cert-modern .md-logo-box { background: #fff; border-radius: 2mm; padding: .5mm; display: flex; align-items: center; }
    .cert-modern .md-logo { max-height: 30mm; max-width: 44mm; display: block; } /* old: max-width 70mm (landscape 60mm); now fits the 45mm (landscape 60mm) slot */
    .cert-modern .md-band-text { flex: 1; min-width: 0; text-align: center; } /* old: flex: 1 only; min-width 0 lets the one-line school name shrink instead of pushing the band */
    .cert-modern .md-school { margin: 0; font-size: 22pt; font-weight: 700; letter-spacing: .3pt; color: {{ $mdOn }}; }
    .cert-modern .md-address { margin: 1mm 0 0; font-size: 9.5pt; opacity: .9; color: {{ $mdOn }}; }
    /* old: .md-stripe background {{ $mdPrimary }}, opacity .35 (faint line in the main colour). Now the school accent (gold by default), same as the marksheet Modern */
    .cert-modern .md-stripe { height: 1.6mm; background: {{ $mdAccent }}; }

    .cert-modern .md-main { flex: 1; display: flex; flex-direction: column; padding: 8mm 16mm 0; min-height: 0; }
    .cert-modern .md-title { margin: 0 0 3mm; padding-left: 4mm; border-left: 1.8mm solid {{ $mdPrimary }}; font-size: 22pt; font-weight: 700; text-transform: uppercase; letter-spacing: 1pt; color: {{ $mdPrimary }}; }
    .cert-modern .md-meta { display: flex; justify-content: space-between; font-size: 10.5pt; color: #4b5563; margin-bottom: 5mm; border-bottom: .3mm solid #d1d5db; padding-bottom: 2mm; }
    .cert-modern .md-body { flex: 1; font-size: 14.5pt; line-height: 1.95; text-align: left; overflow: hidden; }

    .cert-page.landscape .cert-modern .md-band { padding: 5mm 14mm 4mm; }
    .cert-page.landscape .cert-modern .md-logo { max-height: 22mm; max-width: 60mm; } /* old: max-height 13mm */
    .cert-page.landscape .cert-modern .md-school { font-size: 18pt; }
    .cert-page.landscape .cert-modern .md-main { padding: 4mm 16mm 0; }
    .cert-page.landscape .cert-modern .md-title { font-size: 18pt; margin-bottom: 2mm; }
    .cert-page.landscape .cert-modern .md-meta { margin-bottom: 2mm; padding-bottom: 1mm; }
    .cert-page.landscape .cert-modern .md-body { font-size: 13pt; line-height: 1.7; }

    .cert-modern .md-foot { display: flex; justify-content: space-between; align-items: flex-end; padding: 3mm 16mm 9mm; }
    .cert-page.landscape .cert-modern .md-foot { padding-bottom: 6mm; }
    .cert-modern .md-foot > div { width: 33%; text-align: center; font-size: 11pt; }
    .cert-modern .md-foot > .md-foot-left { text-align: left; }
    .cert-modern .md-stamp { max-height: 28mm; max-width: 28mm; }
    .cert-page.landscape .cert-modern .md-stamp { max-height: 22mm; max-width: 22mm; }
    .cert-modern .md-sign-img { display: block; margin: 0 auto 1mm; max-height: 16mm; max-width: 50mm; }
    .cert-page.landscape .cert-modern .md-sign-img { max-height: 12mm; }
    .cert-modern .md-sign-line { border-top: .4mm solid {{ $mdPrimary }}; padding-top: 1mm; }
    .cert-modern .md-bottom { height: 3mm; background: {{ $mdPrimary }}; }

    .cert-modern .md-cancelled {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 70pt; font-weight: bold; letter-spacing: 6pt; color: rgba(220, 38, 38, .22); border: 2mm solid rgba(220, 38, 38, .22);
        padding: 2mm 10mm; pointer-events: none;
    }
</style>

<div class="cert-modern">
    <div class="md-band">
        {{-- equal left / right slots: logo (or empty) on the left, empty spacer on the right, name block centred on the page --}}
        <div class="md-slot">
        @if($schoolBrand->logo_url)
        <div class="md-logo-box"><img class="md-logo" src="{{ $schoolBrand->logo_url }}" alt=""></div>
        @endif
        </div>
        <div class="md-band-text">
            <h1 class="md-school">{{ $schoolBrand->school_name }}</h1>
            @php
                $contact = collect([$schoolBrand->address, $schoolBrand->phone ? 'Ph: ' . $schoolBrand->phone : null, $schoolBrand->email])->filter()->implode(' | ');
            @endphp
            @if($contact)
            <p class="md-address">{{ $contact }}</p>
            @endif
        </div>
        <div class="md-slot"></div>
    </div>
    <div class="md-stripe"></div>

    <div class="md-main">
        <h2 class="md-title">{{ $certificate->title }}</h2>
        <div class="md-meta">
            <span>Certificate No.: <strong>{{ $certificate->certificate_no }}</strong></span>
            <span>Date: <strong>{{ $certificate->issue_date->format('d-m-Y') }}</strong></span>
        </div>
        <div class="md-body">{!! nl2br(e($certificate->body)) !!}</div>
    </div>

    <div class="md-foot">
        <div class="md-foot-left">
            @if($schoolBrand->school_stamp_url)
            <img class="md-stamp" src="{{ $schoolBrand->school_stamp_url }}" alt="">
            @endif
        </div>
        <div>
            @if($schoolBrand->principal_signature_url)
            <img class="md-sign-img" src="{{ $schoolBrand->principal_signature_url }}" alt="">
            @endif
            <div class="md-sign-line">
                @if($schoolBrand->head_name)<strong>{{ $schoolBrand->head_name }}</strong><br>@endif
                {{ $schoolBrand->head_title ?: 'Principal' }}
            </div>
        </div>
    </div>
    <div class="md-bottom"></div>

    @if($certificate->isCancelled())
    <div class="md-cancelled">CANCELLED</div>
    @endif
</div>
