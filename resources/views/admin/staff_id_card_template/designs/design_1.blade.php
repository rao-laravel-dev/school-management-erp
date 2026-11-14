{{-- Design 1: original layout (moved unchanged from card.blade.php) --}}
    <div class="card-pair">
        {{-- FRONT --}}
        <div class="id-card {{ $cardClass }} design-1">
            <span class="tm-tr"></span><span class="tm-bl"></span>
            <div class="id-card-header">
                @if($template->logo)
                <img src="{{ asset('uploads/staff_id_card_template/' . $template->logo) }}">
                @elseif($schoolBrand->logo_url)
                <img src="{{ $schoolBrand->logo_url }}">
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

            <div class="id-card-footer">
                <span>{{ $serialNo }}</span>
                {{-- <span>Valid: {{ now()->format('Y') }}-{{ now()->addYear()->format('y') }}</span> --}}
                <span>Valid: {{ !empty($validSession) ? $validSession : now()->format('Y') . '-' . now()->addYear()->format('y') }}</span>
            </div>
        </div>

        {{-- BACK --}}
        <div class="id-card {{ $cardClass }} design-1">
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
                <div class="qr-wrap">{!! QrCode::size(60)->generate(route('card_verify.show', $staff->getOrCreateQrCode())) !!}</div>
                @endif
                @if($template->show_barcode)
                @php $barcodeValue = $staff->getOrCreateQrCode(); @endphp
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
