@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Salary Slips</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Salary Slips</li>
            </ol>
        </nav>
    </div>
    
</div>

<div class="row mb-3">
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-primary">
            <div class="card-body py-2">
                <small class="text-muted">Total Slips</small>
                <h4 class="mb-0 text-primary">{{ $summary['total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-warning">
            <div class="card-body py-2">
                <small class="text-muted">Pending</small>
                <h4 class="mb-0 text-warning">{{ $summary['pending'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-success">
            <div class="card-body py-2">
                <small class="text-muted">Paid</small>
                <h4 class="mb-0 text-success">{{ $summary['paid'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-3 border-0 border-info">
            <div class="card-body py-2">
                <small class="text-muted">Total Amount</small>
                <h4 class="mb-0 text-info">{{ number_format($summary['total_amount'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Salary Slips - {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h5>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border">
                <i class="bx bx-calendar"></i> {{ now()->format('d-m-Y') }}
            </span>
            @can('access-salary-slips')
            <a href="{{ route('salary_slips.create', ['role' => $role, 'month' => $month]) }}" class="btn btn-primary btn-sm">
                 Generate
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body">

        <form method="GET" action="{{ route('salary_slips.index') }}" class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}" onchange="this.form.submit()">
            </div>
            <div class="col-6 col-md-3">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Staff</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ $role == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
        </form>

        @if(session('skipped') && count(session('skipped')) > 0)
        <div class="alert alert-warning" role="alert">
            <i class="bx bx-error"></i>
            <strong>{{ count(session('skipped')) }} staff skipped:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('skipped') as $s)
                <li>{{ $s['user'] }} — {{ $s['reason'] }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slips as $slip)
                    <tr>
                        <td>{{ $slip->user->name }}</td>
                        <td>{{ ucfirst($slip->user->roles->first()->name ?? '-') }}</td>
                        <td>{{ number_format($slip->net_salary, 2) }}</td>
                        <td>
                            <span class="badge {{ $slip->status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($slip->status) }}
                            </span>
                        </td>
                        <td>{{ $slip->payment_date?->format('d-m-Y') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('salary_slips.show', $slip->id) }}" class="btn btn-sm btn-outline-success">
                                <i class="bx bx-show"></i> View
                            </a>
                            @if($slip->status == 'pending')
                            <a href="{{ route('salary_slips.edit', $slip->id) }}" class="btn btn-sm btn-outline-primary px-3">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete px-3" data-id="{{ $slip->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="alert alert-info mb-0" role="alert">
                                <i class='bx bx-info-circle'></i> No salary slips found for {{ \Carbon\Carbon::parse($month)->format('F Y') }}. Try selecting a different month or generate new slips.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $slips->appends(request()->query())->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
        toastr.error("{{ session('error') }}");
        @endif
        @if(session('skipped') && count(session('skipped')) > 0)
        @foreach(session('skipped') as $s)
        toastr.warning("{{ $s['user'] }}: {{ $s['reason'] }}");
        @endforeach
        @endif

        @if($errors->any())
        toastr.error("{{ $errors->first() }}", 'Validation Error');
        @endif

        $('.btn-delete').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Delete Salary Slip?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        method: 'POST',
                        action: '/salary-slips/' + id
                    });
                    form.append('@csrf');
                    form.append('@method("DELETE")');
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>
@endpush