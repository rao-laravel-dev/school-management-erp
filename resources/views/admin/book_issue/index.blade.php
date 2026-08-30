@extends($current_layout)

@section('title', 'Book Issue / Return')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Book Issue', 'url' => route('book_issue.index')],
]" />

<div class="container-fluid">

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Book Issues</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('book_issue.reports') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-bar-chart-alt-2"></i> Reports
                </a>
                @can('manage-book-issues')
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#issueModal">
                    <i class="bx bx-plus"></i> Issue New Book
                </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped w-100" id="bookIssuesTable">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                        <th style="width:140px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                    <tr id="issue-row-{{ $issue->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $issue->book->title ?? '-' }}</td>
                        <td><span class="fw-semibold text-primary">{{ $issue->libraryMember->member_name ?? '-' }}</span></td>
                        <td>{{ ucfirst($issue->libraryMember->member_type ?? '-') }}</td>
                        <td>{{ $issue->issue_date }}</td>
                        <td class="due-date-cell">{{ $issue->due_date }}</td>
                        <td>{{ $issue->return_date ?? '-' }}</td>
                        <td>
                            @php
                            $badge = match($issue->status) {
                            'issued' => 'bg-primary',
                            'returned' => 'bg-success',
                            'overdue' => 'bg-danger',
                            'lost' => 'bg-dark',
                            default => 'bg-secondary',
                            };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($issue->status) }}</span>
                        </td>
                        <td>{{ number_format($issue->fine_amount, 2) }}</td>
                        <td class="text-nowrap">
                            <div class="d-flex gap-1">
                                <a href="{{ route('book_issue.show', $issue->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bx bx-show"></i>
                                </a>
                                @can('manage-book-issues')
                                @if($issue->status !== 'returned')
                                <button class="btn btn-sm btn-outline-success btn-return" data-id="{{ $issue->id }}" title="Return">
                                    <i class="bx bx-undo"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-extend" data-id="{{ $issue->id }}" data-due="{{ $issue->due_date }}" title="Extend Due Date">
                                    <i class="bx bx-calendar-edit"></i>
                                </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $issue->id }}" title="Delete">
                                    <i class="bx bx-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    {{-- Blade ka @empty yahan fallback ke tor par hai agar server side direct empty aaye --}}
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Issue Modal -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="issueForm">
                <div class="modal-header bg-success">
                    <h5 class="modal-title">Issue New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3 position-relative">
                        <label class="form-label form-label-sm">Search Book <span class="text-danger">*</span></label>
                        <input type="text" id="bookSearch" class="form-control form-control-sm" placeholder="Type title or ISBN..." autocomplete="off">
                        <input type="hidden" id="book_id" name="book_id">
                        <div id="bookResults" class="list-group position-absolute w-100" style="z-index:1060; max-height:180px; overflow-y:auto;"></div>
                        <span class="text-danger error-text" data-error="book_id"></span>
                    </div>

                    <div class="mb-3 position-relative">
                        <label class="form-label form-label-sm">Search Member <span class="text-danger">*</span></label>
                        <input type="text" id="memberSearch" class="form-control form-control-sm" placeholder="Type name or card no..." autocomplete="off">
                        <input type="hidden" id="library_member_id" name="library_member_id">
                        <div id="memberResults" class="list-group position-absolute w-100" style="z-index:1060; max-height:180px; overflow-y:auto;"></div>
                        <span class="text-danger error-text" data-error="library_member_id"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label form-label-sm">Due Date <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" id="due_date" class="form-control form-control-sm">
                        <span class="text-danger error-text" data-error="due_date"></span>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Issue Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extend Due Date Modal -->
