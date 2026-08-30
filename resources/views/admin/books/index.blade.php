@extends($current_layout)

@section('title', 'Books')

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Books', 'url' => route('books.index')],
]" />

<div class="container-fluid">
    <div class="row">

        {{-- ===================== LEFT: ADD / EDIT FORM ===================== --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="formTitle">Add Book</h5>
                    <button type="button" class="btn btn-sm btn-secondary d-none" id="btnCancelEdit">
                        Cancel Edit
                    </button>
                </div>
                <div class="card-body">
                    <form id="bookForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="book_id" name="book_id">

                        <div class="mb-3">
                            <label for="book_category_id" class="form-label form-label-sm">Category <span class="text-danger">*</span></label>
                            <select name="book_category_id" id="book_category_id" class="form-select form-select-sm">
                                <option value="">Select Category</option>
                                @foreach($bookCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text" data-error="book_category_id"></span>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label form-label-sm">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control form-control-sm" placeholder="e.g. Introduction to Algorithms">
                            <span class="text-danger error-text" data-error="title"></span>
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label form-label-sm">Author</label>
                            <input type="text" name="author" id="author" class="form-control form-control-sm" placeholder="e.g. Thomas H. Cormen">
                            <span class="text-danger error-text" data-error="author"></span>
                        </div>

                        <div class="mb-3">
                            <label for="isbn" class="form-label form-label-sm">ISBN</label>
                            <input type="text" name="isbn" id="isbn" class="form-control form-control-sm" placeholder="e.g. 978-0262046305">
                            <span class="text-danger error-text" data-error="isbn"></span>
                        </div>

                        <div class="mb-3">
                            <label for="publisher" class="form-label form-label-sm">Publisher</label>
                            <input type="text" name="publisher" id="publisher" class="form-control form-control-sm" placeholder="e.g. MIT Press">
                            <span class="text-danger error-text" data-error="publisher"></span>
                        </div>

                        <div class="mb-3">
                            <label for="edition" class="form-label form-label-sm">Edition</label>
                            <input type="text" name="edition" id="edition" class="form-control form-control-sm" placeholder="e.g. 3rd, 2021">
                            <span class="text-danger error-text" data-error="edition"></span>
                        </div>

                        <div class="mb-3">
                            <label for="total_copies" class="form-label form-label-sm">Total Copies <span class="text-danger">*</span></label>
                            <input type="number" name="total_copies" id="total_copies" min="1" value="1" class="form-control form-control-sm" placeholder="e.g. 5">
                            <span class="text-danger error-text" data-error="total_copies"></span>
                            <small class="text-muted d-none" id="availableCopiesHint"></small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label form-label-sm">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control form-control-sm" placeholder="Short summary of the book (optional)"></textarea>
                            <span class="text-danger error-text" data-error="description"></span>
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label form-label-sm">Cover Image</label>
                            <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm">
                            <span class="text-danger error-text" data-error="cover_image"></span>
                            <div class="mt-2">
                                <img id="coverPreview" src="#" class="d-none border rounded" style="width:120px;height:150px;object-fit:cover;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-success w-100" id="btnSubmit">
                            <span id="btnSubmitText">Save Book</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===================== RIGHT: BOOKS LIST ===================== --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Books List</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped w-100" id="viewDataTable">
                        <thead>
                            <tr>
                                <th style="width:60px">Cover</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th style="width:100px">ISBN</th>
                                <th style="width:80px">Copies</th>
                                <th style="width:90px">Status</th>
                                <th style="width:100px">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($books as $book)
                            <tr id="book-row-{{ $book->id }}">
                                <td>
                                    <img src="{{ $book->cover_image
    ? asset('uploads/book_covers/'.$book->cover_image)
    : asset('images/no-image.png') }}"
                                        width="45"
                                        height="55"
                                        class="rounded"
                                        style="object-fit:cover">

                                </td>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author ?? '-' }}</td>
                                <td>{{ $book->category->name ?? '-' }}</td>
                                <td>{{ $book->isbn ?? '-' }}</td>
                                <td class="copies-cell">{{ $book->available_copies }} / {{ $book->total_copies }}</td>
                                <td>
                                    @can('manage-books')
                                    <a href="javascript:void(0);"
                                        data-id="{{ $book->id }}"
                                        class="btn btn-sm {{ $book->status ? 'btn-outline-success' : 'btn-outline-danger' }} confirm-toggle px-3 toggle-status-btn" id="status-btn-{{ $book->id }}">
                                        {{ $book->status ? 'Active' : 'Inactive' }}
                                    </a>
                                    @else
                                    <span class="badge {{ $book->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $book->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="{{ $book->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $book->id }}">
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
</div>
@endsection

