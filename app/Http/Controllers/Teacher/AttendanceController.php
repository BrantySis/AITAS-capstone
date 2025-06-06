<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;


class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get today's day (e.g. Monday)
        $today = now()->format('l'); // 'Monday', 'Tuesday', etc.

        // Optional: filter to today's schedules
        $schedules = Schedule::with('room')
            ->where('user_id', $user->id)
           // ->where('day', $today) // optional: show only today's schedules
            ->orderBy('time_start')
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
            'is_valid' => true // This can be updated later using radius logic
        ]);

        return back()->with('success', 'Attendance recorded.');
    }
}
