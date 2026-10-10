@forelse($enrollments as $enrollment)
<tr>
    <td class="text-center align-middle">{{ $loop->iteration }}</td>
    <td class="text-center align-middle">
        {{-- FIXED: Yahan $student ke bajaye $enrollment->student use karein --}}
        <img src="{{ $enrollment->student->photo_url }}" class="rounded shadow-sm p-1 border" width="45" height="45" alt="Student">
    </td>

    <td>
        <div class="fw-bold">
            <a href="javascript:;" class="view-student-btn" data-id="{{ $enrollment->student->id }}">
                {{ ucfirst($enrollment->student->first_name) }} {{ ucfirst($enrollment->student->last_name) }}
            </a>
        </div>
        <small class="text-muted">User: {{ $enrollment->student->user->username ?? 'N/A' }}</small>
    </td>

    <td>
        <div class="fw-bold">{{ $enrollment->student->parent->father_name ?? 'N/A' }}</div>
        <small class="text-muted">User: {{ $enrollment->student->parent->user->username ?? 'N/A' }}</small>
    </td>

    <td>
        <div class="text-dark fw-bold">
            <i class="bx bx-phone"></i> {{ $enrollment->student->parent->father_phone ?? 'N/A' }}
        </div>
    </td>

    <td class="fw-bold text-primary">
        {{ $enrollment->student->admission_no }}
    </td>

    <td>
        <span class="badge bg-light-dark text-dark border w-100">
            {{ $enrollment->roll_no ?? 'N/A' }}
        </span>
    </td>

    <td>
        <span class="badge bg-light-info text-info border border-info px-2">
            {{ $enrollment->schoolClass->name ?? 'N/A' }}
        </span>
    </td>

    <td class="text-center align-middle">
        @php
        // Logic ko pehle define kar liya taake dono jagah same color/text aaye
        $status = $enrollment->student->status;
        $colorClass = ($status == 1) ? 'success' : (($status == 2) ? 'warning' : 'danger');
        $statusText = ($status == 1) ? 'Active' : (($status == 2) ? 'Pending' : 'Inactive');
        @endphp

        {{-- Admin (ya jiske paas permission hai) ko button dikhega --}}
        @can('manage-students')
        @if($status == 2)
        <button type="button" class="btn btn-sm btn-outline-{{ $colorClass }} toggle-status-pending" data-id="{{ $enrollment->student->id }}" data-url="{{ route('students.approve', $enrollment->student->id) }}">
            <i class="fas fa-clock me-1"></i> {{ $statusText }}
        </button>
        @else
        <button type="button" class="btn btn-sm btn-outline-{{ $colorClass }} toggle-status-approved" data-id="{{ $enrollment->student->id }}" data-url="{{ route('students.status', $enrollment->student->id) }}">
            {{ $statusText }}
        </button>
        @endif

        {{-- Receptionist (ya jiske paas permission nahi hai) ko badge dikhega --}}
        @else
        <span class="badge bg-{{ $colorClass }}">
            {{ $statusText }}
        </span>
        @endcan
    </td>

    <td class="text-center align-middle">
        <div class="d-flex justify-content-center gap-1">
            {{-- Edit Button --}}
            @can('manage-students')
            <a href="{{ route('students.edit', $enrollment->student->id) }}"
                class="btn btn-sm btn-outline-primary px-2" title="Edit">
                <i class="bx bx-edit"></i>
            </a>
            @endcan

            {{-- Delete Button --}}
            @can('manage-students')
            <a href="{{ route('students.delete', $enrollment->student->id) }}"
                class="btn btn-sm btn-outline-danger px-2 delete-btn" title="Delete">
                <i class="bx bx-trash"></i>
            </a>
            @endcan
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center text-muted">No students registered yet.</td>
</tr>
@endforelse