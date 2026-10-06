<?php

namespace App\Traits;

use App\Exports\GenericExport;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

trait Exportable
{
    protected function resolveLogoPath(): ?string
    {
        $siteSetting = SiteSetting::current();

        if (!$siteSetting->logo) {
            return null;
        }

        $source = storage_path('app/public/uploads/site_setting/' . $siteSetting->logo);

        if (!file_exists($source)) {
            return null;
        }

        // Already PNG/JPG ho to conversion ki zaroorat nahi
        if (in_array(strtolower(pathinfo($source, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg'], true)) {
            return $source;
        }

        $tempDir = storage_path('app/temp');
        File::ensureDirectoryExists($tempDir);

        $tempPath = $tempDir . '/export_logo_' . Str::uuid() . '.png';
        (new ImageManager(new Driver()))->read($source)->toPng()->save($tempPath);

        return $tempPath;
    }

    /** Sirf conversion se bani temp file delete hoti hai, original logo kabhi nahi */
    protected function cleanupLogo(?string $path): void
    {
        if ($path && str_contains($path, 'export_logo_') && file_exists($path)) {
            @unlink($path);
        }
    }

    protected function exportToExcel(array $rows, array $headings, string $title, string $filename, array $widths = [], bool $serial = true)
    {
        if ($serial) {
            [$rows, $headings] = $this->addSerialColumn($rows, $headings);
            $widths = $widths ? $this->shiftWidths($widths) : [];
        }

        $widths = $widths ?: $this->calculateWidths($headings, $rows);

        if ($serial) {
            $widths['A'] = 7; // S.No column chhota rakhein
        }

        $logoPath = $this->resolveLogoPath();

        try {
            return Excel::download(
                new GenericExport($rows, $headings, $title, SiteSetting::current(), $logoPath, $widths),
                $filename . '_' . now()->format('Y-m-d_His') . '.xlsx'
            );
        } finally {
            $this->cleanupLogo($logoPath);
        }
    }

    protected function exportToPdf(array $rows, array $headings, string $title, string $filename, string $subtitle = '', string $orientation = 'landscape', bool $serial = true)
    {
        if ($serial) {
            [$rows, $headings] = $this->addSerialColumn($rows, $headings);
        }

        $siteSetting = SiteSetting::current();
        $logoPath = $this->resolveLogoPath();

        try {
            return Pdf::loadView('exports.generic-pdf', compact('rows', 'headings', 'title', 'subtitle', 'siteSetting', 'logoPath'))
                ->setPaper('a4', $orientation)
                ->download($filename . '_' . now()->format('Y-m-d_His') . '.pdf');
        } finally {
            $this->cleanupLogo($logoPath);
        }
    }

    /** Har row ke shuru me S.No (1, 2, 3...) aur heading me "#" */
    protected function addSerialColumn(array $rows, array $headings): array
    {
        $rows = array_map(
            fn ($row, $i) => array_merge([$i + 1], array_values($row)),
            array_values($rows),
            array_keys(array_values($rows))
        );

        array_unshift($headings, '#');

        return [$rows, $headings];
    }

    /** Controller ki di hui widths (A, B, C...) ko ek column aage khisakata hai */
    protected function shiftWidths(array $widths): array
    {
        $shifted = [];

        foreach ($widths as $letter => $width) {
            $shifted[Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($letter) + 1)] = $width;
        }

        return $shifted;
    }

    /** Headings aur data ki lambai se column widths (min 12, max 45) */
    protected function calculateWidths(array $headings, array $rows): array
    {
        $widths = [];

        foreach ($headings as $i => $heading) {
            $max = mb_strlen((string) $heading) + 2;

            foreach ($rows as $row) {
                $max = max($max, mb_strlen((string) ($row[$i] ?? '')));
            }

            $widths[Coordinate::stringFromColumnIndex($i + 1)] = min(max($max + 3, 12), 45);
        }

        return $widths;
    }
}