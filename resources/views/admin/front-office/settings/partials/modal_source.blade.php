{{-- Add Source Modal --}}
<div class="modal fade" id="sourceAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Source</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('settings.source.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="source">Source Name <span class="text-danger">*</span></label>
                        <input type="text" name="source" id="source" class="form-control" placeholder="Enter Source Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter Description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Source</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Source Modals --}}
@foreach($sources as $EditSource)
<div class="modal fade" id="sourceEdit-{{ $EditSource->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('settings.source.update', $EditSource->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="source-{{ $EditSource->id }}">Source Name <span class="text-danger">*</span></label>
                        <input type="text" name="source" id="source-{{ $EditSource->id }}" class="form-control" value="{{ old('source', ucwords($EditSource->name)) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="desc-{{ $EditSource->id }}">Description</label>
                        <textarea name="description" id="desc-{{ $EditSource->id }}" class="form-control" rows="3">{{ old('description', ucfirst($EditSource->description)) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white">Update Source</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach