<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource (Index).
     * Supports filtering by building and searching by room code.
     */
    public function index(Request $request)
    {
        $query = Room::query();

        if ($request->filled('building')) {
            $query->where('building_name', $request->building);
        }

        if ($request->filled('search')) {
            $query->where('room_code', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->get();

        $buildings = Room::select('building_name')
            ->whereNotNull('building_name')
            ->distinct()
            ->pluck('building_name')
            ->sort()
            ->values();

        return view('admin.admin-rooms', compact('rooms', 'buildings'));
    }

    /**
     * Store a newly created room and notify admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_code' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (Room::where('room_code', $validated['room_code'])->exists()) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'Room with this code already exists.'])
                ->with('error', 'Room with this code already exists.')
                ->withInput()
                ->with('modal_open', 1)
                ->with('modal_type', 'add');
        }

        $validated['latitude'] = $validated['latitude'] ?? null;
        $validated['longitude'] = $validated['longitude'] ?? null;

        $room = Room::create($validated);

        // ✅ Admin notification
        AdminNotification::create([
            'type' => 'room',
            'title' => 'New Room Added',
            'message' => "Room {$room->room_code} has been added.",
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room successfully created.');
    }

    /**
     * Update the specified room and notify admin.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_code' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (
            Room::where('room_code', $validated['room_code'])
                ->where('id', '!=', $room->id)
                ->exists()
        ) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'Another room with this code already exists.'])
                ->with('error', 'Another room with this code already exists.')
                ->withInput()
                ->with('modal_open', 1)
                ->with('modal_type', 'edit')
                ->with('room_id_on_error', $room->id);
        }

        $room->update($validated);

        // ✅ Admin notification
        AdminNotification::create([
            'type' => 'room',
            'title' => 'Room Updated',
            'message' => "Room {$room->room_code} has been updated.",
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room successfully updated.');
    }

    /**
     * Remove the specified room and notify admin.
     */
    public function destroy(Room $room)
    {
        $roomCode = $room->room_code;
        $room->delete();

        // ✅ Admin notification
        AdminNotification::create([
            'type' => 'room',
            'title' => 'Room Deleted',
            'message' => "Room {$roomCode} has been deleted.",
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room successfully deleted.');
    }
}
