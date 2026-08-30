@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Discount Policies</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Discount Policies</h5>
                @can('manage-discount-policies')
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#discountPolicyAdd">
                    Add
                </button>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="discountPolicyTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Policy Type</th>
                                <th>Trigger</th>
                                <th>Discount</th>
                                <th>Applies On</th>
                                <th>Status</th>
                                @can('manage-discount-policies')
                                <th width="120">Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $policy->name }}</td>
                                @php
                                $typeColors = [
                                    'category' => 'bg-success',
                                    'sibling'  => 'bg-info',
                                    'other'    => 'bg-secondary',
                                ];
                                @endphp
                                <td>
                                    <span class="badge {{ $typeColors[$policy->policy_type] ?? 'bg-info' }} text-capitalize">
                                        {{ $policy->policy_type }}
                                    </span>
                                    @if($policy->policy_type === 'category' && $policy->category)
                                        <div class="small text-muted mt-1">{{ $policy->category->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $policy->trigger_value ?? '--' }}</td>
                                <td>{{ number_format($policy->discount_value, 2) }}{{ $policy->discount_type === 'percentage' ? '%' : ' PKR' }}</td>
                                <td>{{ $policy->feeType->name ?? 'All Fees' }}</td>
                                <td>
                                    @can('manage-discount-policies')
                                    <a href="javascript:;" class="toggle-status-btn" data-id="{{ $policy->id }}">
                                        <span class="badge {{ $policy->status ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $policy->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </a>
                                    @else
                                    <span class="badge {{ $policy->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $policy->status ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>
                                @can('manage-discount-policies')
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{ '#discountPolicyEdit-'.$policy->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <a href="{{ route('discount_policies.delete', $policy->id) }}" class="btn btn-outline-danger btn-sm px-3 delete-btn">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Modals File --}}
@include('admin.discount_policies.modals')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#discountPolicyTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Policy:"
            }
        });

        // Delete Confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let link = $(this).attr("href");
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });

        // Toggle Status
        $(document).on('click', '.toggle-status-btn', function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{ route('discount_policies.toggle_status', ':id') }}".replace(':id', id),
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) location.reload();
                }
            });
        });

        @if(Session::has('success'))
        toastr.success("{{ Session::get('success') }}");
        @endif
    });
</script>
@endpush