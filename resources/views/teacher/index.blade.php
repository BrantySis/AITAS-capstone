@php
use App\Models\Attendance;
use Carbon\Carbon;
$now = Carbon::now('Asia/Manila');
$todayDateString = $now->toDateString();
@endphp

<x-app-layout>
<div class="max-w-4xl mx-auto p-6">

<!-- Notification -->
<div id="notification-banner" class="hidden fixed top-4 right-4 z-50 p-4 rounded-lg shadow-xl text-white font-medium transition-opacity duration-300" role="alert">
    <span id="notification-message"></span>
</div>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('dashboard.teacher') }}" 
       class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
    <h2 class="flex-grow text-2xl font-bold text-center text-blue-700">Upcoming Schedule</h2>
</div>

<!-- Schedules -->
@if($schedules->isEmpty())
    <p class="text-center text-gray-500">You don't have any upcoming schedules for now.</p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 bg-white text-sm shadow-md rounded-lg">
            <thead class="bg-blue-100 text-blue-800 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left border-b">Subject</th>
                    <th class="px-6 py-3 text-left border-b">Time</th>
                    <th class="px-6 py-3 text-left border-b">Room</th>
                    <th class="px-6 py-3 text-left border-b">Date</th>
                    <th class="px-6 py-3 text-left border-b">Status</th>
                    <th class="px-6 py-3 text-left border-b">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach($schedules as $schedule)
                @php
                    $attendance = Attendance::where('schedule_id', $schedule->id)
                        ->where('user_id', auth()->id())
                        ->latest()
                        ->first();

                    $scheduleStart = Carbon::parse($schedule->starts_at)->setTimezone('Asia/Manila');
                    $scheduleEnd = Carbon::parse($schedule->ends_at)->setTimezone('Asia/Manila');

                    $is_checked_in = $attendance && $attendance->time_in && !$attendance->time_out;
                    $is_checked_out = $attendance && $attendance->time_out;

                    if ($is_checked_out) {
                        $status = 'Attended';
                    } elseif ($is_checked_in) {
                        $status = 'Attending';
                    } elseif ($now->lt($scheduleStart)) {
                        $status = 'Upcoming';
                    } elseif ($now->between($scheduleStart, $scheduleEnd)) {
                        $status = 'Ongoing';
                    } else {
                        $status = 'Missed';
                    }
                @endphp

                <tr class="border-b hover:bg-gray-50" id="row-{{ $schedule->id }}">
                    <td class="px-6 py-4">{{ $schedule->subject->subject_name ?? 'Unknown Subject' }}</td>
                    <td class="px-6 py-4">
                        {{ Carbon::parse($schedule->starts_at)->format('g:i A') }} -
                        {{ Carbon::parse($schedule->ends_at)->format('g:i A') }}
                    </td>
                    <td class="px-6 py-4">{{ optional($schedule->room)->room_code ?? 'No Room' }}</td>
                    <td class="px-6 py-4">{{ Carbon::parse($schedule->starts_at)->format('F j, Y') }}</td>

                    <!-- Status -->
                    <td class="px-6 py-4 font-semibold">
                        <span id="status-{{ $schedule->id }}" class="
                            @if($status === 'Upcoming') text-blue-600
                            @elseif($status === 'Ongoing' || $status === 'Attending') text-yellow-600
                            @elseif($status === 'Attended') text-green-600
                            @elseif($status === 'Missed') text-red-600
                            @else text-gray-600
                            @endif
                        ">
                            {{ $status }}
                        </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 space-y-2">
                        <form method="POST" action="{{ route('teacher.attendance.store') }}" id="attendance-form-{{ $schedule->id }}" class="action-form">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                            <input type="hidden" name="latitude" id="lat-{{ $schedule->id }}">
                            <input type="hidden" name="longitude" id="lng-{{ $schedule->id }}">
                            <input type="hidden" name="checkout" id="checkout-status-{{ $schedule->id }}" value="{{ $is_checked_in ? '1' : '0' }}">
                            <input type="hidden" name="attendance_id" id="attendance-id-{{ $schedule->id }}" value="{{ optional($attendance)->id }}">
                            <input type="hidden" id="room-lat-{{ $schedule->id }}" value="{{ optional($schedule->room)->latitude }}">
                            <input type="hidden" id="room-lng-{{ $schedule->id }}" value="{{ optional($schedule->room)->longitude }}">

                            @if($status === 'Upcoming' || $status === 'Missed')
                                <button type="button" class="w-full rounded px-3 py-1 text-white cursor-not-allowed @if($status === 'Upcoming') bg-blue-500 @else bg-red-500 @endif" disabled>
                                    {{ $status === 'Upcoming' ? 'Not yet started' : '❌ Missed' }}
                                </button>
                            @elseif($is_checked_out)
                                <button type="button" class="w-full rounded bg-gray-400 px-3 py-1 text-white cursor-not-allowed" disabled>
                                    ✅ Attended
                                </button>
                            @elseif($is_checked_in)
                                <button type="button" id="action-btn-{{ $schedule->id }}" onclick="handleAttendanceAction({{ $schedule->id }}, true)" class="w-full rounded bg-yellow-600 px-3 py-1 text-white hover:bg-yellow-700 transition duration-150">
                                    Check Out
                                </button>
                            @elseif($status === 'Ongoing')
                                <button type="button" id="action-btn-{{ $schedule->id }}" onclick="openFaceScanner({{ $schedule->id }})" class="w-full rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700 transition duration-150">
                                    Check In
                                </button>
                            @endif
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
    <div class="bg-white p-6 rounded-lg w-96 text-center shadow-2xl">
        <h3 class="text-xl font-bold mb-4 text-blue-700">Face Verification</h3>
        <video id="scanner-video" autoplay playsinline class="w-full h-64 rounded-lg border-4 border-gray-200 mb-4 shadow-inner"></video>
        <p id="scanner-message" class="text-gray-700 mb-4 font-semibold">Please align your face in front of the camera...</p>
        <button onclick="closeFaceScanner()" class="bg-red-600 text-white px-4 py-2 rounded-full hover:bg-red-700 transition duration-150 shadow-md">Cancel</button>
    </div>
