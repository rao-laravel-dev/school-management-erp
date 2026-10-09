<?php

namespace App\Http\Controllers\Backend;

use App\Exports\GenericTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SchoolClassImport;
use App\Models\SchoolClass;
use App\Traits\Exportable;
use App\Traits\Importable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ClassController extends Controller
{
    use Exportable, Importable;

    private const EXPORT_HEADINGS = ['Class', 'Class Code', 'Numeric Name', 'Has Subjects', 'Description', 'Status'];
    private const IMPORT_HEADINGS = ['name', 'class_code', 'numeric_name', 'has_subjects', 'description'];

    private function classExportRows(): array
    {
        return SchoolClass::orderBy('numeric_name')->get()->map(fn($c) => [
            $c->name,
            $c->class_code,
            $c->numeric_name,
            $c->has_subjects ? 'Yes' : 'No',
            $c->description ?: '-',
            $c->status ? 'Active' : 'Inactive',
        ])->all();
    }
    // End Method

    public function ExportExcel()
    {
        return $this->exportToExcel($this->classExportRows(), self::EXPORT_HEADINGS, 'Class List', 'classes');
    }
    // End Method

    public function ExportPdf()
    {
        return $this->exportToPdf($this->classExportRows(), self::EXPORT_HEADINGS, 'Class List', 'classes', '', 'portrait');
    }
    // End Method

    public function ImportTemplate()
    {
        return Excel::download(
            new GenericTemplateExport(self::IMPORT_HEADINGS, ['Class 5', '5', 5, 'yes', '']),
            'classes_import_template.xlsx'
        );
    }
    // End Method

    public function ImportExcel(Request $request)
    {
        return $this->runImport($request, new SchoolClassImport(), 'classes');
    }
    // End Method

    public function AllClass()
    {
        $classes = SchoolClass::with(['mappedSections'])
            ->withCount(['enrollments' => function ($query) {
                $query->where('enroll_status', 1);
            }])
            ->orderBy('numeric_name', 'ASC')
            ->get();

        return view('admin.classes.index_class', compact('classes'));
    }
    // End Method

    // AJAX: modal ke liye JSON
    public function EditClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        return response()->json($class);
    }
    // End Method

    // Class code rules: CLS- ke baad sirf capital letters/digits (roll no mein dash na aaye), unique
    private function classCodeRules($ignoreId = null): array
    {
        return ['required', 'max:20', 'regex:/^[A-Z0-9]+$/', function ($attribute, $value, $fail) use ($ignoreId) {
            $exists = SchoolClass::where('class_code', 'CLS-' . $value)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();
            if ($exists) {
                $fail('This class code already exists.');
            }
        }];
    }

    public function StoreClass(Request $request)
    {
        $request->merge(['class_code' => strtoupper(trim((string) $request->class_code))]);

        $request->validate([
            'name' => 'required|string|max:255|unique:school_class,name',
            'numeric_name' => 'required|integer|unique:school_class,numeric_name',
            'class_code' => $this->classCodeRules(),
            'description' => 'nullable|string',
        ], [
            'name.required' => 'The class name is mandatory.',
            'name.unique' => 'This class name already exists.',
            'numeric_name.required' => 'Numeric name is required.',
            'numeric_name.unique' => 'A class with this numeric identity already exists.',
            'class_code.required' => 'Class code is required.',
            'class_code.regex' => 'Class code: only capital letters and digits (no dash/space).',
        ]);

        $className = $request->name;
        $slug = Str::slug($className);
        $classCode = 'CLS-' . $request->class_code;
        // e.g. CLS-KG1

        SchoolClass::create([
            'name' => $className,
            'has_subjects' => $request->has('has_subjects'),
            'numeric_name' => $request->numeric_name,
            'class_code' => $classCode,
            'slug' => $slug,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Class Created Successfully!']);
    }
    // End Method

    public function UpdateClass(Request $request, $id)
    {
        $request->merge(['class_code' => strtoupper(trim((string) $request->class_code))]);

        $request->validate([
            'name' => 'required|unique:school_class,name,' . $id,
            'numeric_name' => 'required|numeric',
            'class_code' => $this->classCodeRules($id),
        ], [
            'name.required' => 'Class name is required.',
            'name.unique' => 'This class name is already taken.',
            'class_code.required' => 'Class code is required.',
            'class_code.regex' => 'Class code: only capital letters and digits (no dash/space).',
        ]);

        SchoolClass::findOrFail($id)->update([
            'name' => $request->name,
            'has_subjects' => $request->has('has_subjects'),
            'slug' => Str::slug($request->name),
            'numeric_name' => $request->numeric_name,
            'class_code' => 'CLS-' . $request->class_code,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Class Updated Successfully!']);
    }
    // End Method

    public function UpdateClassStatus($id)
    {
        $class = SchoolClass::findOrFail($id);
        $class->status = $class->status == 1 ? 0 : 1;
        $class->save();

        return response()->json([
            'status' => $class->status,
            'message' => 'Class status updated successfully!'
        ]);
    }
    // End Method

    public function ClassDestroy($id)
    {
        SchoolClass::findOrFail($id)->delete();
        return redirect()->back()->with(['message' => 'Class Deleted Successfully', 'alert-type' => 'error']);
    }
    // End Method
}
