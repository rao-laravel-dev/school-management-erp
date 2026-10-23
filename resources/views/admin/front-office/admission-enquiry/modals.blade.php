{{-- Failed form ki modal id (_modal) match ho to hi old() / errors us modal mein dikhte hain, baqi modals saaf rehte hain --}}
@php
    $enqModal = old('_modal');
    $enqCls = function ($field, $modalId) use ($errors, $enqModal) {
        return ($enqModal === $modalId && $errors->has($field)) ? 'is-invalid' : '';
    };
    $enqMsg = function ($field, $modalId) use ($errors, $enqModal) {
        return $enqModal === $modalId ? $errors->first($field) : '';
    };
    $enqVal = function ($field, $default, $modalId) use ($enqModal) {
        return $enqModal === $modalId ? old($field, $default) : $default;
    };
@endphp

{{-- 1. ADD ENQUIRY MODAL --}}
<div class="modal fade" id="admissionEnquiryAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Admission Enquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission-enquiry.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="admissionEnquiryAdd">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="add_name" value="{{ $enqVal('name', '', 'admissionEnquiryAdd') }}" placeholder="Enter name" class="form-control form-control-sm {{ $enqCls('name', 'admissionEnquiryAdd') }}">
                            <div class="invalid-feedback">{{ $enqMsg('name', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="add_phone" value="{{ $enqVal('phone', '', 'admissionEnquiryAdd') }}" placeholder="eg: 0300-1234567" class="form-control form-control-sm {{ $enqCls('phone', 'admissionEnquiryAdd') }}">
                            <div class="invalid-feedback">{{ $enqMsg('phone', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_email">Email</label>
                            <input type="email" name="email" id="add_email" value="{{ $enqVal('email', '', 'admissionEnquiryAdd') }}" placeholder="eg: name@example.com" class="form-control form-control-sm {{ $enqCls('email', 'admissionEnquiryAdd') }}">
                            <div class="invalid-feedback">{{ $enqMsg('email', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_purpose_id">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id" id="add_purpose_id" class="form-select form-select-sm {{ $enqCls('purpose_id', 'admissionEnquiryAdd') }}">
                                <option value="">-- Select Purpose --</option>
                                @foreach($purposes as $purpose)
                                <option value="{{ $purpose->id }}" {{ $enqVal('purpose_id', '', 'admissionEnquiryAdd') == $purpose->id ? 'selected' : '' }}>{{ ucwords($purpose->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $enqMsg('purpose_id', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_source_id">Source <span class="text-danger">*</span></label>
                            <select name="source_id" id="add_source_id" class="form-select form-select-sm {{ $enqCls('source_id', 'admissionEnquiryAdd') }}">
                                <option value="">-- Select Source --</option>
                                @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ $enqVal('source_id', '', 'admissionEnquiryAdd') == $source->id ? 'selected' : '' }}>{{ ucwords($source->source ?? $source->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $enqMsg('source_id', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_date">Enquiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="add_date" data-default="{{ date('Y-m-d') }}" value="{{ $enqVal('date', date('Y-m-d'), 'admissionEnquiryAdd') }}" class="form-control form-control-sm {{ $enqCls('date', 'admissionEnquiryAdd') }}">
                            <div class="invalid-feedback">{{ $enqMsg('date', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_next_follow_up_date">Next Follow Up</label>
                            <input type="date" name="next_follow_up_date" id="add_next_follow_up_date" value="{{ $enqVal('next_follow_up_date', '', 'admissionEnquiryAdd') }}" class="form-control form-control-sm {{ $enqCls('next_follow_up_date', 'admissionEnquiryAdd') }}">
                            <div class="invalid-feedback">{{ $enqMsg('next_follow_up_date', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="add_assigned_to">Assign To Staff</label>
                            <select name="assigned_to" id="add_assigned_to" class="form-select form-select-sm {{ $enqCls('assigned_to', 'admissionEnquiryAdd') }}">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $enqVal('assigned_to', '', 'admissionEnquiryAdd') == $staff->id ? 'selected' : '' }}>{{ $staff->name_with_role }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $enqMsg('assigned_to', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="add_address">Address</label>
                            <input type="text" name="address" id="add_address" value="{{ $enqVal('address', '', 'admissionEnquiryAdd') }}" placeholder="Enter address" class="form-control form-control-sm {{ $enqCls('address', 'admissionEnquiryAdd') }}">
                            <div class="invalid-feedback">{{ $enqMsg('address', 'admissionEnquiryAdd') }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="add_description">Description</label>
                            <textarea name="description" id="add_description" rows="3" placeholder="Enter description" class="form-control form-control-sm {{ $enqCls('description', 'admissionEnquiryAdd') }}">{{ $enqVal('description', '', 'admissionEnquiryAdd') }}</textarea>
                            <div class="invalid-feedback">{{ $enqMsg('description', 'admissionEnquiryAdd') }}</div>
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

<!-- Edit Modals -->
@foreach($enquiries as $enquiry)
@php $em = 'admissionEnquiryEdit-' . $enquiry->id; @endphp
<div class="modal fade" id="{{ $em }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Admission Enquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission-enquiry.update', $enquiry->id) }}" novalidate>
                @csrf
                <input type="hidden" name="_modal" value="{{ $em }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_name_{{ $enquiry->id }}">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name_{{ $enquiry->id }}" value="{{ $enqVal('name', $enquiry->name, $em) }}" placeholder="Enter name" class="form-control form-control-sm {{ $enqCls('name', $em) }}">
                            <div class="invalid-feedback">{{ $enqMsg('name', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_phone_{{ $enquiry->id }}">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="edit_phone_{{ $enquiry->id }}" value="{{ $enqVal('phone', $enquiry->phone, $em) }}" placeholder="eg: 0300-1234567" class="form-control form-control-sm {{ $enqCls('phone', $em) }}">
                            <div class="invalid-feedback">{{ $enqMsg('phone', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_email_{{ $enquiry->id }}">Email</label>
                            <input type="email" name="email" id="edit_email_{{ $enquiry->id }}" value="{{ $enqVal('email', $enquiry->email, $em) }}" placeholder="eg: name@example.com" class="form-control form-control-sm {{ $enqCls('email', $em) }}">
                            <div class="invalid-feedback">{{ $enqMsg('email', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_purpose_id_{{ $enquiry->id }}">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id" id="edit_purpose_id_{{ $enquiry->id }}" class="form-select form-select-sm {{ $enqCls('purpose_id', $em) }}">
                                <option value="">-- Select Purpose --</option>
                                @foreach($purposes as $purpose)
                                <option value="{{ $purpose->id }}" {{ $enqVal('purpose_id', $enquiry->purpose_id, $em) == $purpose->id ? 'selected' : '' }}>{{ ucwords($purpose->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $enqMsg('purpose_id', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_source_id_{{ $enquiry->id }}">Source <span class="text-danger">*</span></label>
                            <select name="source_id" id="edit_source_id_{{ $enquiry->id }}" class="form-select form-select-sm {{ $enqCls('source_id', $em) }}">
                                <option value="">-- Select Source --</option>
                                @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ $enqVal('source_id', $enquiry->source_id, $em) == $source->id ? 'selected' : '' }}>{{ ucwords($source->source ?? $source->name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $enqMsg('source_id', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_date_{{ $enquiry->id }}">Enquiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="edit_date_{{ $enquiry->id }}" value="{{ $enqVal('date', $enquiry->date, $em) }}" class="form-control form-control-sm {{ $enqCls('date', $em) }}">
                            <div class="invalid-feedback">{{ $enqMsg('date', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_next_follow_up_date_{{ $enquiry->id }}">Next Follow Up</label>
                            <input type="date" name="next_follow_up_date" id="edit_next_follow_up_date_{{ $enquiry->id }}" value="{{ $enqVal('next_follow_up_date', $enquiry->next_follow_up_date, $em) }}" class="form-control form-control-sm {{ $enqCls('next_follow_up_date', $em) }}">
                            <div class="invalid-feedback">{{ $enqMsg('next_follow_up_date', $em) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_assigned_to_{{ $enquiry->id }}">Assign To Staff</label>
                            @php $enqSavedStaff = $enqVal('assigned_to', $enquiry->assigned_to, $em); @endphp
                            <select name="assigned_to" id="edit_assigned_to_{{ $enquiry->id }}" class="form-select form-select-sm {{ $enqCls('assigned_to', $em) }}">
                                <option value="">-- Select Staff --</option>
                                {{-- Saved staff ab inactive/trashed ho to bhi list mein rahe --}}
                                @if($enquiry->assigned_to && $enquiry->staff && !$staffs->contains('id', $enquiry->assigned_to))
                                <option value="{{ $enquiry->assigned_to }}" {{ $enqSavedStaff == $enquiry->assigned_to ? 'selected' : '' }}>{{ $enquiry->staff->name_with_role }}</option>
                                @endif
                                @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $enqSavedStaff == $staff->id ? 'selected' : '' }}>{{ $staff->name_with_role }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">{{ $enqMsg('assigned_to', $em) }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="edit_address_{{ $enquiry->id }}">Address</label>
                            <input type="text" name="address" id="edit_address_{{ $enquiry->id }}" value="{{ $enqVal('address', $enquiry->address, $em) }}" placeholder="Enter address" class="form-control form-control-sm {{ $enqCls('address', $em) }}">
                            <div class="invalid-feedback">{{ $enqMsg('address', $em) }}</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="edit_description_{{ $enquiry->id }}">Description</label>
                            <textarea name="description" id="edit_description_{{ $enquiry->id }}" rows="3" placeholder="Enter description" class="form-control form-control-sm {{ $enqCls('description', $em) }}">{{ $enqVal('description', $enquiry->description, $em) }}</textarea>
                            <div class="invalid-feedback">{{ $enqMsg('description', $em) }}</div>
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
@foreach($enquiries as $enquiry)
<div class="modal fade" id="viewEnquiryModal{{$enquiry->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Enquiry Details: {{ ucwords($enquiry->name) }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ ucwords($enquiry->name) }}</p>
                        <p><strong>Phone:</strong> {{ $enquiry->phone }}</p>
                        <p><strong>Email:</strong> {{ $enquiry->email ?? 'N/A' }}</p>
                        <p><strong>Purpose:</strong> {{ ucwords($enquiry->purpose->name ?? 'N/A') }}</p>
                        <p><strong>Source:</strong> {{ ucwords($enquiry->source->name ?? 'N/A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Enquiry Date:</strong> {{ \Carbon\Carbon::parse($enquiry->date)->format('d-m-Y') }}</p>
                        <p><strong>Next Follow Up:</strong> {{ $enquiry->next_follow_up_date ? \Carbon\Carbon::parse($enquiry->next_follow_up_date)->format('d-m-Y') : 'N/A' }}</p>
                        <p><strong>Assigned To:</strong> {{ optional($enquiry->staff)->name_with_role ?: 'Not Assigned' }}</p>
                        <p>
    <strong>Status:</strong>
    <span class="badge {{ strtolower($enquiry->status) == 'active' ? 'bg-success' :
        (strtolower($enquiry->status) == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
        {{ ucfirst($enquiry->status) }}
    </span>
</p>
                    </div>
                    <div class="col-12">
                        <hr class="my-1">
                        <p class="mb-1"><strong>Address:</strong></p>
                        <p>{{ $enquiry->address ??'No address provided.' }}</p>

                        <p class="mb-1"><strong>Description/Note:</strong></p>
                        <p>{{ $enquiry->description ??'No details provided.' }}</p>
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