</div>

<!-- Leaflet Map -->
<div id="map" class="mt-6 hidden h-64 w-full rounded-lg shadow"></div>
<pre id="gps-debug" class='bg-gray-100 p-2 rounded mt-4 text-xs overflow-auto max-w-4xl mx-auto'></pre>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let scannerVideo, scannerStream, currentScheduleId, scanningActive=false, scanTimeout=null;
let map,userMarker,roomMarker,accuracyCircle,geoWatchId=null;
const FASTAPI_URL = @json($fastapiUrl);

document.addEventListener('DOMContentLoaded',()=>{
    // Initialize variables
    scannerVideo=document.getElementById('scanner-video');

    // Restore checked-in buttons after page reload
    document.querySelectorAll('.action-form').forEach(form=>{
        const scheduleId=form.querySelector('input[name="schedule_id"]').value;
        const attendanceId=form.querySelector('#attendance-id-'+scheduleId).value;
        const checkoutStatus=form.querySelector('#checkout-status-'+scheduleId).value;

        if(attendanceId && checkoutStatus==='1'){
            const btn=document.getElementById('action-btn-'+scheduleId);
            if(btn){
                btn.textContent="Check Out";
                btn.classList.remove("bg-green-600");
                btn.classList.add("bg-yellow-600");
                btn.onclick=()=>handleAttendanceAction(scheduleId,true);
            }
        }
    });
});

// --- Notification ---
function showNotification(msg,type="success"){
    const banner=document.getElementById('notification-banner');
    const message=document.getElementById('notification-message');
    banner.classList.remove('bg-green-500','bg-red-500','hidden');
    banner.classList.add(type==="success"||type==="info"?'bg-green-500':'bg-red-500');
    message.textContent=msg;
    banner.style.opacity='1';
    setTimeout(()=>{banner.style.opacity='0'; setTimeout(()=>banner.classList.add('hidden'),300)},5000);
}

