@extends('layouts.mobile.mobile-loginSignupUI')

@section('title', 'UCLM Register')

@section('content')

    {{-- RESPONSIVE CARD WRAPPER: Matches Login Card --}}
    <div class="z-10 bg-white rounded-xl shadow-2xl p-6 w-11/12 max-w-xs mx-auto text-center">
        
        {{-- Logo and Title Block: Matches Login Block --}}
        <div class="mb-6">
            <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-20 -mt-4">
            <h2 class="text-[9px] text-blue-700 font-extrabold -mt-4">LAPU-LAPU AND MANDAUE</h2>
            <div class="flex items-center mt-4 mb-4">
                <span class="flex-1 border-t border-gray-300 mr-3"></span>
                <span class="text-sm text-gray-500 font-medium whitespace-nowrap">WEB PORTAL - REGISTER</span>
                <span class="flex-1 border-t border-gray-300 ml-3"></span>
            </div>
        </div>

        {{-- Status and Error Messages (General form errors) --}}
        @if(session('status'))
            <div class="mb-4 text-green-600 text-sm">
                {{ session('status') }}
            </div>
        @endif
        
        {{-- Registration Form --}}
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="space-y-3 text-left">
            @csrf

            <input type="hidden" id="face_registered" name="face_registered" value="0" />
            <input type="hidden" id="user_id_hidden" name="user_id_hidden" value="" />
            <input type="hidden" id="face_embeddings_hidden" name="face_embeddings_hidden" value="" />

            {{-- Name Input with Icon (using the fixed layout from login) --}}
            <div class="mb-3">
                <div class="relative">
                    {{-- Assuming an asset for 'name' or using 'user' again --}}
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/user.png') }}" 
                        alt="Name Icon"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="Full Name"
                        class="w-full pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" />
                </div>
                @error('name')
                    <div class="mt-1 text-red-600 text-xs">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email Input with Icon --}}
            <div class="mb-3">
                <div class="relative">
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/envelope.png') }}" 
                        alt="Email Icon"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        placeholder="Email Address"
                        class="w-full pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" />
                </div>
                @error('email')
                    <div class="mt-1 text-red-600 text-xs">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password Input with Icon --}}
            <div class="mb-3">
                <div class="relative">
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/password.png') }}" 
                        alt="Password Icon"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                    <input id="password" type="password" name="password" required
                        placeholder="Password"
                        class="w-full pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" />
                </div>
                @error('password')
                    <div class="mt-1 text-red-600 text-xs">{{ $message }}</div>
                @enderror
            </div>

            {{-- Confirm Password Input with Icon --}}
            <div class="mb-4">
                <div class="relative">
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/pass-confirmation.png') }}" 
                        alt="Confirm Password Icon"
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 object-contain">
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        placeholder="Confirm Password"
                        class="w-full pl-10 pr-6 py-5 border border-[#888888] rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-7" />
                </div>
                @error('password_confirmation')
                    <div class="mt-1 text-red-600 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="py-1">
            {{-- Scan Face Button --}}
            <div class="mt-4">
                <button type="button" id="openFaceModal"
                    class="w-full bg-[#FFC107] hover:bg-[#E6AD00] text-black py-2 rounded-md font-medium text-sm flex items-center justify-center h-10 transition-colors duration-150">
                    Scan Face
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/qr-scan.png') }}" 
                        alt="QR Scan Icon"
                        class="w-4 h-4 ml-2 object-contain">
                </button>
            </div>

            {{-- Final Register Button --}}
            <div class="mt-2">
                <button type="submit" id="registerBtn"
                    class="w-full bg-blue-600 text-white py-2 rounded-md font-medium text-sm flex items-center justify-center h-10 transition-opacity duration-150 opacity-50 cursor-not-allowed"
                    disabled>
                    Register
                    <img src="{{ asset('images/aitas-icons/Login&SignUp/entrance.png') }}"
                        alt="Register Arrow Icon"
                        class="w-4 h-4 ml-2 object-contain transform rotate-180">
                </button>
            </div>
            </div>
        </form>
    </div>

    {{-- Login Link (Outside the card) --}}
    <div class="mt-6 text-sm text-center text-white z-10 flex flex-col items-center">
        <span class="text-xs">Already have an account?</span>
        <a href="{{ route('login') }}" class="text-sm font-medium underline ml-1" style="color: #79BAFD;">Login</a>
    </div>

    {{-- 
        FACE MODAL: Moved outside the main content block, but kept here 
        for JavaScript access and scoping.
    --}}
    <div id="faceModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-[#1a1a2e] rounded-xl shadow-2xl p-6 relative w-full max-w-sm text-white">
            <button id="closeFaceModalX" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <h3 class="text-xl font-bold text-center mb-1">Scan Your Face</h3>
            
            <p id="instructionText" class="text-sm text-center text-gray-400 mb-6">Center your face within the circle</p>

            <div class="relative w-64 h-64 mx-auto overflow-hidden rounded-full max-w-xs bg-gray-900">
                <video id="video" autoplay muted playsinline 
                class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" style="object-position: center center;"></video>

                
                <canvas id="overlayCanvas" width="256" height="256" class="absolute inset-0 z-10"></canvas>
                
                <canvas id="captureCanvas" width="400" height="300" style="display:none;"></canvas>
            </div>
            <p id="faceStatus" class="mt-4 text-center text-sm font-medium h-6 text-green-400"></p>

            <div class="flex justify-center mt-6 space-x-4">
                <button id="registerFaceBtn" class="w-1/2 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                    Start Scan
                </button>
                <button id="closeFaceModal" class="w-1/2 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg font-semibold transition duration-150">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <x-message-modal />

