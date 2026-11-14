{{-- Design 2: side colour band --}}
@once
<style>
    /* ---- Design 2: side colour band ---- */
    .id-card.design-2.horizontal { flex-direction: row; }
    .design-2 .d2-band { background: var(--header-color); color: #fff; display: flex; align-items: center; justify-content: center; gap: 1.5mm; flex: 0 0 auto; text-align: center; overflow: hidden; }
    .design-2.horizontal .d2-band { width: 15mm; flex-direction: column; padding: 2mm 0; }
    .design-2.vertical .d2-band { height: 14mm; flex-direction: row; padding: 0 2mm; }
    .design-2 .d2-band img { width: 12mm; height: auto; max-height: 14mm; object-fit: contain; flex: 0 0 auto; }
    .design-2 .d2-band .d2-school { font-size: 6pt; font-weight: 700; line-height: 1.1; color: #fff; }
    .design-2.horizontal .d2-band .d2-school { writing-mode: vertical-rl; transform: rotate(180deg); max-height: 36mm; overflow: hidden; }
    .design-2 .d2-main { flex: 1 1 auto; min-width: 0; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
    .design-2 .d2-address { flex: 0 0 auto; font-size: 4.5pt; text-align: center; color: #333; padding: 1mm 2mm; border-bottom: .3mm solid var(--header-color); }
    .design-2 .id-card-body { gap: 4mm; }
    .design-2 .id-card-body table td.label { padding-right: 3mm; }
    .design-2 .id-card-body .photo img { width: 16mm; height: 16mm; border-radius: 50%; border: .5mm solid var(--header-color); padding: 0; }
    .design-2.vertical .id-card-body { gap: 2mm; }
    /* back: tighter so the signature line fits inside the narrower content area */
    .design-2 .id-card-back-content { padding: .6mm 1.5mm; gap: 1.5mm; }
    .design-2 .id-card-back-rules, .design-2 .id-card-back-contact { font-size: 3.4pt; }
    .design-2 .id-card-back-rules h6, .design-2 .id-card-back-contact h6 { font-size: 3.8pt; margin-bottom: .2mm; }
    .design-2 .id-card-back-rules ul li { margin-bottom: 0; line-height: 1.05; }
    .design-2 .id-card-back-contact table { margin-bottom: .3mm; }
    .design-2 .id-card-back-contact table td { font-size: 3.4pt; padding: .1mm 0; overflow-wrap: anywhere; }
    .design-2 .id-card-back-footer { padding: .6mm 2mm .4mm; }
    .design-2 .id-card-back-footer .qr-wrap { margin: .3mm 0 .6mm; }
    .design-2 .id-card-back-footer .qr-wrap svg { width: 11mm; height: 11mm; }
    .design-2 .id-card-back-footer .signature-line { margin-top: .4mm; }
    .design-2 .id-card-back-footer .card-meta { margin-top: .4mm; }
</style>
@endonce
<div class="card-pair">
    {{-- FRONT --}}
    <div class="id-card {{ $cardClass }} design-2">
        <span class="tm-tr"></span><span class="tm-bl"></span>
        <div class="d2-band">
            @if($template->logo)
                <img src="{{ asset('uploads/id_card_template/' . $template->logo) }}">
                @elseif($schoolBrand->logo_url)
                <img src="{{ $schoolBrand->logo_url }}">
                @endif
            <div class="d2-school">{{ $template->school_name }}</div>
        </div>
        <div class="d2-main">
            <div class="d2-address">{{ $template->address_phone_email }}</div>
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
                {{-- <span>Valid: {{ now()->format('Y') }}-{{ now()->addYear()->format('y') }}</span> --}}
                <span>Valid: {{ !empty($validSession) ? $validSession : now()->format('Y') . '-' . now()->addYear()->format('y') }}</span>
            </div>
        </div>
    </div>

    {{-- BACK --}}
    <div class="id-card {{ $cardClass }} design-2">
        <span class="tm-tr"></span><span class="tm-bl"></span>
        <div class="d2-band">
            <div class="d2-school">{{ $template->school_name }}</div>
        </div>
        <div class="d2-main">
            <div class="d2-address">Terms &amp; Conditions</div>
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
                <div class="qr-wrap">{!! QrCode::size(60)->generate(route('card_verify.show', $student->getOrCreateQrCode())) !!}</div>
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
</div>
