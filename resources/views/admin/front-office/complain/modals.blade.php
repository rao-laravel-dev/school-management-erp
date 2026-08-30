{{-- Add Modal --}}
<div class="modal fade" id="complainAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Complaint</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('complain.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Row 1: 3 fields --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="complaint_type_id">Complaint Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('complaint_type_id') is-invalid @enderror" name="complaint_type_id" id="complaint_type_id">
                                <option disabled selected>-- Select --</option>
                                @foreach($allComplaints as $allComplaint)
                                <option value="{{ $allComplaint->id }}" {{ old('complaint_type_id') == $allComplaint->id ? 'selected' : '' }}>{{ $allComplaint->name }}</option>
                                @endforeach
                            </select>
                            @error('complaint_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="source_id">Source</label>
                            <select class="form-control @error('source_id') is-invalid @enderror" name="source_id" id="source_id">
                                <option disabled selected>-- Select --</option>
                                @foreach($allSources as $allSource)
                                <option value="{{ $allSource->id }}" {{ old('source_id') == $allSource->id ? 'selected' : '' }}>{{ $allSource->source ?? $allSource->name }}</option>
                                @endforeach
                            </select>
                            @error('source_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="date">Date</label>
                            <input type="text" name="date" id="date" value="{{ \Carbon\Carbon::now()->format('d-M-Y') }}" class="form-control @error('date') is-invalid @enderror" readonly>
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Row 2: 2 fields --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="complaint_by">Complain By <span class="text-danger">*</span></label>
                            <input type="text" name="complaint_by" id="complaint_by" value="{{ old('complaint_by') }}" class="form-control @error('complaint_by') is-invalid @enderror">
                            @error('complaint_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone">Phone</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="eg: 03123456789">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Row 3: email + assigned_to --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="email">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="assigned_to">Assign To Staff</label>
                            <select class="form-control @error('assigned_to') is-invalid @enderror" name="assigned_to" id="assigned_to">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ old('assigned_to') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Row 4: document upload --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="document">Attachment (if any)</label>
                            <input type="file" name="document" id="document" class="form-control @error('document') is-invalid @enderror">
                            @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Row 5: description --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="description">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white"><i class="bx bx-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($complains as $complain)
<div class="modal fade" id="{{ 'complainEdit-'.$complain->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('complain.update', $complain->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Complaint Type</label>
                            <select class="form-control @error('complaint_type_id') is-invalid @enderror" name="complaint_type_id">
                                <option disabled selected>-- Select --</option>
                                @foreach($allComplaints as $allComplaint)
                                <option value="{{ $allComplaint->id }}" {{ $allComplaint->id == $complain->complaint_type_id ? 'selected' : '' }}>{{ $allComplaint->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Source</label>
                            <select class="form-control @error('source_id') is-invalid @enderror" name="source_id">
                                <option disabled selected>-- Select --</option>
                                @foreach($allSources as $allSource)
                                <option value="{{ $allSource->id }}" {{ $allSource->id == $complain->source_id ? 'selected' : '' }}>{{ $allSource->source ?? $allSource->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="text" name="date" value="{{ $complain->date }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Complain By</label>
                            <input type="text" name="complaint_by" value="{{ old('complaint_by', $complain->complaint_by) }}" class="form-control @error('complaint_by') is-invalid @enderror">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $complain->phone) }}" class="form-control @error('phone') is-invalid @enderror" placeholder="eg: 03123456789">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email', $complain->email) }}" class="form-control @error('email') is-invalid @enderror">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign To Staff</label>
                            <select class="form-control @error('assigned_to') is-invalid @enderror" name="assigned_to">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $staff->id == $complain->assigned_to ? 'selected' : '' }}>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Attachment</label>
                            @if($complain->document)
                            <div class="mb-2">
                                <a href="{{ asset('storage/'.$complain->document) }}" target="_blank" class="text-primary">
                                    <i class="bx bx-file"></i> Current File Dekhein
                                </a>
                            </div>
                            @endif
                            <input type="file" name="document" class="form-control @error('document') is-invalid @enderror">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $complain->description) }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Action Taken</label>
                            <textarea name="action_taken" rows="2" class="form-control @error('action_taken') is-invalid @enderror">{{ old('action_taken', $complain->action_taken) }}</textarea>
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