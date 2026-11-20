{{-- Elegant design: cream paper, thin double border, CSS corner ornaments, serif text. Fixed colours so every print looks the same. --}}
<style>
    .cert-elegant { position: absolute; inset: 0; background: #fdf8ec; font-family: Georgia, "Palatino Linotype", "Times New Roman", serif; color: #3b2f1e; }
    .cert-elegant .el-border1 { position: absolute; inset: 5mm; border: .5mm solid #8a6d3b; }
    .cert-elegant .el-border2 { position: absolute; inset: 7.5mm; border: .25mm solid #8a6d3b; }

    /* corner ornaments: L-shaped thick corner + small diamond */
    .cert-elegant .el-corner { position: absolute; width: 16mm; height: 16mm; border: 0 solid #8a6d3b; }
    .cert-elegant .el-corner::after { content: ""; position: absolute; width: 3mm; height: 3mm; background: #8a6d3b; transform: rotate(45deg); }
    .cert-elegant .el-corner.tl { top: 3.5mm; left: 3.5mm; border-top-width: 1.6mm; border-left-width: 1.6mm; }
    .cert-elegant .el-corner.tl::after { top: 4.5mm; left: 4.5mm; }
    .cert-elegant .el-corner.tr { top: 3.5mm; right: 3.5mm; border-top-width: 1.6mm; border-right-width: 1.6mm; }
    .cert-elegant .el-corner.tr::after { top: 4.5mm; right: 4.5mm; }
    .cert-elegant .el-corner.bl { bottom: 3.5mm; left: 3.5mm; border-bottom-width: 1.6mm; border-left-width: 1.6mm; }
    .cert-elegant .el-corner.bl::after { bottom: 4.5mm; left: 4.5mm; }
    .cert-elegant .el-corner.br { bottom: 3.5mm; right: 3.5mm; border-bottom-width: 1.6mm; border-right-width: 1.6mm; }
    .cert-elegant .el-corner.br::after { bottom: 4.5mm; right: 4.5mm; }

    .cert-elegant .el-content { position: absolute; inset: 13mm 16mm; display: flex; flex-direction: column; }
    .cert-elegant .el-head { text-align: center; }
    /* old: .el-logo max-height 20mm, max-width 70mm (landscape 13mm) */
    .cert-elegant .el-logo { max-height: 30mm; max-width: 90mm; margin-bottom: 2mm; }
    .cert-elegant .el-school { margin: 0; font-size: 21pt; font-weight: bold; letter-spacing: .5pt; color: #5b4421; }
    .cert-elegant .el-address { margin: 1mm 0 0; font-size: 10pt; color: #6b5a40; }
    .cert-elegant .el-divider { display: flex; align-items: center; justify-content: center; gap: 3mm; margin: 4mm auto 0; width: 55%; }
    .cert-elegant .el-divider::before, .cert-elegant .el-divider::after { content: ""; flex: 1; height: .3mm; background: #8a6d3b; }
    .cert-elegant .el-divider i { width: 2.4mm; height: 2.4mm; background: #8a6d3b; transform: rotate(45deg); }

    .cert-elegant .el-title { margin: 6mm 0 3mm; text-align: center; font-size: 25pt; font-style: italic; font-weight: normal; letter-spacing: 1pt; color: #7a5c2e; }
    .cert-elegant .el-meta { display: flex; justify-content: space-between; font-size: 11pt; margin-bottom: 5mm; }

    .cert-elegant .el-body { flex: 1; font-size: 15pt; line-height: 2; text-align: justify; padding: 0 3mm; overflow: hidden; }
    .cert-page.landscape .cert-elegant .el-content { inset: 11mm 16mm; }
    .cert-page.landscape .cert-elegant .el-body { font-size: 13.5pt; line-height: 1.75; }
    .cert-page.landscape .cert-elegant .el-title { margin: 2mm 0 1mm; font-size: 20pt; }
    .cert-page.landscape .cert-elegant .el-logo { max-height: 22mm; max-width: 70mm; margin-bottom: 1mm; } /* old: max-height 13mm */
    .cert-page.landscape .cert-elegant .el-school { font-size: 18pt; }
    .cert-page.landscape .cert-elegant .el-divider { margin-top: 2mm; }
    .cert-page.landscape .cert-elegant .el-meta { margin-bottom: 2mm; }

    .cert-elegant .el-foot { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 3mm; }
    .cert-elegant .el-foot > div { width: 33%; text-align: center; font-size: 11pt; }
    .cert-elegant .el-foot > .el-foot-left { text-align: left; }
    .cert-elegant .el-stamp { max-height: 28mm; max-width: 28mm; }
    .cert-page.landscape .cert-elegant .el-stamp { max-height: 22mm; max-width: 22mm; }
    .cert-elegant .el-sign-img { display: block; margin: 0 auto 1mm; max-height: 16mm; max-width: 50mm; }
    .cert-page.landscape .cert-elegant .el-sign-img { max-height: 12mm; }
    .cert-elegant .el-sign-line { border-top: .35mm solid #5b4421; padding-top: 1mm; }

    .cert-elegant .el-cancelled {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 70pt; font-weight: bold; letter-spacing: 6pt; color: rgba(220, 38, 38, .22); border: 2mm solid rgba(220, 38, 38, .22);
        padding: 2mm 10mm; pointer-events: none;
    }
</style>

<div class="cert-elegant">
    <div class="el-border1"></div>
    <div class="el-border2"></div>
    <span class="el-corner tl"></span><span class="el-corner tr"></span><span class="el-corner bl"></span><span class="el-corner br"></span>

    <div class="el-content">
        <div class="el-head">
            @if($schoolBrand->logo_url)
            <img class="el-logo" src="{{ $schoolBrand->logo_url }}" alt="">
            @endif
            <h1 class="el-school">{{ $schoolBrand->school_name }}</h1>
            @php
                $contact = collect([$schoolBrand->address, $schoolBrand->phone ? 'Ph: ' . $schoolBrand->phone : null, $schoolBrand->email])->filter()->implode(' | ');
            @endphp
            @if($contact)
            <p class="el-address">{{ $contact }}</p>
            @endif
            <div class="el-divider"><i></i></div>
        </div>

        <div class="el-title">{{ $certificate->title }}</div>

        <div class="el-meta">
            <span>Certificate No.: <strong>{{ $certificate->certificate_no }}</strong></span>
            <span>Date: <strong>{{ $certificate->issue_date->format('d-m-Y') }}</strong></span>
        </div>

        <div class="el-body">{!! nl2br(e($certificate->body)) !!}</div>

        <div class="el-foot">
            <div class="el-foot-left">
                @if($schoolBrand->school_stamp_url)
                <img class="el-stamp" src="{{ $schoolBrand->school_stamp_url }}" alt="">
                @endif
            </div>
            <div>
                @if($schoolBrand->principal_signature_url)
                <img class="el-sign-img" src="{{ $schoolBrand->principal_signature_url }}" alt="">
                @endif
                <div class="el-sign-line">
                    @if($schoolBrand->head_name)<strong>{{ $schoolBrand->head_name }}</strong><br>@endif
                    {{ $schoolBrand->head_title ?: 'Principal' }}
                </div>
            </div>
        </div>
    </div>

    @if($certificate->isCancelled())
    <div class="el-cancelled">CANCELLED</div>
    @endif
</div>
