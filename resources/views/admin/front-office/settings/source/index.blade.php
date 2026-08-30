@extends($current_layout)

@section('content')
    {{-- Breadcrumb --}}
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Front Office</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Source Management</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card radius-10">
        <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
            <h5 class="mb-0 text-primary">Source Directory</h5>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#sourceAdd">
                <i class='bx bx-plus'></i> Add Source
            </button>
            
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="50">S.No</th>
                            <th>Source Name</th>
                            <th>Description</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sources as $source)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ ucwords($source->name) }}</td>
                                <td>{{ ucwords($source->description) }}</td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#sourceEdit-{{ $source->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('settings.source.delete', $source->id) }}" class="btn btn-outline-danger btn-sm px-3 delete-btn">
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

    {{-- Modals Include --}}
    @include('admin.front-office.settings.partials.modal_source') 
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#viewDataTable').DataTable();

        // Delete Logic
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = link;
            });
        });
    });
</script>
@endpush