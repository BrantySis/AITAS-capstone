<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Schedule;

class TeacherScheduleController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();
        
        // 1. Fetch ALL schedules for the teacher
        // The view's JavaScript will handle filtering by day-of-week and date range.
        $schedules = Schedule::with(['room', 'subject'])
            ->where('user_id', $teacherId)
            ->orderBy('starts_at', 'asc')
            ->get();
        
        // 2. Remove the heavy filtering logic here:
        //    - The filter based on $todayName is now handled in the JavaScript.
        //    - The $currentClasses and $recentClasses are no longer needed
        //      because the frontend is designed to display schedules for a selected date.

        // Pass the full collection of schedules to the view.
        return view('teacher.teacher-schedules', [
            'schedules' => $schedules,
            // Pass empty collections/arrays for these if the view still expects them,
            // otherwise, you should remove them from the 'compact' list in a cleaner setup.
            'currentClasses' => collect(), 
            'recentClasses' => collect(),
        ]);
    }
}