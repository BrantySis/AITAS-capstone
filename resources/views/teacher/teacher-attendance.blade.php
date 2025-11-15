@extends('layouts.mobile.mobile-app')

@section('header_title', 'Attendance')

@section('content')

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

    <style>
        .digital-time {
            color: #072340;
            background: #e6f1f8;
            border: 1px solid rgba(11,34,69,0.08);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            display: inline-block;
            font-size: 2.25rem;
            font-weight: 800;
            text-align: center;
        }
        .panel-1-outer {
            background: #17307a;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .panel-1-inner {
            background: #dbeeff;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 18px rgba(7,18,50,0.06);
        }
        .panel-1-left-bar { width: 10px; height: 56px; border-radius: 6px; background: #1e90ff; }
        .panel-1-subject { color: #072340; }
        .check-button-floating { position: absolute; right: 18px; bottom: 18px; }
        @media (max-width: 640px) {
            .digital-time { font-size: 1.9rem; min-width: 10rem; }
            .panel-1-inner { padding: 10px; }
            .check-button-floating { right: 12px; bottom: 12px; }
        }
        #map { height: 100%; width: 100%; }
    </style>

    <div class="p-4 grid grid-cols-1 gap-4">
        <div class="panel-1-outer relative" style="overflow: visible !important;">
            <div class="panel-1-inner">
                <div class="panel-1-left-bar"
                    @if(in_array($displayStatus, ['Attending','Ongoing','Late'])) style="background:#f59e0b"
                    @elseif($displayStatus === 'Upcoming') style="background:#2563eb"
                    @elseif($displayStatus === 'Attended') style="background:#16a34a"
                    @elseif($displayStatus === 'Missed') style="background:#dc2626"
                    @else style="background:#94a3b8"
                    @endif
                ></div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-700 mb-0">Current Class</p>
                    <h2 class="text-lg font-bold panel-1-subject">
                        {{ optional($schedule->subject)->subject_code ?? 'N/A' }} - {{ optional($schedule->subject)->subject_name ?? 'N/A' }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">{{ optional($schedule->room)->room_code ?? 'N/A' }}</p>
                </div>
                <div class="hidden sm:block" style="width:36px;"></div>
            </div>

            <div class="check-button-floating z-50">
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
                        <div class="flex flex-col items-end">
                            <p id="schedule-end-timer-{{ $schedule->id }}" data-time-left-ms="{{ $timeUntilEndMs }}" class="text-sm text-red-300 font-semibold mb-2">
                                Auto Check-Out in: {{ $timeUntilEndMs > 0 ? 'Calculating...' : 'Overdue' }}
                            </p>
                            <button type="button" id="action-btn-{{ $schedule->id }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-full transition flex items-center">Check Out</button>
                        </div>
                    @else
                        <button type="button" id="action-btn-{{ $schedule->id }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-full transition flex items-center">Check-In</button>
                    @endif
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 flex flex-col items-center">
            <p class="text-xs font-semibold text-gray-500 mb-2">Current Time (Asia/Manila)</p>
            <p id="current-time" class="digital-time">00:00:00 AM</p>
            <p id="current-date" class="text-sm text-gray-600 mt-2">January 1, 2025</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 flex flex-col space-y-2">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-700 font-medium">Time In</span>
                <span id="time-in-display" class="font-bold text-gray-900">
                    {{ optional($attendance)->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('g:i A') : 'Not Yet' }}
                </span>
            </div>
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <span class="text-gray-700 font-medium">Time Out</span>
                <span id="time-out-display" class="font-bold text-gray-900">
                    {{ optional($attendance)->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('g:i A') : 'Not Yet' }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700 font-medium">Status</span>
                <span id="status-{{ $schedule->id }}" class="font-bold text-sm
                    @if($displayStatus === 'Upcoming') text-blue-600
                    @elseif(in_array($displayStatus, ['Ongoing','Attending','Late'])) text-yellow-600
                    @elseif($displayStatus === 'Attended') text-green-600
                    @elseif($displayStatus === 'Missed') text-red-600
                    @else text-gray-600
                    @endif
                ">{{ $displayStatus }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 mt-4">
            <div class="p-4 pb-0">
                <p class="text-xs font-semibold text-gray-500 mb-2">Location Status</p>
            </div>
            <div class="h-64 w-full">
                <div id="map" class="h-full w-full"></div>
            </div>
            <div class="p-2 bg-gray-50 text-center">
                 <p id="gps-status" class="text-xs font-medium text-green-600">GPS Location Confirmed</p>
            </div>
        </div>
    </div>

@else
    <div class="p-4">
        <div class="bg-white rounded-xl shadow-lg p-4 mb-6 border border-gray-200 text-center">
            <p class="text-gray-500">You don't have any classes currently ongoing or upcoming today.</p>
        </div>
    </div>
@endif

<div id="face-scanner-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-[9999] p-4">
    <div class="bg-[#1a1a2e] rounded-xl shadow-2xl p-6 relative w-full max-w-sm text-white">
        <button id="closeFaceModalX" onclick="closeFaceScanner()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors"></button>
        <h3 class="text-xl font-bold text-center mb-1">Scan Your Face</h3>
        <p id="instructionText" class="text-sm text-center text-gray-400 mb-6">Center your face within the circle</p>
        <div class="relative w-64 h-64 mx-auto overflow-hidden rounded-full max-w-xs bg-gray-900">
            <video id="scanner-video" autoplay muted playsinline class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" style="object-position: center center;"></video>
            <canvas id="overlayCanvas" width="256" height="256" class="absolute inset-0 z-10"></canvas>
            <canvas id="captureCanvas" width="400" height="300" style="display:none;"></canvas>
        </div>
        <p id="scanner-message" class="mt-4 text-center text-sm font-medium h-6 text-green-400"></p>
        <div class="flex justify-center mt-6 space-x-4">
            <button id="registerFaceBtn" class="w-1/2 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed" onclick="startScannerCamera()">Start Scan</button>
            <button onclick="closeFaceScanner()" class="w-1/2 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg font-semibold transition duration-150">Cancel</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>

@if($schedule)
<script>
// ======= JS: Digital Clock =======
function updateDigitalClock() {
    const now = new Date();
    const manilaTimeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute:'2-digit', second:'2-digit', hour12: true, timeZone: 'Asia/Manila' });
    const manilaDateStr = now.toLocaleDateString('en-US', { month: 'long', day:'numeric', year:'numeric', timeZone: 'Asia/Manila' });
    document.getElementById('current-time').textContent = manilaTimeStr;
    document.getElementById('current-date').textContent = manilaDateStr;
}
setInterval(updateDigitalClock, 1000);
updateDigitalClock();

// ======= JS: Attendance, Face Scanner, GPS, Map =======
let scannerVideo, scannerStream, currentScheduleId, scanningActive=false, scanTimeout=null;
let map,userMarker,roomMarker,accuracyCircle,geoWatchId=null;
let autoCheckoutTimerGrace = null;
let autoCheckoutTimerSchedule = null;
const FASTAPI_URL = @json($fastapiUrl);

function showNotification(msg,type="success"){
    const banner=document.getElementById('notification-banner');
    const message=document.getElementById('notification-message');
    banner.className='fixed top-0 left-0 right-0 z-50 p-4 text-white text-center transition-opacity duration-300';
    banner.classList.add(type==="success"?"bg-green-500":type==="warning"?"bg-yellow-500":"bg-red-500");
    banner.style.opacity='1';message.textContent=msg;
    setTimeout(()=>{banner.style.opacity='0';setTimeout(()=>banner.classList.add('hidden'),300)},5000);
}

// Haversine Distance
function getDistanceInMeters(lat1,lng1,lat2,lng2){const R=6371000;const dLat=(lat2-lat1)*Math.PI/180;const dLng=(lng2-lng1)*Math.PI/180;const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));}

