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
        $schedules = Schedule::with(['room', 'subject'])
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

        $userId = $request->user_id;
        $scheduleId = $request->schedule_id;
        $now = now('Asia/Manila'); // Use configured timezone

        // Check for duplicate check-in
        $existing = Attendance::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->whereDate('created_at', $now->toDateString())
            ->first();

        if ($existing) {
            return back()->with('error', '❌ You have already checked in for this schedule today.');
        }

        $schedule = Schedule::with('room')->findOrFail($scheduleId);
        $scheduleStart = \Carbon\Carbon::parse($schedule->starts_at)->setTimezone('Asia/Manila');
        $scheduleEnd = \Carbon\Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

        // Allow check-in 15 minutes before schedule starts
        $allowedCheckInTime = $scheduleStart->copy()->subMinutes(15);

        // Prevent early check-in
        if ($now->lt($allowedCheckInTime)) {
            return back()->with('error', '⏳ Check-in not allowed yet. You can check in 15 minutes before the schedule starts.');
        }

        // Prevent late check-in (after schedule ends)
        if ($now->gt($scheduleEnd)) {
            return back()->with('error', '⏰ Check-in period has ended for this schedule.');
        }

        $room = $schedule->room;

        if (!$room || !$room->latitude || !$room->longitude) {
            return back()->with('error', '❌ Room location is not properly set. Please contact the admin.');
        }

        // Validate location (within 5 meters)
        $isValid = $this->isWithinRadius(
            $request->latitude,
            $request->longitude,
            $room->latitude,
            $room->longitude,
            5
        );

        if (!$isValid) {
            return back()->with('error', '❌ You are not within the allowed 5-meter range of the room. Check-in denied.');
        }

        Attendance::create([
            'user_id' => $userId,
            'schedule_id' => $scheduleId,
            'time_in' => $now,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_valid' => true,
        ]);

        return back()->with('success', '✅ Check-in successful and within location.');
    }

    public function timeout(Request $request)
{
    $request->validate([
        'schedule_id' => 'required|exists:schedules,id',
    ]);

    $attendance = Attendance::where('user_id', auth()->id())
        ->where('schedule_id', $request->schedule_id)
        ->whereDate('created_at', now()->toDateString())
        ->first();

    if (!$attendance) {
        return back()->with('error', '❌ You have not checked in yet for this schedule.');
    }

    if ($attendance->time_out) {
        return back()->with('error', '❌ You have already timed out for this schedule.');
    }

    $now = now();
    $schedule = Schedule::findOrFail($request->schedule_id);

    if ($now->gt($schedule->ends_at)) {
        // Too late to time out
        $attendance->update([
            'time_out' => null, // or leave as is
            'is_valid' => false // or introduce a `missed_timeout` flag
        ]);

        return back()->with('error', '❌ Time out failed. You exceeded the allowed time.');
    }

    $attendance->update([
        'time_out' => $now,
    ]);

    return back()->with('success', '✅ You have successfully timed out.');
}

    private function isWithinRadius($lat1, $lon1, $lat2, $lon2, $radius = 5)
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

