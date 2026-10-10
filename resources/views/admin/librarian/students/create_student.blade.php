@extends('layouts.app')

@section('content')
    <div class="page-breadcrumb d-flex justify-content-between align-items-center mb-3">
        <h4>Add Student</h4>

        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <!-- USER INFO -->
                    <div class="col-md-6">
                        <h5>User Info</h5>

                        <!-- Name -->
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}">
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}">
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>

                        <!-- Gender (Radio) -->
                        <div class="mb-3">
                            <label>Gender</label><br>

                            <input type="radio" name="gender" value="male" {{ old('gender') == 'male' ? 'checked' : '' }}> Male

                            <input type="radio" name="gender" value="female" {{ old('gender') == 'female' ? 'checked' : '' }}>
                            Female

                            @error('gender')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Photo -->
                        <div class="row mb-3">

                            <!-- Image Input -->
                            <div class="col-md-6">
                                <label class="form-label">Student Photo</label>
                                <input type="file" name="photo" id="image"
                                    class="form-control @error('photo') is-invalid @enderror">

                                @error('photo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Image Preview -->
                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>

                                <img id="showImage" src="{{ url('uploads/no_image.jpg') }}"
                                    class="rounded-circle avatar-xl img-thumbnail float-start" width="100"
                                    alt="Student Image">
                            </div>

                        </div>
                    </div>

                    <!-- STUDENT INFO -->
                    <div class="col-md-6">
                        <h5>Student Info</h5>

                        <!-- Class -->
                        <div class="mb-3">
                            <label>Class</label>
                            <select name="class" class="form-control @error('class') is-invalid @enderror">
                                <option value="">Select Class</option>

                                @foreach($classes as $class)
                                    <option value="{{ $class }}" {{ old('class') == $class ? 'selected' : '' }}>
                                        Class {{ $class }}
                                    </option>
                                @endforeach

                            </select>
                            @error('class')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Section -->
                        <div class="mb-3">
                            <label>Section</label>
                            <select name="section" class="form-control @error('section') is-invalid @enderror">
                                <option value="">Select Section</option>

                                @foreach($sections as $section)
                                    <option value="{{ $section }}" {{ old('section') == $section ? 'selected' : '' }}>
                                        {{ $section }}
                                    </option>
                                @endforeach

                            </select>
                            @error('section')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Group -->
                        <div class="mb-3">
                            <label>Group</label>
                            <select name="group" class="form-control @error('group') is-invalid @enderror">
                                <option value="">Select Group</option>

                                @foreach($groups as $group)
                                    <option value="{{ $group }}" {{ old('group') == $group ? 'selected' : '' }}>
                                        {{ $group }}
                                    </option>
                                @endforeach

                            </select>
                            @error('group')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Admission Date -->
                        <div class="mb-3">
                            <label>Admission Date</label>
                            <input type="date" name="admission_date" class="form-control"
                                value="{{ old('admission_date', date('Y-m-d')) }}">
                        </div>

                        <!-- Father Name -->
                        <div class="mb-3">
                            <label>Father Name</label>
                            <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
                        </div>

                        <!-- Father Phone -->
                        <div class="mb-3">
                            <label>Father Phone</label>
                            <input type="text" name="father_phone" class="form-control" value="{{ old('father_phone') }}">
                        </div>
                    </div>

                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    Save Student
                </button>

            </form>

        </div>
    </div>

@endsection

{{-- 🔥 TOASTR VALIDATION ERRORS --}}
@push('scripts')
    <script>
        $(document).ready(function () {
            $('#image').change(function (e) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#showImage').attr('src', e.target.result);
                }
                reader.readAsDataURL(e.target.files[0]);
            });
        });
    </script>
@endpush