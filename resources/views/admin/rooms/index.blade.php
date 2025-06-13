<x-app-layout>

            <!-- Back Button Outside the Frame -->
    <div class="max-w-7xl -ml-1 mx-auto mb-6">
        <a href="{{ url()->previous() }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>

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
