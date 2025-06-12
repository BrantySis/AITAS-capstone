<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h2 class="text-2xl font-bold mb-6 text-blue-700 text-center">Upcoming Schedule</h2>

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
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('lat-' + scheduleId).value = lat;
                    document.getElementById('lng-' + scheduleId).value = lng;

                    // Optional map preview
                    const mapContainer = document.getElementById("map");
                    mapContainer.classList.remove('hidden');
                    const map = L.map('map').setView([lat, lng], 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);
                    L.marker([lat, lng]).addTo(map).bindPopup("You are here").openPopup();

                    // Submit form
                    document.querySelector(`#lat-${scheduleId}`).closest('form').submit();
                }, function (error) {
                    alert("❌ Location error: " + error.message);
                });
            } else {
                alert("Geolocation is not supported.");
            }
        }
    </script>

    {{-- Load Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</x-app-layout>
