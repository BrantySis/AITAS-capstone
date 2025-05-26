<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@uclm.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    
        \App\Models\User::create([
            'name' => 'Teacher One',
            'email' => 'teacher@uclm.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
    }
}
