<?php

use App\Http\Controllers\Backend\BankAccountController;
use App\Http\Controllers\Backend\BookCategoryController;
use App\Http\Controllers\Backend\BookController;
use App\Http\Controllers\Backend\BookIssueController;
use App\Http\Controllers\Backend\ClassTimetableController;
use App\Http\Controllers\Backend\CopyOldLessonController;
use App\Http\Controllers\Backend\DiscountPolicyController;
use App\Http\Controllers\Backend\ExamController;
use App\Http\Controllers\Backend\ExamScheduleController;
use App\Http\Controllers\Backend\ExamTypeController;
use App\Http\Controllers\Backend\ExpenseCategoryController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\FeeCollectionController;
use App\Http\Controllers\Backend\FeeStructureController;
use App\Http\Controllers\Backend\FeeTypeController;
use App\Http\Controllers\Backend\FinanceReportController;
use App\Http\Controllers\Backend\FinePolicyController;
use App\Http\Controllers\Backend\HomeWorkController;
use App\Http\Controllers\Backend\IdCardTemplateController;
use App\Http\Controllers\Backend\LessonController;
use App\Http\Controllers\Backend\LessonPlanController;
use App\Http\Controllers\Backend\LibraryMemberController;
use App\Http\Controllers\Backend\LibrarySettingController;
use App\Http\Controllers\Backend\MarksGradeController;
use App\Http\Controllers\Backend\MarkSheetController;
use App\Http\Controllers\Backend\MarksheetTemplateController;
use App\Http\Controllers\Backend\PrintIdCardController;
use App\Http\Controllers\Backend\PrintMarksheetController;
use App\Http\Controllers\Backend\PrintStaffIdCardController;
use App\Http\Controllers\Backend\ResultController;
use App\Http\Controllers\Backend\SalarySlipController;
use App\Http\Controllers\Backend\RoomController;
use App\Http\Controllers\Backend\SiteSettingController;
use App\Http\Controllers\Backend\StaffAttendanceController;
use App\Http\Controllers\Backend\StaffIdCardTemplateController;
use App\Http\Controllers\Backend\StudentCategoryController;
use App\Http\Controllers\Backend\StudentHouseController;
use App\Http\Controllers\Backend\SyllabusStatusController;
use App\Http\Controllers\Backend\TeacherTimetableController;
use App\Http\Controllers\Backend\TopicController;
use App\Http\Controllers\FrontOffice\AdmissionEnquiryController;
use App\Http\Controllers\FrontOffice\ComplaintController;
use App\Http\Controllers\FrontOffice\Settings\ComplaintTypeController;
use App\Http\Controllers\FrontOffice\Settings\PurposeController;
use App\Http\Controllers\FrontOffice\Settings\SourceController;
use App\Http\Controllers\FrontOffice\VisitorController;
use Illuminate\Support\Facades\Route;