<script>
/* -------------------------------
   GLOBAL CONFIG + ELEMENTS
--------------------------------- */
const LARAVEL_URL = @json($laravelApiUrl ?? 'https://aitas-capstone.test');
const FASTAPI_URL = @json($fastapiUrl ?? 'https://aitas-capstone.test:8001');

const openFaceModal = document.getElementById('openFaceModal');
const closeFaceModal = document.getElementById('closeFaceModal');
const closeFaceModalX = document.getElementById('closeFaceModalX');
const faceModal = document.getElementById('faceModal');
const regBtn = document.getElementById('registerBtn');
const registerFaceBtn = document.getElementById('registerFaceBtn');
const faceStatus = document.getElementById('faceStatus');
const instructionText = document.getElementById('instructionText');

const video = document.getElementById('video');
const overlayCanvas = document.getElementById('overlayCanvas');
const overlayContext = overlayCanvas.getContext('2d');
const captureCanvas = document.getElementById('captureCanvas');
const captureCtx = captureCanvas.getContext('2d');

const faceRegistered = document.getElementById('face_registered');
const userIdHidden = document.getElementById('user_id_hidden');
const faceEmbeddingsHidden = document.getElementById('face_embeddings_hidden');

const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('password_confirmation');

let stream = null;
let animationFrameId = null;
let tempUserId = null;

/* -------------------------------
   CUSTOM MESSAGE MODAL
--------------------------------- */
function showCustomMessage(message, type = 'error') {
    const modal = document.getElementById('messageModal');
    const title = document.getElementById('messageModalTitle');
    const body = document.getElementById('messageModalBody');
    const icon = document.getElementById('messageModalIcon');
    const okButton = document.getElementById('messageModalOkButton');

    const isSuccess = type === 'success';
    const color = isSuccess ? '#43A047' : '#E53935';
    const hover = isSuccess ? '#388E3C' : '#D32F2F';

    icon.innerHTML = isSuccess
        ? `<svg class="w-20 h-20 mx-auto" stroke="${color}" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
               <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
           </svg>`
        : `<svg class="w-20 h-20 mx-auto" stroke="${color}" fill="none" stroke-width="1.5" viewBox="0 0 24 24">
               <circle cx="12" cy="12" r="10"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>
           </svg>`;

    title.textContent = isSuccess ? 'Success!' : 'Error!';
    title.style.color = color;
    body.textContent = message;

    okButton.style.backgroundColor = color;
    okButton.onmouseover = () => okButton.style.backgroundColor = hover;
    okButton.onmouseout = () => okButton.style.backgroundColor = color;

    modal.classList.remove('hidden');
    okButton.onclick = () => {
        modal.classList.add('hidden');
        if (isSuccess) window.location.href = "{{ route('login') }}";
    };
}

/* -------------------------------
   CAMERA CONTROL
--------------------------------- */
function stopCamera() {
    faceModal.classList.add('hidden');
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    video.srcObject = null;
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
    animationFrameId = null;
}

closeFaceModal.addEventListener('click', stopCamera);
closeFaceModalX.addEventListener('click', stopCamera);

/* -------------------------------
   FACE OVERLAY
--------------------------------- */
function drawOverlay() {
    if (!video.videoWidth) {
        animationFrameId = requestAnimationFrame(drawOverlay);
        return;
    }

    const w = overlayCanvas.width, h = overlayCanvas.height;
    overlayContext.clearRect(0, 0, w, h);

    const cx = w / 2, cy = h / 2, r = Math.min(cx, cy) * 0.95;
    overlayContext.beginPath();
    overlayContext.arc(cx, cy, r, 0, Math.PI * 2);
    overlayContext.rect(0, 0, w, h);
    overlayContext.fillStyle = 'rgba(0, 0, 0, 0.5)';
    overlayContext.fill('evenodd');

    overlayContext.beginPath();
    overlayContext.arc(cx, cy, r, 0, Math.PI * 2);
    overlayContext.strokeStyle = '#3b82f6';
    overlayContext.lineWidth = 4;
    overlayContext.stroke();

    animationFrameId = requestAnimationFrame(drawOverlay);
}

