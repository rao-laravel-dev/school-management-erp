{{-- Classic design: double border (thick navy + thin gold), serif text. Fixed colours so every print looks the same. --}}
<style>
    .cert-classic { position: absolute; inset: 0; padding: 5mm; font-family: Georgia, "Times New Roman", serif; color: #1f2937; }
    .cert-classic .cc-outer { height: 100%; border: 2.2mm solid #1e3a5f; padding: 1.6mm; }
    .cert-classic .cc-inner { position: relative; height: 100%; border: .7mm solid #b8860b; padding: 8mm 12mm; display: flex; flex-direction: column; }

    /* corner ornaments */
    .cert-classic .cc-corner { position: absolute; width: 9mm; height: 9mm; border: .7mm solid #b8860b; background: #fff; }
    .cert-classic .cc-corner.tl { top: -5mm; left: -5mm; }
    .cert-classic .cc-corner.tr { top: -5mm; right: -5mm; }
    .cert-classic .cc-corner.bl { bottom: -5mm; left: -5mm; }
    .cert-classic .cc-corner.br { bottom: -5mm; right: -5mm; }

    .cert-classic .cc-head { text-align: center; }
    /* old: .cc-logo max-height 22mm, max-width 70mm (landscape 16mm) */
    .cert-classic .cc-logo { max-height: 30mm; max-width: 90mm; margin-bottom: 2mm; }
    .cert-classic .cc-school { margin: 0; font-size: 22pt; font-weight: bold; letter-spacing: .5pt; color: #1e3a5f; }
    .cert-classic .cc-address { margin: 1mm 0 0; font-size: 10pt; color: #4b5563; }
    .cert-classic .cc-rule { height: .6mm; background: #b8860b; margin: 4mm auto 0; width: 40%; }

    .cert-classic .cc-title { margin: 7mm 0 4mm; text-align: center; font-size: 24pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5pt; color: #1e3a5f; }
    .cert-classic .cc-meta { display: flex; justify-content: space-between; font-size: 11pt; margin-bottom: 6mm; }

    .cert-classic .cc-body { flex: 1; font-size: 15pt; line-height: 2; text-align: justify; padding: 0 4mm; overflow: hidden; }
    .cert-page.landscape .cert-classic .cc-body { font-size: 14pt; line-height: 1.8; }
    .cert-page.landscape .cert-classic .cc-title { margin: 3mm 0 2mm; font-size: 21pt; }
    .cert-page.landscape .cert-classic .cc-logo { max-height: 22mm; max-width: 70mm; } /* old: max-height 16mm */
    .cert-page.landscape .cert-classic .cc-inner { padding: 5mm 12mm; }
    .cert-page.landscape .cert-classic .cc-meta { margin-bottom: 3mm; }

    .cert-classic .cc-foot { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 4mm; }
    .cert-classic .cc-foot > div { width: 33%; text-align: center; font-size: 11pt; }
    .cert-classic .cc-foot > .cc-foot-left { text-align: left; }
    .cert-classic .cc-stamp { max-height: 28mm; max-width: 28mm; }
    .cert-classic .cc-sign-img { display: block; margin: 0 auto 1mm; max-height: 16mm; max-width: 50mm; }
    .cert-classic .cc-sign-line { border-top: .4mm solid #1f2937; padding-top: 1mm; }

    .cert-classic .cc-cancelled {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 70pt; font-weight: bold; letter-spacing: 6pt; color: rgba(220, 38, 38, .22); border: 2mm solid rgba(220, 38, 38, .22);
        padding: 2mm 10mm; pointer-events: none;
    }
</style>

<div class="cert-classic">
    <div class="cc-outer">
        <div class="cc-inner">
            <span class="cc-corner tl"></span><span class="cc-corner tr"></span><span class="cc-corner bl"></span><span class="cc-corner br"></span>

            <div class="cc-head">
                @if($schoolBrand->logo_url)
                <img class="cc-logo" src="{{ $schoolBrand->logo_url }}" alt="">
                @endif
                <h1 class="cc-school">{{ $schoolBrand->school_name }}</h1>
                @php
                    $contact = collect([$schoolBrand->address, $schoolBrand->phone ? 'Ph: ' . $schoolBrand->phone : null, $schoolBrand->email])->filter()->implode(' | ');
                @endphp
                @if($contact)
                <p class="cc-address">{{ $contact }}</p>
                @endif
                <div class="cc-rule"></div>
            </div>

            <div class="cc-title">{{ $certificate->title }}</div>

            <div class="cc-meta">
                <span>Certificate No.: <strong>{{ $certificate->certificate_no }}</strong></span>
                <span>Date: <strong>{{ $certificate->issue_date->format('d-m-Y') }}</strong></span>
            </div>

            <div class="cc-body">{!! nl2br(e($certificate->body)) !!}</div>

            <div class="cc-foot">
                {{-- old: empty left cell + stamp in the centre cell --}}
                <div class="cc-foot-left">
                    @if($schoolBrand->school_stamp_url)
                    <img class="cc-stamp" src="{{ $schoolBrand->school_stamp_url }}" alt="">
                    @endif
                </div>
                <div>
                    @if($schoolBrand->principal_signature_url)
                    <img class="cc-sign-img" src="{{ $schoolBrand->principal_signature_url }}" alt="">
                    @endif
                    <div class="cc-sign-line">
                        @if($schoolBrand->head_name)<strong>{{ $schoolBrand->head_name }}</strong><br>@endif
                        {{ $schoolBrand->head_title ?: 'Principal' }}
                    </div>
                </div>
            </div>

            @if($certificate->isCancelled())
            <div class="cc-cancelled">CANCELLED</div>
            @endif
        </div>
    </div>
</div>
