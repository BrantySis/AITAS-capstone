@extends('layouts.mobile.mobile-app')

@section('header_title', 'Attendance')

@section('content')

{{-- Single Global Notification Banner --}}
<div id="notification-banner" class="fixed top-0 left-0 right-0 z-50 p-4 text-white text-center transition-opacity duration-300 hidden" style="opacity: 0;">
    <p id="notification-message" class="font-semibold"></p>
</div>

@php
    $schedule = $currentSchedule ?? null;
@endphp

@if($schedule)
    @php
        $attendance = $schedule->attendance ?? null;
        $displayStatus = $attendance->status ?? ($schedule->status ?? 'Upcoming');
        $is_checked_in_active = $attendance && $attendance->time_in && !$attendance->time_out;
        $is_checked_out = $attendance && $attendance->time_out;

        $endTime = \Carbon\Carbon::parse($schedule->ends_at);
        $timeUntilEndMs = now()->diffInMilliseconds($endTime, false);
    @endphp

    <div class="p-4 grid grid-cols-1 gap-4">

    {{-- Panel 1: Current Class --}}
    <div class="bg-blue-300 rounded-xl shadow-lg p-4 border border-gray-200 flex items-center">
        <div class="w-3 h-16 rounded mr-4
            @if(in_array($displayStatus, ['Attending','Ongoing','Late'])) bg-yellow-600
            @elseif($displayStatus === 'Upcoming') bg-blue-600
            @elseif($displayStatus === 'Attended') bg-green-600
            @elseif($displayStatus === 'Missed') bg-red-600
            @else bg-gray-400
            @endif
        "></div>
        <div>
            <p class="text-xs font-semibold text-gray-500 mb-1">Current Class</p>
            <h2 class="text-lg font-bold text-gray-900">
                {{ optional($schedule->subject)->subject_code ?? 'N/A' }} - {{ optional($schedule->subject)->subject_name ?? 'N/A' }}
            </h2>
            <p class="text-sm text-gray-600">{{ optional($schedule->room)->room_code ?? 'N/A' }}</p>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($schedule->starts_at)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->ends_at)->format('g:i A') }}
            </p>
        </div>
    

    {{-- Check-In / Check-Out Button directly below Panel 1 --}}
    <div class="flex justify-center">
         @if($is_checked_in_active)
                <p id="schedule-end-timer-{{ $schedule->id }}" data-time-left-ms="{{ $timeUntilEndMs }}" class="text-sm text-red-500 font-semibold mb-2">
                    Auto Check-Out in: {{ $timeUntilEndMs > 0 ? 'Calculating...' : 'Overdue' }}
                </p>
            @endif

            <form method="POST" action="{{ route('teacher.attendance.store') }}" id="attendance-form-{{ $schedule->id }}" class="action-form">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
            <input type="hidden" name="latitude" id="lat-{{ $schedule->id }}">
            <input type="hidden" name="longitude" id="lng-{{ $schedule->id }}">
            <input type="hidden" name="last_good_latitude" id="last-lat-{{ $schedule->id }}">
            <input type="hidden" name="last_good_longitude" id="last-lng-{{ $schedule->id }}">
            <input type="hidden" name="checkout" id="checkout-status-{{ $schedule->id }}" value="{{ $is_checked_in_active ? '1' : '0' }}">
            <input type="hidden" name="attendance_id" id="attendance-id-{{ $schedule->id }}" value="{{ optional($attendance)->id }}">
            <input type="hidden" id="room-lat-{{ $schedule->id }}" value="{{ optional($schedule->room)->latitude }}">
            <input type="hidden" id="room-lng-{{ $schedule->id }}" value="{{ optional($schedule->room)->longitude }}">

            @if($is_checked_out)
                <button type="button" class="bg-gray-400 text-white font-semibold py-2 px-4 rounded-full cursor-not-allowed" disabled>✅ Attended</button>
            @elseif($is_checked_in_active)
                <button type="button" id="action-btn-{{ $schedule->id }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-full transition flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3v-1"></path></svg>
                    Check Out
                </button>
            @else
                <button type="button" id="action-btn-{{ $schedule->id }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-full transition flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Check In
                </button>
            @endif
        </form>
        </div>
    </div>
    
    {{-- Panel 2: Current Time --}}
    <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 flex flex-col items-center">
        <!-- Label -->
        <p class="text-xs font-semibold text-gray-500 mb-1">Current Time</p>

        <!-- Manila Time -->
        @php
            $manilaTime = now()->setTimezone('Asia/Manila');
        @endphp
        <p class="text-lg font-bold text-gray-900">{{ $manilaTime->format('g:i:s A') }}</p>

        <!-- Date -->
        <p class="text-sm text-gray-600 mt-1">{{ $manilaTime->format('F j, Y') }}</p>
    </div>

       {{-- Panel 3: Time In / Time Out / Status --}}
