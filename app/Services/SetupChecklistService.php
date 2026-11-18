<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\ClassSubject;
use App\Models\ClassTimetable;
use App\Models\ExamType;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\IdCardTemplate;
use App\Models\MarksGrade;
use App\Models\SchoolClass;
use App\Models\SchoolTiming;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * "Finish setting up your school" checklist (T339; T340b reuses it).
 * Every step is computed live with exists() queries: no table, no stored flag.
 * Step shape: key, label, group ('required' | 'recommended'), done (bool), url (string|null).
 * url is null when the user lacks the page's permission or the step is already done.
 */
class SetupChecklistService
{
    public function steps(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        $steps = [
            // ---- Required (counted) ----
            $this->step('school_profile', 'School profile complete', 'required', $this->profileComplete(), 'site_setting.edit', 'access-site-settings', $user),
            $this->step('academic_year', 'Active academic session', 'required', AcademicYear::where('is_current', 1)->exists(), 'academic_years.index', 'access-academics', $user),
            $this->step('school_timing', 'Active school timing', 'required', SchoolTiming::where('is_active', 1)->exists(), 'school_timing.index', 'access-school-timing', $user),
            $this->step('classes', 'Classes added', 'required', SchoolClass::query()->exists(), 'classes.index', 'access-academics', $user),
            $this->step('class_sections', 'Sections mapped to a class', 'required', DB::table('school_class_section')->exists(), 'school_class_section.index', 'access-academics', $user),
            $this->step('class_subjects', 'Subjects assigned to classes', 'required', ClassSubject::query()->exists(), 'school-class-subjects.index', 'access-academics', $user),
            // Same condition as the dashboard cards (AdminManage): active user (status 1) with the role (+ profile row for staff)
            /* old: $this->step('teacher', 'At least 1 teacher', 'required', Teacher::query()->exists(), 'teacher.index', 'access-teacher', $user), */
            $this->step('teacher', 'At least 1 teacher', 'required', User::role('teacher')->where('status', 1)->whereHas('teacher')->exists(), 'teacher.index', 'access-teacher', $user),
            /* old: $this->step('student', 'At least 1 student', 'required', Student::query()->exists(), 'students.index', 'access-students', $user), */
            $this->step('student', 'At least 1 student', 'required', User::role('student')->where('status', 1)->exists(), 'students.index', 'access-students', $user),
            $this->step('fee_types', 'Fee types added', 'required', FeeType::query()->exists(), 'fee_types.index', 'access-fee-types', $user),
            $this->step('fee_structure', 'Fee structure added', 'required', FeeStructure::query()->exists(), 'fee_structure.index', 'access-fee-structures', $user),
            $this->step('exam_types', 'Exam types added', 'required', ExamType::query()->exists(), 'exam_type.index', 'access-exam-types', $user),

            // ---- Recommended (optional, not counted) ----
            $this->step('class_timetable', 'Class timetable', 'recommended', ClassTimetable::query()->exists(), 'class_timetable.index', 'access-class-timetable', $user),
            // Grades are saved per exam (marks_grade.index needs an exam id), so the link goes to the Exams page.
            $this->step('grade_scheme', 'Grade scheme', 'recommended', MarksGrade::query()->exists(), 'exam.index', 'access-exams', $user),
            $this->step('student_id_card', 'Student ID card template', 'recommended', IdCardTemplate::query()->exists(), 'id_card_template.index', 'access-id-card-templates', $user),
            $this->staffAccountStep($user),
            $this->step('library_books', 'Library books', 'recommended', Book::query()->exists(), 'books.index', 'access-books', $user),
        ];

        return $steps;
    }

    /** Summary used by the dashboard card: required steps only drive done/total/percent/complete. */
    public function summary(?User $user = null): array
    {
        $steps = $this->steps($user);
        $required = array_values(array_filter($steps, fn($s) => $s['group'] === 'required'));
        $recommended = array_values(array_filter($steps, fn($s) => $s['group'] === 'recommended'));
        $done = count(array_filter($required, fn($s) => $s['done']));
        $total = count($required);

        return [
            'steps'       => $steps,
            'required'    => $required,
            'recommended' => $recommended,
            'done'        => $done,
            'total'       => $total,
            'percent'     => $total > 0 ? (int) round($done / $total * 100) : 0,
            'complete'    => $done === $total,
        ];
    }

    // school_name, phone, logo, principal_signature must all be non-empty after trim
    private function profileComplete(): bool
    {
        $s = SiteSetting::first();
        if (!$s) {
            return false;
        }
        foreach (['school_name', 'phone', 'logo', 'principal_signature'] as $col) {
            if (trim((string) $s->{$col}) === '') {
                return false;
            }
        }
        return true;
    }

    // At least 1 account with role receptionist / accountant / librarian; link = first staff page the user may open
    private function staffAccountStep(?User $user): array
    {
        /* old: $done = User::role(['receptionist', 'accountant', 'librarian'])->exists(); */
        // Same as the dashboard staff cards: active (status 1) user with the role AND the profile row (relation name = role name)
        $done = false;
        foreach (['receptionist', 'accountant', 'librarian'] as $role) {
            if (User::role($role)->where('status', 1)->whereHas($role)->exists()) {
                $done = true;
                break;
            }
        }
        $pages = [
            ['receptionist.index', 'access-receptionist'],
            ['accountant.index', 'access-accountant'],
            ['librarians.index', 'access-librarian'],
        ];
        $url = null;
        if (!$done && $user) {
            foreach ($pages as [$route, $perm]) {
                if ($user->can($perm)) {
                    $url = route($route);
                    break;
                }
            }
        }
        return ['key' => 'staff_account', 'label' => 'Receptionist / accountant / librarian account', 'group' => 'recommended', 'done' => $done, 'url' => $url];
    }

    private function step(string $key, string $label, string $group, bool $done, string $route, string $permission, ?User $user): array
    {
        return [
            'key'   => $key,
            'label' => $label,
            'group' => $group,
            'done'  => $done,
            'url'   => (!$done && $user && $user->can($permission)) ? route($route) : null,
        ];
    }
}
