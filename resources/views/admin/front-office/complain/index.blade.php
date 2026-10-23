@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Front Office</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Complaint</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Complaint Directory</h5>
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#complainAdd">
                     Add
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Complaint Type</th>
                                <th>Source</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Updated By</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complains as $complain)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucwords($complain->complaintType->name ?? '-') }}</td>
                                <td>{{ ucwords($complain->source->source ?? $complain->source->name ?? '-') }}</td>
                                {{-- Name Column Modal ke liye --}}
                                <td>
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#viewComplainModal{{ $complain->id }}" class="text-danger fw-bold">
                                        {{ $complain->complaint_by }}
                                    </a>
                                </td>
                                <td>{{ $complain->phone }}</td>
                                {{-- data-order: DataTables date ko real date order mein sort kare --}}
                                <td data-order="{{ \Carbon\Carbon::parse($complain->date)->format('Y-m-d') }}">{{ \Carbon\Carbon::parse($complain->date)->format('d-m-Y') }}</td>
                                <td class="text-wrap">{{ $complain->description }}</td>
                                <td class="text-center align-middle">
                                    @php
                                    $statusColors = [
                                    'resolved' => 'btn-outline-success',
                                    'in_progress' => 'btn-outline-info',
                                    'pending' => 'btn-outline-warning'
                                    ];
                                    $statusLabels = [
                                    'resolved' => 'Resolved',
                                    'in_progress' => 'In Progress',
                                    'pending' => 'Pending'
                                    ];
                                    $statusClass = $statusColors[$complain->status] ?? 'btn-outline-secondary';
                                    $statusLabel = $statusLabels[$complain->status] ?? ucfirst($complain->status);
                                    @endphp
                                    @can('manage-front-office')
                                    <button type="button"
                                        class="btn btn-sm {{ $statusClass }} toggle-complain-status"
                                        data-id="{{ $complain->id }}"
                                        data-status="{{ $complain->status }}">
                                        {{ $statusLabel }}
                                    </button>
                                    @else
                                    <span class="badge {{ str_replace('btn-outline-', 'bg-', $statusClass) }}">{{ $statusLabel }}</span>
                                    @endcan
                                </td>
                                <td>{{ optional($complain->createdBy)->name_with_role ?: '-' }}</td>
                                <td class="updated-by-cell">{{ optional($complain->updatedBy)->name_with_role ?: '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{'#complainEdit-'.$complain->id}}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    @can('manage-front-office')
                                    <a href="{{ route('complain.delete', $complain->id) }}"
                                        class="btn btn-outline-danger btn-sm px-3 delete-btn">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                    @endcan
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

{{-- Include Modals File --}}
@include('admin.front-office.complain.modals')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Initializing DataTable
        var table = $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            // Server order (newest first) aur table order same: # 1 = latest
            "order": [[0, 'asc']],
            "language": {
                "search": "Search Complaint:"
            }
        });

        // 2. Delete Confirmation (route DELETE only, is liye AJAX DELETE)
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
            let deleteBtn = $(this);
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                // fee-assignment-swal = app.css dark mode popup + buttons gap
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: 'btn btn-danger btn-sm',
                    cancelButton: 'btn btn-secondary btn-sm'
                },
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (!result.isConfirmed) return;
                // Double click se bachao: reply aane tak button disabled
                deleteBtn.addClass('disabled');
                $.ajax({
                    url: link,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(res) {
                        toastr.success(res.message);
                        setTimeout(function() { location.reload(); }, 3000);
                    },
                    error: function(xhr) {
                        deleteBtn.removeClass('disabled');
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error ' + xhr.status + ': Something went wrong');
                    }
                });
            });
        });

        // Modal ke errors saaf: typing/change par us field ka, modal band hone par sab
        $(document).on('input change', '.modal .is-invalid', function() {
            $(this).removeClass('is-invalid').siblings('.invalid-feedback').text('');
        });
        $(document).on('hidden.bs.modal', '.modal', function() {
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').text('');
        });

        // Add modal band hone par form poora khali (old() values bhi), readonly date waise hi rahe. Edit modals ko haath nahi lagaya
        $(document).on('hidden.bs.modal', '#complainAdd', function() {
            $(this).find('input:not([type=hidden]):not([readonly]), textarea').val('');
            $(this).find('select').prop('selectedIndex', 0);
        });

        // 3. Status Change Event (popup: current status chhor kar baqi do + Cancel)
        $(document).on('click', '.toggle-complain-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let complainId = btn.data('id');
            let current = btn.data('status');
            let options = [
                { value: 'pending', label: 'Pending', cls: 'btn btn-warning btn-sm' },
                { value: 'in_progress', label: 'In Progress', cls: 'btn btn-info btn-sm' },
                { value: 'resolved', label: 'Resolved', cls: 'btn btn-success btn-sm' }
            ].filter(function(o) { return o.value !== current; });

            Swal.fire({
                title: 'Change Status',
                text: 'Select the new status for this complaint.',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: options[0].label,
                denyButtonText: options[1].label,
                // fee-assignment-swal = app.css dark mode popup + buttons gap
                buttonsStyling: false,
                customClass: {
                    popup: 'fee-assignment-swal',
                    confirmButton: options[0].cls,
                    denyButton: options[1].cls,
                    cancelButton: 'btn btn-secondary btn-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) sendComplaintAjax(btn, complainId, options[0].value);
                else if (result.isDenied) sendComplaintAjax(btn, complainId, options[1].value);
            });
        });

        // Toastr & Modal Validation Error Handling
        @if($errors->any())
        // Jo modal fail hua wahi dobara khulta hai (Add ya us row ka Edit)
        var errModal = document.getElementById(@json(old('_modal', 'complainAdd')));
        if (errModal) { new bootstrap.Modal(errModal).show(); }
        toastr.error(@json($errors->first()));
        @endif

        @if(Session::has('success'))
        toastr.success(@json(Session::get('success')));
        @endif

        @if(Session::has('error'))
        toastr.error(@json(Session::get('error')));
        @endif
    });

    // 4. Global AJAX Function
    function sendComplaintAjax(btn, id, newStatus) {
        // Double click se bachao: reply aane tak button disabled
        btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('complain.status', ':id') }}".replace(':id', id),
            type: 'POST',
            data: { status: newStatus },
            success: function(res) {
                let status = res.status;
                let labels = {
                    'pending': 'Pending',
                    'in_progress': 'In Progress',
                    'resolved': 'Resolved'
                };
                let colors = {
                    'pending': 'btn-outline-warning',
                    'in_progress': 'btn-outline-info',
                    'resolved': 'btn-outline-success'
                };

                btn.text(labels[status] || status);
                btn.data('status', status);

                btn.removeClass('btn-outline-success btn-outline-danger btn-outline-warning btn-outline-info btn-outline-secondary');
                btn.addClass(colors[status] || 'btn-outline-secondary');

                // Updated By cell reload ke bagair naya naam dikhaye
                btn.closest('tr').find('.updated-by-cell').text(res.updated_by);

                toastr.success(res.message);
            },
            error: function() {
                toastr.error("Status update failed!");
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    }
</script>
@endpush