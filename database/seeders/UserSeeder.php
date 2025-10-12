<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs from the roles table
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@uclm.edu.ph'], // unique identifier
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
            ]
        );

        // Teacher user
        User::updateOrCreate(
            ['email' => 'teacher@uclm.edu.ph'], // unique identifier
            [
                'name' => 'Teacher One',
                'password' => bcrypt('password'),
                'role_id' => $teacherRole->id,
            ]
        );
    }
}
