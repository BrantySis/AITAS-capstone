@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Room Locations') {{-- Clearer Title --}}

@section('content')

@php
    $buildings = [
        'Basic Ed Building',
        'Old Building',
        'Annex Building',
        'Maritime Building',
        'CBE Building',
        'Field',
    ];
@endphp

<div class="p-4 md:p-6 lg:p-8 max-w-6xl mx-auto"> {{-- Added padding and max-width container --}}

    {{-- ===================== HEADER & ADD BUTTON ===================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
        <h2 class="text-3xl font-extrabold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 mr-3 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-2M5 21h2m2 0h6m2 0h-2M5 5h14M8 21v-4a3 3 0 013-3h2a3 3 0 013 3v4" />
            </svg>
            Room Locations
        </h2>

        {{-- Primary Action Button (Full width on mobile) --}}
        <button onclick="openAddModal()" 
            class="inline-block bg-blue-600 text-white px-5 py-2.5 rounded-xl hover:bg-blue-700 w-full md:w-auto text-center font-semibold shadow-lg transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add New Room
        </button>
    </div>

    {{-- ===================== MESSAGES ===================== --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-400 text-green-700 p-4 rounded-md mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        {{-- Added ID to facilitate error message targeting/clearing within modal JS if needed --}}
        <div id="pageErrorContainer" class="bg-red-100 border-l-4 border-red-400 text-red-700 p-4 rounded-md mb-6" role="alert">
            <p>**Validation Error:** Please check the form, especially if the **{{ session('modal_type', 'add') }}** modal reopened.</p>
        </div>
    @endif

    ---

    {{-- ========================================================================= --}}
    {{-- FILTERS: Search + Building --}}
    {{-- Responsive filter bar: stacks on mobile, side-by-side on tablet/desktop --}}
    {{-- ========================================================================= --}}
    <form method="GET" action="{{ route('admin.rooms.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-3 mb-8">
        {{-- Search Input (Stays full width until sm) --}}
        <div class="relative w-full sm:w-2/5">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by Room Code..." 
                value="{{ request('search') }}"
                class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-xl px-4 py-2.5 pl-10 text-base focus:outline-none transition shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-3 top-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z" />
            </svg>
        </div>

        {{-- Building Select --}}
        <select 
            name="building" 
            class="border-2 border-gray-300 focus:border-blue-500 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-full sm:w-2/5 md:w-1/4 bg-white transition">
            <option value="">-- All Buildings --</option>
            @foreach ($buildings as $building)
                <option value="{{ $building }}" {{ request('building') == $building ? 'selected' : '' }}>
                    {{ $building }}
                </option>
            @endforeach
        </select>

        {{-- Filter Button --}}
        <button type="submit" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md w-full sm:w-1/5 md:w-auto transition">
            Apply Filter
        </button>
    </form>

    ---

    ## 🏛️ Managed Rooms

    {{-- ------------------------------------------------------------------------- --}}
    {{-- RESPONSIVE GRID: 1 column on mobile, 2 on sm/tablet, 3 on desktop --}}
    {{-- ------------------------------------------------------------------------- --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($rooms as $room)
            <div class="bg-white p-5 shadow-xl rounded-xl border-t-4 border-blue-500 flex flex-col justify-between hover:shadow-2xl transition duration-200">
                
                {{-- ROOM HEADER --}}
                <div class="mb-4">
                    <div class="text-xs text-blue-600 font-bold uppercase tracking-widest mb-1">
                        {{ $room->building_name }}
                    </div>
                    <div class="text-2xl font-extrabold text-gray-900 truncate">
                        {{ $room->room_code }}
                    </div>
                </div>

                {{-- LOCATION INFO --}}
                <div class="text-sm text-gray-700 border-y border-gray-100 py-3 mb-4 space-y-1">
                    <p class="flex justify-between items-center"><span class="font-semibold text-gray-600">Latitude:</span> <span class="text-gray-900 font-medium">{{ number_format($room->latitude, 6) }}</span></p>
                    <p class="flex justify-between items-center"><span class="font-semibold text-gray-600">Longitude:</span> <span class="text-gray-900 font-medium">{{ number_format($room->longitude, 6) }}</span></p>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex justify-end gap-3 mt-auto">
                    <button 
                        type="button" 
                        onclick="openEditModal({{ json_encode($room) }})" 
                        class="text-sm text-blue-600 hover:text-blue-800 font-semibold px-3 py-1.5 rounded-lg border border-blue-100 bg-blue-50 transition">
                        <span class="md:hidden">Edit</span><span class="hidden md:inline">✏️ Edit</span>
                    </button>

                    <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                            class="text-sm text-red-600 hover:text-red-800 font-semibold px-3 py-1.5 rounded-lg border border-red-100 bg-red-50 transition"
                            onclick="return confirm('WARNING: Are you sure you want to delete room {{ $room->room_code }}? This cannot be undone.')">
                            <span class="md:hidden">Delete</span><span class="hidden md:inline">🗑️ Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-6 text-center text-gray-500 shadow-md rounded-lg border">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-8 h-8 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <p class="text-lg font-medium">No rooms found matching the current criteria.</p>
                <p class="text-sm">Try adjusting your search or building filter.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination (if available) --}}
    @if (method_exists($rooms ?? null, 'links'))
        <div class="mt-8">
            {{ $rooms->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- MODAL TEMPLATE (Improved Look & Feel) --}}
{{-- ========================================================================= --}}
    
@php
    $modalMapId = 'roomModalMap';
@endphp

<div id="roomManagerModal" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm z-50 hidden overflow-y-auto transition-opacity duration-300" aria-modal="true" role="dialog">
    {{-- Modal Content Container --}}
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white w-full mx-auto my-6 p-6 rounded-xl shadow-2xl max-w-lg transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-2xl font-bold text-gray-900" id="modalTitle"></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
            </div>

            <form id="roomManagerForm" method="POST" class="space-y-5">
                @csrf
                @method('POST')
                <input type="hidden" name="modal_open" value="1">
                <input type="hidden" name="modal_type" id="modalTypeInput" value="add">
                <input type="hidden" name="room_id_on_error" id="modalRoomIdOnError">

                {{-- Added Error Container for Geolocation/Validation feedback within modal --}}
                <div id="modalErrorContainer" class="hidden"></div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Room Code</label>
                    <input type="text" name="room_code" id="modal_room_code" 
                            value="" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 focus:outline-none transition" required>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Building Name</label>
                    <select name="building_name" id="modal_building_name" 
                        class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 focus:outline-none bg-white transition" required>
                        <option value="">-- Select Building --</option>
                        @foreach ($buildings as $building)
                            <option value="{{ $building }}">{{ $building }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Location Section --}}
                <div class="border-t pt-4">
                    <h4 class="font-bold text-lg text-gray-800 mb-3">Geographical Coordinates (GPS)</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Latitude</label>
                            <input type="text" name="latitude" id="modal_latitude" 
                                    value="" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Longitude</label>
                            <input type="text" name="longitude" id="modal_longitude" 
                                    value="" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 focus:outline-none transition">
                        </div>
                    </div>

                    <button type="button" onclick="getLocationModal()" class="mt-3 text-sm text-blue-600 font-medium hover:text-blue-800">
                        📍 Use My Current Location (High Accuracy)
                    </button>
                </div>

                <div id="{{ $modalMapId }}" class="w-full h-64 mt-4 rounded-lg shadow-inner border border-gray-200"></div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">
                        Cancel
                    </button>
                    <button type="submit" id="submitButton" class="px-5 py-2 rounded-lg shadow-md font-semibold transition"></button>
                </div>
            </form>
        </div>
    </div>
</div>
    
{{-- ========================================================================= --}}
{{-- MAP & MODAL SCRIPTS (Unchanged, as logic was already robust) --}}
{{-- ========================================================================= --}}
    
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let roomMap, roomMarker, roomCircle, geoWatchId = null;
    const accuracyThreshold = 10; // meters (Target accuracy for GeoLocation)
    const editCircleRadius = 4; // meters (Used for the Edit map circle)
    const defaultLat = 14.5995;   // Default/Fallback coordinates (Manila, Philippines)
    const defaultLng = 120.9842;
    const mapContainerId = '{{ $modalMapId }}';
    
    // Retrieve old input data once on page load
    window.oldInput = {!! json_encode(Session::getOldInput()) !!};

    // --- UTILITY: Update Form Coordinates ---
    function updateModalFormCoordinates(lat, lng) {
        document.getElementById('modal_latitude').value = lat.toFixed(6);
        document.getElementById('modal_longitude').value = lng.toFixed(6);
    }

    // --- MODAL CONTROL ---

    function setModalDefaults(isEdit, roomData = {}) {
        const form = document.getElementById('roomManagerForm');
        const submitButton = document.getElementById('submitButton');
        const modalTitle = document.getElementById('modalTitle');
        const modalTypeInput = document.getElementById('modalTypeInput');
        const modalRoomIdOnError = document.getElementById('modalRoomIdOnError');

        // 1. Set form action, method, and title
        if (isEdit) {
            form.action = `/admin/rooms/${roomData.id}`; 
            form.querySelector('input[name="_method"]').value = 'PUT';
            modalTitle.textContent = `Edit Room: ${roomData.room_code || 'Loading...'}`;
            submitButton.textContent = 'Update Room';
            submitButton.className = 'bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow-md font-semibold transition';
            modalTypeInput.value = 'edit';
            modalRoomIdOnError.value = roomData.id;
        } else {
            form.action = "{{ route('admin.rooms.store') }}";
            form.querySelector('input[name="_method"]').value = 'POST';
            modalTitle.textContent = 'Add New Room';
            submitButton.textContent = 'Save Room';
            submitButton.className = 'bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md font-semibold transition';
            modalTypeInput.value = 'add';
            modalRoomIdOnError.value = '';
        }

        // 2. Clear geolocation tracking if active
        if (geoWatchId !== null) {
            navigator.geolocation.clearWatch(geoWatchId);
            geoWatchId = null;
        }
        
        // Clear previous geo-messages
        document.getElementById('modalErrorContainer').innerHTML = '';
        document.getElementById('modalErrorContainer').classList.add('hidden');
    }

    function openModalTransition() {
        const modal = document.getElementById('roomManagerModal');
        const content = document.getElementById('modalContent');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 50); // Small delay to allow visibility change
    }

    function closeModal() {
        // Stop geolocation tracking
        if (geoWatchId !== null) {
            navigator.geolocation.clearWatch(geoWatchId);
            geoWatchId = null;
        }
        
        const modal = document.getElementById('roomManagerModal');
        const content = document.getElementById('modalContent');

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            // Clear old input only after successful submission/close
            window.oldInput = {}; 
        }, 300); // Wait for transition
    }


    function openAddModal() {
        setModalDefaults(false);

        let lat = parseFloat(window.oldInput.latitude || defaultLat);
        let lng = parseFloat(window.oldInput.longitude || defaultLng);
        
        document.getElementById('modal_room_code').value = window.oldInput.room_code || '';
        document.getElementById('modal_building_name').value = window.oldInput.building_name || '';
        updateModalFormCoordinates(lat, lng);

        initRoomMap(lat, lng, accuracyThreshold, true);
        openModalTransition();
    }

    function openEditModal(roomData) {
        setModalDefaults(true, { id: roomData.id, room_code: roomData.room_code });

        let lat = parseFloat(window.oldInput.latitude || roomData.latitude || defaultLat);
        let lng = parseFloat(window.oldInput.longitude || roomData.longitude || defaultLng);
        
        document.getElementById('modal_room_code').value = window.oldInput.room_code || roomData.room_code;
        document.getElementById('modal_building_name').value = window.oldInput.building_name || roomData.building_name || '';
        updateModalFormCoordinates(lat, lng);

        initRoomMap(lat, lng, editCircleRadius, true);
        openModalTransition();
    }

    // --- LEAFLET MAP LOGIC (Shared) ---
    function initRoomMap(lat, lng, radius, forceRecenter = false) {
        // Use requestAnimationFrame for safer map invalidation after modal opens
        requestAnimationFrame(() => { 
            const mapElement = document.getElementById(mapContainerId);
            if (!mapElement) return;

            if (!roomMap) {
                roomMap = L.map(mapContainerId, { zoomControl: true }).setView([lat, lng], 19);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(roomMap);
                
                roomMarker = L.marker([lat, lng], { draggable: true }).addTo(roomMap)
                    .bindPopup("Drag to adjust location").openPopup();

                roomCircle = L.circle([lat, lng], {
                    radius: radius,
                    color: 'blue',
                    fillColor: '#cce5ff',
                    fillOpacity: 0.3
                }).addTo(roomMap);

                // Map Event Listeners
                roomMarker.on('dragend', function (e) {
                    const pos = roomMarker.getLatLng();
                    updateModalFormCoordinates(pos.lat, pos.lng);
                    roomCircle.setLatLng(pos);
                });

                roomMap.on('click', function (e) {
                    roomMarker.setLatLng(e.latlng);
                    roomCircle.setLatLng(e.latlng);
                    updateModalFormCoordinates(e.latlng.lat, e.latlng.lng);
                });
            } else {
                roomMap.invalidateSize();
                roomMarker.setLatLng([lat, lng]);
                roomCircle.setLatLng([lat, lng]).setRadius(radius);
                
                if (forceRecenter) {
                    roomMap.setView([lat, lng], 19);
                }
            }
        });
    }

    // --- GEOLOCATION LOGIC ---

    function getLocationModal() {
        if (!navigator.geolocation) {
            console.error("Geolocation not supported by this browser.");
            // Display error in the modal's dedicated error container
            document.getElementById('modalErrorContainer').innerHTML = 
                `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mb-2">❌ Geolocation not supported by this browser.</div>`;
            document.getElementById('modalErrorContainer').classList.remove('hidden');
            return;
        }
        
        if (geoWatchId !== null) {
            navigator.geolocation.clearWatch(geoWatchId);
            geoWatchId = null;
        }

        // Display "Searching" message
        document.getElementById('modalErrorContainer').innerHTML = 
            `<div class="bg-blue-100 border border-blue-400 text-blue-700 p-3 rounded-lg mb-2">📡 Getting precise GPS position... Keep this modal open for the best result.</div>`;
        document.getElementById('modalErrorContainer').classList.remove('hidden');

        // Start watchPosition for continuous updates and high accuracy
        geoWatchId = navigator.geolocation.watchPosition(
            pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const acc = pos.coords.accuracy;

                updateModalFormCoordinates(lat, lng);
                initRoomMap(lat, lng, acc, true); // Update map, force recenter, use accuracy as circle radius

                if (acc <= accuracyThreshold) {
                    // Success: Accuracy target achieved
                    console.log(`✅ Accurate GPS acquired! Accuracy: ${acc.toFixed(1)}m`);
                    document.getElementById('modalErrorContainer').innerHTML = 
                        `<div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded-lg mb-2">✅ GPS acquired! Accuracy: **${acc.toFixed(1)}m**. You can now save.</div>`;

                    navigator.geolocation.clearWatch(geoWatchId);
                    geoWatchId = null;
                } else {
                    // Approximate position found, display feedback
                    console.log(`ℹ️ Approximate position found. Accuracy: ${acc.toFixed(1)}m. Waiting for better signal...`);
                    document.getElementById('modalErrorContainer').innerHTML = 
                        `<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 p-3 rounded-lg mb-2">ℹ️ Position found. Accuracy: **${acc.toFixed(1)}m**. Waiting for better signal...</div>`;
                }
            },
            err => {
                // Handle errors
                console.error("❌ Geolocation Error: " + err.message);
                document.getElementById('modalErrorContainer').innerHTML = 
                    `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mb-2">❌ Error: ${err.message}. Showing previous/default location.</div>`;
                
                // On error, set the form back to the previous/default coordinates
                const currentLat = parseFloat(document.getElementById('modal_latitude').value || defaultLat);
                const currentLng = parseFloat(document.getElementById('modal_longitude').value || defaultLng);

                updateModalFormCoordinates(currentLat, currentLng);
                initRoomMap(currentLat, currentLng, accuracyThreshold, true);

                navigator.geolocation.clearWatch(geoWatchId);
                geoWatchId = null;
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );
    }

    // --- INITIALIZATION ON PAGE LOAD ---

    document.addEventListener('DOMContentLoaded', function() {
        // Close modal on outside click
        document.getElementById('roomManagerModal').addEventListener('click', function(event) {
            // Check if the click occurred directly on the modal backdrop, not its content
            if (event.target === this) {
                closeModal();
            }
        });

        // Sticky logic to reopen the modal if validation failed
        if ('{{ session('modal_open') }}' === '1') {
            const modalType = '{{ session('modal_type') }}';
            
            const roomData = {
                id: modalType === 'edit' ? "{{ session('room_id_on_error') }}" : null,
                room_code: window.oldInput.room_code || '',
                building_name: window.oldInput.building_name || '',
                latitude: window.oldInput.latitude || defaultLat, 
                longitude: window.oldInput.longitude || defaultLng
            };
            
            if (modalType === 'edit' && roomData.id) {
                openEditModal(roomData);
            } else if (modalType === 'add') {
                openAddModal();
            }
        }
        
        // Delete confirmation logic (Using native confirm for simplicity as before)
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!window.confirm('Are you sure you want to delete this room? This cannot be undone.')) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endsection