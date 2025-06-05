<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Room;

class ScheduleController extends Controller
{
    public function index()
    {
        // Load schedules with 'teacher' relationship (not 'user')
        $schedules = Schedule::with(['teacher', 'room'])->get();
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        // Fetch teachers by role 'teacher'
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        $rooms = Room::all();

        // Pass as 'teachers' to the view
        return view('admin.schedules.create', compact('teachers', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'edp_code' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'type' => 'required|in:lecture,lab',
            'day' => 'required|string',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        // Prevent overlapping schedule for the same teacher
        $conflict = Schedule::where('user_id', $request->user_id)
            ->where('day', $request->day)
            ->where(function ($query) use ($request) {
                $query->whereBetween('time_start', [$request->time_start, $request->time_end])
                    ->orWhereBetween('time_end', [$request->time_start, $request->time_end])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('time_start', '<=', $request->time_start)
                            ->where('time_end', '>=', $request->time_end);
                    });
            })->exists();

        if ($conflict) {
            return redirect()->back()
                ->withErrors(['conflict' => 'This teacher already has a schedule during that time.'])
                ->withInput();
        }

        Schedule::create($request->all());

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        $rooms = Room::all();

        return view('admin.schedules.edit', compact('schedule', 'teachers', 'rooms'));
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
            'day' => 'required|string',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
        ]);

        // Check for conflicts excluding the current schedule
        $conflict = Schedule::where('user_id', $request->user_id)
            ->where('day', $request->day)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('time_start', [$request->time_start, $request->time_end])
                    ->orWhereBetween('time_end', [$request->time_start, $request->time_end])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('time_start', '<=', $request->time_start)
                            ->where('time_end', '>=', $request->time_end);
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
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}

