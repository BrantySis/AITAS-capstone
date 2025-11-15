<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherAttendanceExport;

class AttendanceController extends Controller
{
    /**
     * Show today's schedules and current schedule for attendance
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Manila');

        // Get today's schedules for this teacher
        $todaySchedules = Schedule::where('user_id', $user->id)
            ->whereDate('starts_at', $now->toDateString())
            ->with(['room', 'subject'])
            ->orderBy('starts_at')
            ->get();

        $currentSchedule = null;

        foreach ($todaySchedules as $schedule) {

            // Skip broken schedules (missing room or subject)
            if (!$schedule->room || !$schedule->subject) continue;

            $scheduleStart = Carbon::parse($schedule->starts_at)->setTimezone('Asia/Manila');
            $scheduleEnd   = Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

            $attendance = Attendance::where('schedule_id', $schedule->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            // Cutoffs
            $lateCutoff = $scheduleStart->copy()->addMinutes(15);
            $missedDeadline = $scheduleStart->copy()->addMinutes(30);

            // Determine status
            if ($attendance) {
                $status = $attendance->status;
            } else {
                if ($now->lt($scheduleStart)) $status = 'Upcoming';
                elseif ($now->between($scheduleStart, $lateCutoff)) $status = 'Ongoing';
                elseif ($now->between($lateCutoff, $missedDeadline)) $status = 'Late';
                elseif ($now->between($missedDeadline, $scheduleEnd)) $status = 'Ongoing';
                else $status = 'Missed';
            }

            $schedule->status = $status;
            $schedule->attendance = $attendance;

            if (!$currentSchedule && in_array($status, ['Ongoing', 'Attending', 'Late', 'Upcoming'])) {
                $currentSchedule = $schedule;
            }
        }

        return view('teacher.teacher-attendance', [
            'currentSchedule' => $currentSchedule,
            'todaySchedules' => $todaySchedules,
            'fastapiUrl' => env('FASTAPI_URL', 'https://aitas-capstone.test:8001'),
        ]);
    }

    /**
     * Store/check-in or check-out attendance
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'checkout' => 'nullable|in:0,1',
            'attendance_id' => 'nullable|exists:attendances,id',
            'auto_missed' => 'nullable|in:1',
        ]);

        $userId = $request->user_id;
        $scheduleId = $request->schedule_id;
        $isCheckout = $request->checkout == "1";
        $attendanceId = $request->attendance_id;
        $isAutoMissed = $request->auto_missed == "1";
        $now = now('Asia/Manila');

        // Get schedule with relations
        $schedule = Schedule::with(['room', 'subject'])->find($scheduleId);

        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => '❌ Schedule not found.']);
        }

        // Ensure room and subject exist
        $room = $schedule->room;
        $subject = $schedule->subject;
        if (!$room || !$subject) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Schedule room or subject not properly set.'
            ]);
        }

        // Auto-Missed Logic
        if ($isAutoMissed) {
            $attendance = Attendance::firstOrCreate(
                ['schedule_id' => $schedule->id, 'user_id' => $userId],
                ['status' => 'Missed']
            );

            if ($attendance->status !== 'Missed') {
                $attendance->update(['status' => 'Missed']);
            }

            TeacherNotification::create([
                'type' => 'attendance',
                'title' => 'Missed Class',
                'message' => "You were automatically marked as MISSED for {$subject->subject_name} at {$room->room_code}.",
                'created_by' => $userId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Attendance automatically marked as MISSED.'
            ]);
        }

        // GPS validation
        $isValidLocation = $this->isWithinRadius(
            $request->latitude,
            $request->longitude,
            $room->latitude ?? 0,
            $room->longitude ?? 0,
            5
        );

        // CHECK-IN
        if (!$isCheckout) {
            $attendance = Attendance::firstOrNew([
                'user_id' => $userId,
                'schedule_id' => $scheduleId
            ]);

            if ($attendance->exists && $attendance->time_in && !$attendance->time_out) {
                return response()->json(['status' => 'error', 'message' => '❌ Already checked in.']);
            }

            $allowedCheckInTime = Carbon::parse($schedule->starts_at)->subMinutes(15);
            if ($now->lt($allowedCheckInTime)) {
                return response()->json(['status' => 'error', 'message' => '⏳ Check-in not allowed yet.']);
            }

            $lateCutoff = Carbon::parse($schedule->starts_at)->addMinutes(15);
            $missedCutoff = Carbon::parse($schedule->starts_at)->addMinutes(30);

            if ($now->gt($missedCutoff)) {
                $attendance->status = 'Missed';
                $attendance->save();
                return response()->json(['status' => 'error', 'message' => '❌ Deadline passed. Marked as MISSED.']);
            }

            $statusToSet = $now->lte($lateCutoff) ? 'Attending' : 'Late';

            $attendance->fill([
                'time_in' => $now,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_valid' => $isValidLocation,
                'status' => $statusToSet,
            ]);
            $attendance->save();

            TeacherNotification::create([
                'type' => 'attendance',
                'title' => 'Check-in ' . $statusToSet,
                'message' => "You have successfully checked in for {$subject->subject_name} at {$room->room_code}. Status: $statusToSet",
                'created_by' => $userId,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $statusToSet === 'Attending' ? '✅ Check-in successful.' : '⚠️ Check-in successful, but recorded as LATE.',
                'attendance_id' => $attendance->id,
                'check_in_status' => $statusToSet
            ]);
        }

        // CHECK-OUT
        $attendance = Attendance::find($attendanceId);
        if (!$attendance || $attendance->time_out) {
            return response()->json(['status' => 'error', 'message' => '❌ Attendance record not found or already checked out.']);
        }

        $finalStatus = match ($attendance->status) {
            'Late' => 'Late',
            'Attending', 'Ongoing' => 'Attended',
            'Missed' => 'Missed',
            default => 'Attended',
        };

        $attendance->update([
            'time_out' => $now,
            'status' => $finalStatus,
            'latitude_out' => $request->latitude,
            'longitude_out' => $request->longitude,
        ]);

        TeacherNotification::create([
            'type' => 'attendance',
            'title' => 'Check-out Completed',
            'message' => "You checked out from {$subject->subject_name} at {$room->room_code}. Final status: $finalStatus",
            'created_by' => $userId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ Check-out successful. You are now marked as ' . $finalStatus . '.',
        ]);
    }

    /**
     * Attendance history with filters
     */
    public function history(Request $request)
    {
        $teacher = Auth::user();

        $search = $request->input('search');
        $status = $request->input('status');
        $subject = $request->input('subject');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Attendance::with(['schedule.subject', 'schedule.room'])
            ->where('user_id', $teacher->id)
            ->whereIn('status', ['Attended', 'Late', 'Missed'])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('schedule.subject', fn($subQ) => $subQ->where('subject_name', 'like', "%$search%"))
                  ->orWhereHas('schedule.room', fn($roomQ) => $roomQ->where('room_code', 'like', "%$search%"))
                  ->orWhere('status', 'like', "%$search%");
            });
        }

        if ($status) $query->where('status', $status);
        if ($subject) $query->whereHas('schedule.subject', fn($q) => $q->where('subject_name', $subject));
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $attendanceHistory = $query->get()
            ->groupBy(fn($attendance) => Carbon::parse($attendance->created_at)->toDateString());

        $subjects = Schedule::where('user_id', $teacher->id)
            ->with('subject')
            ->get()
            ->pluck('subject.subject_name')
            ->unique()
            ->filter()
            ->values();

        return view('teacher.teacher-history', [
            'attendanceHistory' => $attendanceHistory,
            'subjects' => $subjects
        ]);
    }

    /**
     * Export attendance to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'start_date', 'end_date', 'subject']);
        $filename = 'attendance_export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new TeacherAttendanceExport($filters), $filename);
    }

    /**
     * Validate location within radius (meters)
     */
    private function isWithinRadius($lat1, $lon1, $lat2, $lon2, $radius = 5)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radius;
    }
}
