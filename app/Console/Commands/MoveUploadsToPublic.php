<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MoveUploadsToPublic extends Command
{
    protected $signature = 'uploads:move-to-public {--dry-run : Sirf counts dikhao, kuch copy mat karo}';

    protected $description = 'Copy files from storage/app/public to public/uploads (copy only, nothing deleted, existing files skipped)';

    public function handle()
    {
        $dry = $this->option('dry-run');
        $source = storage_path('app/public');

        // storage/app/public/<key> -> public/uploads/<value>
        $map = [
            'uploads'          => '',
            'teacher_images'   => 'teacher_images',
            'book_covers'      => 'book_covers',
            'complaints'       => 'complaints',
            'leave_documents'  => 'leave_documents',
        ];

        $rows = [];
        foreach ($map as $from => $to) {
            $fromDir = $source . '/' . $from;
            if (!File::isDirectory($fromDir)) {
                continue;
            }

            $toBase = public_path('uploads' . ($to !== '' ? '/' . $to : ''));
            $found = $copied = $skipped = $failed = 0;

            foreach (File::allFiles($fromDir) as $file) {
                $found++;
                $target = $toBase . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if (File::exists($target)) {
                    $skipped++;
                    continue;
                }

                if ($dry) {
                    $copied++;
                    continue;
                }

                File::ensureDirectoryExists(dirname($target));
                File::copy($file->getPathname(), $target) ? $copied++ : $failed++;
            }

            $rows[] = ['storage/app/public/' . $from, 'public/uploads' . ($to !== '' ? '/' . $to : '/*'), $found, $copied, $skipped, $failed];
        }

        $this->table(['From', 'To', 'Found', $dry ? 'Would copy' : 'Copied', 'Skipped (exists)', 'Failed'], $rows);
        $this->info($dry ? 'Dry run only, nothing copied.' : 'Done. Nothing was deleted from storage.');

        return self::SUCCESS;
    }
}
