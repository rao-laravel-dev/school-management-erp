{{-- 1. ADD VISITOR MODAL --}}

<div class="modal fade" id="visitorAdd" tabindex="-1" aria-hidden="true">
    {{ session('success') }}
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Visitor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="visitorForm" method="POST" action="{{ route('visitor-book.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="purpose_id">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id"
                                id="purpose_id"
                                class="form-control @error('purpose_id') is-invalid @enderror">

                                <option value="">-- Select --</option>

                                @foreach($allPurposes as $p)
                                <option value="{{ $p->id }}" {{ old('purpose_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>

                            @error('purpose_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="meeting_with_type">Meeting With Type <span class="text-danger">*</span></label>
                            <select name="meeting_with_type" id="meeting_with_type" class="form-control @error('meeting_with_type') is-invalid @enderror" onchange="loadCategories(this.value)">
                                <option value="" disabled selected>-- Select Type --</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                @error('meeting_with_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </select>
                        </div>

                        <div class="col-md-6" id="category_container" style="display:none;">
                            <label class="form-label fw-semibold" id="cat_label">Category</label>
                            <select id="category_dropdown" name="category_id" class="form-control" onchange="loadPeople(this.value)">
                                <option value="">-- Select --</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="person_container" style="display:none;">
                            <label class="form-label fw-semibold" for="meeting_with_id">Select Person</label>
                            <select name="meeting_with_id" id="person_dropdown" class="form-control @error('meeting_with_id') is-invalid @enderror">
                                <option value="">-- Select Person --</option>
                                @error('meeting_with_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="name">Visitor Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Visitor name here">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="eg: 03123456789">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="id_card">ID Card</label>
                            <input type="number" name="id_card" id="id_card" class="form-control" placeholder="eg: 12345-1234567-8">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="no_of_person">Number of Persons</label>
                            <input type="number" name="no_of_person" id="no_of_person" class="form-control" placeholder="eg: 4">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="in_time">In Time</label>
                            <input type="time" name="in_time" id="in_time" class="form-control" value="{{ date('H:i') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="out_time">Out Time</label>
                            <input type="time" name="out_time" id="out_time" class="form-control">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. EDIT VISITOR MODALS --}}
@foreach($visitors as $v)
<div class="modal fade" id="visitorEdit-{{$v->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Visitor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm-{{$v->id}}" method="POST" action="{{ route('visitor-book.update', $v->id) }}">
                @csrf
                <input type="hidden" name="visitor_id" value="{{ $v->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_purpose_{{$v->id}}">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id"
                                id="edit_purpose_{{$v->id}}"
                                class="form-control @error('purpose_id') is-invalid @enderror">

                                @foreach($allPurposes as $p)
                                <option value="{{ $p->id }}"
                                    {{ old('purpose_id',$v->purpose_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>

                            @error('purpose_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_meeting_{{$v->id}}">Meeting With <span class="text-danger">*</span></label>
                            <select name="meeting_with_type"
                                id="edit_meeting_{{$v->id}}"
                                class="form-control"
                                onchange="loadEditCategories(this.value, {{$v->id}})">


                                <option value="student"
                                    {{ $v->meeting_with_type == 'student' ? 'selected' : '' }}>
                                    Student
                                </option>


                                <option value="staff"
                                    {{ in_array($v->meeting_with_type,['teacher','accountant','librarian','receptionist']) ? 'selected' : '' }}>
                                    Staff
                                </option>


                            </select>

                            @error('meeting_with_type')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <input type="hidden" id="saved_type_{{$v->id}}" value="{{$v->meeting_with_type}}">

                        <input type="hidden" id="old_category_{{$v->id}}" value="{{ $v->meeting_with_type }}">
                        <div class="col-md-6" id="category_container_{{$v->id}}" style="display:none;">
                            <label class="form-label fw-semibold" id="cat_label">Category</label>
                            <select
                                id="category_dropdown_{{$v->id}}"
                                name="category_id"
                                class="form-control"
                                onchange="loadEditPeople(this.value, {{$v->id}})">
                                <option value="">-- Select --</option>
                            </select>
                        </div>

                        <input type="hidden" id="old_person_{{$v->id}}" value="{{ $v->meeting_with_id }}">
                        <div class="col-md-6" id="person_container_{{$v->id}}" style="display:none;">
                            <label class="form-label fw-semibold" for="meeting_with_id">Select Person</label>
                            <select
                                name="meeting_with_id"
                                id="person_dropdown_{{$v->id}}"
                                class="form-control">
                                <option value="">-- Select Person --</option>
                                @error('meeting_with_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_name_{{$v->id}}">Visitor Name <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="edit_name_{{$v->id}}"
                                value="{{ old('name',$v->name) }}"
                                class="form-control @error('name') is-invalid @enderror">

                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_phone_{{$v->id}}">Phone <span class="text-danger">*</span></label>
                            <input type="text"
                                name="phone"
                                id="edit_phone_{{$v->id}}"
                                value="{{ old('phone',$v->phone) }}"
                                class="form-control @error('phone') is-invalid @enderror">

                            @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_idcard_{{$v->id}}">ID Card</label>
                            <input type="number" name="id_card" id="edit_idcard_{{$v->id}}" value="{{ old('id_card', $v->id_card) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_person_{{$v->id}}">No of Persons</label>
                            <input type="number" name="no_of_person" id="edit_person_{{$v->id}}" value="{{ old('no_of_person', $v->no_of_person) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_date_{{$v->id}}">Date</label>
                            <input type="date" name="date" id="edit_date_{{$v->id}}" value="{{ old('date', $v->date ?? date('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_timein_{{$v->id}}">In Time</label>
                            <input type="time" name="in_time" id="edit_timein_{{$v->id}}" value="{{ old('in_time',$v->in_time) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_timeout_{{$v->id}}">Out Time</label>
                            <input type="time" name="out_time" id="edit_timeout_{{$v->id}}" value="{{ old('out_time',$v->out_time) }}" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Global AJAX Setup (CSRF ke liye)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 2. Add Form AJAX
        $('#visitorForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#visitorAdd').modal('hide');
                    toastr.success(response.success || "Added successfully!");
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        showValidationErrors(form, errors);
                    } else {
                        toastr.error("An unexpected error occurred!");
                    }
                }
            });
        });

        // 3. Edit Form AJAX (Updated with event prevention)
        $(document).on('submit', '[id^="editForm-"]', function(e) {
            e.preventDefault();
            let form = $(this);
            let id = form.attr('id').replace('editForm-', '');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#visitorEdit-' + id).modal('hide');
                    toastr.success(response.message || "Updated successfully!");
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        showValidationErrors(form, errors);
                    } else {
                        console.log(xhr.responseText);
                        toastr.error("Update failed!");
                    }
                }
            });
        });

        // --- Validation Error Helper ---
        function showValidationErrors(form, errors) {
            // purane errors clear karein
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            let isFirst = true;

            $.each(errors, function(key, messages) {
                let field = form.find('[name="' + key + '"]');
                if (field.length) {
                    field.addClass('is-invalid');
                    field.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');

                    if (isFirst) {
                        field.focus();

                        // field name readable banayein (purpose_id -> Purpose)
                        let fieldLabel = key
                            .replace('_id', '')
                            .replace(/_/g, ' ')
                            .replace(/\b\w/g, c => c.toUpperCase());

                        toastr.error(fieldLabel + ' is required.');
                        isFirst = false;
                    }
                }
            });
        }

        // --- Other Helper Functions ---
        window.loadCategories = function(type) {
            $('#category_container').show();
            $('#cat_label').text(type === 'student' ? 'Select Class' : 'Select Role');
            let url = "{{ route('visitor.get-categories', ':type') }}".replace(':type', type);
            $.get(url, function(data) {
                let dropdown = $('#category_dropdown');
                dropdown.empty().append('<option value="">-- Select --</option>');
                $.each(data, function(i, item) {
                    dropdown.append('<option value="' + item.id + '">' + (item.name || item.class_name) + '</option>');
                });
            });
        };

        window.loadPeople = function(catId) {
            let type = $('#meeting_with_type').val();
            $('#person_container').show();
            let url = "{{ route('visitor.get-people', [':type', ':id']) }}".replace(':type', type).replace(':id', catId);
            $.get(url, function(data) {
                let dropdown = $('#person_dropdown');
                dropdown.empty().append('<option value="">-- Select Person --</option>');
                $.each(data, function(i, person) {
                    dropdown.append('<option value="' + person.id + '">' + person.name + '</option>');
                });
            });
        };

        window.loadEditCategories = function(type, id, categoryId, selectedPerson) {
            $('#category_container_' + id).show();
            let url = "{{ route('visitor.get-categories', ':type') }}".replace(':type', type);
            $.get(url, function(data) {
                let dropdown = $('#category_dropdown_' + id);
                dropdown.empty().append('<option value="">-- Select --</option>');
                $.each(data, function(i, item) {
                    dropdown.append('<option value="' + item.id + '">' + (item.name || item.class_name) + '</option>');
                });
                dropdown.val(categoryId);
                loadEditPeople(categoryId, id, selectedPerson);
            });
        };

        window.loadEditPeople = function(catId, id, selectedPerson) {
            let type = $('#edit_meeting_' + id).val();
            $('#person_container_' + id).show();
            let url = "{{ route('visitor.get-people', [':type', ':id']) }}".replace(':type', type).replace(':id', catId);
            $.get(url, function(data) {
                let dropdown = $('#person_dropdown_' + id);
                dropdown.empty().append('<option value="">-- Select Person --</option>');
                $.each(data, function(i, person) {
                    dropdown.append('<option value="' + person.id + '">' + person.name + '</option>');
                });
                dropdown.val(selectedPerson);
            });
        };

        $(document).on('shown.bs.modal', '[id^="visitorEdit-"]', function() {
            let id = $(this).attr('id').replace('visitorEdit-', '');
            $.get("{{ route('visitor.edit-data', ':id') }}".replace(':id', id), function(res) {
                loadEditCategories(res.type, id, res.category_id, res.visitor.meeting_with_id);
            });
        });

        $(document).on('hidden.bs.modal', '.modal', function() {
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
    });
</script>
@endpush