@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Fees Collection', 'url' => '#'],
    ['label' => 'Assign Fee', 'url' => route('fee_assignments.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Assign Fee</h5>
                @can('manage-fee-assignments')
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" id="feeAssignmentAddBtn" data-bs-toggle="modal" data-bs-target="#feeAssignmentAdd">
                    Add
                </button>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Kind</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Due Date / Months</th>
                                <th>Target</th>
                                <th>Fee Rows</th>
                                <th>Paid</th>
                                @can('manage-fee-assignments')
                                <th width="120">Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeAssignments as $feeAssignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $feeAssignment->title }}</td>
                                <td>
                                    <span class="badge {{ $feeAssignment->kind === 'one_time' ? 'bg-success' : 'bg-info' }}">
                                        {{ $feeAssignment->kind === 'one_time' ? 'One-time' : 'Recurring' }}
                                    </span>
                                </td>
                                <td>{{ $feeAssignment->feeType->name ?? '-' }}</td>
                                <td>{{ number_format($feeAssignment->amount, 2) }}</td>
                                <td>
                                    @if($feeAssignment->kind === 'recurring_monthly')
                                        {{ \App\Models\FeeAssignment::monthsLabel($feeAssignment->start_month, $feeAssignment->end_month) }}
                                    @else
                                        {{ $feeAssignment->due_date ? $feeAssignment->due_date->format('d-m-Y') : '-' }}
                                    @endif
                                </td>
                                <td>{{ $feeAssignment->target_label ?: '-' }}</td>
                                <td>{{ $feeAssignment->student_fees_count }}</td>
                                <td>{{ number_format($feeAssignment->student_fees_sum_paid_amount ?: 0, 2) }}</td>
                                @can('manage-fee-assignments')
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 feeAssignmentEditBtn"
                                        data-id="{{ $feeAssignment->id }}"
                                        data-title="{{ $feeAssignment->title }}"
                                        data-fee-type="{{ $feeAssignment->feeType->name ?? '-' }}"
                                        data-amount="{{ number_format($feeAssignment->amount, 2) }}"
                                        data-kind="{{ $feeAssignment->kind }}"
                                        data-due-date="{{ $feeAssignment->kind === 'recurring_monthly' ? \App\Models\FeeAssignment::monthsLabel($feeAssignment->start_month, $feeAssignment->end_month) : ($feeAssignment->due_date ? $feeAssignment->due_date->format('d-m-Y') : '-') }}"
                                        data-end-month="{{ $feeAssignment->end_month }}"
                                        data-target-label="{{ $feeAssignment->target_label ?: '-' }}"
                                        data-students="{{ count($feeAssignment->student_ids ?: []) }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm px-3 feeAssignmentDeleteBtn" data-id="{{ $feeAssignment->id }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@can('manage-fee-assignments')
@include('admin.fee_assignments.modals')
@endcan

@endsection

