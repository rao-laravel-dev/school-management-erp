@forelse($receptionists as $receptionist)
<tr>
    <td class="text-center align-middle">
        @if(!empty($receptionist->photo) && file_exists(public_path('uploads/receptionists/'.$receptionist->photo)))
        {{-- rounded-circle hata kar sirf rounded likha hai --}}
        <img src="{{ url('uploads/receptionists/'.$receptionist->photo) }}" class="rounded shadow-sm p-1 border" width="45" height="45" alt="Receptionist">
        @else
        <img src="{{ url('uploads/no_image.jpg') }}" class="rounded shadow-sm p-1 border" width="45" height="45" alt="No Image">
        @endif
    </td>

    <td>
        <div class="text-primary fw-bold" style="cursor: pointer;"
            onclick="showReceptionistDetails({{ $receptionist->id }})">
            {{ ucfirst($receptionist->first_name) }} {{ ucfirst($receptionist->last_name) }}
        </div>
    </td>

    <td>
        <span class="text-success fw-bold">{{ $receptionist->receptionist_id ?? 'N/A' }}</span>
    </td>

    <td>
        <div class="text-dark">
            <i class="bx bx-phone"></i> {{ $receptionist->phone ?? 'N/A' }}
        </div>
    </td>

    <td>
        {{ $receptionist->user->email ?? 'N/A' }}
    </td>

    <td class="text-center align-middle">
        @php
        $userStatus = $receptionist->user?->status ?? 0;

        if ($userStatus == 1) {
        $colorClass = 'success';
        $statusText = 'Active';
        } elseif ($userStatus == 2) {
        $colorClass = 'warning';
        $statusText = 'Pending';
        } else {
        $colorClass = 'danger';
        $statusText = 'Inactive';
        }
        @endphp

        @can('manage-receptionist')
        {{-- Agar permission hai to button --}}
        <button type="button" class="btn btn-sm btn-outline-{{ $colorClass }} toggle-status-btn" data-id="{{ $receptionist->id }}">
            {{ $statusText }}
        </button>
        @else
        {{-- Agar permission nahi hai to sirf badge --}}
        <span class="badge bg-{{ $colorClass }}">{{ $statusText }}</span>
        @endcan
    </td>

    <td class="text-center">
    <div class="d-flex justify-content-center gap-2">
        @can('manage-receptionist')
            {{-- Edit Button --}}
            <a href="{{ route('receptionist.edit', $receptionist->id) }}" class="btn btn-sm btn-outline-info" title="Edit">
                <i class="bx bx-edit my-0"></i>
            </a>
            
            {{-- Delete Button --}}
            <a href="{{ route('receptionist.delete', $receptionist->id) }}" class="btn btn-sm btn-outline-danger" id="delete" title="Delete">
                <i class="bx bx-trash my-0"></i>
            </a>
        @else
            {{-- Agar permission nahi hai to kuch show nahi hoga --}}
            <span class="text-muted" style="font-size: 12px;"></span>
        @endcan
    </div>
</td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center text-muted">No receptionists found.</td>
</tr>
@endforelse