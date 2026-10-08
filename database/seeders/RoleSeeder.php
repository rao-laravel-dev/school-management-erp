<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cache saaf karein taake nayi permissions foran reflect hon
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Roles ensure karein (firstOrCreate: jo hai wo rehne do, jo nahi hai wo bana do)
        $roles = ['superadmin', 'admin', 'receptionist', 'accountant', 'librarian', 'teacher', 'parent', 'student', 'user'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // 3. SuperAdmin (SAB KUCH - syncPermissions purani hata kar nayi update karega)
        Role::findByName('superadmin', 'web')->syncPermissions(Permission::all());

        // 4. Admin
        Role::findByName('admin', 'web')->syncPermissions([
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
            'access-lessons',
            'manage-lessons',
            'access-topics',
            'manage-topics',
            'access-lesson-plans',
            'manage-lesson-plans',
            'access-copy-old-lessons',
            'manage-copy-old-lessons',
            'access-syllabus-status',
            'manage-syllabus-status',
            'access-exam-types',
            'manage-exam-types',
            'access-exams',
            'manage-exams',
            'access-exam-schedules',
            'manage-exam-schedules',
            'access-marks-grades',
            'manage-marks-grades',
            'access-marksheets',
            'manage-marksheets',
            'access-marksheet-templates',
            'manage-marksheet-templates',
            'access-skill-categories',      // web.php
            'manage-skill-categories',      // web.php
            'access-skill-assessment-areas',// web.php
            'manage-skill-assessment-areas',// web.php
            'access-skill-assessment-entry',// web.php
            'manage-skill-assessment-entry',// web.php
            'access-report-card-template',  // web.php
            'manage-report-card-template',  // web.php
            'access-print-report-card',     // web.php
            'manage-print-report-card',     // web.php
            'access-results',
            'manage-results',
            'access-site-settings',
            'manage-site-settings',
            'access-student-import',
            'manage-student-import',
            'access-school-timing',         // web.php          
            'manage-school-timing',         // web.php
            'access-students',
            'manage-students',
            'access-student-categories',
            'manage-student-categories',
            'access-student-houses',
            'manage-student-houses',
            'access-teacher',
            'manage-teacher',
            'access-teacher-assignment',
            'manage-teacher-assignment',
            'access-receptionist',
            'manage-receptionist',
            'access-accountant',
            'manage-accountant',
            'access-leave-setting',
            'manage-leave-setting',
            'access-leave-application',
            'manage-leave-application',
            'access-attendance',
            'manage-attendance',
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
            'access-finance-report',
            'access-staff-attendance',
            'manage-staff-attendance',
            'access-discount-policies',
            'manage-discount-policies',
            'access-fine-policies',
            'manage-fine-policies',
            'access-book-categories',
            'manage-book-categories',
            'access-books',
            'manage-books',
            'access-library-settings',
            'manage-library-settings',
            'access-id-card-templates',
            'manage-id-card-templates',
            'access-staff-id-card-templates',
            'manage-staff-id-card-templates',
            'access-library-members',
            'manage-library-members',
            'access-book-issues',
            'manage-book-issues',
            'view roles',
            'add roles',
            'edit roles',
            'delete roles'
        ]);

        // 5. Receptionist
        Role::findByName('receptionist', 'web')->syncPermissions([
            'view dashboard',
            'access-academics',
            'access-class-timetable',
            'access-rooms',
            'manage-rooms',
            'access-teacher-timetable',
            'access-lessons',
            'access-topics',
            'access-lesson-plans',
            'access-copy-old-lessons',
            'access-syllabus-status',
            'access-exam-types',
            'access-exams',
            'access-exam-schedules',
            'access-marks-grades',
            'access-marksheets',
            'access-marksheet-templates',
            'manage-marksheet-templates',
            'access-skill-categories',       // web.php
            'access-skill-assessment-areas', // web.php
            'access-skill-assessment-entry', // web.php
            'access-report-card-template',   // web.php
            'access-print-report-card',      // web.php
            'access-results',
            'access-site-settings',
            'access-school-timing',             // web.php          
            'access-id-card-templates',
            'manage-id-card-templates',
            'access-staff-id-card-templates',
            'access-teacher',
            'access-teacher',
            'access-teacher-assignment',
            'access-accountant',
            'access-students',
            'access-student-categories',
            'access-student-houses',
            'access-attendance',
            'access-leave-application',
            'access-front-office',
            // Fee + finance permissions receptionist se hata di (08-10-2026)
            'access-staff-attendance',
            'access-my-salary-slips',       // web.php my.salary_slips.*
            'access-my-attendance',         // web.php my.attendance.index

        ]);

        // 6. Accountant
        Role::findByName('accountant', 'web')->syncPermissions([
            'view dashboard',
            'access-receptionist',
            'access-teacher',
            'access-students',
            'access-student-categories',
            'access-student-houses',
            'access-attendance',
            'access-class-timetable',
            'access-lessons',
            'access-topics',
            'access-lesson-plans',
            'access-copy-old-lessons',
            'access-syllabus-status',
            'access-exam-types',
            'access-exams',
            'access-exam-schedules',
            'access-marks-grades',
            'access-marksheets',
            'access-marksheet-templates',
            'access-skill-categories',          // web.php
            'access-skill-assessment-areas',    // web.php
            'access-skill-assessment-entry',    // web.php
            'access-report-card-template',      // web.php
            'access-print-report-card',         // web.php
            'access-results',
            'access-site-settings',
            'access-school-timing',             // web.php          
            'access-id-card-templates',
            'access-staff-id-card-templates',
            'access-leave-application',
            'access-fee-structures',
            'access-fee-collections',
            'access-fee-types',
            'access-bank-accounts',
            'access-expense-categories',
            'access-expenses',
            'access-salary-slips',
            'access-staff-attendance',
            'access-discount-policies',
            'access-fine-policies',
            'access-my-salary-slips',       // web.php my.salary_slips.*
            'access-my-attendance',         // web.php my.attendance.index

        ]);

        // 7. Teacher
        Role::findByName('teacher', 'web')->syncPermissions([
            'view dashboard',
            'access-students',
            'access-student-categories',
            'access-student-houses',
            'access-attendance',
            'access-my-salary-slips',       // web.php Only for Teacher this permission.
            'access-my-attendance',         // web.php my.attendance.index
            'access-home-works',
            'manage-home-works',
            'access-class-timetable',
            'access-my-timetable',          // web.php Teacher view only permission
            'access-lessons',
            'manage-lessons',
            'access-topics',
            'manage-topics',
            'access-lesson-plans',
            'access-my-lesson-plan',        // web.php Teacher view only permission
            'access-copy-old-lessons',
            'access-my-syllabus-status',    // web.php Teacher view only permission
            'access-exam-types',
            'access-exams',
            'access-exam-schedules',
            'access-my-exam-schedule',          // web.php Teacher k liye permission only
            'access-marks-grades',
            'access-marksheets',
            'access-marksheet-templates',
            'access-results',
            'access-leave-application',
            'view school-notices'
        ]);

        // 8. Parent
        Role::findByName('parent', 'web')->syncPermissions([
            'view dashboard',
            'access-attendance',
            'access-marksheet-templates',
        ]);

        // 9. Student
        Role::findByName('student', 'web')->syncPermissions([
            'view dashboard',
            'access-academics',
            'access-attendance',
            'access-marksheet-templates',
            'access-teacher',
            'access-profile',     // Nayi permission
            'access-assignments'  // Nayi permission
        ]);

        // 10. User (Default)
        Role::findByName('user', 'web')->syncPermissions(['view dashboard']);
    }
}