/* -------------------------------
   VALIDATION BEFORE FACE SCAN
--------------------------------- */
openFaceModal.addEventListener('click', async () => {
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const confirm = confirmInput.value.trim();

    if (!name || !email || !password || !confirm)
        return showCustomMessage("Please fill in all fields first.", "error");

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
        return showCustomMessage("Please enter a valid email address.", "error");

    if (password !== confirm)
        return showCustomMessage("Passwords do not match.", "error");

    try {
        const check = await fetch(`${LARAVEL_URL}/api/teacher/check-email`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ email })
        });

        const data = await check.json();
        if (data.exists) return showCustomMessage("Email is already registered.", "error");
    } catch {
        return showCustomMessage("Cannot verify email. Check your internet.", "error");
    }

    // ✅ If everything valid, open camera
    faceModal.classList.remove("hidden");
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            video.play();
            animationFrameId = requestAnimationFrame(drawOverlay);
        };
    } catch (err) {
        showCustomMessage("Camera access denied: " + err.message, "error");
    }
});

/* -------------------------------
   FACE SCAN AND REGISTRATION
--------------------------------- */
async function captureFrame() {
    captureCtx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
    return new Promise(res => captureCanvas.toBlob(res, "image/jpeg", 0.9));
}

async function registerFacePhases(userId, captures) {
    const formData = new FormData();
    captures.forEach(img => formData.append('images', img));
    formData.append('user_id', userId);

    const res = await fetch(`${FASTAPI_URL}/register`, { method: 'POST', body: formData });

    const text = await res.text(); // get raw response
    let data;

    try {
        data = JSON.parse(text); // try parsing as JSON
    } catch (err) {
        console.error("FastAPI returned non-JSON response:", text);
        throw new Error("Face scan failed: server did not return valid JSON.");
    }

    if (!res.ok || !data.embeddings) {
        console.error("FastAPI error response:", data);
        throw new Error(data.detail || "Face scan failed. Try again.");
    }

    return data.embeddings;
}

registerFaceBtn.addEventListener('click', async () => {
    const name = nameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const confirm = confirmInput.value.trim();

    if (!name || !email || !password || !confirm)
        return showCustomMessage("Please complete the form first.", "error");

    registerFaceBtn.disabled = true;
    registerFaceBtn.textContent = "Scanning...";
    faceStatus.textContent = "Capturing your face...";

    try {
        const phases = [
            { text: "Look straight", duration: 3 },
            { text: "Turn left slightly", duration: 3 },
            { text: "Turn right slightly", duration: 3 },
        ];

        const captures = [];
        for (let p of phases) {
            instructionText.textContent = p.text;
            for (let i = p.duration; i > 0; i--) {
                faceStatus.textContent = `Capturing in ${i}...`;
                await new Promise(r => setTimeout(r, 1000));
            }
            captures.push(await captureFrame());
        }

        const tempUser = await fetch(`${LARAVEL_URL}/api/teacher/register-temp`, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: new URLSearchParams({ name, email, password, password_confirmation: confirm, face_registered: 0 })
        });

        const result = await tempUser.json();
        if (!tempUser.ok || !result.user_id) throw new Error("Temporary user creation failed.");
        tempUserId = result.user_id;

        const embeddings = await registerFacePhases(tempUserId, captures);
        faceEmbeddingsHidden.value = JSON.stringify(embeddings);
        userIdHidden.value = tempUserId;
        faceRegistered.value = "1";

        regBtn.disabled = false;
        regBtn.classList.remove("opacity-50", "cursor-not-allowed");
        regBtn.classList.add("hover:bg-blue-700");

        faceStatus.textContent = "Face scan complete ✅";
        instructionText.textContent = "You may now register.";

        setTimeout(stopCamera, 2000);
    } catch (err) {
        showCustomMessage(err.message, "error");
    } finally {
        registerFaceBtn.disabled = false;
        registerFaceBtn.textContent = "Start Scan";
    }
});

/* -------------------------------
   FINAL REGISTER SUBMIT
--------------------------------- */
document.getElementById('registerForm').addEventListener('submit', async e => {
    e.preventDefault();

    if (faceRegistered.value !== "1")
        return showCustomMessage("Please complete a face scan first.", "error");

    regBtn.disabled = true;
    regBtn.textContent = "Processing...";

    try {
        const formData = new FormData(e.target);
        const res = await fetch(`${LARAVEL_URL}/api/teacher/register`, {
            method: "POST",
            headers: { "Accept": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        });

        const data = await res.json();
        if (res.ok) showCustomMessage("Registration successful! Please check your Gmail to verify your email before logging in.", "success");
        else showCustomMessage("Registration failed: " + (data.message || "Please try again."), "error");
    } catch {
        showCustomMessage("An unexpected error occurred.", "error");
    } finally {
        regBtn.disabled = false;
        regBtn.textContent = "Register";
    }
});
</script>
@endsection
