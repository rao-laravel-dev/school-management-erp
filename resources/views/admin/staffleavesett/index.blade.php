@extends($current_layout)

@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Staff Management</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Staff Leave List</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-none border">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary">Staff Leave Quota List</h5>
        <a href="{{ route('staffleavesett.create') }}" class="btn btn-primary btn-sm">
            <i class="bx bx-plus"></i> Assign New Leave
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-bordered table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Staff Name</th>
                        <th>Medical</th>
                        <th>Casual</th>
                        <th>Maternity</th>
                        <th>Sick</th>
                        <th>Mandatory</th>
                        <th>Action</th>
                    </tr>
                </thead>
               <tbody>
    @foreach($leaveQuotas as $roleId => $quotas)
    <tr>
        <td class="fw-bold">{{ ucfirst($quotas->first()->role->name ?? 'N/A') }}</td>
        
        <td><span class="badge bg-primary-subtle text-primary">{{ $quotas->where('leave_type', 'medical')->first()->total_days ?? 0 }}</span></td>
        <td><span class="badge bg-info-subtle text-info">{{ $quotas->where('leave_type', 'casual')->first()->total_days ?? 0 }}</span></td>
        <td><span class="badge bg-warning-subtle text-warning">{{ $quotas->where('leave_type', 'maternity')->first()->total_days ?? 0 }}</span></td>
        <td><span class="badge bg-danger-subtle text-danger">{{ $quotas->where('leave_type', 'sick')->first()->total_days ?? 0 }}</span></td>
        <td><span class="badge bg-secondary-subtle text-secondary">{{ $quotas->where('leave_type', 'mandatory')->first()->total_days ?? 0 }}</span></td>
        
        <td>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-info edit-btn" data-id="{{ $roleId }}">
                    <i class="bx bxs-edit"></i> Edit
                </button>

                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                    data-url="{{ route('staffleavesett.destroy', $roleId) }}">
                    <i class="bx bxs-trash"></i> Delete
                </button>
            </div>
        </td>
    </tr>
    @endforeach
</tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.delete-btn', function() {
        var btn = $(this);
        // Yahan se URL mil gaya jo aapne button ke data-url mein dala tha
        var url = btn.data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete all leave quotas for this staff!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete all!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url, // Ab yahan dynamic URL use ho raha hai
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.success);
                        // Table row ko remove karein
                        $('#example').DataTable().row(btn.closest('tr')).remove().draw();
                    },
                    error: function(xhr) {
                        toastr.error('Error: Something went wrong!');
                    }
                });
            }
        });
    });

    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');

        // Yahan route name sahi hona chahiye jo route:list mein show ho raha hai
        var url = "{{ route('staffleavesett.edit', ':id') }}".replace(':id', id);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#modal-body-content').html(response);
                $('#editModal').modal('show');
            },
            error: function(xhr) {
                console.log("Error details:", xhr.responseText); // Ye error bata dega
                toastr.error('Error loading edit form!');
            }
        });
    });
</script>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Leave Quota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
            </div>
        </div>
    </div>
</div>


@endpush