<div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 flex flex-col space-y-2">
    <!-- Time In -->
    <div class="flex justify-between items-center">
        <span class="text-gray-700 font-medium">Time In</span>
        <span class="font-bold text-gray-900">
            {{ optional($attendance)->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('g:i A') : 'Not Yet' }}
        </span>
    </div>

    <!-- Time Out -->
    <div class="flex justify-between items-center">
        <span class="text-gray-700 font-medium">Time Out</span>
        <span class="font-bold text-gray-900">
            {{ optional($attendance)->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('g:i A') : 'Not Yet' }}
        </span>
    </div>

    <!-- Attendance Status -->
    <!-- Attendance Status -->
<div class="flex justify-between items-center">
    <span class="text-gray-700 font-medium">Status</span>
    <span id="status-{{ $schedule->id }}" class="font-bold text-sm
        @if($displayStatus === 'Upcoming') text-blue-600
        @elseif(in_array($displayStatus, ['Ongoing','Attending','Late'])) text-yellow-600
        @elseif($displayStatus === 'Attended') text-green-600
        @elseif($displayStatus === 'Missed') text-red-600
        @else text-gray-600
        @endif
    ">
        {{ $displayStatus }}
    </span>
    </div>
</div>


 {{-- Map Panel --}}
<div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 mt-4">
    <div class="h-64 w-full">
        <div id="map" class="h-full w-full"></div>
    </div>
</div>

@else
    <div class="p-4">
        <div class="bg-white rounded-xl shadow-lg p-4 mb-6 border border-gray-200 text-center">
            <p class="text-gray-500">You don't have any classes currently ongoing or upcoming today.</p>
        </div>
    </div>
@endif

{{-- Face Scanner Modal --}}
<div id="face-scanner-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm relative">
        <h3 class="text-xl font-bold mb-4 text-center">Face Scanner</h3>
        <div class="w-full aspect-video bg-gray-200 rounded-lg overflow-hidden mb-4">
            <video id="scanner-video" autoplay playsinline class="w-full h-full object-cover"></video>
        </div>
        <p id="scanner-message" class="text-sm text-center text-gray-700 font-medium">Initializing camera...</p>
        <button type="button" onclick="closeFaceScanner()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                 d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>