// Face Scanner
function openFaceScanner(scheduleId){
    currentScheduleId = scheduleId;
    document.getElementById('face-scanner-modal').classList.remove('hidden');
    startScannerCamera();
    if(map) map.invalidateSize(); // <-- force map redraw
}

function closeFaceScanner(){
    document.getElementById('face-scanner-modal').classList.add('hidden');
    scanningActive = false;
    if(scanTimeout) clearTimeout(scanTimeout);
    if(scannerStream){
        scannerStream.getTracks().forEach(t => t.stop());
        scannerStream = null;
    }
    if(scannerVideo){
        scannerVideo.pause();
        scannerVideo.srcObject = null;
    }
    if(map) map.invalidateSize(); // optional, ensures map remains correct after closing
}

async function startScannerCamera(){if(!scannerVideo)scannerVideo=document.getElementById('scanner-video');try{scannerStream=await navigator.mediaDevices.getUserMedia({video:true});scannerVideo.srcObject=scannerStream;await scannerVideo.play();scanningActive=true;scanTimeout=setTimeout(scanFaceLoop,1000);}catch(err){document.getElementById('scanner-message').textContent="❌ Camera error: "+err.message;}}
async function scanFaceLoop(){if(!scanningActive||!scannerStream)return;const canvas=document.createElement('canvas');canvas.width=scannerVideo.videoWidth||320;canvas.height=scannerVideo.videoHeight||240;canvas.getContext('2d').drawImage(scannerVideo,0,0,canvas.width,canvas.height);const blob=await new Promise(res=>canvas.toBlob(res,'image/jpeg',0.8));const formData=new FormData();formData.append("image",blob);document.getElementById('scanner-message').textContent="🔄 Sending image to server...";try{const res=await fetch(`${FASTAPI_URL}/recognize`,{method:"POST",body:formData});const data=await res.json();if(data.status==="success"&&data.match!=="Unknown"){document.getElementById('scanner-message').textContent="✅ Face verified!";closeFaceScanner();verifyLocationForCheckIn(currentScheduleId);return;}else{document.getElementById('scanner-message').textContent="❌ Match not found. Scanning again...";}}catch(err){document.getElementById('scanner-message').textContent="❌ Error: "+err.message;}if(scanningActive)scanTimeout=setTimeout(scanFaceLoop,3000);}

