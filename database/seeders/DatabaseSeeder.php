<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks to truncate safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate dependent tables first
        DB::table('attendances')->truncate();
        DB::table('schedules')->truncate();
        DB::table('subjects')->truncate();
        DB::table('rooms')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Call seeders in proper order
        $this->call([
            RolesTableSeeder::class,
            UserSeeder::class,
            RoomSeeder::class,
            SubjectSeeder::class,
            ScheduleSeeder::class,
            AttendanceSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
