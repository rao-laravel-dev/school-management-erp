<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SuperAdmin ko "God Mode" dene ke liye (Sab kuch access milega)
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        // 2. Dynamic Layout Selector (Helper)
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();

                // 1. Role mapping
                $layoutMap = [
                    'superadmin'   => 'admin.layout.app',
                    'admin'        => 'admin.layout.app',
                    'accountant'   => 'accountant.layout.app',
                    'receptionist' => 'reception.layout.app',
                    'librarian'    => 'admin.librarian.layout.app',
                    'teacher'      => 'teacher.layout.app',
                    'parent'       => 'parent.layout.app',
                    'student'      => 'student.layout.app',
                ];

                // 2. User ka role nikalen
                $roleName = $user->activeRoleName() ?? 'user'; // pehla enabled role (disabled role skip)

                // 3. Layout choose karein
                $layout = $layoutMap[$roleName] ?? 'user.layout.app';

                // 4. Fallback: Agar file exist na kare to default par le jaye
                if (!view()->exists($layout)) {
                    $layout = 'admin.layout.app';
                }

                // --- DEBUGGING KE LIYE (Test karne ke baad ise hatayein) ---
                // dd($layout, $roleName); 

                // 5. Layout pass karein
                $view->with('current_layout', $layout);
            }
        });

        // Student header "Class Teacher" badge: every student page gets it (dashboard/myteacher already pass their own)
        View::composer('student.body.header', function ($view) {
            if (array_key_exists('assignedTeacher', $view->getData()) || !auth()->check()) {
                return;
            }

            $assignedTeacher = null;
            $student = auth()->user()->studentProfile;

            if ($student) {
                // Same rule as StudentProfileController: latest active enrollment + its academic year
                $enrollment = DB::table('enrollments')
                    ->where('student_id', $student->id)
                    ->where('enroll_status', 1)
                    ->orderByDesc('id')
                    ->first();

                if ($enrollment && !empty($enrollment->class_id) && !empty($enrollment->section_id)) {
                    $assignedTeacher = DB::table('teacher_assignments')
                        ->join('teachers', 'teacher_assignments.teacher_id', '=', 'teachers.id')
                        ->join('school_class', 'teacher_assignments.class_id', '=', 'school_class.id')
                        ->join('sections', 'teacher_assignments.section_id', '=', 'sections.id')
                        ->where('teacher_assignments.class_id', $enrollment->class_id)
                        ->where('teacher_assignments.section_id', $enrollment->section_id)
                        ->where('teacher_assignments.academic_year_id', $enrollment->academic_year_id)
                        ->select(
                            'teachers.first_name',
                            'teachers.last_name',
                            'teachers.photo',
                            'school_class.name as class_name',
                            'sections.name as section_name'
                        )
                        ->first();
                }
            }

            $view->with('assignedTeacher', $assignedTeacher);
        });
    }
}
