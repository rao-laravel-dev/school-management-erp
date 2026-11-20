<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    private const RELATIONS = ['student.parent', 'schoolClass', 'section', 'academicYear'];

    // Issue form: type, student, wording + live preview
    public function create()
    {
        $types = CertificateService::activeTypes();
        $sessions = AcademicYear::orderByDesc('id')->get(['id', 'name', 'is_current']);
        // old: $classes = SchoolClass::where('status', 1)->orderBy('name')->get(['id', 'name']);
        // Baqi class dropdowns jaisa: numeric_name phir name (warna Class 1, Class 10, Class 2...)
        $classes = SchoolClass::where('status', 1)->orderBy('numeric_name')->orderBy('name')->get(['id', 'name']);
        $setting = SiteSetting::current();

        // JS ke liye: type key => title, fields, required, body
        $typesJs = collect($types)->map(fn ($t) => [
            'title' => $t['title'],
            'fields' => $t['fields'],
            'required' => $t['required'],
            'body' => $t['body'],
        ]);

        return view('admin.certificate.create', [
            'types' => $types,
            'typesJs' => $typesJs,
            'sessions' => $sessions,
            'classes' => $classes,
            'fieldMeta' => Certificate::FIELDS,
            'conductOptions' => Certificate::CONDUCT_OPTIONS,
            'orientations' => Certificate::ORIENTATIONS,
            'designs' => Certificate::DESIGNS,
            'missingHead' => !$setting->principal_signature || !$setting->head_name,
        ]);
    } // End Method

    // AJAX: class select hone par uske sections (apna route, kisi aur module ki permission par nirbhar nahi)
    public function getSectionsByClass($classId)
    {
        $sections = Section::join('school_class_section', 'school_class_section.section_id', '=', 'sections.id')
            ->where('school_class_section.school_class_id', $classId)
            ->where('school_class_section.status', 1)
            ->select('sections.id', 'sections.name')
            ->orderBy('sections.name')
            ->get();

        return response()->json($sections);
    } // End Method

    // AJAX: session + class + section ke students (sab enroll_status, taake passed-out ko bhi SLC mil sake)
    public function getStudents(Request $request)
    {
        $v = $request->validate([
            'session_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'class_id' => ['required', 'integer', Rule::exists((new SchoolClass)->getTable(), 'id')],
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')],
        ]);

        $students = Enrollment::with('student')
            ->where('academic_year_id', $v['session_id'])
            ->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])
            ->whereHas('student') // soft-deleted student skip
            ->orderBy('roll_no')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id, // enrollment id
                'text' => $e->student->full_name . ' (' . $e->student->admission_no . ')',
            ])
            ->values();

        return response()->json($students);
    } // End Method

    // Live preview: unsaved Certificate -> asli print blade (ID card ke livePreview jaisa), DB mein kuch nahi likhta
    public function livePreview(Request $request)
    {
        $typeKeys = array_keys(CertificateService::activeTypes());
        $request->validate([
            'type' => ['required', Rule::in($typeKeys)],
            'enrollment_id' => ['nullable', 'integer', Rule::exists('enrollments', 'id')],
            'orientation' => ['nullable', Rule::in(array_keys(Certificate::ORIENTATIONS))],
            'design' => ['nullable', Rule::in(array_keys(Certificate::DESIGNS))],
            'issue_date' => 'nullable|date',
            'title' => 'nullable|string|max:150',
            'body' => 'nullable|string|max:5000',
        ]);

        $type = CertificateService::type($request->type);
        $issueDate = $request->filled('issue_date') ? Carbon::parse($request->issue_date) : Carbon::today();
        $enrollment = $request->filled('enrollment_id')
            ? Enrollment::with(self::RELATIONS)->find($request->enrollment_id)
            : null;
        if (!$enrollment || !$enrollment->student) {
            $enrollment = $this->sampleEnrollment();
        }

        $fields = $this->typeFields($request, $type);
        $body = $request->filled('body') ? $request->body : $type['body'];
        $tokens = CertificateService::placeholders($enrollment, $fields, $issueDate, 'Given on save');

        $certificate = new Certificate();
        $certificate->forceFill([
            'certificate_no' => 'Given on save',
            'type' => $type['key'],
            'title' => $request->filled('title') ? $request->title : $type['title'],
            'body' => CertificateService::render($body, $tokens),
            // old: 'design' => 'classic',
            'design' => $request->input('design', 'classic') ?: 'classic',
            'orientation' => $request->input('orientation', 'portrait'),
            'issue_date' => $issueDate,
            'status' => Certificate::STATUS_ISSUED,
        ]);
        $isPreview = true;

        return view('admin.certificate.print', compact('certificate', 'isPreview'));
    } // End Method

    public function store(Request $request)
    {
        $type = CertificateService::type((string) $request->input('type'));
        $request->validate($this->rules($type), $this->messages($type));

        $type = CertificateService::type($request->type);
        $enrollment = Enrollment::with(self::RELATIONS)->findOrFail($request->enrollment_id);
        if (!$enrollment->student) {
            return redirect()->back()->withInput()->with('error', 'This student record no longer exists.');
        }

        $issueDate = Carbon::parse($request->issue_date);
        $fields = $this->typeFields($request, $type);
        $year = (int) $issueDate->format('Y');

        // SLC / Transfer: abhi sirf print hota hai. School Left status change baad mein (alag kaam)
        try {
            $certificate = DB::transaction(function () use ($request, $type, $enrollment, $issueDate, $fields, $year) {
                [$serial, $number] = CertificateService::nextSerial($type['key'], $type['prefix'], $year);
                $tokens = CertificateService::placeholders($enrollment, $fields, $issueDate, $number);

                return Certificate::create([
                    'certificate_no' => $number,
                    'type' => $type['key'],
                    'serial_year' => $year,
                    'serial' => $serial,
                    'student_id' => $enrollment->student_id,
                    'enrollment_id' => $enrollment->id,
                    'title' => $request->title,
                    'body' => CertificateService::render($request->body, $tokens),
                    'fields' => $fields + ['template' => $request->body], // original wording bhi mehfooz
                    // old: 'design' => 'classic',
                    'design' => $request->design,
                    'orientation' => $request->orientation,
                    'issue_date' => $issueDate->toDateString(),
                    'status' => Certificate::STATUS_ISSUED,
                    'issued_by' => auth()->id(),
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            return redirect()->back()->withInput()->with('error', 'Another certificate was saved at the same time. Click Save again.');
        }

        return redirect()->route('certificate.print', ['certificate' => $certificate->id, 'autoprint' => 1])
            ->with('success', 'Certificate ' . $certificate->certificate_no . ' issued.');
    } // End Method

    public function print(Request $request, Certificate $certificate)
    {
        $isPreview = false;
        $autoPrint = $request->boolean('autoprint');

        return view('admin.certificate.print', compact('certificate', 'isPreview', 'autoPrint'));
    } // End Method

    // Issued certificates register: filters + pagination (query string kept)
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|max:40',
            'status' => ['nullable', Rule::in([Certificate::STATUS_ISSUED, Certificate::STATUS_CANCELLED])],
            'class_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'q' => 'nullable|string|max:100',
        ], [
            'date_to.after_or_equal' => 'Date To must be on or after Date From',
        ]);

        $query = Certificate::with([
            'student' => fn ($q) => $q->withTrashed(), // soft-deleted student ka record bhi register mein dikhe
            'enrollment.schoolClass',
            'enrollment.section',
            'issuer',
        ])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('class_id'), fn ($q) => $q->whereHas('enrollment', fn ($e) => $e->where('class_id', $request->class_id)))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('issue_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('issue_date', '<=', $request->date_to));

        // Search: certificate no, GR No. (admission_no) ya student name
        if ($request->filled('q')) {
            $like = '%' . addcslashes(trim($request->q), '%_\\') . '%';
            $query->where(function ($w) use ($like) {
                $w->where('certificate_no', 'like', $like)
                    ->orWhereHas('student', function ($s) use ($like) {
                        $s->withTrashed()->where(function ($n) use ($like) {
                            $n->where('admission_no', 'like', $like)
                                ->orWhere('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", [$like]);
                        });
                    });
            });
        }

        $certificates = $query->orderByDesc('issue_date')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.certificate.index', [
            'certificates' => $certificates,
            'types' => CertificateService::types(), // inactive types bhi: purane certificates filter ho sakein
            'classes' => SchoolClass::where('status', 1)->orderBy('numeric_name')->orderBy('name')->get(['id', 'name']),
        ]);
    } // End Method

    // Cancel (delete nahi): reason zaroori, dobara cancel 422
    public function cancel(Request $request, Certificate $certificate)
    {
        $validator = Validator::make($request->all(), [
            'cancel_reason' => 'required|string|max:255',
        ], [
            'cancel_reason.required' => 'Cancel reason is required',
            'cancel_reason.max' => 'Cancel reason must be 255 characters or less',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Ek hi query mein status check + update: do admin ek saath cancel karein to sirf ek jeete
        $changed = Certificate::where('id', $certificate->id)
            ->where('status', Certificate::STATUS_ISSUED)
            ->update([
                'status' => Certificate::STATUS_CANCELLED,
                'cancel_reason' => trim($request->cancel_reason),
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);
        if (!$changed) {
            return response()->json(['success' => false, 'message' => 'This certificate is already cancelled.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Certificate ' . $certificate->certificate_no . ' cancelled.']);
    } // End Method

    // Certificate Types page: built-in + custom, edit wording / active, add custom
    public function types()
    {
        return view('admin.certificate.types', [
            'types' => CertificateService::types(),
            'issuedCounts' => Certificate::selectRaw('type, COUNT(*) as total')->groupBy('type')->pluck('total', 'type'),
            'fieldMeta' => Certificate::FIELDS,
        ]);
    } // End Method

    // Add custom type (no key) ya edit (key). Built-in: sirf title / wording / active. Custom: + fields (prefix add par hi)
    public function saveType(Request $request)
    {
        $all = CertificateService::types();
        $key = (string) $request->input('key', '');
        $editing = $key !== '';
        if ($editing && !isset($all[$key])) {
            abort(404);
        }
        $isCustom = $editing ? $all[$key]['custom'] : true;

        $request->merge(['prefix' => strtoupper(trim((string) $request->input('prefix', '')))]);
        $request->validate($this->typeRules($all, $editing, $isCustom), $this->typeMessages());

        $active = $request->boolean('active');
        // Kam az kam ek type active rahe warna Issue page khali
        $activeLeft = collect($all)->filter(fn ($t, $k) => $k === $key ? $active : $t['active'])->count() + ($editing ? 0 : ($active ? 1 : 0));
        if ($activeLeft < 1) {
            throw ValidationException::withMessages(['active' => 'At least one certificate type must stay active']);
        }

        $setting = SiteSetting::current();
        $saved = $setting->certificate_types ?: [];
        $overrides = $saved['overrides'] ?? [];
        $custom = $saved['custom'] ?? [];

        if (!$isCustom) {
            $default = Certificate::TYPES[$key];
            $isDefault = $request->title === $default['title'] && $request->body === $default['body'] && $active;
            if ($isDefault) {
                unset($overrides[$key]); // default jaisa hi hai: override ki zaroorat nahi
            } else {
                $overrides[$key] = ['title' => $request->title, 'body' => $request->body, 'active' => $active];
            }
        } else {
            $fields = array_values(array_intersect(array_keys(Certificate::FIELDS), (array) $request->input('fields', [])));
            $required = array_values(array_intersect($fields, (array) $request->input('required', [])));
            if (!$editing) {
                $key = $this->newCustomKey($request->title, $all);
                $custom[$key] = ['prefix' => $request->prefix];
            }
            $custom[$key] = array_merge($custom[$key] ?? [], [
                'title' => $request->title,
                'body' => $request->body,
                'fields' => $fields,
                'required' => $required,
                'active' => $active,
            ]);
            if ($editing) {
                $custom[$key]['prefix'] = $all[$key]['prefix']; // issue ho chuke numbers ka prefix kabhi nahi badalta
            }
        }

        $setting->update(['certificate_types' => ['overrides' => $overrides, 'custom' => $custom]]);

        return redirect()->route('certificate.types')->with('success', $editing ? 'Certificate type updated.' : 'Custom certificate type added.');
    } // End Method

    // Built-in type ko default wording par wapas
    public function resetType(string $key)
    {
        if (!isset(Certificate::TYPES[$key])) {
            return redirect()->route('certificate.types')->with('error', 'Only built-in types can be reset to default.');
        }

        $setting = SiteSetting::current();
        $saved = $setting->certificate_types ?: [];
        $overrides = $saved['overrides'] ?? [];
        unset($overrides[$key]);
        $setting->update(['certificate_types' => ['overrides' => $overrides, 'custom' => $saved['custom'] ?? []]]);

        return redirect()->route('certificate.types')->with('success', 'Type reset to default wording.');
    } // End Method

    private function typeRules(array $all, bool $editing, bool $isCustom): array
    {
        $rules = [
            'title' => 'required|string|max:150',
            'body' => 'required|string|max:5000',
            'active' => 'nullable|boolean',
        ];
        if ($isCustom) {
            if (!$editing) {
                $rules['prefix'] = ['required', 'regex:/^[A-Z]{2,5}$/', function ($attr, $value, $fail) use ($all) {
                    foreach ($all as $t) {
                        if ($t['prefix'] === $value) {
                            $fail('This prefix is already used by another type');
                        }
                    }
                }];
            }
            $fieldKeys = array_keys(Certificate::FIELDS);
            $rules['fields'] = 'nullable|array';
            $rules['fields.*'] = ['string', Rule::in($fieldKeys)];
            $rules['required'] = 'nullable|array';
            $rules['required.*'] = ['string', Rule::in($fieldKeys)];
        }

        return $rules;
    }

    private function typeMessages(): array
    {
        return [
            'title.required' => 'Title is required',
            'body.required' => 'Wording is required',
            'prefix.required' => 'Prefix is required',
            'prefix.regex' => 'Prefix must be 2 to 5 capital letters (e.g. MIG)',
        ];
    }

    // custom_<title slug>, key hamesha server banata hai aur unique rakhta hai
    private function newCustomKey(string $title, array $all): string
    {
        $base = 'custom_' . (substr(Str::slug($title, '_'), 0, 30) ?: 'type');
        $key = $base;
        $i = 2;
        while (isset($all[$key])) {
            $key = $base . '_' . $i++;
        }

        return $key;
    }

    // Type ke apne fields hi lete hain (baqi input ignore)
    private function typeFields(Request $request, array $type): array
    {
        $fields = [];
        foreach ($type['fields'] as $key) {
            $fields[$key] = trim((string) $request->input($key, ''));
        }

        return $fields;
    }

    private function rules(?array $type): array
    {
        $rules = [
            'type' => ['required', Rule::in(array_keys(CertificateService::activeTypes()))],
            'enrollment_id' => ['required', 'integer', Rule::exists('enrollments', 'id')],
            'title' => 'required|string|max:150',
            'body' => 'required|string|max:5000',
            'orientation' => ['required', Rule::in(array_keys(Certificate::ORIENTATIONS))],
            'design' => ['required', Rule::in(array_keys(Certificate::DESIGNS))],
            'issue_date' => 'required|date',
        ];

        // Type ke fields: required wale required, baqi optional
        foreach (($type['fields'] ?? []) as $key) {
            $meta = Certificate::FIELDS[$key];
            $r = [in_array($key, $type['required'], true) ? 'required' : 'nullable'];
            $r[] = $meta[1] === 'date' ? 'date' : 'string';
            if ($meta[2] > 0) {
                $r[] = 'max:' . $meta[2];
            }
            $rules[$key] = $r;
        }

        return $rules;
    }

    private function messages(?array $type): array
    {
        $m = [
            'type.required' => 'Certificate Type is required',
            'type.in' => 'Selected Certificate Type is not available',
            'enrollment_id.required' => 'Student is required',
            'enrollment_id.exists' => 'Selected Student is invalid',
            'title.required' => 'Title is required',
            'body.required' => 'Wording is required',
            'orientation.required' => 'Orientation is required',
            'design.required' => 'Design is required',
            'design.in' => 'Selected Design is not available',
            'issue_date.required' => 'Issue Date is required',
        ];
        foreach (($type['fields'] ?? []) as $key) {
            $m[$key . '.required'] = Certificate::FIELDS[$key][0] . ' is required';
        }

        return $m;
    }

    // Student choose na ho to preview ke liye nakli (unsaved) student, kuch DB mein nahi jata
    private function sampleEnrollment(): Enrollment
    {
        $student = (new Student)->forceFill([
            'id' => 1,
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'gender' => 'male',
            'admission_no' => 'GR-0001',
            'date_of_birth' => '2012-03-12',
            'admission_date' => '2020-04-01',
        ]);
        $student->setRelation('parent', (new ParentProfile)->forceFill([
            'father_name' => 'Muhammad Raza',
            'mother_name' => 'Ayesha Raza',
        ]));

        $enrollment = (new Enrollment)->forceFill(['roll_no' => '12']);
        $enrollment->setRelation('student', $student);
        $enrollment->setRelation('schoolClass', (new SchoolClass)->forceFill(['name' => 'Class 5']));
        $enrollment->setRelation('section', (new Section)->forceFill(['name' => 'A']));
        $year = AcademicYear::where('is_current', 1)->first() ?: (new AcademicYear)->forceFill(['name' => '2026-27']);
        $enrollment->setRelation('academicYear', $year);

        return $enrollment;
    }
}
