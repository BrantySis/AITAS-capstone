<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Attendance;
use App\Models\Subject;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['teacher', 'room', 'subject']);

        // Optional filter: show only "in-progress" schedules
        if ($request->filled('filter') && $request->filter === 'in-progress') {
            $now = now();
            $query->where('starts_at', '<=', $now)
                  ->where('ends_at', '>=', $now);
        }

        $schedules = $query->orderBy('starts_at')->get();

        // Identify schedules with attendance records
        $attendanceMap = Attendance::pluck('schedule_id')->unique()->toArray();

        return view('admin.schedules.index', compact('schedules', 'attendanceMap'));
    }

    public function create()
    {
        // Fetch only teachers
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get();
        $rooms = Room::all();
        $subjects = Subject::all();

        return view('admin.schedules.create', compact('teachers', 'rooms', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'edp_code' => 'required|string|max:255',
            'type' => 'required|in:lecture,lab',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $subject = Subject::findOrFail($request->subject_id);

        // Check for conflicting schedules for same teacher
        $conflict = $this->checkScheduleConflict(
            $request->user_id,
            $request->starts_at,
            $request->ends_at
        );

        if ($conflict) {
            return back()->withErrors([
                'conflict' => '⚠️ This teacher already has a schedule during that time.'
            ])->withInput();
        }

        Schedule::create([
            'user_id' => $request->user_id,
            'room_id' => $request->room_id,
            'subject_id' => $request->subject_id,
            'edp_code' => $request->edp_code,
            'units' => $subject->units ?? 3, // fallback if missing
            'type' => $request->type,
            'starts_at' => Carbon::parse($request->starts_at),
            'ends_at' => Carbon::parse($request->ends_at),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', '✅ Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get();
        $rooms = Room::all();
        $subjects = Subject::all();

        return view('admin.schedules.edit', compact('schedule', 'teachers', 'rooms', 'subjects'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'edp_code' => 'required|string|max:255',
            'type' => 'required|in:lecture,lab',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $subject = Subject::findOrFail($request->subject_id);

        // Check conflict excluding current schedule
        $conflict = $this->checkScheduleConflict(
            $request->user_id,
            $request->starts_at,
            $request->ends_at,
            $schedule->id
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
            'starts_at' => Carbon::parse($request->starts_at),
            'ends_at' => Carbon::parse($request->ends_at),
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', '✅ Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        // Prevent deletion if attendance already exists
        if ($schedule->attendances()->exists()) {
            return redirect()->route('admin.schedules.index')
                ->with('error', '❌ Cannot delete a schedule with recorded attendance.');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', '🗑️ Schedule deleted successfully.');
    }

    /**
     * Check for schedule conflicts.
     */
    private function checkScheduleConflict($teacherId, $start, $end, $excludeId = null)
    {
        $query = Schedule::where('user_id', $teacherId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('starts_at', [$start, $end])
              ->orWhereBetween('ends_at', [$start, $end])
              ->orWhere(function ($inner) use ($start, $end) {
                  $inner->where('starts_at', '<=', $start)
                        ->where('ends_at', '>=', $end);
              });
        })->exists();
    }
}
