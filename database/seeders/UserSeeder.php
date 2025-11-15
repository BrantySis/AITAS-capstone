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
            'Hospitality Management',
            'Engineering',
            'College of Computer Studies',
            'Marine Engineering',
            'Marine Transportation',
            'Nursing',
            'Customs Administration',
            'Accountancy',
            'College of Teacher Education',
            'Criminology',
            'SHS',
            'Basic Education',
            'General Education'
        ];

        foreach ($departments as $dept) {
            // 1 Admin per department
            User::updateOrCreate(
                ['email' => strtolower(str_replace(' ','', $dept)) . '.admin@example.com'],
                [
                    'name' => $dept . ' Admin',
                    'password' => bcrypt('password'),
                    'role_id' => $adminRole->id,
                    'department' => $dept,
                    'status' => 'active',
                ]
            );

            // 2 Teachers per department
            for ($i=1; $i<=2; $i++) {
                User::updateOrCreate(
                    ['email' => strtolower(str_replace(' ','', $dept)) . ".teacher{$i}@example.com"],
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
                ['email' => strtolower(str_replace(' ','', $dept)) . '.dean@example.com'],
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
