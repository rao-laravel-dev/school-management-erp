{{-- Marksheet print shell: page setup + the chosen design (admin/print_marksheet/designs/<design>.blade.php).
     $exam, $template, $sheets, $siteSetting, $branding, $isPreview (live preview on the template page: no auto print) --}}
@php
    $design = \App\Services\PrintDesignService::design($template);
    $orientation = \App\Services\PrintDesignService::orientation($template);
    $isPreview = $isPreview ?? false;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Marksheet - {{ $exam->name }}</title>
    <link rel="icon" href="{{ $schoolBrand->icon_url ?: ($mssIcon ?: asset('backend/assets/images/favicon-32x32.png')) }}" type="{{ $schoolBrand->icon_url ? 'image/webp' : 'image/png' }}" />
    <style>
        /* old: size: A4; (portrait only) */
        @page {
            size: A4 {{ $orientation }};
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            margin: 0;
        }
    </style>
</head>

<body class="{{ $isPreview ? 'ep-preview' : '' }}">

    @include('admin.print_marksheet.designs.' . $design)

    {{-- School name stays on ONE line: font shrinks (min 14pt) until it fits the space next to the logos. Classic (.school-name) is untouched --}}
    <script>
        (function() {
            function fit() {
                // old: '.ep-school' only. Classic with logo ON (.top-row.has-logo .school-name) now shrinks too
                document.querySelectorAll('.ep-school, .top-row.has-logo .school-name').forEach(function(el) {
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

    @if(!$isPreview)
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif

</body>

</html>
