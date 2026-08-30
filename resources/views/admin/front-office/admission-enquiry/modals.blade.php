<div class="modal fade" id="admissionEnquiryAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Admission Enquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission-enquiry.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="email">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="purpose_id">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id" id="purpose_id" class="form-control @error('purpose_id') is-invalid @enderror" required>
                                <option disabled selected>-- Select Purpose --</option>
                                @foreach($purposes as $purpose)
                                <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                                @endforeach
                            </select>
                            @error('purpose_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="source_id">Source <span class="text-danger">*</span></label>
                            <select name="source_id" id="source_id" class="form-control @error('source_id') is-invalid @enderror">
                                <option disabled selected>-- Select Source --</option>
                                @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ old('source_id') == $source->id ? 'selected' : '' }}>{{ $source->source ?? $source->name }}</option>
                                @endforeach
                            </select>
                            @error('source_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="date">Enquiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control @error('date') is-invalid @enderror">
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="next_follow_up_date">Next Follow Up</label>
                            <input type="date" name="next_follow_up_date" id="next_follow_up_date" value="{{ old('next_follow_up_date') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="assigned_to">Assign To Staff</label>
                            <select name="assigned_to" id="assigned_to" class="form-control">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                {{-- Yahan user_id ya id, jo aap database mein save karna chahte hain --}}
                                <option value="{{ $staff->user_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="address">Address</label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold" for="description">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
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

<!-- Edit Modals -->
@foreach($enquiries as $enquiry)
<div class="modal fade" id="admissionEnquiryEdit-{{ $enquiry->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Admission Enquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admission-enquiry.update', $enquiry->id) }}">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="name-{{ $enquiry->id }}">Name</label>
                            <input type="text" name="name" value="{{ old('name', $enquiry->name) }}" class="form-control @error('name') is-invalid @enderror">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone-{{ $enquiry->id }}">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $enquiry->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="email-{{ $enquiry->id }}">Email</label>
                            <input type="email" name="email" value="{{ old('email', $enquiry->email) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purpose</label>
                            <select name="purpose_id" class="form-control" required>
                                @foreach($purposes as $purpose)
                                <option value="{{ $purpose->id }}" {{ old('purpose_id', $enquiry->purpose_id) == $purpose->id ? 'selected' : '' }}>
                                    {{ $purpose->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="source_id-{{ $enquiry->id }}">Source</label>
                            <select name="source_id" class="form-control">
                                @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ old('source_id', $enquiry->source_id) == $source->id ? 'selected' : '' }}>{{ $source->source ?? $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="date-{{ $enquiry->id }}">Date</label>
                            <input type="date" name="date" value="{{ old('date', $enquiry->date) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="next-{{ $enquiry->id }}">Next Follow Up</label>
                            <input type="date" name="next_follow_up_date" value="{{ old('next_follow_up_date', $enquiry->next_follow_up_date) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="assigned_to">Assign To Staff</label>
                            <select name="assigned_to" id="assigned_to" class="form-control">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                {{-- Logic: Agar database wali ID aur current staff ID match kare, toh 'selected' show karo --}}
                                <option value="{{ $staff->user_id }}"
                                    {{ (isset($enquiry) && $enquiry->assigned_to == $staff->user_id) ? 'selected' : '' }}>
                                    {{ $staff->first_name }} {{ $staff->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" name="address" value="{{ old('address', $enquiry->address) }}" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $enquiry->description) }}</textarea>
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
                        <p><strong>Purpose:</strong> {{ $enquiry->purpose->name ?? 'N/A' }}</p>
                        <p><strong>Source:</strong> {{ $enquiry->source->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Enquiry Date:</strong> {{ $enquiry->date }}</p>
                        <p><strong>Next Follow Up:</strong> {{ $enquiry->next_follow_up_date ?? 'N/A' }}</p>
                        <p><strong>Assigned To:</strong> {{ $enquiry->staff->first_name ?? 'Not Assigned' }}</p>
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
                        <p class="text-muted">{{ $enquiry->address ?? 'No address provided.' }}</p>

                        <p class="mb-1"><strong>Description/Note:</strong></p>
                        <p class="text-muted">{{ $enquiry->description ?? 'No details provided.' }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach