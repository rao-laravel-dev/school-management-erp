<?php

use App\Http\Controllers\Accountant\AccountantProfileController;
use App\Http\Controllers\Backend\AcademicCalendarController;
use App\Http\Controllers\Backend\AcademicYearController;
use App\Http\Controllers\Backend\AccountantController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\AttendanceController;
use App\Http\Controllers\Backend\ClassController;
use App\Http\Controllers\Backend\EventTypeController;
use App\Http\Controllers\Backend\GroupController;
use App\Http\Controllers\Backend\LibrariansController;
use App\Http\Controllers\Backend\ParentsController;
use App\Http\Controllers\Backend\PrintReportCardController;
use App\Http\Controllers\Backend\ReceptionController;
use App\Http\Controllers\Backend\ReportCardTemplateController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SchoolClassSectionController;
use App\Http\Controllers\Backend\SchoolClassSubjectController;
use App\Http\Controllers\Backend\SchoolTimingController;
use App\Http\Controllers\Backend\SectionController;
use App\Http\Controllers\Backend\SkillAssessmentAreaController;
use App\Http\Controllers\Backend\SkillAssessmentEntryController;
use App\Http\Controllers\Backend\SkillCategoryController;
use App\Http\Controllers\Backend\StaffLeaveApplicationController;
use App\Http\Controllers\Backend\StaffLeaveSettingController;
use App\Http\Controllers\Backend\StudentController;
use App\Http\Controllers\Backend\SubjectController;
use App\Http\Controllers\Backend\SuperAdminController;
use App\Http\Controllers\Backend\TeacherAssignmentController;
use App\Http\Controllers\Backend\TeachersController;
use App\Http\Controllers\Backend\TeacherTimetableController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Receptionist\ReceptionistProfileController;
use App\Http\Controllers\Students\StudentProfileController;
use App\Http\Controllers\Teacher\TeacherLessonPlanController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Frontend Public Routes
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'HomeIndex')->name('home');
    Route::get('/about', 'about')->name('about');
    Route::get('/contact', 'contact')->name('contact');
    // Baki public pages yahan add karein
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    /** @var \App\Models\User $user */
    $user = Auth::user();

    if ($user->hasRole('superadmin'))
        return redirect()->route('superadmin.dashboard');

    if ($user->hasRole('admin'))
        return redirect()->route('admin.dashboard');

    if ($user->hasRole('receptionist'))
        return redirect()->route('reception.dashboard');

    // Student ke liye thoda behtar logic:
    if ($user->hasRole('student')) {
        // Agar woh pehle se dashboard par nahi hai, tabhi redirect karein
        if (!request()->is('student.dashboard')) {
            return redirect()->route('student.dashboard');
        }
        // Agar pehle se wahan hai, toh kuch na karein (ya yahan se bhi return kar sakte hain)
        return;
    }

    // Default Fallback (agar koi role match na kare)
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// 🔥 COMMON PROFILE (ALL ROLES)
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// SUPER-ADMIN-ROUTES
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    Route::controller(SuperAdminController::class)->group(function () {

        Route::get('/dashboard', 'SuperAdminManage')->name('dashboard');
        Route::get('/logout', 'SuperAdminLogout')->name('logout');
        Route::get('/profile', 'SuperAdminProfile')->name('profile');
        Route::post('/profile/update', 'SuperAdminProfileUpdate')->name('profile.update');
    });
});
//SUPER-ADMIN LOGIN 
Route::get('superadmin/login', [SuperAdminController::class, 'SuperAdminLogin'])->name('superadmin.login');



// ADMIN ROUTES
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::controller(AdminController::class)->group(function () {

        Route::get('/dashboard', 'AdminManage')->name('dashboard');
        Route::get('/logout', 'AdminLogout')->name('logout');
        Route::get('/profile', 'AdminProfile')->name('profile');
        Route::post('/profile/update', 'AdminProfileUpdate')->name('profile.update');
        Route::get('/change/password', 'AdminChangePassword')->name('change.password');
        Route::post('/password/update', 'AdminPasswordUpdate')->name('password.update');
    });
});
//ADMIN LOGIN 
Route::get('admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login');



// RECEPTIONIST ROUTES
Route::middleware(['auth', 'role:receptionist'])->prefix('reception')->name('reception.')->group(function () {

    Route::controller(ReceptionistProfileController::class)->group(function () {

        Route::get('/dashboard', 'ReceptionistManage')->name('dashboard');
        Route::get('/logout', 'ReceptionLogout')->name('logout');
        Route::get('/profile', 'ReceptionistProfile')->name('profile');
        Route::post('/profile/update', 'ReceptionistProfileUpdate')->name('profile.update');
    });
});
// RECEPTIONIST LOGIN
Route::get('reception/login', [ReceptionistProfileController::class, 'ReceptionLogin'])->name('reception.login');


// ACCOUNTANTS ROUTES
Route::middleware(['auth', 'role:accountant'])->prefix('accountant')->name('accountant.')->group(function () {

    Route::controller(AccountantProfileController::class)->group(function () {

        Route::get('/dashboard', 'AccountantManage')->name('dashboard');
        Route::get('/logout', 'AccountantLogout')->name('logout');
        Route::get('/profile', 'AccountantProfile')->name('profile');
        Route::post('/profile/update', 'AccountantProfileUpdate')->name('profile.update');
    });
});
// ACCOUNTANTS LOGIN
Route::get('accountant/login', [AccountantProfileController::class, 'AccountantLogin'])->name('accountant.login');


// LIBRARIANS ROUTES
Route::middleware(['auth', 'role:librarian'])->prefix('librarian')->name('librarian.')->group(function () {

    Route::controller(LibrariansController::class)->group(function () {

        Route::get('/dashboard', 'LibrarianManage')->name('dashboard');
        Route::get('/logout', 'LibrarianLogout')->name('logout');
        Route::get('/profile', 'LibrarianProfile')->name('profile');
        Route::post('/profile/update', 'LibrarianProfileUpdate')->name('profile.update');
    });
});
// LIBRARIANS LOGIN
Route::get('librarian/login', [LibrariansController::class, 'LibrarianLogin'])->name('librarian.login');


// TEACHERS ROUTES
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::controller(TeacherProfileController::class)->group(function () {
        Route::get('/dashboard', 'TeacherManage')->name('dashboard');
        Route::get('/logout', 'TeacherLogout')->name('logout');
        Route::get('/profile', 'TeacherProfile')->name('profile');
        Route::post('/profile/update', 'TeacherProfileUpdate')->name('profile.update');

        Route::middleware('can:access-my-exam-schedule')->group(function () {
            Route::get('/exam-schedule', 'myExamSchedule')->name('exam_schedule.index');
        });

        Route::middleware('can:access-my-syllabus-status')->group(function () {
            Route::get('/syllabus-status', 'mySyllabusStatus')->name('syllabus_status.index');
        });

        Route::middleware('can:access-my-salary-slips')->group(function () {
            Route::get('/salary-slips', 'mySalarySlips')->name('salary_slips.index');
        });

        Route::middleware('can:access-my-timetable')->group(function () {
            Route::get('/timetable', 'myTimetable')->name('timetable.index');
            Route::get('/timetable/get-data/{teacherId}', [TeacherTimetableController::class, 'getData'])->name('timetable.get_data');
        });
    });

    // ==========================================
    // TEACHER LESSON PLAN — ab isi group ke andar
    // ==========================================
    Route::middleware('can:access-my-lesson-plan')->group(function () {
        Route::controller(TeacherLessonPlanController::class)->group(function () {
            Route::get('/lesson-plan', 'index')->name('lesson_plan.index');
            Route::get('/lesson-plan/get-slots', 'getTeacherSlots')->name('lesson_plan.get_slots');
            Route::get('/lesson-plan/get-lessons', 'getLessons')->name('lesson_plan.get_lessons');
            Route::get('/lesson-plan/get-topics/{lessonId}', 'getTopics')->name('lesson_plan.get_topics');
            Route::post('/lesson-plan/quick-add-lesson', 'quickAddLesson')->name('lesson_plan.quick_lesson');
            Route::post('/lesson-plan/quick-add-topic', 'quickAddTopic')->name('lesson_plan.quick_topic');
            Route::post('/lesson-plan/save', 'save')->name('lesson_plan.save');
            Route::post('/lesson-plan/upload-image', 'uploadImage')->name('lesson_plan.upload_image');
            Route::post('/lesson-plan/comment/save', 'saveComment')->name('lesson_plan.comment.save');
            Route::put('/lesson-plan/update/{id}', 'update')->name('lesson_plan.update');
            Route::get('/lesson-plan/edit/{id}', 'edit')->name('lesson_plan.edit');
            Route::get('/lesson-plan/view/{id}', 'view')->name('lesson_plan.view');
        });
    });
});
// TEACHERS LOGIN
Route::get('teacher/login', [TeacherProfileController::class, 'TeacherLogin'])->name('teacher.login');


