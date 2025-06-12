<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();
        $now = now();
    
        // Filter only today's schedules that haven't ended yet
        $schedules = Schedule::with('room')
            ->where('user_id', $teacher->id)
            ->whereDate('starts_at', $now->toDateString()) // Only today
            ->where('ends_at', '>=', $now) // Not yet ended
            ->orderBy('starts_at')
            ->get();
    
        // Get IDs of schedules already checked in
        $checkedInSchedules = Attendance::where('user_id', $teacher->id)
            ->whereDate('time_in', $now->toDateString())
            ->pluck('schedule_id')
            ->toArray();
    
        return view('teacher.index', compact('schedules', 'checkedInSchedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
    
        $scheduleId = $request->schedule_id;
        $userId = $request->user_id;
    
        // Prevent duplicate check-ins
        $existing = Attendance::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->whereDate('created_at', now()->toDateString())
            ->first();
    
        if ($existing) {
            return back()->with('error', '❌ You have already checked in for this schedule today.');
        }
    
        $schedule = Schedule::with('room')->findOrFail($scheduleId);
        $room = $schedule->room;
    
        if (!$room || !$room->latitude || !$room->longitude) {
            return back()->with('error', '❌ Room location is not properly set. Please contact the admin.');
        }
    
        $isValid = $this->isWithinRadius(
            $request->latitude,
            $request->longitude,
            $room->latitude,
            $room->longitude,
            50 // meters
        );
    
        if (!$isValid) {
            return back()->with('error', '❌ You are not within the allowed room location. Check-in denied.');
        }
    
        Attendance::create([
            'user_id' => $userId,
            'schedule_id' => $scheduleId,
            'time_in' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_valid' => true,
        ]);
    
        return back()->with('success', '✅ Check-in successful and within location.');
    }

    private function isWithinRadius($lat1, $lon1, $lat2, $lon2, $radius = 50)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radius;
    }
}
