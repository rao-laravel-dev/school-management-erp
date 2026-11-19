@extends($current_layout)

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Site Setting', 'url' => '#'],
    ['label' => 'Setting', 'url' => route('site_setting.edit')],
]" />

{{-- T340a: header card (cover + icon + name + tagline + counts) with the 3 tabs at its bottom --}}
@php
    $theme = $setting->theme;
    $initials = strtoupper(mb_substr(trim($setting->school_name ?: 'S'), 0, 1));
    // Same image the sidebar shows: school icon, else the MSS product icon, else the default logo icon; initial only if none exists
    $headIcon = $setting->icon_url;
    foreach (['mss-icon.png', 'logo-icon.png'] as $f) {
        if (!$headIcon && is_file(public_path('backend/assets/images/' . $f))) {
            $headIcon = asset('backend/assets/images/' . $f);
        }
    }
    $tiles = [
        ['Students', $counts['students'], 'bx bx-user'],
        ['Staff', $counts['staff'], 'bx bx-id-card'],
        ['Classes', $counts['classes'], 'bx bx-building'],
        ['Users', $counts['users'], 'bx bx-group'],
    ];
@endphp
{{-- old: <div class="card overflow-hidden"> --}}
<div class="card overflow-hidden ss-head-card">
    {{-- old: style="background: linear-gradient(135deg, primary, color-mix(in srgb, primary 60%, #000));" --}}
    <div class="ss-cover"></div>
    {{-- old: <div class="card-body pt-0"> <div class="d-flex flex-wrap align-items-end gap-3 ss-head"> (whole row was pulled up over the cover) --}}
    <div class="card-body pt-0">
        <div class="d-flex flex-wrap align-items-start gap-3">
            <div class="ss-icon bg-white shadow-sm d-flex align-items-center justify-content-center">
                @if($headIcon)
                <img src="{{ $headIcon }}" alt="School Icon" class="img-fluid">
                @else
                {{-- old: class="fw-bold fs-2 text-primary" --}}
                <span class="fw-bold fs-2 ss-icon-initial">{{ $initials }}</span>
                @endif
            </div>
            <div class="flex-grow-1 ss-name">
                <h4 class="mb-0 fw-semi-bold">{{ $setting->school_name }}</h4>
                <div class="text-muted">{{ $setting->tagline ?: 'Add a tagline in the School profile tab' }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2 ss-name">
                @foreach($tiles as [$label, $value, $icon])
                <div class="border rounded px-3 py-2 text-center ss-tile">
                    <div class="fw-bold fs-5 lh-1">{{ number_format($value) }}</div>
                    <small class="text-muted"><i class="{{ $icon }}"></i> {{ $label }}</small>
                </div>
                @endforeach
            </div>
        </div>

        <ul class="nav nav-tabs mt-3" id="siteSettingTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $tab === 'profile' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button" role="tab">School profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $tab === 'theme' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-theme" type="button" role="tab">Appearance &amp; theme</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $tab === 'checklist' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-checklist" type="button" role="tab">Setup checklist</button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content">
    {{-- ================= Tab 1: School profile ================= --}}
    <div class="tab-pane fade {{ $tab === 'profile' ? 'show active' : '' }}" id="tab-profile" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="fw-semi-bold text-primary mb-0">Site Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('site_setting.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="school_name" class="form-label">School Name <span class="text-danger">*</span></label>
                                    <input type="text" name="school_name" id="school_name"
                                        placeholder="e.g. Mount Carmel School"
                                        class="form-control form-control-sm @error('school_name') is-invalid @enderror"
                                        value="{{ old('school_name', $setting->school_name) }}">
                                    @error('school_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="registration_no" class="form-label">Registration / Affiliation No</label>
                                    <input type="text" name="registration_no" id="registration_no"
                                        placeholder="e.g. REG-2024-001"
                                        class="form-control form-control-sm @error('registration_no') is-invalid @enderror"
                                        value="{{ old('registration_no', $setting->registration_no) }}">
                                    @error('registration_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- 👇 NAYA FIELD YAHAN ADD KAREIN --}}
                                <div class="col-md-6">
                                    <label for="admission_prefix" class="form-label">Admission No. Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="admission_prefix" id="admission_prefix"
                                        placeholder="e.g. APS, SSS"
                                        maxlength="10"
                                        class="form-control form-control-sm @error('admission_prefix') is-invalid @enderror"
                                        value="{{ old('admission_prefix', $setting->admission_prefix) }}">
                                    <small class="text-muted">Used in student Admission Numbers, e.g. APS-2026-0001</small>
                                    @error('admission_prefix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" id="phone"
                                        placeholder="e.g. 0321-1234567"
                                        class="form-control form-control-sm @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $setting->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email"
                                        placeholder="e.g. info@school.edu.pk"
                                        class="form-control form-control-sm @error('email') is-invalid @enderror"
                                        value="{{ old('email', $setting->email) }}">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="text" name="website" id="website"
                                        placeholder="e.g. https://www.school.edu.pk"
                                        class="form-control form-control-sm @error('website') is-invalid @enderror"
                                        value="{{ old('website', $setting->website) }}">
                                    @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="established_year" class="form-label">Established Year</label>
                                    <input type="text" name="established_year" id="established_year"
                                        placeholder="e.g. 2010"
                                        class="form-control form-control-sm @error('established_year') is-invalid @enderror"
                                        value="{{ old('established_year', $setting->established_year) }}">
                                    @error('established_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" name="address" id="address"
                                        placeholder="e.g. 110 Kings Street, Lahore"
                                        class="form-control form-control-sm @error('address') is-invalid @enderror"
                                        value="{{ old('address', $setting->address) }}">
                                    @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- T340a: tagline (shown in the header card above) --}}
                                <div class="col-md-6">
                                    <label for="tagline" class="form-label">Tagline</label>
                                    <input type="text" name="tagline" id="tagline" maxlength="150"
                                        placeholder="e.g. Excellence in Education"
                                        class="form-control form-control-sm @error('tagline') is-invalid @enderror"
                                        value="{{ old('tagline', $setting->tagline) }}">
                                    @error('tagline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="header_note" class="form-label">Header Note</label>
                                    <input type="text" name="header_note" id="header_note"
                                        placeholder="Text shown at top of printed documents"
                                        class="form-control form-control-sm @error('header_note') is-invalid @enderror"
                                        value="{{ old('header_note', $setting->header_note) }}">
                                    @error('header_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="footer_note" class="form-label">Footer Note</label>
                                    <input type="text" name="footer_note" id="footer_note"
                                        placeholder="Text shown at bottom of printed documents"
                                        class="form-control form-control-sm @error('footer_note') is-invalid @enderror"
                                        value="{{ old('footer_note', $setting->footer_note) }}">
                                    @error('footer_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="logo" class="form-label">School Logo</label>
                                    <input type="file" name="logo" id="logo" accept="image/*"
                                        class="form-control form-control-sm @error('logo') is-invalid @enderror">
                                    <small class="text-muted d-block">Wide logo, recommended 800x300 PNG with transparent background (8:3). Used on documents. JPG, PNG or WebP — max 2MB</small>
                                    @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($setting->logo_url)
                                    <div><img src="{{ $setting->logo_url }}" class="mt-2" style="max-height:60px;" alt="Current Logo"></div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="icon" class="form-label">School Icon</label>
                                    <input type="file" name="icon" id="icon" accept="image/*"
                                        class="form-control form-control-sm @error('icon') is-invalid @enderror">
                                    <small class="text-muted d-block">Square icon, recommended 512x512 PNG with transparent background. Used in sidebar, role login pages and browser tab. JPG, PNG or WebP — max 2MB</small>
                                    @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($setting->icon_url)
                                    <div><img src="{{ $setting->icon_url }}" class="mt-2" style="max-height:60px;" alt="Current Icon"></div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="principal_signature" class="form-label">Principal Signature</label>
                                    <input type="file" name="principal_signature" id="principal_signature" accept="image/*"
                                        class="form-control form-control-sm @error('principal_signature') is-invalid @enderror">
                                    <small class="text-muted">JPG, PNG or WebP — max 2MB</small>
                                    @error('principal_signature')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($setting->principal_signature)
                                    <div><img src="{{ asset('uploads/site_setting/' . $setting->principal_signature) }}" class="mt-2" style="max-height:60px;" alt="Current Signature"></div>
                                    @endif
                                </div>
                            </div>

                            @can('manage-site-settings')
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-sm btn-success px-4">Update</button>
                            </div>
                            @endcan
                        </form>
                    </div>
                </div>
            </div>

            {{-- T340a: read-only "School at a glance" (existing data only) --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="fw-semi-bold text-primary mb-0">School at a glance</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $glance = [
                                ['Registration No', $setting->registration_no],
                                ['Established', $setting->established_year],
                                ['Admission Prefix', $setting->admission_prefix],
                                ['School Timing', $timing ? $timing->season_name . ' (' . \Carbon\Carbon::parse($timing->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($timing->end_time)->format('h:i A') . ')' : null],
                                ['Phone', $setting->phone],
                                ['Email', $setting->email],
                            ];
                        @endphp
                        <ul class="list-group list-group-flush">
                            @foreach($glance as [$label, $value])
                            <li class="list-group-item px-0 d-flex justify-content-between gap-3">
                                <span class="text-muted">{{ $label }}</span>
                                <span class="fw-semibold text-end text-break">{{ $value ?: '-' }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= Tab 2: Appearance & theme ================= --}}
    <div class="tab-pane fade {{ $tab === 'theme' ? 'show active' : '' }}" id="tab-theme" role="tabpanel">
        @include('admin.site_setting.partials.theme_tab')
    </div>

    {{-- ================= Tab 3: Setup checklist (placeholder, T340b) ================= --}}
    <div class="tab-pane fade {{ $tab === 'checklist' ? 'show active' : '' }}" id="tab-checklist" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="fw-semi-bold text-primary mb-0">Setup checklist</h5>
            </div>
            <div class="card-body text-muted">Coming in the next update.</div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* T340a: live theme vars (saved theme on load; the Appearance tab overrides them on click, Round 2 global partial uses the same names) */
    {{-- old (now set once by partials/site_theme):
    :root {
        --ss-primary: {{ $theme['primary'] }};
        --ss-accent: {{ $theme['accent'] }};
        --ss-on: {{ $theme['on_primary'] }};
        --ss-primary-rgb: {{ implode(',', sscanf(ltrim($theme['primary'], '#'), '%2x%2x%2x') ?: [0, 140, 255]) }};
        --ss-font: {!! $theme['font_stack'] !!};
    }
    --}}
    .ss-head-card { border-radius: 14px; overflow: hidden; }
    /* old: .ss-cover { height: 110px; } */
    .ss-cover { height: 110px; background: linear-gradient(135deg, var(--ss-primary) 0%, var(--ss-primary) 45%, var(--ss-accent) 100%); }
    /* old: .ss-icon-initial { color: var(--ss-primary); } */
    .ss-icon-initial { color: var(--ss-primary-text); }
    /* old: #siteSettingTabs .nav-link.active { color: var(--ss-primary); } */
    #siteSettingTabs .nav-link.active { color: var(--ss-primary-text); }
    /* old: .ss-head { margin-top: -40px; } */
    /* old: .ss-icon { width: 80px; height: 80px; border-radius: 12px; padding: 8px; overflow: hidden; border: 3px solid #fff; margin-top: -40px; } */
    .ss-icon { width: 80px; height: 80px; border-radius: 14px; padding: 8px; overflow: hidden; border: 5px solid #fff; background: #fff !important; box-shadow: 0 4px 12px rgb(0 0 0 / 18%) !important; margin-top: -40px; }
    .ss-name { margin-top: 12px; }
    .ss-tile { min-width: 84px; background: #fff; color: #4c5258; }
    .ss-tile .fs-5 { color: #32393f; }
    /* old: html.dark-theme .ss-icon { border-color: #12181a; background: #1b2326 !important; } */
    html.dark-theme .ss-icon { border-color: #fff; background: #fff !important; }
    html.dark-theme .ss-tile { background: #1b2326; color: #cfd3d6; border-color: rgb(255 255 255 / 14%) !important; }
    html.dark-theme .ss-tile .fs-5 { color: #fff; }
    html.dark-theme .ss-tile small { color: #a8adb0 !important; }
    html.dark-theme #siteSettingTabs .nav-link { color: #cfd3d6; background: transparent; }
    html.dark-theme #siteSettingTabs .nav-link:hover { color: #fff; }
    html.dark-theme #siteSettingTabs .nav-link.active { color: #fff; background-color: #12181a; border-color: rgb(255 255 255 / 14%) rgb(255 255 255 / 14%) #12181a; }
</style>
@endpush

@push('scripts')
<script>
    @if($errors->any())
    toastr.error("{{ $errors->first() }}");
    @endif
    @if(session('toastr-success'))
    toastr.success("{{ session('toastr-success') }}");
    @endif

    // Keep the open tab in the URL (?tab=profile|theme|checklist) so a refresh stays on it
    (function () {
        var names = { 'tab-profile': 'profile', 'tab-theme': 'theme', 'tab-checklist': 'checklist' };
        document.querySelectorAll('#siteSettingTabs [data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function () {
                var name = names[btn.getAttribute('data-bs-target').replace('#', '')];
                if (!name) { return; }
                var url = new URL(window.location.href);
                url.searchParams.set('tab', name);
                history.replaceState(null, '', url.toString());
            });
        });
    })();
</script>
@endpush
