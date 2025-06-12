<x-app-layout>
    <h2 class="text-2xl font-bold mb-4">Add Room</h2>

    {{-- Error/Validation messages --}}
    @if ($errors->has('duplicate'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errors->first('duplicate') }}
        </div>
    @endif

    @if ($errors->any() && !$errors->has('duplicate'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf

        <div>
            <label class="block font-semibold">Room Code</label>
            <input type="text" name="room_code" value="{{ old('room_code') }}" class="w-full border px-4 py-2 rounded" required>
        </div>

        <div>
            <label class="block font-semibold">Building Name</label>
            <input type="text" name="building_name" value="{{ old('building_name') }}" class="w-full border px-4 py-2 rounded">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold">Latitude</label>
                <input type="text" name="latitude" id="latitude" class="w-full border px-4 py-2 rounded" readonly>
            </div>
            <div>
                <label class="block font-semibold">Longitude</label>
                <input type="text" name="longitude" id="longitude" class="w-full border px-4 py-2 rounded" readonly>
            </div>
        </div>

        <button type="button" onclick="getLocation()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            📍 Detect Location
        </button>

        <div id="map" class="w-full h-64 my-4 rounded shadow border"></div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        let map, marker;

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;

                        // Initialize or update the map
                        if (!map) {
                            map = L.map('map').setView([lat, lng], 18);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(map);
                            marker = L.marker([lat, lng]).addTo(map).bindPopup('You are here.').openPopup();
                        } else {
                            map.setView([lat, lng], 18);
                            marker.setLatLng([lat, lng]);
                        }
                    },
                    function (error) {
                        alert("⚠️ Location access failed: " + error.message);
                    }
                );
            } else {
                alert("🚫 Geolocation is not supported by your browser.");
            }
        }
    </script>
</x-app-layout>
