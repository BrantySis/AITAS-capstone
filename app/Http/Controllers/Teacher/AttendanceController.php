<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherAttendanceExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $now = now('Asia/Manila');

        $schedules = Schedule::with(['room', 'subject', 'attendances' => function ($q) use ($teacher, $now) {
            $q->where('user_id', $teacher->id)
              ->whereDate('time_in', $now->toDateString());
        }])
        ->where('user_id', $teacher->id)
        ->whereDate('starts_at', $now->toDateString())
        ->orderBy('starts_at')
        ->get();

        return view('teacher.teacher-dashboard', [
            'schedules' => $schedules,
            'fastapiUrl' => env('FASTAPI_URL', 'https://aitas-capstone.test:8001'),
        ]);
    }

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

        $schedule = Schedule::with('room')->findOrFail($scheduleId);
        $room = $schedule->room;

        $scheduleStart = Carbon::parse($schedule->starts_at)->setTimezone('Asia/Manila');
        $scheduleEnd   = Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

        // ✅ Check if room has coordinates
        if (!$room || !$room->latitude || !$room->longitude) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Room location is not properly set.'
            ]);
        }

        // ✅ AUTO-MISSED (after 30 minutes)
        if ($isAutoMissed) {
            $attendance = Attendance::firstOrCreate(
                ['schedule_id' => $schedule->id, 'user_id' => $userId],
                ['status' => 'Missed']
            );

            if ($attendance->status !== 'Missed') {
                $attendance->update(['status' => 'Missed']);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Attendance automatically marked as MISSED: The 30-minute check-in deadline has passed.'
            ]);
        }

        // ✅ Validate GPS (within 5 meters)
        $isValidLocation = $this->isWithinRadius(
            $request->latitude,
            $request->longitude,
            $room->latitude,
            $room->longitude,
            5
        );

        // ==========================
        // 🚀 CHECK-IN LOGIC
        // ==========================
        if (!$isCheckout) {
            $attendance = Attendance::where('user_id', $userId)
                ->where('schedule_id', $scheduleId)
                ->first();

            // ❌ Already checked in
            if ($attendance && $attendance->time_in && !$attendance->time_out) {
                return response()->json([
                    'status' => 'error',
                    'message' => '❌ You have already checked in.'
                ]);
            }

            // 🕒 Prevent early check-in (15 min before start)
            $allowedCheckInTime = $scheduleStart->copy()->subMinutes(15);
            if ($now->lt($allowedCheckInTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => '⏳ Check-in not allowed yet. You can check in 15 minutes before the schedule starts.'
                ]);
            }

            // ✅ Late / Missed Handling
            $lateCutoff = $scheduleStart->copy()->addMinutes(15);
            $missedCutoff = $scheduleStart->copy()->addMinutes(30);

            if ($now->gt($missedCutoff)) {
                Attendance::firstOrCreate(
                    ['user_id' => $userId, 'schedule_id' => $scheduleId],
                    ['status' => 'Missed']
                );

                return response()->json([
                    'status' => 'error',
                    'message' => '❌ Check-in failed. The 30-minute deadline has already passed. Marked as MISSED.'
                ]);
            }

            // ✅ Corrected time classification
            if ($now->lt($scheduleStart)) {
                // Early but allowed (within 15 minutes before start)
                $statusToSet = 'Attending';
                $message = '✅ Early check-in successful.';
            } elseif ($now->lte($lateCutoff)) {
                // On time or up to 15 minutes after start
                $statusToSet = 'Attending';
                $message = '✅ Check-in successful.';
            } else {
                // 15–30 minutes late
                $statusToSet = 'Late';
                $message = '⚠️ Check-in successful, but recorded as LATE.';
            }

            // ✅ Record attendance
            $attendance = Attendance::updateOrCreate(
                ['id' => $attendanceId ?? null],
                [
                    'user_id' => $userId,
                    'schedule_id' => $scheduleId,
                    'time_in' => $now,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'is_valid' => $isValidLocation,
                    'status' => $statusToSet,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'attendance_id' => $attendance->id,
                'check_in_status' => $statusToSet
            ]);
        }

        // ==========================
        // 🚀 CHECK-OUT LOGIC
        // ==========================
        if (!$attendanceId) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Attendance record not found for checkout.'
            ]);
        }

        $attendance = Attendance::find($attendanceId);

        if (!$attendance || $attendance->time_out) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ You have not checked in yet or already checked out.'
            ]);
        }

        // ✅ Preserve status correctly on checkout
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

        return response()->json([
            'status' => 'success',
            'message' => '✅ Check-out successful. You are now marked as ' . $finalStatus . '.',
        ]);
    }

    public function history(Request $request)
    {
        $teacher = Auth::user();

        // ✅ Filters
        $search = $request->input('search');
        $status = $request->input('status');
        $subject = $request->input('subject');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // ✅ Base query
        $query = Attendance::with(['schedule.subject', 'schedule.room'])
            ->where('user_id', $teacher->id)
            ->whereIn('status', ['Attended', 'Late', 'Missed'])
            ->orderByDesc('created_at');

        // ✅ Search filter (subject, room, or status)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('schedule.subject', function ($subQ) use ($search) {
                    $subQ->where('subject_name', 'like', "%{$search}%");
                })
                ->orWhereHas('schedule.room', function ($roomQ) use ($search) {
                    $roomQ->where('room_code', 'like', "%{$search}%");
                })
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // ✅ Status filter
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // ✅ Subject filter
        if (!empty($subject)) {
            $query->whereHas('schedule.subject', function ($q) use ($subject) {
                $q->where('subject_name', $subject);
            });
        }

        // ✅ Date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        // ✅ Fetch attendance records
        $attendanceHistory = $query->get()
            ->groupBy(function ($attendance) {
                return Carbon::parse($attendance->created_at)->toDateString();
            });

        // ✅ Fetch subjects for dropdown filter
        $subjects = \App\Models\Schedule::where('user_id', $teacher->id ?? Auth::id())
                    ->with('subject')
                    ->get()
                    ->pluck('subject.subject_name')
                    ->unique()
                    ->filter()
                    ->values();


        return view('teacher.teacher-history', [
            'attendanceHistory' => $attendanceHistory,
            'subjects' => $subjects,
        ]);
    }

    // public function export(Request $request)
    // {
    //     $teacherId = Auth::id();

    //     $query = Attendance::where('user_id', $teacherId)
    //         ->whereIn('status', ['Attended', 'Late', 'Missed'])
    //         ->with(['schedule.subject', 'schedule.room']);

    //     // Apply filters if any
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('schedule.subject', function ($subQuery) use ($search) {
    //                 $subQuery->where('subject_name', 'like', "%$search%");
    //             })->orWhereHas('schedule.room', function ($roomQuery) use ($search) {
    //                 $roomQuery->where('room_code', 'like', "%$search%");
    //             });
    //         });
    //     }

    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->filled('start_date') && $request->filled('end_date')) {
    //         $query->whereBetween('created_at', [
    //             Carbon::parse($request->start_date)->startOfDay(),
    //             Carbon::parse($request->end_date)->endOfDay(),
    //         ]);
    //     }

    //     if ($request->filled('subject')) {
    //         $query->whereHas('schedule.subject', function ($q) use ($request) {
    //             $q->where('subject_name', $request->subject);
    //         });
    //     }

    //     $attendances = $query->get();

    //     // ✅ Create a CSV response
    //     $response = new StreamedResponse(function() use ($attendances) {
    //         $handle = fopen('php://output', 'w');
    //         fputcsv($handle, ['Date', 'Subject', 'Room', 'Start Time', 'End Time', 'Status']);

    //         foreach ($attendances as $attendance) {
    //             fputcsv($handle, [
    //                 $attendance->created_at->format('Y-m-d'),
    //                 $attendance->schedule->subject->subject_name ?? 'N/A',
    //                 $attendance->schedule->room->room_code ?? 'N/A',
    //                 optional($attendance->schedule->starts_at)->format('g:i A') ?? '-',
    //                 optional($attendance->schedule->ends_at)->format('g:i A') ?? '-',
    //                 $attendance->status,
    //             ]);
    //         }

    //         fclose($handle);
    //     });

    //     $filename = 'attendance_export_' . now()->format('Ymd_His') . '.csv';

    //     $response->headers->set('Content-Type', 'text/csv');
    //     $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

    //     return $response;
    // }

    public function export(Request $request)
{
    $filters = $request->only(['search', 'status', 'start_date', 'end_date', 'subject']);
    $filename = 'attendance_export_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(new TeacherAttendanceExport($filters), $filename);
}

    // ======================================================
    // 📍 Helper: Validate location within radius (meters)
    // ======================================================
    private function isWithinRadius($lat1, $lon1, $lat2, $lon2, $radius = 5)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= $radius;
    }
}
