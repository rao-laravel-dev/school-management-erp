<!DOCTYPE html>
<html>

<head>
    <title>ID Cards</title>
    <style>
        :root {
            --card-w: {{ $template->design_type === 'vertical' ? '54mm' : '85.6mm' }};
            --card-h: {{ $template->design_type === 'vertical' ? '85.6mm' : '54mm' }};
            --header-color: {{ $template->header_color ?? '#4b3bdb' }};
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #eee;
            text-align: center;
        }

        .card-pair {
            display: inline-flex;
            gap: 15px;
            align-items: flex-start;
            margin: 10px;
        }

        /* ---- CR80 exact size ---- */
        .id-card {
            width: var(--card-w);
            height: var(--card-h);
            border: 1px solid #ccc;
            border-radius: 3mm;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            vertical-align: top;
            background: #fff;
            text-align: left;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
            @if($template->background_image)
            background-image: url('{{ asset('uploads/id_card_template/' . $template->background_image) }}');
            background-size: cover;
            @endif
        }

        /* Trim / cut guide marks at corners */
        .id-card::before, .id-card::after,
        .id-card .tm-tr, .id-card .tm-bl {
            content: "";
            position: absolute;
            width: 3mm;
            height: 3mm;
            border-color: #999;
            border-style: solid;
            border-width: 0;
        }

        .id-card::before {
            top: -0.3mm;
            left: -0.3mm;
            border-top-width: .3mm;
            border-left-width: .3mm;
        }

        .id-card::after {
            bottom: -0.3mm;
            right: -0.3mm;
            border-bottom-width: .3mm;
            border-right-width: .3mm;
        }

        .id-card .tm-tr {
            top: -0.3mm;
            right: -0.3mm;
            border-top-width: .3mm;
            border-right-width: .3mm;
        }

        .id-card .tm-bl {
            bottom: -0.3mm;
            left: -0.3mm;
            border-bottom-width: .3mm;
            border-left-width: .3mm;
        }

        .id-card-header {
            background-color: var(--header-color);
            color: #fff;
            text-align: center;
            padding: 1.2mm 2mm;
            flex: 0 0 auto;
        }

        .id-card-header img {
            height: 6mm;
        }

        .id-card-header h5 {
            margin: .5mm 0 0;
            font-size: 7pt;
            font-weight: 700;
            line-height: 1.1;
            color: #fff;
        }

        .id-card-header small {
            font-size: 4.5pt;
            display: block;
            line-height: 1.2;
            color: #fff;
        }

        /* Horizontal: photo left, details right | Vertical: photo top, details below */
        /* old: .id-card-body { padding: 1.5mm 2mm; font-size: 5.5pt; flex: 1 1 auto; overflow: hidden; display: flex; align-items: center; flex-direction: row; gap: 2mm; } */
        .id-card-body {
            padding: 1.5mm 3mm;
            font-size: 5.5pt;
            flex: 1 1 auto;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: row;
            /* old: gap: 3mm; */
            /* old: gap: 5mm; */
            gap: 7mm;
        }

        /* old: .id-card.vertical .id-card-body { flex-direction: column; align-items: center; } */
        .id-card.vertical .id-card-body {
            flex-direction: column;
            align-items: center;
            /* old: justify-content: flex-start; */
            justify-content: safe center;
            padding: 2.5mm 3mm 1.5mm;
            /* old: gap: 2mm; */
            gap: 4mm;
        }

        .id-card-body .photo {
            flex: 0 0 auto;
            text-align: center;
        }

        .id-card-body .photo img {
            width: 15mm;
            height: 19mm;
            object-fit: cover;
            border-radius: 1mm;
            border: .2mm solid #ddd;
            display: block;
            /* old: (no padding / background / box-sizing) */
            padding: .6mm;
            background: #fff;
            box-sizing: border-box;
        }

        /* old: .id-card-body table { width: 100%; text-align: left; border-collapse: collapse; } */
        .id-card-body table {
            flex: 0 1 auto;
            width: auto;
            max-width: 100%;
            min-width: 0;
            text-align: left;
            border-collapse: collapse;
        }

        .id-card.vertical .id-card-body table {
            flex: 0 0 auto;
        }

        .id-card-body table td {
            padding: .3mm 0;
            text-align: left;
            font-size: 5pt;
            line-height: 1.25;
            vertical-align: top;
            color: #333;
            overflow-wrap: anywhere;
        }

        /* old: .id-card-body table td.label { font-weight: 700; width: 38%; white-space: nowrap; } */
        .id-card-body table td.label {
            font-weight: 700;
            width: auto;
            white-space: nowrap;
            /* old: padding-right: 3mm; then 5mm; */
            padding-right: 7mm;
        }

        /* Vertical only: wider label gap + a little row spacing */
        .id-card.vertical .id-card-body table td {
            padding: .7mm 0;
        }

        .id-card.vertical .id-card-body table td.label {
            padding-right: 8mm;
        }

        .id-card-footer {
            text-align: center;
            padding: 1mm 2mm;
            border-top: .2mm dashed #ccc;
            flex: 0 0 auto;
            font-size: 4pt;
            color: #666;
            display: flex;
            justify-content: space-between;
        }

        /* ---- Back side ---- */
        .id-card-back-header {
            background-color: var(--header-color);
            color: #fff;
            text-align: center;
            padding: 1mm;
            font-size: 5pt;
            font-weight: 700;
            flex: 0 0 auto;
        }

        .id-card-back-content {
            flex: 0 0 auto;
            overflow: hidden;
            padding: 1.2mm 2mm;
            display: flex;
            flex-direction: row;
            gap: 2mm;
        }

        .id-card.vertical .id-card-back-content {
            flex-direction: column;
        }

        .id-card-back-rules {
            flex: 1 1 52%;
            font-size: 4pt;
            color: #333;
        }

        .id-card.vertical .id-card-back-rules {
            flex: none;
        }

        .id-card-back-rules h6 {
            margin: 0 0 .4mm;
            font-size: 4.5pt;
            border-bottom: .2mm solid #ddd;
            padding-bottom: .2mm;
            color: #000;
        }

        .id-card-back-rules ul {
            margin: 0;
            padding-left: 2.5mm;
        }

        .id-card-back-rules ul li {
            margin-bottom: .2mm;
            line-height: 1.1;
        }

        .id-card-back-contact {
            flex: 1 1 48%;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 4pt;
            color: #333;
            margin-top: 0;
        }

        .id-card.vertical .id-card-back-contact {
            flex: none;
            align-items: flex-start;
            margin-top: 1.2mm;
        }

        .id-card-back-contact h6 {
            margin: 0 0 .4mm;
            font-size: 4.5pt;
            border-bottom: .2mm solid #ddd;
            padding-bottom: .2mm;
            align-self: stretch;
            color: #000;
        }

        .id-card-back-contact table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1mm;
        }

        .id-card-back-contact table td {
            padding: .2mm 0;
            font-size: 4pt;
        }

        .id-card-back-contact table td.label {
            font-weight: 700;
            width: 40%;
        }

        .id-card-back-footer {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2mm 2mm 1mm;
            border-top: .2mm dashed #ccc;
        }

        .id-card-back-footer .qr-wrap {
            margin: 1mm 0 1.5mm;
        }

        

        .id-card-back-footer .barcode-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 55mm;
            max-width: 60%;
            margin: 0 auto .5mm;
            overflow: hidden;
        }

        .id-card-back-footer .barcode-wrap svg,
        .id-card-back-footer .barcode-wrap img {
            width: 100% !important;
            height: 5mm !important;
            max-width: 100%;
            max-height: 5mm;
            object-fit: contain;
        }

        .id-card-back-footer .barcode-text {
            font-size: 3.5pt;
            letter-spacing: .3mm;
            color: #333;
            font-family: 'Courier New', monospace;
            margin-bottom: 1mm;
        }

        .id-card-back-footer .signature-line {
            margin-top: 1mm;
            border-top: .2mm solid #999;
            width: 55%;
            display: inline-block;
            padding-top: .4mm;
            font-size: 3.5pt;
            color: #333;
        }

        .card-meta {
            width: 100%;
            font-size: 3pt;
            color: #888;
            display: flex;
            justify-content: space-between;
            margin-top: .8mm;
        }

        /* ---- PRINT: exact CR80 size, no stretching, 1 student per page ---- */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                background: #fff !important;
                margin: 0;
                padding: 0;
                display: block;
            }

            .no-print {
                display: none !important;
            }

            .card-pair {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
                min-height: 100vh;
                margin: 0;
                page-break-after: always;
                break-after: page;
                page-break-inside: avoid;
            }

            .card-pair:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .id-card {
                width: var(--card-w) !important;
                height: var(--card-h) !important;
                box-shadow: none;
                margin: 0 5mm;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align:right; margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    @php
    $cardClass = $template->design_type === 'vertical' ? 'vertical' : 'horizontal';
    @endphp

    @foreach($enrollments as $enrollment)
    @php
    $student = $enrollment->student;
    $serialNo = 'SC-' . str_pad($student->id, 5, '0', STR_PAD_LEFT);
    @endphp

    @include('admin.id_card_template.designs.design_' . (in_array((int) $template->design, [1, 2, 3], true) ? (int) $template->design : 1))
    @endforeach
    <script>
    window.onload = function() {
        window.print();
    };
</script>
</body>

</html>