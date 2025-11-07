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
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['teacher', 'room', 'subject']);

        // 🔍 Search
        if ($search = $request->input('search')) {
            $query->whereHas('teacher', fn($q) => $q->where('name', 'like', "%$search%"))
                ->orWhereHas('subject', fn($q) => $q->where('subject_name', 'like', "%$search%"))
                ->orWhereHas('room', fn($q) => $q->where('room_code', 'like', "%$search%"));
        }

        // ⚙️ Filter
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
        $attendanceMap = Attendance::pluck('schedule_id')->unique()->toArray();

        // ✅ Load lists for dropdowns
        $teachers = User::where('role_id', 2)->get();
        $rooms = Room::all();
        $subjects = Subject::all();

        return view('admin.admin-schedules', compact(
            'schedules',
            'attendanceMap',
            'teachers',
            'rooms',
            'subjects'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'edp_code' => 'required|string|max:255',
            'type' => 'required|in:lecture,lab',
            'day_of_week' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'semester' => 'required|string',
            'school_year' => 'required|string',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
        ]);

        try {
            $subject = Subject::findOrFail($request->subject_id);

            // ✅ Check for teacher schedule conflict
            $conflict = $this->checkScheduleConflict(
                $request->user_id,
                $request->day_of_week,
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

            // ✅ Create schedule record
            Schedule::create([
                'user_id' => $request->user_id,
                'room_id' => $request->room_id,
                'subject_id' => $request->subject_id,
                'edp_code' => $request->edp_code,
                'units' => $subject->units ?? 3,
                'type' => $request->type,
                'day_of_week' => $request->day_of_week,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'semester' => $request->semester,
                'school_year' => $request->school_year,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
            ]);

            return redirect()->route('admin.schedules.index')
                ->with('success', '✅ Schedule created successfully.');

        } catch (\Exception $e) {
            Log::error('Schedule creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => '❌ Failed to create schedule. Please check inputs or try again.']);
        }
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'edp_code' => 'required|string|max:255',
            'type' => 'required|in:lecture,lab',
            'day_of_week' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'semester' => 'required|string',
            'school_year' => 'required|string',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
        ]);

        $subject = Subject::findOrFail($request->subject_id);

        // ✅ Conflict check (ignore current schedule)
        $conflict = $this->checkScheduleConflict(
            $request->user_id,
            $request->day_of_week,
            $request->starts_at,
            $request->ends_at,
            $request->start_date,
            $request->end_date,
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
            'day_of_week' => $request->day_of_week,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'semester' => $request->semester,
            'school_year' => $request->school_year,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->route('admin.schedules.index')
            ->with('success', '✅ Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
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
    private function checkScheduleConflict($teacherId, $dayOfWeek, $startTime, $endTime, $startDate, $endDate, $excludeId = null)
    {
        $query = Schedule::where('user_id', $teacherId)
            ->where('day_of_week', $dayOfWeek);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $startTime = Carbon::parse($startTime);
        $endTime   = Carbon::parse($endTime);
        $startDate = Carbon::parse($startDate);
        $endDate   = Carbon::parse($endDate);

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
}
