@extends($current_layout)
@section('content')
<x-breadcrumb :items="[
    ['label' => 'ID Card', 'url' => '#'],
    ['label' => 'Print Staff ID Card', 'url' => route('print_staff_id_card.index')],
]" />

<div class="card">
    <div class="card-header">
        <h5 class="fw-semi-bold text-primary mb-0">Select Criteria</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <select id="role" class="form-select form-select-sm">
                    <option value="">Select</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label for="template_id" class="form-label">Staff ID Card Template <span class="text-danger">*</span></label>
                <select id="template_id" class="form-select form-select-sm">
                    <option value="">Select</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" id="loadStaffBtn" class="btn btn-sm btn-primary w-100">
                    <i class="bx bx-search"></i> Show Staff
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" id="staffListWrapper" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="selectAllStaff">
            <label class="form-check-label" for="selectAllStaff">Select All</label>
        </div>
        <button type="button" id="printSelectedBtn" class="btn btn-sm btn-success">
            <i class="bx bx-printer"></i> Print Selected Cards
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="40"></th>
                        <th width="50">Photo</th>
                        <th>Staff ID</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody id="staffListBody"></tbody>
            </table>
        </div>
        <div id="noStaffMsg" class="alert alert-warning d-none mb-0">
            <i class="bx bx-error-circle"></i> Is role me koi staff nahi mila.
        </div>
    </div>
</div>

<form id="generateCardsForm" action="{{ route('print_staff_id_card.generate') }}" method="POST" target="_blank" style="display:none;">
    @csrf
    <input type="hidden" name="template_id" id="hiddenTemplateId">
    <div id="staffIdsContainer"></div>
</form>
@endsection

@push('scripts')
<script>
$(function() {

    $('#role, #template_id').on('change', function() {
        $('#staffListWrapper').hide();
    });

    $('#loadStaffBtn').on('click', function() {
        const role = $('#role').val();
        const templateId = $('#template_id').val();

        if (!role || !templateId) {
            toastr.error('Role aur Template dono select karein.');
            return;
        }

        $.get('/print-staff-id-card/get-staff/' + role, function(data) {
            $('#staffListBody').empty();
            $('#selectAllStaff').prop('checked', false);

            if (data.length === 0) {
                $('#noStaffMsg').removeClass('d-none');
                $('#staffListWrapper').show();
                return;
            }
            $('#noStaffMsg').addClass('d-none');

            let rows = '';
            data.forEach(function(s) {
                rows += `
                    <tr>
                        <td><input type="checkbox" class="form-check-input staffCheckbox" value="${s.id}"></td>
                        <td><img src="${s.photo}" width="35" height="35" style="object-fit:cover;border-radius:4px;"></td>
                        <td>${s.staff_id}</td>
                        <td>${s.name}</td>
                    </tr>`;
            });
            $('#staffListBody').html(rows);
            $('#staffListWrapper').show();
        });
    });

    $('#selectAllStaff').on('change', function() {
        $('.staffCheckbox').prop('checked', $(this).is(':checked'));
    });

    $('#printSelectedBtn').on('click', function() {
        const selected = $('.staffCheckbox:checked').map(function() { return this.value; }).get();

        if (selected.length === 0) {
            toastr.error('Kam az kam ek staff select karein.');
            return;
        }

        $('#hiddenTemplateId').val($('#template_id').val());

        const container = $('#staffIdsContainer');
        container.empty();
        selected.forEach(function(id) {
            container.append(`<input type="hidden" name="staff_ids[]" value="${id}">`);
        });

        $('#generateCardsForm').submit();
    });

});
</script>
@endpush