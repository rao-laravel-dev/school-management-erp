<?php

namespace App\Imports;

use App\Models\SchoolClass;
use Illuminate\Support\Str;

class SchoolClassImport extends ValidatedImport
{
    protected function rules(): array
    {
        return [
            'name' => [
                'bail', // pehla error aate hi baaki name rules skip
                'required', 'string', 'max:255',
                'unique:school_class,name',
                function ($attribute, $value, $fail) {
                    // "Class-11" aur "Class 11" ka slug same banta hai
                    if (SchoolClass::where('slug', Str::slug($value))->exists()) {
                        $fail('A class with a similar name already exists.');
                    }
                },
            ],
            'numeric_name' => ['required', 'integer', 'unique:school_class,numeric_name'],
            'class_code'   => [
                'required', 'max:20', 'regex:/^[A-Z0-9]+$/',
                function ($attribute, $value, $fail) {
                    if (SchoolClass::where('class_code', 'CLS-' . $value)->exists()) {
                        $fail('Class code ' . $value . ' already exists.');
                    }
                },
            ],
            'has_subjects' => ['required', 'boolean'],
            'description'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique'           => 'Class ":input" already exists.',
            'numeric_name.unique'   => 'Numeric name :input is already used by another class.',
            'class_code.regex'      => 'Class code must be capital letters/digits only (no dash/space).',
            'has_subjects.required' => 'The has_subjects must be yes or no.',
        ];
    }

    protected function uniqueInFile(): array
    {
        return ['name', 'numeric_name', 'class_code'];
    }

    protected function prepare(array $row): array
    {
        // Excel kabhi 5 ko 5.0 bana deta hai
        if (is_numeric($row['numeric_name'] ?? null) && floor($row['numeric_name']) == $row['numeric_name']) {
            $row['numeric_name'] = (int) $row['numeric_name'];
        }

        $row['has_subjects'] = match (strtolower((string) ($row['has_subjects'] ?? ''))) {
            'yes', 'y', '1', 'true' => true,
            'no', 'n', '0', 'false' => false,
            default                 => null,
        };

        $row['class_code'] = strtoupper(trim((string) ($row['class_code'] ?? '')));

        $row['slug'] = Str::slug($row['name'] ?? '');

        return $row;
    }

    protected function persist(array $data): void
    {
        SchoolClass::create([
            'name'         => $data['name'],
            'has_subjects' => $data['has_subjects'],
            'numeric_name' => $data['numeric_name'],
            'class_code'   => 'CLS-' . $data['class_code'], // StoreClass jaisa
            'slug'         => $data['slug'],
            'description'  => $data['description'] ?? null,
            'status'       => 1,
        ]);
    }
}