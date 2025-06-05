<x-app-layout>
    <h2 class="text-2xl font-bold mb-4">Edit Room</h2>

    {{-- Duplicate error --}}
    @if ($errors->has('duplicate'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errors->first('duplicate') }}
        </div>
    @endif

    {{-- Other validation errors --}}
    @if ($errors->any() && !$errors->has('duplicate'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.rooms.update', $room) }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold">Room Code</label>
            <input type="text" name="room_code" value="{{ old('room_code', $room->room_code) }}" class="w-full border px-4 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold">Building Name</label>
            <input type="text" name="building_name" value="{{ old('building_name', $room->building_name) }}" class="w-full border px-4 py-2 rounded">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold">Latitude</label>
                <input type="text" name="latitude" value="{{ old('latitude', $room->latitude) }}" class="w-full border px-4 py-2 rounded">
            </div>
            <div>
                <label class="block font-semibold">Longitude</label>
                <input type="text" name="longitude" value="{{ old('longitude', $room->longitude) }}" class="w-full border px-4 py-2 rounded">
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update</button>
            <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</x-app-layout>
