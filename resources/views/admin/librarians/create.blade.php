@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Librarian Management', 'url' => route('librarians.index')],
    ['label' => 'Add New Librarian', 'url' => route('librarians.create')],
]" />

<div class="card shadow-none border">
    <div class="card-body">
        <form action="{{ route('librarians.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">Basic Information</h5>
                <a href="{{ route('librarians.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> Librarian List</a>
            </div>
            <hr />

            @include('admin.librarians.partials.form_fields', ['librarian' => null])

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('librarians.index') }}" class="btn btn-sm btn-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-sm btn-success px-4">Save Librarian Profile</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    @include('admin.librarians.partials.form_js')

    $(document).ready(function() {
        // Librarian ID preview: First Name blur/change par (save par server dobara banata hai)
        $('#first_name').on('blur change', function() {
            const name = $(this).val();
            const idField = $('#librarian_id_field');

            if (name && name.trim().length > 0) {
                idField.val("Generating...");
                $.ajax({
                    url: "{{ route('librarians.get-librarian-id') }}",
                    type: 'GET',
                    data: {
                        first_name: name
                    },
                    success: function(res) {
                        if (res.librarian_id) {
                            idField.val(res.librarian_id);
                        } else {
                            idField.val("");
                            toastr.error("ID generation failed. Please try again.");
                        }
                    },
                    error: function() {
                        idField.val("");
                        toastr.error("Server error, could not generate ID.");
                    }
                });
            }
        });

        // Validation fail hone par ek hi clean toast, warna server error
        @if($errors->any())
            toastr.error("Please check the highlighted fields.", "Validation Error", {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            });
        @elseif(session('toastr-error'))
            toastr.error(@json(session('toastr-error')), "Error");
        @endif

        // Page reload ke baad first name ho to ID preview dobara
        if ($('#first_name').val()) {
            $('#first_name').trigger('change');
        }

        focusFirstError();
    });
</script>
@endpush
