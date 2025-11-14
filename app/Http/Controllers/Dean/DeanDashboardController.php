<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;

class DeanDashboardController extends Controller
{
    /**
     * Display the dean dashboard.
     */
    public function index()
    {
        // Count total teachers (users with role_id = 2)
        $totalTeachers = User::where('role_id', 2)->count();

        // Count all schedules (adjust filter as needed)
        $activeSchedules = Schedule::count();

        // Return the dean dashboard view
        return view('dean.dean-dashboard', [
            'totalTeachers' => $totalTeachers,
            'activeSchedules' => $activeSchedules,
        ]);
    }
}
