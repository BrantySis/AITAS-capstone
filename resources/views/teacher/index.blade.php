<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('dashboard.teacher') }}" 
               class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <h2 class="flex-grow text-2xl font-bold text-center text-blue-700">Upcoming Schedule</h2>
        </div>

        <!-- Notifications -->
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-400 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded bg-red-100 border border-red-400 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <!-- Schedules -->
        @if($schedules->isEmpty())
            <p class="text-center text-gray-500">You don't have any upcoming schedules.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 bg-white text-sm shadow-md rounded-lg">
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
                                <td class="px-6 py-4">{{ $schedule->subject->subject_name ?? 'Unknown Subject' }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($schedule->starts_at)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->ends_at)->format('g:i A') }}
                                </td>
                                <td class="px-6 py-4">{{ optional($schedule->room)->room_code ?? 'No Room' }}</td>
                                <td class="px-6 py-4">{{ \Carbon\Carbon::parse($schedule->starts_at)->format('F j, Y') }}</td>
                                <td class="px-6 py-4 space-y-2">
                                    @php
                                        $hasCheckedIn = in_array($schedule->id, $checkedInSchedules);
                                    @endphp

                                    <!-- Check-In Button -->
                                    @if($hasCheckedIn)
                                        <button class="w-full rounded bg-gray-400 px-3 py-1 text-white cursor-not-allowed" disabled>
                                            Already Checked In
                                        </button>
                                    @else
                                    <form method="GET" action="{{ route('teacher.teacher.face.verification') }}">
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <button type="submit"
                                                class="w-full rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                            Check In
                                        </button>
                                    </form>
                                        {{-- <form method="POST" action="{{ route('teacher.attendance.store') }}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                            <input type="hidden" name="latitude" id="lat-{{ $schedule->id }}">
                                            <input type="hidden" name="longitude" id="lng-{{ $schedule->id }}">
                                            <input type="hidden" id="room-lat-{{ $schedule->id }}" value="{{ optional($schedule->room)->latitude }}">
                                            <input type="hidden" id="room-lng-{{ $schedule->id }}" value="{{ optional($schedule->room)->longitude }}">

                                            <span id="distance-msg-{{ $schedule->id }}" class="block mt-2 text-sm text-red-600"></span>

                                            <button type="button"
                                                    onclick="startFaceVerification({{ $schedule->id }})"
                                                    class="w-full rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                                Check In
                                            </button>
                                        </form> --}}
                                    @endif

                                    <!-- Time Out Button -->
                                    <form method="POST" action="{{ route('teacher.attendance.timeout') }}">
                                        @csrf
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <button type="submit"
                                                class="w-full rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700">
                                            Time Out
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Map -->
    <div id="map" class="mt-6 hidden h-64 w-full rounded-lg shadow"></div>

    <!-- Hidden video + canvas for face -->
    <video id="video" autoplay class="hidden"></video>
    <canvas id="canvas" width="400" height="300" class="hidden"></canvas>

    <!-- Scripts -->
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                return true;
            } catch (err) {
                alert("❌ Camera error: " + err);
                return false;
            }
        }

        function captureFrame() {
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            return new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));
        }

        async function startFaceVerification(scheduleId) {
            const ok = await startCamera();
            if (!ok) return;

            const blob = await captureFrame();
            const formData = new FormData();
            formData.append("image", blob);

            try {
                const res = await fetch("http://127.0.0.1:8001/recognize", { method: "POST", body: formData });
                const data = await res.json();

                if (data.status === "success" && data.recognized_as !== "Unknown") {
                    alert("✅ Face verified: " + data.recognized_as);
                    getLocationAndSubmit(scheduleId); // proceed to GPS + Laravel
                } else {
                    alert("❌ Face not recognized. Check-in blocked.");
                }
            } catch (err) {
                alert("⚠️ Error contacting face server: " + err);
            }
        }

        function getLocationAndSubmit(scheduleId) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    const acc = pos.coords.accuracy;
                    if (acc > 10) {
                        alert("⚠️ GPS accuracy is low (" + acc + "m). Please wait.");
                        return;
                    }
                    const userLat = pos.coords.latitude;
                    const userLng = pos.coords.longitude;
                    document.getElementById('lat-' + scheduleId).value = userLat;
                    document.getElementById('lng-' + scheduleId).value = userLng;

                    const roomLat = parseFloat(document.getElementById('room-lat-' + scheduleId).value);
                    const roomLng = parseFloat(document.getElementById('room-lng-' + scheduleId).value);
                    const dist = getDistanceInMeters(userLat, userLng, roomLat, roomLng);
                    const msg = document.getElementById('distance-msg-' + scheduleId);
                    msg.textContent = `📍 You are ${dist.toFixed(2)}m from room.`;

                    // Map
                    const mapDiv = document.getElementById("map");
                    mapDiv.classList.remove("hidden");
                    mapDiv.innerHTML = "";
                    const map = L.map("map").setView([userLat, userLng], 18);
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
                    L.marker([userLat, userLng]).addTo(map).bindPopup("You are here").openPopup();
                    L.circle([roomLat, roomLng], {color:"blue",radius:5}).addTo(map);
                    L.marker([roomLat, roomLng]).addTo(map).bindPopup("Room");

                    if (dist <= 5) {
                        document.querySelector(`#lat-${scheduleId}`).closest('form').submit();
                    } else {
                        msg.textContent += " ❌ Outside allowed range.";
                    }
                }, e => alert("❌ Location error: " + e.message), {
                    enableHighAccuracy:true,timeout:10000,maximumAge:0
                });
            } else {
                alert("❌ Geolocation not supported.");
            }
        }

        function getDistanceInMeters(lat1, lng1, lat2, lng2) {
            const R = 6371000, dLat=(lat2-lat1)*Math.PI/180, dLng=(lng2-lng1)*Math.PI/180;
            const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
            return R*2*Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }
    </script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</x-app-layout>
