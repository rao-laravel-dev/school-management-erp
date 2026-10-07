# Teacher Assignment + Timetable + Marks: Workflow & Task List

Project: smartschool (Laravel 12, Blade, Spatie, MySQL)
Is file ko naye chat mein paste/upload kar dein, aur kaho: "Step N se aage chalo."

---

## 1. Final design (ek rule, har class ke liye)

Choti class (Nursery/KG/Montessori) ho ya badi (Class 1-10), **rule same hai**. Admin jaisa chahe set kare: choti class mein ek teacher sab subjects le sakta hai, ya alag teachers. System ko farq nahi parhta.

| Role | Table | Kaam |
|---|---|---|
| **Class Teacher (in-charge)** | `teacher_assignments` | Har (year + class + section) ka sirf 1. Attendance, remarks, report card/marksheet review |
| **Subject Teacher (period wala)** | `class_timetables` | Kaun teacher, kis class/section mein, kaun sa subject, kis din/waqt |
| **Marks entry ka haq** | `class_timetables` se | Jis teacher ki timetable mein (class + section + subject) row ho, wahi us subject ke marks de sakta hai |
| **Admin** | Permission | Sab kuch override |

Important:
- `teacher_assignments` mein **subject nahi** hoga (`class_subject_id` hat raha hai).
- `has_subjects` column ab kisi logic mein use nahi hoga (baad mein drop kar sakte hain).
- Teacher apni class ke ilawa kisi bhi class mein period le sakta hai (timetable mein koi bhi teacher select hota hai). Clash check ka logic already hai.
- Class Teacher ko doosre teacher ke subject ke marks edit ka haq nahi. Wo sirf consolidated view, remarks, marksheet dekhta hai.
- Koi extra library/plugin nahi lagani.

---

## 2. Teen cheezein jo "kaun kya karega" decide karti hain

```
teacher_assignments  -> Class Teacher (in-charge)
class_timetables     -> Subject Teacher + marks entry permission
admin role           -> override
```

Marks entry permission check (jab marks module mein lagana ho):

```php
$allowed = ClassTimetable::where('teacher_id', $teacher->id)
    ->where('school_class_id', $classId)
    ->where('section_id', $sectionId)
    ->where('subject_id', $subjectId)
    ->exists();

abort_unless($allowed || auth()->user()->hasRole('admin'), 403);
```

Class Teacher check:

```php
$isClassTeacher = TeacherAssignment::where([
    'teacher_id'       => $teacher->id,
    'class_id'         => $classId,
    'section_id'       => $sectionId,
    'academic_year_id' => $activeYearId,
])->exists();
```

---

## 3. Steps (ek ek karke)

### PHASE A: Teacher Assignment module (pehle ye, phir test)

| # | File | Kya karna hai |
|---|---|---|
| A1 | Migration `create_teacher_assignments_table` (purani edit) | `class_subject_id` hatao. `unique(['academic_year_id','class_id','section_id'], 'class_teacher_unique')` lagao. Phir `migrate:rollback --path=...` + `migrate` (ya DROP TABLE + `DELETE FROM migrations WHERE migration LIKE '%create_teacher_assignments_table%'`) |
| A2 | Model `TeacherAssignment` | `$fillable = ['academic_year_id','class_id','section_id','teacher_id']`. `classSubject()` relation hatao |
| A3 | Routes file (`teacher/assign` group) | `get-subjects-by-class` route hatao |
| A4 | `TeacherAssignmentController` | Naya version (neeche section 4). `getSubjectsByClass()` delete |
| A5 | `admin/teacher_assign/create.blade.php` | Subject wala `<div class="mb-3">` delete. Teacher checkbox -> single `<select name="teacher_id">`. JS se `loadSubjects()` aur uski calls delete |
| A6 | `admin/teacher_assign/index.blade.php` | Columns: Academic Year, Class, Section, Class Teacher. Subject column nahi. Controller `AllTeacherClass()` mein sirf `teacher, schoolClass, section, academicYear` eager load |
| A7 | Test | Create, edit, delete, duplicate (same class+section dobara assign na ho) |

### PHASE B: Teacher Timetable info

