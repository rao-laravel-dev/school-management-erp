{{-- Khali list par koi row nahi; DataTables "No librarians found." dikhata hai --}}
@foreach($librarians as $librarian)
<tr>
    <td class="text-center align-middle">
        <img src="{{ $librarian->photo_url }}"
             class="rounded shadow-sm p-1 border"
             style="width: 45px; height: 45px; object-fit: cover;"
             alt="Librarian"
             onerror="this.onerror=null; this.src='{{ asset('uploads/no_image.jpg') }}';">
    </td>
    <td>
        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-primary view-librarian-btn" data-id="{{ $librarian->id }}">
            {{ $librarian->first_name }} {{ $librarian->last_name }}
        </a>
    </td>
    <td>
        <span class="text-success fw-bold">{{ $librarian->librarian_id ?: '-' }}</span>
    </td>
    <td>
        <div class="text-dark">
            <i class="bx bx-phone"></i> {{ $librarian->phone ?: '-' }}
        </div>
    </td>
    <td>{{ $librarian->email }}</td>
    <td class="text-center align-middle">
        @php
            $status = $librarian->user->status ?? 0;
        @endphp

        {{-- Status Toggle: sirf 'manage-librarian' wala change kar sake --}}
        @can('manage-librarian')
            @if($status == 1)
                <button type="button" class="btn btn-sm btn-outline-success toggle-librarian-status" data-id="{{ $librarian->id }}">Active</button>
            @elseif($status == 2)
                <button type="button" class="btn btn-sm btn-outline-warning toggle-librarian-status" data-id="{{ $librarian->id }}">Pending</button>
            @else
                <button type="button" class="btn btn-sm btn-outline-danger toggle-librarian-status" data-id="{{ $librarian->id }}">Inactive</button>
            @endif
        @else
            <span class="badge {{ $status == 1 ? 'bg-success' : ($status == 2 ? 'bg-warning' : 'bg-danger') }}">
                {{ $status == 1 ? 'Active' : ($status == 2 ? 'Pending' : 'Inactive') }}
            </span>
        @endcan
    </td>

    @can('manage-librarian')
    <td>
        <div class="d-flex gap-2">
            <a href="{{ route('librarians.edit', $librarian->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="bx bx-edit"></i>
            </a>

            {{-- Trash: DELETE request (GET nahi) --}}
            <form action="{{ route('librarians.delete', $librarian->id) }}" method="POST" class="delete-form d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Move to Trash">
                    <i class="bx bx-trash"></i>
                </button>
            </form>
        </div>
    </td>
    @endcan
</tr>
@endforeach