// --- Distance ---
function getDistanceInMeters(lat1,lng1,lat2,lng2){
    const R=6371000,dLat=(lat2-lat1)*Math.PI/180,dLng=(lng2-lng1)*Math.PI/180;
    const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}

// --- Stop Tracking ---
function stopTracking(){ 
    if(geoWatchId!==null){ navigator.geolocation.clearWatch(geoWatchId); geoWatchId=null; }
    if(map) document.getElementById("map").classList.add("hidden");
    const debugDiv=document.getElementById('gps-debug'); if(debugDiv) debugDiv.textContent='Geolocation tracking stopped.';
}

// --- Face Scanner ---
function openFaceScanner(scheduleId){
    currentScheduleId=scheduleId;
    document.getElementById('scanner-message').textContent="Please align your face in front of the camera...";
    document.getElementById('face-scanner-modal').classList.remove('hidden');
    startScannerCamera();
}
function closeFaceScanner(){
    document.getElementById('face-scanner-modal').classList.add('hidden');
    scanningActive=false;
    if(scanTimeout) clearTimeout(scanTimeout);
    if(scannerStream){scannerStream.getTracks().forEach(track=>track.stop());scannerStream=null;}
    scannerVideo.pause();scannerVideo.srcObject=null;scannerVideo.removeAttribute("src");scannerVideo.load();
}
async function startScannerCamera(){
    try{
        scannerStream=await navigator.mediaDevices.getUserMedia({video:true});
        scannerVideo.srcObject=scannerStream;
        await new Promise(res=>{scannerVideo.onloadedmetadata=()=>{scannerVideo.play();res();}});
        scanningActive=true;
        scanTimeout=setTimeout(()=>{if(scanningActive) scanFaceLoop();},3000);
    }catch(err){document.getElementById('scanner-message').textContent="❌ Camera error: "+err.message;}
}
async function scanFaceLoop(){
    if(!scanningActive||!scannerStream) return;
    const canvas=document.createElement('canvas'); canvas.width=scannerVideo.videoWidth||320; canvas.height=scannerVideo.videoHeight||240;
    const ctx=canvas.getContext('2d'); ctx.drawImage(scannerVideo,0,0,canvas.width,canvas.height);
    const blob=await new Promise(res=>canvas.toBlob(res,'image/jpeg',0.8));
    const formData=new FormData(); formData.append("image",blob);

    try{
        const res=await fetch(`${FASTAPI_URL}/recognize`,{method:"POST",body:formData});
        const data=await res.json();
        if(data.status==="success" && data.match!=="Unknown"){
            document.getElementById('scanner-message').textContent="✅ Face verified!";
            closeFaceScanner();
            initHighAccuracyTracking(currentScheduleId);
            return;
        }else{
            document.getElementById('scanner-message').textContent="🔄 Scanning face...";
        }
    }catch(err){document.getElementById('scanner-message').textContent="❌ Error scanning face: "+err.message;}
    if(scanningActive) requestAnimationFrame(scanFaceLoop);
}

