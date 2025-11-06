<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate tables to avoid duplicate key errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable FK checks
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::table('rooms')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable FK checks

        // Call individual seeders
        $this->call(RolesTableSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RoomSeeder::class);
    }
}
