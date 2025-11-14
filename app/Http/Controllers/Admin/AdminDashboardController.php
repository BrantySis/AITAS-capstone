<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ============= PRIMARY METRICS =============
        
        // Total Teachers (role_id = 2)
        $totalTeachers = User::where('role_id', 2)->count();

        // Total Students (role_id = 3 — adjust if your student role is different)
        $totalDeans = User::where('role_id', 3)->count();

        // Active Subjects (is_active = 1)
        $activeSubjects = Subject::where('is_active', 1)->count();

        // Active Schedules — using soft-delete + date range
        $activeSchedules = Schedule::whereNull('deleted_at')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->count();

        // Active Rooms
        $activeRooms = Room::where('is_active', 1)->count();


        // ============= DAILY MONITORING =============

        // Today’s Day (e.g., Monday)
        $today = now()->format('l');

        // Today's Classes (based on day_of_week)
        $todaysClasses = Schedule::where('day_of_week', $today)
            ->whereNull('deleted_at')
            ->count();

        // Total Attendance Today
        $attendanceToday = Attendance::whereDate('created_at', today())
            ->count();

        // Late Today
        $lateToday = Attendance::where('status', 'late')
            ->whereDate('created_at', today())
            ->count();

        // Absent Today
        $absentToday = Attendance::where('status', 'absent')
            ->whereDate('created_at', today())
            ->count();


        // ============= RETURN VIEW =============
        
        return view('admin.admin-dashboard', [
            // PRIMARY
            'totalTeachers'     => $totalTeachers,
            'totalDeans'     => $totalDeans,
            'activeSubjects'    => $activeSubjects,
            'activeSchedules'   => $activeSchedules,
            'activeRooms'       => $activeRooms,

            // DAILY MONITORING
            'todaysClasses'     => $todaysClasses,
            'attendanceToday'   => $attendanceToday,
            'lateToday'         => $lateToday,
            'absentToday'       => $absentToday,
        ]);
    }
}
