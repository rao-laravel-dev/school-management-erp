@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Front Office</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Admission Enquiry</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Admission Enquiry Directory</h5>
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#admissionEnquiryAdd">
                     Add
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    {{-- DataTables automatically handles Search, Pagination, and Dropdowns --}}
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Source</th>
                                <th>Purpose</th>
                                <th>Enquiry Date</th>
                                <th>Next Follow Up</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enquiries as $enquiry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                {{-- Name Column Modal ke liye --}}
                                <td>
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#viewEnquiryModal{{$enquiry->id}}" class="text-primary fw-bold">
                                        {{ ucwords($enquiry->name) }}
                                    </a>
                                </td>
                                <td>{{ $enquiry->phone }}</td>
                                <td>{{ ucwords($enquiry->source->name ?? 'N/A') }}</td>
                                <td>{{ ucwords($enquiry->purpose->name ?? 'N/A') }}</td>
                                <td>{{ $enquiry->date }}</td>
                                <td>{{ $enquiry->next_follow_up_date ? \Carbon\Carbon::parse($enquiry->next_follow_up_date)->format('d-M-Y') : '-' }}</td>

                                <td class="text-center align-middle">
                                    @can('manage-front-office')
                                    @php
                                    $statusColors = [
                                    'active' => 'btn-outline-success',
                                    'inactive' => 'btn-outline-danger',
                                    'pending' => 'btn-outline-warning'
                                    ];
                                    // Agar status array mein nahi mila, toh 'btn-outline-secondary' use hoga
                                    $statusClass = $statusColors[$enquiry->status] ?? 'btn-outline-secondary';
                                    @endphp

                                    <button type="button"
                                        class="btn btn-sm {{ $statusClass }} toggle-enquiry-status"
                                        data-id="{{ $enquiry->id }}">
                                        {{ ucfirst($enquiry->status) }}
                                    </button>
                                    @else
                                    @php
                                    $badgeColors = [
                                    'active' => 'bg-success',
                                    'inactive' => 'bg-danger',
                                    'pending' => 'bg-warning text-dark'
                                    ];
                                    $badgeClass = $badgeColors[$enquiry->status] ?? 'bg-secondary';
                                    @endphp

                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($enquiry->status) }}
                                    </span>
                                    @endcan
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{'#admissionEnquiryEdit-'.$enquiry->id}}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('admission-enquiry.delete', $enquiry->id) }}" class="btn btn-outline-danger btn-sm px-3 delete-btn">
                                        <i class="bx bx-trash"></i>
                                    </a>
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

{{-- Modals (Include your Add/Edit modals here) --}}
@include('admin.front-office.admission-enquiry.modals')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Initializing DataTable
        var table = $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Enquiry:"
            }
        });

        // 2. Delete Confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
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
                    window.location.href = link;
                }
            });
        });

        // 3. Status Toggle Event (Delegated for DataTable)
        $(document).on('click', '.toggle-enquiry-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let enquiryId = btn.data('id');
            let currentStatus = btn.text().trim().toLowerCase();

            if (currentStatus === 'pending') {
                Swal.fire({
                    title: 'Action Required',
                    text: "Do you want to Approve or Reject this enquiry?",
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Approve',
                    denyButtonText: 'Reject'
                }).then((result) => {
                    if (result.isConfirmed) sendEnquiryAjax(btn, enquiryId, 'approve');
                    else if (result.isDenied) sendEnquiryAjax(btn, enquiryId, 'reject');
                });
            } else {
                sendEnquiryAjax(btn, enquiryId, 'toggle');
            }
        });

        // Toastr & Modal Validation Error Handling
        @if($errors->any())
        var myModal = new bootstrap.Modal(document.getElementById('admissionEnquiryAdd'));
        myModal.show();
        toastr.error("{{ $errors->first() }}");
        @endif

        @if(Session::has('success'))
        toastr.success("{{ Session::get('success') }}");
        @endif
    });

    // 4. Global AJAX Function
    function sendEnquiryAjax(btn, id, action) {
        $.ajax({
            url: '/front-office/admission-enquiry/status/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action
            },
            success: function(res) {
                let status = res.status.toLowerCase();
                let newText = status.charAt(0).toUpperCase() + status.slice(1);

                btn.text(newText);

                // Remove all possible status classes
                btn.removeClass('btn-outline-success btn-outline-danger btn-outline-warning btn-outline-secondary');

                // Apply new class
                if (status === 'active') btn.addClass('btn-outline-success');
                else if (status === 'inactive') btn.addClass('btn-outline-danger');
                else btn.addClass('btn-outline-warning'); // Pending

                toastr.success(res.message);
            },
            error: function() {
                toastr.error("Status update failed!");
            }
        });
    }

    
</script>
@endpush