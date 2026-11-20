@extends($current_layout)
@section('title', 'Certificate Types')
@section('content')

<x-breadcrumb :items="[
    ['label' => 'Certificates', 'url' => '#'],
    ['label' => 'Certificate Types', 'url' => route('certificate.types')],
]" />

<div class="card radius-10">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
        <h5 class="mb-0 text-primary">Certificate Types</h5>
        <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#typeAdd">Add Custom Type</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="certificateTypeTable">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Prefix</th>
                        <th>Kind</th>
                        <th>Fields</th>
                        <th>Issued</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($types as $key => $t)
                    <tr class="certificate-type-row">
                        <td class="fw-bold">{{ $t['title'] }}</td>
                        <td>{{ $t['prefix'] }}</td>
                        <td>
                            @if($t['custom'])<span class="badge bg-info text-dark">Custom</span>
                            @else<span class="badge bg-secondary">Built-in</span>@endif
                            @if($t['edited'])<span class="badge bg-warning text-dark">Edited</span>@endif
                        </td>
                        <td>
                            @forelse($t['fields'] as $fk)
                            <span class="badge bg-light text-dark border">{{ $fieldMeta[$fk][0] }}{{ in_array($fk, $t['required'], true) ? ' *' : '' }}</span>
                            @empty
                            <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td>{{ isset($issuedCounts[$key]) ? $issuedCounts[$key] : 0 }}</td>
                        <td>
                            @if($t['active'])<span class="badge bg-success">Active</span>
                            @else<span class="badge bg-danger">Inactive</span>@endif
                        </td>
                        <td class="text-nowrap">
                            <button type="button" class="btn btn-outline-warning btn-sm" title="Edit" aria-label="Edit" data-bs-toggle="modal" data-bs-target="#typeEdit-{{ $key }}">
                                <i class="bx bx-edit"></i>
                            </button>
                            @if($t['edited'])
                            <form method="POST" action="{{ route('certificate.types.reset', $key) }}" class="d-inline certificate-type-reset-form">
                                @csrf
                                <button type="button" class="btn btn-outline-danger btn-sm certificate-type-reset-btn" title="Reset to default wording" aria-label="Reset to default wording">
                                    <i class="bx bx-reset"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Custom Type modal --}}
<div class="modal fade" id="typeAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('certificate.types.save') }}" class="modal-content certificate-type-form" novalidate autocomplete="off">
            @csrf
            <input type="hidden" name="_modal" value="typeAdd">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">Add Custom Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.certificate.partials.type_modal', ['p' => 'add', 't' => null, 'f' => old('_modal') === 'typeAdd', 'fieldMeta' => $fieldMeta])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Type modals (one per row) --}}
@foreach($types as $key => $t)
<div class="modal fade" id="typeEdit-{{ $key }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('certificate.types.save') }}" class="modal-content certificate-type-form" novalidate autocomplete="off">
            @csrf
            <input type="hidden" name="_modal" value="typeEdit-{{ $key }}">
            <input type="hidden" name="key" value="{{ $key }}">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit {{ $t['title'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.certificate.partials.type_modal', ['p' => 'edit_' . $key, 't' => $t, 'f' => old('_modal') === 'typeEdit-' . $key, 'fieldMeta' => $fieldMeta])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
/* Dark mode: Show/Required checkboxes and Active switch (this page only) */
html.dark-theme .certificate-type-fields .form-check-input,
html.dark-theme .certificate-type-form .form-check-input {
    background-color: transparent;
    border: 1px solid rgba(255, 255, 255, 0.35);
}
html.dark-theme .certificate-type-fields .form-check-input:checked,
html.dark-theme .certificate-type-form .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
html.dark-theme .certificate-type-form .form-check-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // ---- Required implies Show; unticking Show unticks Required ----
    $(document).on('change', '.certificate-type-req', function() {
        if (this.checked) { $(this).closest('tr').find('.certificate-type-show').prop('checked', true); }
    });
    $(document).on('change', '.certificate-type-show', function() {
        if (!this.checked) { $(this).closest('tr').find('.certificate-type-req').prop('checked', false); }
    });

    // ---- submit check (UX only, the server validates again) ----
    function setFieldError($el, msg) {
        $el.addClass('is-invalid');
        $el.siblings('.invalid-feedback').text(msg);
    }
    $(document).on('submit', '.certificate-type-form', function(e) {
        const $form = $(this);
        const missing = [];
        $form.find('[data-required="1"]').each(function() {
            if (!$.trim($(this).val())) { missing.push({ $el: $(this), msg: $(this).data('label') + ' is required' }); }
        });
        if (missing.length) {
            e.preventDefault();
            missing.forEach(function(m) { setFieldError(m.$el, m.msg); });
            toastr.clear();
            toastr.error(missing[0].msg);
            missing[0].$el.trigger('focus');
        }
    });
    $(document).on('input change', '.certificate-type-form .is-invalid', function() { $(this).removeClass('is-invalid'); });
    // Modal band ho to uske errors saaf (har modal ka apna form hai)
    // $(document).on('hidden.bs.modal', '.modal', function() { $(this).find('.is-invalid').removeClass('is-invalid'); });
    // Modal band ho to form reset: Add khali, Edit apni saved values par wapas; errors saaf
    $(document).on('hidden.bs.modal', '.modal', function() {
        const $m = $(this);
        const $form = $m.find('.certificate-type-form').addBack('.certificate-type-form');
        if ($form.length) {
            $form[0].reset(); // restores the values rendered in the page (saved values for Edit)
            if ($m.is('#typeAdd')) {
                $form.find('input[type="text"], textarea').val('');
                $form.find('.certificate-type-show, .certificate-type-req').prop('checked', false);
                $form.find('input[name="active"][type="checkbox"]').prop('checked', true);
            }
            $form.find('.invalid-feedback').text('');
            $form.find('.text-danger.small').remove();
        }
        $m.find('.is-invalid').removeClass('is-invalid');
    });

    // ---- reset to default: confirm, then POST ----
    $(document).on('click', '.certificate-type-reset-btn', function() {
        const $form = $(this).closest('form');
        Swal.fire({
            title: 'Reset to default?',
            text: 'The title, wording and status of this type go back to the built-in values. Issued certificates are not changed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset it'
        }).then(function(result) {
            if (result.isConfirmed) { $form.trigger('submit'); }
        });
    });

    // ---- server errors / flash ----
    @if($errors->any())
    var errModal = document.getElementById(@json(old('_modal', 'typeAdd')));
    if (errModal) { new bootstrap.Modal(errModal).show(); }
    toastr.error(@json($errors->first()));
    @endif
    @if(Session::has('error')) toastr.error(@json(Session::get('error'))); @endif
    @if(Session::has('success')) toastr.success(@json(Session::get('success'))); @endif
});
</script>
@endpush
