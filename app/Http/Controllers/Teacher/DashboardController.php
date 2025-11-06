<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Manila');

        // 1. Get today's schedules
        $todaySchedules = Schedule::where('user_id', $user->id)
            ->whereDate('starts_at', $now->toDateString())
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $currentSchedule = null;

        foreach ($todaySchedules as $schedule) {
            $scheduleStart = Carbon::parse($schedule->starts_at)->setTimezone('Asia/Manila');
            $scheduleEnd = Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

            $attendance = Attendance::where('schedule_id', $schedule->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            // Define cutoffs
            $lateCutoff = $scheduleStart->copy()->addMinutes(15);
            $missedDeadline = $scheduleStart->copy()->addMinutes(30);

            // Default status
            $status = 'Unknown';

            if ($attendance) {
                $status = $attendance->status;
            } elseif ($now->lt($scheduleStart)) {
                $status = 'Upcoming';
            } elseif ($now->between($scheduleStart, $lateCutoff)) {
                $status = 'Ongoing'; // on-time or within grace
            } elseif ($now->between($lateCutoff, $missedDeadline)) {
                $status = 'Late';
            } elseif ($now->between($missedDeadline, $scheduleEnd)) {
                $status = 'Ongoing'; // still running even if past late window
            } else {
                $status = 'Missed';
            }

            $schedule->status = $status;
            $schedule->attendance = $attendance;

            // Assign current schedule once
            if (!$currentSchedule && in_array($status, ['Ongoing', 'Attending', 'Late', 'Upcoming'])) {
                $currentSchedule = $schedule;
            }
        }

        return view('teacher.teacher-dashboard', [
            'currentSchedule' => $currentSchedule,
            'fastapiUrl' => env('FASTAPI_URL', 'https://aitas-capstone.test:8001'),
        ]);
    }
}
