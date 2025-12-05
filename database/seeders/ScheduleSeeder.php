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
    public function run(): void
    {
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->take(10)->get();
        $rooms = Room::all();
        $ccsSubjects = Subject::where('department', 'College of Computer Studies')->get();
        $nursingSubjects = Subject::where('department', 'Nursing')->get();

        if ($teachers->isEmpty() || $rooms->isEmpty() || $ccsSubjects->isEmpty() || $nursingSubjects->isEmpty()) {
            $this->command->warn('Teachers, rooms, or subjects missing. Run seeders first.');
            return;
        }

        $dayPatterns = [
            ['Monday','Wednesday','Friday'],      // MWF
            ['Tuesday','Thursday'],               // TTH
            ['Saturday'],                         // Sat
            ['Sunday'],                           // Sun
        ];
        $classTypes = ['lecture', 'lab'];
        $usedEdpCodes = [];

        foreach ($teachers as $teacher) {
            // Assign 1 subject from each department
            $assignedSubjects = [
                $ccsSubjects->random(),
                $nursingSubjects->random()
            ];

            foreach ($assignedSubjects as $subject) {
                $room = $rooms->random();

                $startHour = rand(7, 16);
                $startMinute = rand(0, 1) ? '00' : '30';
                $startTime = Carbon::createFromTime($startHour, $startMinute, 0);
                $endTime = (clone $startTime)->addHour();

                $dayPattern = $dayPatterns[array_rand($dayPatterns)];

                // Unique EDP code
                do {
                    $edpCode = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                } while (in_array($edpCode, $usedEdpCodes));
                $usedEdpCodes[] = $edpCode;

                Schedule::updateOrCreate(
                    ['edp_code' => $edpCode],
                    [
                        'user_id'     => $teacher->id,
                        'room_id'     => $room->id,
                        'subject_id'  => $subject->id,
                        'edp_code'    => $edpCode,
                        'units'       => $subject->units ?? 3,
                        'type'        => $classTypes[array_rand($classTypes)],
                        'day_of_week' => $dayPattern, // store as array
                        'starts_at'   => $startTime->format('H:i:s'),
                        'ends_at'     => $endTime->format('H:i:s'),
                        'school_year' => $subject->school_year ?? '2025-2026',
                        'semester'    => $subject->semester ?? '1st', // use text format
                        'start_date'  => now()->startOfMonth()->toDateString(),
                        'end_date'    => now()->endOfMonth()->toDateString(),
                        'room_lat'    => $room->latitude ?? 0.0,
                        'room_lng'    => $room->longitude ?? 0.0,
                    ]
                );
            }
        }

        $this->command->info('ScheduleSeeder completed: 10 teachers, 1 CCS + 1 Nursing schedule each.');
    }
}
