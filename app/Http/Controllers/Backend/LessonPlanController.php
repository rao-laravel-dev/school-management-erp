<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassTimetable;
use App\Models\Lesson;
use App\Models\LessonPlan;
use App\Models\LessonPlanComment;
use App\Models\Teacher;
use App\Models\Topic;
use App\Services\ImageService;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{

    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $teachers = Teacher::orderBy('first_name')->get(); // users.id ki jagah teachers.id
        return view('admin.lesson_plan.index', compact('teachers'));
    }
    // End Method

    public function getTeacherSlots($teacherId)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $timetables = ClassTimetable::with(['schoolClass:id,name', 'section:id,name', 'subject:id,name'])
            ->where('teacher_id', $teacherId)
            ->get()
            ->groupBy('day');

        $lessonPlans = LessonPlan::where('teacher_id', $teacherId)->pluck('id', 'class_timetable_id');

        // current week ka Monday
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();

        $dayOffsets = [
            'Monday' => 0,
            'Tuesday' => 1,
            'Wednesday' => 2,
            'Thursday' => 3,
            'Friday' => 4,
            'Saturday' => 5,
            'Sunday' => 6,
        ];

        $grid = [];
        foreach ($days as $day) {
            $slots = [];
            $dayDate = $startOfWeek->copy()->addDays($dayOffsets[$day])->format('Y-m-d');

            if (isset($timetables[$day])) {
                foreach ($timetables[$day] as $slot) {
                    $slots[] = [
                        'class_timetable_id' => $slot->id,
                        'class_id' => $slot->school_class_id,
                        'section_id' => $slot->section_id,
                        'subject_id' => $slot->subject_id,
                        'class'   => $slot->schoolClass->name ?? '-',
                        'section' => $slot->section->name ?? '-',
                        'subject' => $slot->subject->name ?? '-',
                        'time_from' => $slot->time_from,
                        'time_to'   => $slot->time_to,
                        'date'      => $dayDate,   // <-- naya field
                        'lesson_plan_id' => $lessonPlans[$slot->id] ?? null,
                    ];
                }
            }
            $grid[$day] = $slots;
        }

        return response()->json(['success' => true, 'grid' => $grid]);
    }
    // End Method

    public function save(Request $request)
    {
        $academicYear = AcademicYear::where('is_current', 1)->first();
        if (!$academicYear) {
            return response()->json(['message' => 'No current academic year set.'], 422);
        }

        $validated = $request->validate([
            'class_timetable_id' => 'required|exists:class_timetables,id',
            'lesson_id'   => 'required|exists:lessons,id',
            'topic_id'    => 'nullable|exists:topics,id',
            'sub_topic'   => 'nullable|string|max:255',
            'date'        => 'required|date',
            'time_from'   => 'nullable',
            'time_to'     => 'nullable',
            'youtube_url' => 'nullable|string|max:255',
            'lecture_video' => 'nullable|file|mimes:mp4,mov,avi,wmv,webm,mkv|max:51200',
            'attachment'    => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip|max:51200',
            'teaching_method' => 'nullable|string',
            'general_objectives' => 'nullable|string',
            'previous_knowledge' => 'nullable|string',
            'comprehensive_questions' => 'nullable|string',
            'presentation' => 'nullable|string',
        ]);

        $classTimetable = ClassTimetable::findOrFail($validated['class_timetable_id']);

        $data = $validated;
        $data['academic_year_id'] = $academicYear->id;
        $data['class_id']    = $classTimetable->school_class_id;
        $data['section_id']  = $classTimetable->section_id;
        $data['subject_id']  = $classTimetable->subject_id;
        $data['teacher_id']  = $classTimetable->teacher_id;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $data['attachment'] = $this->imageService->upload($file, 'uploads/lesson_plan', 800, 800, 80);
            } else {
                $data['attachment'] = $this->moveFile($file, 'lesson_plan');
            }
        }

        if ($request->hasFile('lecture_video')) {
            $data['lecture_video'] = $this->moveFile($request->file('lecture_video'), 'lesson_plan');
        }
        LessonPlan::create($data);

        // Mark topic as completed for syllabus tracking
        if (!empty($data['topic_id'])) {
            Topic::where('id', $data['topic_id'])->update([
                'is_completed' => 1,
                'completion_date' => $data['date'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Lesson plan added successfully.']);
    }
    // End Method

    public function edit($id)
    {
        $lessonPlan = LessonPlan::findOrFail($id);
        return response()->json(['success' => true, 'data' => $lessonPlan]);
    }
    // End Method

    public function update(Request $request, $id)
    {
        $lessonPlan = LessonPlan::findOrFail($id);

        $validated = $request->validate([
            'lesson_id'   => 'required|exists:lessons,id',
            'topic_id'    => 'nullable|exists:topics,id',
            'sub_topic'   => 'nullable|string|max:255',
            'date'        => 'required|date',
            'time_from'   => 'nullable',
            'time_to'     => 'nullable',
            'youtube_url' => 'nullable|string|max:255',
            'lecture_video' => 'nullable|file|mimes:mp4,mov,avi,wmv,webm,mkv|max:51200',
            'attachment'    => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip|max:51200',
            'teaching_method' => 'nullable|string',
            'general_objectives' => 'nullable|string',
            'previous_knowledge' => 'nullable|string',
            'comprehensive_questions' => 'nullable|string',
            'presentation' => 'nullable|string',
        ]);

        if ($request->hasFile('attachment')) {
            if ($lessonPlan->attachment) {
                $this->imageService->delete($lessonPlan->attachment, 'uploads/lesson_plan');
            }
            $file = $request->file('attachment');
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $validated['attachment'] = $this->imageService->upload($file, 'uploads/lesson_plan', 800, 800, 80);
            } else {
                $validated['attachment'] = $this->moveFile($file, 'lesson_plan');
            }
        }

        if ($request->hasFile('lecture_video')) {
            if ($lessonPlan->lecture_video) {
                $this->imageService->delete($lessonPlan->lecture_video, 'uploads/lesson_plan');
            }
            $validated['lecture_video'] = $this->moveFile($request->file('lecture_video'), 'lesson_plan');
        }

        // Yahan create ki jagah update use karein aur $data ki jagah $validated
        $lessonPlan->update($validated);

        if (!empty($validated['topic_id'])) {
            Topic::where('id', $validated['topic_id'])->update([
                'is_completed' => 1,
                'completion_date' => $validated['date'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Lesson plan updated successfully.']);
    }
    // End Method

    public function view($id)
    {
        $lessonPlan = LessonPlan::with(['lesson', 'topic', 'comments.user'])->findOrFail($id);
        $html = view('admin.lesson_plan.view_partial', compact('lessonPlan'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }
    // End Method

    public function destroy($id)
    {
        $lessonPlan = LessonPlan::findOrFail($id);

        if ($lessonPlan->attachment) {
            $this->imageService->delete($lessonPlan->attachment, 'uploads/lesson_plan');
        }

        if ($lessonPlan->lecture_video) {
            $this->imageService->delete($lessonPlan->lecture_video, 'uploads/lesson_plan');
        }

        $lessonPlan->delete();

        return response()->json(['success' => true, 'message' => 'Lesson plan deleted successfully.']);
    }
    // End Method

    public function getLessons(Request $request)
    {
        $academicYear = AcademicYear::where('is_current', 1)->first();

        $lessons = Lesson::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $academicYear->id ?? null)   // <-- add this line
            ->where('status', 1)
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $lessons]);
    }
    // End Method

    public function getTopics($lessonId)
    {
        $topics = Topic::where('lesson_id', $lessonId)
            ->where('status', 1)
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $topics]);
    }
    // End Method

    public function saveComment(Request $request)
    {
        $request->validate([
            'lesson_plan_id' => 'required|exists:lesson_plans,id',
            'comment' => 'required|string',
        ]);

        $comment = LessonPlanComment::create([
            'lesson_plan_id' => $request->lesson_plan_id,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'comment' => [
                'user_name' => auth()->user()->name,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }
    // End Method

    private function moveFile($file, $folder)
    {
        $path = public_path("uploads/{$folder}");
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);
        return $filename;
    }
    // End Method

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB
        ]);

        $filename = $this->imageService->upload($request->file('image'), 'uploads/lesson_plan', 800, 800, 80);

        return response()->json([
            'success' => true,
            'url' => asset('uploads/lesson_plan/' . $filename),
        ]);
    }
    // End Method

}