@push('scripts')
{{-- Select2 JS layout mein load nahi hota (sirf CSS), isliye local copy yahin --}}
<script src="{{ asset('backend/assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Assignment:"
            }
        });

        @can('manage-fee-assignments')
        const feeAssignmentSectionsUrl = "{{ route('fee_assignments.get_sections', ':id') }}";
        const feeAssignmentStudentsUrl = "{{ route('fee_assignments.students') }}";
        const feeAssignmentPreviewUrl = "{{ route('fee_assignments.preview') }}";
        const feeAssignmentStoreUrl = "{{ route('fee_assignments.store') }}";
        const feeAssignmentUpdateUrl = "{{ route('fee_assignments.update', ':id') }}";
        const feeAssignmentDeleteUrl = "{{ route('fee_assignments.delete', ':id') }}";

        // ---------- Select2 ----------
        // Plugin na mile to baqi handlers phir bhi bind hon
        if ($.fn.select2) {
            $('#feeAssignmentClasses').select2({
                dropdownParent: $('#feeAssignmentAdd'),
                width: '100%',
                placeholder: 'Select classes'
            });
            $('#feeAssignmentStudents').select2({
                dropdownParent: $('#feeAssignmentAdd'),
                width: '100%',
                placeholder: 'Search students',
                minimumInputLength: 2,
                ajax: {
                    url: feeAssignmentStudentsUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            academic_year_id: $('#feeAssignmentYear').val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    }
                }
            });
        }

        function feeAssignmentClearErrors() {
            $('#feeAssignmentForm').find('.is-invalid').removeClass('is-invalid');
            $('#feeAssignmentForm').find('[id^="feeAssignmentError-"]').text('');
        }

        // Field theek karte hi uska error hata do (Add + Edit modal; Select2 change event bhi yahi pakadta hai)
        $('#feeAssignmentForm, #feeAssignmentEditForm').on('input change', '[name]', function() {
            const base = ($(this).attr('name') || '').replace('[]', '');
            const prefix = $(this).closest('form').attr('id') === 'feeAssignmentEditForm' ? '#feeAssignmentEditError-' : '#feeAssignmentError-';
            $(this).removeClass('is-invalid');
            $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
            $(prefix + base).text('');
        });

        // Sirf chuna hua target block dikhao; baqi disabled (serialize mein nahi jaate)
        function feeAssignmentShowTarget(target) {
            $('.fee-assignment-block').each(function() {
                const match = $(this).data('target') === target;
                $(this).toggle(match);
                $(this).find('select').prop('disabled', !match);
            });
            $('#feeAssignmentPreviewBox').html('');
        }

        function feeAssignmentResetForm() {
            $('#feeAssignmentForm')[0].reset();
            $('#feeAssignmentClasses').val(null).trigger('change');
            $('#feeAssignmentStudents').empty().trigger('change');
            $('#feeAssignmentSection').html('<option value="">Select section</option>');
            feeAssignmentClearErrors();
            feeAssignmentShowTarget('school');
            feeAssignmentApplyKind('');
            $('#feeAssignmentFeeTypeNote').text('');
        }

        // Recurring: sirf Selected students + Start/End Month, Due Date nahi. Hidden inputs disabled (serialize mein nahi jaate)
        function feeAssignmentApplyKind(kind) {
            const recurring = kind === 'recurring_monthly';
            $('#feeAssignmentDueDateBox').toggle(!recurring);
            $('#feeAssignmentDueDate').prop('disabled', recurring);
            $('.fee-assignment-month-box').toggle(recurring);
            $('.fee-assignment-month-box input').prop('disabled', !recurring);
            $('.fee-assignment-target').not('#feeAssignmentTargetStudents').prop('disabled', recurring);
            if (recurring) {
                $('#feeAssignmentTargetStudents').prop('checked', true);
                feeAssignmentShowTarget('students');
            }
        }

        // Fee Type ke neeche note: kind Fee Type se tay hota hai
        function feeAssignmentUpdateNote() {
            const kind = $('#feeAssignmentFeeType option:selected').data('kind');
            let note = '';
            if (kind === 'one_time') {
                note = 'One-time fee for the selected students.';
            } else if (kind === 'recurring_monthly') {
                note = 'Monthly fee for the selected students. Rows are created for each month from the Start Month.';
            }
            $('#feeAssignmentFeeTypeNote').text(note);
            feeAssignmentApplyKind(kind || '');
        }

        $('#feeAssignmentFeeType').on('change', feeAssignmentUpdateNote);

        $('.fee-assignment-target').on('change', function() {
            feeAssignmentShowTarget($(this).val());
        });

        // Year badalne par students list aur preview reset
        $('#feeAssignmentYear').on('change', function() {
            $('#feeAssignmentStudents').empty().trigger('change');
            $('#feeAssignmentPreviewBox').html('');
        });

        $('#feeAssignmentAddBtn').on('click', feeAssignmentResetForm);

        // Class select -> sections
        $('#feeAssignmentClass').on('change', function() {
            const classId = $(this).val();
            const $section = $('#feeAssignmentSection');
            $section.html('<option value="">Select section</option>');
            if (!classId) {
                return;
            }
            $.get(feeAssignmentSectionsUrl.replace(':id', classId), function(sections) {
                $.each(sections, function(i, s) {
                    $section.append($('<option>').val(s.id).text(s.name));
                });
            });
        });

        // Error dikhao: 'class_ids.0' jaisi key ko base field par
        function feeAssignmentShowErrors(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                const messages = [];
                $.each(xhr.responseJSON.errors, function(field, list) {
                    const base = field.split('.')[0];
                    $('#feeAssignmentForm [name="' + base + '"], #feeAssignmentForm [name="' + base + '[]"]').addClass('is-invalid');
                    $('#feeAssignmentError-' + base).text(list[0]);
                    messages.push(list[0]);
                });
                // Toastr mein sirf pehla error, baqi fields par invalid-feedback
                toastr.error(messages[0]);
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('Error ' + xhr.status + ': Something went wrong');
            }
        }

        function feeAssignmentPreviewHtml(res) {
            let html = '<strong>' + res.count + '</strong> active student(s) will get this fee.';
            if (res.skipped > 0) {
                html += ' <span class="text-danger">' + res.skipped + ' selected student(s) skipped (not active).</span>';
            }
            if (res.names.length) {
                html += '<br>' + $('<div>').text(res.names.join(', ')).html() + (res.count > res.names.length ? ' ...' : '');
            }
            return html;
        }

        // ---------- Preview ----------
        $('#feeAssignmentPreviewBtn').on('click', function() {
            feeAssignmentClearErrors();
            $.ajax({
                url: feeAssignmentPreviewUrl,
                type: 'POST',
                dataType: 'json',
                data: $('#feeAssignmentForm').serialize(),
                success: function(res) {
                    $('#feeAssignmentPreviewBox').html(feeAssignmentPreviewHtml(res));
                },
                error: function(xhr) {
                    $('#feeAssignmentPreviewBox').html('');
                    feeAssignmentShowErrors(xhr);
                }
            });
        });

        // ---------- Save (store validation + count -> confirm -> store) ----------
        $('#feeAssignmentForm').on('submit', function(e) {
            e.preventDefault();
            feeAssignmentClearErrors();
            const formData = $(this).serialize();

            // Pehle preview (full=1: saari fields validate) se count lo, phir confirm, phir store
            $.ajax({
                url: feeAssignmentPreviewUrl,
                type: 'POST',
                dataType: 'json',
                data: formData + '&full=1',
                success: function(res) {
                    if (res.count === 0) {
                        toastr.error('No active students found for the selected target.');
                        return;
                    }
                    const recurring = typeof res.months !== 'undefined';
                    Swal.fire({
                        title: 'Create fees for ' + res.count + ' student(s)?',
                        text: recurring
                            ? res.months + ' months x ' + res.count + ' students = ' + res.rows + ' rows'
                            : 'One unpaid fee row is created for each student.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, create',
                        cancelButtonText: 'Cancel',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'fee-assignment-swal',
                            confirmButton: 'btn btn-success btn-sm',
                            cancelButton: 'btn btn-secondary btn-sm'
                        }
                    }).then(function(result) {
                        if (!result.isConfirmed) {
                            return;
                        }
                        $('#feeAssignmentSaveBtn').prop('disabled', true);
                        $.ajax({
                            url: feeAssignmentStoreUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: formData,
                            success: function(res2) {
                                $('#feeAssignmentAdd').modal('hide');
                                toastr.success(res2.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 3000);
                            },
                            error: function(xhr) {
                                $('#feeAssignmentSaveBtn').prop('disabled', false);
                                feeAssignmentShowErrors(xhr);
                            }
                        });
                    });
                },
                error: feeAssignmentShowErrors
            });
        });

        // ---------- Edit title ----------
        $(document).on('click', '.feeAssignmentEditBtn', function() {
            $('#feeAssignmentEditId').val($(this).data('id'));
            $('#feeAssignmentEditFeeType').text($(this).attr('data-fee-type'));
            $('#feeAssignmentEditAmount').text($(this).attr('data-amount'));
            $('#feeAssignmentEditDueDate').text($(this).attr('data-due-date'));
            $('#feeAssignmentEditTarget').text($(this).attr('data-target-label'));
            $('#feeAssignmentEditStudents').text($(this).attr('data-students'));
            const recurring = $(this).attr('data-kind') === 'recurring_monthly';
            $('#feeAssignmentEditDueDateLabel').text(recurring ? 'Months' : 'Due Date');
            $('#feeAssignmentEditNote').text(recurring
                ? 'Amount, start month and students cannot be changed. To change the amount, set an End Month and create a new assignment.'
                : 'Amount, date and students cannot be changed after fees are created. Delete and create again if no payment is made.');
            $('#feeAssignmentEditEndBox').toggle(recurring);
            $('#feeAssignmentEditEndMonth').val($(this).attr('data-end-month') || '').prop('disabled', !recurring).removeClass('is-invalid');
            $('#feeAssignmentEditError-end_month').text('');
            $('#feeAssignmentEditTitle').val($(this).data('title')).removeClass('is-invalid');
            $('#feeAssignmentEditError-title').text('');
            $('#feeAssignmentEdit').modal('show');
        });

        $('#feeAssignmentEditForm').on('submit', function(e) {
            e.preventDefault();
            $('#feeAssignmentEditTitle, #feeAssignmentEditEndMonth').removeClass('is-invalid');
            $('#feeAssignmentEditError-title, #feeAssignmentEditError-end_month').text('');
            const updateBtn = $(this).find('button[type="submit"]');
            updateBtn.prop('disabled', true);

            const editData = {
                _method: 'PUT',
                title: $('#feeAssignmentEditTitle').val()
            };
            if (!$('#feeAssignmentEditEndMonth').prop('disabled')) {
                editData.end_month = $('#feeAssignmentEditEndMonth').val();
            }

            $.ajax({
                url: feeAssignmentUpdateUrl.replace(':id', $('#feeAssignmentEditId').val()),
                type: 'POST',
                dataType: 'json',
                data: editData,
                success: function(res) {
                    $('#feeAssignmentEdit').modal('hide');
                    toastr.success(res.message);
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                },
                error: function(xhr) {
                    updateBtn.prop('disabled', false);
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        const field = errors.title ? 'title' : 'end_month';
                        const message = errors[field][0];
                        $(field === 'title' ? '#feeAssignmentEditTitle' : '#feeAssignmentEditEndMonth').addClass('is-invalid');
                        $('#feeAssignmentEditError-' + field).text(message);
                        toastr.error(message);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error ' + xhr.status + ': Something went wrong');
                    }
                }
            });
        });

        // ---------- Delete ----------
        $(document).on('click', '.feeAssignmentDeleteBtn', function() {
            const id = $(this).data('id');
            const deleteBtn = $(this);

            Swal.fire({
                title: 'Delete this assignment?',
                text: 'Its unpaid fee rows are removed. This is refused if any row has a payment.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-danger btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                }
            }).then(function(result) {
                if (!result.isConfirmed) {
                    return;
                }
                deleteBtn.prop('disabled', true);
                $.ajax({
                    url: feeAssignmentDeleteUrl.replace(':id', id),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        toastr.success(res.message);
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    },
                    error: function(xhr) {
                        deleteBtn.prop('disabled', false);
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error ' + xhr.status + ': Something went wrong');
                    }
                });
            });
        });
        @endcan
    });
</script>
@endpush
