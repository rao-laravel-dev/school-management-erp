@extends($current_layout)

@section('title', 'Skill Categories')

@section('content')
<div class="content-wrapper">
    <x-breadcrumb :items="[
        ['label' => 'Examination', 'url' => '#'],
        ['label' => 'Skill Category', 'url' => route('skill_categories.index')],
    ]" />

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    @can('manage-skill-categories')
                    <button type="button" class="btn btn-success btn-sm"
                        data-toggle="modal" data-target="#addCategoryModal"
                        data-bs-toggle="modal" data-bs-target="#addCategoryModal"
                        onclick="resetAddForm()">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                    @endcan
                </div>
                <div class="card-body">
                    <table id="skillCategoryTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Areas Count</th>
                                @can('manage-skill-categories')
                                <th>Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $index => $category)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @if($category->status)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->skillAssessmentAreas()->count() > 0)
                                        <span class="badge bg-warning-subtle text-warning">
                                            {{ $category->skillAssessmentAreas()->count() }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-secondary">0</span>
                                    @endif
                                </td>
                                @can('manage-skill-categories')
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary px-3" onclick="editCategory({{ $category->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger px-3" onclick="deleteCategory({{ $category->id }})">
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

@include('admin.skill_category.modals')
@endsection

@push('scripts')
<script>
$(function () {
    @if($errors->any())
        toastr.error("{{ $errors->first() }}");
    @endif

    $('#skillCategoryTable').DataTable({
        @can('manage-skill-categories')
        columnDefs: [{ orderable: false, targets: -1 }],
        @endcan
        language: {
            emptyTable: '<div class="alert alert-danger text-center">No data available in the table</div>'
        }
    });
});

function deleteCategory(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This category will be deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('skill-categories') }}/" + id,
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
                    toastr.error('Unable to delete category.');
                }
            });
        }
    });
}
</script>
@endpush