@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Front Office</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Purpose Management</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            {{-- Updated Header with Consistent Styling --}}
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Purpose Directory</h5>
                
                    <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#purposeAdd">
                        <i class='bx bx-plus'></i> Add
                    </button>

            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="50">S.No</th>
                                <th>Purpose</th>
                                <th>Description</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purposes as $purpose)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucwords($purpose->name) }}</td>
                                <td>{{ ucwords($purpose->description) }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#purposeEdit-{{ $purpose->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('settings.purpose.delete', $purpose->id) }}" class="btn btn-outline-danger btn-sm px-3 delete-btn">
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


{{-- Modals Include --}}
    @include('admin.front-office.settings.partials.modal_purpose')


@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // DataTable
        $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true
        });

        // Delete Confirmation
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

        // Toastr & Modal Validation
        @if($errors->any())
        var myModal = new bootstrap.Modal(document.getElementById('purposeAdd'));
        myModal.show();
        @foreach($errors->all() as $error)
        toastr.error("{{ $error }}");
        @endforeach
        @endif

        @if(Session::has('success'))
        toastr.success("{{ Session::get('success') }}");
        @endif
    });
</script>
@endpush