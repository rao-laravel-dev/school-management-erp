@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Librarian Management', 'url' => route('librarians.index')],
    ['label' => 'Edit Librarian', 'url' => '#'],
]" />

<div class="card shadow-none border">
    <div class="card-body">
        <form id="librarianEditForm" action="{{ route('librarians.update', $librarian->id) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-primary">Edit Librarian Information</h5>
                <a href="{{ route('librarians.index') }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-list-ul"></i> Librarian List</a>
            </div>
            <hr />

            @include('admin.librarians.partials.form_fields', ['librarian' => $librarian, 'librarianId' => $librarian->librarian_id])

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('librarians.index') }}" class="btn btn-sm btn-secondary px-4">Cancel</a>
                <button type="submit" id="updateLibrarianBtn" class="btn btn-sm btn-success px-4">Update Librarian Profile</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    @include('admin.librarians.partials.form_js')

    $(document).ready(function() {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "preventDuplicates": true,
            "positionClass": "toast-top-right"
        };

        // Non-AJAX fallback (JS fail ho to normal submit ke errors)
        @if($errors->any())
            toastr.error("Please check the highlighted fields.", "Validation Error");
        @elseif(session('toastr-error'))
            toastr.error(@json(session('toastr-error')), "Error");
        @endif
        focusFirstError();

        // Field ke neeche error (input-group / radio group ke baad)
        function setFieldError(name, message) {
            let field = $('[name="' + name + '"]');
            if (!field.length) return;
            let msg = $('<div class="invalid-feedback d-block"></div>').text(message);
            if (name === 'bank_type') {
                $('#bank_type_group').append(msg);
                return;
            }
            field.addClass('is-invalid');
            let group = field.closest('.input-group');
            if (group.length) {
                group.after(msg);
            } else {
                field.after(msg);
            }
        }

        // AJAX Update (accountant jaisa JSON); success par index, wahan flash toastr
        $('#librarianEditForm').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let btn = $('#updateLibrarianBtn');
            let formData = new FormData(form[0]);

            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST', // _method=PUT FormData mein hai
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    toastr.success(res.message || 'Librarian updated successfully!');
                    window.location.href = "{{ route('librarians.index') }}";
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Update Librarian Profile');

                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').remove();

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            setFieldError(key, value[0]);
                        });
                        toastr.error("Please check the highlighted fields.");
                        focusFirstError();
                    } else {
                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Server Error: ' + xhr.status);
                    }
                }
            });
        });
    });
</script>
@endpush
