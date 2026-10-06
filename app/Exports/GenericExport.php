<?php

namespace App\Exports;

use App\Models\SiteSetting;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class GenericExport implements FromArray, WithHeadings, WithColumnWidths, WithEvents, WithStrictNullComparison
{
    protected array $rows;
    protected array $headings;
    protected string $title;      // e.g. "Attendance Report", "Class List"
    protected $siteSetting;
    protected ?string $logoPath;
    protected array $widths;      // optional: ['A' => 15, 'B' => 20, ...]

    const HEADER_ROWS = 4;

    public function __construct(array $rows, array $headings, string $title, $siteSetting = null, ?string $logoPath = null, array $widths = [])
    {
        $this->rows        = $rows;
        $this->headings     = $headings;
        $this->title        = $title;
        $this->siteSetting  = $siteSetting ?? SiteSetting::current();
        $this->logoPath     = $logoPath;
        $this->widths       = $widths;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function columnWidths(): array
    {
        return $this->widths; // khaali chhod dein to default width chalegi
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings));
                $headerRowInserted = self::HEADER_ROWS;

                $sheet->insertNewRowBefore(1, $headerRowInserted);

                // Row 1: School Name — ab B se merge (A khaali, sirf logo ke liye)
                $sheet->mergeCells("B1:{$lastCol}1");
                $sheet->setCellValue('B1', $this->siteSetting->school_name ?? config('app.name'));
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $contactLine = collect([
                    $this->siteSetting->address ?? null,
                    $this->siteSetting->phone ? 'Ph: ' . $this->siteSetting->phone : null,
                    $this->siteSetting->email ?? null,
                ])->filter()->implode('   |   ');

                $sheet->mergeCells("B2:{$lastCol}2");
                $sheet->setCellValue('B2', $contactLine);
                $sheet->getStyle('B2')->getFont()->setSize(10);
                $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

                $sheet->mergeCells("B3:{$lastCol}3");
                $sheet->setCellValue('B3', $this->title);
                $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('4472C4');
                $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells("B4:{$lastCol}4");
                $sheet->setCellValue('B4', 'Generated: ' . now()->format('d M Y, h:i A'));
                $sheet->getStyle('B4')->getFont()->setSize(9)->getColor()->setRGB('777777');
                $sheet->getStyle('B4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(14);

                if ($this->logoPath && file_exists($this->logoPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('School Logo');
                    $drawing->setPath($this->logoPath);
                    $drawing->setHeight(45);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(2);
                    $drawing->setWorksheet($sheet);
                }

                $headingRowNum = $headerRowInserted + 1;
                $sheet->getStyle("A{$headingRowNum}:{$lastCol}{$headingRowNum}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $lastDataRow = $sheet->getHighestRow();
                $sheet->getStyle("A{$headingRowNum}:{$lastCol}{$lastDataRow}")->getBorders()
                    ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CCCCCC'));
                    
                // Data cells: sab left align (Excel numbers ko khud right karta hai), # column center
                $firstDataRow = $headingRowNum + 1;

                if ($lastDataRow >= $firstDataRow) {
                    $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    if (($this->headings[0] ?? null) === '#') {
                        $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }
}
