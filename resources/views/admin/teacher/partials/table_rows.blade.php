@forelse($teachers as $teacher)
<tr>
    <td class="text-center align-middle">
    @php
        $photoPath = $teacher->photo;
        $imageUrl = ($photoPath && \Storage::disk('public')->exists('teacher_images/' . $photoPath))
            ? asset('storage/teacher_images/' . $photoPath)
            : asset('uploads/no_image.jpg');
    @endphp

    <img src="{{ $imageUrl }}" 
         class="rounded shadow-sm p-1 border" 
         style="width: 45px; height: 45px; object-fit: cover;" 
         alt="Teacher"
         onerror="this.onerror=null; this.src='{{ asset('uploads/no_image.jpg') }}';">
</td>
    <td>
    <a href="javascript:void(0)" class="text-decoration-none fw-bold text-primary view-teacher-btn" data-id="{{ $teacher->id }}">
        {{ $teacher->first_name }} {{ $teacher->last_name }}
    </a>
</td>


    <td>
    <span class="text-success fw-bold">{{ $teacher->teacher_id ?? 'N/A' }}</span>
</td>
    <td>
        <div class="text-dark">
            <i class="bx bx-phone"></i> {{ $teacher->phone }}
        </div>
    </td>
    <td>{{ $teacher->email }}</td>
    <td class="text-center align-middle">
    @php
        $status = $teacher->user->status ?? 0;
    @endphp

    {{-- Status Toggle: Sirf wahi user change kar sake jispe 'manage-teacher' ki permission ho --}}
    @can('manage-teacher')
        @if($status == 1)
            <button type="button" class="btn btn-sm btn-outline-success toggle-teacher-status" data-id="{{ $teacher->id }}">Active</button>
        @elseif($status == 2)
            <button type="button" class="btn btn-sm btn-outline-warning toggle-teacher-status" data-id="{{ $teacher->id }}"><i class="fas fa-clock me-1"></i>Pending</button>
        @else
            <button type="button" class="btn btn-sm btn-outline-danger toggle-teacher-status" data-id="{{ $teacher->id }}">Inactive</button>
        @endif
    @else
        {{-- Agar permission nahi hai, toh sirf status ka label dikhayein (bina button ke) --}}
        <span class="badge {{ $status == 1 ? 'bg-success' : ($status == 2 ? 'bg-warning' : 'bg-danger') }}">
            {{ $status == 1 ? 'Active' : ($status == 2 ? 'Pending' : 'Inactive') }}
        </span>
    @endcan
</td>

<td>
    <div class="d-flex gap-2">
        {{-- Edit ke liye 'access-teacher' ya 'manage-teacher' dono chal sakte hain --}}
        @can('access-teacher')
            <a href="{{ route('teacher.edit', $teacher->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-edit"></i>
            </a>
        @endcan

        {{-- Delete ke liye sirf 'manage-teacher' --}}
        @can('manage-teacher')
            <a href="{{ route('teacher.delete', $teacher->id) }}" class="btn btn-sm btn-outline-danger" id="delete-btn">
                <i class="bx bx-trash"></i>
            </a>
        @endcan
    </div>
</td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">No teachers found.</td>
</tr>
@endforelse

<div class="modal fade" id="teacherDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Teacher Full Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="teacherModalBody">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>