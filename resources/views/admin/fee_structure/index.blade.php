@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Fee Structure</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- LEFT: Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0" id="formTitle">Add Fee Structure</h5>
            </div>
            <div class="card-body">
                <form id="feeStructureForm">
                    @csrf
                    <input type="hidden" id="feeStructureId" name="id">

                    <div class="mb-3">
                        <label class="form-label">Class</label>
                        <select class="form-select form-select-sm" id="school_class_id" name="school_class_id">
                            <option value="">Select</option>
                            @foreach($schoolClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error-school_class_id"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <select class="form-select form-select-sm" id="academic_year_id" name="academic_year_id">
                            <option value="">Select</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error-academic_year_id"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fee Type <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="fee_type_id" name="fee_type_id">
                            <option value="">Select</option>
                            @foreach($feeTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error-fee_type_id"></div>
                    </div>



                    <div class="mb-3">
                        <label class="form-label">Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="amount" name="amount">
                        <div class="invalid-feedback" id="error-amount"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="due_date" name="due_date">
                        <div class="invalid-feedback" id="error-due_date"></div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm" id="btnResetForm">Reset</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitFeeStructure">Save</button>
                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT: Listing -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Fee Structure List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Class</th>
                                <th>Academic Year</th>
                                <th>Fee Type</th>
                                <th>Frequency</th> {{-- 🔥 Naya column --}}
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeStructures as $fs)
                            <tr id="feeStructureRow{{ $fs->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $fs->schoolClass->name ?? '-' }}</td>
                                <td>{{ $fs->academicYear->name ?? '-' }}</td>
                                <td>{{ $fs->feeType->name ?? '-' }}</td>
                                <td>
                                    @php
                                    $freqLabels = [
                                    'one_time' => ['label' => 'One-Time', 'badge' => 'bg-success'],
                                    'monthly' => ['label' => 'Monthly', 'badge' => 'bg-info'],
                                    'quarterly' => ['label' => 'Quarterly', 'badge' => 'bg-warning text-dark'],
                                    'half_yearly' => ['label' => 'Half-Yearly', 'badge' => 'bg-secondary'],
                                    'annual' => ['label' => 'Annual', 'badge' => 'bg-warning'],
                                    ];
                                    $freq = $freqLabels[$fs->feeType->frequency ?? ''] ?? ['label' => '-', 'badge' => 'bg-light text-dark'];
                                    @endphp
                                    <span class="badge {{ $freq['badge'] }}">{{ $freq['label'] }}</span>
                                </td>
                                <td>{{ number_format($fs->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($fs->due_date)->format('d-m-Y') }}</td>
                                <td>
                                    @if($fs->isGeneratedForCurrentPeriod())
                                    <span class="badge bg-secondary">Generated</span>
                                    @else
                                    <button type="button" class="btn btn-outline-success btn-sm btnGenerateFees" data-id="{{ $fs->id }}">
                                        <i class="fa fa-sync"></i> Generate
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-warning btn-sm btnEditFeeStructure"
                                        data-id="{{ $fs->id }}"
                                        data-fee_type_id="{{ $fs->fee_type_id }}"
                                        data-school_class_id="{{ $fs->school_class_id }}"
                                        data-academic_year_id="{{ $fs->academic_year_id }}"
                                        data-amount="{{ $fs->amount }}"
                                        data-due_date="{{ $fs->due_date }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $fs->id }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {

        // ---------- DataTable ----------
        $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search:"
            }
        });

        const $form = $('#feeStructureForm');
        const $btnSubmit = $('#btnSubmitFeeStructure');
        const $formTitle = $('#formTitle');
        const $feeStructureId = $('#feeStructureId');

        // Field name -> readable label (labels mein "for" attribute nahi hai isliye manual map)
        const fieldLabels = {
            fee_type_id: 'Fee Type',
            school_class_id: 'Class',
            academic_year_id: 'Academic Year',
            amount: 'Amount',
            due_date: 'Due Date'
        };

        // ---------- Helper: clear validation errors ----------
        function clearErrors() {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');
        }

        // ---------- Helper: show validation errors ----------
        function showErrors(errors) {
            clearErrors();
            $.each(errors, function(field, messages) {
                $('#' + field).addClass('is-invalid');
                $('#error-' + field).text(messages[0]);
            });
        }

        // ---------- Reset form to "Add" mode ----------
        function resetForm() {
            $form[0].reset();
            $feeStructureId.val('');
            clearErrors();
            $formTitle.text('Add Fee Structure');
            $btnSubmit.text('Save');
        }

        $('#btnResetForm').on('click', function() {
            resetForm();
        });

        // ---------- Create / Update Submit ----------
        // store -> POST fee-structures/store  (route: fee_structures.store)
        // update -> POST fee-structures/update/{id} (route: fee_structures.update)
        $form.on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            const id = $feeStructureId.val();
            // JS file ke script section mein:
            const url = id ?
                "{{ route('fee_structure.update', ':id') }}".replace(':id', id) :
                "{{ route('fee_structure.store') }}";

            const formData = {
                _token: "{{ csrf_token() }}",
                fee_type_id: $('#fee_type_id').val(),
                school_class_id: $('#school_class_id').val(),
                academic_year_id: $('#academic_year_id').val(),
                amount: $('#amount').val(),
                due_date: $('#due_date').val(),
            };

            // Update route bhi POST hai, method spoofing ki zaroorat nahi

            $btnSubmit.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(res) {
                    toastr.success(res.message || 'Saved successfully.');
                    resetForm();
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        showErrors(errors);

                        // Sirf pehle field ka error toastr mein dikhao
                        const firstField = Object.keys(errors)[0];
                        const fieldLabel = fieldLabels[firstField] || firstField.replace(/_/g, ' ');
                        toastr.error(errors[firstField][0], fieldLabel);
                    } else {
                        toastr.error('Something went wrong. Please try again.');
                    }
                },
                complete: function() {
                    $btnSubmit.prop('disabled', false);
                }
            });
        });

        // ---------- Edit Button ----------
        $(document).on('click', '.btnEditFeeStructure', function() {
            const data = $(this).data();

            $feeStructureId.val(data.id);
            $('#fee_type_id').val(data.fee_type_id);
            $('#school_class_id').val(data.school_class_id);
            $('#academic_year_id').val(data.academic_year_id);
            $('#amount').val(data.amount);
            $('#due_date').val(data.due_date);

            clearErrors();
            $formTitle.text('Edit Fee Structure');
            $btnSubmit.text('Update');

            $('html, body').animate({
                scrollTop: 0
            }, 300);
        });

        // ---------- Delete Button (SweetAlert + Toastr) ----------
        // delete route GET hai: fee-structures/delete/{id} (route: fee_structures.delete)
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            const $row = $('#feeStructureRow' + id);

            Swal.fire({
                title: 'Are you sure?',
                text: "This fee structure will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        // JS file ke script section mein:
                        url: "{{ route('fee_structure.delete', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        success: function(res) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                            });
                            toastr.success(res.message || 'Deleted successfully.');
                        },
                        error: function() {
                            toastr.error('Failed to delete. Please try again.');
                        }
                    });
                }
            });
        });

        // ---------- Generate Fees Button ----------
        // generate route POST hai: fee-structures/generate/{id} (route: fee_structures.generate)
        $(document).on('click', '.btnGenerateFees', function() {
            const id = $(this).data('id');
            const $btn = $(this);

            Swal.fire({
                title: 'Generate Fees?',
                text: "Ye is fee structure ke liye fees generate kar dega.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, generate it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: "{{ url('fee-structure/generate') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            toastr.success(res.message || 'Fees generated successfully.');
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Failed to generate fees. Please try again.';
                            toastr.error(msg);
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        });

    });
</script>
@endpush