@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Librarian Management', 'url' => route('librarians.index')],
    ['label' => 'Trash Bin', 'url' => '#'],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                <h5 class="mb-0 text-danger"><i class="bx bx-trash"></i> Trashed Librarians</h5>
                <a href="{{ route('librarians.index') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-arrow-back"></i> Back to List
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Librarian ID</th>
                                <th>Email</th>
                                <th>Deleted At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trashedLibrarians as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td class="text-center align-middle">
                                    <img src="{{ $item->photo_url }}" class="rounded shadow-sm p-1 border" width="45" height="45" alt="Photo">
                                </td>
                                <td>
                                    <div class="text-primary fw-bold">{{ $item->first_name }} {{ $item->last_name }}</div>
                                </td>
                                <td><span class="text-success fw-bold">{{ $item->librarian_id }}</span></td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $item->deleted_at ? $item->deleted_at->format('d M, Y') : 'N/A' }}
                                        <br>
                                        <small>{{ $item->deleted_at ? $item->deleted_at->format('h:i A') : '' }}</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Restore: POST --}}
                                        <form action="{{ route('librarians.restore', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="bx bx-refresh my-0"></i>
                                            </button>
                                        </form>
                                        {{-- Permanent delete: DELETE + SweetAlert --}}
                                        <form action="{{ route('librarians.force-delete', $item->id) }}" method="POST" class="delete-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger force-delete-btn" title="Permanent Delete">
                                                <i class="bx bx-trash my-0"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No trashed librarians found.</td>
                            </tr>
                            @endforelse
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
    $(document).on('click', '.force-delete-btn', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: 'Permanent Delete?',
            text: "This record will be deleted permanently and cannot be recovered!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.trigger('submit');
            }
        });
    });
</script>
@endpush