| # | File | Kya karna hai |
|---|---|---|
| B1 | `TeacherTimetableController::getTeacherInfo()` | Response mein `class_teacher_of` (assignments se) aur `classes` (ClassTimetable se unique class-section) |
| B2 | `admin/teacher_timetable/index.blade.php` JS | `class_teacher_of` ko "Class Teacher Of" aur `classes` ko "Teaching Classes" label se dikhao |
| B3 | `Teacher` model | `getNameAttribute()` (first + last) maujood ho, warna `name` null aayega |

### PHASE C: TeachersController fixes

| # | Method | Fix |
|---|---|---|
| C1 | `generateTeacherId()` | `Teacher::withTrashed()->latest('id')->first()` (soft-deleted ki wajah se duplicate ID na bane) |
| C2 | `UpdateTeacher()` | `$teacher->update([...])` mein `'email'` aur `'gender'` add |
| C3 | `TeacherDestroy()` | Agar teacher ke `assignments()` ya `ClassTimetable` rows hon to delete block + error message |
| C4 | `getGeneratedId()` | `debug_found` aur uski query hatao |

### PHASE D: Optional improvements

| # | File | Kya karna hai |
|---|---|---|
| D1 | `ClassTimetableController::create()`, `TeacherAssignmentController::AddTeacherClass()` / `EditTeacherClass()` | Teachers dropdown sirf active: `Teacher::whereHas('user', fn($q)=>$q->where('status',1))->orderBy('first_name')->get()` |
| D2 | `ClassTimetableController::save()` | Subject us class ka ho: `ClassSubject::where('class_id',$classId)->pluck('subject_id')` mein `periods.*.subject_id` hona chahiye |
| D3 | Timetable create page (baad mein) | "Copy same teacher to all periods" button (choti classes ke liye asaani) |
| D4 | `has_subjects` | Kisi logic mein use na ho. Chahein to column drop |

### PHASE E: Data check

| # | Kaam |
|---|---|
| E1 | Choti classes (Montessori, Nursery, Prep, KG-1, KG-2, Class 1-3) ke subjects `class_subject` mein add karein, warna timetable ka subject dropdown khali aayega |
| E2 | `details_partial.blade.php` (teacher details) mein `classSubject` use ho raha ho to hatao |
| E3 | `LessonPlan`, `HomeWork`, `Syllabus Status` jahan `TeacherAssignment::class_subject_id` use hota ho, wahan check karo (HomeWorkController, TeacherProfileController::mySyllabusStatus). Ye ab timetable se aana chahiye |

### PHASE F: Marks module (baad mein)

| # | Kaam |
|---|---|
| F1 | MarkSheetController: teacher ke liye allowed combos `class_timetables` se, admin ko sab |
| F2 | Class Teacher ko consolidated view + remarks, marks edit nahi |
| F3 | Report card/marksheet print: Class Teacher ya admin |

---

## 4. Phase A ka controller (final version)

