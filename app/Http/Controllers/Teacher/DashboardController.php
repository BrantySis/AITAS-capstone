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
    public function index()
    {
        $teacherId = Auth::id();
        $today = Carbon::today();

        // Classes scheduled today
        $schedulesToday = Schedule::where('user_id', $teacherId)
            ->whereDate('starts_at', $today)
            ->get();
        $classesToday = $schedulesToday->count();

        // Attendance records for today
        $attendancesToday = Attendance::where('user_id', $teacherId)
            ->whereDate('created_at', $today)
            ->get();

        // Completed attendance (present or late)
        $completedAttendance = $attendancesToday->whereIn('status', ['Present', 'Late'])->count();

        // Late today (time_in > schedule start time)
        $lateToday = Attendance::join('schedules', 'attendances.schedule_id', '=', 'schedules.id')
            ->where('attendances.user_id', $teacherId)
            ->whereDate('attendances.created_at', $today)
            ->whereColumn('attendances.time_in', '>', 'schedules.starts_at')
            ->count();

        // Missed classes (scheduled but no attendance)
        $attendedScheduleIds = $attendancesToday->pluck('schedule_id')->toArray();
        $missedClasses = $schedulesToday->whereNotIn('id', $attendedScheduleIds)->count();

        // Donut chart values
        $present = $completedAttendance - $lateToday; // marked Present
        $absent = $missedClasses;

        // -----------------------------
        // Monthly attendance chart data
        // -----------------------------
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Group attendance by date
        $monthlyAttendance = Attendance::where('user_id', $teacherId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            });

        $attendanceThisMonth = [];
        $period = new \DatePeriod(
            $startOfMonth,
            new \DateInterval('P1D'),
            $endOfMonth->addDay() // include last day
        );

        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $attendanceThisMonth[$day] = $monthlyAttendance->has($day) ? $monthlyAttendance[$day]->count() : 0;
        }

        return view('teacher.teacher-dashboard', [
            'classesToday' => $classesToday,
            'completedAttendance' => $completedAttendance,
            'lateToday' => $lateToday,
            'missedClasses' => $missedClasses,
            'present' => $present,
            'absent' => $absent,
            'attendanceThisMonth' => $attendanceThisMonth,
        ]);
    }
}
