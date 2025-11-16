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
    protected int $rowCount = 0; // Track imported rows

    /**
     * Helper to safely cast a value to string and trim
     */
    protected function asString($value)
    {
        if ($value === null) return null;
        return trim((string)$value);
    }

    /**
     * Helper to parse a date safely
     */
    protected function parseDate($value)
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        try {
            $teacherName = $this->asString($row['teacher_name'] ?? null);
            $roomCode = $this->asString($row['room_code'] ?? null);
            $subjectCode = $this->asString($row['subject_code'] ?? null);

            // Skip row if mandatory relations are missing
            if (!$teacherName || !$roomCode || !$subjectCode) {
                return null;
            }

            $teacher = User::where('name', $teacherName)->first();
            $room = Room::where('room_code', $roomCode)->first();
            $subject = Subject::where('subject_code', $subjectCode)->first();

            if (!$teacher || !$room || !$subject) {
                return null;
            }

            $schedule = new Schedule([
                'user_id'      => $teacher->id,
                'room_id'      => $room->id,
                'subject_id'   => $subject->id,
                'edp_code'     => $this->asString($row['edp_code'] ?? ''),
                'units'        => $subject->units ?? 3,
                'type'         => $this->asString($row['type'] ?? 'lecture'),
                'day_of_week'  => $this->asString($row['day_of_week'] ?? ''),
                'start_date'   => $this->parseDate($row['start_date'] ?? null),
                'end_date'     => $this->parseDate($row['end_date'] ?? null),
                'semester'     => $this->asString($row['semester'] ?? ''),
                'school_year'  => $this->asString($row['school_year'] ?? ''),
                'starts_at'    => $this->asString($row['starts_at'] ?? ''),
                'ends_at'      => $this->asString($row['ends_at'] ?? ''),
            ]);

            $this->rowCount++;

            return $schedule;
        } catch (\Exception $e) {
            \Log::error('Import row failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the total number of successfully imported rows.
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}
