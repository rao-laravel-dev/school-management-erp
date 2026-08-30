@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Academic</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Holiday Type List</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    {{-- LEFT FORM --}}
    <div class="col-md-4">
        <div class="card radius-10">
            <div class="card-header">
                <h5 id="formTitle">Add</h5>
            </div>
            <div class="card-body">
                <form id="eventForm" action="{{ route('event_type.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="method" value="POST">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" id="color" class="form-control form-control-color">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <button class="btn btn-primary" id="saveBtn">Save</button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelBtn">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT INDEX LIST --}}
    <div class="col-md-8">
        <div class="card radius-10">
            <div class="card-header">
                <h5>Event Types</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="eventTypeTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Status</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eventTypes as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->name }}</td>
                                <td class="align-middle">
                                    {{-- Rounded Color Box with internal gap --}}
                                    <div class="d-inline-block align-middle"
                                        style="background: {{ $row->color }}; 
                width: 40px; 
                height: 30px; 
                border: 1px solid black; 
                border-radius: 8px; /* Bahar ki outline rounded */
                box-shadow: inset 0 0 0 4px white; /* Andar ki shadow bhi rounded hai */
                vertical-align: middle;">
                                    </div>

                                    {{-- Number (Color Code) --}}
                                    <span class="ms-2 align-middle">{{ $row->color }}</span>
                                </td>
                                <td>
                                    @if($row->status)
                                    <span class="badge rounded-pill bg-light-success text-success px-3">Active</span>
                                    @else
                                    <span class="badge rounded-pill bg-light-danger text-danger px-3">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Edit Button --}}
                                    <button class="btn btn-sm btn-outline-warning editBtn px-3"
                                        data-id="{{ $row->id }}"
                                        data-name="{{ $row->name }}"
                                        data-color="{{ $row->color }}"
                                        data-status="{{ $row->status }}"
                                        data-url="{{ route('event_type.update', $row->id) }}">
                                        <i class="bx bx-edit"></i>
                                    </button>

                                    {{-- Delete Form --}}
                                    <button type="button" class="btn btn-sm btn-outline-danger px-3 delete-btn" data-url="{{ route('event_type.delete', $row->id) }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @endsection

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#eventTypeTable').DataTable();

            // Reset validation styles on input change
            $('.form-control, .form-select').on('input change', function() {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            });

            $('.editBtn').click(function() {
                $('#formTitle').text('Edit Event Type');
                $('#saveBtn').text('Update');
                $('#cancelBtn').removeClass('d-none');
                $('#name').val($(this).data('name'));
                $('#color').val($(this).data('color'));
                $('#status').val($(this).data('status'));
                $('#method').val('POST');   // ✅ PUT ki jagah POST
                $('#eventForm').attr('action', $(this).data('url')); // ✅ correct URL from route()
            });

            $('#cancelBtn').click(function() {
                $('#formTitle').text('Add Event Type');
                $('#saveBtn').text('Save');
                $('#cancelBtn').addClass('d-none');
                $('#eventForm').attr('action', "{{ route('event_type.store') }}");
                $('#method').val('POST');
                $('#eventForm')[0].reset();
            });

            $('#eventForm').submit(function(e) {
                e.preventDefault();
                let form = $(this);
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST', // Use POST with _method field for PUT
                    data: form.serialize(),
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status == 422) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                let input = $('#' + key);
                                if (input.length) {
                                    input.addClass('is-invalid');
                                    input.after('<div class="invalid-feedback">' + value[0] + '</div>');
                                }
                                toastr.error(value[0]);
                            });
                        }
                    }
                });
            });
            // Delete using SweetAlert and AJAX
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                let url = $(this).data('url'); // Button se URL get karein

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
                        $.ajax({
                            url: url,
                            type: 'GET', // Ab GET request bhejen
                            success: function(response) {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            },
                            error: function(xhr) {
                                // Agar error ho toh message show karein
                                let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting record!';
                                toastr.error(errorMsg);
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush