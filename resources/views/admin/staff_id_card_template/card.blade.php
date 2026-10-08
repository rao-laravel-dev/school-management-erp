<!DOCTYPE html>
<html>

<head>
    <title>Staff ID Cards</title>
    <style>
        :root {
            --card-w: {{ $template->design_type === 'vertical' ? '54mm' : '85.6mm' }};
            --card-h: {{ $template->design_type === 'vertical' ? '85.6mm' : '54mm' }};
            --header-color: {{ $template->header_color ?? '#4b3bdb' }};
        }

        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #eee; text-align: center; }
        .card-pair { display: inline-flex; gap: 15px; align-items: flex-start; margin: 10px; }

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
            background-image: url('{{ asset('uploads/staff_id_card_template/' . $template->background_image) }}');
            background-size: cover;
            @endif
        }

        .id-card::before, .id-card::after, .id-card .tm-tr, .id-card .tm-bl {
            content: ""; position: absolute; width: 3mm; height: 3mm;
            border-color: #999; border-style: solid; border-width: 0;
        }
        .id-card::before { top: -0.3mm; left: -0.3mm; border-top-width: .3mm; border-left-width: .3mm; }
        .id-card::after { bottom: -0.3mm; right: -0.3mm; border-bottom-width: .3mm; border-right-width: .3mm; }
        .id-card .tm-tr { top: -0.3mm; right: -0.3mm; border-top-width: .3mm; border-right-width: .3mm; }
        .id-card .tm-bl { bottom: -0.3mm; left: -0.3mm; border-bottom-width: .3mm; border-left-width: .3mm; }

        .id-card-header { background-color: var(--header-color); color: #fff; text-align: center; padding: 1.2mm 2mm; flex: 0 0 auto; }
        .id-card-header img { height: 6mm; }
        .id-card-header h5 { margin: .5mm 0 0; font-size: 7pt; font-weight: 700; line-height: 1.1; color: #fff; }
        .id-card-header small {
    font-size: 4.5pt;
    display: block;
    line-height: 1.2;
    color: #fff;
}

        .id-card-body { padding: 1.5mm 2mm; font-size: 5.5pt; flex: 1 1 auto; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-direction: row; gap: 4mm; }
        .id-card.vertical .id-card-body { flex-direction: column; align-items: center; gap: 2mm; }
        .id-card-body .photo { flex: 0 0 auto; text-align: center; }
        .id-card-body .photo img { width: 15mm; height: 19mm; object-fit: cover; border-radius: 1mm; border: .2mm solid #ddd; display: block; }
        .id-card-body table { width: auto; max-width: 100%; text-align: left; border-collapse: collapse; }
        .id-card-body table td { padding: .3mm 0; text-align: left; font-size: 5pt; line-height: 1.25; vertical-align: top; color: #333; overflow-wrap: anywhere; }
        .id-card-body table td.label { font-weight: 700; width: 38%; white-space: nowrap; padding-right: 3mm; }

        .id-card-back-header { background-color: var(--header-color); color: #fff; text-align: center; padding: 1mm; font-size: 5pt; font-weight: 700; flex: 0 0 auto; }
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
        .id-card-back-footer .barcode-text { font-size: 3.5pt; letter-spacing: .3mm; color: #333; font-family: 'Courier New', monospace; margin-bottom: 1mm; }
        .id-card-back-footer .signature-line { margin-top: 1mm; border-top: .2mm solid #999; width: 55%; display: inline-block; padding-top: .4mm; font-size: 3.5pt; color: #333; }

        @media print {
            @page { size: A4; margin: 10mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #fff !important; margin: 0; padding: 0; display: block; }
            .no-print { display: none !important; }
            .card-pair { display: flex; justify-content: center; align-items: center; width: 100%; min-height: 100vh; margin: 0; page-break-after: always; break-after: page; page-break-inside: avoid; }
            .card-pair:last-child { page-break-after: auto; break-after: auto; }
            .id-card { width: var(--card-w) !important; height: var(--card-h) !important; box-shadow: none; margin: 0 5mm; }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align:right; margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    @php $cardClass = $template->design_type === 'vertical' ? 'vertical' : 'horizontal'; @endphp

    @foreach($staffMembers as $staff)
    @php
        $detail = $staff->teacher ?? $staff->accountant ?? $staff->receptionist;
        $folder = $staff->teacher ? 'teacher_images' : ($staff->accountant ? 'accountants' : ($staff->receptionist ? 'receptionists' : null));
        $photoUrl = $staff->teacher ? $staff->teacher->photo_url : (($detail && $detail->photo && $folder) ? asset('uploads/' . $folder . '/' . $detail->photo) : asset('images/no-image.png'));
        $staffCode = $staff->staff_code; // User accessor: teacher/accountant/receptionist ka apna ID
        $serialNo = 'SF-' . str_pad($staff->id, 5, '0', STR_PAD_LEFT);
    @endphp

    <div class="card-pair">
        {{-- FRONT --}}
        <div class="id-card {{ $cardClass }}">
            <span class="tm-tr"></span><span class="tm-bl"></span>
            <div class="id-card-header">
                @if($template->logo)
                <img src="{{ asset('uploads/staff_id_card_template/' . $template->logo) }}">
                @endif
                <h5>{{ $template->school_name }}</h5>
                <small>{{ $template->address_phone_email }}</small>
            </div>

            <div class="id-card-body">
                <div class="photo">
                    <img src="{{ $photoUrl }}">
                </div>

                <table>
                    @if($template->show_staff_name)
                    <tr><td class="label">Name</td><td>{{ strtoupper($staff->name) }}</td></tr>
                    @endif
                    @if($template->show_staff_id)
                    <tr><td class="label">Staff ID</td><td>{{ $staffCode }}</td></tr>
                    @endif
                    @if($template->show_designation)
                    <tr><td class="label">Designation</td><td>{{ ucfirst($staff->roles->first()->name ?? '') }}</td></tr>
                    @endif
                    @if($template->show_department)
                    <tr><td class="label">Department</td><td>{{ $detail->department ?? '' }}</td></tr>
                    @endif
                    @if($template->show_father_name)
                    <tr><td class="label">Father</td><td>{{ $detail->father_name ?? '' }}</td></tr>
                    @endif
                    @if($template->show_mother_name)
                    <tr><td class="label">Mother</td><td>{{ $detail->mother_name ?? '' }}</td></tr>
                    @endif
                    @if($template->show_phone)
                    <tr><td class="label">Phone</td><td>{{ $detail->phone ?? '' }}</td></tr>
                    @endif
                    @if($template->show_dob)
                    <tr><td class="label">D.O.B</td><td>{{ $detail && $detail->dob ? \Carbon\Carbon::parse($detail->dob)->format('d-m-Y') : '' }}</td></tr>
                    @endif
                    @if($template->show_date_of_joining)
                    <tr><td class="label">Joining</td><td>{{ $detail && $detail->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d-m-Y') : '' }}</td></tr>
                    @endif
                    @if($template->show_current_address)
                    <tr><td class="label">Address</td><td>{{ $detail->address ?? '' }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- BACK --}}
        <div class="id-card {{ $cardClass }}">
            <span class="tm-tr"></span><span class="tm-bl"></span>
            <div class="id-card-back-header">{{ $template->school_name }} — Terms &amp; Conditions</div>

            <div class="id-card-back-content">
                <div class="id-card-back-rules">
                    <h6>Staff Card Rules</h6>
                    <ul>
                        <li>Property of {{ $template->school_name }}; carry at all times.</li>
                        <li>Not transferable.</li>
                        <li>If found, return to school office.</li>
                        <li>Valid for current employment period only.</li>
                        <li>Loss must be reported; duplicate fee applies.</li>
                    </ul>
                </div>

                <div class="id-card-back-contact">
                    <h6>Contact Info</h6>
                    <table>
                        <tr><td class="label">Staff Phone</td><td>{{ $detail->phone ?? '' }}</td></tr>
                        <tr><td class="label">Address</td><td>{{ $detail->address ?? '' }}</td></tr>
                        <tr><td class="label">School</td><td>{{ $template->address_phone_email }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="id-card-back-footer">
                @if($template->show_qr_code)
                <div class="qr-wrap">{!! QrCode::size(60)->generate($staff->getOrCreateQrCode()) !!}</div>
                @endif
                @if($template->show_barcode)
                @php $barcodeValue = $staff->getOrCreateQrCode(); @endphp
                <div class="barcode-wrap">{!! DNS1D::getBarcodeHTML($barcodeValue, 'C128', 1, 20) !!}</div>
                <div class="barcode-text">{{ $barcodeValue }}</div>
                @endif
                <div class="signature-line">Principal / Authorized Signature</div>
            </div>
        </div>
    </div>
    @endforeach
</body>

</html>