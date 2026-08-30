<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\Enrollment;
use App\Models\LibraryMember;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class BookIssueController extends Controller
{
    public function index()
    {
        $issues = BookIssue::with(['book', 'libraryMember'])->latest()->get();
        return view('admin.book_issue.index', compact('issues'));
    }
    // End Method

    public function show($id)
    {
        $issue = BookIssue::with(['book', 'libraryMember', 'issuedBy', 'returnedBy'])->findOrFail($id);

        $memberDetail = null;
        $enrollment = null;
        $classTeacher = null;
        $discount = null;
        $staffDetail = null;
        $staffType = null;

        if ($issue->libraryMember->member_type === 'student') {

            $memberDetail = Student::with(['category', 'house', 'parent'])->find($issue->libraryMember->member_id);

            if ($memberDetail) {
                $enrollment = Enrollment::with(['schoolClass', 'section', 'group', 'academicYear'])
                    ->where('student_id', $memberDetail->id)
                    ->where('enroll_status', 1)
                    ->latest()
                    ->first();

                $discount = StudentDiscount::with('discountPolicy')
                    ->where('student_id', $memberDetail->id)
                    ->where('status', 1)
                    ->first();

                if ($enrollment) {
                    $assignment = TeacherAssignment::with('teacher')
                        ->where('class_id', $enrollment->class_id)
                        ->where('section_id', $enrollment->section_id)
                        ->where('academic_year_id', $enrollment->academic_year_id)
                        ->first();

                    $classTeacher = $assignment->teacher ?? null;
                }
            }
        } else {
            // staff: har role model try karo jab tak match na mile
            $modelMap = [
                'teacher'      => \App\Models\Teacher::class,
                'accountant'   => \App\Models\Accountant::class,
                'receptionist' => \App\Models\Receptionist::class,
                //'librarian'    => \App\Models\Librarian::class,
            ];

            foreach ($modelMap as $roleKey => $modelClass) {
                $found = $modelClass::with('salary')->where('user_id', $issue->libraryMember->member_id)->first();
                if ($found) {
                    $staffDetail = $found;
                    $staffType = $roleKey;
                    break;
                }
            }
        }

        return view('admin.book_issue.show', compact(
            'issue',
            'memberDetail',
            'enrollment',
            'classTeacher',
            'discount',
            'staffDetail',
            'staffType'
        ));
    }
    // End Method

    // ---------- AJAX: Book search (issue form ke liye) ----------
    public function searchBook(Request $request)
    {
        $term = $request->get('term', '');

        $books = Book::where('status', 1)
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('isbn', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'title', 'isbn', 'available_copies']);

        return response()->json($books);
    }
    // End Method

    // ---------- AJAX: Member search (card no ya naam se) ----------
    public function searchMember(Request $request)
    {
        $term = $request->get('term', '');

        $byCard = LibraryMember::where('library_card_no', 'like', "%{$term}%")->get();

        $studentIds = Student::where('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%")
            ->pluck('id');

        $byStudentName = LibraryMember::where('member_type', 'student')
            ->whereIn('member_id', $studentIds)
            ->get();

        $userIds = User::where('name', 'like', "%{$term}%")->pluck('id');

        $byStaffName = LibraryMember::where('member_type', 'staff')
            ->whereIn('member_id', $userIds)
            ->get();

        $members = $byCard->merge($byStudentName)->merge($byStaffName)->unique('id')->take(10);

        $data = $members->map(function ($m) {
            return [
                'id' => $m->id,
                'library_card_no' => $m->library_card_no,
                'name' => $m->member_name,
                'type' => ucfirst($m->member_type),
            ];
        })->values();

        return response()->json($data);
    }

    public function reportsIndex()
    {
        $categories = BookCategory::where('status', 1)->get();
        return view('admin.book_issue.reports', compact('categories'));
    }
    // End Method

    public function reportsData(Request $request)
    {
        $query = BookIssue::with(['book.category', 'libraryMember']);

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }
        if ($request->filled('member_type')) {
            $query->whereHas('libraryMember', fn($q) => $q->where('member_type', $request->member_type));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('book', fn($q) => $q->where('book_category_id', $request->category_id));
        }

        $issues = $query->latest()->get();

        $data = $issues->map(function ($issue) {
            return [
                'book_title' => $issue->book->title ?? '-',
                'category' => $issue->book->category->name ?? '-',
                'member_name' => $issue->libraryMember->member_name ?? '-',
                'member_type' => ucfirst($issue->libraryMember->member_type ?? '-'),
                'issue_date' => $issue->issue_date,
                'due_date' => $issue->due_date,
                'return_date' => $issue->return_date ?? '-',
                'status' => $issue->status,
                'fine_amount' => $issue->fine_amount,
            ];
        });

        return response()->json([
            'issues' => $data,
            'summary' => [
                'total_issued' => $issues->count(),
                'total_returned' => $issues->where('status', 'returned')->count(),
                'total_overdue' => $issues->where('status', 'overdue')->count(),
                'total_fine' => $issues->sum('fine_amount'),
            ],
        ]);
    }
    // End Method

    // ---------- Issue Book ----------
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'library_member_id' => 'required|exists:library_members,id',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        $book = Book::findOrFail($request->book_id);

        if ($book->available_copies < 1) {
            return response()->json(['message' => 'No copies available for this book.'], 422);
        }

        $alreadyIssued = BookIssue::where('book_id', $book->id)
            ->where('library_member_id', $request->library_member_id)
            ->where('status', 'issued')
            ->exists();

        if ($alreadyIssued) {
            return response()->json(['message' => 'This book is already issued to this member.'], 422);
        }

        BookIssue::create([
            'book_id' => $book->id,
            'library_member_id' => $request->library_member_id,
            'issue_date' => now()->toDateString(),
            'due_date' => $request->due_date,
            'status' => 'issued',
            'issued_by' => auth()->id(),
        ]);

        $book->decrement('available_copies');

        return response()->json(['message' => 'Book issued successfully.']);
    }

    // ---------- Return Book ----------
    public function returnBook($id)
    {
        $issue = BookIssue::findOrFail($id);

        if ($issue->status === 'returned') {
            return response()->json(['message' => 'This book is already returned.'], 422);
        }

        $issue->update([
            'return_date' => now()->toDateString(),
            'status' => 'returned',
            'returned_by' => auth()->id(),
        ]);

        $issue->book()->increment('available_copies');

        return response()->json(['message' => 'Book returned successfully.']);
    }

    // ---------- Update (due date extend) ----------
    public function update(Request $request, $id)
    {
        $request->validate(['due_date' => 'required|date']);

        $issue = BookIssue::findOrFail($id);
        $issue->update(['due_date' => $request->due_date]);

        return response()->json(['message' => 'Due date updated successfully.']);
    }

    // ---------- Delete ----------
    public function destroy($id)
    {
        $issue = BookIssue::findOrFail($id);

        if ($issue->status !== 'returned') {
            $issue->book()->increment('available_copies');
        }

        $issue->delete();

        return response()->json(['message' => 'Record deleted successfully.']);
    }
}
