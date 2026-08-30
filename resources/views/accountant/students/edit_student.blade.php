@extends('layouts.app')

@section('content')

    <div class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <h4>Edit Student</h4>

        <a href="{{ route('adminstudents.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('adminstudents.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <!-- USER INFO -->
                    <div class="col-md-6">
                        <h5>User Info</h5>

                        <!-- Name -->
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $student->user->name) }}">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $student->user->email) }}">
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $student->user->phone) }}">
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control"
                                value="{{ old('address', $student->user->address) }}">
                        </div>

                        <!-- Gender -->
                        <div class="mb-3">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="male" {{ $student->user->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $student->user->gender == 'female' ? 'selected' : '' }}>Female
                                </option>
                            </select>
                        </div>

                        <!-- Photo -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Photo</label>
                                <input type="file" name="photo" class="form-control" id="image">
                            </div>

                            <div class="col-md-6">
                                <label></label>
                                <img id="showImage" src="{{ $student->user->photo
        ? asset('uploads/students/' . $student->user->photo)
        : asset('uploads/no_image.jpg') }}" width="80">
                            </div>
                        </div>

                    </div>

                    <!-- STUDENT INFO -->
                    <div class="col-md-6">
                        <h5>Student Info</h5>

                        <div class="mb-3">
                            <label>Class</label>
                            <select name="class" class="form-control @error('class') is-invalid @enderror">
                                <option value="">Select Class</option>

                                @foreach($classes as $class)
                                    <option value="{{ $class }}" {{ old('class', $student->class) == $class ? 'selected' : '' }}>
                                        Class {{ $class }}
                                    </option>
                                @endforeach

                            </select>

                            @error('class')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Section</label>
                            <select name="section" class="form-control @error('section') is-invalid @enderror">
                                <option value="">Select Section</option>

                                @foreach($sections as $section)
                                    <option value="{{ $section }}" {{ old('section', $student->section) == $section ? 'selected' : '' }}>
                                        {{ $section }}
                                    </option>
                                @endforeach

                            </select>

                            @error('section')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Group</label>
                            <select name="group" class="form-control @error('group') is-invalid @enderror">
                                <option value="">Select Group</option>

                                @foreach($groups as $group)
                                    <option value="{{ $group }}" {{ old('group', $student->group) == $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach

                            </select>

                            @error('group')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label>Admission Date</label>
                            <input type="date" name="admission_date" class="form-control"
                                value="{{ $student->admission_date }}">
                        </div>

                        <div class="mb-3">
                            <label>Father Name</label>
                            <input type="text" name="father_name" class="form-control"
                                value="{{ old('father_name', $student->father_name) }}">
                        </div>

                        <div class="mb-3">
                            <label>Father Phone</label>
                            <input type="text" name="father_phone" class="form-control"
                                value="{{ old('father_phone', $student->father_phone) }}">
                        </div>

                    </div>

                </div>

                <button type="submit" class="btn btn-success mt-3">
                    Update Student
                </button>

            </form>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            $('#image').change(function (e) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#showImage').attr('src', e.target.result);
                }

                reader.readAsDataURL(e.target.files[0]);
            });

        });
    </script>
@endpush