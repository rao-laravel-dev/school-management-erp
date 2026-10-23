{{-- 1. ADD VISITOR MODAL --}}

<div class="modal fade" id="visitorAdd" tabindex="-1" aria-hidden="true">
    {{ session('success') }}
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bx bx-plus me-2"></i>Add Visitor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="visitorForm" method="POST" action="{{ route('visitor-book.store') }}" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="purpose_id">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id"
                                id="purpose_id"
                                class="form-select form-select-sm">

                                <option value="">-- Select --</option>

                                @foreach($allPurposes as $p)
                                <option value="{{ $p->id }}">{{ ucwords($p->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="meeting_with_type">Meeting With Type <span class="text-danger">*</span></label>
                            <select name="meeting_with_type" id="meeting_with_type" class="form-select form-select-sm" onchange="loadCategories(this.value)">
                                <option value="" disabled selected>-- Select Type --</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="category_container" style="display:none;">
                            <label class="form-label fw-semibold" id="cat_label" for="category_dropdown">Category</label>
                            <select id="category_dropdown" name="category_id" class="form-select form-select-sm" onchange="loadPeople(this.value)">
                                <option value="">-- Select --</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="person_container" style="display:none;">
                            <label class="form-label fw-semibold" for="person_dropdown">Select Person</label>
                            <select name="meeting_with_id" id="person_dropdown" class="form-select form-select-sm">
                                <option value="">-- Select Person --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="name">Visitor Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control form-control-sm" placeholder="Visitor name here">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control form-control-sm" placeholder="eg: 03123456789">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="id_card">ID Card</label>
                            <input type="text" name="id_card" id="id_card" maxlength="15" class="form-control form-control-sm" placeholder="eg: 12345-1234567-8">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="no_of_person">Number of Persons</label>
                            <input type="number" name="no_of_person" id="no_of_person" min="1" class="form-control form-control-sm" placeholder="eg: 4">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="in_time">In Time <span class="text-danger">*</span></label>
                            <input type="time" name="in_time" id="in_time" class="form-control form-control-sm" value="{{ date('H:i') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="out_time">Out Time</label>
                            <input type="time" name="out_time" id="out_time" class="form-control form-control-sm">
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

{{-- 2. EDIT VISITOR MODALS --}}
@foreach($visitors as $v)
<div class="modal fade" id="visitorEdit-{{$v->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-edit me-2"></i>Edit Visitor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm-{{$v->id}}" method="POST" action="{{ route('visitor-book.update', $v->id) }}" novalidate>
                @csrf
                <input type="hidden" name="visitor_id" value="{{ $v->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_purpose_{{$v->id}}">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose_id"
                                id="edit_purpose_{{$v->id}}"
                                class="form-select form-select-sm">

                                <option value="">-- Select --</option>
                                @foreach($allPurposes as $p)
                                <option value="{{ $p->id }}"
                                    {{ $v->purpose_id == $p->id ? 'selected' : '' }}>
                                    {{ ucwords($p->name) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_meeting_{{$v->id}}">Meeting With Type <span class="text-danger">*</span></label>
                            <select name="meeting_with_type"
                                id="edit_meeting_{{$v->id}}"
                                class="form-select form-select-sm"
                                onchange="loadEditCategories(this.value, {{$v->id}})">

                                <option value="student"
                                    {{ $v->meeting_with_type == 'student' ? 'selected' : '' }}>
                                    Student
                                </option>

                                {{-- Student ke ilawa har saved type (teacher, receptionist, purana 'staff' ...) Staff hai --}}
                                <option value="staff"
                                    {{ $v->meeting_with_type != 'student' ? 'selected' : '' }}>
                                    Staff
                                </option>

                            </select>
                        </div>
                        <input type="hidden" id="saved_type_{{$v->id}}" value="{{$v->meeting_with_type}}">

                        <input type="hidden" id="old_category_{{$v->id}}" value="{{ $v->meeting_with_type }}">
                        <div class="col-md-6" id="category_container_{{$v->id}}" style="display:none;">
                            <label class="form-label fw-semibold" id="cat_label_{{$v->id}}" for="category_dropdown_{{$v->id}}">Category</label>
                            <select
                                id="category_dropdown_{{$v->id}}"
                                name="category_id"
                                class="form-select form-select-sm"
                                onchange="loadEditPeople(this.value, {{$v->id}})">
                                <option value="">-- Select --</option>
                            </select>
                        </div>

                        <input type="hidden" id="old_person_{{$v->id}}" value="{{ $v->meeting_with_id }}">
                        <div class="col-md-6" id="person_container_{{$v->id}}" style="display:none;">
                            <label class="form-label fw-semibold" for="person_dropdown_{{$v->id}}">Select Person</label>
                            <select
                                name="meeting_with_id"
                                id="person_dropdown_{{$v->id}}"
                                class="form-select form-select-sm">
                                <option value="">-- Select Person --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_name_{{$v->id}}">Visitor Name <span class="text-danger">*</span></label>
                            <input type="text"
                                name="name"
                                id="edit_name_{{$v->id}}"
                                value="{{ $v->name }}"
                                placeholder="Visitor name here"
                                class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_phone_{{$v->id}}">Phone <span class="text-danger">*</span></label>
                            <input type="text"
                                name="phone"
                                id="edit_phone_{{$v->id}}"
                                value="{{ $v->phone }}"
                                placeholder="eg: 03123456789"
                                class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_idcard_{{$v->id}}">ID Card</label>
                            <input type="text" name="id_card" id="edit_idcard_{{$v->id}}" maxlength="15" value="{{ $v->id_card }}" placeholder="eg: 12345-1234567-8" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="edit_person_{{$v->id}}">Number of Persons</label>
                            <input type="number" name="no_of_person" id="edit_person_{{$v->id}}" min="1" value="{{ $v->no_of_person }}" placeholder="eg: 4" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_date_{{$v->id}}">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="edit_date_{{$v->id}}" value="{{ $v->date ?? date('Y-m-d') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_timein_{{$v->id}}">In Time <span class="text-danger">*</span></label>
                            <input type="time" name="in_time" id="edit_timein_{{$v->id}}" value="{{ $v->in_time }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="edit_timeout_{{$v->id}}">Out Time</label>
                            <input type="time" name="out_time" id="edit_timeout_{{$v->id}}" value="{{ $v->out_time }}" class="form-control form-control-sm">
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
@foreach($visitors as $v)
<div class="modal fade" id="viewVisitorModal{{ $v->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Visitor Details: {{ ucwords($v->name) }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ ucwords($v->name) }}</p>
                        <p><strong>Phone:</strong> {{ $v->phone ?: 'N/A' }}</p>
                        <p><strong>ID Card:</strong> {{ $v->id_card ?: 'N/A' }}</p>
                        <p><strong>Purpose:</strong> {{ ucwords($v->purpose->name ?? 'N/A') }}</p>
                        <p><strong>Persons:</strong> {{ $v->no_of_person ?: 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Meeting With:</strong> {{ ucwords($v->meeting_with_type) }}</p>
                        <p><strong>Meeted With:</strong> {{ isset($meetingNames[$v->id]) ? $meetingNames[$v->id] : 'N/A' }}</p>
                        <p><strong>Date:</strong> {{ $v->date ? \Carbon\Carbon::parse($v->date)->format('d-m-Y') : 'N/A' }}</p>
                        <p><strong>Time In:</strong> {{ $v->in_time ? \Carbon\Carbon::parse($v->in_time)->format('h:i a') : 'N/A' }}</p>
                        <p><strong>Time Out:</strong> {{ $v->out_time ? \Carbon\Carbon::parse($v->out_time)->format('h:i a') : '--' }}</p>
                    </div>
                    <div class="col-12">
                        <hr class="my-1">
                        <p class="mb-1"><strong>Created By:</strong> {{ optional($v->createdBy)->name_with_role ?: '-' }}</p>
                        <p class="mb-0"><strong>Updated By:</strong> {{ optional($v->updatedBy)->name_with_role ?: '-' }}</p>
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
                    field.after('<div class="invalid-feedback d-block">' + $('<div>').text(messages[0]).html() + '</div>');

                    if (isFirst) {
                        field.focus();

                        // server ka apna message dikhao (required, CNIC format, ...)
                        toastr.error(messages[0]);
                        isFirst = false;
                    }
                }
            });
        }

        // Field type/change kare to uska error foran hata do (date/time/select bhi)
        $(document).on('input change', '.modal .is-invalid', function() {
            $(this).removeClass('is-invalid').next('.invalid-feedback').remove();
        });

        // Modal khulne par purane errors saaf (har modal ka apna form hai)
        $(document).on('show.bs.modal', '.modal', function() {
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
        });

        // --- Other Helper Functions ---
        // Staff role ka naam (teacher) Capitalized dikhao, sirf label (value same). Class naam waise hi.
        function catLabel(type, label) {
            return type === 'student' ? label : String(label).replace(/\b\w/g, function(c) { return c.toUpperCase(); });
        }

        window.loadCategories = function(type) {
            $('#category_container').show();
            $('#cat_label').html(type === 'student' ? 'Select Class' : 'Select Role <span class="text-danger">*</span>');
            let url = "{{ route('visitor.get-categories', ':type') }}".replace(':type', type);
            $.get(url, function(data) {
                let dropdown = $('#category_dropdown');
                dropdown.empty().append('<option value="">-- Select --</option>');
                $.each(data, function(i, item) {
                    dropdown.append('<option value="' + item.id + '">' + catLabel(type, item.name || item.class_name) + '</option>');
                });
            }).fail(function() { toastr.error('Could not load categories.'); });
        };

        window.loadPeople = function(catId) {
            let type = $('#meeting_with_type').val();
            $('#person_container').show();
            let url = "{{ route('visitor.get-people', [':type', ':id']) }}".replace(':type', type).replace(':id', catId);
            $.get(url, function(data) {
                let dropdown = $('#person_dropdown');
                dropdown.empty().append('<option value="">-- Select Person --</option>');
                $.each(data, function(i, person) {
                    dropdown.append($('<option>').val(person.id).text(person.name));
                });
            }).fail(function() { toastr.error('Could not load people.'); });
        };

        window.loadEditCategories = function(type, id, categoryId, selectedPerson) {
            $('#category_container_' + id).show();
            $('#cat_label_' + id).html(type === 'student' ? 'Select Class' : 'Select Role <span class="text-danger">*</span>');
            let url = "{{ route('visitor.get-categories', ':type') }}".replace(':type', type);
            $.get(url, function(data) {
                let dropdown = $('#category_dropdown_' + id);
                dropdown.empty().append('<option value="">-- Select --</option>');
                $.each(data, function(i, item) {
                    dropdown.append('<option value="' + item.id + '">' + catLabel(type, item.name || item.class_name) + '</option>');
                });
                dropdown.val(categoryId);
                // Purani row (role/class saved nahi) mein blank ki jagah placeholder
                if (!dropdown.val()) dropdown.val('');
                loadEditPeople(categoryId, id, selectedPerson);
            }).fail(function() { toastr.error('Could not load categories.'); });
        };

        window.loadEditPeople = function(catId, id, selectedPerson) {
            let type = $('#edit_meeting_' + id).val();
            $('#person_container_' + id).show();
            let url = "{{ route('visitor.get-people', [':type', ':id']) }}".replace(':type', type).replace(':id', catId);
            // keep = saved person, inactive/trashed ho to bhi list mein rahe
            $.get(url, { keep: selectedPerson || '' }, function(data) {
                let dropdown = $('#person_dropdown_' + id);
                dropdown.empty().append('<option value="">-- Select Person --</option>');
                $.each(data, function(i, person) {
                    dropdown.append($('<option>').val(person.id).text(person.name));
                });
                dropdown.val(selectedPerson);
                // Saved person list mein na ho (purani row) to blank ki jagah placeholder
                if (!dropdown.val()) dropdown.val('');
            }).fail(function() { toastr.error('Could not load people.'); });
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

        // Add modal band hone par form poora khali, dependent Role/Person fields wapas hide, phir aaj ki date + abhi ka time. Edit modals ko haath nahi lagaya
        $(document).on('hidden.bs.modal', '#visitorAdd', function() {
            $(this).find('input:not([type=hidden]), textarea').val('');
            $(this).find('select').prop('selectedIndex', 0);
            $('#category_container, #person_container').hide();
            $('#cat_label').text('Category');
            $('#category_dropdown').html('<option value="">-- Select --</option>');
            $('#person_dropdown').html('<option value="">-- Select Person --</option>');
            let now = new Date();
            let pad = function(n) { return (n < 10 ? '0' : '') + n; };
            $('#date').val(now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()));
            $('#in_time').val(pad(now.getHours()) + ':' + pad(now.getMinutes()));
        });
    });
</script>
@endpush
