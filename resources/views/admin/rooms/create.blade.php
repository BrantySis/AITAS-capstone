<x-app-layout>
    <h2 class="text-2xl font-bold mb-4">Add Room</h2>

    <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        <div>
            <label class="block font-semibold">Room Code</label>
            <input type="text" name="room_code" class="w-full border px-4 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold">Building Name</label>
            <input type="text" name="building_name" class="w-full border px-4 py-2 rounded">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold">Latitude</label>
                <input type="text" name="latitude" class="w-full border px-4 py-2 rounded">
            </div>
            <div>
                <label class="block font-semibold">Longitude</label>
                <input type="text" name="longitude" class="w-full border px-4 py-2 rounded">
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</x-app-layout>
