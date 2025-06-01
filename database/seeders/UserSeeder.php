<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs from the roles table
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'teacher')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@uclm.edu.ph',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Teacher One',
            'email' => 'teacher@uclm.edu.ph',
            'password' => bcrypt('password'),
            'role_id' => $teacherRole->id,
        ]);
    }
}
