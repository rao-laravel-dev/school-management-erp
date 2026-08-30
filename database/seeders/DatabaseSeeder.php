<?php

namespace Database\Seeders;

use Database\Seeders\UserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // System Auth Seeders
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,

            // School Foundation/Structure
            SchoolDataSeeder::class,

            // Dummy/Test Data
            // StudentEnrollmentSeeder::class,
        ]);
    }
}
