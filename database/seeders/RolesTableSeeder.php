<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'teacher'],
            // add more as needed
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']], // check if this ID exists
                ['name' => $role['name']] // update or insert name
            );
        }
    }
}
