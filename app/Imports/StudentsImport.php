<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\SchoolClassSection;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentHouse;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    /** @var string[] Human-readable "Row X: reason" error list. Empty = success. */
    protected array $errors = [];

    /** @var int Number of students actually inserted (0 if any errors occurred). */
    protected int $importedCount = 0;

    protected const DEFAULT_PASSWORD = 'Welcome@123';

    public function collection(Collection $rows)
    {
        // Drop fully blank rows (e.g. trailing empty rows in the sheet)
        $rows = $rows->filter(fn($row) => collect($row)->filter(fn($v) => trim((string) $v) !== '')->isNotEmpty())->values();

        if ($rows->isEmpty()) {
            $this->errors[] = 'The uploaded file has no data rows.';
            return;
        }

        $activeYear = AcademicYear::where('is_current', 1)->first();
        if (!$activeYear) {
            $this->errors[] = 'No active Academic Year is configured — cannot import.';
            return;
        }

        $admissionNosSeen = []; // admission_no => first row number it appeared on
        $validatedRows = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +1 for 0-index, +1 for the header row
            $rowErrors = [];

            $admissionNo      = trim((string) ($row['admission_no'] ?? ''));
            $className        = trim((string) ($row['class_name'] ?? ''));
            $sectionName      = trim((string) ($row['section_name'] ?? ''));
            $groupName        = trim((string) ($row['group_name'] ?? ''));
            $firstName        = trim((string) ($row['first_name'] ?? ''));
            $lastName         = trim((string) ($row['last_name'] ?? ''));
            $gender           = strtolower(trim((string) ($row['gender'] ?? '')));
            $dob              = trim((string) ($row['date_of_birth'] ?? ''));
            $admissionDate    = trim((string) ($row['admission_date'] ?? ''));
            $categoryName     = trim((string) ($row['category_name'] ?? ''));
            $religion         = trim((string) ($row['religion'] ?? ''));
            $caste            = trim((string) ($row['caste'] ?? ''));
            $bloodGroup       = trim((string) ($row['blood_group'] ?? ''));
            $houseName        = trim((string) ($row['house_name'] ?? ''));
            $fatherName       = trim((string) ($row['father_name'] ?? ''));
            $fatherPhone      = trim((string) ($row['father_phone'] ?? ''));
            $fatherCnic       = trim((string) ($row['father_cnic'] ?? ''));
            $motherName       = trim((string) ($row['mother_name'] ?? ''));
            $motherPhone      = trim((string) ($row['mother_phone'] ?? ''));
            $motherCnic       = trim((string) ($row['mother_cnic'] ?? ''));
            $guardianRelation = strtolower(trim((string) ($row['guardian_relation'] ?? '')));
            $guardianName     = trim((string) ($row['guardian_name'] ?? ''));
            $guardianPhone    = trim((string) ($row['guardian_phone'] ?? ''));
            $guardianEmail    = trim((string) ($row['guardian_email'] ?? ''));
            $guardianAddress  = trim((string) ($row['guardian_address'] ?? ''));

            // ---- Required fields ----
            if ($admissionNo === '') $rowErrors[] = 'admission_no is required';
            if ($className === '') $rowErrors[] = 'class_name is required';
            if ($sectionName === '') $rowErrors[] = 'section_name is required';
            if ($firstName === '') $rowErrors[] = 'first_name is required';
            if ($lastName === '') $rowErrors[] = 'last_name is required';
            if (!in_array($gender, ['male', 'female'], true)) $rowErrors[] = 'gender must be male or female';
            if (!$this->isValidDate($dob)) $rowErrors[] = 'date_of_birth must be a valid date in YYYY-MM-DD format';
            if (!$this->isValidDate($admissionDate)) $rowErrors[] = 'admission_date must be a valid date in YYYY-MM-DD format';
            if ($fatherName === '') $rowErrors[] = 'father_name is required';
            if (!preg_match('/^(03|923|\+923)[0-9]{9}$/', $fatherPhone)) $rowErrors[] = 'father_phone format is invalid (expected 03XXXXXXXXX)';
            if (!preg_match('/^\d{5}-\d{7}-\d{1}$/', $fatherCnic)) $rowErrors[] = 'father_cnic format is invalid (expected 42101-1234567-8)';
            if (!in_array($guardianRelation, ['father', 'mother', 'other'], true)) $rowErrors[] = 'guardian_relation must be father, mother or other';
            if ($guardianRelation === 'other') {
                if ($guardianName === '') $rowErrors[] = 'guardian_name is required when guardian_relation is other';
                if ($guardianPhone === '') $rowErrors[] = 'guardian_phone is required when guardian_relation is other';
            }

            // ---- admission_no duplicate checks (within file AND in DB) ----
            if ($admissionNo !== '') {
                if (isset($admissionNosSeen[$admissionNo])) {
                    $rowErrors[] = "admission_no '{$admissionNo}' is duplicated in this file (first seen on row {$admissionNosSeen[$admissionNo]})";
                } else {
                    $admissionNosSeen[$admissionNo] = $rowNum;
                }
                if (Student::where('admission_no', $admissionNo)->exists() || User::where('username', $admissionNo)->exists()) {
                    $rowErrors[] = "admission_no '{$admissionNo}' already exists in the system";
                }
            }

            // ---- Class / Section / Group / Category / House lookups ----
            $classModel = $className !== '' ? SchoolClass::where('name', $className)->first() : null;
            if ($className !== '' && !$classModel) $rowErrors[] = "class_name '{$className}' not found";

            $sectionModel = $sectionName !== '' ? Section::where('name', $sectionName)->first() : null;
            if ($sectionName !== '' && !$sectionModel) $rowErrors[] = "section_name '{$sectionName}' not found";

            if ($classModel && $sectionModel) {
                $pivotOk = SchoolClassSection::where('school_class_id', $classModel->id)
                    ->where('section_id', $sectionModel->id)
                    ->where('status', 1)
                    ->exists();
                if (!$pivotOk) {
                    $rowErrors[] = "section '{$sectionName}' is not assigned to class '{$className}'";
                }
            }

            $groupModel = null;
            if ($groupName !== '') {
                $groupModel = Group::where('name', $groupName)->first();
                if (!$groupModel) $rowErrors[] = "group_name '{$groupName}' not found";
            }

            $categoryModel = null;
            if ($categoryName !== '') {
                $categoryModel = StudentCategory::where('name', $categoryName)->first();
                if (!$categoryModel) $rowErrors[] = "category_name '{$categoryName}' not found";
            }

            $houseModel = null;
            if ($houseName !== '') {
                $houseModel = StudentHouse::where('name', $houseName)->first();
                if (!$houseModel) $rowErrors[] = "house_name '{$houseName}' not found";
            }

            if (!empty($rowErrors)) {
                $this->errors[] = "Row {$rowNum}: " . implode('; ', $rowErrors);
                continue;
            }

            $validatedRows[] = [
                'row_num'           => $rowNum,
                'admission_no'      => $admissionNo,
                'class'             => $classModel,
                'section'           => $sectionModel,
                'group'             => $groupModel,
                'category'          => $categoryModel,
                'house'             => $houseModel,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'gender'            => $gender,
                'date_of_birth'     => $dob,
                'admission_date'    => $admissionDate,
                'religion'          => $religion ?: null,
                'caste'             => $caste ?: null,
                'blood_group'       => $bloodGroup ?: null,
                'father_name'       => $fatherName,
                'father_phone'      => $fatherPhone,
                'father_cnic'       => $fatherCnic,
                'mother_name'       => $motherName ?: null,
                'mother_phone'      => $motherPhone ?: null,
                'mother_cnic'       => $motherCnic ?: null,
                'guardian_relation' => $guardianRelation,
                'guardian_name'     => $guardianName ?: null,
                'guardian_phone'    => $guardianPhone ?: null,
                'guardian_email'    => $guardianEmail ?: null,
                'guardian_address'  => $guardianAddress ?: null,
            ];
        }

        // Whole-file-reject: any error anywhere means nothing gets inserted
        if (!empty($this->errors)) {
            return;
        }

        DB::transaction(function () use ($validatedRows, $activeYear) {
            $defaultPassword = Hash::make(self::DEFAULT_PASSWORD);
            $parentProfilesInBatch = []; // father_cnic => ParentProfile (so siblings in the same file share one parent)

            foreach ($validatedRows as $data) {
                // ---- Parent / Guardian (reuse if sibling, else create) ----
                $fatherCnic = $data['father_cnic'];

                if (isset($parentProfilesInBatch[$fatherCnic])) {
                    $parentProfile = $parentProfilesInBatch[$fatherCnic];
                } else {
                    $parentProfile = ParentProfile::where('father_cnic', $fatherCnic)->first();

                    if (!$parentProfile) {
                        $parentUser = User::create([
                            'name'     => $data['father_name'],
                            'username' => $fatherCnic,
                            'email'    => $data['guardian_email'],
                            'password' => $defaultPassword,
                            'role_id'  => 7,
                            'status'   => 2,
                        ]);
                        $parentUser->assignRole('parent');

                        $parentProfile = ParentProfile::create([
                            'user_id'           => $parentUser->id,
                            'father_name'       => $data['father_name'],
                            'father_phone'      => $data['father_phone'],
                            'mother_name'       => $data['mother_name'],
                            'mother_phone'      => $data['mother_phone'],
                            'is_guardian'       => $data['guardian_relation'],
                            'guardian_name'     => $data['guardian_name'],
                            'guardian_relation' => $data['guardian_relation'],
                            'guardian_phone'    => $data['guardian_phone'],
                            'guardian_email'    => $data['guardian_email'],
                            'guardian_address'  => $data['guardian_address'],
                            'father_cnic'       => $fatherCnic,
                            'mother_cnic'       => $data['mother_cnic'],
                        ]);
                    }

                    $parentProfilesInBatch[$fatherCnic] = $parentProfile;
                }

                // ---- Roll No (auto-generated, same sequence logic as StoreStudent) ----
                [$classCode, $sectionChar, $groupCode, $rollMaxSeq] = $this->buildRollSequence(
                    $data['class'], $data['section'], $data['group'], $activeYear->id
                );
                $seqStr = str_pad($rollMaxSeq + 1, 2, '0', STR_PAD_LEFT);
                $rollNo = $groupCode
                    ? "{$classCode}-{$groupCode}-{$sectionChar}-{$seqStr}"
                    : "{$classCode}-{$sectionChar}-{$seqStr}";

                // ---- Student User + Student record ----
                $studentUser = User::create([
                    'name'     => $data['first_name'] . ' ' . $data['last_name'],
                    'username' => $data['admission_no'],
                    'email'    => null,
                    'password' => $defaultPassword,
                    'role_id'  => 8,
                    'status'   => 2,
                ]);
                $studentUser->assignRole('student');

                $student = Student::create([
                    'user_id'        => $studentUser->id,
                    'parent_id'      => $parentProfile->id,
                    'admission_no'   => $data['admission_no'],
                    'roll_number'    => $rollNo,
                    'first_name'     => $data['first_name'],
                    'last_name'      => $data['last_name'],
                    'gender'         => $data['gender'],
                    'date_of_birth'  => $data['date_of_birth'],
                    'admission_date' => $data['admission_date'],
                    'category_id'    => $data['category']?->id,
                    'house_id'       => $data['house']?->id,
                    'religion'       => $data['religion'],
                    'caste'          => $data['caste'],
                    'blood_group'    => $data['blood_group'],
                ]);

                Enrollment::create([
                    'student_id'       => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'class_id'         => $data['class']->id,
                    'section_id'       => $data['section']->id,
                    'group_id'         => $data['group']?->id,
                    'roll_no'          => $rollNo,
                    'enroll_status'    => 1,
                ]);

                $this->importedCount++;
            }
        });
    }

    /**
     * Same sequence-generation logic as StudentController::buildRollSequence(),
     * duplicated here per project convention (see ParentsController note on
     * not sharing traits between controllers/import classes).
     */
    private function buildRollSequence($classModel, $sectionModel, $groupModel, $academicYearId): array
    {
        $classCode = $classModel->roll_code;
        $sectionChar = strtoupper(substr(trim($sectionModel->name), 0, 1));
        $groupCode = $groupModel ? strtoupper(trim($groupModel->group_code ?? $groupModel->name)) : null;

        $pattern = $groupCode
            ? $classCode . '-' . $groupCode . '-' . $sectionChar . '-%'
            : $classCode . '-' . $sectionChar . '-%';

        $maxSeq = Enrollment::where('class_id', $classModel->id)
            ->where('section_id', $sectionModel->id)
            ->where('academic_year_id', $academicYearId)
            ->where('roll_no', 'like', $pattern)
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(roll_no, '-', -1) AS UNSIGNED)) as max_seq")
            ->value('max_seq') ?? 0;

        return [$classCode, $sectionChar, $groupCode, $maxSeq];
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$y, $m, $d] = explode('-', $date);
        return checkdate((int) $m, (int) $d, (int) $y);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}