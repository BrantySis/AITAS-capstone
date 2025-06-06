<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
   public function index()
{
    $teacher = auth()->user();
    $now = now();

    $schedules = Schedule::with('room') // this is critical
        ->where('user_id', $teacher->id)
        ->where('starts_at', '>=', $now)
        ->orderBy('starts_at')
        ->get();

    return view('teacher.index', compact('schedules'));
}

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        Attendance::create([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'check_in_time' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_valid' => true // Temporary, can be updated based on radius logic
        ]);

        return back()->with('success', 'Attendance recorded.');
    }
}