// PARENTS ROUTES
Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::controller(ParentsController::class)->group(function () {
        Route::get('/dashboard', 'ParentManage')->name('dashboard');
        Route::get('/student-dashboard/{id}', 'StudentDashboard')->name('students.dashboard');
        Route::get('/student-details/{id}', 'StudentDashboard')->name('students.details');
        Route::get('/logout', 'ParentLogout')->name('logout');
        Route::get('/profile', 'ParentProfile')->name('profile');
        Route::post('/profile/update', 'ParentProfileUpdate')->name('profile.update');
        // UPDATED: Results (simple summary, not marksheet template)
        Route::get('/student/{id}/results', 'resultIndex')->name('students.results.index');
        Route::get('/student/{id}/results/{result}', 'resultDetails')->name('students.results.details');
    });
});
// PARENTS LOGIN
Route::get('parent/login', [ParentsController::class, 'ParentLogin'])->name('parent.login');
// STUDENTS ROUTES
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Student Basic Operations
    Route::controller(StudentProfileController::class)->group(function () {
        Route::get('/dashboard', 'StudentManage')->name('dashboard');
        Route::get('/logout', 'StudentLogout')->name('logout');
        Route::get('/profile', 'StudentProfile')->name('profile');
        Route::post('/profile/update', 'StudentProfileUpdate')->name('profile.update');
        // NAYA ROUTE: Iska auto name 'student.teachers.index' banega
        Route::get('/teacher/myteacher', 'myTeachers')->name('teacher.myteacher');
        Route::get('/my-subjects', 'viewMySubjects')->name('subjects.view');
        // UPDATED ROUTES: Results (simple summary, not marksheet template)
        Route::get('/results', 'resultIndex')->name('results.index');
        Route::get('/results/{result}', 'resultDetails')->name('results.details');
    });
    // Student Attendance (Using AttendanceController)
    Route::controller(AttendanceController::class)->group(function () {
        Route::get('/attendance', 'viewMyAttendance')->name('attendance.view');
        Route::get('/attendance/report', 'viewReport')->name('attendance.report');
    });
});
// STUDENTS LOGIN
Route::get('students/login', [StudentProfileController::class, 'StudentLogin'])->name('students.login');


