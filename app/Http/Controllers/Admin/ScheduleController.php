<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Subject;
use App\Models\AdminNotification;
use App\Models\TeacherNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewScheduleNotification;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SchedulesImport;

class ScheduleController extends Controller
{
    private array $dayPatternMap = [
        'MWF' => ['Monday', 'Wednesday', 'Friday'],
        'TTH' => ['Tuesday', 'Thursday'],
        'Sat' => ['Saturday'],
        'Sun' => ['Sunday'],
    ];

    /**
     * Display a listing of schedules.
     */
    public function index(Request $request)
    {
        $query = Schedule::with(['teacher', 'room', 'subject']);

        if ($search = $request->input('search')) {
            $query->whereHas('teacher', fn($q) => $q->where('name', 'like', "%$search%"))
                ->orWhereHas('subject', fn($q) => $q->where('subject_name', 'like', "%$search%"))
                ->orWhereHas('room', fn($q) => $q->where('room_code', 'like', "%$search%"));
        }

        $now = now();
        if ($filter = $request->input('filter')) {
            if ($filter === 'in-progress') {
                $query->where('starts_at', '<=', $now)->where('ends_at', '>=', $now);
            } elseif ($filter === 'upcoming') {
                $query->where('starts_at', '>', $now);
            } elseif ($filter === 'missed') {
                $query->where('ends_at', '<', $now);
            }
        }

        $query->orderBy('starts_at', 'asc');
        $schedules = $query->paginate(10);

        $teachers = User::where('role_id', 2)->get();
        $rooms = Room::all();
        $subjects = Subject::all();

        return view('admin.admin-schedules', compact('schedules', 'teachers', 'rooms', 'subjects'));
    }