// Attendance Action
function handleAttendanceAction(scheduleId,isCheckout,posData=null){
    const submit=(pos)=>{
        const form=document.getElementById('attendance-form-'+scheduleId);
        const btn=document.getElementById('action-btn-'+scheduleId);
        const statusSpan=document.getElementById('status-'+scheduleId);
        const latInput=document.getElementById('lat-'+scheduleId);
        const lngInput=document.getElementById('lng-'+scheduleId);
        const checkoutStatusInput=document.getElementById('checkout-status-'+scheduleId);
        const attendanceIdInput=document.getElementById('attendance-id-'+scheduleId);
        const lastLatInput=document.getElementById('last-lat-'+scheduleId);
        const lastLngInput=document.getElementById('last-lng-'+scheduleId);
        let finalLat=pos&&pos.coords?pos.coords.latitude:null;
        let finalLng=pos&&pos.coords?pos.coords.longitude:null;
        if(isCheckout&&!pos&&(lastLatInput&&lastLngInput&&lastLatInput.value&&lastLngInput.value)){finalLat=lastLatInput.value;finalLng=lastLngInput.value;}
        if(finalLat&&latInput)latInput.value=finalLat;if(finalLng&&lngInput)lngInput.value=finalLng;if(checkoutStatusInput)checkoutStatusInput.value=isCheckout?'1':'0';
        btn.disabled=true;btn.textContent=isCheckout?'Checking Out...':'Checking In...';btn.classList.add('opacity-50');
        fetch(form.action,{method:'POST',body:new FormData(form)}).then(res=>res.json()).then(data=>{
            showNotification(data.message,data.status==='success'?'success':'error');btn.classList.remove('opacity-50');btn.disabled=false;
            if(data.status==='success'){
                if(!isCheckout){
                    if(statusSpan){statusSpan.textContent=data.check_in_status||'Attending';statusSpan.className='font-bold text-yellow-600 text-sm';}
                    if(autoCheckoutTimerSchedule){clearTimeout(autoCheckoutTimerSchedule);}
                    autoCheckoutTimerSchedule=setTimeout(()=>handleAttendanceAction(scheduleId,true),data.auto_checkout_delay_ms||0);
                } else {
                    if(statusSpan){statusSpan.textContent='Attended';statusSpan.className='font-bold text-green-600 text-sm';}
                    btn.disabled=true;btn.textContent='✅ Attended';btn.classList.add('cursor-not-allowed');
                }
            }
        }).catch(err=>{btn.classList.remove('opacity-50');btn.disabled=false;showNotification('Error: '+err.message,'error');});
    };
    if(!posData){navigator.geolocation.getCurrentPosition(submit,()=>submit(null),{enableHighAccuracy:true});}else submit(posData);
}

// Map Init
function initMap(){
    const mapEl=document.getElementById('map');
    if(!mapEl)return;
    const lat=parseFloat(document.getElementById('room-lat-{{ $schedule->id }}')?.value||0);
    const lng=parseFloat(document.getElementById('room-lng-{{ $schedule->id }}')?.value||0);
    map=L.map('map').setView([lat,lng],16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
    roomMarker=L.marker([lat,lng],{title:'Classroom'}).addTo(map);
    accuracyCircle=L.circle([lat,lng],{radius:50,color:'#34d399',fillOpacity:0.2}).addTo(map);
    if(navigator.geolocation){geoWatchId=navigator.geolocation.watchPosition(pos=>{const uLat=pos.coords.latitude;const uLng=pos.coords.longitude;if(userMarker){userMarker.setLatLng([uLat,uLng]);accuracyCircle.setLatLng([lat,lng]);}else{userMarker=L.marker([uLat,uLng],{title:'Your Location'}).addTo(map);}document.getElementById('last-lat-{{ $schedule->id }}').value=uLat;document.getElementById('last-lng-{{ $schedule->id }}').value=uLng;},err=>{console.warn('Geo error',err)},{enableHighAccuracy:true});}
}
document.addEventListener('DOMContentLoaded',()=>{initMap();document.getElementById('action-btn-{{ $schedule->id }}')?.addEventListener('click',()=>openFaceScanner({{ $schedule->id }}));});
</script>
@endif
@endsection
