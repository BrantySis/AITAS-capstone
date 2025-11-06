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
        // Get role IDs from the roles table or create if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $deanRole = Role::firstOrCreate(['name' => 'dean']); // <- Added this line

        // Admin user
        User::updateOrCreate(
            ['email' => 'andreineri2002@gmail.com'], 
            [
                'name' => 'Andrei Neri',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
            ]
        );

        // Teacher user
        User::updateOrCreate(
            ['email' => 'teacher@uclm.edu.ph'], 
            [
                'name' => 'Teacher One',
                'password' => bcrypt('password'),
                'role_id' => $teacherRole->id,
            ]
        );

        // Dean user
        User::updateOrCreate(
            ['email' => 'dean@uclm.edu.ph'], 
            [
                'name' => 'Dean Example',
                'password' => bcrypt('password'),
                'role_id' => $deanRole->id,
            ]
        );
    }
}
