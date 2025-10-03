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
                                @php $hasCheckedIn = in_array($schedule->id, $checkedInSchedules); @endphp

                                @if($hasCheckedIn)
                                    <button class="w-full rounded bg-gray-400 px-3 py-1 text-white cursor-not-allowed" disabled>
                                        Already Checked In
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('teacher.attendance.store') }}" id="checkin-form-{{ $schedule->id }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                        <input type="hidden" name="latitude" id="lat-{{ $schedule->id }}">
                                        <input type="hidden" name="longitude" id="lng-{{ $schedule->id }}">
                                        <input type="hidden" id="room-lat-{{ $schedule->id }}" value="{{ optional($schedule->room)->latitude }}">
                                        <input type="hidden" id="room-lng-{{ $schedule->id }}" value="{{ optional($schedule->room)->longitude }}">

                                        <button type="button"
                                                onclick="openFaceScanner({{ $schedule->id }})"
                                                class="w-full rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                            Check In
                                        </button>
                                    </form>
                                @endif

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

<!-- Face Scanner Modal -->
<div id="face-scanner-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-96 text-center">
        <h3 class="text-lg font-bold mb-4">Face Verification</h3>
        <video id="scanner-video" autoplay playsinline class="w-full h-64 rounded border mb-4"></video>
        <p id="scanner-message" class="text-gray-700 mb-4">Please align your face in front of the camera...</p>
        <button onclick="closeFaceScanner()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Cancel</button>
    </div>
</div>

<!-- Leaflet Map -->
<div id="map" class="mt-6 hidden h-64 w-full rounded-lg shadow"></div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let scannerVideo = document.getElementById('scanner-video');
let scannerStream = null;
let currentScheduleId = null;
let scanningActive = false; 
let scanTimeout = null;

const FASTAPI_URL = @json($fastapiUrl);

function showNotification(message, type = "success") {
    if (type === "success") {
        alert("✅ SUCCESS: " + message);
    } else if (type === "error") {
        alert("❌ ERROR: " + message);
    } else {
        alert("ℹ️ " + message);
    }
}

function openFaceScanner(scheduleId) {
    currentScheduleId = scheduleId;
    document.getElementById('scanner-message').textContent = "Please align your face in front of the camera...";
    document.getElementById('face-scanner-modal').classList.remove('hidden');
    startScannerCamera();
}

function closeFaceScanner() {
    document.getElementById('face-scanner-modal').classList.add('hidden');
    scanningActive = false; 

    if (scanTimeout) {
        clearTimeout(scanTimeout);
        scanTimeout = null;
    }

    if (scannerStream) {
        scannerStream.getTracks().forEach(track => track.stop());
        scannerStream = null;
    }

    // Reset video so old frame won't freeze
    scannerVideo.pause();
    scannerVideo.srcObject = null;
    scannerVideo.removeAttribute("src");
    scannerVideo.load();
}

async function startScannerCamera() {
    try {
        scannerStream = await navigator.mediaDevices.getUserMedia({ video: true });
        scannerVideo.srcObject = scannerStream;

        await new Promise(resolve => {
            scannerVideo.onloadedmetadata = () => {
                scannerVideo.play();
                resolve();
            };
        });

        // Delay scanning for 3 seconds so you’re not frozen on old face
        scanningActive = true;
        scanTimeout = setTimeout(() => {
            if (scanningActive) scanFaceLoop();
        }, 3000);

    } catch(err) {
        document.getElementById('scanner-message').textContent = "❌ Camera error: " + err;
    }
}

async function scanFaceLoop() {
    if (!scanningActive || !scannerStream) return;

    const canvas = document.createElement('canvas');
    canvas.width = scannerVideo.videoWidth || 320;
    canvas.height = scannerVideo.videoHeight || 240;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(scannerVideo, 0, 0, canvas.width, canvas.height);

    const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg'));
    const formData = new FormData();
    formData.append("image", blob);

    try {
        const res = await fetch(`${FASTAPI_URL}/recognize`, { method: "POST", body: formData });
        const data = await res.json();
        const recognized = data.match ?? "Unknown";

        if (data.status === "success" && recognized !== "Unknown") {
            document.getElementById('scanner-message').textContent = "✅ Face verified: " + recognized;
            closeFaceScanner();
            showNotification("Face verified successfully!", "success");
            getLocationAndSubmit(currentScheduleId);
            return;
        } else {
            document.getElementById('scanner-message').textContent = "🔄 Scanning face...";
        }
    } catch(err) {
        console.error("Face scan error:", err);
        document.getElementById('scanner-message').textContent = "❌ Error scanning face.";
        showNotification("Error scanning face.", "error");
    }

    if (scanningActive) {
        requestAnimationFrame(scanFaceLoop);
    }
}

function getLocationAndSubmit(scheduleId) {
    if (!navigator.geolocation) return showNotification("Geolocation not supported.", "error");

    navigator.geolocation.getCurrentPosition(pos => {
        const userLat = pos.coords.latitude;
        const userLng = pos.coords.longitude;
        const acc = pos.coords.accuracy;

        if (acc > 10) {
            showNotification("GPS accuracy is low (" + acc + "m).", "error");
            return;
        }

        document.getElementById('lat-' + scheduleId).value = userLat;
        document.getElementById('lng-' + scheduleId).value = userLng;

        const roomLat = parseFloat(document.getElementById('room-lat-' + scheduleId).value);
        const roomLng = parseFloat(document.getElementById('room-lng-' + scheduleId).value);
        const dist = getDistanceInMeters(userLat, userLng, roomLat, roomLng);

        alert(`📍 Location Info:
- Your Position: (${userLat.toFixed(6)}, ${userLng.toFixed(6)})
- Room Position: (${roomLat.toFixed(6)}, ${roomLng.toFixed(6)})
- Distance: ${dist.toFixed(2)} meters`);

        if (dist <= 5) {
            showNotification("Checked in successfully!", "success");
            document.getElementById('checkin-form-' + scheduleId).submit();
        } else {
            showNotification("Outside allowed range. Distance: " + dist.toFixed(2) + "m", "error");
        }
    }, e => {
        showNotification("Location error: " + e.message, "error");
    }, { enableHighAccuracy:true, timeout:10000, maximumAge:0 });
}

function getDistanceInMeters(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2-lat1) * Math.PI/180;
    const dLng = (lng2-lng1) * Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
</script>

</x-app-layout>
