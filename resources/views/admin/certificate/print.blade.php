{{-- Certificate print page (standalone, like the ID card print). $certificate (saved or unsaved), $isPreview, $autoPrint --}}
@php
    $orientation = $certificate->orientation === 'landscape' ? 'landscape' : 'portrait';
    $backUrl = \Illuminate\Support\Facades\Route::has('certificate.index') ? route('certificate.index') : route('certificate.create');
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $certificate->title }}{{ empty($isPreview) ? ' - ' . $certificate->certificate_no : '' }}</title>
    <link rel="icon" href="{{ $schoolBrand->icon_url ?: ($mssIcon ?: asset('backend/assets/images/favicon-32x32.png')) }}" type="{{ $schoolBrand->icon_url ? 'image/webp' : 'image/png' }}" />
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: #eee;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        /* A4 minus 8mm margin each side; 1mm less than the full height so a second blank page never appears */
        .cert-page {
            position: relative;
            /* old: display: inline-block; (the line gap under an inline-block pushed landscape onto a 2nd page) */
            display: block;
            margin: 0 auto;
            background: #fff;
            text-align: left;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .25);
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .cert-page.portrait { width: 194mm; height: 280mm; }
        /* old: .cert-page.landscape { width: 280mm; height: 194mm; } (exactly the printable height = 2nd blank page) */
        .cert-page.landscape { width: 280mm; height: 193mm; }

        .cert-toolbar { margin-bottom: 10px; text-align: right; }
        .cert-toolbar a, .cert-toolbar button {
            display: inline-block; margin-left: 6px; padding: 6px 16px; font-size: 14px;
            border: 1px solid #1e3a5f; border-radius: 4px; background: #1e3a5f; color: #fff; text-decoration: none; cursor: pointer;
        }
        .cert-toolbar a { background: #fff; color: #1e3a5f; }

        @media print {
            @page { size: A4 {{ $orientation }}; margin: 8mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; margin: 0; padding: 0; }
            .cert-page { box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>

<body>
    @if(empty($isPreview))
    <div class="cert-toolbar no-print">
        <a href="{{ $backUrl }}">Back</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>
    @endif

    <div class="cert-page {{ $orientation }}">
        {{-- Design partial: add more designs as admin/certificate/designs/<name>.blade.php --}}
        @include('admin.certificate.designs.' . (array_key_exists($certificate->design, \App\Models\Certificate::DESIGNS) ? $certificate->design : 'classic'))
    </div>

    {{-- School name stays on ONE line: font shrinks (min 14pt) until it fits its box --}}
    <script>
        (function() {
            function fit() {
                document.querySelectorAll('.cc-school, .el-school, .fm-school, .md-school, .ry-school').forEach(function(el) {
                    el.style.whiteSpace = 'nowrap';
                    el.style.overflow = 'hidden';
                    el.style.textOverflow = 'ellipsis'; // only if the name is still too long at 14pt
                    el.style.fontSize = '';
                    var size = parseFloat(getComputedStyle(el).fontSize) * 0.75; // px -> pt
                    // old: while (el.scrollWidth > el.clientWidth + 1 && size > 14) { (scrollWidth did not report the clipped text reliably)
                    var range = document.createRange();
                    range.selectNodeContents(el);
                    while (range.getBoundingClientRect().width > el.clientWidth && size > 12) { // old: size > 14
                        size -= 0.5;
                        el.style.fontSize = size + 'pt';
                    }
                });
            }
            fit();
            window.addEventListener('load', fit);
            if (document.fonts && document.fonts.ready) { document.fonts.ready.then(fit); }
        })();
    </script>

    {{-- Saved certificate opens the print dialog only right after Save (?autoprint=1), never in the preview --}}
    @if(empty($isPreview) && !empty($autoPrint))
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>

</html>
