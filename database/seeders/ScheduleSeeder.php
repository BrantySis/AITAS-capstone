<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Room;
use App\Models\User;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Replace with actual existing IDs in your users and rooms table
        $schedules = [
            [
                'user_id' => 1,
                'room_id' => 1,
                'edp_code' => 'EDP1234',
                'subject' => 'IT Capstone',
                'units' => 3,
                'type' => 'lecture',
                'day' => 'Monday',
                'time_start' => '09:00:00',
                'time_end' => '10:30:00',
            ],
            [
                'user_id' => 1,
                'room_id' => 2,
                'edp_code' => 'EDP5678',
                'subject' => 'System Admin',
                'units' => 3,
                'type' => 'lab',
                'day' => 'Wednesday',
                'time_start' => '13:00:00',
                'time_end' => '15:00:00',
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
