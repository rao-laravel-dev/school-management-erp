{{-- Failed form ki modal id (_modal) match ho to hi old() / errors us modal mein dikhte hain, baqi modals saaf rehte hain --}}
@php
    $ctModal = old('_modal');
    $ctCls = function ($field, $modalId) use ($errors, $ctModal) {
        return ($ctModal === $modalId && $errors->has($field)) ? 'is-invalid' : '';
    };
    $ctMsg = function ($field, $modalId) use ($errors, $ctModal) {
        return $ctModal === $modalId ? $errors->first($field) : '';
    };
    $ctVal = function ($field, $default, $modalId) use ($ctModal) {
        return $ctModal === $modalId ? old($field, $default) : $default;
    };
@endphp

<!-- Add Complaint-type Modal -->
<div class="modal fade" id="complaintTypeAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.complaint-type.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="complaintTypeAdd">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="name">Complaint Type <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ $ctVal('name', '', 'complaintTypeAdd') }}" class="form-control form-control-sm {{ $ctCls('name', 'complaintTypeAdd') }}" placeholder="Enter Complaint Type">
                            <div class="invalid-feedback">{{ $ctMsg('name', 'complaintTypeAdd') }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="description">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control form-control-sm {{ $ctCls('description', 'complaintTypeAdd') }}" placeholder="Complaint Description Here...">{{ $ctVal('description', '', 'complaintTypeAdd') }}</textarea>
                            <div class="invalid-feedback">{{ $ctMsg('description', 'complaintTypeAdd') }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($complaintTypes as $type)
@php $tm = 'complaintTypeEdit-' . $type->id; @endphp
<div class="modal fade" id="{{ $tm }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Complaint Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.complaint-type.update', $type->id) }}" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="{{ $tm }}">
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="name-{{ $type->id }}">Complaint Type <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name-{{ $type->id }}" value="{{ $ctVal('name', $type->name, $tm) }}" class="form-control form-control-sm {{ $ctCls('name', $tm) }}">
                            <div class="invalid-feedback">{{ $ctMsg('name', $tm) }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="desc-{{ $type->id }}">Description</label>
                            <textarea name="description" rows="4" id="desc-{{ $type->id }}" class="form-control form-control-sm {{ $ctCls('description', $tm) }}">{{ $ctVal('description', $type->description, $tm) }}</textarea>
                            <div class="invalid-feedback">{{ $ctMsg('description', $tm) }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
