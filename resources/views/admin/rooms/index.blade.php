<x-app-layout>
    <h2 class="text-2xl font-bold mb-4">Room List</h2>
    <a href="{{ route('admin.rooms.create') }}" class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Room</a>

    <table class="w-full bg-white shadow rounded-lg">
        <thead>
            <tr class="bg-gray-100 text-left text-sm text-gray-600 uppercase">
                <th class="px-6 py-3">Room Code</th>
                <th class="px-6 py-3">Building</th>
                <th class="px-6 py-3">Latitude</th>
                <th class="px-6 py-3">Longitude</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rooms as $room)
                <tr class="border-b">
                    <td class="px-6 py-4">{{ $room->room_code }}</td>
                    <td class="px-6 py-4">{{ $room->building_name }}</td>
                    <td class="px-6 py-4">{{ $room->latitude }}</td>
                    <td class="px-6 py-4">{{ $room->longitude }}</td>
                    <td class="px-6 py-4 flex gap-2">
                        <a href="{{ route('admin.rooms.edit', $room) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Delete this room?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-4">No rooms found.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-app-layout>
