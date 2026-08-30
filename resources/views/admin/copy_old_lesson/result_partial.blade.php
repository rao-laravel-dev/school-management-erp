<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semi-bold text-primary fs-5">Lesson &amp; Topics</h6>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="selectAll">
            <label class="form-check-label" for="selectAll">Select All</label>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8" style="max-height: 420px; overflow-y: auto;">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr><th style="width: 40px;">#</th><th>Lesson / Topic</th></tr>
                    </thead>
                    <tbody>
                        @foreach($lessons as $i => $lesson)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input lesson-checkbox" value="{{ $lesson->id }}" id="lesson_{{ $lesson->id }}">
                                    <label class="form-check-label fw-semibold" for="lesson_{{ $lesson->id }}">{{ $lesson->name }}</label>
                                </div>
                                @foreach($lesson->topics as $ti => $topic)
                                <div class="form-check ms-4">
                                    <input type="checkbox" class="form-check-input" disabled>
                                    <label class="form-check-label small text-muted">{{ $i + 1 }}.{{ $ti + 1 }} {{ $topic->name }}</label>
                                </div>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 border-start">
                <h6 class="mb-3">Select Target</h6>
                <div class="mb-3">
                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                    <select id="target_academic_year_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select Academic Year.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select id="target_class_id" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Please select Class.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select id="target_section_id" class="form-select form-select-sm" disabled>
                        <option value="">-- Select Class First --</option>
                    </select>
                    <div class="invalid-feedback">Please select Section.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select id="target_subject_id" class="form-select form-select-sm" disabled>
                        <option value="">-- Select Class First --</option>
                    </select>
                    <div class="invalid-feedback">Please select Subject.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button id="copyBtn" class="btn btn-success btn-sm">
            <i class='bx bx-save'></i> Save
        </button>
    </div>
</div>