// CRUD ROUTE IN ONE GROUP
Route::middleware(['auth'])->group(function () {

    // ==========================================
    // ACCOUNTANT CRUD ROUTE PIPELINE
    // ==========================================
    Route::controller(AccountantController::class)->prefix('accountant')->name('accountant.')->group(function () {

        Route::middleware(['can:access-accountant'])->group(function () {
            Route::get('/', 'AllAccountant')->name('index');
            Route::get('/add', 'AddAccountant')->name('create');
            Route::get('/get-accountant-id', 'getGeneratedId')->name('get-accountant-id');
            Route::get('/get-details/{id}', 'getDetails')->name('get-details');
            Route::post('/store', 'StoreAccountant')->name('store');
            Route::get('/edit/{id}', 'EditAccountant')->name('edit');
            Route::put('/update/{id}', 'UpdateAccountant')->name('update');
        });

        Route::middleware(['can:manage-accountant'])->group(function () {
            Route::post('/status/{id}', 'AccountantStatus')->name('status');
            Route::get('/delete/{id}', 'AccountantDestroy')->name('delete');
            Route::get('/trash', 'AccountantTrash')->name('trash');
            Route::get('/restore/{id}', 'AccountantRestore')->name('restore');
            Route::get('/force-delete/{id}', 'AccountantForceDelete')->name('force-delete');
        });
    });


    // ==========================================
    // TEACHER ASSIGNMENT MODULE PIPELINE
    // ==========================================
    Route::controller(TeacherAssignmentController::class)->prefix('teacher/assign')->name('teacher.assign.')->group(function () {

        // 1. General Access: Assignment dekhna aur naya assign karna
        Route::middleware(['can:access-teacher-assignment'])->group(function () {
            Route::get('/', 'AllTeacherClass')->name('index');
            Route::get('/get-sections-by-class', 'getSectionsByClass')->name('get-sections-by-class');
            Route::get('/get-subjects-by-class', 'getSubjectsByClass')->name('get-subjects-by-class');
            Route::get('/create', 'AddTeacherClass')->name('create');
            Route::post('/store', 'StoreTeacherClass')->name('store');
            Route::get('/edit/{id}', 'EditTeacherClass')->name('edit');
            Route::put('/update/{id}', 'UpdateTeacherClass')->name('update');
        });

        // 2. Management Access: Existing assignment badalna ya delete karna
        Route::middleware(['can:manage-teacher-assignment'])->group(function () {
            Route::delete('/delete/{id}', 'DestroyTeacherClass')->name('delete');
        });
    });

    // ==========================================
    // STAFF LEAVE SETTING MODULE
    // ==========================================
    Route::controller(StaffLeaveSettingController::class)->group(function () {

        // 1. General Access: Sirf dekhna aur naye rules banana
        Route::middleware(['can:access-leave-setting'])->group(function () {
            Route::get('/staffleavesett', 'index')->name('staffleavesett.index');
            Route::get('/staffleavesett/create', 'create')->name('staffleavesett.create');
            Route::post('/staffleavesett/store', 'store')->name('staffleavesett.store');
        });

        // 2. Management Access: Rules ko badalna ya delete karna (Critical)
        Route::middleware(['can:manage-leave-setting'])->group(function () {
            Route::get('/staffleavesett/{id}/edit', 'edit')->name('staffleavesett.edit');
            Route::put('/staffleavesett/{id}', 'update')->name('staffleavesett.update');
            Route::delete('/staffleavesett/{id}', 'destroy')->name('staffleavesett.destroy');
        });
    });

    // ==========================================
    // STAFF LEAVE APPLICATION
    // ==========================================
    Route::controller(StaffLeaveApplicationController::class)->group(function () {

        // 1. Staff Access: Application banana, dekhna aur update karna
        // Permission: 'access-leave-application'
        Route::middleware(['can:access-leave-application'])->group(function () {
            Route::get('/staffleaveapp', 'index')->name('staffleaveapp.index');
            Route::get('/staffleaveapp/create', 'create')->name('staffleaveapp.create');
            Route::post('/staffleaveapp/store', 'store')->name('staffleaveapp.store');

            // Edit & Update Routes
            Route::get('/staffleaveapp/{id}/edit', 'edit')->name('staffleaveapp.edit');
            Route::put('/staffleaveapp/{id}', 'update')->name('staffleaveapp.update');

            Route::get('/staffleaveapp/{id}', 'show')->name('staffleaveapp.show');

            // Helpers (AJAX calls)
            Route::get('/staffleaveapp/get-leave-types/{user_id}', 'getLeaveTypes')->name('staffleaveapp.getLeaveTypes');
            Route::get('/staffleaveapp/get-balance/{user_id}/{leave_type}', 'getLeaveBalance')->name('staffleaveapp.getBalance');
        });

        // 2. Admin Access: Sirf Admin approve/reject/delete kar sakta hai
        // Permission: 'manage-leave-application'
        Route::middleware(['can:manage-leave-application'])->group(function () {
            Route::post('/staffleaveapp/status/{id}', 'updateStatus')->name('staffleaveapp.updateStatus');
            Route::delete('/staffleaveapp/{id}', 'destroy')->name('staffleaveapp.destroy');
        });
    });


    // ==========================================
    // RECEPTIONIST CRUD ROUTE PIPELINE
    // ==========================================
    Route::controller(ReceptionController::class)->prefix('receptionist')->name('receptionist.')->group(function () {

        // 1. General Access: Data Entry aur View (Staff/Manager)
        Route::middleware(['can:access-receptionist'])->group(function () {
            Route::get('/', 'AllReceptionist')->name('index');
            Route::get('/add', 'AddReceptionist')->name('create');
            Route::get('/get-receptionist-id', 'getGeneratedId')->name('get-receptionist-id');
            Route::get('/get-details/{id}', 'getDetails')->name('get-details');
            Route::post('/store', 'StoreReceptionist')->name('store');
            Route::get('/edit/{id}', 'EditReceptionist')->name('edit');
            Route::post('/update/{id}', 'UpdateReceptionist')->name('update');
        });

        // 2. Critical Access: Status + Deletion + Trash (Admin/Management)
        Route::middleware(['can:manage-receptionist'])->group(function () {
            Route::post('/status/{id}', 'ReceptionistStatus')->name('status'); // Status critical hai
            Route::get('/delete/{id}', 'ReceptionistDestroy')->name('delete');
            Route::get('/trash', 'TrashReceptionist')->name('trash');
            Route::get('/restore/{id}', 'RestoreReceptionist')->name('restore');
            Route::get('/force-delete/{id}', 'ForceDeleteReceptionist')->name('force-delete');
        });
    });


    // ==========================================
    // TEACHER MODULE CRUD PIPELINE
    // ==========================================
    Route::prefix('teacher')->name('teacher.')->group(function () {

        // 1. Teacher CRUD (TeachersController)
        Route::middleware(['can:access-teacher'])->controller(TeachersController::class)->group(function () {
            Route::get('/', 'AllTeacher')->name('index');
            Route::get('/create', 'AddTeacher')->name('create');
            Route::get('/get-teacher-id', 'getGeneratedId')->name('get-teacher-id');
            Route::get('/get-details/{id}', 'getDetails')->name('get-details');
            Route::post('/store', 'StoreTeacher')->name('store');
            Route::get('/edit/{id}', 'EditTeacher')->name('edit');
            Route::post('/update/{id}', 'UpdateTeacher')->name('update');
        });



        // 2. Critical Access (Admin)
        Route::middleware(['can:manage-teacher'])->controller(TeachersController::class)->group(function () {
            Route::post('/status/{id}', 'TeacherStatus')->name('status');
            Route::get('/trash', 'TeacherTrash')->name('trash');
            Route::get('/restore/{id}', 'TeacherRestore')->name('restore');
            Route::get('/force-delete/{id}', 'TeacherForceDelete')->name('force-delete');
            Route::get('/delete/{id}', 'TeacherDestroy')->name('delete');
        });
    });



    // ==========================================
    // STUDENTS MODULE CRUD & AJAX PIPELINE
    // ==========================================
    Route::controller(StudentController::class)->group(function () {

        // Sab ke liye common middleware (Access)
        Route::middleware(['can:access-students'])->group(function () {
            Route::get('/students', 'AllStudent')->name('students.index');
            Route::get('/students/create', 'AddStudent')->name('students.create');
            Route::post('/students/store', 'StoreStudent')->name('students.store');

            // Sibling & AJAX Routes
            Route::get('/students/check-parent/{cnic}', 'CheckParentProfile')->name('students.checkParent');
            Route::get('/students/get-sections/{class_id}', 'getSectionsByClass')->name('students.get_sections');
            Route::get('/students/get-groups/{class_id}', 'getGroupsByClass')->name('students.get_groups');
            Route::get('/students/filter', 'FilterStudents')->name('students.filter');
            Route::get('/students/get-identifiers', 'GetNextAcademicIdentifiers')->name('students.get_identifiers');
            Route::get('/students/get-fee-structure', 'getFeeStructure')->name('students.get_fee_structure');
            Route::get('/students/get-details/{id}', 'getStudentDetails')->name('students.get_details');

            // Edit/Update
            Route::get('/students/edit/{id}', 'EditStudent')->name('students.edit');
            Route::post('/students/update/{id}', 'UpdateStudent')->name('students.update');
        });

        // Sirf Admin ya specific permission wale (Status & Delete)
        Route::middleware(['can:manage-students'])->group(function () {
            Route::post('/students/approve/{id}', 'ApproveAdmission')->name('students.approve');
            Route::post('/students/status/{id}', 'StudentStatus')->name('students.status');

            // Soft Delete Routes
            Route::get('/students/delete/{id}', 'StudentDestroy')->name('students.delete');
            Route::get('/students/trash', 'StudentTrash')->name('students.trash');
            Route::get('/students/restore/{id}', 'StudentRestore')->name('students.restore');
            Route::get('/students/force-delete/{id}', 'StudentForceDelete')->name('students.force-delete');
        });
    });



    // 1. ACADEMICS MODULE (Class, Section, Subject, Academic Years, etc.)
    Route::middleware(['can:access-academics'])->group(function () {
        // ==========================================
        // CLASS ALL ROUTES
        // ==========================================
        Route::controller(ClassController::class)->group(function () {
            Route::get('/classes', 'AllClass')->name('classes.index');
            Route::post('/classes/store', 'StoreClass')->name('classes.store');
            Route::get('/classes/edit/{id}', 'EditClass')->name('classes.edit');
            Route::post('/classes/update/{id}', 'UpdateClass')->name('classes.update');
        });

        // ==========================================
        // SECTION ALL ROUTES
        // ==========================================
        Route::controller(SectionController::class)->group(function () {
            Route::get('/sections', 'AllSection')->name('sections.index');
            Route::get('/sections/create', 'AddSection')->name('sections.create');
            Route::post('/sections/store', 'StoreSection')->name('sections.store');
            Route::get('/sections/edit/{id}', 'EditSection')->name('sections.edit');
            Route::post('/sections/update/{id}', 'UpdateSection')->name('sections.update');
        });

        // ==========================================
        // GROUP ALL ROUTES
        // ==========================================
        Route::controller(GroupController::class)->group(function () {
            Route::get('/groups', 'AllGroup')->name('groups.index');
            Route::get('/groups/create', 'AddGroup')->name('groups.create');
            Route::post('/groups/store', 'StoreGroup')->name('groups.store');
            Route::get('/groups/edit/{id}', 'EditGroup')->name('groups.edit');
            Route::post('/groups/update/{id}', 'UpdateGroup')->name('groups.update');
        });

        // ==========================================
        // SUBJECT ALL ROUTES
        // ==========================================
        Route::controller(SubjectController::class)->group(function () {
            Route::get('/subjects', 'AllSubject')->name('subjects.index');
            Route::get('/subjects/create', 'AddSubject')->name('subjects.create');
            Route::post('/subjects/store', 'StoreSubject')->name('subjects.store');
            Route::get('/subjects/edit/{id}', 'EditSubject')->name('subjects.edit');
            Route::post('/subjects/update/{id}', 'UpdateSubject')->name('subjects.update');
        });

        // ==========================================
        // SCHOOL CLASS SUBJECTS PIVOT ROUTES
        // ==========================================
        Route::controller(SchoolClassSubjectController::class)->group(function () {
            Route::get('/school-class-subjects', 'indexClassSubjects')->name('school-class-subjects.index');
            Route::get('/school-class-subjects/assign', 'assignClassSubjectsForm')->name('school-class-subjects.assign');
            Route::post('/school-class-subjects/save', 'saveClassSubjectsMapping')->name('school-class-subjects.save');
            Route::get('/school-class-subjects/edit/{id}', 'editClassSubjectsMapping')->name('school-class-subjects.edit');
            Route::post('/school-class-subjects/update/{id}', 'updateClassSubjectsMapping')->name('school-class-subjects.update');
        });

        // ==========================================
        // GROUP SUBJECTS PIVOT ROUTES
        // ==========================================
        // Route::controller(GroupSubjectController::class)->group(function () {
        //     Route::get('/group-subjects', 'indexGroupSubjects')->name('group-subjects.index');
        //     Route::get('/group-subjects/assign', 'assignGroupSubjectsForm')->name('group-subjects.assign');
        //     Route::post('/group-subjects/save', 'saveGroupSubjectsMapping')->name('group-subjects.save');
        //     Route::get('/group-subjects/edit/{id}', 'editGroupSubjectsMapping')->name('group-subjects.edit');
        //     Route::get('/group-subjects/status/{id}', 'GroupSubjectStatus')->name('group-subjects.status');
        //     Route::get('/group-subjects/delete/{id}', 'DeleteGroupSubjectMapping')->name('group-subjects.delete');
        // });

        // ==========================================
        // ACADEMIC YEARS ALL ROUTES
        // ==========================================
        Route::controller(AcademicYearController::class)->group(function () {
            Route::get('/academic-years', 'index')->name('academic_years.index');
            Route::post('/academic-years/store', 'store')->name('academic_years.store');
            Route::get('/academic-years/edit/{id}', 'edit')->name('academic_years.edit');
            Route::post('/academic-years/update/{id}', 'update')->name('academic_years.update');
            Route::get('/academic-years/current/{id}', 'markAsCurrent')->name('academic_years.current');
        });

        // ==========================================
        // ACADEMIC CALENDAR ALL ROUTES
        // ==========================================
        Route::controller(AcademicCalendarController::class)->prefix('academic-calendar')->name('academic_calendar.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update/{academicCalendar}', 'update')->name('update');
            Route::get('/calendar', 'calendarView')->name('calendar'); // 👈 new route
        });


        // ==========================================
        // EVENT TYPE ALL ROUTES
        // ==========================================
        Route::controller(EventTypeController::class)->prefix('event_type')->name('event_type.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{eventType}', 'edit')->name('edit');
            Route::post('/update/{eventType}', 'update')->name('update');
        });


        // ==========================================
        // SCHOOL CLASS-SECTION ALL ROUTES
        // ==========================================
        Route::controller(SchoolClassSectionController::class)->prefix('school-class-section')->name('school_class_section.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
        });
    });

    // 1. ACADEMICS MODULE (Class, Section, Subject, Academic Years, etc.)
    Route::middleware(['can:manage-academics'])->group(function () {
        // ==========================================
        // CLASS ALL ROUTES
        // ==========================================
        Route::controller(ClassController::class)->group(function () {

            Route::get('/classes/status/{id}', 'UpdateClassStatus')->name('classes.status');
            Route::get('/classes/delete/{id}', 'ClassDestroy')->name('classes.delete');
        });

        // ==========================================
        // SECTION ALL ROUTES
        // ==========================================
        Route::controller(SectionController::class)->group(function () {
            Route::post('/sections/status/{id}', 'UpdateSectionStatus')->name('sections.status');
            Route::get('/sections/delete/{id}', 'SectionDestroy')->name('sections.delete');
        });

        // ==========================================
        // GROUP ALL ROUTES
        // ==========================================
        Route::controller(GroupController::class)->group(function () {
            Route::get('/groups/status/{id}', 'GroupStatus')->name('groups.status');
            Route::get('/groups/delete/{id}', 'GroupDestroy')->name('groups.delete');
        });

        // ==========================================
        // SUBJECT ALL ROUTES
        // ==========================================
        Route::controller(SubjectController::class)->group(function () {
            Route::get('/subjects/status/{id}', 'SubjectStatus')->name('subjects.status');
            Route::get('/subjects/delete/{id}', 'SubjectDestroy')->name('subjects.delete');
        });

        // ==========================================
        // SCHOOL CLASS SUBJECTS PIVOT ROUTES
        // ==========================================
        Route::controller(SchoolClassSubjectController::class)->group(function () {
            Route::get('/school-class-subjects/status/{id}', 'ClassSubjectStatus')->name('school-class-subjects.status');
            Route::get('/school-class-subjects/delete/{id}', 'DeleteClassSubjectsMapping')->name('school-class-subjects.delete');
        });

        // ==========================================
        // SCHOOL CLASS-SECTION ALL ROUTES
        // ==========================================
        Route::controller(SchoolClassSectionController::class)->prefix('school-class-section')->name('school_class_section.')->group(function () {
            Route::delete('/destroy/{id}', 'destroy')->name('destroy');
            Route::get('/toggle-status/{id}', 'toggleStatus')->name('toggle_status');
        });

        // ==========================================
        // ACADEMIC YEARS ALL ROUTES
        // ==========================================
        Route::controller(AcademicYearController::class)->group(function () {
            Route::get('/academic-years/delete/{id}', 'destroy')->name('academic_years.delete');
            // Special Status & Current Year Actions
            Route::get('/academic-years/status/{id}', 'toggleStatus')->name('academic_years.status');
        });

        // ==========================================
        // ACADEMIC CALENDAR ALL ROUTES
        // ==========================================
        Route::controller(AcademicCalendarController::class)->prefix('academic-calendar')->name('academic_calendar.')->group(function () {
            Route::get('/delete/{academicCalendar}', 'destroy')->name('delete');
            Route::get('/status/{academicCalendar}', 'toggleStatus')->name('status');
        });


        // ==========================================
        // EVENT TYPE ALL ROUTES
        // ==========================================
        Route::controller(EventTypeController::class)->prefix('event_type')->name('event_type.')->group(function () {
            Route::post('/status/{eventType}', 'status')->name('status');
            Route::get('/delete/{eventType}', 'destroy')->name('delete');
        });
    });

    // ==========================================
    // ATTENDANCE CRUD ALL ROUTES
    // ==========================================
    Route::prefix('attendance')->name('attendance.')->group(function () {

        // 1. General Access: Daily Attendance mark karna aur reports dekhna
        Route::middleware(['can:access-attendance'])->group(function () {
            Route::controller(AttendanceController::class)->group(function () {
                Route::get('/', 'AllAttendance')->name('index');
                Route::get('/create', 'AddAttendance')->name('create');
                Route::get('/mark/{class_id?}/{section_id?}/{date?}', 'markAttendance')->name('mark');
                Route::get('/details/{class_id}', 'showDetails')->name('details');
                Route::get('/get-students', 'GetStudents')->name('get.students');
                Route::post('/store', 'StoreAttendance')->name('store');
                Route::get('/edit/{id}', 'EditAttendance')->name('edit');
                Route::post('/update/{id}', 'UpdateAttendance')->name('update');
                Route::get('/scan', 'ScanAttendance')->name('scan');
                Route::get('/scan-latest', 'GetLatestScan')->name('scan.latest');
                Route::post('/scan-store', 'StoreScanAttendance')->name('scan.store');
                Route::get('/my-class-attendance', 'myClassAttendanceReport')->name('report');
            });
        });

        // 2. Management Access: Attendance edit, update ya delete karna
        Route::middleware(['can:manage-attendance'])->group(function () {
            Route::controller(AttendanceController::class)->group(function () {

                Route::post('/mark-departure', 'markClassDeparture')->name('markDeparture');
                Route::get('/delete/{id}', 'AttendanceDestroy')->name('delete');
            });
        });
    });

    // ==========================================
    // Skill Category CRUD ALL ROUTES
    // ==========================================
    Route::prefix('skill-categories')->name('skill_categories.')->group(function () {
        Route::controller(SkillCategoryController::class)->group(function () {
            Route::middleware('can:access-skill-categories')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'show')->name('show');
            });
            Route::middleware('can:manage-skill-categories')->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
            });
        });
    });

    // ==========================================
    // SKILL ASSESSMENT AREA CRUD ALL ROUTES
    // ==========================================
    Route::prefix('skill-assessment-areas')->name('skill_assessment_areas.')->group(function () {
        Route::controller(SkillAssessmentAreaController::class)->group(function () {
            Route::middleware('can:access-skill-assessment-areas')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'show')->name('show');
            });
            Route::middleware('can:manage-skill-assessment-areas')->group(function () {
                Route::post('/', 'store')->name('store');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
            });
        });
    });
});

