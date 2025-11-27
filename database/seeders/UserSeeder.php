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
        $deanRole = Role::firstOrCreate(['name' => 'dean']);

        // Departments
        $departments = [
            'College of Computer Studies',
            'Nursing',
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

        foreach ($departments as $dept) {
            // 5 Teachers per department
            for ($i = 1; $i <= 5; $i++) {
                User::updateOrCreate(
                    ['email' => strtolower(str_replace(' ', '', $dept)) . ".teacher{$i}@example.com"],
                    [
                        'name' => $dept . " Teacher {$i}",
                        'password' => bcrypt('password'),
                        'role_id' => $teacherRole->id,
                        'department' => $dept,
                        'status' => 'active',
                    ]
                );
            }

            // 1 Dean per department
            User::updateOrCreate(
                ['email' => strtolower(str_replace(' ', '', $dept)) . '.dean@example.com'],
                [
                    'name' => $dept . ' Dean',
                    'password' => bcrypt('password'),
                    'role_id' => $deanRole->id,
                    'department' => $dept,
                    'status' => 'active',
                ]
            );
        }
    }
}
