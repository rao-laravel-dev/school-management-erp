@extends($current_layout)
@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Students Information', 'url' => '#'],
    ['label' => 'Bulk Import', 'url' => route('student_import.index')],
]" />

<div class="container-fluid px-4 py-3">

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
            <h5 class="mb-0 text-primary">Bulk Student Import</h5>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('students.create') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Admission Form
                </a>
                <a href="{{ route('student_import.download_template') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-download me-1"></i> Download Sample Template
                </a>
            </div>
        </div>

        <div class="card-body p-4">

            @if (session('import_errors'))
                <div class="alert alert-danger">
                    <strong>Import failed — {{ count(session('import_errors')) }} issue(s) found. Nothing was imported:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- INSTRUCTIONS SECTION (Separate Cards) --}}
            <div class="row mb-4">
                {{-- English Instructions Card --}}
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card border h-100 shadow-none bg-light">
                        <div class="card-body">
                            <h6 class="text-primary mb-2">Instructions</h6>
                            <hr class="text-primary opacity-50 mb-3" style="height: 2px;">
                            <ul class="mb-0 ps-3 text-secondary" style="font-size: 13px;">
                                <li class="mb-1">File must be in Excel (.xlsx) format — the first row must have exactly the same column headers as in the sample template.</li>
                                <li class="mb-1">Date fields (Date Of Birth, Admission Date) must be in <strong class="text-primary">YYYY-MM-DD</strong> format (e.g. 2015-04-08).</li>
                                <li class="mb-1">Gender must be either <strong class="text-primary">Male</strong> or <strong class="text-primary">Female</strong>.</li>
                                <li class="mb-1">Category Name and House Name must exactly match the spelling already saved on the Student Category / Student House pages.</li>
                                <li class="mb-1">Guardian Relation must be <strong class="text-primary">father</strong>, <strong class="text-primary">mother</strong>, or <strong class="text-primary">other</strong> — if "other" is selected, Guardian Name and Guardian Phone are required.</li>
                                <li class="mb-1">Father CNIC format: <strong class="text-primary">42101-1234567-8</strong> — must be unique.</li>
                                <li class="mb-1">Class Name / Section Name must already exist in the system — if the name doesn't match, the <strong class="text-primary">entire file will be rejected</strong>.</li>
                                <li class="mb-1">A duplicate Admission No (within the file or already in the system) will also cause the <strong class="text-primary">entire file to be rejected</strong>.</li>
                                <li class="mb-1">The Roll No. column is not required — the system will auto-generate it.</li>
                                <li>Every imported student and guardian will get the default login password <strong class="text-primary">Welcome@123</strong>.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Roman Urdu Instructions Card --}}
                <div class="col-md-6">
                    <div class="card border h-100 shadow-none bg-light">
                        <div class="card-body">
                            <h6 class="text-primary mb-2">Hidayat</h6>
                            <hr class="text-primary opacity-50 mb-3" style="height: 2px;">
                            <ul class="mb-0 ps-3 text-secondary" style="font-size: 13px;">
                                <li class="mb-1">File Excel (.xlsx) format mein honi chahiye — pehli row exactly wahi column headers honi chahiye jo sample template mein hain.</li>
                                <li class="mb-1">Date fields (Date Of Birth, Admission Date) ka format <strong class="text-primary">YYYY-MM-DD</strong> hona chahiye (e.g. 2015-04-08).</li>
                                <li class="mb-1">Gender: <strong class="text-primary">Male</strong> ya <strong class="text-primary">Female</strong> hi likhein.</li>
                                <li class="mb-1">Category Name aur House Name — bilkul wahi spelling use karein jo Student Category / Student House page pe already bani hui hai.</li>
                                <li class="mb-1">Guardian Relation: <strong class="text-primary">father</strong>, <strong class="text-primary">mother</strong> ya <strong class="text-primary">other</strong> — agar "other" select karein to Guardian Name aur Guardian Phone bharna zaroori hai.</li>
                                <li class="mb-1">Father CNIC format: <strong class="text-primary">42101-1234567-8</strong> — unique hona chahiye.</li>
                                <li class="mb-1">Class Name / Section Name pehle se system mein assign honi chahiye — naam match na hua to <strong class="text-primary">poori file reject</strong> ho jayegi.</li>
                                <li class="mb-1">Duplicate Admission No (file k andar ya system mein already maujood) hone par bhi <strong class="text-primary">poori file reject</strong> ho jayegi.</li>
                                <li class="mb-1">Roll No. column ki zaroorat nahi — system khud auto-generate karega.</li>
                                <li>Har imported student aur guardian ka default login password <strong class="text-primary">Welcome@123</strong> set hoga.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUMN PREVIEW --}}
            <div class="my-4">
                <h6 class="text-primary mb-2">Sample Data Structure Preview</h6>
                <hr class="text-primary opacity-50 mb-3" style="height: 2px;">
                <div class="table-responsive border mb-4">
                    <table class="table table-bordered table-sm mb-0" style="white-space: nowrap; font-size: 12px;">
                        <thead class="table-light">
                            <tr>
                                <th>Admission No*</th><th>Class Name*</th><th>Section Name*</th><th>Group Name</th>
                                <th>First Name*</th><th>Last Name*</th><th>Gender*</th><th>Date Of Birth*</th>
                                <th>Admission Date*</th><th>Category Name</th><th>Religion</th><th>Caste</th>
                                <th>Blood Group</th><th>House Name</th><th>Father Name*</th><th>Father Phone*</th>
                                <th>Father CNIC*</th><th>Mother Name</th><th>Mother Phone</th><th>Mother CNIC</th>
                                <th>Guardian Relation*</th><th>Guardian Name</th><th>Guardian Phone</th>
                                <th>Guardian Email</th><th>Guardian Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-muted">
                                <td>2024-0001</td><td>Class 5</td><td>A</td><td></td>
                                <td>Ahmed</td><td>Khan</td><td>Male</td><td>2014-05-10</td>
                                <td>2024-04-01</td><td>General</td><td>Islam</td><td></td>
                                <td>O+</td><td>Red</td><td>Imran Khan</td><td>03001234567</td>
                                <td>42101-1234567-8</td><td>Sara Khan</td><td>03007654321</td><td></td>
                                <td>father</td><td></td><td></td><td></td><td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- UPLOAD FORM --}}
            <div class="mt-4 pt-2">
                <h6 class="text-primary mb-2">Upload Excel File</h6>
                <hr class="text-primary opacity-50 mb-3" style="height: 2px;">
                
                <form action="{{ route('student_import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
    <label class="form-label text-secondary" for="excel-file">Select Excel file (.xlsx)</label>
    <input type="file" name="file" id="excel-file"
           class="form-control form-control-sm @error('file') is-invalid @enderror"
           accept=".xlsx,.xls">
    @error('file')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-sm px-4">Upload &amp; Import</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        @if (session('toastr-success'))
            toastr.success("{{ session('toastr-success') }}");
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error("{{ $error }}");
            @endforeach
        @endif
    });
</script>
@endpush