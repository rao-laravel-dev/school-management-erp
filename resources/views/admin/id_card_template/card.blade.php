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
        .id-card-body {
            padding: 1.5mm 2mm;
            font-size: 5.5pt;
            flex: 1 1 auto;
            overflow: hidden;
            display: flex;
            align-items: center;
            flex-direction: row;
            gap: 2mm;
        }

        .id-card.vertical .id-card-body {
            flex-direction: column;
            align-items: center;
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
        }

        .id-card-body table {
            width: 100%;
            text-align: left;
            border-collapse: collapse;
        }

        .id-card-body table td {
            padding: .3mm 0;
            text-align: left;
            font-size: 5pt;
            line-height: 1.25;
            vertical-align: top;
            color: #333;
        }

        .id-card-body table td.label {
            font-weight: 700;
            width: 38%;
            white-space: nowrap;
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

    <div class="card-pair">
        {{-- FRONT --}}
        <div class="id-card {{ $cardClass }}">
            <span class="tm-tr"></span><span class="tm-bl"></span>
            <div class="id-card-header">
                @if($template->logo)
                <img src="{{ asset('uploads/id_card_template/' . $template->logo) }}">
                @endif
                <h5>{{ $template->school_name }}</h5>
                <small>{{ $template->address_phone_email }}</small>
            </div>

            <div class="id-card-body">
                <div class="photo">
                    <img src="{{ $student->photo_url }}">
                </div>

                <table>
                    @if($template->show_student_name)
                    <tr>
                        <td class="label">Name</td>
                        <td>{{ strtoupper($student->first_name . ' ' . $student->last_name) }}</td>
                    </tr>
                    @endif
                    @if($template->show_admission_no)
                    <tr>
                        <td class="label">Adm No</td>
                        <td>{{ $student->admission_no }}</td>
                    </tr>
                    @endif
                    @if($template->show_roll_no)
                    <tr>
                        <td class="label">Roll No</td>
                        <td>{{ $enrollment->roll_no }}</td>
                    </tr>
                    @endif
                    @if($template->show_class)
                    <tr>
                        <td class="label">Class</td>
                        <td>{{ $enrollment->schoolClass->name ?? '' }} - {{ $enrollment->section->name ?? '' }}</td>
                    </tr>
                    @endif
                    @if($template->show_house)
                    <tr>
                        <td class="label">House</td>
                        <td>{{ $student->house->name ?? '' }}</td>
                    </tr>
                    @endif
                    @if($template->show_father_name)
                    <tr>
                        <td class="label">Father</td>
                        <td>{{ $student->parent->father_name ?? '' }}</td>
                    </tr>
                    @endif
                    @if($template->show_mother_name)
                    <tr>
                        <td class="label">Mother</td>
                        <td>{{ $student->parent->mother_name ?? '' }}</td>
                    </tr>
                    @endif
                    @if($template->show_phone)
                    <tr>
                        <td class="label">Phone</td>
                        <td>{{ $student->parent->father_phone ?? '' }}</td>
                    </tr>
                    @endif
                    @if($template->show_dob)
                    <tr>
                        <td class="label">D.O.B</td>
                        <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->format('d-m-Y') }}</td>
                    </tr>
                    @endif
                    @if($template->show_blood_group)
                    <tr>
                        <td class="label">Blood Grp</td>
                        <td>{{ $student->blood_group }}</td>
                    </tr>
                    @endif
                    @if($template->show_address)
                    <tr>
                        <td class="label">Address</td>
                        <td>{{ $student->parent->guardian_address ?? '' }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="id-card-footer">
                <span>{{ $serialNo }}</span>
                <span>Valid: {{ now()->format('Y') }}-{{ now()->addYear()->format('y') }}</span>
            </div>
        </div>

        {{-- BACK --}}
        <div class="id-card {{ $cardClass }}">
            <span class="tm-tr"></span><span class="tm-bl"></span>
            <div class="id-card-back-header">
                {{ $template->school_name }} — Terms &amp; Conditions
            </div>

            <div class="id-card-back-content">
                <div class="id-card-back-rules">
                    <h6>School Rules</h6>
                    <ul>
                        <li>Property of {{ $template->school_name }}; carry at all times.</li>
                        <li>Not transferable.</li>
                        <li>If found, return to school office.</li>
                        <li>Valid for current session only.</li>
                        <li>Loss must be reported; duplicate fee applies.</li>
                    </ul>
                </div>

                <div class="id-card-back-contact">
                    <h6>Emergency Contact</h6>
                    <table>
                        <tr>
                            <td class="label">Guardian</td>
                            <td>{{ $student->parent->father_phone ?? $student->parent->mother_phone ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Address</td>
                            <td>{{ $student->parent->guardian_address ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="label">School</td>
                            <td>{{ $template->address_phone_email }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="id-card-back-footer">
                @if($template->show_qr_code)
                <div class="qr-wrap">{!! QrCode::size(60)->generate($student->getOrCreateQrCode()) !!}</div>
                @endif
                @if($template->show_barcode)
                @php $barcodeValue = $student->getOrCreateQrCode(); @endphp
                <div class="barcode-wrap">{!! DNS1D::getBarcodeHTML($barcodeValue, 'C128', 1, 20) !!}</div>
                <div class="barcode-text">{{ $barcodeValue }}</div>
                @endif
                <div class="signature-line">Principal / Authorized Signature</div>
                <div class="card-meta">
                    <span>Card No: {{ $serialNo }}</span>
                    <span>Issued: {{ now()->format('d-m-Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <script>
    window.onload = function() {
        window.print();
    };
</script>
</body>

</html>