// --- Attendance Action ---
function handleAttendanceAction(scheduleId,isCheckout,positionData=null){
    const submitAttendance=(pos)=>{
        const form=document.getElementById('attendance-form-'+scheduleId);
        const btn=document.getElementById('action-btn-'+scheduleId);
        const statusSpan=document.getElementById('status-'+scheduleId);
        document.getElementById('lat-'+scheduleId).value=pos.coords.latitude;
        document.getElementById('lng-'+scheduleId).value=pos.coords.longitude;
        document.getElementById('checkout-status-'+scheduleId).value=isCheckout?'1':'0';

        btn.disabled=true; btn.textContent=isCheckout?'Checking Out...':'Checking In...'; btn.classList.add('opacity-50');

        fetch(form.action,{method:'POST',body:new FormData(form)})
            .then(res=>res.json())
            .then(data=>{
                showNotification(data.message,data.status); btn.classList.remove('opacity-50'); btn.disabled=false;

                if(data.status==='success'){
                    if(!isCheckout){
                        btn.textContent="Check Out"; btn.classList.replace("bg-green-600","bg-yellow-600");
                        btn.onclick=()=>handleAttendanceAction(scheduleId,true);
                        document.getElementById('checkout-status-'+scheduleId).value='1';
                        if(data.attendance_id) document.getElementById('attendance-id-'+scheduleId).value=data.attendance_id;
                        statusSpan.textContent='Attending'; statusSpan.className='text-yellow-600';
                    }else{
                        btn.textContent="✅ Attended"; btn.classList.replace("bg-yellow-600","bg-gray-400"); btn.disabled=true; btn.onclick=null;
                        statusSpan.textContent='Attended'; statusSpan.className='text-green-600';
                    }
                }else{
                    btn.textContent=isCheckout?'Check Out':'Check In';
                    if(isCheckout){btn.classList.replace("bg-green-600","bg-yellow-600");}else{btn.classList.replace("bg-yellow-600","bg-green-600");}
                }
            }).catch(err=>{showNotification("Error submitting attendance: "+err.message,"error"); btn.classList.remove('opacity-50'); btn.disabled=false; btn.textContent=isCheckout?'Check Out':'Check In'; btn.classList.replace("bg-yellow-600",isCheckout?"bg-yellow-600":"bg-green-600");});
    };

    if(!navigator.geolocation){ showNotification("Geolocation not supported.","error"); return; }

    if(isCheckout){navigator.geolocation.getCurrentPosition(submitAttendance, err=>showNotification("Location error for checkout: "+err.message,"error"),{enableHighAccuracy:true});}
    else{
        if(positionData){submitAttendance(positionData);}
        else{showNotification("Critical Error: Check-In requires verified location data. Please use Face Scanner.","error");}
    }
}

// --- High Accuracy Tracking ---
function initHighAccuracyTracking(scheduleId){
    if(!navigator.geolocation){showNotification("Geolocation not supported.","error"); return;}
    const roomLat=parseFloat(document.getElementById('room-lat-'+scheduleId).value);
    const roomLng=parseFloat(document.getElementById('room-lng-'+scheduleId).value);
    const mapElement=document.getElementById("map");
    if(map){map.remove(); map=null;}
    mapElement.classList.remove("hidden");
    map=L.map("map").setView([roomLat,roomLng],18);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:19}).addTo(map);
    roomMarker=L.marker([roomLat,roomLng]).addTo(map).bindPopup("Room Location").openPopup();
    let checkedIn=false,initialPositionFound=false;
    showNotification("Acquiring high-accuracy location... Please remain still.","info");

    geoWatchId=navigator.geolocation.watchPosition(pos=>{
        if(!initialPositionFound){map.setView([pos.coords.latitude,pos.coords.longitude],18); initialPositionFound=true;}
        const userLat=pos.coords.latitude,userLng=pos.coords.longitude,acc=pos.coords.accuracy,dist=getDistanceInMeters(userLat,userLng,roomLat,roomLng);
        const debugDiv=document.getElementById('gps-debug');
        debugDiv.textContent=JSON.stringify({scheduleId,userLat,userLng,accuracy:acc.toFixed(2)+'m',distanceToRoom:dist.toFixed(2)+'m',isAccurate:acc<=200,isClose:dist<=5,checkedIn},null,2);
        if(userMarker){userMarker.setLatLng([userLat,userLng]); accuracyCircle.setLatLng([userLat,userLng]).setRadius(acc);}
        else{userMarker=L.marker([userLat,userLng],{icon:L.icon({iconUrl:"https://cdn-icons-png.flaticon.com/512/684/684908.png",iconSize:[32,32]})}).addTo(map).bindPopup("You are here").openPopup();
              accuracyCircle=L.circle([userLat,userLng],{radius:acc,color:"blue",fillColor:"blue",fillOpacity:0.1}).addTo(map);}
        if(!checkedIn && acc<=200 && dist<=5){checkedIn=true; stopTracking(); handleAttendanceAction(scheduleId,false,pos);}
    },err=>showNotification("Error tracking position: "+err.message,"error"),{enableHighAccuracy:true,maximumAge:0,timeout:30000});
}
</script>
</x-app-layout>
