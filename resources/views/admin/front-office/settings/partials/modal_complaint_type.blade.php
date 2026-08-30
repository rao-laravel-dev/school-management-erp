
<!-- Add Complaint-type Modal -->
<div class="modal fade" id="complaintAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.complaint-type.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="name">Complaint Type <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Complaint Type">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="description">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control" placeholder="Complaint Description Here...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($complaintTypes as $type)
<div class="modal fade" id="complaintEdit-{{ $type->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Complaint Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('settings.complaint-type.update', $type->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="name-{{ $type->id }}">Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name-{{ $type->id }}" value="{{ old('name', $type->name) }}" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="desc-{{ $type->id }}">Description</label>
                            <textarea name="description" rows="4" id="desc-{{ $type->id }}" class="form-control">{{ old('description', $type->description) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white"><i class="bx bx-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach