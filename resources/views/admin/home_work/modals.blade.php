{{-- ADD MODAL --}}
<div class="modal fade" id="addHomeworkModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="{{ route('home_work.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Add Homework</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-md-3">
              <label for="add_class_id" class="form-label">Class</label>
              <select name="class_id" id="add_class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                <option value="">Select Class</option>
                @foreach($classes as $class)
                  <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
              </select>
              @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
              <label for="add_section_id" class="form-label">Section</label>
              <select name="section_id" id="add_section_id" class="form-select form-select-sm @error('section_id') is-invalid @enderror" disabled>
                <option value="">Select Class First</option>
              </select>
              @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3" id="add_group_wrapper" style="display:none;">
              <label for="add_group_id" class="form-label">Group</label>
              <select name="group_id" id="add_group_id" class="form-select form-select-sm @error('group_id') is-invalid @enderror">
                <option value="">Select Group</option>
              </select>
              @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3" id="add_subject_wrapper">
              <label for="add_subject_id" class="form-label">Subject</label>
              <select name="subject_id" id="add_subject_id" class="form-select form-select-sm @error('subject_id') is-invalid @enderror" disabled>
                <option value="">Select Class First</option>
              </select>
              @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label for="add_title" class="form-label">Title</label>
              <input type="text" name="title" id="add_title" value="{{ old('title') }}"
                     class="form-control form-control-sm @error('title') is-invalid @enderror">
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label for="add_description" class="form-label">Description</label>
              <textarea name="description" id="add_description" rows="3"
                        class="form-control form-control-sm @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
              @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label for="add_homework_date" class="form-label">Homework Date</label>
              <input type="date" name="homework_date" id="add_homework_date" value="{{ old('homework_date', now()->format('Y-m-d')) }}"
                     class="form-control form-control-sm @error('homework_date') is-invalid @enderror">
              @error('homework_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label for="add_due_date" class="form-label">Due Date</label>
              <input type="date" name="due_date" id="add_due_date" value="{{ old('due_date') }}"
                     class="form-control form-control-sm @error('due_date') is-invalid @enderror">
              @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label for="add_attachment" class="form-label">Attachment</label>
              <input type="file" name="attachment" id="add_attachment"
                     class="form-control form-control-sm @error('attachment') is-invalid @enderror">
              @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editHomeworkModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editHomeworkForm" action="" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit Homework</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-md-3">
              <label for="edit_class_id" class="form-label">Class</label>
              <select name="class_id" id="edit_class_id" class="form-select form-select-sm @error('class_id') is-invalid @enderror">
                <option value="">Select Class</option>
                @foreach($classes as $class)
                  <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
              </select>
              @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
              <label for="edit_section_id" class="form-label">Section</label>
              <select name="section_id" id="edit_section_id" class="form-select form-select-sm @error('section_id') is-invalid @enderror">
                <option value="">Select Section</option>
              </select>
              @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3" id="edit_group_wrapper" style="display:none;">
              <label for="edit_group_id" class="form-label">Group</label>
              <select name="group_id" id="edit_group_id" class="form-select form-select-sm @error('group_id') is-invalid @enderror">
                <option value="">Select Group</option>
              </select>
              @error('group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3" id="edit_subject_wrapper">
              <label for="edit_subject_id" class="form-label">Subject</label>
              <select name="subject_id" id="edit_subject_id" class="form-select form-select-sm @error('subject_id') is-invalid @enderror">
                <option value="">Select Subject</option>
              </select>
              @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label for="edit_title" class="form-label">Title</label>
              <input type="text" name="title" id="edit_title"
                     class="form-control form-control-sm @error('title') is-invalid @enderror">
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
              <label for="edit_description" class="form-label">Description</label>
              <textarea name="description" id="edit_description" rows="3"
                        class="form-control form-control-sm @error('description') is-invalid @enderror"></textarea>
              @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label for="edit_homework_date" class="form-label">Homework Date</label>
              <input type="date" name="homework_date" id="edit_homework_date"
                     class="form-control form-control-sm @error('homework_date') is-invalid @enderror">
              @error('homework_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label for="edit_due_date" class="form-label">Due Date</label>
              <input type="date" name="due_date" id="edit_due_date"
                     class="form-control form-control-sm @error('due_date') is-invalid @enderror">
              @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label for="edit_attachment" class="form-label">Attachment (leave empty to keep existing)</label>
              <input type="file" name="attachment" id="edit_attachment"
                     class="form-control form-control-sm @error('attachment') is-invalid @enderror">
              @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- VIEW HOMEWORK MODAL --}}
<div class="modal fade" id="viewHomeworkModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Homework Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-sm align-middle mb-0">
          <tbody>
            <tr>
              <th class="w-25 bg-light">Title</th>
              <td id="view_title"></td>
            </tr>
            <tr>
              <th class="bg-light">Class</th>
              <td id="view_class"></td>
            </tr>
            <tr>
              <th class="bg-light">Section</th>
              <td id="view_section"></td>
            </tr>
            <tr>
              <th class="bg-light">Subject</th>
              <td id="view_subject"></td>
            </tr>
            <tr>
              <th class="bg-light">Description</th>
              <td id="view_description"></td>
            </tr>
            <tr>
              <th class="bg-light">Homework Date</th>
              <td id="view_homework_date"></td>
            </tr>
            <tr>
              <th class="bg-light">Due Date</th>
              <td id="view_due_date"></td>
            </tr>
            <tr>
              <th class="bg-light">Attachment</th>
              <td id="view_attachment">-</td>
            </tr>
            <tr>
              <th class="bg-light">Assigned By</th>
              <td id="view_assigned_by"></td>
            </tr>
            <tr>
              <th class="bg-light">Status</th>
              <td id="view_status"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>