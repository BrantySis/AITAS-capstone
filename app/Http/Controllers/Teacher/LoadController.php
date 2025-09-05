<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;


class LoadController extends Controller
{
                    public function index(Request $request)
                {
                    $teacherId = Auth::id();
                    $now = now();
                    $filter = $request->query('filter');

                    // Get all attended schedule IDs for the teacher today or in the past
                    $attendedScheduleIds = Attendance::where('user_id', $teacherId)
                        ->pluck('schedule_id')
                        ->toArray();

                    $query = Schedule::with(['room', 'subject'])->where('user_id', $teacherId);

                    if ($filter === 'in-progress') {
                        $query->where('starts_at', '<=', $now)->where('ends_at', '>=', $now);
                    } elseif ($filter === 'upcoming') {
                        $query->where('starts_at', '>', $now);
                    } elseif ($filter === 'past') {
                        $query->where('ends_at', '<', $now);
                    }

                    $schedules = $query->orderBy('starts_at')->get();

                    return view('teacher.load', compact('schedules', 'filter', 'attendedScheduleIds'));
                }
}
