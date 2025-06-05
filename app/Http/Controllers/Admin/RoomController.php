<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'room_code' => 'required|string|max:255',
        'building_name' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    // Custom duplicate check
    if (Room::where('room_code', $validated['room_code'])->exists()) {
        return back()
            ->withErrors(['duplicate' => 'Room with this code already exists.'])
            ->withInput();
    }

    Room::create($validated);

    return redirect()->route('admin.rooms.index')->with('success', 'Room created.');
}

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

   public function update(Request $request, Room $room)
   {
        $validated = $request->validate([
            'room_code' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Prevent duplicate room_code (excluding current room)
        if (Room::where('room_code', $validated['room_code'])
                ->where('id', '!=', $room->id)
                ->exists()) {
            return back()
                ->withErrors(['duplicate' => 'Another room with this code already exists.'])
                ->withInput();
        }

        $room->update($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Room updated.');
   }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Room deleted.');
    }
}

