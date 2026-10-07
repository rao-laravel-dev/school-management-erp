<?php

namespace App\Exports;

use App\Models\Enrollment;
use App\Models\SiteSetting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    protected ?string $classId;
    protected ?string $sectionId;
    protected ?string $groupId;
    protected ?string $keyword;
    protected $siteSetting;
    protected ?string $logoPath;

    // Header block ke liye kitni rows insert karni hain (logo/school-info)
    const HEADER_ROWS = 4;

    public function __construct($classId = null, $sectionId = null, $groupId = null, $keyword = null, $siteSetting = null, $logoPath = null)
    {
        $this->classId     = $classId;
        $this->sectionId   = $sectionId;
        $this->groupId     = $groupId;
        $this->keyword     = $keyword;
        $this->siteSetting = $siteSetting ?? SiteSetting::current();
        $this->logoPath    = $logoPath;
    }

    public function collection()
    {
        $query = Enrollment::with([
            'student.user',
            'student.parent',
            'student.category',
            'student.house',
            'schoolClass',
            'section',
            'group',
        ]);

        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }
        if ($this->groupId) {
            $query->where('group_id', $this->groupId);
        }
        if ($this->keyword) {
            $keyword = $this->keyword;
            $query->whereHas('student', function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('admission_no', 'like', "%{$keyword}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Admission No', 'Login ID', 'Full Name', 'Gender', 'Date of Birth', 'Admission Date',
            'Class', 'Section', 'Group', 'Roll No',
            'Category', 'House', 'Religion', 'Caste', 'Blood Group',
            'Father Name', 'Father Phone', 'Father CNIC',
            'Mother Name', 'Mother Phone', 'Mother CNIC',
            'Guardian Relation', 'Guardian Name', 'Guardian Phone', 'Guardian Email', 'Guardian Address',
            'Status',
        ];
    }

    public function map($enrollment): array
    {
        $student = $enrollment->student;
        $parent  = $student->parent;

        return [
            $student->admission_no,
            optional($student->user)->username ?? $student->admission_no,
            trim($student->first_name . ' ' . $student->last_name),
            ucfirst($student->gender),
            $student->date_of_birth,
            $student->admission_date,
            $enrollment->schoolClass->name ?? '',
            $enrollment->section->name ?? '',
            $enrollment->group->name ?? '-',
            $enrollment->roll_no,
            $student->category->name ?? '-',
            $student->house->name ?? '-',
            $student->religion ?? '-',
            $student->caste ?? '-',
            $student->blood_group ?? '-',
            $parent->father_name ?? '',
            $parent->father_phone ?? '',
            $parent->father_cnic ?? '',
            $parent->mother_name ?? '-',
            $parent->mother_phone ?? '-',
            $parent->mother_cnic ?? '-',
            ucfirst($parent->guardian_relation ?? ''),
            $parent->guardian_name ?? '-',
            $parent->guardian_phone ?? '-',
            $parent->guardian_email ?? '-',
            $parent->guardian_address ?? '-',
            optional($student->user)->status == 1 ? 'Active' : 'Inactive',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 12, 'C' => 25, 'D' => 10, 'E' => 12, 'F' => 12,
            'G' => 12, 'H' => 10, 'I' => 12, 'J' => 14,
            'K' => 14, 'L' => 12, 'M' => 12, 'N' => 12, 'O' => 12,
            'P' => 20, 'Q' => 15, 'R' => 18,
            'S' => 20, 'T' => 15, 'U' => 18,
            'V' => 14, 'W' => 20, 'X' => 15, 'Y' => 22, 'Z' => 25,
            'AA' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Headings row ab HEADER_ROWS ke baad shift ho jayegi (AfterSheet mein handle hoga)
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));
                $headerRowInserted = self::HEADER_ROWS;

                // 1. Upar 4 khali rows insert karo (headings row 1 se ab (HEADER_ROWS+1) pe chali jayegi)
                $sheet->insertNewRowBefore(1, $headerRowInserted);

                // 2. Row 1: School Name
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', $this->siteSetting->school_name ?? config('app.name'));
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 3. Row 2: Address | Phone | Email
                $contactLine = collect([
                    $this->siteSetting->address ?? null,
                    $this->siteSetting->phone ? 'Ph: ' . $this->siteSetting->phone : null,
                    $this->siteSetting->email ?? null,
                ])->filter()->implode('   |   ');

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', $contactLine);
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 4. Row 3: "Student List" title
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Student List');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('4472C4');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 5. Row 4: Generated Date & Time
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', 'Generated: ' . now()->format('d M Y, h:i A'));
                $sheet->getStyle('A4')->getFont()->setSize(9)->getColor()->setRGB('777777');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 6. Row Heights thora barha dein header rows ki
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(16);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(14);

                // 7. Logo insert karo (agar available ho) — top-left corner
                if ($this->logoPath && file_exists($this->logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('School Logo');
                    $drawing->setPath($this->logoPath);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(2);
                    $drawing->setWorksheet($sheet);
                }

                // 8. Actual headings row (ab row = HEADER_ROWS + 1) style karo
                $headingRowNum = $headerRowInserted + 1;
                $sheet->getStyle("A{$headingRowNum}:{$lastCol}{$headingRowNum}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // 9. Data rows ke around thin border (zebra Excel mein manual karna padta — yahan optional light border)
                $lastDataRow = $sheet->getHighestRow();
                $sheet->getStyle("A{$headingRowNum}:{$lastCol}{$lastDataRow}")->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
            },
        ];
    }
}