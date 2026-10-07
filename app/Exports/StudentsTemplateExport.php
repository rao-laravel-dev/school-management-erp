<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithColumnFormatting
{
    /**
     * Column headers — must match the columns expected by StudentsImport.
     */
    public function headings(): array
    {
        return [
            'admission_no', 'class_name', 'section_name', 'group_name',
            'first_name', 'last_name', 'gender', 'date_of_birth', 'admission_date',
            'category_name', 'religion', 'caste', 'blood_group', 'house_name',
            'father_name', 'father_phone', 'father_cnic',
            'mother_name', 'mother_phone', 'mother_cnic',
            'guardian_relation', 'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_address',
        ];
    }

    /**
     * One example/dummy row shown under the headers as a filling guide.
     */
    public function array(): array
    {
        return [
            [
                'OLD-2019-045', 'Class 5', 'A', '',
                'Ali', 'Khan', 'male', '2013-05-10', '2019-04-01',
                'General/Regular', 'Islam', '', 'O+', 'Red',
                'Muhammad Khan', '03001234567', '42101-1234567-8',
                'Ayesha Khan', '03007654321', '42101-7654321-8',
                'father', '', '', '', '',
            ],
        ];
    }

    /**
     * Bold the header row so it's visually distinct from the example row.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Column widths — one entry per column letter (A-Y = 25 columns),
     * so nothing shows truncated when the admin opens the file.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 16, // admission_no
            'B' => 14, // class_name
            'C' => 14, // section_name
            'D' => 14, // group_name
            'E' => 16, // first_name
            'F' => 16, // last_name
            'G' => 10, // gender
            'H' => 14, // date_of_birth
            'I' => 15, // admission_date
            'J' => 18, // category_name
            'K' => 12, // religion
            'L' => 12, // caste
            'M' => 12, // blood_group
            'N' => 12, // house_name
            'O' => 20, // father_name
            'P' => 15, // father_phone
            'Q' => 18, // father_cnic
            'R' => 20, // mother_name
            'S' => 15, // mother_phone
            'T' => 18, // mother_cnic
            'U' => 16, // guardian_relation
            'V' => 20, // guardian_name
            'W' => 15, // guardian_phone
            'X' => 22, // guardian_email
            'Y' => 25, // guardian_address
        ];
    }

    /**
     * Force date columns (H = date_of_birth, I = admission_date) to be
     * treated as plain text, so Excel doesn't auto-convert/reformat them.
     */
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }
}