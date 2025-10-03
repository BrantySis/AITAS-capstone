<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - University of Cebu</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.tailwindcss.com"></script>
<style>
@keyframes snow-fall {
    0% { transform: translateY(-10vh); opacity: 0.8; }
    100% { transform: translateY(110vh); opacity: 0; }
}
.snow-dot {
    position: absolute;
    top: 0;
    border-radius: 9999px;
    background-color: white;
    opacity: 0.7;
    animation-name: snow-fall;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}
</style>
</head>
<body>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-500 to-blue-700 relative">

    <!-- Snowfall -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        @for ($i = 0; $i < 50; $i++)
        @php
            $duration = rand(10, 20);
            $size = rand(2, 10);
            $left = rand(0, 100);
            $top = rand(-100, 100);
        @endphp
        <div class="snow-dot"
            style="width: {{ $size }}px; height: {{ $size }}px; left: {{ $left }}%; top: {{ $top }}vh; animation: snow-fall {{ $duration }}s linear 0s infinite;">
        </div>
        @endfor
    </div>

    <div class="z-10 bg-white rounded-md shadow-lg p-8 w-full max-w-md text-center">
        <!-- Logo -->
        <div class="mb-6">
            <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="mx-auto h-16 mb-2">
            <h1 class="text-md font-semibold text-blue-800">University of Cebu</h1>
            <h2 class="text-sm text-blue-600">Lapu-Lapu and Mandaue</h2>
            <p class="text-xs text-gray-500 mt-1">WEB PORTAL - REGISTER</p>
        </div>

        <!-- Errors -->
        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Register Form -->
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="space-y-4 text-left">
            @csrf

            <input type="hidden" id="face_registered" name="face_registered" value="0">
            <input type="hidden" id="user_id_hidden" name="user_id_hidden" value="">

            <!-- Name -->
            <div>
                <label for="name" class="block text-gray-700 text-sm mb-1">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-700 text-sm mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-gray-700 text-sm mb-1">Password</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-gray-700 text-sm mb-1">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Scan Face -->
            <div class="mt-4">
                <button type="button" id="openFaceModal"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded font-semibold">
                    Scan Face
                </button>
            </div>

            <!-- Submit -->
            <div class="mt-4">
                <button type="submit" id="registerBtn"
                    class="w-full bg-blue-600 text-white py-2 rounded font-semibold flex items-center justify-center opacity-50 cursor-not-allowed"
                    disabled>
                    Register
                </button>
            </div>

            <!-- Links -->
            <div class="mt-4 text-sm text-center text-blue-700">
                <a href="{{ route('login') }}" class="hover:underline">Already have an account?</a>
            </div>
        </form>

        <!-- Face Modal -->
        <div id="faceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 relative w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Face Registration</h3>
                
                <video id="video" autoplay class="w-full h-64 border rounded"></video>
                <canvas id="canvas" width="400" height="300" style="display:none;"></canvas>
                
                <p id="faceStatus" class="mt-2 text-sm text-blue-700 font-semibold"></p>
                
                <div class="flex justify-between mt-4">
                    <button id="registerFaceBtn"
                        class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">Register Face</button>
                    <button id="closeFaceModal"
                        class="bg-gray-400 hover:bg-gray-500 text-white py-2 px-4 rounded">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const openFaceModal = document.getElementById('openFaceModal');
const closeFaceModal = document.getElementById('closeFaceModal');
const faceModal = document.getElementById('faceModal');
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const context = canvas.getContext('2d');
const registerFaceBtn = document.getElementById('registerFaceBtn');
const faceStatus = document.getElementById('faceStatus');
const regBtn = document.getElementById('registerBtn');

const FASTAPI_URL = @json($fastapiUrl);
const LARAVEL_URL = @json($laravelApiUrl);

// Open modal & camera
openFaceModal.addEventListener('click', async () => {
    faceModal.classList.remove('hidden');
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
    } catch (err) {
        alert("Camera access denied: " + err);
    }
});

// Close modal & stop camera
closeFaceModal.addEventListener('click', () => {
    faceModal.classList.add('hidden');
    const stream = video.srcObject;
    if (stream) stream.getTracks().forEach(track => track.stop());
    video.srcObject = null;
});

// Capture frame
function captureFrame() {
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    return new Promise(resolve => canvas.toBlob(blob => resolve(blob), "image/jpeg"));
}

// Multi-phase scan
async function registerFacePhases(userId) {
    const phases = ["Look straight", "Turn left", "Turn right"];
    const captures = [];

    for (let phase of phases) {
        faceStatus.textContent = `Phase: ${phase}`;
        for (let count = 3; count > 0; count--) {
            faceStatus.textContent = `Phase: ${phase} (Capturing in ${count})`;
            await new Promise(res => setTimeout(res, 1000));
        }
        const blob = await captureFrame();
        captures.push(blob);
    }

    const formData = new FormData();
    captures.forEach(img => formData.append('images', img));
    formData.append('user_id', userId);

    const response = await fetch(`${FASTAPI_URL}/register`, {
        method: 'POST',
        body: formData
    });
    const data = await response.json();
    return data.message;
}

// Register face
registerFaceBtn.addEventListener('click', async () => {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const password_confirmation = document.getElementById('password_confirmation').value.trim();

    if (!name || !email || !password || !password_confirmation) {
        faceStatus.textContent = "Fill up the form first.";
        return;
    }

    try {
        // Step 1: Create user in Laravel
        let laravelResp = await fetch(`${LARAVEL_URL}/teacher/register`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({ name, email, password, password_confirmation, face_registered: 0 })
        });

        let result = await laravelResp.json();

        if (!result.user_id) {
            faceStatus.textContent = "User registration failed.";
            return;
        }

        // Step 2: Send images to FastAPI
        const msg = await registerFacePhases(result.user_id);

        faceStatus.textContent = "Face registration successful ✅";

        // Step 3: Update form
        document.getElementById('face_registered').value = "1";
        document.getElementById('user_id_hidden').value = result.user_id;

        // Step 4: Enable Register button
        regBtn.disabled = false;
        regBtn.classList.remove("opacity-50", "cursor-not-allowed");
        regBtn.classList.add("hover:bg-blue-700");

    } catch (err) {
        faceStatus.textContent = "Registration failed ❌: " + err;
    }
});
</script>
</body>
</html>
