@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Fees Collection', 'url' => '#'],
    ['label' => 'Fee Types', 'url' => route('fee_types.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 bg-transparent py-3">
    <h5 class="mb-0 text-primary">Fee Types Directory</h5>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('fee_types.export_excel') }}" class="btn btn-success btn-sm shadow-sm" title="Export Excel">
            <i class="fas fa-file-excel"></i> <span class="d-none d-sm-inline">Excel</span>
        </a>
        <a href="{{ route('fee_types.export_pdf') }}" class="btn btn-danger btn-sm shadow-sm" title="Download PDF">
            <i class="fas fa-file-pdf"></i> <span class="d-none d-sm-inline">PDF</span>
        </a>
        @can('manage-fee-types')
        <button type="button" class="btn btn-success btn-sm shadow-sm px-md-5" id="btnAddFeeType" data-bs-toggle="modal" data-bs-target="#feeTypeModal">
            <span class="d-none d-sm-inline">Add</span>
            <i class="bx bx-plus d-sm-none"></i>
        </button>
        @endcan
    </div>
</div>

            <div class="card-body">
                <div class="table-responsive">
                    {{-- DataTables automatically handles Search, Pagination, and Dropdowns --}}
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Frequency</th> {{-- 🔥 Naya column --}}
                                <th>Discountable</th>
                                <th>Description</th>
                                @can('manage-fee-types')
                                <th width="120">Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeTypes as $feeType)
                            <tr id="feeTypeRow{{ $feeType->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feeType->name }}</td>
                                <td>
                                    @php
                                    $freqLabels = [
                                    'one_time' => ['label' => 'One-Time', 'badge' => 'bg-success'],
                                    'monthly' => ['label' => 'Monthly', 'badge' => 'bg-info'],
                                    'quarterly' => ['label' => 'Quarterly', 'badge' => 'bg-warning text-dark'],
                                    'half_yearly' => ['label' => 'Half-Yearly','badge' => 'bg-secondary'],
                                    'annual' => ['label' => 'Annual', 'badge' => 'bg-warning'],
                                    ];
                                    $freq = $freqLabels[$feeType->frequency] ?? ['label' => ucfirst($feeType->frequency), 'badge' => 'bg-light text-dark'];
                                    @endphp
                                    <span class="badge {{ $freq['badge'] }}">{{ $freq['label'] }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $feeType->is_discountable ? 'bg-success' : 'bg-warning' }}">
                                        {{ $feeType->is_discountable ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>{{ $feeType->description ?? '-' }}</td>

                                @can('manage-fee-types')
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 btnEditFeeType"
                                        data-id="{{ $feeType->id }}"
                                        data-name="{{ $feeType->name }}"
                                        data-frequency="{{ $feeType->frequency }}"
                                        data-description="{{ $feeType->description }}"
                                        date-discountable="{{ $feeType->is_discountable }}"
                                        data-status="{{ $feeType->status }}"
                                        data-bs-toggle="modal" data-bs-target="#feeTypeModal">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm px-3 delete-btn" data-id="{{ $feeType->id }}">
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

{{-- Add/Edit Modal --}}
<div class="modal fade" id="feeTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="feeTypeForm">
                @csrf
                <input type="hidden" id="feeTypeId" name="id">

                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="feeTypeModalLabel">Add Fee Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="name" name="name">
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="frequency" name="frequency">
                            <option value="one_time">One-Time</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half-Yearly</option>
                            <option value="annual">Annual</option>
                        </select>
                        <div class="invalid-feedback" id="error-frequency"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" rows="2"></textarea>
                        <div class="invalid-feedback" id="error-description"></div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_discountable" name="is_discountable" value="1">
                        <label class="form-check-label" for="is_discountable">
                            Discount allowed on this fee type?
                        </label>
                        <div class="invalid-feedback" id="error-is_discountable"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitFeeType">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        // 1. Initialize DataTable — search, pagination, entries-dropdown, total-records sab automatic
        var table = $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Fee Type:"
            }
        });

        let isEdit = false;

        // Reset modal on Add button click
        $('#btnAddFeeType').on('click', function() {
            isEdit = false;
            $('#feeTypeForm')[0].reset();
            $('#feeTypeId').val('');
            $('#is_discountable').prop('checked', false);   // new checkbox
            $('#feeTypeForm').find('.is-invalid').removeClass('is-invalid');
            $('#feeTypeForm').find('.invalid-feedback').text('');
            $('#feeTypeModalLabel').text('Add Fee Type');
            $('#btnSubmitFeeType').text('Save');
        });

        // Fill modal on Edit button click
        $(document).on('click', '.btnEditFeeType', function() {
            isEdit = true;
            $('#feeTypeForm').find('.is-invalid').removeClass('is-invalid');
            $('#feeTypeForm').find('.invalid-feedback').text('');
            $('#feeTypeModalLabel').text('Edit Fee Type');
            $('#btnSubmitFeeType').text('Update');

            $('#feeTypeId').val($(this).data('id'));
            $('#name').val($(this).data('name'));
            $('#frequency').val($(this).data('frequency'));
            $('#description').val($(this).data('description'));
            $('#is_discountable').prop('checked', $(this).data('discountable') == 1);  // new
        });

        // Submit form (Add or Update)
        $('#feeTypeForm').on('submit', function(e) {
            e.preventDefault();

            // Reset previous error states
            $('#feeTypeForm').find('.is-invalid').removeClass('is-invalid');
            $('#feeTypeForm').find('.invalid-feedback').text('');

            let id = $('#feeTypeId').val();
            let url = isEdit ?
                "{{ route('fee_types.update', ':id') }}".replace(':id', id) :
                "{{ route('fee_types.store') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#feeTypeModal').modal('hide');
                    toastr.success(res.message ?? 'Saved successfully');
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    console.log('Status:', xhr.status);
                    console.log('Response:', xhr.responseText);

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];
                        $.each(errors, function(field, messages) {
                            $('#' + field).addClass('is-invalid');
                            $('#error-' + field).text(messages[0]);
                            let fieldLabel = field.charAt(0).toUpperCase() + field.slice(1);
                            errorMessages.push(fieldLabel + ': ' + messages[0]);
                        });
                        toastr.error(errorMessages.join('<br>'));
                    } else {
                        toastr.error('Error ' + xhr.status + ': ' + (xhr.responseJSON?.message ?? 'Something went wrong'));
                    }
                }
            });
        });

        // Delete Confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let url = "{{ route('fee_types.delete', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            toastr.success(res.message ?? 'Deleted successfully');
                            setTimeout(() => location.reload(), 800);
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message ?? 'Something went wrong';
                            toastr.error(msg);
                        }
                    });
                }
            });
        });

    });
</script>
@endpush