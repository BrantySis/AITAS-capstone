<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;
use App\Models\Attendance;
use App\Models\Subject;

class ScheduleController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Schedule::with(['teacher', 'room']);
    
        // Filter: show only in-progress if requested
        if ($request->has('filter') && $request->filter === 'in-progress') {
            $now = now();
            $query->where('starts_at', '<=', $now)
                  ->where('ends_at', '>=', $now);
        }
    
        $schedules = $query->orderBy('starts_at')->get();
    
        // Mark which schedules were attended
        $attendanceMap = Attendance::pluck('schedule_id')->unique()->toArray();
    
        return view('admin.schedules.index', compact('schedules', 'attendanceMap'));
    }
    

    public function create()
    {
        // Fetch teachers by role 'teacher'
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        $rooms = Room::all();
        $subjects = Subject::all(); // Add subjects

        return view('admin.schedules.create', compact('teachers', 'rooms', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'subject_id' => 'required|exists:subjects,id', // Change validation
            'edp_code' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'type' => 'required|in:lecture,lab',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        // Get units from selected subject
        $subject = Subject::find($request->subject_id);
        
        // Prevent overlapping schedule for the same teacher
        $conflict = Schedule::where('user_id', $request->user_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('starts_at', [$request->starts_at, $request->ends_at])
                      ->orWhereBetween('ends_at', [$request->starts_at, $request->ends_at])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('starts_at', '<=', $request->starts_at)
                            ->where('ends_at', '>=', $request->ends_at);
                      });
            })->exists();

        if ($conflict) {
            return redirect()->back()
                ->withErrors(['conflict' => 'This teacher already has a schedule during that time.'])
                ->withInput();
        }

        Schedule::create([
            'user_id' => $request->user_id,
            'room_id' => $request->room_id,
            'subject_id' => $request->subject_id,
            'edp_code' => $request->edp_code,
            'units' => $subject->units, // Auto-fill from subject
            'type' => $request->type,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        $rooms = Room::all();
        $subjects = Subject::all(); // Add subjects

        return view('admin.schedules.edit', compact('schedule', 'teachers', 'rooms', 'subjects'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'edp_code' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'type' => 'required|in:lecture,lab',
            'starts_at' => 'required|date',
             'ends_at' => 'required|date|after:starts_at',
        ]);

        // Check for conflicts excluding the current schedule
       $conflict = Schedule::where('user_id', $request->user_id)
    ->where('id', '!=', $schedule->id)
    ->where(function ($query) use ($request) {
        $query->whereBetween('starts_at', [$request->starts_at, $request->ends_at])
              ->orWhereBetween('ends_at', [$request->starts_at, $request->ends_at])
              ->orWhere(function ($q) use ($request) {
                  $q->where('starts_at', '<=', $request->starts_at)
                    ->where('ends_at', '>=', $request->ends_at);
              });
    })->exists();

        if ($conflict) {
            return redirect()->back()
                ->withErrors(['conflict' => 'This teacher already has a schedule during that time.'])
                ->withInput();
        }

        $schedule->update($request->all());

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule updated successfully.');
    }

    

    public function destroy(Schedule $schedule)
    {
        // Prevent deletion if attendance exists
        if ($schedule->attendances()->exists()) {
            return redirect()->route('admin.schedules.index')
                ->with('error', '❌ Cannot delete schedule with recorded attendance.');
        }
    
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}


