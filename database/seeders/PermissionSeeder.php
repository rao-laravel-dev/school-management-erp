<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'access-academics',
            'manage-academics',
            'access-home-works',
            'manage-home-works',
            'access-class-timetable',
            'manage-class-timetable',
            'access-rooms',
            'manage-rooms',
            'access-teacher-timetable',
            'access-my-timetable',          // web.php Teacher view only permission
            'access-lessons',
            'manage-lessons',
            'access-topics',
            'manage-topics',
            'access-lesson-plans',
            'manage-lesson-plans',
            'access-my-lesson-plan',        // web.php Teacher view only permission
            'access-copy-old-lessons',
            'manage-copy-old-lessons',
            'access-syllabus-status',
            'manage-syllabus-status',
            'access-my-syllabus-status',    // web.php Teacher view only permission
            'access-exam-types',
            'manage-exam-types',
            'access-exams',
            'manage-exams',
            'access-exam-schedules',
            'manage-exam-schedules',
            'access-my-exam-schedule',          // web.php Teacher k liye permission only
            'access-marks-grades',
            'manage-marks-grades',
            'access-marksheets',
            'manage-marksheets',
            'access-marksheet-templates',
            'manage-marksheet-templates',
            'access-skill-categories',          // web.php
            'manage-skill-categories',          // web.php
            'access-skill-assessment-areas',    // web.php
            'manage-skill-assessment-areas',    // web.php
            'access-skill-assessment-entry',    // web.php
            'manage-skill-assessment-entry',    // web.php
            'access-report-card-template',      // web.php
            'manage-report-card-template',      // web.php
            'access-print-report-card',         // web.php
            'manage-print-report-card',         // web.php
            'access-results',
            'manage-results',
            'access-site-settings',             
            'manage-site-settings',
            'access-student-import',
            'manage-student-import',
            'access-student-promotion',         // front_office.php student_promotion.*
            'manage-student-promotion',         // front_office.php student_promotion.* (store, undo)
            'access-school-timing',             // web.php
            'manage-school-timing',             // web.php
            'access-id-card-templates',
            'manage-id-card-templates',
            'access-staff-id-card-templates',
            'manage-staff-id-card-templates',
            'access-teacher',
            'manage-teacher',
            'access-teacher-assignment',
            'manage-teacher-assignment',
            'access-students',
            'manage-students',
            'access-student-categories',
            'manage-student-categories',
            'access-student-houses',
            'manage-student-houses',
            'access-receptionist',
            'manage-receptionist',
            'access-accountant',
            'manage-accountant',
            'access-librarian',             // web.php librarians.* (admin CRUD)
            'manage-librarian',             // web.php librarians.* (admin CRUD)
            'access-attendance',
            'manage-attendance',
            'mark-attendance',              // web.php attendance.create/mark/get.students/store/edit/update (accountant: read only)
            'access-scan-attendance',       // web.php attendance.scan* (admin, receptionist, class teacher)
            'access-leave-setting',
            'manage-leave-setting',
            'access-leave-application',
            'manage-leave-application',
            'view school-notices',
            'access-front-office',
            'manage-front-office',
            'access-fee-structures',
            'manage-fee-structures',
            'access-fee-collections',
            'manage-fee-collections',
            'access-fee-types',
            'manage-fee-types',
            'access-bank-accounts',
            'manage-bank-accounts',
            'access-expense-categories',
            'manage-expense-categories',
            'access-expenses',
            'manage-expenses',
            'access-salary-slips',
            'manage-salary-slips',
            'access-my-salary-slips',       // web.php All staff (my.salary_slips.*) + teacher.salary_slips.index
            'access-my-attendance',         // web.php All staff (my.attendance.index)
            'access-finance-report',
            'access-staff-attendance',
            'manage-staff-attendance',
            'mark-staff-attendance',        // front_office.php staff_attendance.create/store/edit/update (admin, receptionist; accountant read only)
            'access-discount-policies',
            'manage-discount-policies',
            'access-fine-policies',
            'manage-fine-policies',
            'access-billing-settings',
            'manage-billing-settings',
            'access-fee-assignments',
            'manage-fee-assignments',
            'access-book-categories',
            'manage-book-categories',
            'access-books',
            'manage-books',
            'access-library-settings',
            'manage-library-settings',
            'access-library-members',
            'manage-library-members',
            'access-book-issues',
            'manage-book-issues',
            'view roles',
            'add roles',
            'edit roles',
            'delete roles',
            // Student ki naye permissions
            'access-profile',
            // Student portal-only (P1 portal permission security)
            'access-student-academics',     // student portal: My Subjects, My Teachers, Results
            'access-student-attendance',    // student portal: own attendance view
            'access-student-timetable',     // student portal: own class timetable (read-only)
            'access-assignments',
            'access-student-homework',      // student portal: own class homework (read-only)
            'access-parent-timetable',      // parent portal: selected child's class timetable (read-only)
            'access-parent-homework',       // parent portal: selected child's homework (read-only)
            'access-parent-results',        // parent portal: selected child's results (read-only)
            'access-parent-students',       // parent portal: selected child's dashboard / details (read-only)
        ];

        foreach ($permissions as $permission) {
            // firstOrCreate se database clean nahi hoga, bas jo missing hai wo add ho jayega
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
