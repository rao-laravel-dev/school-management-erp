<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SuperAdmin
        $sa = User::create([
            'name'     => 'Super Admin',
            'photo'    => 'null',
            'username' => 'superadmin',
            'email'    => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin123@'),
        ]);
        $sa->assignRole('superadmin');

        // Admin
        $ad = User::create([
            'name'     => 'Admin',
            'photo'    => 'null',
            'username' => 'admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('admin123@'),
        ]);
        $ad->assignRole('admin');

        // Receptionist
        $rec = User::create([
            'name'     => 'Receptionist',
            'photo'    => 'null',
            'username' => 'receptionist',
            'email'    => 'receptionist@gmail.com',
            'password' => Hash::make('receptionist123@'),
        ]);
        $rec->assignRole('receptionist');

        // Accountant
        $acc = User::create([
            'name'     => 'Accountant',
            'photo'    => 'null',
            'username' => 'accountant',
            'email'    => 'accountant@gmail.com',
            'password' => Hash::make('accountant123@'),
        ]);
        $acc->assignRole('accountant');

        // Librarian
        $lib = User::create([
            'name'     => 'Librarian',
            'photo'    => 'null',
            'username' => 'librarian',
            'email'    => 'librarian@gmail.com',
            'password' => Hash::make('librarian123@'),
        ]);
        $lib->assignRole('librarian');

        // Teacher
        $tea = User::create([
            'name'     => 'Teacher',
            'photo'    => 'null',
            'username' => 'teacher',
            'email'    => 'teacher@gmail.com',
            'password' => Hash::make('teacher123@'),
        ]);
        $tea->assignRole('teacher');

        // Parent
        $par = User::create([
            'name'     => 'Parent',
            'photo'    => 'null',
            'username' => 'parent',
            'email'    => 'parent@gmail.com',
            'password' => Hash::make('parent123@'),
        ]);
        $par->assignRole('parent');

        // Student
        $stu = User::create([
            'name'     => 'Student',
            'photo'    => 'null',
            'username' => 'student',
            'email'    => 'student@gmail.com',
            'password' => Hash::make('student123@'),
        ]);
        $stu->assignRole('student');

        // User
        $usr = User::create([
            'name'     => 'User',
            'photo'    => 'null',
            'username' => 'user',
            'email'    => 'user@gmail.com',
            'password' => Hash::make('user123@'),
        ]);
        $usr->assignRole('user');
    }
}
