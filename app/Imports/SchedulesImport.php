<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Subject;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SchedulesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            $teacher = User::where('name', $row['teacher_name'] ?? '')->first();
            $room = Room::where('room_code', $row['room_code'] ?? '')->first();
            $subject = Subject::where('subject_code', $row['subject_code'] ?? '')->first();

            if (!$teacher || !$room || !$subject) {
                return null; // skip invalid rows
            }

            return new Schedule([
                'user_id' => $teacher->id,
                'room_id' => $room->id,
                'subject_id' => $subject->id,
                'edp_code' => $row['edp_code'] ?? '',
                'units' => $subject->units ?? 3,
                'type' => $row['type'] ?? 'lecture',
                'day_of_week' => $row['day_of_week'] ?? '',
                'start_date' => isset($row['start_date']) ? Carbon::parse($row['start_date']) : null,
                'end_date' => isset($row['end_date']) ? Carbon::parse($row['end_date']) : null,
                'semester' => $row['semester'] ?? '',
                'school_year' => $row['school_year'] ?? '',
                'starts_at' => $row['starts_at'] ?? '',
                'ends_at' => $row['ends_at'] ?? '',
            ]);
        } catch (\Exception $e) {
            \Log::error('Import row failed: ' . $e->getMessage());
            return null;
        }
    }
}
