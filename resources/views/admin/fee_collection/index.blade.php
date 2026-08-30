@extends($current_layout)
@section('content')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Fees Collection</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Collect Fees</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card radius-10 mb-3">
    <div class="card-body">
        <div class="row g-3">

            {{-- FORM 1: Class + Section filter --}}
            <div class="col-12 col-lg-7">
                <form method="GET" action="{{ route('fees.collect.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-sm-5">
                        <label class="form-label mb-1" for="class_id">Class</label>
                        <select class="form-select form-select-sm" id="class_id" name="class_id">
                            <option value="">Select Class</option>
                            @foreach($schoolClasses as $class)
                            <option value="{{ $class->id }}" {{ (string)$classId === (string)$class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-5">
                        <label class="form-label mb-1" for="section_id">Section</label>
                        <select class="form-select form-select-sm" id="section_id" name="section_id">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ (string)$sectionId === (string)$section->id ? 'selected' : '' }}>
                                {{ $section->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
                    </div>
                </form>
            </div>

            {{-- FORM 2: Keyword search (All Classes) --}}
            <div class="col-12 col-lg-5">
                <form method="GET" action="{{ route('fees.collect.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-sm-9">
                        <label class="form-label mb-1" for="keyword">Search By Keyword (All Classes)</label>
                        <input type="text" class="form-control form-control-sm" id="keyword" name="keyword" value="{{ $keyword }}"
                            placeholder="Name, Admission No, Roll No, Phone...">
                    </div>
                    <div class="col-12 col-sm-3">
                        <button type="submit" class="btn btn-success btn-sm w-100">Search</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="card radius-10">
    <div class="card-header bg-transparent">
        <h5 class="mb-0 text-primary">Student List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="studentListTable" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Admission No</th>
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Father Name</th>
                        <th>Date of Birth</th>
                        <th>Mobile No</th>
                        <th>Fee Balance</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $student->enrollments->first()->schoolClass->name ?? '-' }}</td>
                        <td>{{ $student->enrollments->first()->section->name ?? '-' }}</td>
                        <td>{{ $student->admission_no }}</td>
                        <td>{{ $student->enrollments->first()->roll_no ?? '-' }}</td>
                        <td>{{ $student->full_name }}</td>
                        <td>{{ $student->parent->father_name ?? '-' }}</td>
                        <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $student->parent->father_phone ?? $student->parent->guardian_phone ?? '-' }}</td>
                        <td>
                            @if($student->fee_balance > 0)
                            <span class="badge bg-danger">{{ number_format($student->fee_balance, 2) }}</span>
                            @else
                            <span class="badge bg-success">Clear</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('fees.collect.show', $student->id) }}" class="btn btn-success btn-sm">
                                <i class="bx bx-wallet"></i> Collect Fees
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $('#studentListTable').DataTable({
        "pageLength": 50,
        "ordering": true,
        "language": {
            "search": "Search:",
            "emptyTable": "No students found. Please select a class or search by keyword."
        }
    });
    $(function() {
        $('#class_id').on('change', function() {
            const classId = $(this).val();
            const $section = $('#section_id');
            $section.html('<option value="">Select Section</option>');

            if (!classId) return;

            $.get("{{ url('fees/collect/get-sections') }}/" + classId, function(res) {
                res.forEach(function(sec) {
                    $section.append('<option value="' + sec.id + '">' + sec.name + '</option>');
                });
            });
        });
    });
</script>
@endpush