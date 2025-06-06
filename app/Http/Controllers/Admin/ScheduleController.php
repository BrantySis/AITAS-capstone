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
             'starts_at' => 'required|date',
             'ends_at' => 'required|date|after:starts_at',
        ]);

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
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}

