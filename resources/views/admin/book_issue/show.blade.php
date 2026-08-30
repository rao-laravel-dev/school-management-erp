@extends($current_layout)

@section('title', 'Book Issue Details')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Library', 'url' => '#'],
    ['label' => 'Book Issue', 'url' => route('book_issue.index')],
    ['label' => 'Details', 'url' => '#'],
]" />

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Book Issue Details</h4>
        <a href="{{ route('book_issue.index') }}" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back"></i> Back to List
        </a>
    </div>

    <div class="row">
        {{-- LEFT: Member Profile Card --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center bg-primary bg-gradient text-white rounded-top">
                    @php
                    if ($memberDetail) {
                    $photoUrl = ($memberDetail->photo && file_exists(public_path('uploads/students/' . $memberDetail->photo)))
                    ? asset('uploads/students/' . $memberDetail->photo)
                    : asset('uploads/no_image.jpg');
                    } elseif ($staffDetail && $staffDetail->photo) {
                    $photoUrl = asset('storage/' . $staffDetail->photo);
                    } else {
                    $photoUrl = asset('uploads/no_image.jpg');
                    }
                    @endphp
                    <img src="{{ $photoUrl }}"
                        alt="{{ $issue->libraryMember->member_name }}"
                        class="rounded-circle border border-3 border-white mb-2"
                        style="width:100px;height:100px;object-fit:cover;">
                        
                    <h5 class="mb-0">{{ $issue->libraryMember->member_name }}</h5>
                    <span class="badge bg-light text-dark mt-1">{{ ucfirst($issue->libraryMember->member_type) }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Library Card</td>
                            <td class="fw-bold text-success">{{ $issue->libraryMember->library_card_no ?? '-' }}</td>
                        </tr>

                        @if($issue->libraryMember->member_type === 'student' && $memberDetail)
                        <tr>
                            <td class="text-muted">Admission No</td>
                            <td class="fw-semibold">{{ $memberDetail->admission_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Father Name</td>
                            <td>{{ $memberDetail->parent?->father_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">DOB</td>
                            <td>{{ $memberDetail->date_of_birth ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Gender</td>
                            <td>{{ ucfirst($memberDetail->gender ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Guardian Phone</td>
                            <td>{{ $memberDetail->parent?->guardian_phone ?? $memberDetail->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Blood Group</td>
                            <td><span class="badge bg-danger">{{ $memberDetail->blood_group ?? '-' }}</span></td>
                        </tr>
                        @endif

                        @if($issue->libraryMember->member_type === 'staff' && $staffDetail)
                        <tr>
                            <td class="text-muted">Staff ID</td>
                            <td class="fw-semibold">{{ $staffDetail->{$staffType . '_id'} ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Role</td>
                            <td><span class="badge bg-info text-dark">{{ ucfirst($staffType) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Father Name</td>
                            <td>{{ $staffDetail->father_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">DOB</td>
                            <td>{{ $staffDetail->dob ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Gender</td>
                            <td>{{ ucfirst($staffDetail->gender ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td>{{ $staffDetail->phone ?? '-' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Student: Category / House / Scholarship --}}
            @if($issue->libraryMember->member_type === 'student' && $memberDetail)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-warning bg-gradient text-dark">
                    <i class="bx bx-medal"></i> Category, House & Scholarship
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Category</td>
                            <td>
                                @if($memberDetail->category)
                                <span class="badge bg-secondary">{{ $memberDetail->category->name }}</span>
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">House</td>
                            <td>
                                @if($memberDetail->house)
                                <span class="badge" style="background-color: {{ $memberDetail->house->color ?? '#6c757d' }};">
                                    {{ $memberDetail->house->name }}
                                </span>
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Scholar</td>
                            <td>
                                @if($discount)
                                <span class="badge bg-success">Yes — {{ $discount->discountPolicy->name ?? '' }}</span>
                                @else
                                <span class="badge bg-light text-dark">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            {{-- Staff: Salary --}}
            @if($issue->libraryMember->member_type === 'staff' && $staffDetail && $staffDetail->salary)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-success bg-gradient text-white">
                    <i class="bx bx-money"></i> Salary Info
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Basic Salary</td>
                            <td class="fw-bold">{{ number_format($staffDetail->salary->basic_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Allowance</td>
                            <td>{{ number_format($staffDetail->salary->allowance, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deduction</td>
                            <td>{{ number_format($staffDetail->salary->deduction, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Academic/Employment + Book/Issue Info --}}
        <div class="col-lg-8">

            @if($issue->libraryMember->member_type === 'student' && $enrollment)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-info bg-gradient text-white">
                    <i class="bx bx-book-content"></i> Academic Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Class</small>
                            <span class="fw-semibold">{{ $enrollment->schoolClass->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Section</small>
                            <span class="fw-semibold">{{ $enrollment->section->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Group</small>
                            <span class="fw-semibold">{{ $enrollment->group->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Roll No</small>
                            <span class="fw-semibold">{{ $enrollment->roll_no ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Academic Year</small>
                            <span class="fw-semibold">{{ $enrollment->academicYear->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Class Teacher</small>
                            <span class="fw-semibold text-primary">
                                {{ $classTeacher ? trim($classTeacher->first_name . ' ' . $classTeacher->last_name) : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($issue->libraryMember->member_type === 'staff' && $staffDetail)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-info bg-gradient text-white">
                    <i class="bx bx-briefcase"></i> Employment Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Joining Date</small>
                            <span class="fw-semibold">{{ $staffDetail->joining_date ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Qualification</small>
                            <span class="fw-semibold">{{ $staffDetail->qualification ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Contract Type</small>
                            <span class="fw-semibold">{{ $staffDetail->contract_type ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Work Shift</small>
                            <span class="fw-semibold">{{ $staffDetail->work_shift ?? '-' }}</span>
                        </div>
                        @if($staffType === 'teacher')
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Assigned Class</small>
                            <span class="fw-semibold">{{ $staffDetail->schoolClass->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Assigned Section</small>
                            <span class="fw-semibold">{{ $staffDetail->section->name ?? '-' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Book & Issue Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-dark bg-gradient text-white">
                    <i class="bx bx-book"></i> Book Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block">Title</small>
                            <span class="fw-bold">{{ $issue->book->title ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">ISBN</small>
                            <span>{{ $issue->book->isbn ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Author</small>
                            <span>{{ $issue->book->author ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-gradient text-white">
                    <i class="bx bx-calendar"></i> Issue Status
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Issue Date</small>
                            <span class="fw-semibold">{{ $issue->issue_date }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Due Date</small>
                            <span class="fw-semibold">{{ $issue->due_date }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Return Date</small>
                            <span class="fw-semibold">{{ $issue->return_date ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Status</small>
                            @php
                            $badge = match($issue->status) {
                            'issued' => 'bg-primary',
                            'returned' => 'bg-success',
                            'overdue' => 'bg-danger',
                            'lost' => 'bg-dark',
                            default => 'bg-secondary',
                            };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($issue->status) }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Fine Amount</small>
                            <span class="fw-bold text-danger">{{ number_format($issue->fine_amount, 2) }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Fine Paid</small>
                            @if($issue->fine_paid)
                            <span class="badge bg-success">Paid</span>
                            @else
                            <span class="badge bg-warning text-dark">Unpaid</span>
                            @endif
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Issued By</small>
                            <span>{{ $issue->issuedBy->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Returned By</small>
                            <span>{{ $issue->returnedBy->name ?? '-' }}</span>
                        </div>
                    </div>

                    @if($issue->remarks)
                    <hr>
                    <small class="text-muted d-block">Remarks</small>
                    <p class="mb-0">{{ $issue->remarks }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection