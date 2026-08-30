@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Attendance', 'url' => '#'],
    ['label' => 'Student Attendance', 'url' => route('attendance.create')],
    ['label' => 'Scan Attendance', 'url' => '#'],
]" />

<div class="kiosk-wrapper">
    <div class="kiosk-card">

        {{-- ===== HEADER ===== --}}
        <div class="kiosk-header">
            <div class="kiosk-logo-box">
                @if(!empty($siteSetting->logo))
                    <img src="{{ asset($siteSetting->logo) }}" alt="logo" class="kiosk-logo-img">
                @endif
                <h1 class="kiosk-school-name">{{ $siteSetting->school_name ?? 'School System' }}</h1>
            </div>

            <div class="kiosk-subtitle-row row justify-content-end">
                <div class="col-md-4 text-center">
                    <span class="kiosk-subtitle fw-semi-bold fs-5">Attendance Gadget</span>
                </div>
                <div class="col-md-2 offset-md-2 text-end d-flex align-items-center justify-content-end gap-1">
                    <input type="text" id="scan-input" class="form-control form-control-sm kiosk-scan-input"
                           placeholder="Scan / Roll No / Adm No"
                           autocomplete="off" autofocus>
                    <button type="button" class="btn btn-sm kiosk-camera-btn" title="Scan with camera"
                            data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                        {{-- inline SVG so it never depends on an icon-font being loaded on this layout --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="kiosk-datetime">
                <span id="kiosk-day"></span>,
                <span id="kiosk-date"></span>
                <span id="kiosk-time" class="ms-2"></span>
            </div>
        </div>

        {{-- ===== BODY (static structure, only values change on scan) ===== --}}
        <div class="kiosk-body">

            <div class="kiosk-info-box">
                <img id="kiosk-photo" src="/images/default-avatar.png" class="kiosk-photo-circle">

                <div class="kiosk-id-name" id="kiosk-id-name">-- | Waiting for scan...</div>

                <table class="table table-sm table-borderless kiosk-details mb-0" id="kiosk-details-table">
                    <tr><td id="kiosk-label-1">Father Name</td><td id="kiosk-value-1">-</td></tr>
                    <tr><td id="kiosk-label-2">Class / Section</td><td id="kiosk-value-2">-</td></tr>
                    <tr><td id="kiosk-label-3">Group</td><td id="kiosk-value-3">-</td></tr>
                    <tr><td id="kiosk-label-4">Admission / Joining Date</td><td id="kiosk-value-4">-</td></tr>
                    <tr><td>Date / Time</td><td id="kiosk-value-datetime">-</td></tr>
                </table>
            </div>

            <div class="kiosk-status-box status-idle" id="kiosk-status-box">Please Scan</div>

        </div>
    </div>

    {{-- ===== QR / BARCODE CAMERA SCANNER MODAL ===== --}}
    <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        Scan QR / Barcode
                    </h6>
                    <button type="button" class="btn-close" id="qrScannerCloseBtn" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qr-reader" style="width: 100%;"></div>
                    <p class="text-muted small mt-2 mb-0">Point the camera at a student/staff QR or barcode</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    html, body { overflow-x: hidden; }

    .kiosk-wrapper {
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 16px 24px;
        overflow-x: hidden;
        box-sizing: border-box;
    }

    .kiosk-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 18px rgba(0,0,0,.06);
        width: 100%;
    }

    .kiosk-header {
        background: linear-gradient(135deg, #1e0fb0, #2a17e0) !important;
        color: #ffffff !important;
        text-align: center;
        padding: 40px 16px 200px;
    }
    .kiosk-header * { color: #ffffff !important; }

    .kiosk-logo-box {
        border: 1px solid rgba(255,255,255,.6);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 12px 28px;
        max-width: 100%;
    }

    .kiosk-logo-img { height: 40px; width: auto; }

    .kiosk-school-name {
        font-size: clamp(20px, 3vw, 34px);
        font-weight: 800;
        margin: 0;
        color: #ffffff !important;
    }

    .kiosk-subtitle-row {
        margin: 12px auto 0;
    }

    .kiosk-subtitle {
        opacity: .95;
        font-size: clamp(13px, 2vw, 16px);
        white-space: nowrap;
    }

    .kiosk-scan-input {
        width: 100%;
        max-width: 220px;
        display: inline-block;
        color: #1e293b !important;
    }
    .kiosk-scan-input::placeholder { color: #9ca3af; font-size: 12px; }

    .kiosk-camera-btn {
        background: #ffffff !important;
        color: #1e0fb0 !important;
        border: none;
        border-radius: 6px;
        padding: 6px 10px;
        line-height: 1;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .kiosk-camera-btn svg { display: block; }
    /* .kiosk-header * forces color:#fff on every descendant (incl. this svg),
       which made the icon invisible on the button's white background.
       This rule has equal specificity but targets the svg directly, so it wins. */
    .kiosk-header .kiosk-camera-btn svg,
    .kiosk-header .kiosk-camera-btn svg * {
        color: #1e0fb0 !important;
        stroke: #1e0fb0 !important;
    }

    .kiosk-datetime {
        font-size: clamp(16px, 3vw, 30px);
        font-weight: 700;
        margin-top: 14px;
    }

    .kiosk-body {
        padding: clamp(20px, 3vw, 40px);
        padding-top: 0;
        text-align: center;
    }

    .kiosk-info-box {
        max-width: 760px;
        margin: -170px auto 0;
        background: #fff;
        border: 1px solid #1e0fb0;
        border-radius: 12px;
        padding: clamp(20px, 3vw, 32px) clamp(16px, 3vw, 28px);
        position: relative;
    }

    .kiosk-photo-circle {
        width: clamp(100px, 12vw, 160px);
        height: clamp(100px, 12vw, 160px);
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #1e0fb0;
        display: block;
        margin: 0 auto 14px;
        background: #f3f4f6;
    }

    .kiosk-id-name {
        text-align: center;
        font-weight: 800;
        color: #1e0fb0;
        font-size: clamp(18px, 3vw, 30px);
        margin-bottom: 16px;
        word-break: break-word;
    }

    .kiosk-details { margin: 0 auto; text-align: left; border-collapse: separate !important; border-spacing: 0 10px; }
    .kiosk-details tr td:first-child {
        background: #e9ecef;
        color: #6b7280;
        font-weight: 600;
        font-size: clamp(11px, 1.4vw, 14px);
        width: 45%;
        text-transform: uppercase;
    }
    .kiosk-details tr td { padding: 10px 12px; font-size: clamp(13px, 1.6vw, 17px); }

    .kiosk-status-box {
        border: 2px solid;
        border-radius: 8px;
        text-align: center;
        padding: clamp(12px, 2vh, 18px) 24px;
        margin-top: 22px;
        margin-bottom: 24px;
        width: fit-content;
        min-width: 220px;
        max-width: 90%;
        margin-left: auto;
        margin-right: auto;
        font-size: clamp(16px, 2.6vw, 24px);
        font-weight: 800;
        letter-spacing: .5px;
        line-height: 1.3;
        word-break: break-word;
    }
    .kiosk-status-box.status-idle    { border-color: #d1d5db; color: #9ca3af; }
    .kiosk-status-box.status-success { border-color: #22c55e; color: #16a34a; }
    .kiosk-status-box.status-danger  { border-color: #ef4444; color: #dc2626; }

    /* ---- large / kiosk screens ---- */
    @media (min-width: 1400px) {
        .kiosk-header { padding: 56px 24px 220px; }
        .kiosk-info-box { margin-top: -190px; }
    }

    @media (max-width: 576px) {
        .kiosk-header { padding: 24px 12px 110px; }
        .kiosk-info-box { margin-top: -60px; padding: 20px 14px; }
        .kiosk-subtitle-row .col-md-3,
        .kiosk-subtitle-row .col-md-3.offset-md-3 { text-align: center !important; margin-left: 0 !important; }
        .kiosk-scan-input { max-width: 90%; margin-top: 8px; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    let qrScanner = null;
    let isScanning = false;   // true only once start() has actually resolved
    let isStarting = false;   // true while start() promise is in-flight

    /* ---------- safely stop + clear the camera, no matter what state it's in ---------- */
    function stopScanner() {
        if (!qrScanner) return Promise.resolve();

        if (!isScanning) {
            // never actually started (or already stopped) — just drop the reference
            qrScanner = null;
            isStarting = false;
            return Promise.resolve();
        }

        return qrScanner.stop()
            .catch(() => {})               // ignore "not running" style errors
            .then(() => qrScanner ? qrScanner.clear().catch(() => {}) : null)
            .finally(() => {
                qrScanner = null;
                isScanning = false;
                isStarting = false;
            });
    }

    /* ---------- camera QR/barcode scanning inside modal ---------- */
    $('#qrScannerModal').on('shown.bs.modal', function () {
        if (qrScanner || isStarting) return; // guard against double-init
        isStarting = true;

        qrScanner = new Html5Qrcode("qr-reader", {
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.CODE_93,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.ITF
            ]
        });

        qrScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            (decodedText) => {
                // stop first, then close modal, then submit — avoids a race with hide.bs.modal
                stopScanner().finally(() => {
                    $('#qrScannerModal').modal('hide');
                    submitScan(decodedText, 'qr-camera');
                });
            },
            () => { /* per-frame "not found" callback — expected, ignore */ }
        ).then(() => {
            isScanning = true;
            isStarting = false;
        }).catch(function (err) {
            isStarting = false;
            isScanning = false;
            qrScanner = null;
            toastr.error('Camera could not be started: ' + err);
        });
    });

    /* close (X) button — stop camera explicitly, THEN close, so the modal
       is never left half-closed while the camera stream is still held */
    $('#qrScannerCloseBtn').on('click', function () {
        stopScanner().finally(() => $('#qrScannerModal').modal('hide'));
    });

    // covers backdrop click / ESC key as well
    $('#qrScannerModal').on('hide.bs.modal', function () {
        stopScanner();
    });

    $('#qrScannerModal').on('hidden.bs.modal', function () {
        refocusScanInput();
    });

    /* ---------- live day / date / time ---------- */
    function tickClock() {
        const now = new Date();
        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        const pad = n => String(n).padStart(2, '0');

        $('#kiosk-day').text(days[now.getDay()]);
        $('#kiosk-date').text(`${pad(now.getDate())}-${pad(now.getMonth()+1)}-${now.getFullYear()}`);

        let h = now.getHours();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        $('#kiosk-time').text(`${pad(h)}:${pad(now.getMinutes())}:${pad(now.getSeconds())} ${ampm}`);
    }
    tickClock();
    setInterval(tickClock, 1000);

    /* ---------- keep input focused so hardware scanner (keyboard-wedge) keeps working even if user clicks elsewhere ---------- */
    window.refocusScanInput = function refocusScanInput() {
        if ($('#qrScannerModal').hasClass('show')) return;
        if (!$('#scan-input').is(':focus')) {
            $('#scan-input').trigger('focus');
        }
    };
    setInterval(refocusScanInput, 1500);

    $(document).on('keypress', '#scan-input', function(e) {
        if (e.key === 'Enter') {
            let code = $(this).val().trim();
            $(this).val('');
            if (code) submitScan(code, 'scan-input');
        }
    });

    /* ---------- submit scan ---------- */
    window.submitScan = function submitScan(code, deviceId) {
        $.ajax({
            url: "{{ route('attendance.scan.store') }}",
            type: "POST",
            data: {
                code: code,
                device_id: deviceId,
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                renderKioskPanel(res.student, res.status, res.message, res.scan_type);
                toastr[res.status === 'error' ? 'error' : 'success'](res.message);
            },
            error: function(xhr) {
                let msg = xhr.responseJSON && xhr.responseJSON.message ?
                    xhr.responseJSON.message :
                    "Scan failed. Please try again.";
                toastr.error(msg);
            }
        });
        $('#scan-input').val('').focus();
    };

    /* ---------- update the static panel in place (student OR staff) ---------- */
    function renderKioskPanel(person, status, message, scanType) {
        if (!person) {
            $('#kiosk-id-name').text('-- | Not Found');
            $('#kiosk-status-box').attr('class', 'kiosk-status-box status-danger').text(message || 'Not Found');
            return;
        }

        // welcome (checkin) = green success, everything else (checkout / already marked / error) = red danger
        let statusClass = (scanType === 'checkin') ? 'status-success' : 'status-danger';
        let bigMsg = message || 'WELCOME';

        let idValue;

        if (person.type === 'staff') {
            idValue = person.staff_id; // e.g. teacher_id / accountant_id / receptionist_id / librarian_id — resolved server-side
            $('#kiosk-label-1').text('Designation');
            $('#kiosk-value-1').text(person.designation ?? '-');
            $('#kiosk-label-2').text('Department');
            $('#kiosk-value-2').text(person.department ?? '-');
            $('#kiosk-label-3').text('Phone');
            $('#kiosk-value-3').text(person.phone ?? '-');
            $('#kiosk-label-4').text('Joining Date');
            $('#kiosk-value-4').text(person.joining_date ?? '-');
        } else {
            idValue = person.roll_no ?? person.admission_no;
            $('#kiosk-label-1').text('Father Name');
            $('#kiosk-value-1').text(person.father_name ?? '-');
            $('#kiosk-label-2').text('Class / Section');
            $('#kiosk-value-2').text(person.class_section ?? '-');
            $('#kiosk-label-3').text('Group');
            $('#kiosk-value-3').text(person.group ?? '-');
            $('#kiosk-label-4').text('Admission Date');
            $('#kiosk-value-4').text(person.admission_date ?? '-');
        }

        let dateTime = [person.date, person.time].filter(Boolean).join(' , ');
        $('#kiosk-value-datetime').text(dateTime || '-');
        $('#kiosk-photo').attr('src', person.photo).off('error').on('error', function() {
            $(this).attr('src', '/images/default-avatar.png');
        });
        $('#kiosk-id-name').text(`${idValue ?? ''} | ${person.name}`);
        $('#kiosk-status-box').attr('class', 'kiosk-status-box ' + statusClass).text(bigMsg);
    }
})();
</script>
@endpush