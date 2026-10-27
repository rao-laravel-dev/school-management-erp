<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentPromotionRequest;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentPromotionController extends Controller
{
    // Enrollment_id wale tables (migrations se). Naya table aaye to yahan ek line add karein
    private const ENROLLMENT_DATA_TABLES = [
        'attendances',
        'mark_sheets',
        'results',
        'print_marksheets',
        'student_skill_marks',
        'print_report_cards',
    ];

    // Enroll status codes (Enrollment model comment ke mutabiq)
    private const STATUS_ACTIVE = 1;
    private const STATUS_PROMOTED = 2;
    private const STATUS_DETAINED = 3;
    private const STATUS_PASSED_OUT = 5;

    public function index()
    {
        $years = AcademicYear::whereNotNull('start_date')->orderBy('start_date')->get(['id', 'name', 'start_date', 'is_current', 'status']);
        $classes = SchoolClass::where('status', 1)->orderBy('numeric_name')->get(['id', 'name', 'numeric_name']);
        $highestClassId = $this->highestClassId();
        $currentYearId = $this->getActiveSessionId();

        return view('admin.student_promotion.index', compact('years', 'classes', 'highestClassId', 'currentYearId'));
    } // End Method

    public function getSections($classId)
    {
        $sections = DB::table('school_class_section')
            ->join('sections', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->select('sections.id', 'sections.name')
            ->get();

        return response()->json($sections);
    } // End Method

    // Sirf published result wale exams (From session ke)
    public function getExams($yearId)
    {
        $exams = Exam::where('academic_year_id', $yearId)
            ->where('publish_result', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($exams);
    } // End Method

    public function getStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_year_id'    => ['required', Rule::exists('academic_years', 'id')],
            'to_year_id'      => ['required', Rule::exists('academic_years', 'id')],
            'from_class_id'   => ['required', Rule::exists('school_class', 'id')],
            'from_section_id' => ['required', Rule::exists('sections', 'id')],
            'exam_id'         => ['nullable', Rule::exists('exams', 'id')->where('academic_year_id', $request->from_year_id)->where('publish_result', 1)],
        ], [
            'exam_id.exists' => 'Selected exam is not published for the From session',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $fromYear = AcademicYear::find($request->from_year_id);
        $toYear = AcademicYear::find($request->to_year_id);
        if (!$fromYear->start_date || !$toYear->start_date || $toYear->start_date->lte($fromYear->start_date)) {
            return response()->json(['success' => false, 'message' => 'To session must start after the From session'], 422);
        }

        $isHighest = (int) $request->from_class_id === (int) $this->highestClassId();

        // Promoted/Detained/Passed Out rows bhi chahiye taake "Already enrolled" + Undo dikhe
        $enrollments = Enrollment::with(['student'])
            ->whereHas('student')
            ->where('academic_year_id', $fromYear->id)
            ->where('class_id', $request->from_class_id)
            ->where('section_id', $request->from_section_id)
            ->whereIn('enroll_status', [self::STATUS_ACTIVE, self::STATUS_PROMOTED, self::STATUS_DETAINED, self::STATUS_PASSED_OUT])
            ->get()
            ->sortBy('roll_no', SORT_NATURAL)
            ->values();

        $results = collect();
        if ($request->filled('exam_id')) {
            $results = Result::with('grade')
                ->where('exam_id', $request->exam_id)
                ->where('class_id', $request->from_class_id)
                ->where('section_id', $request->from_section_id)
                ->whereIn('enrollment_id', $enrollments->pluck('id'))
                ->get()
                ->keyBy('enrollment_id');
        }

        // To session mein pehle se enrollment (soft-deleted bhi: unique key unhe bhi pakadti hai)
        $toEnrollments = Enrollment::withTrashed()
            ->with(['schoolClass', 'section'])
            ->where('academic_year_id', $toYear->id)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->get()
            ->keyBy('student_id');

        $blocked = $this->enrollmentsWithData($toEnrollments->pluck('id')->all(), $toYear->id);

        $rows = $enrollments->map(function ($e) use ($results, $toEnrollments, $blocked, $request, $isHighest) {
            $result = $results->get($e->id);
            $toEnrollment = $toEnrollments->get($e->student_id);

            $passAction = $isHighest ? 'passed_out' : 'promote';
            if (!$request->filled('exam_id')) {
                $defaultAction = $passAction;
            } elseif ($result) {
                $defaultAction = $result->status === 'Pass' ? $passAction : 'detain';
            } else {
                $defaultAction = 'skip'; // result nahi bana: admin khud tay kare
            }

            $note = null;
            $canUndo = false;
            if ($toEnrollment) {
                $note = 'Already enrolled: ' . ($toEnrollment->schoolClass ? $toEnrollment->schoolClass->name : '-')
                    . ' ' . ($toEnrollment->section ? $toEnrollment->section->name : '') . ', roll ' . $toEnrollment->roll_no;
                $canUndo = in_array((int) $e->enroll_status, [self::STATUS_PROMOTED, self::STATUS_DETAINED], true)
                    && (int) $toEnrollment->enroll_status === self::STATUS_ACTIVE
                    && !$toEnrollment->trashed()
                    && !in_array($toEnrollment->id, $blocked, true);
            } elseif ((int) $e->enroll_status === self::STATUS_PASSED_OUT) {
                $note = 'Passed Out';
                $canUndo = true;
            } elseif ((int) $e->enroll_status !== self::STATUS_ACTIVE) {
                $note = 'Already moved to another session';
            }

            return [
                'enrollment_id'  => $e->id,
                'admission_no'   => $e->student->admission_no,
                'name'           => trim($e->student->first_name . ' ' . $e->student->last_name),
                'roll_no'        => $e->roll_no,
                'percentage'     => $result ? $result->percentage : null,
                'grade'          => $result && $result->grade ? $result->grade->grade_name : null,
                'result_status'  => $result ? $result->status : null,
                'default_action' => $defaultAction,
                'selectable'     => $note === null,
                'note'           => $note,
                'can_undo'       => $canUndo,
            ];
        });

        return response()->json(['success' => true, 'is_highest' => $isHighest, 'rows' => $rows]);
    } // End Method

    public function store(StudentPromotionRequest $request)
    {
        $fromYear = AcademicYear::findOrFail($request->from_year_id);
        $toYear = AcademicYear::findOrFail($request->to_year_id);
        if (!$fromYear->start_date || !$toYear->start_date || $toYear->start_date->lte($fromYear->start_date)) {
            return response()->json(['success' => false, 'message' => 'To session must start after the From session'], 422);
        }

        $actions = collect($request->input('rows'))->pluck('action', 'enrollment_id')->filter(fn ($a) => $a !== 'skip');
        if ($actions->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Select at least one student (Promote, Detain or Passed Out)'], 422);
        }

        $fromClass = SchoolClass::findOrFail($request->from_class_id);
        $isHighest = (int) $fromClass->id === (int) $this->highestClassId();

        $toClass = null;
        $toSection = null;
        if ($actions->contains('promote')) {
            if ($isHighest) {
                return response()->json(['success' => false, 'message' => 'Last class cannot be promoted. Use Passed Out'], 422);
            }
            if (!$request->filled('to_class_id') || !$request->filled('to_section_id')) {
                return response()->json(['success' => false, 'message' => 'Select the To class and To section'], 422);
            }
            $toClass = SchoolClass::where('status', 1)->find($request->to_class_id);
            $toSection = Section::find($request->to_section_id);
            if (!$toClass || !$toSection) {
                return response()->json(['success' => false, 'message' => 'To class or section not found'], 422);
            }
            if ((int) $toClass->numeric_name <= (int) $fromClass->numeric_name) {
                return response()->json(['success' => false, 'message' => 'To class must be higher than the From class'], 422);
            }
        }
        if ($actions->contains('passed_out') && !$isHighest) {
            return response()->json(['success' => false, 'message' => 'Passed Out is only for the last class'], 422);
        }

        $fromSection = Section::findOrFail($request->from_section_id);

        $counts = DB::transaction(function () use ($request, $actions, $fromYear, $toYear, $fromClass, $fromSection, $toClass, $toSection) {
                // Roll no purane roll order mein diye jayen
                $oldRows = Enrollment::whereIn('id', $actions->keys())
                    ->where('academic_year_id', $fromYear->id)
                    ->where('class_id', $fromClass->id)
                    ->where('section_id', $fromSection->id)
                    ->where('enroll_status', self::STATUS_ACTIVE)
                    ->lockForUpdate()
                    ->get()
                    ->sortBy('roll_no', SORT_NATURAL);

                if ($oldRows->count() !== $actions->count()) {
                    abort(response()->json(['success' => false, 'message' => 'Some selected students are not active in the From class. Reload the list'], 422));
                }

                $counts = ['promote' => 0, 'detain' => 0, 'passed_out' => 0];
                $rollSeq = []; // target (class+section+group) ka chalta hua last number

                foreach ($oldRows as $old) {
                    $action = $actions[$old->id];

                    if ($action === 'passed_out') {
                        $old->update(['enroll_status' => self::STATUS_PASSED_OUT]);
                        $counts['passed_out']++;
                        continue;
                    }

                    if (Enrollment::withTrashed()->where('student_id', $old->student_id)->where('academic_year_id', $toYear->id)->exists()) {
                        abort(response()->json(['success' => false, 'message' => 'A selected student is already enrolled in the To session. Reload the list'], 422));
                    }

                    if ($action === 'promote') {
                        $classModel = $toClass;
                        $sectionModel = $toSection;
                        // Group sirf tab jab nayi class mein wahi group mojood ho
                        $groupId = $old->group_id && DB::table('class_subject')->where('class_id', $toClass->id)->where('group_id', $old->group_id)->exists()
                            ? $old->group_id : null;
                    } else {
                        $classModel = $fromClass;
                        $sectionModel = $fromSection;
                        $groupId = $old->group_id;
                    }
                    $groupModel = $groupId ? Group::find($groupId) : null;

                    [$classCode, $sectionChar, $groupCode, $maxSeq] = $this->buildRollSequence($classModel, $sectionModel, $groupModel, $toYear->id);
                    $key = $classModel->id . '-' . $sectionModel->id . '-' . ($groupId ?: 0);
                    $seq = max($maxSeq, isset($rollSeq[$key]) ? $rollSeq[$key] : 0);

                    do {
                        $seq++;
                        $seqStr = str_pad($seq, 2, '0', STR_PAD_LEFT);
                        $rollNo = $groupCode
                            ? "{$classCode}-{$groupCode}-{$sectionChar}-{$seqStr}"
                            : "{$classCode}-{$sectionChar}-{$seqStr}";
                        $taken = Enrollment::withTrashed()
                            ->where('academic_year_id', $toYear->id)
                            ->where('class_id', $classModel->id)
                            ->where('section_id', $sectionModel->id)
                            ->where('roll_no', $rollNo)
                            ->exists();
                    } while ($taken);
                    $rollSeq[$key] = $seq;

                    Enrollment::create([
                        'student_id'       => $old->student_id,
                        'academic_year_id' => $toYear->id,
                        'class_id'         => $classModel->id,
                        'section_id'       => $sectionModel->id,
                        'group_id'         => $groupId,
                        'roll_no'          => $rollNo,
                        'enroll_status'    => self::STATUS_ACTIVE,
                    ]);

                    $old->update(['enroll_status' => $action === 'promote' ? self::STATUS_PROMOTED : self::STATUS_DETAINED]);
                    $counts[$action]++;
                }

                return $counts;
            });

        return response()->json([
            'success' => true,
            'message' => "Saved: {$counts['promote']} promoted, {$counts['detain']} detained, {$counts['passed_out']} passed out",
        ]);
    } // End Method

    // $enrollment = purani (From) row. To session ki nayi row sirf tab hatti hai jab us par koi data na ho
    public function undo(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            'to_year_id' => ['required', Rule::exists('academic_years', 'id')],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (!in_array((int) $enrollment->enroll_status, [self::STATUS_PROMOTED, self::STATUS_DETAINED, self::STATUS_PASSED_OUT], true)) {
            return response()->json(['success' => false, 'message' => 'This enrollment was not promoted'], 422);
        }

        if ((int) $enrollment->enroll_status === self::STATUS_PASSED_OUT) {
            $enrollment->update(['enroll_status' => self::STATUS_ACTIVE]);
            return response()->json(['success' => true, 'message' => 'Passed Out undone']);
        }

        $fromYear = AcademicYear::find($enrollment->academic_year_id);
        $toYear = AcademicYear::find($request->to_year_id);
        if (!$fromYear || !$fromYear->start_date || !$toYear->start_date || $toYear->start_date->lte($fromYear->start_date)) {
            return response()->json(['success' => false, 'message' => 'Invalid To session'], 422);
        }

        $new = Enrollment::where('student_id', $enrollment->student_id)
            ->where('academic_year_id', $toYear->id)
            ->first();
        if (!$new) {
            return response()->json(['success' => false, 'message' => 'No enrollment found in the To session'], 404);
        }
        if ((int) $new->enroll_status !== self::STATUS_ACTIVE) {
            return response()->json(['success' => false, 'message' => 'Cannot undo: the student has moved further from that session'], 422);
        }
        if (in_array($new->id, $this->enrollmentsWithData([$new->id], $toYear->id), true)) {
            return response()->json(['success' => false, 'message' => 'Cannot undo: the new enrollment already has attendance, marks or fees'], 422);
        }

        DB::transaction(function () use ($new, $enrollment) {
            $new->forceDelete(); // soft delete rakhne se unique key phir bhi bandh rehti
            $enrollment->update(['enroll_status' => self::STATUS_ACTIVE]);
        });

        return response()->json(['success' => true, 'message' => 'Promotion undone']);
    } // End Method

    // Un enrollment ids ki list jin par data mojood hai (attendance, marks, results, fees...)
    private function enrollmentsWithData(array $enrollmentIds, $toYearId): array
    {
        if (empty($enrollmentIds)) {
            return [];
        }

        $withData = [];
        foreach (self::ENROLLMENT_DATA_TABLES as $table) {
            $withData = array_merge($withData, DB::table($table)->whereIn('enrollment_id', $enrollmentIds)->distinct()->pluck('enrollment_id')->all());
        }

        // student_fees mein enrollment_id nahi: student + To session ke fee structure / assignment se pakadte hain
        $students = Enrollment::withTrashed()->whereIn('id', $enrollmentIds)->pluck('student_id', 'id');
        $studentsWithFees = DB::table('student_fees')
            ->whereIn('student_id', $students->values())
            ->where(function ($q) use ($toYearId) {
                $q->whereIn('fee_structure_id', DB::table('fee_structures')->where('academic_year_id', $toYearId)->select('id'))
                    ->orWhereIn('fee_assignment_id', DB::table('fee_assignments')->where('academic_year_id', $toYearId)->select('id'));
            })
            ->distinct()
            ->pluck('student_id')
            ->all();
        foreach ($students as $enrollmentId => $studentId) {
            if (in_array($studentId, $studentsWithFees)) {
                $withData[] = $enrollmentId;
            }
        }

        return array_values(array_unique($withData));
    } // End Method

    // Sab se oonchi (active) class: numeric_name ke hisab se
    private function highestClassId()
    {
        return SchoolClass::where('status', 1)->orderByDesc('numeric_name')->value('id');
    } // End Method

    // StudentController::buildRollSequence jaisa hi rule (class + section + group + session)
    private function buildRollSequence($classModel, $sectionModel, $groupModel, $academicYearId): array
    {
        $classCode = preg_replace('/[^A-Z0-9]/', '', strtoupper($classModel->roll_code));
        $sectionChar = $sectionModel ? strtoupper(substr(trim($sectionModel->name), 0, 1)) : 'A';
        $groupCode = $groupModel ? strtoupper(trim($groupModel->group_code ?? $groupModel->name)) : null;

        $pattern = $groupCode
            ? $classCode . '-' . $groupCode . '-' . $sectionChar . '-%'
            : $classCode . '-' . $sectionChar . '-%';

        $maxSeq = Enrollment::withTrashed()
            ->where('class_id', $classModel->id)
            ->where('section_id', $sectionModel->id ?? null)
            ->where('academic_year_id', $academicYearId)
            ->where('roll_no', 'like', $pattern)
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(roll_no, '-', -1) AS UNSIGNED)) as max_seq")
            ->value('max_seq') ?: 0;

        return [$classCode, $sectionChar, $groupCode, $maxSeq];
    } // End Method
}
