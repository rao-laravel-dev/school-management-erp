@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Front Office</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
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
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complains as $complain)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $complain->complaintType->name ?? '-' }}</td>
                                <td>{{ $complain->source->source ?? $complain->source->name ?? '-' }}</td>
                                <td class="text-danger fw-bold">{{ $complain->complaint_by }}</td>
                                <td>{{ $complain->phone }}</td>
                                <td>{{ $complain->date }}</td>
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
                                    <button type="button"
                                        class="btn btn-sm {{ $statusClass }} toggle-complain-status"
                                        data-id="{{ $complain->id }}"
                                        data-status="{{ $complain->status }}">
                                        {{ $statusLabel }}
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{'#complainEdit-'.$complain->id}}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('complain.delete', $complain->id) }}"
                                        class="btn btn-outline-danger btn-sm px-3 delete-btn">
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
            "language": {
                "search": "Search Complaint:"
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

        // 3. Status Toggle Event (simple cycle, no approve/reject popup)
        $(document).on('click', '.toggle-complain-status', function(e) {
            e.preventDefault();
            let btn = $(this);
            let complainId = btn.data('id');
            sendComplaintAjax(btn, complainId);
        });

        // Toastr & Modal Validation Error Handling
        @if($errors->any())
        var myModal = new bootstrap.Modal(document.getElementById('complainAdd'));
        myModal.show();
        toastr.error("{{ $errors->first() }}");
        @endif

        @if(Session::has('success'))
        toastr.success("{{ Session::get('success') }}");
        @endif

        @if(Session::has('error'))
        toastr.error("{{ Session::get('error') }}");
        @endif
    });

    // 4. Global AJAX Function
    function sendComplaintAjax(btn, id) {
        $.ajax({
            url: '/front-office/complain/status/' + id,
            type: 'GET',
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

                btn.text(labels[status] ?? status);
                btn.data('status', status);

                btn.removeClass('btn-outline-success btn-outline-danger btn-outline-warning btn-outline-info btn-outline-secondary');
                btn.addClass(colors[status] ?? 'btn-outline-secondary');

                toastr.success(res.message);
            },
            error: function() {
                toastr.error("Status update failed!");
            }
        });
    }
</script>
@endpush