// ==========================================
// SKILL ASSESSMENT ENTRY CRUD ALL ROUTES
// ==========================================
Route::prefix('skill-assessment-entry')->name('skill_assessment_entry.')->group(function () {
    Route::controller(SkillAssessmentEntryController::class)->group(function () {
        Route::middleware('can:access-skill-assessment-entry')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-sections', 'getSections')->name('get_sections');
            Route::get('/get-grades', 'getGrades')->name('get_grades');
            Route::get('/get-students', 'getStudents')->name('get_students');
        });
        Route::middleware('can:manage-skill-assessment-entry')->group(function () {
            Route::post('/save', 'save')->name('save');
        });
    });
});

// ==========================================
// REPORT CARD TEMPLATE CRUD ALL ROUTES
// ==========================================
Route::prefix('report-card-template')->name('report_card_template.')->group(function () {
    Route::controller(ReportCardTemplateController::class)->group(function () {
        Route::middleware('can:access-report-card-template')->group(function () {
            Route::get('/', 'index')->name('index');
        });
        Route::middleware('can:manage-report-card-template')->group(function () {
            Route::post('/save', 'save')->name('save');
            Route::get('/{report_card_template}/edit', 'edit')->name('edit');
            Route::put('/{report_card_template}', 'update')->name('update');
            Route::delete('/{report_card_template}', 'destroy')->name('destroy');
        });
    });
});

