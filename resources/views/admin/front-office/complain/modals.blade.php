{{-- Failed form ki modal id (_modal) match ho to hi old() / errors us modal mein dikhte hain, baqi modals saaf rehte hain --}}
@php
    $cmpModal = old('_modal');
    $cmpCls = function ($field, $modalId) use ($errors, $cmpModal) {
        return ($cmpModal === $modalId && $errors->has($field)) ? 'is-invalid' : '';
    };
    $cmpMsg = function ($field, $modalId) use ($errors, $cmpModal) {
        return $cmpModal === $modalId ? $errors->first($field) : '';
    };
    $cmpVal = function ($field, $default, $modalId) use ($cmpModal) {
        return $cmpModal === $modalId ? old($field, $default) : $default;
    };
@endphp

{{-- Add Modal --}}
<div class="modal fade" id="complainAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Complaint</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('complain.store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="complainAdd">
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Row 1: 3 fields --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="add_complaint_type_id">Complaint Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm {{ $cmpCls('complaint_type_id', 'complainAdd') }}" name="complaint_type_id" id="add_complaint_type_id">
                                <option value="">-- Select --</option>
                                @foreach($allComplaints as $allComplaint)
                                <option value="{{ $allComplaint->id }}" {{ $cmpVal('complaint_type_id', '', 'complainAdd') == $allComplaint->id ? 'selected' : '' }}>{{ ucwords($allComplaint->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('complaint_type_id', 'complainAdd') }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="add_source_id">Source <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm {{ $cmpCls('source_id', 'complainAdd') }}" name="source_id" id="add_source_id">
                                <option value="">-- Select --</option>
                                @foreach($allSources as $allSource)
                                <option value="{{ $allSource->id }}" {{ $cmpVal('source_id', '', 'complainAdd') == $allSource->id ? 'selected' : '' }}>{{ ucwords($allSource->source ?? $allSource->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('source_id', 'complainAdd') }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="add_date">Date <span class="text-danger">*</span></label>
                            <input type="text" name="date" id="add_date" value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}" class="form-control form-control-sm {{ $cmpCls('date', 'complainAdd') }}" readonly>
                            <div class="invalid-feedback">{{ $cmpMsg('date', 'complainAdd') }}</div>
                        </div>

                        {{-- Row 2: 2 fields --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_complaint_by">Complain By <span class="text-danger">*</span></label>
                            <input type="text" name="complaint_by" id="add_complaint_by" value="{{ $cmpVal('complaint_by', '', 'complainAdd') }}" placeholder="Enter name" class="form-control form-control-sm {{ $cmpCls('complaint_by', 'complainAdd') }}">
                            <div class="invalid-feedback">{{ $cmpMsg('complaint_by', 'complainAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_phone">Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="add_phone" value="{{ $cmpVal('phone', '', 'complainAdd') }}" class="form-control form-control-sm {{ $cmpCls('phone', 'complainAdd') }}" placeholder="eg: 0300-1234567">
                            <div class="invalid-feedback">{{ $cmpMsg('phone', 'complainAdd') }}</div>
                        </div>

                        {{-- Row 3: email + assigned_to --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_email">Email</label>
                            <input type="email" name="email" id="add_email" value="{{ $cmpVal('email', '', 'complainAdd') }}" placeholder="eg: name@example.com" class="form-control form-control-sm {{ $cmpCls('email', 'complainAdd') }}">
                            <div class="invalid-feedback">{{ $cmpMsg('email', 'complainAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_assigned_to">Assign To Staff</label>
                            <select class="form-select form-select-sm {{ $cmpCls('assigned_to', 'complainAdd') }}" name="assigned_to" id="add_assigned_to">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $cmpVal('assigned_to', '', 'complainAdd') == $staff->id ? 'selected' : '' }}>{{ $staff->name_with_role }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('assigned_to', 'complainAdd') }}</div>
                        </div>

                        {{-- Row 4: document upload --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="add_document">Attachment (if any)</label>
                            <input type="file" name="document" id="add_document" class="form-control form-control-sm {{ $cmpCls('document', 'complainAdd') }}">
                            <div class="invalid-feedback">{{ $cmpMsg('document', 'complainAdd') }}</div>
                        </div>

                        {{-- Row 5: description --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="add_description">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="add_description" rows="3" placeholder="Enter description" class="form-control form-control-sm {{ $cmpCls('description', 'complainAdd') }}">{{ $cmpVal('description', '', 'complainAdd') }}</textarea>
                            <div class="invalid-feedback">{{ $cmpMsg('description', 'complainAdd') }}</div>
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

{{-- Edit Modals --}}
@foreach($complains as $complain)
@php $cm = 'complainEdit-' . $complain->id; @endphp
<div class="modal fade" id="{{ $cm }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('complain.update', $complain->id) }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="{{ $cm }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_complaint_type_id_{{ $complain->id }}">Complaint Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm {{ $cmpCls('complaint_type_id', $cm) }}" name="complaint_type_id" id="edit_complaint_type_id_{{ $complain->id }}">
                                <option value="">-- Select --</option>
                                @foreach($allComplaints as $allComplaint)
                                <option value="{{ $allComplaint->id }}" {{ $cmpVal('complaint_type_id', $complain->complaint_type_id, $cm) == $allComplaint->id ? 'selected' : '' }}>{{ ucwords($allComplaint->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('complaint_type_id', $cm) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_source_id_{{ $complain->id }}">Source <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm {{ $cmpCls('source_id', $cm) }}" name="source_id" id="edit_source_id_{{ $complain->id }}">
                                <option value="">-- Select --</option>
                                @foreach($allSources as $allSource)
                                <option value="{{ $allSource->id }}" {{ $cmpVal('source_id', $complain->source_id, $cm) == $allSource->id ? 'selected' : '' }}>{{ ucwords($allSource->source ?? $allSource->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('source_id', $cm) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_date_{{ $complain->id }}">Date <span class="text-danger">*</span></label>
                            <input type="text" name="date" id="edit_date_{{ $complain->id }}" value="{{ \Carbon\Carbon::parse($complain->date)->format('d-m-Y') }}" class="form-control form-control-sm {{ $cmpCls('date', $cm) }}" readonly>
                            <div class="invalid-feedback">{{ $cmpMsg('date', $cm) }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_complaint_by_{{ $complain->id }}">Complain By <span class="text-danger">*</span></label>
                            <input type="text" name="complaint_by" id="edit_complaint_by_{{ $complain->id }}" value="{{ $cmpVal('complaint_by', $complain->complaint_by, $cm) }}" placeholder="Enter name" class="form-control form-control-sm {{ $cmpCls('complaint_by', $cm) }}">
                            <div class="invalid-feedback">{{ $cmpMsg('complaint_by', $cm) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_phone_{{ $complain->id }}">Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="edit_phone_{{ $complain->id }}" value="{{ $cmpVal('phone', $complain->phone, $cm) }}" class="form-control form-control-sm {{ $cmpCls('phone', $cm) }}" placeholder="eg: 0300-1234567">
                            <div class="invalid-feedback">{{ $cmpMsg('phone', $cm) }}</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_email_{{ $complain->id }}">Email</label>
                            <input type="email" name="email" id="edit_email_{{ $complain->id }}" value="{{ $cmpVal('email', $complain->email, $cm) }}" placeholder="eg: name@example.com" class="form-control form-control-sm {{ $cmpCls('email', $cm) }}">
                            <div class="invalid-feedback">{{ $cmpMsg('email', $cm) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_assigned_to_{{ $complain->id }}">Assign To Staff</label>
                            @php $cmpSavedStaff = $cmpVal('assigned_to', $complain->assigned_to, $cm); @endphp
                            <select class="form-select form-select-sm {{ $cmpCls('assigned_to', $cm) }}" name="assigned_to" id="edit_assigned_to_{{ $complain->id }}">
                                <option value="">-- Select Staff --</option>
                                {{-- Saved staff ab inactive/trashed ho to bhi list mein rahe --}}
                                @if($complain->assigned_to && $complain->staff && !$staffs->contains('id', $complain->assigned_to))
                                <option value="{{ $complain->assigned_to }}" {{ $cmpSavedStaff == $complain->assigned_to ? 'selected' : '' }}>{{ $complain->staff->name_with_role }}</option>
                                @endif
                                @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $cmpSavedStaff == $staff->id ? 'selected' : '' }}>{{ $staff->name_with_role }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('assigned_to', $cm) }}</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="edit_document_{{ $complain->id }}">Attachment</label>
                            @if($complain->document)
                            <div class="mb-2">
                                <a href="{{ asset('storage/'.$complain->document) }}" target="_blank" class="text-primary">
                                    <i class="bx bx-file"></i> View current file
                                </a>
                            </div>
                            @endif
                            <input type="file" name="document" id="edit_document_{{ $complain->id }}" class="form-control form-control-sm {{ $cmpCls('document', $cm) }}">
                            <div class="invalid-feedback">{{ $cmpMsg('document', $cm) }}</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="edit_description_{{ $complain->id }}">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="edit_description_{{ $complain->id }}" rows="3" placeholder="Enter description" class="form-control form-control-sm {{ $cmpCls('description', $cm) }}">{{ $cmpVal('description', $complain->description, $cm) }}</textarea>
                            <div class="invalid-feedback">{{ $cmpMsg('description', $cm) }}</div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold" for="edit_action_taken_{{ $complain->id }}">Action Taken</label>
                            <textarea name="action_taken" id="edit_action_taken_{{ $complain->id }}" rows="2" placeholder="Enter action taken" class="form-control form-control-sm {{ $cmpCls('action_taken', $cm) }}">{{ $cmpVal('action_taken', $complain->action_taken, $cm) }}</textarea>
                            <div class="invalid-feedback">{{ $cmpMsg('action_taken', $cm) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_status_{{ $complain->id }}">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit_status_{{ $complain->id }}" class="form-select form-select-sm {{ $cmpCls('status', $cm) }}">
                                @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'] as $statusValue => $statusText)
                                <option value="{{ $statusValue }}" {{ $cmpVal('status', $complain->status, $cm) == $statusValue ? 'selected' : '' }}>{{ $statusText }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $cmpMsg('status', $cm) }}</div>
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


<!-- Detail Modal Window -->
@foreach($complains as $complain)
<div class="modal fade" id="viewComplainModal{{ $complain->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Complaint Details: {{ $complain->complaint_by }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>Complaint Type:</strong> {{ ucwords($complain->complaintType->name ?? 'N/A') }}</p>
                        <p><strong>Source:</strong> {{ ucwords($complain->source->source ?? $complain->source->name ?? 'N/A') }}</p>
                        <p><strong>Name:</strong> {{ $complain->complaint_by }}</p>
                        <p><strong>Phone:</strong> {{ $complain->phone ?: 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $complain->email ?: 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($complain->date)->format('d-m-Y') }}</p>
                        <p><strong>Assigned To:</strong> {{ optional($complain->staff)->name_with_role ?: 'Not Assigned' }}</p>
                        <p>
                            <strong>Status:</strong>
                            <span class="badge {{ $complain->status == 'resolved' ? 'bg-success' : ($complain->status == 'in_progress' ? 'bg-info text-dark' : 'bg-warning text-dark') }}">
                                {{ ucwords(str_replace('_', ' ', $complain->status)) }}
                            </span>
                        </p>
                        <p><strong>Created By:</strong> {{ optional($complain->createdBy)->name_with_role ?: '-' }}</p>
                        <p><strong>Updated By:</strong> {{ optional($complain->updatedBy)->name_with_role ?: '-' }}</p>
                    </div>
                    <div class="col-12">
                        <hr class="my-1">
                        <p class="mb-1"><strong>Description:</strong></p>
                        <p>{{ $complain->description ?: 'No details provided.' }}</p>

                        <p class="mb-1"><strong>Action Taken:</strong></p>
                        <p>{{ $complain->action_taken ?: 'No action taken yet.' }}</p>

                        @if($complain->document)
                        <p class="mb-1"><strong>Attachment:</strong></p>
                        <p><a href="{{ asset('storage/'.$complain->document) }}" target="_blank" class="text-primary"><i class="bx bx-file"></i> View file</a></p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