<pre id="gps-debug" class="bg-gray-100 p-2 rounded mt-4 text-xs overflow-auto max-w-full mx-auto" data-timer-start=''></pre>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Global variables
    let scannerVideo, scannerStream, currentScheduleId, scanningActive=false, scanTimeout=null;
    let map,userMarker,roomMarker,accuracyCircle,geoWatchId=null;
    let isLiveTracking = false;
    let autoCheckoutTimerGrace = null; // grace period timer when leaving room
    let autoCheckoutTimerSchedule = null; // schedule end auto-checkout timer
    const FASTAPI_URL = @json($fastapiUrl);

    // Helper to trigger auto check-out (on schedule end or grace expiry)
    const performAutoCheckout = (isScheduleEnd = false) => {
        const scheduleId = currentScheduleId;
        if (!scheduleId) return;

        const lastLatInput = document.getElementById('last-lat-' + scheduleId);
        const lastLngInput = document.getElementById('last-lng-' + scheduleId);
        const debugDiv = document.getElementById('gps-debug');

        let checkoutReason = isScheduleEnd
            ? "Scheduled end of class. Using last known good location."
            : "Grace period expired while outside room.";

        debugDiv.textContent = JSON.stringify({
            status: "Auto Check-Out Triggered",
            reason: checkoutReason,
            finalLat: lastLatInput?.value ? lastLatInput.value.substring(0, 10) : 'N/A',
            finalLng: lastLngInput?.value ? lastLngInput.value.substring(0, 10) : 'N/A'
        }, null, 2);

        stopTracking();

        // Use handleAttendanceAction to perform checkout; it will fallback to last-good location if pos null
        handleAttendanceAction(scheduleId, true, null);
    };

    // DOM ready restoration & auto-missed check
    document.addEventListener('DOMContentLoaded', () => {
        scannerVideo = document.getElementById('scanner-video');

        // Iterate each action-form (usually only one for currentSchedule)
        document.querySelectorAll('.action-form').forEach(form => {
            const scheduleId = form.querySelector('input[name="schedule_id"]').value;
            const attendanceId = document.querySelector('#attendance-id-' + scheduleId)?.value || '';
            const checkoutStatusInput = document.getElementById('checkout-status-' + scheduleId);
            const btn = document.getElementById('action-btn-' + scheduleId);
            const statusSpan = document.getElementById('status-' + scheduleId);
            const scheduleEndTimerDiv = document.getElementById(`schedule-end-timer-${scheduleId}`);

            // 1) Auto-Missed: if schedule controller already calculated 'Missed' and there's no attendance, auto-post
            if (statusSpan && statusSpan.textContent.includes('Missed') && (!attendanceId || attendanceId === '')) {
                console.log(`Auto-recording Missed attendance for schedule ${scheduleId}`);

                const missedInput = document.createElement('input');
                missedInput.type = 'hidden';
                missedInput.name = 'auto_missed';
                missedInput.value = '1';
                form.appendChild(missedInput);

                fetch(form.action, { method: 'POST', body: new FormData(form) })
                    .then(res => res.json())
                    .then(data => {
                        showNotification(data.message, data.status);
                        if (data.status === 'error' && btn) {
                            btn.textContent = '❌ Missed Deadline';
                            btn.classList.replace("bg-gray-500", "bg-red-600");
                            btn.disabled = true;
                        }
                    })
                    .catch(err => console.error("Failed to auto-record Missed attendance.", err));

                return; // skip other checks for this schedule
            }

            // 2) If already checked-in (restore state)
            if (attendanceId && checkoutStatusInput && checkoutStatusInput.value === '1') {
                currentScheduleId = scheduleId;

                if (btn) {
                    btn.textContent = "Check Out";
                    btn.classList.remove("bg-green-600");
                    btn.classList.add("bg-yellow-600");
                    btn.onclick = () => handleAttendanceAction(scheduleId, true);
                }
                if (statusSpan) {
                    statusSpan.textContent = 'Attending';
                    statusSpan.className = 'font-bold text-xs text-yellow-600';
                }

                // If schedule end timer exists, start it
                if (scheduleEndTimerDiv) {
                    const timeLeftMs = parseInt(scheduleEndTimerDiv.dataset.timeLeftMs);
                    if (!isNaN(timeLeftMs)) {
                        if (timeLeftMs <= 0) {
                            showNotification("Auto Check-Out triggered: Class time has passed on load.", "warning");
                            performAutoCheckout(true);
                            return;
                        } else {
                            initLiveTracking(scheduleId);
                            startScheduleEndTimer(scheduleId, timeLeftMs);
                        }
                    }
                } else {
                    // Fallback: start live tracking even without timer
                    initLiveTracking(scheduleId);
                }
            }

            // 3) If status is Ongoing and user not checked in, make button do face-scan / check-in
            else if (btn && statusSpan && (statusSpan.textContent.includes('Ongoing') || statusSpan.textContent.includes('Late')) && checkoutStatusInput && checkoutStatusInput.value === '0') {
                btn.textContent = "Check-In";
                btn.classList.replace("bg-yellow-600", "bg-green-600");
                btn.onclick = () => openFaceScanner(scheduleId);
            }
        });
    });

    // Notification helper
    function showNotification(msg, type = "success") {
        const banner = document.getElementById('notification-banner');
        const message = document.getElementById('notification-message');
        if (!banner || !message) {
            console.warn("Notification elements missing. Displaying alert.");
            alert(type.toUpperCase() + ": " + msg);
            return;
        }

        banner.classList.remove('bg-green-500', 'bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'hidden');
        if (type === "success" || type === "info") {
            banner.classList.add('bg-green-500');
        } else if (type === "warning") {
            banner.classList.add('bg-yellow-500');
        } else {
            banner.classList.add('bg-red-500');
        }

        message.textContent = msg;
        banner.style.opacity = '1';
        setTimeout(() => { banner.style.opacity = '0'; setTimeout(() => banner.classList.add('hidden'), 300); }, 5000);
    }

    // Haversine distance (meters)
    function getDistanceInMeters(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    // Stop tracking helper
    function stopTracking() {
        if (geoWatchId !== null) {
            navigator.geolocation.clearWatch(geoWatchId);
            geoWatchId = null;
        }
        if (autoCheckoutTimerGrace !== null) {
            clearTimeout(autoCheckoutTimerGrace);
            autoCheckoutTimerGrace = null;
        }
        if (autoCheckoutTimerSchedule !== null) {
            clearTimeout(autoCheckoutTimerSchedule);
            autoCheckoutTimerSchedule = null;
            const timerDiv = document.getElementById(`schedule-end-timer-${currentScheduleId}`);
            if (timerDiv) timerDiv.textContent = 'Auto Check-Out in: CANCELED';
        }

        isLiveTracking = false;
        const debugDiv = document.getElementById('gps-debug');
        if (debugDiv) debugDiv.textContent = 'Geolocation tracking stopped.';
    }

    // Face scanner open/close
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
        if (scannerStream) { scannerStream.getTracks().forEach(track => track.stop()); scannerStream = null; }
        if (scannerVideo) {
            scannerVideo.pause(); scannerVideo.srcObject = null; scannerVideo.removeAttribute("src"); scannerVideo.load();
        }
    }

    // Start camera for face scanner
    async function startScannerCamera() {
        if (!scannerVideo) scannerVideo = document.getElementById('scanner-video');
        try {
            scannerStream = await navigator.mediaDevices.getUserMedia({ video: true });
            scannerVideo.srcObject = scannerStream;
            await new Promise(res => { scannerVideo.onloadedmetadata = () => { scannerVideo.play(); res(); }; });
            scanningActive = true;
            scanTimeout = setTimeout(() => { if (scanningActive) scanFaceLoop(); }, 3000);
        } catch (err) {
            document.getElementById('scanner-message').textContent = "❌ Camera error: " + err.message;
        }
    }

    // Continual scanning loop - posts image to FASTAPI
    async function scanFaceLoop() {
        if (!scanningActive || !scannerStream) return;
        const canvas = document.createElement('canvas'); canvas.width = scannerVideo.videoWidth || 320; canvas.height = scannerVideo.videoHeight || 240;
        const ctx = canvas.getContext('2d'); ctx.drawImage(scannerVideo, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.8));
        const formData = new FormData(); formData.append("image", blob);

        document.getElementById('scanner-message').textContent = "🔄 Sending image to server for verification...";

        try {
            const res = await fetch(`${FASTAPI_URL}/recognize`, { method: "POST", body: formData });
            const data = await res.json();

            if (data.status === "success" && data.match !== "Unknown") {
                document.getElementById('scanner-message').textContent = "✅ Face verified! Initiating location check...";
                closeFaceScanner();
                verifyLocationForCheckIn(currentScheduleId);
                return;
            } else {
                document.getElementById('scanner-message').textContent = "❌ Match not found. Scanning again in 3s...";
            }
        } catch (err) {
            document.getElementById('scanner-message').textContent = "❌ Error scanning face: " + err.message;
            console.error("FastAPI Error:", err);
        }

        if (scanningActive) {
            scanTimeout = setTimeout(() => { if (scanningActive) scanFaceLoop(); }, 3000);
        }
    }

    // ------------------------
    // Attendance Action (check-in / check-out)
    // ------------------------
    function handleAttendanceAction(scheduleId, isCheckout, positionData = null) {
        const submitAttendance = (pos) => {
            const form = document.getElementById('attendance-form-' + scheduleId);
            const btn = document.getElementById('action-btn-' + scheduleId);
            const statusSpan = document.getElementById('status-' + scheduleId);

            const latInput = document.getElementById('lat-' + scheduleId);
            const lngInput = document.getElementById('lng-' + scheduleId);
            const checkoutStatusInput = document.getElementById('checkout-status-' + scheduleId);
            const attendanceIdInput = document.getElementById('attendance-id-' + scheduleId);

            const lastGoodLatInput = document.getElementById('last-lat-' + scheduleId);
            const lastGoodLngInput = document.getElementById('last-lng-' + scheduleId);

            if (!form || !btn || !statusSpan) {
                showNotification("Error: Class card not found on page. Please refresh.", "error");
                stopTracking();
                return;
            }

            // Determine final coordinates to submit
            let finalLat = pos && pos.coords ? pos.coords.latitude : null;
            let finalLng = pos && pos.coords ? pos.coords.longitude : null;

            // If checkout and no fresh GPS, fallback to last saved good location
            if (isCheckout && !pos) {
                if (lastGoodLatInput && lastGoodLngInput && lastGoodLatInput.value && lastGoodLngInput.value) {
                    finalLat = lastGoodLatInput.value;
                    finalLng = lastGoodLngInput.value;
                    console.log("Using last good location for check-out.");
                    showNotification("Submitting Check-Out using last verified location.", "info");
                } else {
                    console.warn("Attempting check-out without fresh GPS or last good location.");
                    showNotification("Check-Out without location data. Server will use check-in data.", "warning");
                }
            }

            if (finalLat && latInput) latInput.value = finalLat;
            if (finalLng && lngInput) lngInput.value = finalLng;
            if (checkoutStatusInput) checkoutStatusInput.value = isCheckout ? '1' : '0';

            btn.disabled = true;
            btn.textContent = isCheckout ? 'Checking Out...' : 'Checking In...';
            btn.classList.add('opacity-50');

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(res => res.json())
            .then(data => {
                // Show message (server returns 'status' + 'message')
                showNotification(data.message, data.status === 'success' ? 'success' : 'error');
                btn.classList.remove('opacity-50');
                btn.disabled = false;

                if (data.status === 'success') {
                    if (!isCheckout) {
                        // CHECK-IN SUCCESS
                        initLiveTracking(scheduleId);

                        // Use server-determined check_in_status for immediate display
                        const currentStatus = data.check_in_status || 'Attending';
                        let statusColorClass = 'text-yellow-600';
                        if (currentStatus === 'Late') statusColorClass = 'text-yellow-500';
                        if (statusSpan) {
                            statusSpan.textContent = currentStatus;
                            statusSpan.className = 'font-bold text-xs ' + statusColorClass;
                        }

                        // Update button to Check Out
                        btn.textContent = "Check Out";
                        btn.classList.replace("bg-green-600", "bg-yellow-600");
                        btn.onclick = () => handleAttendanceAction(scheduleId, true);
                        if (checkoutStatusInput) checkoutStatusInput.value = '1';
                        if (data.attendance_id && attendanceIdInput) attendanceIdInput.value = data.attendance_id;

                        // Start schedule end timer if present
                        const scheduleEndTimerDiv = document.getElementById(`schedule-end-timer-${scheduleId}`);
                        if (scheduleEndTimerDiv) {
                            const timeLeftMs = parseInt(scheduleEndTimerDiv.dataset.timeLeftMs);
                            if (!isNaN(timeLeftMs) && timeLeftMs > 0) {
                                startScheduleEndTimer(scheduleId, timeLeftMs);
                            }
                        }
                    } else {
                        // CHECK-OUT SUCCESS
                        stopTracking();

                        // Use server-provided final_status (AttendanceController returns message + status)
                        const finalStatus = data.final_status || (data.message && data.message.includes('Late') ? 'Late' : 'Attended');
                        let statusColorClass = 'text-green-600';
                        let buttonText = '✅ Attended';
                        if (finalStatus === 'Late') {
                            statusColorClass = 'text-red-500';
                            buttonText = '⚠️ Late';
                        }

                        // Update button & disable
                        btn.textContent = buttonText;
                        btn.classList.replace("bg-yellow-600", "bg-gray-400");
                        btn.disabled = true;
                        btn.onclick = null;

                        if (statusSpan) {
                            statusSpan.textContent = finalStatus;
                            statusSpan.className = 'font-bold text-xs ' + statusColorClass;
                        }

                        // Clear last good location
                        if (lastGoodLatInput) lastGoodLatInput.value = '';
                        if (lastGoodLngInput) lastGoodLngInput.value = '';
                    }
                } else {
                    // Not success — revert button text / style
                    btn.textContent = isCheckout ? 'Check Out' : 'Check In';
                    if (isCheckout) {
                        btn.classList.replace("bg-gray-400", "bg-yellow-600");
                    } else {
                        btn.classList.replace("bg-yellow-600", "bg-green-600");
                    }
                }
            })
            .catch(err => {
                showNotification("Error submitting attendance: " + err.message, "error");
                btn.classList.remove('opacity-50');
                btn.disabled = false;
                btn.textContent = isCheckout ? 'Check Out' : 'Check In';
                // best-effort class restore
                btn.classList.replace("bg-yellow-600", isCheckout ? "bg-yellow-600" : "bg-green-600");
            });
        };

        // if navigator not available, warn / bail
        if (!navigator.geolocation) {
            showNotification("Geolocation not supported.", "error");
            return;
        }

        // If this is checkout, try to get fresh GPS first otherwise fallback to last-known-location
        if (isCheckout) {
            navigator.geolocation.getCurrentPosition(submitAttendance, (err) => {
                console.warn("Manual Checkout GPS failed. Falling back to last good location or server check-in data.", err);
                submitAttendance(null);
            }, { enableHighAccuracy: true });
        } else {
            // For check-in we require a verified position (should come from verifyLocationForCheckIn)
            if (positionData) {
                showNotification("Location Verified. Checking In...", "info");
                submitAttendance(positionData);
            } else {
                showNotification("Critical Error: Check-In requires verified location data. Please use Face Scanner.", "error");
            }
        }
    }

    // Verify location (high-accuracy continuous check) before check-in
    function verifyLocationForCheckIn(scheduleId) {
        if (!navigator.geolocation) {
            showNotification("Geolocation not supported.", "error");
            return;
        }

        const roomLat = parseFloat(document.getElementById('room-lat-' + scheduleId).value);
        const roomLng = parseFloat(document.getElementById('room-lng-' + scheduleId).value);

        if (isNaN(roomLat) || isNaN(roomLng) || (roomLat === 0 && roomLng === 0)) {
            showNotification("Room coordinates are missing or invalid!", "error");
            return;
        }

        initializeMap(scheduleId, roomLat, roomLng);
        let checkedIn = false, initialPositionFound = false;
        showNotification("Acquiring high-accuracy location for Check-In (max 30s).", "info");

        if (geoWatchId !== null) navigator.geolocation.clearWatch(geoWatchId);

        geoWatchId = navigator.geolocation.watchPosition(pos => {
            const userLat = pos.coords.latitude, userLng = pos.coords.longitude, acc = pos.coords.accuracy;
            const dist = getDistanceInMeters(userLat, userLng, roomLat, roomLng);

            if (!initialPositionFound) { map.setView([userLat, userLng], 18); initialPositionFound = true; }
            updateMapMarkers(userLat, userLng, acc);

            const isAccurate = acc <= 1000; // loose initial check; final server uses 5m
            const isClose = dist <= 20;

            console.log("Room Coords:", roomLat, roomLng);
            console.log("User Coords:", userLat, userLng);
            console.log("Calculated Distance:", dist.toFixed(2) + "m");
            console.log("Check-In Conditions: isAccurate:", isAccurate, "isClose:", isClose);

            // If within a conservative radius and accuracy, proceed
            if (!checkedIn && isAccurate && isClose) {
                checkedIn = true;
                navigator.geolocation.clearWatch(geoWatchId);
                geoWatchId = null;
                handleAttendanceAction(scheduleId, false, pos);
            }
        }, err => {
            showNotification("Error verifying position: " + err.message, "error");
            navigator.geolocation.clearWatch(geoWatchId);
            geoWatchId = null;
        }, { enableHighAccuracy: true, maximumAge: 0, timeout: 30000 });
    }

    // ------------------------
    // Schedule end timer (visual + trigger)
    // ------------------------
    let displayInterval = null;
    function startScheduleEndTimer(id, timeLeftMs) {
        if (autoCheckoutTimerSchedule !== null) clearTimeout(autoCheckoutTimerSchedule);
        if (displayInterval !== null) clearInterval(displayInterval);

        const timerDiv = document.getElementById(`schedule-end-timer-${id}`);

        autoCheckoutTimerSchedule = setTimeout(() => {
            performAutoCheckout(true);
        }, timeLeftMs);

        const startTime = Date.now();

        displayInterval = setInterval(() => {
            const elapsedTime = Date.now() - startTime;
            const remaining = timeLeftMs - elapsedTime;

            if (remaining <= 0) {
                clearInterval(displayInterval);
                displayInterval = null;
                if (timerDiv) timerDiv.textContent = 'Auto Check-Out in: NOW';
                return;
            }

            const totalSeconds = Math.ceil(remaining / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            const timeStr = `${hours}h ${minutes}m ${seconds}s`;
            if (timerDiv) timerDiv.textContent = `Auto Check-Out in: ${timeStr}`;
        }, 1000);
    }

    // ------------------------
    // Live tracking & grace period
    // ------------------------
    function initLiveTracking(scheduleId) {
        if (!navigator.geolocation) {
            showNotification("Geolocation not supported for live tracking.", "error");
            return;
        }
        if (isLiveTracking) return;

        const roomLat = parseFloat(document.getElementById('room-lat-' + scheduleId).value);
        const roomLng = parseFloat(document.getElementById('room-lng-' + scheduleId).value);
        const lastLatInput = document.getElementById('last-lat-' + scheduleId);
        const lastLngInput = document.getElementById('last-lng-' + scheduleId);

        const OUTSIDE_DISTANCE = 10; // meters
        const REQUIRED_ACCURACY = 1000; // meters
        const GRACE_PERIOD_MS = 30 * 60 * 1000; // 30 seconds for testing; change to 30*60*1000 for prod

        if (isNaN(roomLat) || isNaN(roomLng) || (roomLat === 0 && roomLng === 0)) {
            showNotification("Cannot start live tracking: Room coordinates missing.", "error");
            return;
        }

        isLiveTracking = true;
        currentScheduleId = scheduleId;
        initializeMap(scheduleId, roomLat, roomLng);
        showNotification("Live location tracking active. Grace period for leaving location applies.", "info");

        if (geoWatchId !== null) navigator.geolocation.clearWatch(geoWatchId);

        geoWatchId = navigator.geolocation.watchPosition(pos => {
            const userLat = pos.coords.latitude, userLng = pos.coords.longitude, acc = pos.coords.accuracy;
            const dist = getDistanceInMeters(userLat, userLng, roomLat, roomLng);

            updateMapMarkers(userLat, userLng, acc);

            const debugDiv = document.getElementById('gps-debug');

            const isOutside = dist > OUTSIDE_DISTANCE;
            const isAccurate = acc <= REQUIRED_ACCURACY;

            const isLocationGood = !isOutside && isAccurate;
            if (isLocationGood && lastLatInput && lastLngInput) {
                lastLatInput.value = userLat;
                lastLngInput.value = userLng;
            }

            let logMessage = "In Range. Tracking Active.";
            let timerStatus = autoCheckoutTimerGrace ? `Running` : 'Inactive';

            if (isOutside && isAccurate) {
                if (autoCheckoutTimerGrace === null) {
                    debugDiv.dataset.timerStart = new Date().toISOString();
                    autoCheckoutTimerGrace = setTimeout(() => {
                        performAutoCheckout(false);
                    }, GRACE_PERIOD_MS);
                    logMessage = `OUTSIDE: Grace period STARTED (${Math.ceil(GRACE_PERIOD_MS / 1000)} sec).`;
                    showNotification("Warning: You have left the room. Auto check-out in 30 minutes if you don't return.", "warning");
                } else {
                    logMessage = "OUTSIDE: Grace period RUNNING.";
                }
            } else {
                if (autoCheckoutTimerGrace !== null) {
                    clearTimeout(autoCheckoutTimerGrace);
                    autoCheckoutTimerGrace = null;
                    debugDiv.dataset.timerStart = '';
                    logMessage = "BACK IN ROOM/ACCURACY LOW: Grace period CANCELLED.";
                    showNotification("Location restored. Auto check-out cancelled.", "success");
                } else {
                    logMessage = "In Range. Tracking Active.";
                }
            }

            debugDiv.textContent = JSON.stringify({
                status: logMessage,
                userLat: userLat.toFixed(6),
                userLng: userLng.toFixed(6),
                accuracy: acc.toFixed(2) + 'm',
                distanceToRoom: dist.toFixed(2) + 'm',
                isOutsideThreshold: isOutside,
                isAccurate: isAccurate,
                gracePeriodActive: autoCheckoutTimerGrace !== null,
                timerStatus: timerStatus,
                lastGoodLocation: `${lastLatInput.value ? lastLatInput.value.substring(0, 10) : 'N/A'},${lastLngInput.value ? lastLngInput.value.substring(0, 10) : 'N/A'}`
            }, null, 2);

        }, err => {
            showNotification("Live tracking error: " + err.message, "error");
            stopTracking();
        }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 30000 });
    }

    // ------------------------
    // Map helpers
    // ------------------------
    function initializeMap(scheduleId, lat, lng) {
        if (map) { map.remove(); map = null; }
        document.getElementById("map").classList.remove("hidden");
        map = L.map("map").setView([lat, lng], 18);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(map);
        roomMarker = L.marker([lat, lng]).addTo(map).bindPopup("Room Location").openPopup();
        userMarker = null;
        accuracyCircle = null;
    }

    function updateMapMarkers(lat, lng, accuracy) {
        if (userMarker) {
            userMarker.setLatLng([lat, lng]);
            if (accuracyCircle) accuracyCircle.setLatLng([lat, lng]).setRadius(accuracy);
        } else {
            userMarker = L.marker([lat, lng], {
                icon: L.icon({ iconUrl: "https://cdn-icons-png.flaticon.com/512/684/684908.png", iconSize: [32, 32] })
            }).addTo(map).bindPopup("You are here").openPopup();
            accuracyCircle = L.circle([lat, lng], { radius: accuracy, color: "blue", fillColor: "blue", fillOpacity: 0.1 }).addTo(map);
            map.setView([lat, lng], 18);
        }
        if (map) map.invalidateSize();
    }
</script>

@endsection
