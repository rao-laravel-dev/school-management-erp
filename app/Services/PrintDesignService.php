<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SiteSetting;
use App\Models\Student;
use Carbon\Carbon;

// Marksheet / Report Card print designs: shared options, school branding, sample data for the live preview
class PrintDesignService
{
    public const DESIGNS = [
        'classic' => 'Classic (current layout)',
        'modern'  => 'Modern (colour band)',
        'formal'  => 'Formal (bordered, official)',
    ];

    public const ORIENTATIONS = [
        'portrait'  => 'Portrait',
        'landscape' => 'Landscape',
    ];

    public static function validHex($hex): bool
    {
        return is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1;
    }

    public static function design($template): string
    {
        return array_key_exists($template->design, self::DESIGNS) ? $template->design : 'classic';
    }

    public static function orientation($template): string
    {
        return $template->orientation === 'landscape' ? 'landscape' : 'portrait';
    }

    // School branding + colours for a print page: template colour wins, else the school theme colour
    public static function branding(SiteSetting $site, $template): array
    {
        $theme   = $site->theme;
        $primary = self::validHex($template->primary_color) ? strtolower($template->primary_color) : $theme['primary'];
        $accent  = self::validHex($template->accent_color) ? strtolower($template->accent_color) : $theme['accent'];

        return [
            'school_name'   => $site->school_name,
            'address'       => $site->address,
            'phone'         => $site->phone,
            'email'         => $site->email,
            'logo_url'      => $template->show_school_logo ? $site->logo_url : null,
            'signature_url' => $template->show_head_signature ? $site->principal_signature_url : null,
            'head_name'     => $template->show_head_signature ? $site->head_name : null,
            'head_title'    => $site->head_title ?: 'Principal',
            'stamp_url'     => $template->show_stamp ? $site->school_stamp_url : null,
            'primary'       => $primary,
            'accent'        => $accent,
            'on_primary'    => self::onSolid($primary),
        ];
    }

