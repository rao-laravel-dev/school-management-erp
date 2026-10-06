@extends($current_layout)

@section('content')

{{-- Breadcrumb --}}
<x-breadcrumb :items="[
    ['label' => 'Academic', 'url' => '#'],
    ['label' => 'Rooms', 'url' => route('rooms.index')],
]" />

<div class="row">
    <div class="col-xl">
        <div class="card radius-10">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 text-primary">Rooms</h5>
                @can('manage-rooms')
                <button type="button" class="btn btn-success btn-sm shadow-sm px-5" data-bs-toggle="modal" data-bs-target="#roomAdd">
                    Add
                </button>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="viewDataTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Room No.</th>
                                <th>Name</th>
                                <th>Building / Floor</th>
                                <th>Capacity</th>
                                <th>Type</th>
                                <th>Assigned Class</th>
                                <th>Status</th>
                                @can('manage-rooms')
                                <th width="120">Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $room->room_no }}</td>
                                <td>{{ $room->name ?: '-' }}</td>
                                <td>{{ trim(($room->building ?: '') . ' ' . ($room->floor ? '/ ' . $room->floor : '')) ?: '-' }}</td>
                                <td>{{ $room->capacity ?: '-' }}</td>
                                <td>{{ $room->type ? ($types[$room->type] ?? ucfirst($room->type)) : '-' }}</td>
                                <td>{{ $room->schoolClass ? $room->schoolClass->name . ' - ' . optional($room->section)->name : '-' }}</td>
                                <td class="text-center align-middle">
                                    @can('manage-rooms')
                                    <button type="button"
                                        class="btn btn-sm {{ $room->status ? 'btn-outline-success' : 'btn-outline-danger' }} toggle-room-status"
                                        data-id="{{ $room->id }}">
                                        {{ $room->status ? 'Active' : 'Inactive' }}
                                    </button>
                                    @else
                                    @if($room->status)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                    @endcan
                                </td>
                                @can('manage-rooms')
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="{{ '#roomEdit-' . $room->id }}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <form action="{{ route('rooms.delete', $room->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm px-3 delete-btn">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
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

{{-- Include Modals File --}}
@can('manage-rooms')
@include('admin.rooms.modals')
@endcan

@endsection

@push('styles')
<style>
    /* Theme sirf border-color badalta hai, modal mein nazar nahi aata: halka laal background + glow */
    .room-form .form-control.is-invalid,
    .room-form .form-select.is-invalid {
        background-color: #fff5f6;
        border-color: #fd3550;
        box-shadow: 0 0 0 .15rem rgba(253, 53, 80, .2);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Initializing DataTable (client-side search/pagination only)
        $('#viewDataTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "Search Room:"
            }
        });

        // 2. Delete Confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let form = $(this).closest('.delete-form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.trigger('submit');
                }
            });
        });

        // Toastr & Modal Validation Error Handling (jis modal se submit hua, wahi dobara khulta hai)
        @if($errors->any())
        var errModal = document.getElementById(@json(old('_modal', 'roomAdd')));
        if (errModal) {
            new bootstrap.Modal(errModal).show();
        }
        toastr.error(@json($errors->first()));
        @endif

        @if(Session::has('success'))
        toastr.success(@json(Session::get('success')));
        @endif

        @if(Session::has('error'))
        toastr.error(@json(Session::get('error')));
        @endif
    });

    // ---------- Class -> Section dropdown (AJAX) ----------
    function fillSections($scope, classId, selected) {
        let $sec = $scope.find('.room-section');
        $sec.html('<option value="">' + (classId ? 'Select Section' : 'Select Class first') + '</option>');
        if (!classId) return;

        $.get(`/rooms/get-sections/${classId}`, function(sections) {
            sections.forEach(function(s) {
                $sec.append(`<option value="${s.id}" ${(selected && s.id == selected) ? 'selected' : ''}>${s.name}</option>`);
            });
        }).fail(function() {
            toastr.error('Failed to load sections');
        });
    }

    // Modal khulte hi uska saved/old section load ho
    $(document).on('show.bs.modal', '.room-modal', function() {
        let $m = $(this);
        fillSections($m, $m.find('.room-class').val(), $m.find('.room-section').attr('data-selected'));
    });

    $(document).on('change', '.room-class', function() {
        fillSections($(this).closest('.room-form'), $(this).val(), '');
        $(this).removeClass('is-invalid');
    });

    // ---------- Field error helpers (message field ke neeche .invalid-feedback mein) ----------
    function setFieldError($el, msg) {
        $el.addClass('is-invalid');
        $el.siblings('.invalid-feedback').first().text(msg);
    }

    function clearFieldError($el) {
        $el.removeClass('is-invalid');
        $el.siblings('.invalid-feedback').first().text('');
    }

    // ---------- Empty form: sab missing fields laal + message neeche, ek hi toastr, pehli field par focus ----------
    $(document).on('submit', '.room-form', function(e) {
        let $form = $(this);
        let missing = [];
        $form.find('.is-invalid').each(function() {
            clearFieldError($(this));
        });

        $form.find('[data-required="1"]').each(function() {
            if (!$.trim($(this).val())) {
                missing.push({ $el: $(this), msg: $(this).data('label') + ' is required' });
            }
        });

        // Class select ho to Section bhi zaroori
        let $section = $form.find('.room-section');
        if ($form.find('.room-class').val() && !$section.val()) {
            missing.push({ $el: $section, msg: 'Section is required' });
        }

        if (missing.length) {
            e.preventDefault();
            missing.forEach(function(m) {
                setFieldError(m.$el, m.msg);
            });
            toastr.clear();
            toastr.error(missing[0].msg);
            missing[0].$el.trigger('focus');
        }
    });

    // Type karne ya select badalne par us field ka error hat jaye
    $(document).on('input change', '.room-form .is-invalid', function() {
        clearFieldError($(this));
    });

    // Modal band ho to uske errors saaf (har modal ka apna form hai, to Add/Edit mix nahi hote)
    $(document).on('hidden.bs.modal', '.room-modal', function() {
        $(this).find('.is-invalid').each(function() {
            clearFieldError($(this));
        });
    });

    // Toggle Status
    $(document).on('click', '.toggle-room-status', function(e) {
        e.preventDefault();
        let btn = $(this);
        let id = btn.data('id');

        $.post(`/rooms/${id}/toggle-status`, {
            _token: '{{ csrf_token() }}'
        }, function(res) {
            let isActive = res.status == 1;

            btn.text(isActive ? 'Active' : 'Inactive');
            btn.removeClass('btn-outline-success btn-outline-danger');
            btn.addClass(isActive ? 'btn-outline-success' : 'btn-outline-danger');

            toastr.success(res.message);
        }).fail(function() {
            toastr.error('Status update failed!');
        });
    });
</script>
@endpush
