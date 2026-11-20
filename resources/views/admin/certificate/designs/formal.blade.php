{{-- Formal design: plain single thin black border, black text, simple official look (SLC / DOB). --}}
<style>
    .cert-formal { position: absolute; inset: 0; padding: 6mm; font-family: "Times New Roman", Times, serif; color: #000; background: #fff; }
    .cert-formal .fm-frame { height: 100%; border: .4mm solid #000; padding: 9mm 14mm; display: flex; flex-direction: column; }

    .cert-formal .fm-head { text-align: center; }
    .cert-formal .fm-logo { max-height: 20mm; max-width: 70mm; margin-bottom: 2mm; }
    .cert-formal .fm-school { margin: 0; font-size: 20pt; font-weight: bold; text-transform: uppercase; }
    .cert-formal .fm-address { margin: 1mm 0 0; font-size: 10pt; }
    .cert-formal .fm-rule { border-top: .4mm solid #000; margin-top: 4mm; }

    .cert-formal .fm-title { margin: 6mm 0 4mm; text-align: center; font-size: 20pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; text-underline-offset: 1.2mm; }
    .cert-formal .fm-meta { display: flex; justify-content: space-between; font-size: 11pt; margin-bottom: 6mm; }

    .cert-formal .fm-body { flex: 1; font-size: 14pt; line-height: 2; text-align: justify; overflow: hidden; }
    .cert-page.landscape .cert-formal .fm-frame { padding: 6mm 14mm; }
    .cert-page.landscape .cert-formal .fm-body { font-size: 13pt; line-height: 1.75; }
    .cert-page.landscape .cert-formal .fm-title { margin: 3mm 0 2mm; font-size: 17pt; }
    .cert-page.landscape .cert-formal .fm-logo { max-height: 13mm; margin-bottom: 1mm; }
    .cert-page.landscape .cert-formal .fm-school { font-size: 17pt; }
    .cert-page.landscape .cert-formal .fm-rule { margin-top: 2mm; }
    .cert-page.landscape .cert-formal .fm-meta { margin-bottom: 3mm; }

    .cert-formal .fm-foot { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 3mm; }
    .cert-formal .fm-foot > div { width: 33%; text-align: center; font-size: 11pt; }
    .cert-formal .fm-foot > .fm-foot-left { text-align: left; }
    .cert-formal .fm-stamp { max-height: 28mm; max-width: 28mm; }
    .cert-page.landscape .cert-formal .fm-stamp { max-height: 22mm; max-width: 22mm; }
    .cert-formal .fm-sign-img { display: block; margin: 0 auto 1mm; max-height: 16mm; max-width: 50mm; }
    .cert-page.landscape .cert-formal .fm-sign-img { max-height: 12mm; }
    .cert-formal .fm-sign-line { border-top: .4mm solid #000; padding-top: 1mm; }

    .cert-formal .fm-cancelled {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 70pt; font-weight: bold; letter-spacing: 6pt; color: rgba(220, 38, 38, .22); border: 2mm solid rgba(220, 38, 38, .22);
        padding: 2mm 10mm; pointer-events: none;
    }
</style>

<div class="cert-formal">
    <div class="fm-frame">
        <div class="fm-head">
            @if($schoolBrand->logo_url)
            <img class="fm-logo" src="{{ $schoolBrand->logo_url }}" alt="">
            @endif
            <h1 class="fm-school">{{ $schoolBrand->school_name }}</h1>
            @php
                $contact = collect([$schoolBrand->address, $schoolBrand->phone ? 'Ph: ' . $schoolBrand->phone : null, $schoolBrand->email])->filter()->implode(' | ');
            @endphp
            @if($contact)
            <p class="fm-address">{{ $contact }}</p>
            @endif
            <div class="fm-rule"></div>
        </div>

        <div class="fm-title">{{ $certificate->title }}</div>

        <div class="fm-meta">
            <span>Certificate No.: <strong>{{ $certificate->certificate_no }}</strong></span>
            <span>Date: <strong>{{ $certificate->issue_date->format('d-m-Y') }}</strong></span>
        </div>

        <div class="fm-body">{!! nl2br(e($certificate->body)) !!}</div>

        <div class="fm-foot">
            <div class="fm-foot-left">
                @if($schoolBrand->school_stamp_url)
                <img class="fm-stamp" src="{{ $schoolBrand->school_stamp_url }}" alt="">
                @endif
            </div>
            <div>
                @if($schoolBrand->principal_signature_url)
                <img class="fm-sign-img" src="{{ $schoolBrand->principal_signature_url }}" alt="">
                @endif
                <div class="fm-sign-line">
                    @if($schoolBrand->head_name)<strong>{{ $schoolBrand->head_name }}</strong><br>@endif
                    {{ $schoolBrand->head_title ?: 'Principal' }}
                </div>
            </div>
        </div>
    </div>

    @if($certificate->isCancelled())
    <div class="fm-cancelled">CANCELLED</div>
    @endif
</div>
