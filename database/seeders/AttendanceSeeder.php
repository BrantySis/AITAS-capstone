<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get();
        $schedules = Schedule::all();

        if ($teachers->isEmpty() || $schedules->isEmpty()) {
            $this->command->warn('No teachers or schedules found. Run Users and Schedule seeders first.');
            return;
        }

        $statuses = ['Attended', 'Late', 'Missed'];

        foreach ($schedules as $schedule) {
            // Randomly pick a status
            $status = $statuses[array_rand($statuses)];

            $timeIn = null;
            $timeOut = null;
            $isValid = 0;

            if ($status === 'Attended') {
                $timeIn = Carbon::parse($schedule->starts_at)
                    ->subMinutes(rand(0, 5)) // on time or slightly early
                    ->format('H:i:s');

                $timeOut = Carbon::parse($schedule->ends_at)
                    ->addMinutes(rand(0, 10)) // slightly longer class
                    ->format('H:i:s');

                $isValid = 1;
            } elseif ($status === 'Late') {
                $timeIn = Carbon::parse($schedule->starts_at)
                    ->addMinutes(rand(5, 15)) // late by 5-15 minutes
                    ->format('H:i:s');

                $timeOut = Carbon::parse($schedule->ends_at)
                    ->addMinutes(rand(0, 10))
                    ->format('H:i:s');

                $isValid = 1; // still considered valid attendance
            }
            // Missed: timeIn & timeOut remain null, isValid = 0

            // Randomized location near room for demo
            $roomLat = $schedule->room->latitude ?? 10.3158;
            $roomLng = $schedule->room->longitude ?? 123.8855;

            $lat = $roomLat + ((rand(-50,50)/10000)); // +/- ~0.005 degrees
            $lng = $roomLng + ((rand(-50,50)/10000));

            Attendance::updateOrCreate(
                [
                    'user_id' => $schedule->user_id,
                    'schedule_id' => $schedule->id
                ],
                [
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => $status,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'is_valid' => $isValid,
                    'created_at' => Carbon::now()->subDays(rand(0,30)),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $this->command->info('Attendances seeded with Attended, Late, and Missed statuses.');
    }
}
