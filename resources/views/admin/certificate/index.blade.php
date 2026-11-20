@extends($current_layout)
@section('title', 'Issued Certificates')
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Certificates', 'url' => '#'],
    ['label' => 'Issued Certificates', 'url' => route('certificate.index')],
]" />

{{-- Filters (GET, so the result page can be bookmarked / paginated with the same query) --}}
<div class="card radius-10">
    <div class="card-body">
        <form id="certificateFilterForm" method="GET" action="{{ route('certificate.index') }}" autocomplete="off">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label for="certificateFilterType" class="small fw-bold">Type</label>
                    <select id="certificateFilterType" name="type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        @foreach($types as $key => $t)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $t['title'] }}{{ $t['active'] ? '' : ' (inactive)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="certificateFilterStatus" class="small fw-bold">Status</label>
                    <select id="certificateFilterStatus" name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="certificateFilterClass" class="small fw-bold">Class</label>
                    <select id="certificateFilterClass" name="class_id" class="form-select form-select-sm">
                        <option value="">All classes</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ (string) request('class_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="certificateFilterFrom" class="small fw-bold">Date From</label>
                    <input type="date" id="certificateFilterFrom" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="certificateFilterTo" class="small fw-bold">Date To</label>
                    <input type="date" id="certificateFilterTo" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-lg-9 col-md-12">
                    <label for="certificateFilterSearch" class="small fw-bold">Search</label>
                    <input type="text" id="certificateFilterSearch" name="q" maxlength="100" class="form-control form-control-sm"
                        placeholder="e.g. student name, GR No. or certificate no." value="{{ request('q') }}">
                </div>
                <div class="col-lg-3 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm px-4">Filter</button>
                    <a href="{{ route('certificate.index') }}" class="btn btn-danger btn-sm px-4">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">Issued Certificates</h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary">Records: {{ $certificates->total() }}</span>
            @can('manage-certificates')
            <a href="{{ route('certificate.create') }}" class="btn btn-success btn-sm shadow-sm px-5">Issue Certificate</a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="certificateTable">
                <thead class="table-light">
                    <tr>
                        <th>Certificate No.</th>
                        <th>Type</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Issue Date</th>
                        <th>Issued By</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certificates as $certificate)
                    @php
                        $student = $certificate->student;
                        $enrollment = $certificate->enrollment;
                    @endphp
                    <tr class="certificate-row">
                        <td class="fw-bold">{{ $certificate->certificate_no }}</td>
                        <td>{{ isset($types[$certificate->type]) ? $types[$certificate->type]['title'] : $certificate->title }}</td>
                        <td>
                            {{ $student ? $student->full_name : '-' }}
                            <div class="small text-muted">GR No. {{ $student ? $student->admission_no : '-' }}</div>
                        </td>
                        <td>
                            @if($enrollment && $enrollment->schoolClass)
                            {{ $enrollment->schoolClass->name }}{{ $enrollment->section ? ' - ' . $enrollment->section->name : '' }}
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $certificate->issue_date->format('d-m-Y') }}</td>
                        <td>{{ $certificate->issuer ? $certificate->issuer->name : '-' }}</td>
                        <td>
                            @if($certificate->isCancelled())
                            <span class="badge bg-danger" title="{{ $certificate->cancel_reason }}">Cancelled</span>
                            <div class="small text-muted">{{ $certificate->cancelled_at ? $certificate->cancelled_at->format('d-m-Y') : '' }}</div>
                            @else
                            <span class="badge bg-success">Issued</span>
                            @endif
                            {{-- SLC jisne student ko School Left kiya --}}
                            {{-- old: @if(!empty(($certificate->fields ?: [])['status_change'])) --}}
                            @if(!$certificate->isCancelled() && !empty(($certificate->fields ?: [])['status_change']))
                            <div><span class="badge badge-school-left mt-1" title="This certificate marked the student School Left">Marked Left</span></div>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('certificate.print', $certificate->id) }}" target="_blank" class="btn btn-outline-primary btn-sm" title="Print" aria-label="Print">
                                <i class="bx bx-printer"></i>
                            </a>
                            @can('manage-certificates')
                            @if(!$certificate->isCancelled())
                            <button type="button" class="btn btn-outline-danger btn-sm certificate-cancel-btn" title="Cancel" aria-label="Cancel"
                                data-id="{{ $certificate->id }}" data-no="{{ $certificate->certificate_no }}"
                                data-restores="{{ !empty(($certificate->fields ?: [])['status_change']) ? 1 : 0 }}">
                                <i class="bx bx-x-circle"></i>
                            </button>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No certificates found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $certificates->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Cancel Swal in dark mode: validation strip, textarea and label were light on the dark popup */
    html.dark-theme .certificate-cancel-swal .swal2-validation-message { background: #1c2427; color: #f1f3f4; border-top: 1px solid rgb(255 255 255 / 16%); }
    html.dark-theme .certificate-cancel-swal .swal2-input-label { color: #e4e5e6; }
    html.dark-theme .certificate-cancel-swal .swal2-textarea { background: #12181a; color: #e4e5e6; border-color: rgb(255 255 255 / 25%); }
    html.dark-theme .certificate-cancel-swal .swal2-textarea::placeholder { color: #8a949b; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const CANCEL_URL = "{{ route('certificate.cancel', ':id') }}";

    // ---- cancel: Swal asks for a reason, JSON POST, reload on success ----
    $(document).on('click', '.certificate-cancel-btn', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const no = $('<div>').text($btn.data('no')).html();
        const restores = String($btn.data('restores')) === '1';

        Swal.fire({
            title: 'Cancel ' + no + '?',
            html: 'The certificate stays in the register as <b>Cancelled</b> and prints with a CANCELLED mark.'
                + (restores ? '<br><b>The student will be restored to their previous status.</b>' : ''),
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Reason *',
            inputPlaceholder: 'e.g. Wrong date of birth, reissued',
            inputAttributes: { maxlength: 255 },
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'Keep it',
            buttonsStyling: false,
            customClass: {
                // old: popup: 'fee-assignment-swal',
                popup: 'fee-assignment-swal certificate-cancel-swal',
                confirmButton: 'btn btn-danger btn-sm',
                cancelButton: 'btn btn-secondary btn-sm'
            },
            inputValidator: function(value) {
                if (!$.trim(value)) { return 'Cancel reason is required'; }
            }
        }).then(function(result) {
            if (!result.isConfirmed) { return; }
            $btn.prop('disabled', true);
            $.ajax({
                url: CANCEL_URL.replace(':id', id),
                type: 'POST',
                data: { cancel_reason: $.trim(result.value) }
            }).done(function(res) {
                toastr.clear();
                toastr.success(res.message);
                setTimeout(function() { location.reload(); }, 1500);
            }).fail(function(xhr) {
                $btn.prop('disabled', false);
                const j = xhr.responseJSON || {};
                let msg = j.message || 'Could not cancel the certificate.';
                if (j.errors && j.errors.cancel_reason) { msg = j.errors.cancel_reason[0]; }
                toastr.clear();
                toastr.error(msg);
                if (xhr.status === 422 && j.message) { setTimeout(function() { location.reload(); }, 2000); } // already cancelled: refresh the row
            });
        });
    });

    // ---- server errors / flash ----
    @if($errors->any())
    toastr.error(@json($errors->first()));
    @endif
    @if(Session::has('error')) toastr.error(@json(Session::get('error'))); @endif
    @if(Session::has('success')) toastr.success(@json(Session::get('success'))); @endif
});
</script>
@endpush
