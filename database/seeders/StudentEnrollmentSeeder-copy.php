<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class StudentEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 3; $i++) {
            $email = $faker->unique()->safeEmail;
            
            // 1. User account for Parent
            $userId = DB::table('users')->insertGetId([
                'name'     => $faker->name,
                'email'    => $email,
                'username' => explode('@', $email)[0] . rand(100, 999),
                'password' => Hash::make('password'),
                'created_at' => now()
            ]);

            // 2. Parent Profile
            $parentId = DB::table('parent_profiles')->insertGetId([
                'user_id'           => $userId,
                'father_name'       => $faker->name('male'),
                'father_cnic'       => $faker->unique()->numerify('#############'),
                'father_phone'      => '0300-1234567',
                'mother_name'       => $faker->name('female'),
                'created_at'        => now()
            ]);

            // 3. Students
            for ($j = 0; $j < 2; $j++) {
                // Student ke liye alag User account (kyunki aap ke schema mein user_id mandatory hai)
                $sEmail = $faker->unique()->safeEmail;
                $sUserId = DB::table('users')->insertGetId([
                    'name' => $faker->firstName,
                    'email' => $sEmail,
                    'username' => 'stud_' . rand(1000, 9999),
                    'password' => Hash::make('password'),
                    'created_at' => now()
                ]);

                $class = DB::table('school_class')->inRandomOrder()->first();
                $section = DB::table('sections')->inRandomOrder()->first();

                DB::table('students')->insert([
                    'user_id'     => $sUserId,      // Mandatory field
                    'parent_id'   => $parentId,     // Updated to parent_id
                    'first_name'  => $faker->firstName,
                    'last_name'   => $faker->lastName,
                    'admission_no'=> 'ADM-' . rand(1000, 9999),
                    'roll_number' => 'R-' . rand(100, 999),
                    'class_id'    => $class->id,
                    'section_id'  => $section->id,
                    'created_at'  => now()
                ]);
            }
        }
    }
}