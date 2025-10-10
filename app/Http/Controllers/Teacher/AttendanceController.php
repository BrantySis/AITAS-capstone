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
        $teacher = auth::user();
        $now = now('Asia/Manila');

        $schedules = Schedule::with(['room', 'subject'])
            ->where('user_id', $teacher->id)
            ->whereDate('starts_at', $now->toDateString())
            ->orderBy('starts_at')
            ->get();

        $checkedInSchedules = Attendance::where('user_id', $teacher->id)
            ->whereDate('time_in', $now->toDateString())
            ->pluck('schedule_id')
            ->toArray();

        return view('teacher.index', [
            'schedules' => $schedules,
            'checkedInSchedules' => $checkedInSchedules,
            'fastapiUrl' => env('FASTAPI_URL', 'http://127.0.0.1:8001'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'checkout' => 'nullable|in:0,1', // 0 = checkin, 1 = checkout
        ]);

        $userId = $request->user_id;
        $scheduleId = $request->schedule_id;
        $isCheckout = $request->checkout == "1";
        $now = now('Asia/Manila');

        $attendance = Attendance::where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->whereDate('time_in', $now->toDateString())
            ->first();

        $schedule = Schedule::with('room')->findOrFail($scheduleId);
        $room = $schedule->room;

        if (!$room || !$room->latitude || !$room->longitude) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Room location is not properly set.'
            ]);
        }

        // Location check within 5 meters
        $isValidLocation = $this->isWithinRadius(
            $request->latitude,
            $request->longitude,
            $room->latitude,
            $room->longitude,
            5
        );

        if (!$isCheckout) {
            // Check-in
            if ($attendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => '❌ You have already checked in.'
                ]);
            }

            $allowedCheckInTime = \Carbon\Carbon::parse($schedule->starts_at, 'Asia/Manila')->subMinutes(15);

            if ($now->lt($allowedCheckInTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => '⏳ Check-in not allowed yet. You can check in 15 minutes before the schedule starts.'
                ]);
            }

            $attendance = Attendance::create([
                'user_id' => $userId,
                'schedule_id' => $scheduleId,
                'time_in' => $now,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_valid' => $isValidLocation,
                'status' => $isValidLocation ? 'Attending' : 'Attending', // pending attendance
            ]);

            return response()->json([
                'status' => 'success',
                'message' => '✅ Check-in successful' . ($isValidLocation ? '' : ' (Location not accurate)') . '.'
            ]);
        } else {
            // Checkout
            if (!$attendance) {
                return response()->json([
                    'status' => 'error',
                    'message' => '❌ You have not checked in yet.'
                ]);
            }

            if ($attendance->time_out) {
                return response()->json([
                    'status' => 'error',
                    'message' => '❌ You have already checked out.'
                ]);
            }

            $attendance->update([
                'time_out' => $now,
                'status' => 'Attended',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => '✅ Check-out successful. You are now marked as Attended.'
            ]);
        }
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
