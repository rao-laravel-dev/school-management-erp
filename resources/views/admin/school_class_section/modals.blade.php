{{-- ADD MAPPING MODAL (Success Theme) --}}
<div class="modal fade" id="addMappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('school_class_section.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bx bx-plus-circle"></i> Assign New Sections</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Class</label>
                        <select name="school_class_id" class="form-select form-select-sm" required>
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold">Select Sections</label>
                        <div class="border p-2" style="height: 180px; overflow-y: scroll; background: #fff;">
                            @foreach($sections as $sec)
                            @php
                                $secColor = match(strtoupper($sec->name)) {
                                    'A' => 'text-success',
                                    'B' => 'text-warning',
                                    'C' => 'text-danger',
                                    default => '',
                                };
                            @endphp
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="section_ids[]" value="{{ $sec->id }}" id="sec{{ $sec->id }}">
                                <label class="form-check-label small fw-bold {{ $secColor }}" for="sec{{ $sec->id }}">{{ $sec->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-save"></i> Save Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MAPPING MODALS (per row, Info Theme) --}}
@foreach($mapped_classes as $row)
<div class="modal fade" id="editModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('school_class_section.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bx bx-edit"></i> Edit Mapping: {{ $row->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Class Name</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $row->name }}" disabled>
                    </div>
                    <div class="mb-1">
                        <label class="form-label small fw-bold">Select Sections</label>
                        <div class="border p-2" style="height: 180px; overflow-y: scroll; background: #fff;">
                            @foreach($sections as $sec)
                            @php
                                $secColor = match(strtoupper($sec->name)) {
                                    'A' => 'text-success',
                                    'B' => 'text-warning',
                                    'C' => 'text-danger',
                                    default => '',
                                };
                            @endphp
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="section_ids[]" value="{{ $sec->id }}"
                                    {{ $row->mappedSections->contains($sec->id) ? 'checked' : '' }}
                                    id="editSec{{ $row->id }}_{{ $sec->id }}">
                                <label class="form-check-label small fw-bold {{ $secColor }}" for="editSec{{ $row->id }}_{{ $sec->id }}">{{ $sec->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white"><i class="bx bx-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach