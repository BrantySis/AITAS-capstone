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

        // Get today's schedules
        $todaySchedules = Schedule::where('user_id', $user->id)
            ->whereDate('starts_at', $now->toDateString())
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $currentSchedule = null;

        foreach ($todaySchedules as $schedule) {
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
            $status = $attendance->status ?? 'Unknown';
            if (!$attendance) {
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

        $schedule = Schedule::with('room')->findOrFail($scheduleId);
        $room = $schedule->room;

        $scheduleStart = Carbon::parse($schedule->starts_at)->setTimezone('Asia/Manila');
        $scheduleEnd   = Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

        // Room coordinates check
        if (!$room || !$room->latitude || !$room->longitude) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Room location is not properly set.'
            ]);
        }

        // AUTO-MISSED
        if ($isAutoMissed) {
            $attendance = Attendance::firstOrCreate(
                ['schedule_id' => $schedule->id, 'user_id' => $userId],
                ['status' => 'Missed']
            );
            if ($attendance->status !== 'Missed') $attendance->update(['status' => 'Missed']);

            TeacherNotification::create([
            'type' => 'attendance',
            'title' => 'Missed Class',
            'message' => "You were automatically marked as MISSED for {$schedule->subject->subject_name} at {$schedule->room->room_code}.",
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
            $room->latitude,
            $room->longitude,
            5
        );

        // CHECK-IN LOGIC
        if (!$isCheckout) {
            $attendance = Attendance::where('user_id', $userId)
                ->where('schedule_id', $scheduleId)
                ->first();

            if ($attendance && $attendance->time_in && !$attendance->time_out) {
                return response()->json(['status'=>'error','message'=>'❌ Already checked in.']);
            }

            $allowedCheckInTime = $scheduleStart->copy()->subMinutes(15);
            if ($now->lt($allowedCheckInTime)) {
                return response()->json(['status'=>'error','message'=>'⏳ Check-in not allowed yet.']);
            }

            $lateCutoff = $scheduleStart->copy()->addMinutes(15);
            $missedCutoff = $scheduleStart->copy()->addMinutes(30);

            if ($now->gt($missedCutoff)) {
                Attendance::firstOrCreate(
                    ['user_id'=>$userId,'schedule_id'=>$scheduleId],
                    ['status'=>'Missed']
                );
                return response()->json(['status'=>'error','message'=>'❌ Deadline passed. Marked as MISSED.']);
            }

            $statusToSet = $now->lte($lateCutoff) ? 'Attending' : 'Late';
            $message = $statusToSet === 'Attending' ? '✅ Check-in successful.' : '⚠️ Check-in successful, but recorded as LATE.';

            $attendance = Attendance::updateOrCreate(
                ['id'=>$attendanceId ?? null],
                [
                    'user_id'=>$userId,
                    'schedule_id'=>$scheduleId,
                    'time_in'=>$now,
                    'latitude'=>$request->latitude,
                    'longitude'=>$request->longitude,
                    'is_valid'=>$isValidLocation,
                    'status'=>$statusToSet,
                ]
            );

            // ✅ TEACHER NOTIFICATION
            TeacherNotification::create([
                'type' => 'attendance',
                'title' => 'Check-in ' . $statusToSet,
                'message' => "You have successfully checked in for {$schedule->subject->subject_name} at {$schedule->room->room_code}. Status: $statusToSet",
                'created_by' => $userId,
            ]);
    
            return response()->json([
                'status'=>'success',
                'message'=>$message,
                'attendance_id'=>$attendance->id,
                'check_in_status'=>$statusToSet
            ]);
        }

        // CHECK-OUT LOGIC
        if (!$attendanceId) {
            return response()->json(['status'=>'error','message'=>'❌ Attendance record not found for checkout.']);
        }

        $attendance = Attendance::find($attendanceId);

        if (!$attendance || $attendance->time_out) {
            return response()->json(['status'=>'error','message'=>'❌ You have not checked in yet or already checked out.']);
        }

        $finalStatus = match ($attendance->status) {
            'Late' => 'Late',
            'Attending', 'Ongoing' => 'Attended',
            'Missed' => 'Missed',
            default => 'Attended',
        };

        $attendance->update([
            'time_out'=>$now,
            'status'=>$finalStatus,
            'latitude_out'=>$request->latitude,
            'longitude_out'=>$request->longitude,
        ]);

        TeacherNotification::create([
        'type' => 'attendance',
        'title' => 'Check-out Completed',
        'message' => "You checked out from {$schedule->subject->subject_name} at {$schedule->room->room_code}. Final status: $finalStatus",
        'created_by' => $userId,
        ]);
        
        return response()->json([
            'status'=>'success',
            'message'=>'✅ Check-out successful. You are now marked as ' . $finalStatus . '.',
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
            ->whereIn('status',['Attended','Late','Missed'])
            ->orderByDesc('created_at');

        if (!empty($search)) {
            $query->where(function($q) use ($search){
                $q->whereHas('schedule.subject', fn($subQ)=>$subQ->where('subject_name','like',"%$search%"))
                  ->orWhereHas('schedule.room', fn($roomQ)=>$roomQ->where('room_code','like',"%$search%"))
                  ->orWhere('status','like',"%$search%");
            });
        }

        if (!empty($status)) $query->where('status',$status);
        if (!empty($subject)) $query->whereHas('schedule.subject', fn($q)=>$q->where('subject_name',$subject));
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at',[
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $attendanceHistory = $query->get()
            ->groupBy(fn($attendance)=>Carbon::parse($attendance->created_at)->toDateString());

        $subjects = Schedule::where('user_id', $teacher->id)
            ->with('subject')
            ->get()
            ->pluck('subject.subject_name')
            ->unique()
            ->filter()
            ->values();

        return view('teacher.teacher-history',[
            'attendanceHistory'=>$attendanceHistory,
            'subjects'=>$subjects
        ]);
    }

    /**
     * Export attendance to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search','status','start_date','end_date','subject']);
        $filename = 'attendance_export_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new TeacherAttendanceExport($filters), $filename);
    }

    /**
     * Validate location within radius (meters)
     */
    private function isWithinRadius($lat1,$lon1,$lat2,$lon2,$radius=5)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2-$lat1);
        $dLon = deg2rad($lon2-$lon1);

        $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
        $c = 2*atan2(sqrt($a),sqrt(1-$a));
        $distance = $earthRadius*$c;

        return $distance <= $radius;
    }
}
