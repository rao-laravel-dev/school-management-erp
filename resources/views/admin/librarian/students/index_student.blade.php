@extends('layouts.app')

@section('content')

<!-- START PAGE WRAPPER -->
<!-- breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Students</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admindashboard') }}">
                                <i class="bx bx-home-alt"></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Students</li>
                        <li class="breadcrumb-item active">All Students</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- end breadcrumb -->
<hr>
        <!-- ADD BUTTON -->
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">All Students</h5>

    @can('add students')
        <a href="{{ route('adminstudents.create') }}" class="btn btn-primary">
            + Add Student
        </a>
    @endcan
</div>

        <hr/>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">

                    <table id="example" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Roll No</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Group</th>
                                <th>Phone</th>
                                <th>Manage</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($students as $key => $student)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <!-- Photo -->
                                    <td>
    <img src="{{ $student->user && $student->user->photo 
        ? asset('uploads/students/' . $student->user->photo) 
        : asset('uploads/no_image.jpg') }}" 
        style="width:70px; height:40px;">
</td>

                                    <!-- Info -->
                                    <td>{{ $student->user->name ?? '-' }}</td>
                                    <td>{{ $student->roll_number ?? '-' }}</td>
                                    <td>{{ $student->class ?? '-' }}</td>
                                    <td>{{ $student->section ?? '-' }}</td>
                                    <td>{{ $student->group ?? '-' }}</td>
                                    <td>{{ $student->user->phone ?? '-' }}</td>

                                    <!-- Actions -->
                                    <td>
    @can('edit students')
        <a href="{{ route('adminstudents.edit', $student->id) }}" 
           class="btn btn-info btn-sm me-1">
            Edit
        </a>
    @endcan

    @can('delete students')
        <a href="{{ route('adminstudents.delete', $student->id) }}" 
           class="btn btn-danger btn-sm deleteBtn">
            Delete
        </a>
    @endcan
</td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Roll No</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Group</th>
                                <th>Phone</th>
                                <th>Manage</th>
                            </tr>
                        </tfoot>

                    </table>

                </div>
            </div>
        </div>

        <hr/>
<!-- END PAGE WRAPPER -->

@endsection

@push('scripts')
<script>
$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    let link = $(this).attr('href');

    Swal.fire({
        title: 'Are you sure?',
        text: "This student will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = link;
        }
    });
});
</script>
@endpush