<?php

namespace App\Traits;

use App\Imports\ValidatedImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

trait Importable
{
    protected function runImport(Request $request, ValidatedImport $import, string $label)
    {
        $request->validate(
            ['file' => 'required|file|mimes:xlsx,xls|max:5120'],
            [
                'file.required' => 'Please select a file to import.',
                'file.mimes'    => 'Only .xlsx or .xls files are allowed.',
                'file.max'      => 'File must be smaller than 5 MB.',
            ]
        );

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('import_errors', ['File could not be processed. Please use the sample template.']);
        }

        if ($import->getErrors()) {
            return back()->with('import_errors', $import->getErrors());
        }

        return back()->with('toastr-success', $import->getImportedCount() . " {$label} imported successfully.");
    }
}