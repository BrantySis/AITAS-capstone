<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Attendance Checker - UC Web Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar {
            transition: width 0.3s ease;
            box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar:not(:hover) {
            width: 64px;
        }
        
        .sidebar:hover {
            width: 240px;
        }
        
        .nav-label {
            opacity: 0;
            transition: opacity 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar:hover .nav-label {
            opacity: 1;
        }
        
        .profile-info {
            opacity: 0;
            transition: opacity 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .sidebar:hover .profile-info {
            opacity: 1;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 4px 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #374151;
        }
        
        .nav-item:hover {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .nav-item.active {
            background-color: #DBEAFE;
            color: #1E40AF;
            font-weight: 600;
        }
        
        .nav-icon {
            min-width: 24px;
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img class="h-8 w-8" src="{{ asset('images/logo.png') }}" alt="UC Logo">
                    </div>
                    <div class="ml-4">
                        <h1 class="text-xl font-semibold text-gray-900">UC Web Portal</h1>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">Welcome, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Layout -->
    <div class="flex pt-16">
        <!-- Sidebar -->
        <aside class="sidebar bg-[#ECFAFF] h-screen shadow-md overflow-hidden fixed z-40">
            <!-- Profile Section -->
            <div class="flex flex-col items-center px-4 py-6 border-b border-gray-200">
                <img src="{{ asset('images/profile.png') }}" alt="Profile" class="w-10 h-10 rounded-full border-2 border-blue-300 mb-2" />
                <div class="text-center profile-info">
                    <div class="text-sm font-semibold text-blue-800">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="text-xs text-gray-600">
                        {{ Auth::user()->role->name ?? 'Teacher' }}
                    </div>
                    <div class="text-xs text-gray-600">
                        Faculty #: {{ Auth::user()->faculty_number ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="mt-4">
                <a href="{{ route('dashboard.teacher') }}" class="nav-item">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-label ml-3">Dashboard</span>
                </a>
                
                <a href="{{ route('teacher.notifications') }}" class="nav-item">
                    <span class="nav-icon">🔔</span>
                    <span class="nav-label ml-3">Notifications</span>
                </a>
                
                <a href="{{ route('teacher.forms') }}" class="nav-item">
                    <span class="nav-icon">📄</span>
                    <span class="nav-label ml-3">DepEd Forms</span>
                </a>
                
                <a href="{{ route('teacher.biometrics') }}" class="nav-item">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label ml-3">Biometrics</span>
                </a>
                
                <a href="{{ route('teacher.grades') }}" class="nav-item">
                    <span class="nav-icon">📝</span>
                    <span class="nav-label ml-3">E Grade</span>
                </a>
                
                <a href="{{ route('teacher.load') }}" class="nav-item">
                    <span class="nav-icon">📅</span>
                    <span class="nav-label ml-3">My Load</span>
                </a>
                
                <a href="{{ route('teacher.evaluation') }}" class="nav-item">
                    <span class="nav-icon">✅</span>
                    <span class="nav-label ml-3">Teacher's Evaluation</span>
                </a>
                
                <a href="{{ route('teacher.calendar') }}" class="nav-item">
                    <span class="nav-icon">📆</span>
                    <span class="nav-label ml-3">My Calendar</span>
                </a>
                
                <a href="{{ route('teacher.attendance.index') }}" class="nav-item active">
                    <span class="nav-icon">👤</span>
                    <span class="nav-label ml-3">Attendance Checker</span>
                </a>
                
                <a href="{{ route('profile.edit') }}" class="nav-item">
                    <span class="nav-icon">🧾</span>
                    <span class="nav-label ml-3">Profile</span>
                </a>
            </nav>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 ml-16 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('dashboard.teacher') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Dashboard
                    </a>
                </div>

                <h2 class="text-3xl font-bold text-blue-700 mb-8">Attendance Checker</h2>

                <!-- Attendance Status Card -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl text-green-600 mb-2">✅</div>
                            <div class="text-sm text-gray-600">Check In</div>
                            <div class="font-semibold text-gray-900">8:00 AM</div>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl text-yellow-600 mb-2">⏰</div>
                            <div class="text-sm text-gray-600">Current Status</div>
                            <div class="font-semibold text-gray-900">Present</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl text-blue-600 mb-2">🕐</div>
                            <div class="text-sm text-gray-600">Hours Today</div>
                            <div class="font-semibold text-gray-900">6.5 hrs</div>
                        </div>
                    </div>
                </div>

                <!-- Face Recognition Attendance -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Face Recognition Attendance</h3>
                    
                    <div class="text-center">
                        <div id="camera-container" class="mb-4">
                            <video id="video" width="400" height="300" autoplay class="mx-auto border rounded-lg shadow-sm"></video>
                            <canvas id="canvas" width="400" height="300" style="display: none;"></canvas>
                        </div>
                        
                        <div class="space-x-4">
                            <button id="start-camera" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                📷 Start Camera
                            </button>
                            
                            <button id="capture-photo" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors" style="display: none;">
                                📸 Capture & Verify
                            </button>
                            
                            <button id="stop-camera" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                                ⏹️ Stop Camera
                            </button>
                        </div>
                        
                        <div id="status-message" class="mt-4 p-3 rounded-lg" style="display: none;"></div>
                    </div>
                </div>

                <!-- Recent Attendance History -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Attendance</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Check In</th>
                                    <th class="px-4 py-2 text-left">Check Out</th>
                                    <th class="px-4 py-2 text-left">Hours</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ now()->format('M d, Y') }}</td>
                                    <td class="px-4 py-2">8:00 AM</td>
                                    <td class="px-4 py-2">-</td>
                                    <td class="px-4 py-2">6.5</td>
                                    <td class="px-4 py-2">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Present</span>
                                    </td>
                                </tr>
                                <!-- Add more rows as needed -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let context = canvas.getContext('2d');
        let stream = null;

        document.getElementById('start-camera').addEventListener('click', async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                
                document.getElementById('start-camera').style.display = 'none';
                document.getElementById('capture-photo').style.display = 'inline-block';
                document.getElementById('stop-camera').style.display = 'inline-block';
                
                showStatus('Camera started. Position your face in the frame.', 'info');
            } catch (err) {
                showStatus('Error accessing camera: ' + err.message, 'error');
            }
        });

        document.getElementById('capture-photo').addEventListener('click', function() {
            context.drawImage(video, 0, 0, 400, 300);
            let imageData = canvas.toDataURL('image/jpeg');
            
            // Here you would send the image to your backend for face recognition
            showStatus('Processing face recognition...', 'info');
            
            // Simulate processing
            setTimeout(() => {
                showStatus('Face verified successfully! Attendance marked.', 'success');
            }, 2000);
        });

        document.getElementById('stop-camera').addEventListener('click', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
                
                document.getElementById('start-camera').style.display = 'inline-block';
                document.getElementById('capture-photo').style.display = 'none';
                document.getElementById('stop-camera').style.display = 'none';
                
                showStatus('Camera stopped.', 'info');
            }
        });

        function showStatus(message, type) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.style.display = 'block';
            statusDiv.textContent = message;
            
            statusDiv.className = 'mt-4 p-3 rounded-lg';
            if (type === 'success') {
                statusDiv.className += ' bg-green-100 text-green-800';
            } else if (type === 'error') {
                statusDiv.className += ' bg-red-100 text-red-800';
            } else {
                statusDiv.className += ' bg-blue-100 text-blue-800';
            }
        }
    </script>
</body>
</html>
