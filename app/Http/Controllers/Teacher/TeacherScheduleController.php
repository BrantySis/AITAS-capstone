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
        $now = Carbon::now();

        // Get all schedules for this teacher
        $schedules = Schedule::with(['room', 'subject'])
            ->where('user_id', $teacherId)
            ->orderBy('starts_at', 'asc')
            ->get();

        // Current classes (in-progress)
        $currentClasses = $schedules->filter(function($s) use ($now) {
            $start = Carbon::parse($s->starts_at);
            $end   = Carbon::parse($s->ends_at);
            return $now->between($start, $end);
        });

        // Recent classes (ended in the past 7 days)
        $recentClasses = $schedules->filter(function($s) use ($now) {
            $end = Carbon::parse($s->ends_at);
            return $end->lt($now) && $end->gt($now->copy()->subDays(7));
        });

        return view('teacher.teacher-schedules', compact(
            'schedules',
            'currentClasses',
            'recentClasses'
        ));
    }
}
