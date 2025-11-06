<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource (Index).
     * Supports filtering by building and searching by room code.
     */
    public function index(Request $request)
    {
        $query = Room::query();

        // 🔍 Filter by building name
        if ($request->filled('building')) {
            $query->where('building_name', $request->building);
        }

        // 🔍 Search by room code
        if ($request->filled('search')) {
            $query->where('room_code', 'like', '%' . $request->search . '%');
        }

        // Fetch filtered rooms
        $rooms = $query->get();

        // Get unique building names for dropdown filter
        $buildings = Room::select('building_name')
            ->whereNotNull('building_name')
            ->distinct()
            ->pluck('building_name')
            ->sort()
            ->values();

        return view('admin.admin-rooms', compact('rooms', 'buildings'));
    }

    /**
     * Store a newly created resource in storage (Add/Create).
     */
    public function store(Request $request)
    {
        // ✅ Validation
        $validated = $request->validate([
            'room_code' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // ✅ Duplicate check
        if (Room::where('room_code', $validated['room_code'])->exists()) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'Room with this code already exists.'])
                ->with('error', 'Room with this code already exists.')
                ->withInput()
                ->with('modal_open', 1)
                ->with('modal_type', 'add');
        }

        // ✅ Ensure nullable fields are properly set
        $validated['latitude'] = $validated['latitude'] ?? null;
        $validated['longitude'] = $validated['longitude'] ?? null;

        // ✅ Create new room
        Room::create($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room successfully created.');
    }

    /**
     * Update the specified resource in storage (Edit).
     */
    public function update(Request $request, Room $room)
    {
        // ✅ Validation
        $validated = $request->validate([
            'room_code' => 'required|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // ✅ Duplicate check (excluding current)
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

        // ✅ Update
        $room->update($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room successfully deleted.');
    }
}
