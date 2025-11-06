<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;

class UpdateAttendanceStatus extends Command
{
    protected $signature = 'attendance:update-status';
    protected $description = 'Automatically update attendance statuses after class ends';

    public function handle()
    {
        $now = Carbon::now('Asia/Manila');

        // Load attendances with their schedules
        $attendances = Attendance::with('schedule')->get();

        foreach ($attendances as $attendance) {
            $schedule = $attendance->schedule;
            if (!$schedule) continue;

            $endTime = Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

            // Case 1: Schedule ended and user did not checkout → Missed
            if (is_null($attendance->time_out) && $now->greaterThan($endTime)) {
                $attendance->status = 'Missed';
                $attendance->save();
                continue;
            }

            // // Case 2: User checked out before schedule end → Attended
            // if ($attendance->time_out && $attendance->status !== 'Missed') {
            //     $attendance->status = 'Attended';
            //     $attendance->save();
            // }
        }

        $this->info('Attendance statuses updated successfully at ' . $now);
    }
}
