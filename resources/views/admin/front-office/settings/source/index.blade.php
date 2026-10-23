@extends($current_layout)

@section('content')
    {{-- Breadcrumb --}}
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Front Office</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
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
                                    @can('manage-front-office')
                                    <a href="{{ route('settings.source.delete', $source->id) }}" class="btn btn-outline-danger btn-sm px-3 delete-btn">
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

    {{-- Modals Include --}}
    @include('admin.front-office.settings.partials.modal_source') 
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#viewDataTable').DataTable();

        // Delete Logic (route DELETE only, is liye AJAX DELETE)
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

        // Add modal band hone par form poora khali (old() values bhi). Edit modals ko haath nahi lagaya
        $(document).on('hidden.bs.modal', '#sourceAdd', function() {
            $(this).find('input:not([type=hidden]), textarea').val('');
        });

        // Toastr & Modal Validation
        @if($errors->any())
        // Jo modal fail hua wahi dobara khulta hai (Add ya us row ka Edit)
        var errModal = document.getElementById(@json(old('_modal', 'sourceAdd')));
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
</script>
@endpush