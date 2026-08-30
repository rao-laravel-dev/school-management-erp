@extends($current_layout)

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Home Work', 'url' => '#'],
    ['label' => 'Home Work', 'url' => route('home_work.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Home Work Directory</h5>
                @can('manage-home-works')
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#addHomeworkModal">
                    Add
                </button>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="homeworkTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Title</th>
                                <th>Homework Date</th>
                                <th>Due Date</th>
                                <th>Attachment</th>
                                <th>Assigned By</th>
                                <th>Status</th>
                                @can('manage-home-works')
                                <th width="120">Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($homeworks as $index => $hw)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $hw->class->name ?? '-' }}</td>
                                <td>{{ $hw->section->name ?? '-' }}</td>
                                <td>{{ $hw->subject->name ?? '-' }}</td>
                                <td>{{ $hw->title }}</td>
                                <td>{{ optional($hw->homework_date)->format('d-M-Y') ?? '-' }}</td>
                                <td>
                                    {{ optional($hw->due_date)->format('d-M-Y') ?? '-' }}
                                    @if($hw->due_date && !$hw->due_date->isPast())
                                    <span class="badge bg-info">Pending</span>
                                    @else
                                    <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    @if($hw->attachment)
                                    <a href="{{ asset($hw->attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>{{ $hw->createdBy->name ?? '-' }}</td>
                                <td>
                                    @can('manage-home-works')
                                    <button type="button" class="btn btn-sm status-toggle-btn {{ $hw->status ? 'btn-outline-success' : 'btn-outline-danger' }} px-3" data-id="{{ $hw->id }}">
                                        {{ $hw->status ? 'Active' : 'Inactive' }}
                                    </button>
                                    @else
                                    <span class="badge {{ $hw->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $hw->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>
                                @can('manage-home-works')
                                <td>
                                    <button type="button" class="btn btn-outline-info btn-sm px-3 view-homework-btn"
                                        data-title="{{ $hw->title }}"
                                        data-description="{{ $hw->description ?? 'No description provided.' }}"
                                        data-class="{{ $hw->class->name ?? '-' }}"
                                        data-section="{{ $hw->section->name ?? '-' }}"
                                        data-subject="{{ $hw->subject->name ?? '-' }}"
                                        data-homework-date="{{ optional($hw->homework_date)->format('d-M-Y') ?? '-' }}"
                                        data-due-date="{{ optional($hw->due_date)->format('d-M-Y') ?? '-' }}"
                                        data-attachment="{{ $hw->attachment ? asset($hw->attachment) : '' }}"
                                        data-assigned-by="{{ $hw->createdBy->name ?? '-' }}"
                                        data-status="{{ $hw->status ? 'Active' : 'Inactive' }}"
                                        data-bs-toggle="modal" data-bs-target="#viewHomeworkModal">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 edit-homework-btn" data-id="{{ $hw->id }}" data-bs-toggle="modal" data-bs-target="#editHomeworkModal">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('home_work.delete', $hw->id) }}" class="btn btn-outline-danger btn-sm px-3 delete-homework-btn">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </td>
                                @endcan
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11">
                                    <div class="alert alert-warning text-center mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> No Homework Record Found
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.home_work.modals')
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#homeworkTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Home Work:"
            }
        });

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif

        @if($errors->any())
        toastr.error("Please fill empty required fields correctly.");
        @endif

        // ===== Reusable cascading dropdown functions =====

        function loadSections(prefix, classId, selectedSectionId) {
            var $section = $('#' + prefix + '_section_id');
            $section.prop('disabled', true).html('<option value="">Loading...</option>');

            $.get("{{ url('home-work/get-sections') }}/" + classId, function(sections) {
                var options = '<option value="">Select Section</option>';
                $.each(sections, function(i, sec) {
                    var selected = (selectedSectionId == sec.id) ? 'selected' : '';
                    options += '<option value="' + sec.id + '" ' + selected + '>' + sec.name + '</option>';
                });
                $section.html(options).prop('disabled', false);
            }).fail(function() {
                toastr.error('Unable to load sections.');
            });
        }

        function loadGroups(prefix, classId, selectedGroupId, callback) {
            var $groupWrapper = $('#' + prefix + '_group_wrapper');
            var $group = $('#' + prefix + '_group_id');

            $.get("{{ url('home-work/get-groups') }}/" + classId, function(groups) {
                if (groups.length > 0) {
                    var options = '<option value="">Select Group</option>';
                    $.each(groups, function(i, g) {
                        var selected = (selectedGroupId == g.id) ? 'selected' : '';
                        options += '<option value="' + g.id + '" ' + selected + '>' + g.name + '</option>';
                    });
                    $group.html(options);
                    $groupWrapper.show();
                } else {
                    $group.html('<option value="">Select Group</option>').val('');
                    $groupWrapper.hide();
                }
                if (callback) callback(groups.length > 0);
            }).fail(function() {
                toastr.error('Unable to load groups.');
            });
        }

        function loadSubjects(prefix, classId, groupId, selectedSubjectId) {
            var $subject = $('#' + prefix + '_subject_id');
            $subject.prop('disabled', true).html('<option value="">Loading...</option>');

            $.get("{{ url('home-work/get-subjects') }}", {
                class_id: classId,
                group_id: groupId
            }, function(subjects) {
                var options = '<option value="">Select Subject</option>';
                $.each(subjects, function(i, sub) {
                    var selected = (selectedSubjectId == sub.id) ? 'selected' : '';
                    options += '<option value="' + sub.id + '" ' + selected + '>' + sub.name + '</option>';
                });
                $subject.html(options).prop('disabled', false);
            }).fail(function() {
                toastr.error('Unable to load subjects.');
            });
        }

        // ===== ADD MODAL cascading =====
        $('#add_class_id').on('change', function() {
            var classId = $(this).val();
            $('#add_group_wrapper').hide();
            $('#add_subject_id').prop('disabled', true).html('<option value="">Select Class First</option>');

            if (!classId) {
                $('#add_section_id').prop('disabled', true).html('<option value="">Select Class First</option>');
                return;
            }

            loadSections('add', classId, null);
            loadGroups('add', classId, null, function(hasGroups) {
                if (!hasGroups) {
                    loadSubjects('add', classId, null, null);
                }
            });
        });

        $('#add_group_id').on('change', function() {
            var classId = $('#add_class_id').val();
            var groupId = $(this).val();
            loadSubjects('add', classId, groupId, null);
        });

        // ===== EDIT MODAL cascading =====
        $('#edit_class_id').on('change', function() {
            var classId = $(this).val();
            $('#edit_group_wrapper').hide();
            $('#edit_subject_id').html('<option value="">Select Class First</option>');

            if (!classId) return;

            loadSections('edit', classId, null);
            loadGroups('edit', classId, null, function(hasGroups) {
                if (!hasGroups) {
                    loadSubjects('edit', classId, null, null);
                }
            });
        });

        // ===== View Modal =====
        $(document).on('click', '.view-homework-btn', function() {
            var btn = $(this);

            $('#view_title').text(btn.data('title'));
            $('#view_class').text(btn.data('class'));
            $('#view_section').text(btn.data('section'));
            $('#view_subject').text(btn.data('subject'));
            $('#view_description').text(btn.data('description'));
            $('#view_homework_date').text(btn.data('homework-date'));
            $('#view_due_date').text(btn.data('due-date'));
            $('#view_assigned_by').text(btn.data('assigned-by'));

            var status = btn.data('status');
            $('#view_status').html(
                '<span class="badge ' + (status === 'Active' ? 'bg-success' : 'bg-secondary') + '">' + status + '</span>'
            );

            var attachmentUrl = btn.data('attachment');
            if (attachmentUrl) {
                $('#view_attachment').html('<a href="' + attachmentUrl + '" target="_blank" class="btn btn-outline-primary btn-sm">View Document</a>');
            } else {
                $('#view_attachment').text('No attachment');
            }
        });

        // ===== Modal open/reopen after validation error =====
        @if(session('open_modal') == 'add')
        var addModal = new bootstrap.Modal(document.getElementById('addHomeworkModal'));
        addModal.show();
        @elseif(str_starts_with(session('open_modal', ''), 'edit_'))
        var editId = "{{ str_replace('edit_', '', session('open_modal', '')) }}";
        if (editId) {
            loadHomeworkIntoEditModal(editId);
            var editModal = new bootstrap.Modal(document.getElementById('editHomeworkModal'));
            editModal.show();
        }
        @endif

        // ===== Status toggle =====
        $(document).on('click', '.status-toggle-btn', function() {
            var id = $(this).data('id');
            $.post("{{ url('home-work/status') }}/" + id, {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 800);
            }).fail(function() {
                toastr.error('Something went wrong.');
            });
        });

        // ===== Delete =====
        $(document).on('click', '.delete-homework-btn', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            Swal.fire({
                title: 'Are you sure?',
                text: "This homework record will be deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = url;
            });
        });

        // ===== Load record into Edit modal (with cascading pre-fill) =====
        function loadHomeworkIntoEditModal(id) {
            $.get("{{ url('home-work/edit') }}/" + id, function(data) {
                $('#edit_id').val(data.id);
                $('#editHomeworkForm').attr('action', "{{ url('home-work/update') }}/" + data.id);
                $('#edit_title').val(data.title);
                $('#edit_description').val(data.description);
                $('#edit_homework_date').val(data.homework_date);
                $('#edit_due_date').val(data.due_date);
                $('#edit_class_id').val(data.class_id);

                loadSections('edit', data.class_id, data.section_id);
                loadGroups('edit', data.class_id, data.group_id, function(hasGroups) {
                    loadSubjects('edit', data.class_id, data.group_id, data.subject_id);
                });
            }).fail(function() {
                toastr.error('Unable to load homework details.');
            });
        }

        $(document).on('click', '.edit-homework-btn', function() {
            loadHomeworkIntoEditModal($(this).data('id'));
        });
    });
</script>
@endpush