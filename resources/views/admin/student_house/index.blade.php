@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Students Information', 'url' => '#'],
    ['label' => 'Student Houses', 'url' => route('student-house.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Student Houses</h5>
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#houseAdd">
                    Add
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($houses as $house)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $house->name }}</td>
                                <td class="align-middle">
                                    {{-- Rounded Color Box with internal gap --}}
                                    <div class="d-inline-block align-middle"
                                        style="background: {{ $house->color }}; 
                width: 40px; 
                height: 30px; 
                border: 1px solid black; 
                border-radius: 8px;
                box-shadow: inset 0 0 0 4px white;
                vertical-align: middle;">
                                    </div>
                                    {{-- Number (Color Code) --}}
                                    <span class="ms-2 align-middle">{{ $house->color }}</span>
                                </td>
                                <td class="text-center align-middle">
                                    @can('manage-student-houses')
                                    <button type="button"
                                        class="btn btn-sm {{ $house->status == 1 ? 'btn-outline-success' : 'btn-outline-danger' }} toggle-house-status"
                                        data-id="{{ $house->id }}"
                                        data-status="{{ $house->status }}">
                                        {{ $house->status == 1 ? 'Active' : 'Inactive' }}
                                    </button>
                                    @else
                                    @if($house->status == 1)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                    @endcan
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{'#houseEdit-'.$house->id}}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('student-house.delete', $house->id) }}"
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
@include('admin.student_house.modals')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Initializing DataTable (client-side search/pagination only)
        var table = $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search House:"
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

        // Toastr & Modal Validation Error Handling
        @if($errors->any())
        var myModal = new bootstrap.Modal(document.getElementById('houseAdd'));
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

    // Toggle Status 
    $(document).on('click', '.toggle-house-status', function(e) {
        e.preventDefault();
        let btn = $(this);
        let id = btn.data('id');

        $.get(`/student-house/${id}/toggle-status`, function(res) {
            let isActive = res.status == 1;

            btn.text(isActive ? 'Active' : 'Inactive');
            btn.data('status', res.status);
            btn.removeClass('btn-outline-success btn-outline-danger');
            btn.addClass(isActive ? 'btn-outline-success' : 'btn-outline-danger');

            toastr.success(res.message);
        }).fail(function() {
            toastr.error('Status update failed!');
        });
    });
</script>
@endpush