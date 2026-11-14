{{-- Design 3: photo-centred banner --}}
@once
<style>
    /* ---- Design 3: photo-centred banner ---- */
    .design-3 .d3-banner { background: var(--header-color); color: #fff; text-align: center; padding: 1.2mm 2mm 4mm; border-radius: 0 0 50% 50% / 0 0 4mm 4mm; flex: 0 0 auto; }
    .design-3 .d3-banner.d3-banner-back { padding-bottom: 2.5mm; }
    .design-3 .d3-banner img { height: 5mm; }
    .design-3 .d3-banner h5 { margin: .3mm 0 0; font-size: 6.5pt; font-weight: 700; line-height: 1.1; color: #fff; }
    .design-3 .d3-banner small { display: block; font-size: 4pt; line-height: 1.2; color: #fff; }
    .design-3 .d3-photo { flex: 0 0 auto; margin-top: -3mm; text-align: center; position: relative; z-index: 1; }
    .design-3 .d3-photo img { width: 14mm; height: 14mm; border-radius: 50%; object-fit: cover; border: .7mm solid #fff; box-shadow: 0 0 0 .3mm var(--header-color); background: #fff; box-sizing: border-box; }
    .design-3.vertical .d3-photo img { width: 18mm; height: 18mm; }
    .design-3 .d3-name { text-align: center; font-size: 6pt; font-weight: 700; letter-spacing: .2mm; color: #000; margin: .8mm 2mm .4mm; }
    .design-3 .d3-body { flex: 1 1 auto; overflow: hidden; padding: .5mm 3mm; font-size: 5pt; color: #333; }
    .design-3 .d3-details { display: block; border-collapse: collapse; margin: 0 auto; }
    .design-3 .d3-details tbody { display: block; }
    .design-3.horizontal .d3-details tbody { column-count: 2; column-gap: 4mm; }
    .design-3 .d3-details tr { display: block; break-inside: avoid; padding: .25mm 0; line-height: 1.25; }
    .design-3 .d3-details td { display: inline; padding: 0; font-size: 5pt; text-align: left; overflow-wrap: anywhere; }
    .design-3 .d3-details td.label { font-weight: 700; }
    .design-3 .d3-details td.label::after { content: ': '; }
    .design-3 .d3-back-code { flex: 0 0 auto; border-top: none; padding: 1mm 2mm 0; }
    .design-3 .d3-back-sign { flex: 0 0 auto; margin-top: auto; padding: 0 2mm 1mm; text-align: center; }
    .design-3 .d3-back-sign .signature-line { margin-top: 1mm; border-top: .2mm solid #999; width: 55%; display: inline-block; padding-top: .4mm; font-size: 3.5pt; color: #333; }
</style>
@endonce
<div class="card-pair">
    {{-- FRONT --}}
    <div class="id-card {{ $cardClass }} design-3">
        <span class="tm-tr"></span><span class="tm-bl"></span>
        <div class="d3-banner">
            @if($template->logo)
                <img src="{{ asset('uploads/id_card_template/' . $template->logo) }}">
                @elseif($schoolBrand->logo_url)
                <img src="{{ $schoolBrand->logo_url }}">
                @endif
            <h5>{{ $template->school_name }}</h5>
            <small>{{ $template->address_phone_email }}</small>
        </div>
        <div class="d3-photo">
            <img src="{{ $student->photo_url }}">
        </div>
        @if($template->show_student_name)
        <div class="d3-name">{{ strtoupper($student->first_name . ' ' . $student->last_name) }}</div>
        @endif
        <div class="d3-body">
            <table class="d3-details">
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
                {{-- <span>Valid: {{ now()->format('Y') }}-{{ now()->addYear()->format('y') }}</span> --}}
                <span>Valid: {{ !empty($validSession) ? $validSession : now()->format('Y') . '-' . now()->addYear()->format('y') }}</span>
            </div>
    </div>

    {{-- BACK --}}
    <div class="id-card {{ $cardClass }} design-3">
        <span class="tm-tr"></span><span class="tm-bl"></span>
        <div class="d3-banner d3-banner-back">
            <h5>{{ $template->school_name }}</h5>
            <small>Terms &amp; Conditions</small>
        </div>

        <div class="id-card-back-footer d3-back-code">
                            @if($template->show_qr_code)
                <div class="qr-wrap">{!! QrCode::size(60)->generate(route('card_verify.show', $student->getOrCreateQrCode())) !!}</div>
                @endif
                @if($template->show_barcode)
                @php $barcodeValue = $student->getOrCreateQrCode(); @endphp
                <div class="barcode-wrap">{!! DNS1D::getBarcodeHTML($barcodeValue, 'C128', 1, 20) !!}</div>
                <div class="barcode-text">{{ $barcodeValue }}</div>
                @endif
                
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

        <div class="d3-back-sign">
            <div class="signature-line">Principal / Authorized Signature</div>
                <div class="card-meta">
                    <span>Card No: {{ $serialNo }}</span>
                    <span>Issued: {{ now()->format('d-m-Y') }}</span>
                </div>
        </div>
    </div>
</div>
