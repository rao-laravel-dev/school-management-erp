@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Front Office</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Visitor Book</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Visitor Book Directory</h5>
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#visitorAdd">
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
                                <th>Phone</th>
                                <th>ID Card</th>
                                <th>Purpose</th>
                                <th>Meeting With</th>
                                <th>Meeted With</th>
                                <th>Persons</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visitors as $visitor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-success fw-bold">{{ ucwords($visitor->name) }}</td>
                                <td>{{ $visitor->phone }}</td>
                                <td>{{ $visitor->id_card }}</td>
                                <td>{{ ucwords($visitor->purpose->name ?? '-') }}</td>
                                <td>{{ ucwords($visitor->meeting_with_type) }}</td>
                                <td>{{ $visitor->meeting_person_name }}</td>
                                <td>{{ $visitor->no_of_person }}</td>
                                <td>
                                    {{ $visitor->date ? \Carbon\Carbon::parse($visitor->date)->format('d/m/Y') : '--' }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($visitor->in_time)->format('h:i a') }}</td>
                                <td>
                                    @if($visitor->out_time)
                                    {{ \Carbon\Carbon::parse($visitor->out_time)->format('h:i a') }}
                                    @else
                                    --
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{'#visitorEdit-'.$visitor->id}}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('visitor-book.delete', $visitor->id) }}"
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
@include('admin.front-office.visitor-book.modals')

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
</script>
@endpush