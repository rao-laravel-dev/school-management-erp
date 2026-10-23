{{-- Failed form ki modal id (_modal) match ho to hi old() / errors us modal mein dikhte hain, baqi modals saaf rehte hain --}}
@php
    $srcModal = old('_modal');
    $srcCls = function ($field, $modalId) use ($errors, $srcModal) {
        return ($srcModal === $modalId && $errors->has($field)) ? 'is-invalid' : '';
    };
    $srcMsg = function ($field, $modalId) use ($errors, $srcModal) {
        return $srcModal === $modalId ? $errors->first($field) : '';
    };
    $srcVal = function ($field, $default, $modalId) use ($srcModal) {
        return $srcModal === $modalId ? old($field, $default) : $default;
    };
@endphp

{{-- Add Source Modal --}}
<div class="modal fade" id="sourceAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Source</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('settings.source.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="sourceAdd">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="source">Source Name <span class="text-danger">*</span></label>
                        <input type="text" name="source" id="source" class="form-control form-control-sm {{ $srcCls('source', 'sourceAdd') }}" value="{{ $srcVal('source', '', 'sourceAdd') }}" placeholder="Enter Source Name">
                        <div class="invalid-feedback">{{ $srcMsg('source', 'sourceAdd') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="description">Description</label>
                        <textarea name="description" id="description" class="form-control form-control-sm {{ $srcCls('description', 'sourceAdd') }}" rows="3" placeholder="Enter Description">{{ $srcVal('description', '', 'sourceAdd') }}</textarea>
                        <div class="invalid-feedback">{{ $srcMsg('description', 'sourceAdd') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save me-1"></i> Save Source</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Source Modals --}}
@foreach($sources as $EditSource)
@php $sm = 'sourceEdit-' . $EditSource->id; @endphp
<div class="modal fade" id="{{ $sm }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('settings.source.update', $EditSource->id) }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="{{ $sm }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="source-{{ $EditSource->id }}">Source Name <span class="text-danger">*</span></label>
                        <input type="text" name="source" id="source-{{ $EditSource->id }}" class="form-control form-control-sm {{ $srcCls('source', $sm) }}" value="{{ $srcVal('source', ucwords($EditSource->name), $sm) }}">
                        <div class="invalid-feedback">{{ $srcMsg('source', $sm) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="desc-{{ $EditSource->id }}">Description</label>
                        <textarea name="description" id="desc-{{ $EditSource->id }}" class="form-control form-control-sm {{ $srcCls('description', $sm) }}" rows="3">{{ $srcVal('description', ucfirst($EditSource->description), $sm) }}</textarea>
                        <div class="invalid-feedback">{{ $srcMsg('description', $sm) }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save me-1"></i> Update Source</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