    // Text colour on a solid colour: white unless white has less than 3:1 contrast (same rule as the school theme / certificates)
    public static function onSolid(string $hex): string
    {
        $lin = function (string $pair): float {
            $c = hexdec($pair) / 255;
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        $l = 0.2126 * $lin(substr($hex, 1, 2)) + 0.7152 * $lin(substr($hex, 3, 2)) + 0.0722 * $lin(substr($hex, 5, 2));

        return (1.05 / ($l + 0.05)) >= 3 ? '#ffffff' : SiteSetting::contrastText($hex);
    }

    // Division from percentage (60+ First, 45+ Second, 33+ Third); failed / no result = '-'. The database has no division column, so it is derived here
    public static function division($percentage, $status): string
    {
        if ($status !== 'Pass' || $percentage === null) {
            return '-';
        }
        $p = (float) $percentage;

        return $p >= 60 ? 'First' : ($p >= 45 ? 'Second' : ($p >= 33 ? 'Third' : '-'));
    }

    // Many subjects: CSS class that tightens rows so a sheet of the new designs still fits one page ('' = normal)
    // $extras = summary boxes / grade scale are on (they take ~20mm), so denser rows start with fewer subjects
    public static function fitLevel(int $subjectCount, string $orientation, bool $extras = false): string
    {
        // old: $limits = $orientation === 'landscape' ? [5, 8] : [10, 15];
        $limits = $orientation === 'landscape' ? ($extras ? [2, 4] : [5, 8]) : ($extras ? [7, 12] : [10, 15]);

        // 'ex' marks a sheet with the extra sections, for a few landscape-only compactions in base.blade.php
        // 'lv3' = landscape + extras + more than 12 subjects: smallest text, no photo
        $lv3 = ($extras && $orientation === 'landscape' && $subjectCount > 12) ? ' lv3' : '';

        return trim(($subjectCount > $limits[1] ? 'lv2' : ($subjectCount > $limits[0] ? 'lv1' : '')) . ($extras ? ' ex' : '') . $lv3);
    }

    // Grade name for a percentage from the exam's marks_grades (same range rule as ResultController); '-' when no grade matches
    public static function gradeFor($percentage, $grades): string
    {
        foreach ($grades as $g) {
            if ($percentage >= $g->min_percentage && $percentage <= $g->max_percentage) {
                return $g->grade_name;
            }
        }

        return '-';
    }

    // Signature / head / school stamp missing in Site Settings? (for the hint on the template page)
    public static function missingBranding(SiteSetting $site): bool
    {
        return !$site->principal_signature || !$site->head_name;
    }

    // [placeholder] tokens in the marksheet body text; parent names come from the parent profile
    public static function marksheetBody(?string $text, $student, $enrollment, $exam): ?string
    {
        if (!$text) {
            return null;
        }

        $parent = optional($student->parent);
        $tokens = [
            '[name]'          => $student->full_name ?? '-',
            '[father_name]'   => $parent->father_name ?: '-',
            '[mother_name]'   => $parent->mother_name ?: '-',
            '[admission_no]'  => $student->admission_no ?? '-',
            '[roll_number]'   => $enrollment->roll_no ?? '-',
            '[roll_no]'       => $enrollment->roll_no ?? '-',
            '[dob]'           => $student->date_of_birth ? Carbon::parse($student->date_of_birth)->format('d-m-Y') : '-',
            '[class]'         => $enrollment->schoolClass->name ?? '-',
            '[section]'       => $enrollment->section->name ?? '-',
            '[exam_name]'     => $exam->name ?? '-',
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $text);
    }

    public static function numberToWords($num): string
    {
        $num = (int) $num;
        if ($num == 0) {
            return 'Zero';
        }
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $words = '';
        if (intdiv($num, 1000) >= 1) {
            $words .= self::numberToWords(intdiv($num, 1000)) . ' Thousand ';
            $num %= 1000;
        }
        if (intdiv($num, 100) >= 1) {
            $words .= $ones[intdiv($num, 100)] . ' Hundred ';
            $num %= 100;
        }
        if ($num > 0) {
            if (!empty($words)) {
                $words .= 'And ';
            }
            $words .= $num < 20 ? $ones[$num] : $tens[intdiv($num, 10)] . (($num % 10) ? '-' . $ones[$num % 10] : '');
        }

        return trim($words);
    }

    // Live preview: one sample marksheet from unsaved fake records, nothing is read from or written to the DB (except the current session name)
    public static function sampleMarksheet($template): array
    {
        $student = (new Student)->forceFill([
            'id' => 1,
            'first_name' => 'Ali',
            'last_name' => 'Raza',
            'gender' => 'male',
            'admission_no' => 'GR-0001',
            'date_of_birth' => '2012-03-12',
        ]);
        $student->setRelation('parent', (new ParentProfile)->forceFill([
            'father_name' => 'Muhammad Raza',
            'mother_name' => 'Ayesha Raza',
        ]));

        $year = AcademicYear::where('is_current', 1)->first() ?: (new AcademicYear)->forceFill(['name' => '2026-27']);

        $enrollment = (new Enrollment)->forceFill(['roll_no' => '12', 'class_id' => 0]);
        $enrollment->setRelation('student', $student);
        $enrollment->setRelation('schoolClass', (new SchoolClass)->forceFill(['name' => 'Class 5']));
        $enrollment->setRelation('section', (new Section)->forceFill(['name' => 'A']));
        $enrollment->setRelation('academicYear', $year);

        $exam = (new Exam)->forceFill(['name' => 'Mid Term Exam']);
        $exam->setRelation('academicYear', $year);

        $subjects = collect([['English', 78], ['Urdu', 85], ['Mathematics', 92], ['Science', 74], ['Islamiyat', 88]])
            ->map(fn ($s) => (object) [
                'subject'        => (object) ['name' => $s[0]],
                'marks_obtained' => $s[1],
                'max_marks'      => 100,
                'passing_marks'  => 33,
            ]);

        $result = (object) [
            'total_marks'    => 500,
            'obtained_marks' => 417,
            'percentage'     => 83.4,
            'position'       => 3,
            'status'         => 'Pass',
            'grade'          => (object) ['grade_name' => 'A', 'remark' => 'Very Good'],
        ];

        return [
            'exam'   => $exam,
            'sheets' => collect([[
                'enrollment' => $enrollment,
                'student'    => $student,
                'result'     => $result,
                'subjects'   => $subjects,
                'body_text'  => self::marksheetBody($template->body_text, $student, $enrollment, $exam),
                'grades'     => collect([['A+', 90, 100], ['A', 80, 89.99], ['B', 70, 79.99], ['C', 60, 69.99], ['D', 50, 59.99], ['E', 33, 49.99], ['F', 0, 32.99]])
                    ->map(fn ($g) => (object) ['grade_name' => $g[0], 'min_percentage' => $g[1], 'max_percentage' => $g[2]]),
                'total_students' => 30,
            ]]),
        ];
    }
}
