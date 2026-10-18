<?php

namespace App\Console\Commands;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillClassSubjectCodes extends Command
{
    protected $signature = 'subjects:backfill-class-codes {--dry-run : Show what would change without saving}';

    protected $description = 'Number existing class_subject rows (class prefix + 01, 02...). Rows that already have a code are not touched.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $skipped = [];
        $summary = [];

        foreach (SchoolClass::orderBy('numeric_name')->get() as $class) {
            if (!$class->subject_prefix) {
                $skipped[] = $class->name;
                continue;
            }

            $rows = ClassSubject::where('class_id', $class->id)->whereNull('subject_code')->orderBy('id')->get();
            if ($rows->isEmpty()) {
                continue;
            }

            // Same subject (alag groups) = same code; maujooda codes aage rakhe jate hain
            $codeMap = ClassSubject::codeMapForClass($class->id);
            $usedCodes = array_values($codeMap);
            $updated = 0;

            DB::transaction(function () use ($class, $rows, &$codeMap, &$usedCodes, &$updated, $dry) {
                foreach ($rows as $row) {
                    if (!isset($codeMap[$row->subject_id])) {
                        $codeMap[$row->subject_id] = ClassSubject::nextSubjectCode($class, $usedCodes);
                        $usedCodes[] = $codeMap[$row->subject_id];
                    }
                    if (!$dry) {
                        $row->update(['subject_code' => $codeMap[$row->subject_id]]);
                    }
                    $updated++;
                }
            });

            $summary[] = [$class->name, $class->subject_prefix, $updated, implode(', ', array_values($codeMap))];
        }

        $this->info($dry ? 'DRY RUN: nothing saved.' : 'Backfill done.');
        if ($summary) {
            $this->table(['Class', 'Prefix', 'Rows numbered', 'Codes in class'], $summary);
        } else {
            $this->line('No rows needed a code.');
        }
        if ($skipped) {
            $this->warn('Skipped (no Subject Code Prefix set): ' . implode(', ', $skipped));
        }

        return self::SUCCESS;
    }
}
