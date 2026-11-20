{{-- Royal design: thick gold frame with an inner navy line, serif title in gold. Fixed colours so every print looks the same. --}}
<style>
    .cert-royal { position: absolute; inset: 0; padding: 4.5mm; font-family: Georgia, "Times New Roman", serif; color: #1f2937; background: #fffdf5; }
    /* gold frame + white gap + navy line, all box-shadow so no images are needed */
    .cert-royal .ry-frame { position: relative; height: 100%; border: 3mm solid #c9a227; box-shadow: inset 0 0 0 .8mm #fffdf5, inset 0 0 0 1.5mm #1b2a5c, inset 0 0 0 2.3mm #fffdf5, inset 0 0 0 2.8mm #c9a227; padding: 10mm 14mm; display: flex; flex-direction: column; }

    .cert-royal .ry-head { text-align: center; }
    /* old: .ry-logo max-height 20mm, max-width 70mm (landscape 13mm) */
    .cert-royal .ry-logo { max-height: 30mm; max-width: 90mm; margin-bottom: 2mm; }
    .cert-royal .ry-school { margin: 0; font-size: 22pt; font-weight: bold; letter-spacing: .6pt; color: #1b2a5c; }
    .cert-royal .ry-address { margin: 1mm 0 0; font-size: 10pt; color: #4b5563; }
    .cert-royal .ry-rule { height: .8mm; background: #c9a227; margin: 4mm auto 0; width: 45%; }

    .cert-royal .ry-title { margin: 6mm 0 3mm; text-align: center; font-size: 26pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2pt; color: #9a7209; }
    .cert-royal .ry-meta { display: flex; justify-content: space-between; font-size: 11pt; margin-bottom: 5mm; color: #1b2a5c; }

    .cert-royal .ry-body { flex: 1; font-size: 15pt; line-height: 2; text-align: justify; padding: 0 3mm; overflow: hidden; }
    .cert-page.landscape .cert-royal .ry-frame { padding: 6mm 14mm; }
    .cert-page.landscape .cert-royal .ry-body { font-size: 13.5pt; line-height: 1.75; }
    .cert-page.landscape .cert-royal .ry-title { margin: 2mm 0 1mm; font-size: 20pt; }
    .cert-page.landscape .cert-royal .ry-logo { max-height: 22mm; max-width: 70mm; margin-bottom: 1mm; } /* old: max-height 13mm */
    .cert-page.landscape .cert-royal .ry-school { font-size: 18pt; }
    .cert-page.landscape .cert-royal .ry-rule { margin-top: 2mm; }
    .cert-page.landscape .cert-royal .ry-meta { margin-bottom: 2mm; }

    .cert-royal .ry-foot { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 3mm; }
    .cert-royal .ry-foot > div { width: 33%; text-align: center; font-size: 11pt; }
    .cert-royal .ry-foot > .ry-foot-left { text-align: left; }
    .cert-royal .ry-stamp { max-height: 28mm; max-width: 28mm; }
    .cert-page.landscape .cert-royal .ry-stamp { max-height: 22mm; max-width: 22mm; }
    .cert-royal .ry-sign-img { display: block; margin: 0 auto 1mm; max-height: 16mm; max-width: 50mm; }
    .cert-page.landscape .cert-royal .ry-sign-img { max-height: 12mm; }
    .cert-royal .ry-sign-line { border-top: .4mm solid #1b2a5c; padding-top: 1mm; color: #1b2a5c; }

    .cert-royal .ry-cancelled {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 70pt; font-weight: bold; letter-spacing: 6pt; color: rgba(220, 38, 38, .22); border: 2mm solid rgba(220, 38, 38, .22);
        padding: 2mm 10mm; pointer-events: none;
    }
</style>

<div class="cert-royal">
    <div class="ry-frame">
        <div class="ry-head">
            @if($schoolBrand->logo_url)
            <img class="ry-logo" src="{{ $schoolBrand->logo_url }}" alt="">
            @endif
            <h1 class="ry-school">{{ $schoolBrand->school_name }}</h1>
            @php
                $contact = collect([$schoolBrand->address, $schoolBrand->phone ? 'Ph: ' . $schoolBrand->phone : null, $schoolBrand->email])->filter()->implode(' | ');
            @endphp
            @if($contact)
            <p class="ry-address">{{ $contact }}</p>
            @endif
            <div class="ry-rule"></div>
        </div>

        <div class="ry-title">{{ $certificate->title }}</div>

        <div class="ry-meta">
            <span>Certificate No.: <strong>{{ $certificate->certificate_no }}</strong></span>
            <span>Date: <strong>{{ $certificate->issue_date->format('d-m-Y') }}</strong></span>
        </div>

        <div class="ry-body">{!! nl2br(e($certificate->body)) !!}</div>

        <div class="ry-foot">
            <div class="ry-foot-left">
                @if($schoolBrand->school_stamp_url)
                <img class="ry-stamp" src="{{ $schoolBrand->school_stamp_url }}" alt="">
                @endif
            </div>
            <div>
                @if($schoolBrand->principal_signature_url)
                <img class="ry-sign-img" src="{{ $schoolBrand->principal_signature_url }}" alt="">
                @endif
                <div class="ry-sign-line">
                    @if($schoolBrand->head_name)<strong>{{ $schoolBrand->head_name }}</strong><br>@endif
                    {{ $schoolBrand->head_title ?: 'Principal' }}
                </div>
            </div>
        </div>

        @if($certificate->isCancelled())
        <div class="ry-cancelled">CANCELLED</div>
        @endif
    </div>
</div>
