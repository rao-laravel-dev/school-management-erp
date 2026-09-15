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
            'access-attendance',
            'manage-attendance',
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
            'access-my-salary-slips',       // web.php Only for Teacher this permission.
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
            'access-assignments',
        ];

        foreach ($permissions as $permission) {
            // firstOrCreate se database clean nahi hoga, bas jo missing hai wo add ho jayega
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
