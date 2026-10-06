<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

abstract class ValidatedImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];
    protected int $imported = 0;

    abstract protected function rules(): array;

    abstract protected function persist(array $data): void;

    /** Columns jo file ke andar unique hone chahiye */
    protected function uniqueInFile(): array
    {
        return [];
    }

    protected function messages(): array
    {
        return [];
    }

    /** Normalize values (yes/no -> bool, name -> id lookup, defaults) */
    protected function prepare(array $row): array
    {
        return $row;
    }

    public function collection(Collection $rows)
    {
        // Blank rows skip (keys preserve hoti hain, is liye row number sahi rehta hai)
        $rows = $rows->filter(
            fn ($r) => $r->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isNotEmpty()
        );

        if ($rows->isEmpty()) {
            $this->errors[] = 'The file has no data rows.';
            return;
        }

        $prepared = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // heading row = 1

            $data = $this->prepare(array_map(
                fn ($v) => is_string($v) ? trim($v) : $v,
                $row->toArray()
            ));

            $validator = Validator::make($data, $this->rules(), $this->messages());

            foreach ($validator->errors()->all() as $message) {
                $this->errors[] = "Row {$line}: {$message}";
            }

            $prepared[$line] = $data;
        }

        foreach ($this->uniqueInFile() as $column) {
            $seen = [];
            foreach ($prepared as $line => $data) {
                $value = mb_strtolower(trim((string) ($data[$column] ?? '')));
                if ($value === '') {
                    continue;
                }
                if (isset($seen[$value])) {
                    $this->errors[] = "Row {$line}: duplicate {$column} '{$data[$column]}' (already in row {$seen[$value]}).";
                } else {
                    $seen[$value] = $line;
                }
            }
        }

        if ($this->errors) {
            return;
        }

        DB::transaction(function () use ($prepared) {
            foreach ($prepared as $data) {
                $this->persist($data);
                $this->imported++;
            }
        });
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }
}