// CRUD ROUTE IN ONE GROUP
Route::middleware(['auth'])->group(function () {
    // =========================================================================
    // LEVEL 1: ACCESS PERMISSION (Standard CRUD Actions)
    // =========================================================================
    Route::middleware(['can:access-front-office'])->group(function () {

        // Admission Enquiry
        Route::controller(AdmissionEnquiryController::class)->group(function () {
            Route::get('/admission-enquiry', 'index')->name('admission-enquiry.index');
            Route::get('/admission-enquiry/create', 'create')->name('admission-enquiry.create');
            Route::post('/admission-enquiry/store', 'store')->name('admission-enquiry.store');
            Route::get('/admission-enquiry/edit/{id}', 'edit')->name('admission-enquiry.edit');
            Route::post('/admission-enquiry/update/{id}', 'update')->name('admission-enquiry.update');

            // Naya Route add krna ho to yahan direct kr skte hein, jaise Search:
            Route::get('/admission-enquiry/search', 'search')->name('admission-enquiry.search');
        });

        // Complaint
        Route::controller(ComplaintController::class)->group(function () {
            Route::get('/complain', 'index')->name('complain.index');
            Route::get('/complain/create', 'create')->name('complain.create');
            Route::post('/complain/store', 'store')->name('complain.store');
            Route::get('/complain/edit/{id}', 'edit')->name('complain.edit');
            Route::post('/complain/update/{id}', 'update')->name('complain.update');
        });

        // Visitor Book
        Route::controller(VisitorController::class)->group(function () {
            Route::get('/visitor-book', 'index')->name('visitor-book.index');
            Route::get('/visitor-book/create', 'create')->name('visitor-book.create');
            Route::post('/visitor-book/store', 'store')->name('visitor-book.store');
            Route::get('/visitor-book/edit/{id}', 'edit')->name('visitor-book.edit');
            Route::post('/visitor-book/update/{id}', 'update')->name('visitor-book.update');
            // AJAX ROUTE
            Route::get('/get-categories/{type}', 'getCategories')->name('visitor.get-categories');
            Route::get('/get-people/{type}/{id}', 'getPeople')->name('visitor.get-people');
            Route::get('visitor-book/edit-data/{id}', 'getEditData')->name('visitor.edit-data');
        });

        // Settings Sub-Folder (Nested)
        Route::prefix('settings')->name('settings.')->group(function () {

            Route::controller(ComplaintTypeController::class)->group(function () {
                Route::get('/complaint-type', 'index')->name('complaint-type.index');
                Route::post('/complaint-type/store', 'store')->name('complaint-type.store');
                Route::get('/complaint-type/edit/{id}', 'edit')->name('complaint-type.edit');
                Route::post('/complaint-type/update/{id}', 'update')->name('complaint-type.update');
            });

            Route::controller(PurposeController::class)->group(function () {
                Route::get('/purpose', 'index')->name('purpose.index');
                Route::post('/purpose/store', 'store')->name('purpose.store');
                Route::get('/purpose/edit/{id}', 'edit')->name('purpose.edit');
                Route::post('/purpose/update/{id}', 'update')->name('purpose.update');
            });

            Route::controller(SourceController::class)->group(function () {
                Route::get('/source', 'index')->name('source.index');
                Route::post('/source/store', 'store')->name('source.store');
                Route::get('/source/edit/{id}', 'edit')->name('source.edit');
                Route::post('/source/update/{id}', 'update')->name('source.update');
            });
        });
    });


    // =========================================================================
    // LEVEL 2: MANAGE PERMISSION (Status Changes, Deletion, Reports etc.)
    // =========================================================================
    Route::middleware(['can:manage-front-office'])->group(function () {

        Route::controller(AdmissionEnquiryController::class)->group(function () {
            Route::post('/admission-enquiry/status/{id}', 'UpdateEnquiryStatus')->name('admission-enquiry.status.update');
            Route::get('/admission-enquiry/delete/{id}', 'EnquiryDestroy')->name('admission-enquiry.delete');
        });

        Route::controller(ComplaintController::class)->group(function () {
            Route::get('/complain/status/{id}', 'UpdateComplaintStatus')->name('complain.status');
            Route::get('/complain/delete/{id}', 'ComplaintDestroy')->name('complain.delete');
        });

        Route::controller(VisitorController::class)->group(function () {
            Route::get('/visitor-book/delete/{id}', 'VisitorDestroy')->name('visitor-book.delete');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/complaint-type/delete/{id}', [ComplaintTypeController::class, 'ComplaintTypeDestroy'])->name('complaint-type.delete');
            Route::get('/purpose/delete/{id}', [PurposeController::class, 'PurposeDestroy'])->name('purpose.delete');
            Route::get('/source/delete/{id}', [SourceController::class, 'SourceDestroy'])->name('source.delete');
        });
    });



    // ==========================================
    // FEE STRUCTURES CRUD
    // ==========================================
    Route::prefix('fee-structure')->name('fee_structure.')->group(function () {

        // 1. General Access: Fee structures dekhna
        Route::middleware(['can:access-fee-structures'])->group(function () {
            Route::controller(FeeStructureController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-fee-types', 'getFeeTypes')->name('get_fee_types');
            });
        });

        // 2. Management Access: CRUD + generate logic
        Route::middleware(['can:manage-fee-structures'])->group(function () {
            Route::controller(FeeStructureController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');

                // Ye sensitive action hai, isliye 'manage' middleware best hai
                Route::post('/generate/{id}', 'generateFees')->name('generate');
            });
        });
    });

    // ==========================================
    // FEE TYPES CRUD
    // ==========================================
    Route::prefix('fee-types')->name('fee_types.')->group(function () {

        // 1. General Access: Sirf View/Read
        Route::middleware(['can:access-fee-types'])->group(function () {
            Route::controller(FeeTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export-excel', 'exportExcel')->name('export_excel');
                Route::get('/export-pdf', 'exportPdf')->name('export_pdf');
            });
        });

        // 2. Management Access: Create, Update, Delete
        Route::middleware(['can:manage-fee-types'])->group(function () {
            Route::controller(FeeTypeController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // BANK ACCOUNTS CRUD (School's own accounts)
    // ==========================================
    Route::prefix('bank-accounts')->name('bank_accounts.')->group(function () {

        // 1. General Access: Sirf View/Read
        Route::middleware(['can:access-bank-accounts'])->group(function () {
            Route::controller(BankAccountController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });

        // 2. Management Access: Create, Update, Delete
        Route::middleware(['can:manage-bank-accounts'])->group(function () {
            Route::controller(BankAccountController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // EXPENSE CATEGORIES CRUD
    // ==========================================
    Route::prefix('expense-categories')->name('expense_categories.')->group(function () {

        Route::middleware(['can:access-expense-categories'])->group(function () {
            Route::controller(ExpenseCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });

        Route::middleware(['can:manage-expense-categories'])->group(function () {
            Route::controller(ExpenseCategoryController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::put('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // EXPENSES CRUD
    // ==========================================
    Route::prefix('expenses')->name('expenses.')->group(function () {

        Route::middleware(['can:access-expenses'])->group(function () {
            Route::controller(ExpenseController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });

        Route::middleware(['can:manage-expenses'])->group(function () {
            Route::controller(ExpenseController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::put('/update/{id}', 'update')->name('update');
                Route::post('/{id}/pay', 'pay')->name('pay');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // SALARY SLIPS
    // ==========================================
    Route::prefix('salary-slips')->name('salary_slips.')->group(function () {

        Route::middleware(['can:access-salary-slips'])->group(function () {
            Route::controller(SalarySlipController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{id}', 'show')->name('show');
            });
        });

        Route::middleware(['can:manage-salary-slips'])->group(function () {
            Route::controller(SalarySlipController::class)->group(function () {
                Route::post('/generate', 'generate')->name('generate');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::post('/{id}/pay', 'pay')->name('pay');
                Route::delete('/{id}', 'destroy')->name('destroy');
            });
        });
    });

    // ==========================================
    // FINANCE REPORT (Income vs Expense - combined)
    // ==========================================
    Route::prefix('finance-report')->name('finance_report.')->group(function () {
        Route::middleware(['can:access-finance-report'])->group(function () {
            Route::controller(FinanceReportController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });
    });

    // ==========================================
    // STAFF ATTENDANCE
    // ==========================================
    Route::prefix('staff-attendance')->name('staff_attendance.')->group(function () {
        Route::middleware(['can:access-staff-attendance'])->group(function () {
            Route::controller(StaffAttendanceController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'CreateAttendance')->name('create');
                Route::post('/store', 'StoreAttendance')->name('store');
                Route::get('/edit', 'EditAttendance')->name('edit');
                Route::post('/update', 'UpdateAttendance')->name('update');
                Route::get('/report', 'ReportAttendnce')->name('report');
                Route::get('/{user}/show', 'ShowAttendance')->name('show');
            });
        });
        Route::middleware(['can:manage-staff-attendance'])->group(function () {
            Route::controller(StaffAttendanceController::class)->group(function () {
                Route::post('/mark-holiday', 'markHoliday')->name('mark_holiday');
            });
        });
    });

    // ==========================================
    // FEE COLLECTION (Collect Fees screen)
    // ==========================================
    Route::prefix('fees')->name('fees.')->group(function () {
        Route::middleware(['can:access-fee-collections'])->group(function () {
            Route::controller(FeeCollectionController::class)->group(function () {
                Route::get('/collect', 'index')->name('collect.index');
                Route::get('/collect/get-sections/{classId}', 'getSections')->name('collect.get_sections');
                Route::get('/collect/{student}', 'show')->name('collect.show');
                Route::get('/collect/receipt/{transaction}', 'receipt')->name('collect.receipt');
            });
        });
        Route::middleware(['can:manage-fee-collections'])->group(function () {
            Route::controller(FeeCollectionController::class)->group(function () {
                Route::post('/collect/{studentFee}/pay', 'pay')->name('collect.pay');
                Route::post('/collect/bulk-pay', 'bulkPay')->name('collect.bulk_pay');
                Route::post('/collect/{studentFee}/exclude', 'exclude')->name('collect.exclude');   // 🔥 naya
                Route::post('/collect/{studentFee}/reassign', 'reassign')->name('collect.reassign'); // 🔥 naya
                Route::get('/collect-report', 'report')->name('collect.report');
            });
        });
    });
    // ==========================================
    // DISCOUNT POLICIES CRUD (Fee Settings)
    // ==========================================
    Route::prefix('discount-policies')->name('discount_policies.')->group(function () {

        Route::middleware(['can:access-discount-policies'])->group(function () {
            Route::controller(DiscountPolicyController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });

        Route::middleware(['can:manage-discount-policies'])->group(function () {
            Route::controller(DiscountPolicyController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::patch('/toggle-status/{id}', 'toggleStatus')->name('toggle_status');
            });
        });
    });

    // ==========================================
    // FINE POLICIES CRUD (Fee Settings)
    // ==========================================
    Route::prefix('fine-policies')->name('fine_policies.')->group(function () {

        Route::middleware(['can:access-fine-policies'])->group(function () {
            Route::controller(FinePolicyController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });

        Route::middleware(['can:manage-fine-policies'])->group(function () {
            Route::controller(FinePolicyController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::patch('/toggle-status/{id}', 'toggleStatus')->name('toggle_status');
            });
        });
    });


    // ==========================================
    // STUDENT CATEGORY
    // ==========================================
    Route::prefix('student-category')->name('student-category.')->group(function () {
        Route::middleware(['can:access-student-categories'])->group(function () {
            Route::controller(StudentCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/{studentCategory}/edit', 'edit')->name('edit');
            });
        });
        Route::middleware(['can:manage-student-categories'])->group(function () {
            Route::controller(StudentCategoryController::class)->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{studentCategory}', 'update')->name('update');
                Route::delete('/{studentCategory}', 'destroy')->name('delete');
                Route::get('/{studentCategory}/toggle-status', 'toggleStatus')->name('toggle-status');
            });
        });
    });

    // ==========================================
    // HOUSE
    // ==========================================
    Route::prefix('student-house')->name('student-house.')->group(function () {
        Route::middleware(['can:access-student-houses'])->group(function () {
            Route::controller(StudentHouseController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/{house}/edit', 'edit')->name('edit');
            });
        });
        Route::middleware(['can:manage-student-houses'])->group(function () {
            Route::controller(StudentHouseController::class)->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{house}', 'update')->name('update');
                Route::delete('/{house}', 'destroy')->name('delete');
                Route::get('/{house}/toggle-status', 'toggleStatus')->name('toggle-status');
            });
        });
    });

    // ==========================================
    // BOOK CATEGORIES CRUD
    // ==========================================
    Route::prefix('book-category')->name('book_category.')->group(function () {

        // 1. General Access: Book categories dekhna
        Route::middleware(['can:access-book-categories'])->group(function () {
            Route::controller(BookCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });

        // 2. Management Access: CRUD
        Route::middleware(['can:manage-book-categories'])->group(function () {
            Route::controller(BookCategoryController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::get('/toggle-status/{id}', 'toggleStatus')->name('toggle_status');
            });
        });
    });

    // ==========================================
    // BOOKS CRUD
    // ==========================================
    Route::prefix('books')->name('books.')->group(function () {

        // 1. General Access: Books dekhna
        Route::middleware(['can:access-books'])->group(function () {
            Route::controller(BookController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/show/{id}', 'show')->name('show');
            });
        });

        // 2. Management Access: CRUD
        Route::middleware(['can:manage-books'])->group(function () {
            Route::controller(BookController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::get('/toggle-status/{id}', 'toggleStatus')->name('toggle_status');
            });
        });
    });

    // ==========================================
    // LIBRARY SETTINGS (Single Row Settings)
    // ==========================================
    Route::prefix('library-settings')->name('library_settings.')->group(function () {
        Route::middleware('can:access-library-settings')->group(function () {
            Route::controller(LibrarySettingController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });
        Route::middleware('can:manage-library-settings')->group(function () {
            Route::controller(LibrarySettingController::class)->group(function () {
                Route::post('/update', 'update')->name('update');
            });
        });
    });

    // ==========================================
    // LIBRARY MEMBERS (Sub-Section Uner Library Settings)
    // ==========================================
    Route::prefix('library-settings/members')->name('library_settings.members.')->group(function () {
        Route::middleware('can:access-library-members')->group(function () {   // <-- changed
            Route::controller(LibraryMemberController::class)->group(function () {
                Route::get('/student', 'studentPage')->name('student');
                Route::get('/staff', 'staffPage')->name('staff');
                Route::get('/student/search', 'searchStudents')->name('student.search');
                Route::get('/staff/search', 'searchStaff')->name('staff.search');
            });
        });
        Route::middleware('can:manage-library-members')->group(function () {   // <-- changed
            Route::controller(LibraryMemberController::class)->group(function () {
                Route::post('/student/add/{id}', 'addStudent')->name('student.create');
                Route::post('/student/remove/{id}', 'removeStudent')->name('student.remove');
                Route::post('/staff/add/{id}', 'addStaff')->name('staff.create');
                Route::post('/staff/remove/{id}', 'removeStaff')->name('staff.remove');
            });
        });
    });

    // ==========================================
    // BOOK ISSUES (Issue / Return) CRUD
    // ==========================================
    Route::prefix('book-issue')->name('book_issue.')->group(function () {
        Route::middleware('can:access-book-issues')->group(function () {
            Route::controller(BookIssueController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/show/{id}', 'show')->name('show');
                Route::get('/search-book', 'searchBook')->name('search_book');
                Route::get('/search-member', 'searchMember')->name('search_member');
                Route::get('/reports', 'reportsIndex')->name('reports');
                Route::get('/reports/data', 'reportsData')->name('reports.data');
            });
        });
        Route::middleware('can:manage-book-issues')->group(function () {
            Route::controller(BookIssueController::class)->group(function () {
                Route::post('/store', 'store')->name('store');
                Route::post('/return/{id}', 'returnBook')->name('return');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // HOME WORKS CRUD
    // ==========================================
    Route::prefix('home-work')->name('home_work.')->group(function () {

        Route::middleware('can:access-home-works')->group(function () {
            Route::controller(HomeWorkController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/list', 'list')->name('list');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-groups/{classId}', 'getGroupsByClass')->name('get_groups');
                Route::get('/get-subjects', 'getSubjects')->name('get_subjects');
            });
        });

        Route::middleware('can:manage-home-works')->group(function () {
            Route::controller(HomeWorkController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::post('/status/{id}', 'statusUpdate')->name('status');
            });
        });
    });

    // ==========================================
    // CLASS TIMETABLE CRUD
    // ==========================================
    Route::prefix('class-timetable')->name('class_timetable.')->group(function () {

        Route::middleware('can:access-class-timetable')->group(function () {
            Route::controller(ClassTimetableController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-data', 'getData')->name('get_data');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-groups/{classId}', 'getGroupsByClass')->name('get_groups');
                Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
            });
        });

        Route::middleware('can:manage-class-timetable')->group(function () {
            Route::controller(ClassTimetableController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/save', 'save')->name('save');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::get('/teacher-availability', 'teacherAvailability')->name('teacher_availability'); // NEW 04-10-26
                Route::get('/busy-teachers', 'busyTeachers')->name('busy_teachers'); // NEW 05-10-26
            });
        });
    });

    // ==========================================
    // ROOMS (MASTER)
    // ==========================================
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::middleware('can:access-rooms')->group(function () {
            Route::controller(RoomController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
            });
        });
        Route::middleware('can:manage-rooms')->group(function () {
            Route::controller(RoomController::class)->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{room}', 'update')->name('update');
                Route::delete('/{room}', 'destroy')->name('delete');
                Route::post('/{room}/toggle-status', 'toggleStatus')->name('toggle-status');
            });
        });
    });

    // ==========================================
    // TEACHER TIMETABLE (READ-ONLY)
    // ==========================================
    Route::prefix('teacher-timetable')->name('teacher_timetable.')->group(function () {

        Route::middleware('can:access-teacher-timetable')->group(function () {
            Route::controller(TeacherTimetableController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-data/{teacherId}', 'getData')->name('get_data');
                Route::get('/get-teacher-info/{teacherId}', 'getTeacherInfo')->name('get_teacher_info');
            });
        });
    });

    // ==========================================
    // LESSON CRUD
    // ==========================================
    Route::prefix('lesson')->name('lesson.')->group(function () {

        Route::middleware('can:access-lessons')->group(function () {
            Route::controller(LessonController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
            });
        });

        Route::middleware('can:manage-lessons')->group(function () {
            Route::controller(LessonController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/save', 'save')->name('save');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::post('/toggle-status/{id}', 'toggleStatus')->name('toggle_status'); // NEW
            });
        });
    });

    // ==========================================
    // TOPIC CRUD
    // ==========================================
    Route::prefix('topic')->name('topic.')->group(function () {

        Route::middleware('can:access-topics')->group(function () {
            Route::controller(TopicController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-data', 'getData')->name('get_data');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
                Route::get('/get-lessons/{subjectId}', 'getLessonsBySubject')->name('get_lessons');
            });
        });

        Route::middleware('can:manage-topics')->group(function () {
            Route::controller(TopicController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::post('/save', 'save')->name('save');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::post('/toggle-status/{id}', 'toggleStatus')->name('toggle-status');  // NEW
            });
        });
    });

    // ==========================================
    // MANAGE LESSON PLAN
    // ==========================================
    Route::prefix('lesson_plan')->name('lesson_plan.')->group(function () {

        Route::middleware('can:access-lesson-plans')->group(function () {
            Route::controller(LessonPlanController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-slots/{teacherId}', 'getTeacherSlots')->name('get_slots');
                Route::get('/view/{id}', 'view')->name('view');
                Route::get('/get-lessons', 'getLessons')->name('get_lessons');        // naya
                Route::get('/get-topics/{lessonId}', 'getTopics')->name('get_topics'); // naya
            });
        });

        Route::middleware('can:manage-lesson-plans')->group(function () {
            Route::controller(LessonPlanController::class)->group(function () {
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::put('/update/{id}', 'update')->name('update');
                Route::post('/save', 'save')->name('save');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
                Route::post('/comment/save', 'saveComment')->name('comment.save');
                Route::post('upload-image', 'uploadImage')->name('upload_image');
            });
        });
    });

    // ==========================================
    // COPY OLD LESSONS
    // ==========================================
    Route::prefix('copy_old_lesson')->name('copy_old_lesson.')->group(function () {

        Route::middleware('can:access-copy-old-lessons')->group(function () {
            Route::controller(CopyOldLessonController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSections')->name('get_sections');
                Route::get('/get-subjects/{classId}', 'getSubjects')->name('get_subjects');
                Route::get('/get-topics', 'getTopics')->name('get_topics');
            });
        });

        Route::middleware('can:manage-copy-old-lessons')->group(function () {
            Route::controller(CopyOldLessonController::class)->group(function () {
                Route::post('/copy', 'copy')->name('copy');
            });
        });
    });

    // ==========================================
    // MANAGE SYLLABUS STATUS
    // ==========================================
    Route::prefix('syllabus_status')->name('syllabus_status.')->group(function () {

        Route::middleware('can:access-syllabus-status')->group(function () {
            Route::controller(SyllabusStatusController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
            });
        });

        Route::middleware('can:manage-syllabus-status')->group(function () {
            Route::controller(SyllabusStatusController::class)->group(function () {
                Route::post('/toggle/{id}', 'toggleStatus')->name('toggle_status');
                Route::post('/update-date/{id}', 'updateCompletionDate')->name('update_date');
            });
        });
    });

    // ==========================================
    // EXAM TYPE (Master Data)
    // ==========================================
    Route::prefix('exam_type')->name('exam_type.')->group(function () {
        Route::middleware('can:access-exam-types')->group(function () {
            Route::controller(ExamTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });
        Route::middleware('can:manage-exam-types')->group(function () {
            Route::controller(ExamTypeController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // EXAM
    // ==========================================
    Route::prefix('exam')->name('exam.')->group(function () {
        Route::middleware('can:access-exams')->group(function () {
            Route::controller(ExamController::class)->group(function () {
                Route::get('/', 'index')->name('index');
            });
        });
        Route::middleware('can:manage-exams')->group(function () {
            Route::controller(ExamController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // EXAM SCHEDULE
    // ==========================================
    Route::prefix('exam_schedule')->name('exam_schedule.')->group(function () {
        Route::middleware('can:access-exam-schedules')->group(function () {
            Route::controller(ExamScheduleController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
                Route::get('/get-default-marks/{classId}/{subjectId}', 'getDefaultMarks')->name('get_default_marks');
            });
        });
        Route::middleware('can:manage-exam-schedules')->group(function () {
            Route::controller(ExamScheduleController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // MARKS GRADE
    // ==========================================
    Route::prefix('marks_grade')->name('marks_grade.')->group(function () {
        Route::middleware('can:access-marks-grades')->group(function () {
            Route::controller(MarksGradeController::class)->group(function () {
                Route::get('/{examId}', 'index')->name('index');
            });
        });
        Route::middleware('can:manage-marks-grades')->group(function () {
            Route::controller(MarksGradeController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::post('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'destroy')->name('delete');
            });
        });
    });

    // ==========================================
    // MARKSHEET
    // ==========================================
    Route::prefix('marksheet')->name('marksheet.')->group(function () {
        Route::middleware('can:access-marksheets')->group(function () {
            Route::controller(MarkSheetController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
            });
        });
        Route::middleware('can:manage-marksheets')->group(function () {
            Route::controller(MarkSheetController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
            });
        });
    });

    // ==========================================
    // MARKSHEET TEMPLATE (Design Marksheet)
    // ==========================================
    Route::prefix('marksheet-template')->name('marksheet_template.')->group(function () {
        Route::middleware('can:access-marksheet-templates')->group(function () {
            Route::controller(MarksheetTemplateController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}/edit', 'edit')->name('edit');
            });
        });
        Route::middleware('can:manage-marksheet-templates')->group(function () {
            Route::controller(MarksheetTemplateController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::put('/{id}/update', 'update')->name('update');
                Route::delete('/{id}/delete', 'destroy')->name('destroy');
            });
        });
    });

    // ==========================================
    // PRINT MARKSHEET
    // ==========================================
    Route::prefix('print-marksheet')->name('print_marksheet.')->group(function () {
        Route::middleware('can:access-marksheets')->group(function () {
            Route::controller(PrintMarksheetController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-students/{examId}/{classId}/{sectionId}', 'getStudents')->name('get_students');
                Route::post('/generate', 'generate')->name('generate');
            });
        });
    });

    // ==========================================
    // RESULT  
    // ==========================================
    Route::prefix('result')->name('result.')->group(function () {
        Route::middleware('can:access-results')->group(function () {
            Route::controller(ResultController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
            });
        });
        Route::middleware('can:manage-results')->group(function () {
            Route::controller(ResultController::class)->group(function () {
                Route::post('/calculate', 'calculate')->name('calculate');
            });
        });
    });

    // ==========================================
    // SITE SETTING
    // ==========================================
    Route::prefix('site-setting')->name('site_setting.')->group(function () {
        Route::middleware('can:access-site-settings')->group(function () {
            Route::controller(SiteSettingController::class)->group(function () {
                Route::get('/', 'edit')->name('edit');
            });
        });
        Route::middleware('can:manage-site-settings')->group(function () {
            Route::controller(SiteSettingController::class)->group(function () {
                Route::post('/', 'update')->name('update');
            });
        });
    });

    // ==========================================
    // STUDENT ID CARD TEMPLATE
    // ==========================================
    Route::prefix('id-card-template')->name('id_card_template.')->group(function () {
        Route::middleware('can:access-id-card-templates')->group(function () {
            Route::controller(IdCardTemplateController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/edit/{id}', 'edit')->name('edit');
                Route::get('/preview/{id}', 'preview')->name('preview');
            });
        });
        Route::middleware('can:manage-id-card-templates')->group(function () {
            Route::controller(IdCardTemplateController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::put('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'delete')->name('delete');
            });
        });
    });

    // ==========================================
    // STAFF ID CARD TEMPLATE
    // ==========================================
    Route::prefix('staff-id-card-template')->name('staff_id_card_template.')->group(function () {
        Route::middleware('can:access-staff-id-card-templates')->group(function () {
            Route::controller(StaffIdCardTemplateController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/edit/{id}', 'edit')->name('edit');
            });
        });
        Route::middleware('can:manage-staff-id-card-templates')->group(function () {
            Route::controller(StaffIdCardTemplateController::class)->group(function () {
                Route::post('/save', 'save')->name('save');
                Route::put('/update/{id}', 'update')->name('update');
                Route::delete('/delete/{id}', 'delete')->name('delete');
            });
        });
    });

    // ==========================================
    // PRINT STUDENT ID CARD
    // ==========================================
    Route::prefix('print-id-card')->name('print_id_card.')->group(function () {
        Route::middleware('can:access-id-card-templates')->group(function () {
            Route::controller(PrintIdCardController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
                Route::get('/get-students/{classId}/{sectionId}', 'getStudents')->name('get_students');
                Route::post('/generate', 'generate')->name('generate');
            });
        });
    });

    // ==========================================
    // PRINT STAFF ID CARD
    // ==========================================
    Route::prefix('print-staff-id-card')->name('print_staff_id_card.')->group(function () {
        Route::middleware('can:access-staff-id-card-templates')->group(function () {
            Route::controller(PrintStaffIdCardController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/get-staff/{role}', 'getStaffByRole')->name('get_staff');
                Route::post('/generate', 'generate')->name('generate');
            });
        });
    });
});
