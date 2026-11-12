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

        // School logo / icon (Site Settings) for the main login, role login pages, portal layouts + sidebars and ID cards; ek request mein ek hi query
        // Also passes the Modern School System (MSS) product logo / icon (static files, null when missing)
        $schoolBrand = null;
        $mssBrand = null;
        View::composer([
            'auth.login',
            'admin.admin_login', 'superadmin.superadmin_login', 'teacher.teacher_login', 'student.student_login',
            'parent.parents_login', 'reception.reception_login', 'accountant.accountant_login', 'admin.librarian.librarian_login',
            'admin.layout.app', 'superadmin.layout.app', 'teacher.layout.app', 'student.layout.app',
            'parent.layout.app', 'reception.layout.app', 'accountant.layout.app', 'admin.librarian.layout.app',
            'admin.body.sidebar', 'superadmin.body.sidebar', 'teacher.body.sidebar', 'student.body.sidebar',
            'parent.body.sidebar', 'reception.body.sidebar', 'accountant.body.sidebar', 'admin.librarian.body.sidebar',
            'admin.id_card_template.card', 'admin.staff_id_card_template.card',
            'frontend.home.body.footer',
        ], function ($view) use (&$schoolBrand, &$mssBrand) {
            if (!$schoolBrand) {
                $schoolBrand = \App\Models\SiteSetting::current();
            }
            // MSS logo / icon: first file found in public/backend/assets/images wins, else null (the view keeps its own fallback)
            if ($mssBrand === null) {
                $pick = function (array $files) {
                    foreach ($files as $file) {
                        if (is_file(public_path('backend/assets/images/' . $file))) {
                            return asset('backend/assets/images/' . $file);
                        }
                    }
                    return null;
                };
                $mssBrand = ['logo' => $pick(['mss-logo.png', 'mss-logo.jpeg']), 'icon' => $pick(['mss-icon.png'])];
            }
            $view->with('schoolBrand', $schoolBrand)->with('mssLogo', $mssBrand['logo'])->with('mssIcon', $mssBrand['icon']);
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

        // Parent sidebar: active child id, only if it really belongs to the logged-in parent (else null = child selection page)
        View::composer('parent.body.sidebar', function ($view) {
            $parentChildId = null;
            $profile = auth()->check() ? auth()->user()->parentProfile : null;

            if ($profile && session('active_student_id')) {
                $parentChildId = $profile->students()->where('students.id', session('active_student_id'))->value('students.id');
            }

            $view->with('parentChildId', $parentChildId);
        });
    }
}