    /**
     * Store a newly created schedule and send notifications.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'edp_code' => 'required|string|max:255',
            'type' => 'required|in:lecture,lab',
            'day_of_week' => 'required|in:MWF,TTH,Sat,Sun',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'semester' => 'required|string',
            'school_year' => 'required|string',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
        ]);

        try {
            $subject = Subject::findOrFail($request->subject_id);

            $dayPattern = $request->day_of_week;
            $daysOfWeek = $this->dayPatternMap[$dayPattern];

            $conflict = $this->checkScheduleConflict(
                    $request->user_id,   // teacher
                    $request->room_id,   // room
                    $daysOfWeek,
                    $request->starts_at,
                    $request->ends_at,
                    $request->start_date,
                    $request->end_date
            );

            if ($conflict) {
                return back()->withErrors([
                    'conflict' => '⚠️ This teacher already has a conflicting schedule on the selected day/time.'
                ])->withInput();
            }

            $schedule = Schedule::create([
                'user_id' => $request->user_id,
                'room_id' => $request->room_id,
                'subject_id' => $request->subject_id,
                'edp_code' => $request->edp_code,
                'units' => $subject->units ?? 3,
                'type' => $request->type,
                'day_of_week' => $dayPattern,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'semester' => $request->semester,
                'school_year' => $request->school_year,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
            ]);

            $teacher = User::find($schedule->user_id);
            if ($teacher) {
                $teacher->notify(new NewScheduleNotification($schedule));

                TeacherNotification::create([
                    'user_id' => $teacher->id,
                    'type' => 'schedule',
                    'title' => 'New Schedule Added',
                    'message' => "You have a new schedule: {$subject->subject_name} in {$schedule->room->room_code} on {$schedule->day_of_week} at {$schedule->starts_at} - {$schedule->ends_at}.",
                    'created_by' => auth()->id(),
                ]);
            }

            AdminNotification::create([
                'type' => 'schedule',
                'title' => 'New Schedule Added',
                'message' => "Schedule for {$subject->subject_name} has been assigned to {$teacher->name}.",
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('success', '✅ Schedule created and notifications sent.');
        } catch (\Exception $e) {
            Log::error('Schedule creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => '❌ Failed to create schedule.']);
        }
    }

    /**
     * Update a schedule.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'edp_code' => 'required|string|max:255',
            'type' => 'required|in:lecture,lab',
            'day_of_week' => 'required|in:MWF,TTH,Sat,Sun',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'semester' => 'required|string',
            'school_year' => 'required|string',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
        ]);

        $subject = Subject::findOrFail($request->subject_id);

        $dayPattern = $request->day_of_week;
        $daysOfWeek = $this->dayPatternMap[$dayPattern];

        $conflict = $this->checkScheduleConflict(
                $request->user_id,
                $request->room_id,
                $daysOfWeek,
                $request->starts_at,
                $request->ends_at,
                $request->start_date,
                $request->end_date,
                $schedule->id // exclude current schedule
        );

        if ($conflict) {
            return back()->withErrors([
                'conflict' => '⚠️ This teacher already has another schedule during that time.'
            ])->withInput();
        }

        $schedule->update([
            'user_id' => $request->user_id,
            'room_id' => $request->room_id,
            'subject_id' => $request->subject_id,
            'edp_code' => $request->edp_code,
            'units' => $subject->units ?? 3,
            'type' => $request->type,
            'day_of_week' => $dayPattern,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'semester' => $request->semester,
            'school_year' => $request->school_year,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        AdminNotification::create([
            'type' => 'schedule',
            'title' => 'Schedule Updated',
            'message' => "Schedule for {$subject->subject_name} has been updated for {$schedule->teacher->name}.",
            'created_by' => auth()->id(),
        ]);

        TeacherNotification::create([
            'user_id' => $schedule->user_id,
            'type' => 'schedule',
            'title' => 'Schedule Updated',
            'message' => "Your schedule for {$subject->subject_name} has been updated: {$schedule->day_of_week} at {$schedule->starts_at} - {$schedule->ends_at}.",
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', '✅ Schedule updated and notification sent.');
    }

    /**
     * Delete a schedule.
     */
    public function destroy(Schedule $schedule)
    {
        if ($schedule->attendances()->exists()) {
            return redirect()->route('admin.schedules.index')
                ->with('error', '❌ Cannot delete a schedule with recorded attendance.');
        }

        $subjectName = $schedule->subject->subject_name;
        $teacherName = $schedule->teacher->name;

        $schedule->delete();

        AdminNotification::create([
            'type' => 'schedule',
            'title' => 'Schedule Deleted',
            'message' => "Schedule for {$subjectName} assigned to {$teacherName} has been deleted.",
            'created_by' => auth()->id(),
        ]);

        TeacherNotification::create([
            'user_id' => $schedule->user_id,
            'type' => 'schedule',
            'title' => 'Schedule Deleted',
            'message' => "Your schedule for {$subjectName} has been deleted.",
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', '🗑️ Schedule deleted and notification sent.');
    }

    /**
     * Check for schedule conflicts (supports multiple weekdays)
     */
private function checkScheduleConflict(
    $teacherId,
    $roomId,
    array $daysOfWeek,
    $startTime,
    $endTime,
    $startDate,
    $endDate,
    $excludeId = null
) {
    $query = Schedule::where(function ($q) use ($teacherId, $roomId) {
            $q->where('user_id', $teacherId)   // teacher conflict
              ->orWhere('room_id', $roomId);  // room conflict
        })
        ->whereIn('day_of_week', array_keys(array_filter($this->dayPatternMap, fn($v) => count(array_intersect($v, $daysOfWeek)) > 0)));

    if ($excludeId) {
        $query->where('id', '!=', $excludeId);
    }

    $startTime = Carbon::parse($startTime);
    $endTime = Carbon::parse($endTime);
    $startDate = Carbon::parse($startDate);
    $endDate = Carbon::parse($endDate);

    return $query->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('starts_at', [$startTime, $endTime])
              ->orWhereBetween('ends_at', [$startTime, $endTime])
              ->orWhere(function ($inner) use ($startTime, $endTime) {
                  $inner->where('starts_at', '<=', $startTime)
                        ->where('ends_at', '>=', $endTime);
              });
        })
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        })
        ->exists();
}
    /**
     * Import schedules from an uploaded Excel or CSV file.
     */
    public function importSchedules(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls|max:4096',
    ]);

    try {
        Excel::import(new SchedulesImport, $request->file('file'));

        AdminNotification::create([
            'type' => 'schedule',
            'title' => 'Schedules Imported',
            'message' => 'New schedules have been successfully imported.',
            'created_by' => auth()->id(),
        ]);

        // Redirect to index to reload schedules properly
        return redirect()->route('admin.schedules.index')
                         ->with('success', '✅ Schedules imported successfully.');
    } catch (\Exception $e) {
        Log::error('Schedule import failed: ' . $e->getMessage());
        return redirect()->route('admin.schedules.index')
                         ->withErrors(['error' => '❌ Failed to import schedules. Please check your file format.']);
    }
}
}
