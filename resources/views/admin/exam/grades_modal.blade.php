<div class="modal fade" id="gradesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Manage Grades — <span id="grades_exam_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="gradeForm" class="row g-2 align-items-end mb-3">
                    @csrf
                    <input type="hidden" id="grade_id" name="id">
                    <input type="hidden" id="grade_exam_id" name="exam_id">

                    <div class="col-md-3">
                        <label for="grade_name" class="form-label">Grade Name</label>
                        <input type="text" class="form-control form-control-sm" id="grade_name" name="grade_name" placeholder="e.g. A+">
                    </div>
                    <div class="col-md-3">
                        <label for="remark" class="form-label">Remark</label>
                        <input type="text" class="form-control form-control-sm" id="remark" name="remark" placeholder="e.g. Excellent">
                    </div>
                    <div class="col-md-2">
                        <label for="min_percentage" class="form-label">Min %</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="min_percentage" name="min_percentage">
                    </div>
                    <div class="col-md-2">
                        <label for="max_percentage" class="form-label">Max %</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="max_percentage" name="max_percentage">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success btn-sm w-100" id="gradeFormSubmitBtn">
                            <i class='bx bx-plus'></i> Add
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="gradesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Grade</th>
                                <th>Remark</th>
                                <th>Min %</th>
                                <th>Max %</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="gradesTableBody">

                            {{-- filled via JS --}}
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-warning d-flex align-items-center justify-content-center text-center mb-0" role="alert">
                                        <i class='bx bx-error me-2'></i>
                                        <span>No grades added yet.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>