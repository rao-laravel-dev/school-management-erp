@extends($current_layout)
@section('content')

{{-- Breadcrumb: home = route('dashboard') (x-breadcrumb home is admin-only, 403 for staff roles) --}}
<div class="page-breadcrumb d-none d-sm-flex d-lg-none align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Scan Attendance</div>
    <div class="ps-3">
        <nav>
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item">Attendance</li>
                @can('mark-attendance')
                <li class="breadcrumb-item"><a href="{{ route('attendance.create') }}">Student Attendance</a></li>
                @else
                <li class="breadcrumb-item">Student Attendance</li>
                @endcan
                <li class="breadcrumb-item active">Scan Attendance</li>
            </ol>
        </nav>
    </div>
</div>

@if($noClass)
<div class="kiosk-wrapper">
    <div class="alert alert-warning mb-0">
        <i class="bx bx-error-circle"></i> No class assigned to you for the current session. Ask the admin to assign you as Class Teacher to use Scan Attendance.
    </div>
</div>
@else
<div class="kiosk-wrapper">
    <div class="kiosk-card">

        {{-- ===== HEADER ===== --}}
        <div class="kiosk-header">
            <div class="kiosk-logo-box">
                @if(!empty($siteSetting->logo))
                <img src="{{ asset('uploads/site_setting/' . $siteSetting->logo) }}" alt="logo" class="kiosk-logo-img">
                @endif
                <h1 class="kiosk-school-name {{ !empty($siteSetting->logo) ? 'visually-hidden' : '' }}">{{ $siteSetting->school_name ?? 'School System' }}</h1>
            </div>

            <div class="kiosk-subtitle-row row justify-content-end">
                <div class="col-md-4 text-center">
                    <span class="kiosk-subtitle fw-semi-bold fs-5">Attendance Gadget</span>
                    @if($classScoped)
                    <div class="kiosk-subtitle small">Scanning for: {{ $scopeLabel }}</div>
                    @endif
                </div>
                <div class="col-md-3 offset-md-1 text-end d-flex align-items-center justify-content-end gap-1">
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
                    <button type="button" class="btn btn-sm kiosk-camera-btn d-none" id="kiosk-fullscreen-btn"
                        title="Full screen">
                        {{-- enter icon (shown normally) --}}
                        <svg class="kiosk-fs-enter" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                        </svg>
                        {{-- exit icon (shown while fullscreen) --}}
                        <svg class="kiosk-fs-exit" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
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
                <img id="kiosk-photo" src="{{ asset('uploads/no_image.jpg') }}" class="kiosk-photo-circle">

                <div class="kiosk-id-name" id="kiosk-id-name">-- | Waiting for scan...</div>

                <table class="table table-sm table-borderless kiosk-details mb-0" id="kiosk-details-table">
                    <tr>
                        <td id="kiosk-label-1">Father Name</td>
                        <td id="kiosk-value-1">-</td>
                    </tr>
                    <tr>
                        <td id="kiosk-label-2">Class / Section</td>
                        <td id="kiosk-value-2">-</td>
                    </tr>
                    <tr>
                        <td id="kiosk-label-3">Group</td>
                        <td id="kiosk-value-3">-</td>
                    </tr>
                    <tr>
                        <td id="kiosk-label-4">Admission / Joining Date</td>
                        <td id="kiosk-value-4">-</td>
                    </tr>
                    <tr>
                        <td>Date / Time</td>
                        <td id="kiosk-value-datetime">-</td>
                    </tr>
                    <tr>
                        <td>Marked By</td>
                        <td id="kiosk-value-marked-by">-</td>
                    </tr>
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
@endif

@endsection

