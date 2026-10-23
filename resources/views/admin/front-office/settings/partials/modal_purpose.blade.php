{{-- Failed form ki modal id (_modal) match ho to hi old() / errors us modal mein dikhte hain, baqi modals saaf rehte hain --}}
@php
    $prpModal = old('_modal');
    $prpCls = function ($field, $modalId) use ($errors, $prpModal) {
        return ($prpModal === $modalId && $errors->has($field)) ? 'is-invalid' : '';
    };
    $prpMsg = function ($field, $modalId) use ($errors, $prpModal) {
        return $prpModal === $modalId ? $errors->first($field) : '';
    };
    $prpVal = function ($field, $default, $modalId) use ($prpModal) {
        return $prpModal === $modalId ? old($field, $default) : $default;
    };
@endphp

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
                action="{{ route('settings.purpose.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="purposeAdd">

                <div class="modal-body">

                    <div class="row g-4">

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="purpose">
                                Purpose <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="purpose" id="purpose" value="{{ $prpVal('purpose', '', 'purposeAdd') }}" class="form-control form-control-sm {{ $prpCls('purpose', 'purposeAdd') }}" placeholder="Enter Purpose">
                            <div class="invalid-feedback">{{ $prpMsg('purpose', 'purposeAdd') }}</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="description">
                                Description
                            </label>

                            <textarea
                                name="description" rows="4" id="description" class="form-control form-control-sm {{ $prpCls('description', 'purposeAdd') }}" placeholder="Enter Description Here">{{ $prpVal('description', '', 'purposeAdd') }}</textarea>
                            <div class="invalid-feedback">{{ $prpMsg('description', 'purposeAdd') }}</div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-danger btn-sm"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bx bx-save me-1"></i>
                        Save Purpose
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

@foreach($purposes as $Editpurpose)
@php $pm = 'purposeEdit-' . $Editpurpose->id; @endphp

<div class="modal fade"
    id="{{ $pm }}"
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
                action="{{ route('settings.purpose.update',$Editpurpose->id) }}" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="{{ $pm }}">

                <div class="modal-body">

                    <div class="row g-4">

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="name-{{ $Editpurpose->id }}">
                                Purpose <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                name="purpose" id="name-{{ $Editpurpose->id }}" value="{{ $prpVal('purpose', $Editpurpose->name, $pm) }}" class="form-control form-control-sm {{ $prpCls('purpose', $pm) }}">
                            <div class="invalid-feedback">{{ $prpMsg('purpose', $pm) }}</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="desc-{{ $Editpurpose->id }}">
                                Description
                            </label>

                            <textarea
                                name="description"
                                rows="4" id="desc-{{ $Editpurpose->id }}" class="form-control form-control-sm {{ $prpCls('description', $pm) }}">{{ $prpVal('description', $Editpurpose->description, $pm) }}</textarea>
                            <div class="invalid-feedback">{{ $prpMsg('description', $pm) }}</div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-danger btn-sm"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                        class="btn btn-success btn-sm">
                        <i class="bx bx-save me-1"></i>
                        Update Purpose
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endforeach
