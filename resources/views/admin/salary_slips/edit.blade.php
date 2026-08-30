@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Edit Salary Slip</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('salary_slips.index') }}">Salary Slips</a></li>
                <li class="breadcrumb-item"><a href="{{ route('salary_slips.show', $slip->id) }}">{{ $slip->user->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <span class="badge bg-light text-dark border">
            <i class="bx bx-calendar"></i> {{ now()->format('d-m-Y') }}
        </span>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header bg-transparent">
        <h5 class="mb-0">Edit Salary Slip — {{ $slip->user->name }} ({{ \Carbon\Carbon::parse($slip->month)->format('F Y') }})</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('salary_slips.update', $slip->id) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')

            <div class="alert alert-info text-danger" role="alert">
                <i class="bx bx-info-circle"></i>
                <strong>Note:</strong> You are editing a <b>pending</b> salary slip. Only Allowance and Manual Deduction can be changed here — Attendance Deduction and Advance Deduction are auto-calculated and locked.
            </div>

            <div class="row mb-3">
                <div class="col-12 col-md-6 mb-3 mb-md-0">
                    <label class="form-label">Allowance <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="allowance"
                        value="{{ old('allowance', $slip->allowance) }}"
                        class="form-control form-control-sm @error('allowance') is-invalid @enderror" required>
                    @error('allowance')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Manual Deduction (fine, tax, etc.) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="manual_deduction"
                        value="{{ old('manual_deduction', $slip->manual_deduction) }}"
                        class="form-control form-control-sm @error('manual_deduction') is-invalid @enderror" required>
                    @error('manual_deduction')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

           

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bx bx-check"></i> Update
                </button>
                <a href="{{ route('salary_slips.show', $slip->id) }}" class="btn btn-secondary btn-sm px-4">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        @if($errors->any())
    toastr.error("{{ $errors->first() }}", 'Validation Error');
@endif

        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Update Salary Slip?',
                text: "This will recalculate the net salary based on the new values.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, Update'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush