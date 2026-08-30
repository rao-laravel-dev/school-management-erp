@forelse($accountants as $accountant)
<tr>
    <td class="text-center align-middle">
        <img src="{{ $accountant->photo && file_exists(public_path('uploads/accountants/'.$accountant->photo)) 
                ? asset('uploads/accountants/'.$accountant->photo) : asset('uploads/no_image.jpg') }}"
            class="rounded shadow-sm p-1 border" style="width: 45px; height: 45px; object-fit: cover;" alt="Accountant">
    </td>
    <td>
        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-primary view-accountant-btn" data-id="{{ $accountant->id }}">
            {{ $accountant->first_name }} {{ $accountant->last_name }}
        </a>
    </td>

    <td>
        <span class="text-success fw-bold">{{ $accountant->accountant_id ?? 'N/A' }}</span>
    </td>
    <td>
        <div class="text-dark">
            <i class="bx bx-phone"></i> {{ $accountant->phone }}
        </div>
    </td>
    <td>{{ $accountant->email }}</td>
    <td class="text-center align-middle">
        @php
            $status = $accountant->user->status ?? 0;
        @endphp

        {{-- Status Toggle: Sirf wahi user change kar sake jispe 'manage-accountant' ki permission ho --}}
        @can('manage-accountant')
            @if($status == 1)
                <button type="button" class="btn btn-sm btn-outline-success toggle-accountant-status" data-id="{{ $accountant->id }}">Active</button>
            @elseif($status == 2)
                <button type="button" class="btn btn-sm btn-outline-warning toggle-accountant-status" data-id="{{ $accountant->id }}"><i class="fas fa-clock me-1"></i>Pending</button>
            @else
                <button type="button" class="btn btn-sm btn-outline-danger toggle-accountant-status" data-id="{{ $accountant->id }}">Inactive</button>
            @endif
        @else
            <span class="badge {{ $status == 1 ? 'bg-success' : ($status == 2 ? 'bg-warning' : 'bg-danger') }}">
                {{ $status == 1 ? 'Active' : ($status == 2 ? 'Pending' : 'Inactive') }}
            </span>
        @endcan
    </td>

    <td>
        <div class="d-flex gap-2">
            @can('access-accountant')
                <a href="{{ route('accountant.edit', $accountant->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-edit"></i>
                </a>
            @endcan

            @can('manage-accountant')
                <a href="{{ route('accountant.delete', $accountant->id) }}" class="btn btn-sm btn-outline-danger" id="delete-btn">
                    <i class="bx bx-trash"></i>
                </a>
            @endcan
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">No accountants found.</td>
</tr>
@endforelse

{{-- Accountant Details Modal --}}
<div class="modal fade" id="accountantDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Accountant Full Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="accountantModalBody">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>