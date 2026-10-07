<?php

namespace App\Http\Controllers\Backend;

use App\Exports\StudentsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    /**
     * Show the bulk import upload page.
     */
    public function index()
    {
        return view('admin.student_import.index');
    }

    /**
     * Download the sample Excel template (headers + 1 example row).
     */
    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport, 'students_import_template.xlsx');
    }

    /**
     * Validate + import the uploaded Excel file. Whole-file-reject: if any
     * row fails validation, nothing is inserted and the error list is shown.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes'    => 'Only .xlsx or .xls files are allowed.',
        ]);

        $import = new StudentsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('import_errors', [
                'File could not be processed. Please check it matches the template format. (' . $e->getMessage() . ')',
            ]);
        }

        if (!empty($import->getErrors())) {
            return back()->with('import_errors', $import->getErrors());
        }

        return redirect()->route('student_import.index')
            ->with('toastr-success', $import->getImportedCount() . ' students imported successfully.');
    }
    // End Method
}
