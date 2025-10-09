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
let map, userMarker, roomMarker, accuracyCircle;
let geoWatchId = null;

const FASTAPI_URL = @json($fastapiUrl);

// ✅ Debug panel initialization
function initDebugPanel() {
    let debugPanel = document.getElementById("debug-panel");
    if (!debugPanel) {
        debugPanel = document.createElement("div");
        debugPanel.id = "debug-panel";
        debugPanel.style.position = "fixed";
        debugPanel.style.bottom = "10px";
        debugPanel.style.right = "10px";
        debugPanel.style.width = "280px";
        debugPanel.style.maxHeight = "40vh";
        debugPanel.style.overflowY = "auto";
        debugPanel.style.background = "rgba(0,0,0,0.8)";
        debugPanel.style.color = "white";
        debugPanel.style.fontSize = "12px";
        debugPanel.style.padding = "10px";
        debugPanel.style.borderRadius = "8px";
        debugPanel.style.zIndex = "9999";
        debugPanel.innerHTML = "<b>📡 Debug Panel</b><hr style='border-color:white'>";
        document.body.appendChild(debugPanel);
    }
    return debugPanel;
}

function logDebug(msg) {
    const panel = initDebugPanel();
    const line = document.createElement("div");
    line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
    panel.appendChild(line);
    panel.scrollTop = panel.scrollHeight;
}

function showNotification(message, type = "success") {
    if (type === "success") alert("✅ " + message);
    else if (type === "error") alert("❌ " + message);
    else alert("ℹ️ " + message);
    logDebug(`${type.toUpperCase()}: ${message}`);
}

// ---------- FACE SCANNER ----------
function openFaceScanner(scheduleId) {
    currentScheduleId = scheduleId;
    document.getElementById('scanner-message').textContent = "Please align your face in front of the camera...";
    document.getElementById('face-scanner-modal').classList.remove('hidden');
    startScannerCamera();
}

function closeFaceScanner() {
    document.getElementById('face-scanner-modal').classList.add('hidden');
    scanningActive = false; 

    if (scanTimeout) clearTimeout(scanTimeout);
    if (scannerStream) {
        scannerStream.getTracks().forEach(track => track.stop());
        scannerStream = null;
    }

    scannerVideo.pause();
    scannerVideo.srcObject = null;
    scannerVideo.removeAttribute("src");
    scannerVideo.load();
    logDebug("🧠 Face scanner closed");
}

async function startScannerCamera() {
    try {
        scannerStream = await navigator.mediaDevices.getUserMedia({ video: true });
        scannerVideo.srcObject = scannerStream;
        await new Promise(res => {
            scannerVideo.onloadedmetadata = () => {
                scannerVideo.play();
                res();
            };
        });

        scanningActive = true;
        scanTimeout = setTimeout(() => {
            if (scanningActive) scanFaceLoop();
        }, 3000);

        logDebug("📸 Camera started successfully");
    } catch(err) {
        document.getElementById('scanner-message').textContent = "❌ Camera error: " + err;
        logDebug("Camera error: " + err);
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

        if (data.status === "success" && data.match !== "Unknown") {
            document.getElementById('scanner-message').textContent = "✅ Face verified!";
            showNotification("Face verified successfully! Checking location...", "success");
            logDebug(`✅ Face verified for ${data.match}`);
            closeFaceScanner();
            initHighAccuracyTracking(currentScheduleId);
            return;
        } else {
            document.getElementById('scanner-message').textContent = "🔄 Scanning face...";
            logDebug("Scanning face... no match yet");
        }
    } catch(err) {
        document.getElementById('scanner-message').textContent = "❌ Error scanning face.";
        showNotification("Error scanning face.", "error");
        logDebug("Error scanning face: " + err);
    }

    if (scanningActive) requestAnimationFrame(scanFaceLoop);
}

// ---------- HIGH ACCURACY GEO ----------
function initHighAccuracyTracking(scheduleId) {
    if (!navigator.geolocation) {
        showNotification("Geolocation not supported.", "error");
        return;
    }

    const roomLat = parseFloat(document.getElementById('room-lat-' + scheduleId).value);
    const roomLng = parseFloat(document.getElementById('room-lng-' + scheduleId).value);

    logDebug(`🎯 Starting GPS tracking for schedule ${scheduleId} (room: ${roomLat}, ${roomLng})`);

    const mapElement = document.getElementById("map");
    mapElement.classList.remove("hidden");

    if (!map) {
        map = L.map("map").setView([roomLat, roomLng], 18);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(map);
    }

    if (roomMarker) map.removeLayer(roomMarker);
    roomMarker = L.marker([roomLat, roomLng]).addTo(map).bindPopup("Room Location").openPopup();

    let checkedIn = false;

    geoWatchId = navigator.geolocation.watchPosition(pos => {
        const userLat = pos.coords.latitude;
        const userLng = pos.coords.longitude;
        const acc = pos.coords.accuracy;
        const dist = getDistanceInMeters(userLat, userLng, roomLat, roomLng);

        if (userMarker) {
            userMarker.setLatLng([userLat, userLng]);
            accuracyCircle.setLatLng([userLat, userLng]).setRadius(acc);
        } else {
            userMarker = L.marker([userLat, userLng], {
                icon: L.icon({
                    iconUrl: "https://cdn-icons-png.flaticon.com/512/684/684908.png",
                    iconSize: [32, 32]
                })
            }).addTo(map).bindPopup("You are here");
            accuracyCircle = L.circle([userLat, userLng], { radius: acc, color: "blue", fillOpacity: 0.2 }).addTo(map);
        }

        map.setView([userLat, userLng], 18);

        logDebug(`GPS → Lat:${userLat.toFixed(6)} Lng:${userLng.toFixed(6)} | Accuracy:${acc.toFixed(1)}m | Dist:${dist.toFixed(2)}m`);

        if (acc <= 10 && dist <= 5 && !checkedIn) {
            checkedIn = true;
            showNotification("Checked in successfully!", "success");
            logDebug("✅ Within 5m and accurate — submitting form!");
            stopTracking();

            mapElement.classList.add("hidden");
            const btn = document.getElementById('checkin-btn-' + scheduleId);
            if (btn) {
                btn.disabled = true;
                btn.textContent = "✅ Attended";
                btn.classList.remove("bg-blue-600");
                btn.classList.add("bg-green-600");
            }

            document.getElementById('checkin-form-' + scheduleId).submit();
        } else if (!checkedIn) {
            if (acc > 10) logDebug("⚠️ Waiting for better accuracy...");
            else logDebug(`📍 Too far: ${dist.toFixed(2)}m`);
        }

    }, err => {
        showNotification("Location error: " + err.message, "error");
        logDebug("Location error: " + err.message);
        stopTracking();
    }, {
        enableHighAccuracy: true,
        timeout: 20000,
        maximumAge: 0
    });
}

function stopTracking() {
    if (geoWatchId !== null) {
        navigator.geolocation.clearWatch(geoWatchId);
        geoWatchId = null;
        logDebug("🛑 Stopped GPS tracking");
    }
}

function getDistanceInMeters(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}
</script>
</x-app-layout>