```php
public function AllTeacherClass()
{
    $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'academicYear'])
        ->latest()->get();

    return view('admin.teacher_assign.index', compact('assignments'));
}

public function AddTeacherClass()
{
    $teachers      = Teacher::all();
    $academicYears = AcademicYear::where('status', 1)->get();
    $classes       = SchoolClass::where('status', 1)->get();
    $sections      = collect();
    $assignments   = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'academicYear'])
        ->latest()->get();

    return view('admin.teacher_assign.create', compact('teachers', 'sections', 'academicYears', 'classes', 'assignments'));
}

public function getSectionsByClass(Request $request)
{
    $sections = DB::table('school_class_section')
        ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
        ->where('school_class_section.school_class_id', $request->class_id)
        ->select('sections.id', 'sections.name')
        ->get();

    return response()->json($sections);
}

private function rules(Request $request): array
{
    return [
        'academic_year_id' => 'required|exists:academic_years,id',
        'class_id'         => ['required', Rule::exists((new SchoolClass)->getTable(), 'id')],
        'section_id'       => [
            'required',
            Rule::exists('school_class_section', 'section_id')
                ->where('school_class_id', $request->class_id),
        ],
        'teacher_id'       => 'required|exists:teachers,id',
    ];
}

public function StoreTeacherClass(Request $request)
{
    $request->validate($this->rules($request));

    $exists = TeacherAssignment::where([
        'academic_year_id' => $request->academic_year_id,
        'class_id'         => $request->class_id,
        'section_id'       => $request->section_id,
    ])->exists();

    if ($exists) {
        return redirect()->back()->withInput()
            ->with('error', 'Is class/section ka Class Teacher pehle se assign hai.');
    }

    TeacherAssignment::create($request->only('academic_year_id', 'class_id', 'section_id', 'teacher_id'));

    $teacher = Teacher::find($request->teacher_id);

    return redirect()->back()
        ->with('success', 'Class Teacher assigned: ' . $teacher->first_name . ' ' . $teacher->last_name);
}

public function EditTeacherClass($id)
{
    $editData      = TeacherAssignment::findOrFail($id);
    $teachers      = Teacher::all();
    $academicYears = AcademicYear::where('status', 1)->get();
    $classes       = SchoolClass::where('status', 1)->get();

    $sections = DB::table('school_class_section')
        ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
        ->where('school_class_section.school_class_id', $editData->class_id)
        ->select('sections.id', 'sections.name')
        ->get();

    $assignments = TeacherAssignment::with(['teacher', 'schoolClass', 'section', 'academicYear'])
        ->latest()->get();

    return view('admin.teacher_assign.create', compact('editData', 'teachers', 'academicYears', 'classes', 'sections', 'assignments'));
}

public function UpdateTeacherClass(Request $request, $id)
{
    $request->validate($this->rules($request));

    $assignment = TeacherAssignment::findOrFail($id);

    $exists = TeacherAssignment::where([
        'academic_year_id' => $request->academic_year_id,
        'class_id'         => $request->class_id,
        'section_id'       => $request->section_id,
    ])->where('id', '!=', $id)->exists();

    if ($exists) {
        return redirect()->back()->withInput()
            ->with('error', 'Is class/section ka Class Teacher pehle se assign hai.');
    }

    $assignment->update($request->only('academic_year_id', 'class_id', 'section_id', 'teacher_id'));

    return redirect()->route('teacher.assign.create')->with('success', 'Assignment updated successfully!');
}

// DestroyTeacherClass() jaisa hai waisa rahega
```

**Extra rule (Phase A mein add ho chuka, DONE):** ek teacher ek academic year mein sirf ek hi class-section ka Class Teacher ban sakta hai. Controller mein `teacherAlreadyInCharge($request, $ignoreId = null)` helper hai. Store/Update mein pehle class-section duplicate check hota hai, phir ye teacher check.

Migration (A1):

```php
Schema::create('teacher_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
    $table->foreignId('class_id')->constrained('school_class')->onDelete('cascade');
    $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
    $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
    $table->timestamps();

    $table->unique(['academic_year_id', 'class_id', 'section_id'], 'class_teacher_unique');
});
```

Blade teacher select (A5):

```blade
<div class="mb-4">
    <label for="teacher_id" class="form-label text-secondary small fw-bold">Class Teacher *</label>
    <select name="teacher_id" id="teacher_id" class="form-select form-select-sm @error('teacher_id') is-invalid @enderror">
        <option value="">Select Teacher</option>
        @foreach($teachers as $teacher)
        <option value="{{ $teacher->id }}"
            {{ old('teacher_id', $editData->teacher_id ?? '') == $teacher->id ? 'selected' : '' }}>
            {{ $teacher->first_name }} {{ $teacher->last_name }}
        </option>
        @endforeach
    </select>
    @error('teacher_id') <span class="text-danger small">{{ $message }}</span> @enderror
</div>
```

JS (A5), subjects ka kuch nahi bacha:

```js
$('#class_id').on('change', function() {
    loadSections($(this).val());
});

@if(isset($editData))
loadSections("{{ $editData->class_id }}", "{{ $editData->section_id }}");
@endif
```

`getTeacherInfo()` (B1):

```php
public function getTeacherInfo($teacherId)
{
    $activeYearId = AcademicYear::getActiveSessionId();

    $teacher = Teacher::with(['assignments' => function ($q) use ($activeYearId) {
        $q->where('academic_year_id', $activeYearId)->with(['schoolClass', 'section']);
    }])->findOrFail($teacherId);

    $label = fn ($className, $sectionName) =>
        $className ? ($sectionName ? "{$className} - {$sectionName}" : $className) : null;

    $classTeacherOf = $teacher->assignments
        ->map(fn ($a) => $label(optional($a->schoolClass)->name, optional($a->section)->name))
        ->filter()->unique()->values();

    $teachingClasses = ClassTimetable::with(['schoolClass', 'section'])
        ->where('teacher_id', $teacherId)
        ->get()
        ->map(fn ($t) => $label(optional($t->schoolClass)->name, optional($t->section)->name))
        ->filter()->unique()->values();

    return response()->json([
        'name'             => $teacher->name,
        'father_name'      => $teacher->father_name,
        'phone'            => $teacher->phone,
        'photo'            => $teacher->photo ? asset('storage/teacher_images/' . $teacher->photo) : asset('backend/assets/images/avatar.png'),
        'class_teacher_of' => $classTeacherOf,
        'classes'          => $teachingClasses,
    ]);
}
```

---

## 5. Jo NAHI karna

- `is_class_teacher` flag nahi.
- `has_subjects` ka logic assignment/marks mein nahi.
- Subject-wise teacher `teacher_assignments` mein nahi (wo timetable ka kaam hai).
- `ClassTimetableController` ka clash/overlap/transaction logic mat chhedo.
- Naya migration nahi, purani edit karni hai.
- Extra library/plugin nahi.

---

## 6. Project conventions (yaad rakhein)

- Breadcrumb: `<x-breadcrumb :items="[...]" />` component.
- Sidebar: har item `has-arrow` parent + `<ul><li>` submenu pattern.
- Controllers: `App\Http\Controllers\Backend` namespace.
- Ek shared controller per module, role-based scoping andar (teacher ko sirf apna data).
- Teacher ke saare `teacher_id` columns `teachers.id` hain, `users.id` nahi.

---

## 7. Status tracker (tick karte jao)
> Pending tasks ab docs/ai/BACKLOG.md mein.

**Abhi kahan hain:** Phase A, B, C COMPLETE (tests pass). (06-10-2026: `TEACHER_ASSIGNMENT_WORKFLOW (2).md` se merge kiya, copies (1) aur (2) delete.)

**Known issues (BACKLOG mein track):**
- Teacher photo ka URL har page mein alag bana hua hai. Teacher index page par photo sahi dikhti hai (wohi reference hai). Edit form, teacher timetable (`getTeacherInfo()`) aur trash page mein URL galat hai. Plan: har jagah `$teacher->photo_url` (accessor `Teacher` model mein ban chuka). Section 4 ke `getTeacherInfo()` snippet mein `asset('storage/teacher_images/...')` galat hai, usay `$teacher->photo_url` se badalna hai.
- Photo ki file `ImageService` se `Storage::disk('public')` par `teacher_images/` mein save hoti hai. File sirf `TeacherForceDelete()` mein delete hoti hai (soft delete mein nahi).

**Notes:**
- Toastr: teacher index blade ab `session('success')` / `session('error')` handle karta hai (purane `toastr-success` / `toastr-error` bhi rakhe hain).

- [x] A1 migration
- [x] A2 model
- [x] A3 routes
- [x] A4 controller
- [x] A5 create.blade.php
- [x] A6 index.blade.php
- [x] A7 test
- [x] B1 getTeacherInfo
- [x] B2 teacher_timetable JS
- [x] B3 Teacher::getNameAttribute
- [x] C1 generateTeacherId (05-10-2026: withTrashed; tested ID FAIZAN005, trashed case untested as Trash Bin empty)
- [x] C2 UpdateTeacher
- [x] C3 TeacherDestroy
- [x] C4 getGeneratedId (05-10-2026: debug_found removed; response only teacher_id)
- [ ] D1 active teachers dropdown
- [ ] D2 save() subject check
- [ ] E1 choti classes ke subjects class_subject mein
- [x] E2 details_partial
- [ ] E3 HomeWork / LessonPlan / SyllabusStatus ka class_subject_id use
  - [x] SyllabusStatus: `TeacherProfileController::mySyllabusStatus()` se `whereNull('class_subject_id')` hataya (05-10-2026, tested)
  - [ ] HomeWork / LessonPlan: check pending
- [ ] F1-F3 marks module
  - [~] F1 partial: `MarkSheetController` `index()` + `save()` access check ab `class_timetables` se (teacher + class + section + subject) (05-10-2026, tested: own subject list, other subject "not assigned")
  - [ ] F1 pending: teacher ko `manage-marksheets` permission + teacher sidebar mein Marksheet link
  - [ ] F2 class teacher consolidated view + remarks
  - [ ] F3 print: class teacher / admin