@push('scripts')
<script>
    $(function() {

        // 1. Initialize DataTable
        var table = $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Book:"
            },
            columnDefs: [{
                    width: "60px",
                    targets: 0
                },
                {
                    width: "100px",
                    targets: 4
                },
                {
                    width: "80px",
                    targets: 5
                },
                {
                    width: "90px",
                    targets: 6
                },
                {
                    width: "110px",
                    targets: 7
                },
                {
                    orderable: false,
                    targets: [0, 6, 7]
                }
            ],
            autoWidth: false

        });

        // Flash messages from session (in case of any non-AJAX redirect actions)
        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
        toastr.error("{{ session('error') }}");
        @endif

        // Flash message saved before an AJAX-triggered reload (survives the reload)
        const pendingToast = sessionStorage.getItem('bookToast');
        if (pendingToast) {
            const {
                type,
                message
            } = JSON.parse(pendingToast);
            toastr[type](message);
            sessionStorage.removeItem('bookToast');
        }

        let isEdit = false;
        let editId = null;

        const storeUrl = "{{ route('books.store') }}";
        const updateUrlBase = "{{ url('books/update') }}"; // + /{id}
        const deleteUrlBase = "{{ url('books/delete') }}"; // + /{id}
        const showUrlBase = "{{ url('books/show') }}"; // + /{id}
        const toggleUrlBase = "{{ url('books/toggle-status') }}"; // + /{id}

        // ---------- Cover image preview (Jab File Select ki jaye) ----------
        $('#cover_image').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#coverPreview').attr('src', e.target.result).removeClass('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                $('#coverPreview').addClass('d-none').attr('src', '#');
            }
        });

        // ---------- Clear validation errors ----------
        function clearErrors() {
            $('.error-text').text('');
            $('.form-control, .form-select').removeClass('is-invalid');
        }

        // ---------- Reset form to "Add" mode ----------
        function resetForm() {
            $('#bookForm')[0].reset();
            $('#book_id').val('');
            $('#coverPreview').addClass('d-none').attr('src', '#');
            $('#availableCopiesHint').addClass('d-none').text('');
            clearErrors();
            isEdit = false;
            editId = null;
            $('#formTitle').text('Add Book');
            $('#btnSubmitText').text('Save Book');
            $('#btnSubmit').removeClass('btn-warning').addClass('btn-success');
            $('#btnCancelEdit').addClass('d-none');
        }

        $('#btnCancelEdit').on('click', resetForm);

        // ---------- Submit (Add / Edit) ----------
        $('#bookForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            const formData = new FormData(this);
            const url = isEdit ? `${updateUrlBase}/${editId}` : storeUrl;

            $('#btnSubmit').prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    // Store the message so it survives the upcoming reload
                    sessionStorage.setItem('bookToast', JSON.stringify({
                        type: 'success',
                        message: res.message || (isEdit ? 'Book updated successfully.' : 'Book added successfully.')
                    }));
                    resetForm();
                    location.reload(); // keep table + copy counts in sync
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        const fieldLabels = {
                            book_category_id: 'Category',
                            title: 'Title',
                            author: 'Author',
                            isbn: 'ISBN',
                            publisher: 'Publisher',
                            edition: 'Edition',
                            total_copies: 'Total Copies',
                            description: 'Description',
                            cover_image: 'Cover Image',
                        };

                        Object.keys(errors).forEach(field => {
                            $(`[data-error="${field}"]`).text(errors[field][0]);
                            $(`#${field}`).addClass('is-invalid');
                        });

                        const missingFields = Object.keys(errors).map(f => fieldLabels[f] || f).join(', ');
                        toastr.error(`Please check the following field(s): ${missingFields}`);
                    } else {
                        toastr.error('Something went wrong. Please try again.');
                    }
                },
                complete: function() {
                    $('#btnSubmit').prop('disabled', false);
                }
            });
        });

        // ---------- Edit ----------
        $('#viewDataTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id');

            $.get(`${showUrlBase}/${id}`, function(res) {
                const book = res.book;

                isEdit = true;
                editId = id;

                $('#book_id').val(book.id);
                $('#book_category_id').val(book.book_category_id);
                $('#title').val(book.title);
                $('#author').val(book.author);
                $('#isbn').val(book.isbn);
                $('#publisher').val(book.publisher);
                $('#edition').val(book.edition);
                $('#total_copies').val(book.total_copies);
                $('#description').val(book.description);

                // Image Preview Fix (Controller se aane wala cover_url use hoga)
                if (book.cover_image) {
                    $('#coverPreview')
                        .attr('src', '/uploads/book_covers/' + book.cover_image)
                        .removeClass('d-none');
                } else {
                    $('#coverPreview')
                        .attr('src', '#')
                        .addClass('d-none');
                }


                $('#availableCopiesHint')
                    .text(`Currently available: ${book.available_copies} / ${book.total_copies}`)
                    .removeClass('d-none');

                $('#formTitle').text('Edit Book');
                $('#btnSubmitText').text('Update Book');
                $('#btnSubmit').removeClass('btn-success').addClass('btn-warning');
                $('#btnCancelEdit').removeClass('d-none');

                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            }).fail(function() {
                toastr.error('Unable to load book details.');
            });
        });

        // ---------- Delete ----------
        $('#viewDataTable').on('click', '.btn-delete', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This book will be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `${deleteUrlBase}/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Book deleted successfully.');
                        table.row($(`#book-row-${id}`)).remove().draw();
                        if (isEdit && editId == id) resetForm();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Unable to delete this book.');
                    }
                });
            });
        });

        // ---------- Toggle Status (AJAX, Category Style Button) ----------
        $('#viewDataTable').on('click', '.toggle-status-btn', function() {
            const btn = $(this);
            const id = btn.data('id');

            $.get(`${toggleUrlBase}/${id}`, function(res) {
                toastr.success(res.message || 'Status updated successfully.');

                // Button ki styling aur text update karna bina page reload kiye
                if (res.status == 1 || res.status === true) {
                    btn.removeClass('btn-outline-danger').addClass('cd btn-outline-success').text('Active');
                    btn.removeClass('btn-outline-danger').addClass('btn-outline-success').text('Active');
                } else {
                    btn.removeClass('btn-outline-success').addClass('btn-outline-danger').text('Inactive');
                }
            }).fail(function() {
                toastr.error('Unable to update status.');
            });
        });

    });
</script>
@endpush