// ==========================================
// PRINT REPORT CARD ALL ROUTES
// ==========================================
Route::prefix('print-report-card')->name('print_report_card.')->group(function () {
    Route::controller(PrintReportCardController::class)->group(function () {
        Route::middleware('can:access-print-report-card')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
            Route::post('/get-students', 'getStudents')->name('get_students');
        });
        Route::middleware('can:manage-print-report-card')->group(function () {
            Route::post('/generate', 'generate')->name('generate');
        });
    });
});

// ==========================================
// SCHOOL TIMING CURD ALL ROUTES
// ==========================================
Route::prefix('school-timing')->name('school_timing.')->group(function () {
    Route::middleware(['can:access-school-timing'])->group(function () {
        Route::controller(SchoolTimingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/edit/{id}', 'edit')->name('edit');
        });
    });

    Route::middleware(['can:manage-school-timing'])->group(function () {
        Route::controller(SchoolTimingController::class)->group(function () {
            Route::post('/store', 'store')->name('store');
            Route::post('/update/{id}', 'update')->name('update');
            Route::post('/set-active/{id}', 'setActive')->name('set_active');
            Route::post('/status/{id}', 'status')->name('status');
        });
    });
});


// ROLE ROUTES
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    Route::controller(RoleController::class)->group(function () {

        Route::get('/roles', 'AllRole')->name('roles.index');
        Route::get('/roles/create', 'AddRole')->name('roles.create');
        Route::post('/roles/store', 'StoreRole')->name('roles.store');

        Route::get('/roles/{id}/edit', 'EditRole')->name('roles.edit');
        Route::post('/roles/{id}/update', 'UpdateRole')->name('roles.update');

        // ✅ better delete
        Route::get('/roles/{id}', 'DeleteRole')->name('roles.delete');
    });
});

require __DIR__ . '/auth.php';
