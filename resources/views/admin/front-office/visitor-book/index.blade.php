@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Front Office</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
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
                                <th>Created By</th>
                                <th>Updated By</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visitors as $visitor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                {{-- Name Column Modal ke liye --}}
                                <td>
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#viewVisitorModal{{ $visitor->id }}" class="text-success fw-bold">
                                        {{ ucwords($visitor->name) }}
                                    </a>
                                </td>
                                <td>{{ $visitor->phone }}</td>
                                <td>{{ $visitor->id_card }}</td>
                                <td>{{ ucwords($visitor->purpose->name ?? '-') }}</td>
                                <td>{{ ucwords($visitor->meeting_with_type) }}</td>
                                {{-- Naam controller ne har type ki ek query se nikale (accessor per-row queries karta tha) --}}
                                <td>{{ isset($meetingNames[$visitor->id]) ? $meetingNames[$visitor->id] : '-' }}</td>
                                <td>{{ $visitor->no_of_person }}</td>
                                {{-- data-order: DataTables date ko real date order mein sort kare --}}
                                <td data-order="{{ $visitor->date ? \Carbon\Carbon::parse($visitor->date)->format('Y-m-d') : '' }}">
                                    {{ $visitor->date ? \Carbon\Carbon::parse($visitor->date)->format('d-m-Y') : '--' }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($visitor->in_time)->format('h:i a') }}</td>
                                <td>
                                    @if($visitor->out_time)
                                    {{ \Carbon\Carbon::parse($visitor->out_time)->format('h:i a') }}
                                    @else
                                    --
                                    @endif
                                </td>
                                <td>{{ optional($visitor->createdBy)->name_with_role ?: '-' }}</td>
                                <td>{{ optional($visitor->updatedBy)->name_with_role ?: '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{'#visitorEdit-'.$visitor->id}}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    @can('manage-front-office')
                                    <a href="{{ route('visitor-book.delete', $visitor->id) }}"
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
@include('admin.front-office.visitor-book.modals')

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
                "search": "Search Visitor:"
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

        // Toastr & Modal Validation Error Handling
        @if($errors->any())
        // Visitor forms AJAX hain, phir bhi kabhi redirect error aaye to Visitor ka Add modal khule
        var myModal = new bootstrap.Modal(document.getElementById('visitorAdd'));
        myModal.show();
        toastr.error(@json($errors->first()));
        @endif

        @if(Session::has('success'))
        toastr.success(@json(Session::get('success')));
        @endif

        @if(Session::has('error'))
        toastr.error(@json(Session::get('error')));
        @endif
    });
</script>
@endpush