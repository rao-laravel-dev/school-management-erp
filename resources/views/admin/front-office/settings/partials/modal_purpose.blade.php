<!-- Add Purpose Modal -->
<div class="modal fade" id="purposeAdd" tabindex="-1" aria-labelledby="purposeAddLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="purposeAddLabel">
                    <i class="bx bx-edit me-2"></i>Add Purpose
                </h5>

                <button type="button" class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>
            </div>

            <form method="POST"
                action="{{ route('settings.purpose.store') }}">
                @csrf

                <div class="modal-body">

                    <div class="row g-4">

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="purpose">
                                Purpose <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}" class="form-control @error('purpose') is-invalid @enderror" placeholder="Enter Purpose">

                            @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="description">
                                Description
                            </label>

                            <textarea
                                name="description" rows="4" id="description" class="form-control @error('description') is-invalid @enderror" placeholder="Enter Description Here">{{ old('description') }}</textarea>

                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-danger"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-1"></i>
                        Save Purpose
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@foreach($purposes as $Editpurpose)

<div class="modal fade"
    id="purposeEdit-{{ $Editpurpose->id }}"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bx bx-edit me-2"></i>
                    Edit Purpose
                </h5>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>

            <form method="POST"
                action="{{ route('settings.purpose.update',$Editpurpose->id) }}">
                @csrf

                <div class="modal-body">

                    <div class="row g-4">

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="name-{{ $Editpurpose->id }}">
                                Purpose <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                name="purpose" id="name-{{ $Editpurpose->id }}" value="{{ old('purpose',$Editpurpose->name) }}" class="form-control @error('purpose') is-invalid @enderror">

                            @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="desc-{{ $Editpurpose->id }}">
                                Description
                            </label>

                            <textarea
                                name="description"
                                rows="4" id="desc-{{ $Editpurpose->id }}" class="form-control @error('description') is-invalid @enderror">{{ old('description',$Editpurpose->description) }}</textarea>

                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-danger"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                        class="btn btn-warning text-white">
                        <i class="bx bx-save me-1"></i>
                        Update Purpose
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endforeach