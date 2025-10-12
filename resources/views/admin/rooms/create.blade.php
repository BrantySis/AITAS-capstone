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
    
    <h2 class="text-2xl font-bold mb-4">Add Room</h2>

    {{-- Validation Errors --}}
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
                <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" class="w-full border px-4 py-2 rounded">
            </div>
            <div>
                <label class="block font-semibold">Longitude</label>
                <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" class="w-full border px-4 py-2 rounded">
            </div>
        </div>

        <button type="button" onclick="getLocation()" class="text-sm text-blue-600 underline">📍 Use My Current Location</button>

        <div id="map" class="w-full h-64 mt-4 rounded shadow border"></div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
            <a href="{{ route('admin.rooms.index') }}" class="text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, marker, circle, geoWatchId = null;
const accuracyThreshold = 10; // meters
const defaultLat = 14.5995;   // Manila default fallback
const defaultLng = 120.9842;

// Initialize Leaflet map
function initMap(lat, lng) {
    if (!map) {
        map = L.map('map').setView([lat, lng], 19);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        marker = L.marker([lat, lng], { draggable: true }).addTo(map)
            .bindPopup("Drag to adjust location").openPopup();

        circle = L.circle([lat, lng], {
            radius: accuracyThreshold,
            color: 'blue',
            fillColor: '#cce5ff',
            fillOpacity: 0.3
        }).addTo(map);

        marker.on('dragend', function (e) {
            const pos = marker.getLatLng();
            updateFormCoordinates(pos.lat, pos.lng);
            circle.setLatLng(pos);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            circle.setLatLng(e.latlng);
            updateFormCoordinates(e.latlng.lat, e.latlng.lng);
        });
    } else {
        map.setView([lat, lng], 19);
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
    }
}

// Update input boxes
function updateFormCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
}

// Start high-accuracy tracking
function getLocation() {
    if (!navigator.geolocation) {
        alert("❌ Geolocation not supported by this browser.");
        return;
    }

    alert("📡 Getting precise GPS position... Please wait a few seconds.");

    geoWatchId = navigator.geolocation.watchPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const acc = pos.coords.accuracy;

            console.log(`GPS accuracy: ${acc.toFixed(1)}m`);

            if (acc <= accuracyThreshold) {
                alert(`✅ Accurate GPS acquired!\nLatitude: ${lat.toFixed(6)}\nLongitude: ${lng.toFixed(6)}\nAccuracy: ${acc.toFixed(1)}m`);
                updateFormCoordinates(lat, lng);
                initMap(lat, lng);
                stopTracking();
            } else {
                // Display temporary approximate position for feedback
                updateFormCoordinates(lat, lng);
                initMap(lat, lng);
            }
        },
        err => {
            alert("❌ Error: " + err.message);
            stopTracking();
        },
        {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 0
        }
    );
}

function stopTracking() {
    if (geoWatchId !== null) {
        navigator.geolocation.clearWatch(geoWatchId);
        geoWatchId = null;
    }
}

// Initialize map on page load
document.addEventListener('DOMContentLoaded', function () {
    const lat = parseFloat(document.getElementById('latitude').value) || defaultLat;
    const lng = parseFloat(document.getElementById('longitude').value) || defaultLng;
    initMap(lat, lng);
});
</script>
</x-app-layout>
