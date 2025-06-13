<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        
    <div class="flex items-center justify-between mb-6">
        <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
            <!-- Left arrow icon -->
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </a>
        <h2 class="text-2xl font-bold text-blue-700 text-center flex-grow">Upcoming Schedule</h2>
    </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($schedules->isEmpty())
            <p class="text-center text-gray-500">You don't have any upcoming schedules.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md text-sm">
                    <thead class="bg-blue-100 text-blue-800 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left border-b">Subject</th>
                            <th class="px-6 py-3 text-left border-b">Time</th>
                            <th class="px-6 py-3 text-left border-b">Room</th>
                            <th class="px-6 py-3 text-left border-b">Date</th>
                            <th class="px-6 py-3 text-left border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                    @foreach($schedules as $schedule)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $schedule->subject }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($schedule->starts_at)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->ends_at)->format('g:i A') }}
                                </td>
                                <td class="px-6 py-4">{{ optional($schedule->room)->room_code ?? 'No Room' }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($schedule->starts_at)->format('F j, Y') }}</td>
                                <td class="px-6 py-4">
                                @php
                                    $hasCheckedIn = in_array($schedule->id, $checkedInSchedules);
                                @endphp

                                @if($hasCheckedIn)
                                    <button class="bg-gray-400 text-white px-3 py-1 rounded cursor-not-allowed" disabled>
                                        Already Checked In
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('teacher.attendance.store') }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <input type="hidden" name="latitude" id="lat-{{ $schedule->id }}">
                                        <input type="hidden" name="longitude" id="lng-{{ $schedule->id }}">
                                        <input type="hidden" id="room-lat-{{ $schedule->id }}" value="{{ optional($schedule->room)->latitude }}">
                                        <input type="hidden" id="room-lng-{{ $schedule->id }}" value="{{ optional($schedule->room)->longitude }}">
                                        <span id="distance-msg-{{ $schedule->id }}" class="text-sm text-red-600 block mt-2"></span>
                                        <button type="button"
                                            onclick="getLocationAndSubmit({{ $schedule->id }})"
                                            class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                            Check In
                                        </button>
                                        
                                    </form>
                                @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Optional: Leaflet map container --}}
    <div id="map" class="mt-6 w-full h-64 rounded-lg shadow hidden"></div>

    <script>
       function getLocationAndSubmit(scheduleId) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;

                document.getElementById('lat-' + scheduleId).value = userLat;
                document.getElementById('lng-' + scheduleId).value = userLng;

                const roomLat = parseFloat(document.getElementById('room-lat-' + scheduleId).value);
                const roomLng = parseFloat(document.getElementById('room-lng-' + scheduleId).value);

                // Calculate distance in meters
                const distance = getDistanceInMeters(userLat, userLng, roomLat, roomLng);

                const msgElement = document.getElementById('distance-msg-' + scheduleId);

                // Show distance message
                msgElement.textContent = `📍 You are approximately ${distance.toFixed(2)} meters from the room.`;

                // Optional map preview
                const mapContainer = document.getElementById("map");
                mapContainer.classList.remove('hidden');
                mapContainer.innerHTML = ""; // Clear old map
                const map = L.map('map').setView([userLat, userLng], 18);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Mark user's location
                L.marker([userLat, userLng]).addTo(map)
                    .bindPopup("You are here").openPopup();

                // Room marker and 50m radius
                L.circle([roomLat, roomLng], {
                    color: 'blue',
                    fillColor: '#cce5ff',
                    fillOpacity: 0.3,
                    radius: 12 // buffer radius
                }).addTo(map).bindPopup("Allowed Check-In Area");

                L.marker([roomLat, roomLng], {
                    icon: L.icon({
                        iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
                        iconSize: [25, 25]
                    })
                }).addTo(map).bindPopup("Room Location");

                // Only submit if within 50 meters
                if (distance <= 12) {
                    document.querySelector(`#lat-${scheduleId}`).closest('form').submit();
                } else {
                    msgElement.textContent += " ❌ You are outside the allowed range (12m). Check-in blocked.";
                }

            }, function (error) {
                alert("❌ Location error: " + error.message);
            });
        } else {
            alert("Geolocation is not supported.");
        }
        }

                    function getDistanceInMeters(lat1, lon1, lat2, lon2) {
                        const R = 6371000; // Earth radius in meters
                        const dLat = toRad(lat2 - lat1);
                        const dLon = toRad(lon2 - lon1);
                        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                                Math.sin(dLon / 2) * Math.sin(dLon / 2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        return R * c;
                    }

                    function toRad(deg) {
                        return deg * (Math.PI / 180);
                    }
                    </script>

    {{-- Load Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</x-app-layout>
