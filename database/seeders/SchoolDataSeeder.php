<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolDataSeeder extends Seeder
{
    public function run(): void
    {
    
        // 1. Sections
        $secA = DB::table('sections')->insertGetId(['name' => 'A', 'section_code' => 'SEC-A', 'created_at' => now()]);
        $secB = DB::table('sections')->insertGetId(['name' => 'B', 'section_code' => 'SEC-B', 'created_at' => now()]);

        // 2. Groups
        $groups = ['General', 'Primary', 'Secondary', 'Science', 'Arts', 'Computer Science'];
        foreach ($groups as $g) {
            DB::table('groups')->insert(['name' => $g, 'group_code' => Str::upper(substr($g, 0, 3)), 'created_at' => now()]);
        }

        // 3. Subjects
        $subjects = [
            'English', 'Urdu', 'Math', 'Islamiyat', 'Science', 'Social Study', 
            'Computer Science', 'Physics', 'Chemistry', 'Biology', 'General Science', 
            'History', 'Geography', 'Drawing'
        ];
        foreach ($subjects as $sub) {
            DB::table('subjects')->insert([
                'name' => $sub,
                'subject_code' => Str::upper(substr($sub, 0, 3)) . '-' . rand(1000, 9999),
                'created_at' => now()
            ]);
        }

        // 4. Classes Setup (Ordered by 'num')
        $classes = [
            ['name' => 'Class 1', 'code' => 'CLS-1', 'num' => 1],
            ['name' => 'Class 2', 'code' => 'CLS-2', 'num' => 2],
            ['name' => 'Class 3', 'code' => 'CLS-3', 'num' => 3],
            ['name' => 'Class 4', 'code' => 'CLS-4', 'num' => 4],
            ['name' => 'Class 5', 'code' => 'CLS-5', 'num' => 5],
            ['name' => 'Class 6', 'code' => 'CLS-6', 'num' => 6],
            ['name' => 'Class 7', 'code' => 'CLS-7', 'num' => 7],
            ['name' => 'Class 8', 'code' => 'CLS-8', 'num' => 8],
            ['name' => 'Class 9', 'code' => 'CLS-9', 'num' => 9],
            ['name' => 'Class 10', 'code' => 'CLS-10', 'num' => 10],
            ['name' => 'Montessori', 'code' => 'CLS-MONT', 'num' => 11],
            ['name' => 'Nursery', 'code' => 'CLS-NUR', 'num' => 22],
            ['name' => 'Prep', 'code' => 'CLS-PRP', 'num' => 33],
            ['name' => 'KG-1', 'code' => 'CLS-KG1', 'num' => 44],
            ['name' => 'KG-2', 'code' => 'CLS-KG2', 'num' => 55],
        ];

        foreach ($classes as $c) {
            $num = $c['num'];

            $classId = DB::table('school_class')->insertGetId([
                'name'         => $c['name'],
                'class_code'   => $c['code'],
                'numeric_name' => $num,
                'slug'         => Str::slug($c['name']),
                'created_at'   => now()
            ]);

            // Section Assignment Logic: Class 8, 9, 10 ke liye 2 sections
            // Note: Agar num 1-7 hai to sirf $secA, agar 8-10 hai to $secA, $secB
            $sections = ($num >= 8 && $num <= 10) ? [$secA, $secB] : [$secA];
            
            foreach ($sections as $sId) {
                DB::table('school_class_section')->insert([
                    'school_class_id' => $classId, 
                    'section_id'      => $sId, 
                    'created_at'      => now()
                ]);
            }
        }
    }
}
