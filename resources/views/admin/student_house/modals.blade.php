{{-- Add House Modal --}}
<div class="modal fade" id="houseAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student-house.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title">Add House</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label>Color</label>
                        <input type="color" name="color"
                            class="form-control form-control-color form-control-sm @error('color') is-invalid @enderror"
                            value="{{ old('color', '#000000') }}">
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit House Modals (one per row) --}}
@foreach($houses as $house)
<div class="modal fade" id="houseEdit-{{ $house->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student-house.update', $house->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit House</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $house->name }}">
                    </div>
                    <div class="form-group mb-3">
                        <label>Color</label>
                        <input type="color" name="color"
                            class="form-control form-control-color form-control-sm"
                            value="{{ $house->color ?? '#000000' }}">
                    </div>
                    <div class="form-group mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="1" {{ $house->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $house->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach