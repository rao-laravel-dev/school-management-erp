<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Group;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchoolClassSubjectController extends Controller
{
    // Class-wise subject code ke liye har class ka subject_prefix lazmi hai; jin classes ka nahi, unke naam wapas
    private function classesWithoutPrefix($classIds)
    {
        return SchoolClass::whereIn('id', $classIds)
            ->where(function ($q) {
                $q->whereNull('subject_prefix')->orWhere('subject_prefix', '');
            })
            ->pluck('name');
    }

    private function missingPrefixResponse($names)
    {
        return redirect()->back()->withInput()->with([
            'message'    => 'Set the Subject Code Prefix first (Manage Class) for: ' . $names->implode(', ') . '.',
            'alert-type' => 'error',
        ]);
    }

    // Assigned classes aur unke subjects ki list dikhane k liye
    public function indexClassSubjects()
    {
        // Yahan 'with' bhi zaroori hai taake subjects show hon
        // Aur 'orderBy' se classes sequence mein aayengi
        $classes = SchoolClass::with('subjects')->orderBy('id', 'asc')->get();

        return view('admin.school_class_subject.index_sch_cls_sub', compact('classes'));
    }
    // End Method

    // Naya subject assign krne ka form
    public function assignClassSubjectsForm()
    {
        // Status check ke sath, taake sirf kaam ki cheezein dikhein
        $classes  = SchoolClass::where('status', 1)->orderBy('id', 'asc')->get();
        $subjects = Subject::where('status', 1)->get();

        // Agar Groups table exist karti hai toh load karein, warna empty collection
        $groups   = class_exists('App\Models\Group') ? Group::where('status', 1)->get() : collect();

        return view('admin.school_class_subject.assign_sch_cls_sub', compact('classes', 'subjects', 'groups'));
    }
    // End Method

    // Mapping ko database (pivot table) me save krne k liye
    public function saveClassSubjectsMapping(Request $request)
{
    // 1. Validation
    $request->validate([
        'class_id'    => 'required|array',
        'subject_ids' => 'required|array',
        'group_id'    => 'nullable', 
    ]);

    $classIds = $request->class_id;

    // Prefix ke baghair class mein code generate nahi ho sakta, save rok do
    $noPrefix = $this->classesWithoutPrefix($classIds);
    if ($noPrefix->isNotEmpty()) {
        return $this->missingPrefixResponse($noPrefix);
    }

    $added = 0;
    $skipped = 0;

    DB::transaction(function () use ($request, $classIds, &$added, &$skipped) {
    // 2. Har selected class ke liye loop chalayein
    foreach ($classIds as $classId) {
        $class = SchoolClass::findOrFail($classId);

        // Is class ke maujooda codes (subject_id => code); same subject ka code dobara use hoga
        $codeMap = ClassSubject::codeMapForClass($class->id);
        $usedCodes = array_values($codeMap);

        // 3. Create = sirf add: detach nahi, is class + group mein pehle se assigned subjects nikalo
        $query = $class->subjects();

        if ($request->filled('group_id')) {
            $query->wherePivot('group_id', $request->group_id);
        } else {
            // Agar group_id null hai to jahan pivot mein group_id null hai, wahi dekho
            $query->wherePivotNull('group_id');
        }
        $alreadyAssigned = array_map('intval', $query->pluck('subjects.id')->all());

        // 4. Attach logic (Data preparation)
        $syncData = [];
        foreach ($request->subject_ids as $subjectId) {
            // Pehle se assigned: skip (code aur marks wahi rahenge)
            if (in_array((int) $subjectId, $alreadyAssigned, true)) {
                $skipped++;
                continue;
            }
            $added++;
            if (!isset($codeMap[$subjectId])) {
                $codeMap[$subjectId] = ClassSubject::nextSubjectCode($class, $usedCodes);
                $usedCodes[] = $codeMap[$subjectId];
            }
            $syncData[$subjectId] = [
                'group_id'     => $request->group_id ?: null, // Empty string ko null mein convert karega
                'subject_code' => $codeMap[$subjectId],
                'full_marks'   => 100,
                'pass_marks'   => 33,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        // 5. Sirf naye subjects attach karein
        if (!empty($syncData)) {
            $class->subjects()->attach($syncData);
        }
    }
    });

    return redirect()->route('school-class-subjects.index')
                     ->with(['message' => "{$added} subject(s) added, {$skipped} already assigned (skipped)", 'alert-type' => $added > 0 ? 'success' : 'info']);
}
    // End Method

   public function editClassSubjectsMapping(Request $request, $id)
{
    $class    = SchoolClass::with('subjects')->findOrFail($id);
    $subjects = Subject::where('status', 1)->get();
    $classes  = SchoolClass::where('status', 1)->get();
    $groups   = class_exists('App\Models\Group') ? Group::where('status', 1)->get() : collect();

    // Query string se na milay to seedha pivot table se is class ka actual group_id nikal lein
    $groupId = $request->get('group_id');
    if (is_null($groupId)) {
        $groupId = DB::table('class_subject')->where('class_id', $id)->value('group_id');
    }

    $query = $class->subjects();
    if ($groupId) {
        $query->wherePivot('group_id', $groupId);
    } else {
        $query->wherePivotNull('group_id');
    }
    $assignedSubjects = $query->pluck('subjects.id')->toArray();

    $affectedClassesQuery = DB::table('class_subject')
        ->join('school_class', 'school_class.id', '=', 'class_subject.class_id')
        ->select('school_class.id', 'school_class.name')
        ->distinct();

    if ($groupId) {
        $affectedClassesQuery->where('class_subject.group_id', $groupId);
    } else {
        $affectedClassesQuery->whereNull('class_subject.group_id');
    }
    $affectedClasses = $affectedClassesQuery->get();

    // Is class ke class-wise subject codes (subject_id => code), edit form mein dikhane ke liye
    $subjectCodes = ClassSubject::codeMapForClass((int) $id);

    return view('admin.school_class_subject.edit_sch_cls_sub', compact(
        'class', 'subjects', 'assignedSubjects', 'classes', 'groups', 'groupId', 'affectedClasses', 'subjectCodes'
    ));
}
    // End Method

    public function updateClassSubjectsMapping(Request $request, $id)
{
    // Typed class-wise codes uppercase/trim (blank = purana ya auto code)
    $typedCodes = [];
    foreach ((array) $request->input('subject_codes', []) as $sid => $code) {
        $code = strtoupper(trim((string) $code));
        if ($code !== '') {
            $typedCodes[(int) $sid] = $code;
        }
    }
    $request->merge(['subject_codes' => $typedCodes]);

    $request->validate([
        'subject_ids' => 'required|array',
        'group_id'    => 'nullable|exists:groups,id',
        'subject_codes'   => 'nullable|array',
        'subject_codes.*' => ['nullable', 'max:10', 'regex:/^[A-Z0-9]+$/'],
    ], [
        'subject_codes.*.max'   => 'Subject code: max 10 characters.',
        'subject_codes.*.regex' => 'Subject code: only capital letters and digits (no dash/space).',
    ]);

    $groupId = $request->filled('group_id') ? $request->group_id : null;

    // 1. Is group ke andar is waqt jitni bhi classes assigned hain, unki IDs nikalo
    $query = DB::table('class_subject');
    if ($groupId !== null) {
        $query->where('group_id', $groupId);
    } else {
        $query->whereNull('group_id');
    }
    $classIds = $query->distinct()->pluck('class_id');

    // 2. Current class ($id) bhi list mein zaroor shamil ho (naye group ke case mein)
    $classIds = $classIds->push($id)->unique();

    // Prefix ke baghair class mein code generate nahi ho sakta, save rok do
    $noPrefix = $this->classesWithoutPrefix($classIds);
    if ($noPrefix->isNotEmpty()) {
        return $this->missingPrefixResponse($noPrefix);
    }

    // Typed codes sirf is class ($id) ke liye: apas mein aur is class ke baqi subjects ke codes se takrao na ho
    $submittedIds = array_map('intval', $request->subject_ids);
    $typedForSubmitted = array_intersect_key($typedCodes, array_flip($submittedIds));
    $finalCodes = $typedForSubmitted + array_intersect_key(ClassSubject::codeMapForClass((int) $id), array_flip($submittedIds));
    if (count($finalCodes) !== count(array_unique($finalCodes))) {
        throw ValidationException::withMessages(['subject_codes' => 'Two subjects cannot have the same code in one class.']);
    }
    $otherCodes = ClassSubject::where('class_id', $id)
        ->whereNotIn('subject_id', $submittedIds)
        ->whereNotNull('subject_code')
        ->pluck('subject_code')
        ->all();
    foreach ($typedForSubmitted as $code) {
        if (in_array($code, $otherCodes, true)) {
            throw ValidationException::withMessages(['subject_codes' => "Code {$code} is already used by another subject in this class."]);
        }
    }

    DB::transaction(function () use ($request, $classIds, $groupId, $id, $typedForSubmitted) {
    // 3. Har class pe wahi update apply karo
    foreach ($classIds as $classId) {
        $class = SchoolClass::find($classId);
        if (!$class) continue;

        // Detach se pehle maujooda codes; is class ($id) ke liye typed codes in par bhaari
        $codeMap = ClassSubject::codeMapForClass($class->id);
        $isEditedClass = ((int) $classId === (int) $id);
        if ($isEditedClass) {
            $codeMap = $typedForSubmitted + $codeMap;
        }
        $usedCodes = array_values($codeMap);

        $q = $class->subjects();
        if ($groupId !== null) {
            $q->wherePivot('group_id', $groupId);
        } else {
            $q->wherePivotNull('group_id');
        }
        // Detach se pehle maujooda rows (marks/created_at wapas lagane ke liye)
        $oldRows = DB::table('class_subject')->where('class_id', $class->id)
            ->when($groupId !== null, fn ($r) => $r->where('group_id', $groupId), fn ($r) => $r->whereNull('group_id'))
            ->get()->keyBy('subject_id');

        $q->detach();

        $syncData = [];
        foreach ($request->subject_ids as $subjectId) {
            if (!isset($codeMap[$subjectId])) {
                $codeMap[$subjectId] = ClassSubject::nextSubjectCode($class, $usedCodes);
                $usedCodes[] = $codeMap[$subjectId];
            }
            $old = $oldRows->get($subjectId);
            $syncData[$subjectId] = [
                'group_id'     => $groupId,
                'subject_code' => $codeMap[$subjectId],
                'full_marks'   => $old ? $old->full_marks : 100,
                'pass_marks'   => $old ? $old->pass_marks : 33,
                'created_at'   => $old ? $old->created_at : now(),
                'updated_at'   => now(),
            ];
        }
        $class->subjects()->attach($syncData);

        // Typed code is class ke baqi groups ki rows par bhi lage (same subject = same code)
        if ($isEditedClass) {
            foreach ($typedForSubmitted as $subjectId => $code) {
                ClassSubject::where('class_id', $class->id)->where('subject_id', $subjectId)->update(['subject_code' => $code]);
            }
        }
    }
    });

    return redirect()->route('school-class-subjects.index')->with([
        'message'    => 'Subjects mapping updated for all classes in this group!',
        'alert-type' => 'success'
    ]);
}
    // End Method


    public function ClassSubjectStatus($id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            // Status flip logic
            $class->status = $class->status == 1 ? 0 : 1;
            $class->save();

            return response()->json([
                'status' => $class->status,
                'message' => 'Class assignment status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while updating status.'
            ], 500);
        }
    }
    // End Method

    /**
     * Remove All Subject Mappings From a Class
     */
    public function DeleteClassSubjectsMapping($id)
    {
        try {
            $class = SchoolClass::findOrFail($id);

            // Many-to-Many pivot relationship se saare linked subjects detach kar diye
            $class->subjects()->detach();

            // Toastr notification trigger for success
            $notification = [
                'message' => 'Class subjects assignment removed successfully!',
                'alert-type' => 'success'
            ];

            return redirect()->route('school-class-subjects.index')->with($notification);
        } catch (\Exception $e) {
            $notification = [
                'message' => 'Failed to remove assignment configuration.',
                'alert-type' => 'error'
            ];

            return redirect()->back()->with($notification);
        }
    }
    // End Method
}
