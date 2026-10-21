<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Group;
use App\Models\HomeWork;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeWorkController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    private function handleFileUpload(UploadedFile $file, string $folder): string
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];

        if (in_array($file->getMimeType(), $imageMimes)) {
            $filename = $this->imageService->upload($file, 'uploads/' . $folder, 1200, 1200, 85);
            return 'uploads/' . $folder . '/' . $filename;
        }

        // non-image files - as-is save
        $destination = public_path('uploads/' . $folder);
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return 'uploads/' . $folder . '/' . $filename;
    }

    // Teacher user ka teachers row; na ho to 500 ki jagah saaf 403
    private function teacherOrAbort($user)
    {
        if (!$user->teacher) {
            abort(403, 'Teacher profile not found. Please contact admin.');
        }
        return $user->teacher;
    }

    public function index()
{
    $user = Auth::user();

    $query = HomeWork::with(['class', 'section', 'subject', 'createdBy']);

    $classes = SchoolClass::where('status', 1)->get();

    if ($user->hasRole('teacher')) {
        $teacher = $this->teacherOrAbort($user);

        $assignments = $teacher->assignments;
        $classIds    = $assignments->pluck('class_id')->unique();

        // exact class+section pairs (1-A + 2-B wala teacher 1-B / 2-A na dekhe)
        $query->where(function ($q) use ($assignments) {
            foreach ($assignments as $a) {
                $q->orWhere(function ($q2) use ($a) {
                    $q2->where('class_id', $a->class_id)->where('section_id', $a->section_id);
                });
            }
        });
        if ($assignments->isEmpty()) {
            $query->whereRaw('1 = 0'); // koi assignment nahi = koi row nahi (empty where sab dikha deta)
        }

        $classes = $classes->whereIn('id', $classIds);
    }

    $homeworks = $query->latest()->get();

    return view('admin.home_work.index', compact('homeworks', 'classes'));
}
// End Method


    public function getSectionsByClass($classId)
    {
        $sections = SchoolClass::findOrFail($classId)->sections; // mappedSections() alias
        $user = Auth::user();

        // Teacher ko us class ke sirf apne assigned sections
        if ($user->hasRole('teacher')) {
            $teacher = $this->teacherOrAbort($user);
            $sectionIds = $teacher->assignments()->where('class_id', $classId)->pluck('section_id');
            $sections = $sections->whereIn('id', $sectionIds)->values(); // values() = JSON array, object nahi
        }

        return response()->json($sections);
    }

    public function getGroupsByClass($classId)
    {
        $class = SchoolClass::findOrFail($classId);
        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $teacher = $this->teacherOrAbort($user);
            if (!$teacher->assignments()->where('class_id', $classId)->exists()) {
                abort(403, 'Aap is class ke teacher nahi hain.');
            }
        }

        $groupIds = $class->subjects()
            ->wherePivotNotNull('group_id')
            ->pluck('class_subject.group_id')
            ->unique();

        $groups = Group::whereIn('id', $groupIds)
            ->where('status', 1)
            ->get();

        return response()->json($groups);
    }

    public function getSubjects(Request $request)
    {
        $class = SchoolClass::findOrFail($request->class_id);
        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $teacher = $this->teacherOrAbort($user);
            if (!$teacher->assignments()->where('class_id', $request->class_id)->exists()) {
                abort(403, 'Aap is class ke teacher nahi hain.');
            }
        }

        $query = $class->subjects();

        if ($request->filled('group_id')) {
            $query->wherePivot('group_id', $request->group_id);
        } else {
            $query->wherePivotNull('group_id');
        }

        $subjects = $query->get();

        return response()->json($subjects);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'class_id'       => 'required|exists:school_class,id',
        'section_id'     => 'required|exists:sections,id',
        'subject_id'     => 'required|exists:subjects,id',
        'title'          => 'required|string|max:255',
        'description'    => 'nullable|string',
        'homework_date'  => 'required|date',
        'due_date'       => 'required|date|after_or_equal:homework_date',
        'attachment'     => 'nullable|file|max:5120',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('open_modal', 'add');
    }

    $data = $validator->validated();
    $user = Auth::user();

    if ($user->hasRole('teacher')) {
        $teacher = $this->teacherOrAbort($user);
        $allowed = $teacher->assignments()
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->exists();

        if (!$allowed) {
            abort(403, 'Aap sirf apni assigned class ke liye homework bana sakte hain.');
        }
    }

    $data['created_by'] = Auth::id();

    if ($request->hasFile('attachment')) {
        $data['attachment'] = $this->handleFileUpload($request->file('attachment'), 'homework');
    }

    HomeWork::create($data);

    return redirect()->back()->with('success', 'Homework added successfully.');
}
// End Method

    public function edit($id)
    {
        $homework = HomeWork::findOrFail($id);
        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $teacher = $this->teacherOrAbort($user);
            $allowed = $teacher->assignments()
                ->where('class_id', $homework->class_id)
                ->where('section_id', $homework->section_id)
                ->exists();

            if (!$allowed) {
                abort(403, 'Aap ye homework edit nahi kar sakte.');
            }
        }

        $groupId = $homework->class
            ->subjects()
            ->where('subjects.id', $homework->subject_id)
            ->first()
            ?->pivot
            ?->group_id;

        return response()->json([
            'id'             => $homework->id,
            'class_id'       => $homework->class_id,
            'section_id'     => $homework->section_id,
            'subject_id'     => $homework->subject_id,
            'group_id'       => $groupId,
            'title'          => $homework->title,
            'description'    => $homework->description,
            'homework_date'  => $homework->homework_date?->format('Y-m-d'),
            'due_date'       => $homework->due_date?->format('Y-m-d'),
            'attachment'     => $homework->attachment,
        ]);
    }

    public function update(Request $request, $id)
{
    $homework = HomeWork::findOrFail($id);
    $user = Auth::user();

    // Teacher sirf apni assigned class ka homework edit kar sake
    if ($user->hasRole('teacher')) {
        $teacher = $this->teacherOrAbort($user);
        $allowed = $teacher->assignments()
            ->where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->exists();

        if (!$allowed) {
            abort(403, 'Aap ye homework edit nahi kar sakte.');
        }
    }

    $validator = Validator::make($request->all(), [
        'class_id'       => 'required|exists:school_class,id',
        'section_id'     => 'required|exists:sections,id',
        'subject_id'     => 'required|exists:subjects,id',
        'title'          => 'required|string|max:255',
        'description'    => 'nullable|string',
        'homework_date'  => 'required|date',
        'due_date'       => 'required|date|after_or_equal:homework_date',
        'attachment'     => 'nullable|file|max:5120',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput()
            ->with('open_modal', 'edit_' . $id);
    }

    $data = $validator->validated();

    if ($request->hasFile('attachment')) {
        if ($homework->attachment && file_exists(public_path($homework->attachment))) {
            unlink(public_path($homework->attachment));
        }
        $data['attachment'] = $this->handleFileUpload($request->file('attachment'), 'homework');
    }

    $homework->update($data);

    return redirect()->back()->with('success', 'Homework updated successfully.');
}
// End Method

    public function destroy($id)
{
    $homework = HomeWork::findOrFail($id);
    $user = Auth::user();

    if ($user->hasRole('teacher')) {
        $teacher = $this->teacherOrAbort($user);
        $allowed = $teacher->assignments()
            ->where('class_id', $homework->class_id)
            ->where('section_id', $homework->section_id)
            ->exists();

        if (!$allowed) {
            abort(403, 'Aap ye homework delete nahi kar sakte.');
        }
    }

    if ($homework->attachment && file_exists(public_path($homework->attachment))) {
        unlink(public_path($homework->attachment));
    }

    $homework->delete();

    if (request()->expectsJson()) {
        return response()->json(['message' => 'Homework deleted successfully.']);
    }

    return redirect()->back()->with('success', 'Homework deleted successfully.');
}
// End Method

    public function statusUpdate($id)
    {
        $homework = HomeWork::findOrFail($id);
        $user = Auth::user();

        if ($user->hasRole('teacher')) {
            $teacher = $this->teacherOrAbort($user);
            $allowed = $teacher->assignments()
                ->where('class_id', $homework->class_id)
                ->where('section_id', $homework->section_id)
                ->exists();

            if (!$allowed) {
                abort(403, 'Aap is homework ka status change nahi kar sakte.');
            }
        }

        $homework->status = $homework->status ? 0 : 1;
        $homework->save();

        return response()->json([
            'status'  => $homework->status,
            'message' => 'Status updated successfully.',
        ]);
    }
}
