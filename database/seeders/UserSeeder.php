<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

        // Departments with realistic teacher names
        $departments = [
            'College of Computer Studies' => [
                'John Doe',
                'Sarah Mitchell',
                'Michael Chen',
                'Emily Rodriguez',
                'David Thompson'
            ],
            'Nursing' => [
                'Jennifer Anderson',
                'Robert Martinez',
                'Lisa Williams',
                'James Parker',
                'Maria Garcia'
            ],
        ];

        // 1 Admin (global)
        User::updateOrCreate(
            ['email' => 'uclmaitas@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
                'department' => 'Administration',
                'status' => 'active',
            ]
        );

        // Create teachers for each department
        foreach ($departments as $dept => $teachers) {
            foreach ($teachers as $index => $teacherName) {
                // Generate email from name (e.g., john.doe@example.com)
                $emailName = strtolower(str_replace(' ', '.', $teacherName));
                
                User::updateOrCreate(
                    ['email' => $emailName . '@example.com'],
                    [
                        'name' => $teacherName,
                        'password' => bcrypt('password'),
                        'role_id' => $teacherRole->id,
                        'department' => $dept,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}