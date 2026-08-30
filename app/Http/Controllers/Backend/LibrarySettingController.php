<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\LibrarySetting;
use Illuminate\Http\Request;

class LibrarySettingController extends Controller
{
    public function index()
    {
        // Hamesha ek hi row honi chahiye - agar exist nahi karti to default row bana do
        $settings = LibrarySetting::first();

        if (!$settings) {
            $settings = LibrarySetting::create([
                'max_issue_days' => 14,
                'fine_per_day' => 0,
                'max_books_per_student' => 2,
                'max_books_per_staff' => 5,
            ]);
        }

        return view('admin.library_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'max_issue_days' => 'required|integer|min:1',
            'fine_per_day' => 'required|numeric|min:0',
            'max_books_per_student' => 'required|integer|min:1',
            'max_books_per_staff' => 'required|integer|min:1',
        ]);

        $settings = LibrarySetting::first();

        if (!$settings) {
            $settings = new LibrarySetting();
        }

        $settings->fill($request->only([
            'max_issue_days',
            'fine_per_day',
            'max_books_per_student',
            'max_books_per_staff',
        ]))->save();

        return response()->json(['message' => 'Library settings updated successfully.']);
    }
}
