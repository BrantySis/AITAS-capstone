<x-app-layout>
     <!-- Back Button -->
     <div class="max-w-7xl -ml-1 mx-auto mb-6">
        <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>
    
    <h2 class="text-2xl font-bold mb-4">Edit Room</h2>

    {{-- Error Messages --}}
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
                <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $room->latitude) }}" class="w-full border px-4 py-2 rounded">
            </div>
            <div>
                <label class="block font-semibold">Longitude</label>
                <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $room->longitude) }}" class="w-full border px-4 py-2 rounded">
            </div>
        </div>

        <button type="button" onclick="getLocation()" class="text-sm text-blue-600 underline">📍 Use My Current Location</button>

        <div id="map" class="w-full h-64 mt-4 rounded shadow border"></div>

        <div class="flex gap-4">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update</button>
            <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>

    {{-- Leaflet & Map Script --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map, marker, circle;
    const radius = 4;

    function initMap(lat, lng) {
        if (!map) {
            map = L.map('map').setView([lat, lng], 30); // or try 20
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            marker = L.marker([lat, lng], { draggable: true }).addTo(map)
                .bindPopup("Drag to adjust location").openPopup();

            circle = L.circle([lat, lng], {
                radius: radius,
                color: 'blue',
                fillColor: '#cce5ff',
                fillOpacity: 0.3
            }).addTo(map);

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat.toFixed(6);
                document.getElementById('longitude').value = position.lng.toFixed(6);
                circle.setLatLng(position);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                circle.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
            });

        } else {
            map.setView([lat, lng], 18);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
        }
    }

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                initMap(lat, lng);
            }, function (error) {
                alert("❌ Error: " + error.message);
            });
        } else {
            alert("Geolocation not supported.");
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const lat = parseFloat(document.getElementById('latitude').value) || 14.5995;
        const lng = parseFloat(document.getElementById('longitude').value) || 120.9842;
        initMap(lat, lng);
    });
    </script>
</x-app-layout>