@push('styles')
<style>
    html,
    body {
        overflow-x: hidden;
    }

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
        box-shadow: 0 4px 18px rgba(0, 0, 0, .06);
        width: 100%;
    }

    .kiosk-header {
        background: linear-gradient(135deg, #1e0fb0, #2a17e0) !important;
        color: #ffffff !important;
        text-align: center;
        padding: 40px 16px 200px;
    }

    .kiosk-header * {
        color: #ffffff !important;
    }

    .kiosk-logo-box {
        border: none;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 12px 28px;
        max-width: 100%;
    }

    .kiosk-logo-img {
        height: 40px;
        width: auto;
    }

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

    .kiosk-scan-input::placeholder {
        color: #9ca3af;
        font-size: 12px;
    }

    /* Dark mode: input ka background dark ho jata hai, text/placeholder light karo */
    html.dark-theme .kiosk-scan-input {
        color: #e4e5e6 !important;
    }

    html.dark-theme .kiosk-scan-input::placeholder {
        color: #aab0b6;
    }

    /* Dark mode: info box white rehta hai, magar .table ka text light ho jata hai (values faint), isliye values dark karo */
    html.dark-theme .kiosk-details tr td:last-child {
        color: #1e293b;
    }

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

    .kiosk-camera-btn svg {
        display: block;
    }

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

    .kiosk-details {
        margin: 0 auto;
        text-align: left;
        border-collapse: separate !important;
        border-spacing: 0 10px;
    }

    .kiosk-details tr td:first-child {
        background: #e9ecef;
        color: #6b7280;
        font-weight: 600;
        font-size: clamp(11px, 1.4vw, 14px);
        width: 45%;
        text-transform: uppercase;
    }

    .kiosk-details tr td {
        padding: 10px 12px;
        font-size: clamp(13px, 1.6vw, 17px);
    }

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

    .kiosk-status-box.status-idle {
        border-color: #d1d5db;
        color: #9ca3af;
    }

    .kiosk-status-box.status-success {
        border-color: #22c55e;
        color: #16a34a;
    }

    .kiosk-status-box.status-warning {
        border-color: #d97706;
        color: #d97706;
    }

    /* late toastr = box wali same orange; style sirf is page par load hoti hai, baqi pages ka toastr unchanged */
    #toast-container > .toast-warning {
        background-color: #d97706 !important;
    }

    .kiosk-status-box.status-danger {
        border-color: #ef4444;
        color: #dc2626;
    }

    /* ---- large / kiosk screens ---- */
    @media (min-width: 1400px) {
        .kiosk-header {
            padding: 56px 24px 220px;
        }

        .kiosk-info-box {
            margin-top: -190px;
        }
    }

    @media (max-width: 576px) {
        .kiosk-header {
            padding: 24px 12px 110px;
        }

        .kiosk-info-box {
            margin-top: -60px;
            padding: 20px 14px;
        }

        .kiosk-subtitle-row .col-md-4,
        .kiosk-subtitle-row .col-md-3.offset-md-1 {
            text-align: center !important;
            margin-left: 0 !important;
            justify-content: center !important;
        }

        .kiosk-scan-input {
            max-width: 90%;
            margin-top: 8px;
        }

        .kiosk-logo-box {
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
        }

        .kiosk-school-name {
            font-size: 18px;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .kiosk-logo-img {
            height: 32px;
            max-width: 100%;
        }
    }

    /* ---- desktop: kiosk fits one screen, no page scroll, blue 50% / white 50% ---- */
    @media (min-width: 992px) {
        .kiosk-wrapper {
            --kiosk-topbar: 60px;  /* .topbar height, app.css */
            --kiosk-footer: 36px;  /* fixed .page-footer height, app.css */
            padding-bottom: 0;     /* was 24px, pushed the page past the viewport */
        }

        .kiosk-card {
            /* 100vh minus topbar, footer and .page-content vertical padding (1.5rem + .7rem) */
            height: calc(100vh - var(--kiosk-topbar) - var(--kiosk-footer) - 2.2rem);
            display: flex;
            flex-direction: column;
            position: relative;    /* anchor for the 50% blue backdrop below */
        }

        /* blue backdrop = top 50% of the card; header and body sit above it (z-index 1) */
        .kiosk-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(135deg, #1e0fb0, #2a17e0);
            z-index: 0;
        }

        .kiosk-header {
            flex: 0 0 auto;        /* height = its content (incl. the class-teacher "Scanning for" line) */
            position: relative;
            z-index: 1;
            padding: 2vh 24px;     /* fixed 200px / 220px bottom padding removed */
        }

        .kiosk-logo-box {
            padding: 8px 24px;
        }

        .kiosk-logo-img {
            height: clamp(40px, 7vh, 72px);
        }

        .kiosk-subtitle-row {
            margin-top: 1vh;
        }

        .kiosk-datetime {
            font-size: clamp(18px, 3.2vh, 30px);
            margin-top: 1vh;
        }

        /* body starts right after the header (no pull-up); it is the scroll container (safety net) */
        .kiosk-body {
            flex: 1 1 auto;
            min-height: 0;
            position: relative;
            z-index: 1;
            overflow-y: auto;
            padding: 0 clamp(20px, 3vw, 40px) 1.5vh;
        }

        /* stacked order (photo, name, rows, status) like below 992px, only vh-based tighter sizes */
        .kiosk-info-box {
            max-width: 900px;
            margin: 0 auto;
            padding: clamp(10px, 1.6vh, 32px) clamp(16px, 2vw, 28px);
        }

        .kiosk-photo-circle {
            width: clamp(60px, 13vh, 140px);
            height: clamp(60px, 13vh, 140px);
            margin: 0 auto 1vh;
        }

        .kiosk-id-name {
            font-size: clamp(16px, 3vh, 30px);
            margin-bottom: 1vh;
        }

        .kiosk-details {
            border-spacing: 0 clamp(2px, .5vh, 10px);
        }

        .kiosk-details tr td {
            padding: clamp(2px, .6vh, 10px) 12px;
            font-size: clamp(12px, 1.8vh, 17px);
        }

        .kiosk-details tr td:first-child {
            font-size: clamp(11px, 1.4vh, 14px);
        }

        .kiosk-status-box {
            padding: clamp(6px, 1.2vh, 18px) 24px;
            margin-top: clamp(6px, 1.2vh, 22px);
            margin-bottom: clamp(6px, 1vh, 24px);
            font-size: clamp(16px, 2.8vh, 24px);
        }
    }

    /* ---- full screen toggle (kiosk device) ---- */
    /* icon swap; id selector beats ".kiosk-camera-btn svg { display:block }" */
    #kiosk-fullscreen-btn .kiosk-fs-exit {
        display: none;
    }

    body.kiosk-fullscreen #kiosk-fullscreen-btn .kiosk-fs-enter {
        display: none;
    }

    body.kiosk-fullscreen #kiosk-fullscreen-btn .kiosk-fs-exit {
        display: block;
    }

    /* hide layout chrome (same markup in admin / reception / teacher layouts) */
    body.kiosk-fullscreen .sidebar-wrapper,
    body.kiosk-fullscreen .topbar,
    body.kiosk-fullscreen .page-footer,
    body.kiosk-fullscreen .overlay,
    body.kiosk-fullscreen .back-to-top {
        display: none !important;
    }

    /* remove the space the sidebar / topbar / footer reserved */
    body.kiosk-fullscreen .page-wrapper,
    body.kiosk-fullscreen .wrapper.toggled .page-wrapper {
        margin: 0 !important;
    }

    body.kiosk-fullscreen .page-content {
        padding: 0 !important;
    }

    body.kiosk-fullscreen .kiosk-wrapper {
        max-width: none;
        padding: 0;
    }

    body.kiosk-fullscreen .kiosk-card {
        border: 0;
        border-radius: 0;
        box-shadow: none;
        min-height: 100vh;
    }

    @media (min-width: 992px) {
        /* card = whole screen; flex column + 50% blue ::before stay as above */
        body.kiosk-fullscreen .kiosk-card {
            height: 100vh;
            justify-content: center;  /* header + body block sits in the vertical middle: equal space above and below */
        }

        /* header turns transparent so the card's 50% blue ::before also fills the space above it (no seam) */
        body.kiosk-fullscreen .kiosk-header {
            background: transparent !important;
        }

        /* body stops absorbing the free space (flex 1 1 auto) so centering works; it can still shrink + scroll as a safety net.
           Its bottom padding and the status box bottom margin are removed so the gap below the status box equals the gap above the header. */
        body.kiosk-fullscreen .kiosk-body {
            flex: 0 1 auto;
            padding-bottom: 0;
        }

        body.kiosk-fullscreen .kiosk-status-box {
            margin-bottom: 0;
        }
    }

    /* fullscreen on tall screens (1920x1080 / 1920x1200): same elements, higher vh coefficients and max caps.
       Below 900px height nothing changes, so 1366x768 keeps the sizes above. */
    @media (min-width: 992px) and (min-height: 900px) {
        body.kiosk-fullscreen .kiosk-logo-img {
            height: clamp(40px, 7.5vh, 120px);
        }

        body.kiosk-fullscreen .kiosk-datetime {
            font-size: clamp(18px, 3.5vh, 44px);
        }

        body.kiosk-fullscreen .kiosk-info-box {
            max-width: 1000px;
            padding: clamp(10px, 1.8vh, 36px) clamp(16px, 2vw, 40px);
        }

        body.kiosk-fullscreen .kiosk-photo-circle {
            width: clamp(60px, 15vh, 200px);
            height: clamp(60px, 15vh, 200px);
        }

        body.kiosk-fullscreen .kiosk-id-name {
            font-size: clamp(16px, 3.6vh, 42px);
            line-height: 1.25;
        }

        body.kiosk-fullscreen .kiosk-details {
            border-spacing: 0 clamp(2px, .55vh, 12px);
        }

        body.kiosk-fullscreen .kiosk-details tr td {
            padding: clamp(2px, .55vh, 12px) 12px;
            font-size: clamp(12px, 1.9vh, 24px);
            line-height: 1.3;
        }

        body.kiosk-fullscreen .kiosk-details tr td:first-child {
            font-size: clamp(11px, 1.5vh, 18px);
        }

        body.kiosk-fullscreen .kiosk-status-box {
            padding: clamp(6px, 1.5vh, 26px) 32px;
            margin-top: clamp(6px, 1.6vh, 26px);
            font-size: clamp(16px, 3.4vh, 38px);
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
    (function() {
        let qrScanner = null;
        let isScanning = false; // true only once start() has actually resolved
        let isStarting = false; // true while start() promise is in-flight

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
                .catch(() => {}) // ignore "not running" style errors
                .then(() => qrScanner ? qrScanner.clear().catch(() => {}) : null)
                .finally(() => {
                    qrScanner = null;
                    isScanning = false;
                    isStarting = false;
                });
        }

        /* ---------- camera QR/barcode scanning inside modal ---------- */
        $('#qrScannerModal').on('shown.bs.modal', function() {
            if (qrScanner || isStarting) return; // guard against double-init

            // Camera sirf HTTPS (ya localhost) par chalta hai; http par phone browser block karta hai
            if (!window.isSecureContext) {
                toastr.warning('Camera needs HTTPS. Use the Roll No / Admission No box instead.');
                return;
            }
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

            let lastScannedCode = null;
            let scanCooldown = false;

            qrScanner.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 250
                },
                (decodedText) => {
                    // camera ko turant pause karein — koi naya frame process nahi hoga
                    qrScanner.pause(true);

                    submitScan(decodedText, 'qr-camera');

                    // 3 second baad camera khud resume ho jaye, agle scan ke liye
                    setTimeout(() => {
                        if (qrScanner && isScanning) {
                            qrScanner.resume();
                        }
                    }, 3000);
                },
                () => {
                    /* per-frame "not found" callback — expected, ignore */
                }
            ).then(() => {
                isScanning = true;
                isStarting = false;
            }).catch(function(err) {
                isStarting = false;
                isScanning = false;
                qrScanner = null;
                toastr.error('Camera could not be started: ' + err);
            });
        });

        /* close (X) button — stop camera explicitly, THEN close, so the modal
           is never left half-closed while the camera stream is still held */
        $('#qrScannerCloseBtn').on('click', function() {
            stopScanner().finally(() => $('#qrScannerModal').modal('hide'));
        });

        // covers backdrop click / ESC key as well
        $('#qrScannerModal').on('hide.bs.modal', function() {
            stopScanner();
        });

        $('#qrScannerModal').on('hidden.bs.modal', function() {
            refocusScanInput();
        });

        /* ---------- live day / date / time ---------- */
        function tickClock() {
            const now = new Date();
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
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

        let lastScanId = null;

        function pollLatestScan() {
            $.get("{{ route('attendance.scan.latest') }}", function(res) {
                if (res && res.scan_id && res.scan_id !== lastScanId) {
                    lastScanId = res.scan_id;
                    renderKioskPanel(res.student, res.status, res.message, res.scan_type, res.is_late);
                }
            });
        }

        // Class teacher ka page gate ka latest-scan card poll nahi karta (server bhi [] deta hai)
        const classScoped = @json($classScoped);
        // Phone/tablet (touch): auto-refocus band, warna on-screen keyboard baar baar khulta hai
        const isTouchDevice = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

        if (!classScoped) {
            setInterval(pollLatestScan, 1500);
        }

        /* ---------- keep input focused so hardware scanner (keyboard-wedge) keeps working even if user clicks elsewhere ---------- */
        window.refocusScanInput = function refocusScanInput() {
            if ($('#qrScannerModal').hasClass('show') || isTouchDevice) return;
            if (!$('#scan-input').is(':focus')) {
                $('#scan-input').trigger('focus');
            }
        };
        if (!isTouchDevice) {
            setInterval(refocusScanInput, 1500);
        }

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
                    renderKioskPanel(res.student, res.status, res.message, res.scan_type, res.is_late);
                    toastr[res.status === 'error' ? 'error' : (res.is_late ? 'warning' : 'success')](res.message);
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message :
                        "Scan failed. Please try again.";
                    // Invalid code / enrollment not found: purana card hata do, sirf error status dikhao
                    resetKioskPanel();
                    $('#kiosk-status-box').attr('class', 'kiosk-status-box status-danger').text(msg);
                    toastr.error(msg);
                }
            });
            $('#scan-input').val('').focus();
        };

        /* ---------- update the static panel in place (student OR staff) ---------- */
        /* pichle banday ka card saaf karo (stale result na dikhe) */
        function resetKioskPanel() {
            $('#kiosk-photo').off('error').attr('src', "{{ asset('uploads/no_image.jpg') }}");
            $('#kiosk-id-name').text('-- | Waiting for scan...');
            $('#kiosk-label-1').text('Father Name');
            $('#kiosk-label-2').text('Class / Section');
            $('#kiosk-label-3').text('Group');
            $('#kiosk-label-4').text('Admission / Joining Date');
            $('#kiosk-value-1, #kiosk-value-2, #kiosk-value-3, #kiosk-value-4, #kiosk-value-datetime, #kiosk-value-marked-by').text('-');
            $('#kiosk-value-3, #kiosk-value-4').closest('tr').show();
        }

        function renderKioskPanel(person, status, message, scanType, isLate) {
            if (!person) {
                resetKioskPanel();
                $('#kiosk-id-name').text('-- | Not Found');
                $('#kiosk-status-box').attr('class', 'kiosk-status-box status-danger').text(message || 'Not Found');
                return;
            }

            // welcome (checkin) = green success, late checkin = yellow/orange warning, everything else (checkout / already marked / error) = red danger
            let statusClass = (scanType === 'checkin') ? (isLate ? 'status-warning' : 'status-success') : 'status-danger';
            let bigMsg = message || 'WELCOME';

            let idValue;

            if (person.type === 'staff') {
                idValue = person.staff_id; // e.g. teacher_id / accountant_id / receptionist_id / librarian_id — resolved server-side
                $('#kiosk-label-1').text('Role');
                $('#kiosk-value-1').text(person.role || '-');
                $('#kiosk-label-2').text('Staff ID');
                $('#kiosk-value-2').text(person.staff_id || '-');
                // row 3 = Joining Date (value na ho to row hide), row 4 = Status (scan type + is_late se)
                $('#kiosk-label-3').text('Joining Date');
                $('#kiosk-value-3').text(person.joining_date || '');
                $('#kiosk-value-3').closest('tr').toggle(!!person.joining_date);
                $('#kiosk-label-4').text('Status');
                $('#kiosk-value-4').text(scanType === 'checkin' ? (isLate ? 'Late' : 'On time') : 'Check-out');
                $('#kiosk-value-4').closest('tr').show();
            } else {
                $('#kiosk-value-3, #kiosk-value-4').closest('tr').show(); // staff card ne hide kiya ho to wapas
                idValue = person.roll_no || person.admission_no;
                $('#kiosk-label-1').text('Father Name');
                $('#kiosk-value-1').text(person.father_name || '-');
                $('#kiosk-label-2').text('Class / Section');
                $('#kiosk-value-2').text(person.class_section || '-');
                $('#kiosk-label-3').text('Group');
                $('#kiosk-value-3').text(person.group || '-');
                $('#kiosk-label-4').text('Admission Date');
                $('#kiosk-value-4').text(person.admission_date || '-');
            }

            let dateTime = [person.date, person.time].filter(Boolean).join(' , ');
            $('#kiosk-value-datetime').text(dateTime || '-');
            $('#kiosk-value-marked-by').text(person.marked_by || '-');
            $('#kiosk-photo').attr('src', person.photo).off('error').on('error', function() {
                $(this).attr('src', "{{ asset('uploads/no_image.jpg') }}");
            });
            $('#kiosk-id-name').text(`${idValue || ''} | ${person.name}`);
            $('#kiosk-status-box').attr('class', 'kiosk-status-box ' + statusClass).text(bigMsg);
        }

        /* ---------- full screen toggle (kiosk device) ---------- */
        // whole page goes fullscreen (not .kiosk-card) so the camera modal, toastr and Swal stay visible
        const fsRoot = document.documentElement;
        const fsSupported = !!(document.fullscreenEnabled || document.webkitFullscreenEnabled);

        function fsElement() {
            return document.fullscreenElement || document.webkitFullscreenElement || null;
        }

        if (fsSupported) {
            $('#kiosk-fullscreen-btn').removeClass('d-none');

            $('#kiosk-fullscreen-btn').on('click', function() {
                if (fsElement()) {
                    (document.exitFullscreen || document.webkitExitFullscreen).call(document);
                } else {
                    const req = fsRoot.requestFullscreen || fsRoot.webkitRequestFullscreen;
                    const p = req.call(fsRoot);
                    // promise rejects if the browser blocks it; just put focus back
                    if (p && p.catch) p.catch(function() { refocusScanInput(); });
                }
            });

            // fires on button click, Esc and browser exit; body class drives the page CSS
            $(document).on('fullscreenchange webkitfullscreenchange', function() {
                const isFs = !!fsElement();
                $('body').toggleClass('kiosk-fullscreen', isFs);
                $('#kiosk-fullscreen-btn').attr('title', isFs ? 'Exit full screen' : 'Full screen');
                refocusScanInput();
            });
        }
    })();
</script>
@endpush