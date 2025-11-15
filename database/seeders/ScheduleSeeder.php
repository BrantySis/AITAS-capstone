<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Subject;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teachers from database
        $teachers = User::whereHas('role', function($q){
            $q->where('name', 'teacher');
        })->get();

        // Get rooms and subjects
        $rooms = Room::all();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $rooms->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('No teachers, rooms, or subjects found. Run Users, Rooms, and Subjects seeders first.');
            return;
        }

        // Allowed day patterns
        $dayPatterns = ['MWF', 'TTH', 'S'];

        // Map for semester ENUM values
        $semesterMap = [
            '1st Semester' => '1st',
            '2nd Semester' => '2nd',
            'Summer' => 'summer',
            '1st' => '1st',
            '2nd' => '2nd',
            'summer' => 'summer',
        ];

        // Generate 20 sample schedules
        for ($i = 1; $i <= 20; $i++) {
            $teacher = $teachers->random();
            $room = $rooms->random();
            $subject = $subjects->random();

            // Random start time between 7:00 - 16:00
            $startHour = rand(7, 16);
            $startMinute = rand(0, 1) ? '00' : '30';
            $startTime = Carbon::createFromTime($startHour, $startMinute, 0);
            $endTime = (clone $startTime)->addHour(); // 1-hour class

            // Normalize semester
            $semester = $semesterMap[$subject->semester] ?? '1st';

            // Class type
            $type = rand(0, 1) ? 'lecture' : 'lab';

            // Day pattern
            $day = $dayPatterns[array_rand($dayPatterns)];

            // Ensure room lat/lng exist (default 0 if null)
            $lat = $room->latitude ?? 0;
            $lng = $room->longitude ?? 0;

            Schedule::updateOrCreate(
                ['edp_code' => 'EDP'.$i],
                [
                    'user_id' => $teacher->id,
                    'room_id' => $room->id,
                    'subject_id' => $subject->id,
                    'edp_code' => 'EDP'.$i,
                    'units' => $subject->units ?? 3,
                    'type' => $type,
                    'day_of_week' => $day,
                    'starts_at' => $startTime->format('H:i:s'),
                    'ends_at' => $endTime->format('H:i:s'),
                    'school_year' => $subject->school_year ?? '2025-2026',
                    'semester' => $semester,
                    'start_date' => now()->startOfMonth()->toDateString(),
                    'end_date' => now()->endOfMonth()->toDateString(),
                    'room_lat' => $lat,
                    'room_lng' => $lng,
                ]
            );
        }

        $this->command->info('ScheduleSeeder completed successfully.');
    }
}