<div class="modal fade" id="extendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="extendForm">
                <input type="hidden" id="extend_issue_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Extend Due Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label form-label-sm">New Due Date</label>
                    <input type="date" id="extend_due_date" class="form-control form-control-sm">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        const issuesTable = $('#bookIssuesTable').DataTable({
            pageLength: 10,
            ordering: true,
            order: [], 
            columnDefs: [{
                orderable: false,
                targets: [0, 9]
            }],
            // 👇 Ye language configuration table ke andar warning alert show karegi jab records nahi honge
            language: {
                emptyTable: `<div class="alert alert-warning m-2 text-center" role="alert">
                                <i class="bx bx-error-circle me-1"></i> No book issues found.
                             </div>`,
                zeroRecords: `<div class="alert alert-warning m-2 text-center" role="alert">
                                <i class="bx bx-error-circle me-1"></i> No matching book issues found.
                             </div>`
            }
        });

        const searchBookUrl = "{{ route('book_issue.search_book') }}";
        const searchMemberUrl = "{{ route('book_issue.search_member') }}";
        const storeUrl = "{{ route('book_issue.store') }}";
        const returnUrlBase = "{{ url('book-issue/return') }}";
        const updateUrlBase = "{{ url('book-issue/update') }}";
        const deleteUrlBase = "{{ url('book-issue/delete') }}";
        const csrf = $('meta[name="csrf-token"]').attr('content');

        function clearErrors() {
            $('.error-text').text('');
            $('.form-control, .form-select').removeClass('is-invalid');
        }

        // ---------- Book autocomplete ----------
        let bookTimer;
        $('#bookSearch').on('input', function() {
            clearTimeout(bookTimer);
            const term = $(this).val();
            $('#book_id').val('');
            if (term.length < 2) {
                $('#bookResults').empty();
                return;
            }

            bookTimer = setTimeout(() => {
                $.get(searchBookUrl, {
                    term
                }, function(res) {
                    $('#bookResults').empty();
                    res.forEach(b => {
                        const disabled = b.available_copies < 1 ? 'disabled text-muted' : '';
                        $('#bookResults').append(
                            `<button type="button" class="list-group-item list-group-item-action ${disabled}" data-id="${b.id}" data-title="${b.title}" ${b.available_copies < 1 ? 'disabled' : ''}>
                            ${b.title} ${b.isbn ? '(' + b.isbn + ')' : ''} — Available (<span class="fw-bold text-danger">${b.available_copies}</span>)
                            </button>`
                        );
                    });
                });
            }, 300);
        });

        $('#bookResults').on('click', 'button', function() {
            $('#book_id').val($(this).data('id'));
            $('#bookSearch').val($(this).data('title'));
            $('#bookResults').empty();
        });

        // ---------- Member autocomplete ----------
        let memberTimer;
        $('#memberSearch').on('input', function() {
            clearTimeout(memberTimer);
            const term = $(this).val();
            $('#library_member_id').val('');
            if (term.length < 2) {
                $('#memberResults').empty();
                return;
            }

            memberTimer = setTimeout(() => {
                $.get(searchMemberUrl, {
                    term
                }, function(res) {
                    $('#memberResults').empty();
                    res.forEach(m => {
                        $('#memberResults').append(
                            `<button type="button" class="list-group-item list-group-item-action" data-id="${m.id}" data-name="${m.name}">
                            ${m.name} — ${m.type} (<span class="fw-bold text-success">${m.library_card_no ?? 'no card'}</span>)
                            </button>`
                        );
                    });
                });
            }, 300);
        });

        $('#memberResults').on('click', 'button', function() {
            $('#library_member_id').val($(this).data('id'));
            $('#memberSearch').val($(this).data('name'));
            $('#memberResults').empty();
        });

        // ---------- Issue Book submit ----------
        $('#issueForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            $.post(storeUrl, {
                _token: csrf,
                book_id: $('#book_id').val(),
                library_member_id: $('#library_member_id').val(),
                due_date: $('#due_date').val(),
            }, function(res) {
                toastr.success(res.message);
                $('#issueModal').modal('hide');
                setTimeout(() => location.reload(), 800);
            }).fail(function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(field, messages) {
                        $(`[data-error="${field}"]`).text(messages[0]);
                    });
                    toastr.error('Please fix the highlighted errors.');
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Unable to issue book.');
                }
            });
        });

        // ---------- Return ----------
        $(document).on('click', '.btn-return', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Mark this book as returned?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, return',
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.post(`${returnUrlBase}/${id}`, {
                    _token: csrf
                }, function(res) {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 800);
                }).fail(function() {
                    toastr.error('Unable to return book.');
                });
            });
        });

        // ---------- Extend Due Date ----------
        $(document).on('click', '.btn-extend', function() {
            $('#extend_issue_id').val($(this).data('id'));
            $('#extend_due_date').val($(this).data('due'));
            $('#extendModal').modal('show');
        });

        $('#extendForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#extend_issue_id').val();

            $.post(`${updateUrlBase}/${id}`, {
                _token: csrf,
                _method: 'POST',
                due_date: $('#extend_due_date').val(),
            }, function(res) {
                toastr.success(res.message);
                $('#extendModal').modal('hide');
                setTimeout(() => location.reload(), 800);
            }).fail(function() {
                toastr.error('Unable to update due date.');
            });
        });

        // ---------- Delete ----------
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Delete this record?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: `${deleteUrlBase}/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: csrf
                    },
                    success: function(res) {
                        toastr.success(res.message);
                        issuesTable.row($(`#issue-row-${id}`)).remove().draw();
                    },
                    error: function() {
                        toastr.error('Unable to delete record.');
                    }
                });
            });
        });
    });
</script>
@endpush