@extends($current_layout)

@section('title', 'Skill Assessment Areas')

@section('content')
<div class="content-wrapper">
    <x-breadcrumb :items="[
        ['label' => 'Examination', 'url' => '#'],
        ['label' => 'Skill Assessment Areas', 'url' => route('skill_assessment_areas.index')],
    ]" />

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    @can('manage-skill-assessment-areas')
                    <button type="button" class="btn btn-success btn-sm"
                        data-toggle="modal" data-target="#addAreaModal"
                        data-bs-toggle="modal" data-bs-target="#addAreaModal"
                        onclick="resetAddForm()">
                        <i class="fas fa-plus"></i> Add Area
                    </button>
                    @endcan
                </div>
                <div class="card-body">
                    <table id="skillAreaTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Status</th>
                                @can('manage-skill-assessment-areas')
                                <th>Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $index => $area)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $area->category->name ?? '-' }}</td>
                                <td>{{ $area->name }}</td>
                                <td>
                                    @if($area->status)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                @can('manage-skill-assessment-areas')
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary px-3" onclick="editArea({{ $area->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger px-3" onclick="deleteArea({{ $area->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

@include('admin.skill_assessment_area.modals')
@endsection

@push('scripts')
<script>
$(function () {
    @if($errors->any())
        toastr.error("{{ $errors->first() }}");
    @endif

    $('#skillAreaTable').DataTable({
        @can('manage-skill-assessment-areas')
        columnDefs: [{ orderable: false, targets: -1 }],
        @endcan
        language: {
            emptyTable: '<div class="alert alert-danger text-center">No data available in the table</div>'
        }
    });
});

function deleteArea(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This skill assessment area will be deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('skill-assessment-areas') }}/" + id,
                method: 'DELETE',
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function () {
                    toastr.error('Unable to delete area.');
                }
            });
        }
    });
}
</script>
@endpush