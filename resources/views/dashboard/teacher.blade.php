<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Teacher Dashboard - UC Web Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar {
            transition: width 0.3s;
            box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.1);
        }
        .sidebar:hover {
            width: 240px;
        }
        .sidebar:hover .nav-label {
            display: inline;
        }
        .nav-label {
            display: none;
        }
        .sidebar:hover .nav-icon {
            justify-content: flex-start;
        }
        .sidebar:hover .profile-name {
            display: inline-block;
        }
        .profile-name {
            display: none;
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
        <aside class="sidebar w-16 hover:w-60 bg-[#ECFAFF] h-screen shadow-md overflow-hidden fixed">
            <!-- Profile Section -->
            <div class="flex flex-col items-center px-4 py-4 space-y-2">
                <img src="{{ asset('images/profile.png') }}" alt="Profile" class="w-10 h-10 rounded-full border-2 border-blue-300" />
                <div class="text-center">
                    <span class="profile-name block text-sm font-semibold text-blue-800">
                        {{ Auth::user()->name }}
                    </span>
                    <span class="profile-name block text-xs text-gray-600">
                        {{ Auth::user()->role->name ?? 'Teacher' }}
                    </span>
                    <span class="profile-name block text-xs text-gray-600">
                        Faculty #: {{ Auth::user()->faculty_number ?? 'N/A' }}
                    </span>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-b border-gray-300 mx-4"></div>

            <!-- Menu Items -->
            <ul class="space-y-2 mt-4 px-2 text-gray-700">
                <li>
                    <a href="{{ route('dashboard.teacher') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>🏠</span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.notifications') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>🔔</span>
                        <span class="nav-label">Notifications</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.forms') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>📄</span>
                        <span class="nav-label">DepEd Forms</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.biometrics') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>📊</span>
                        <span class="nav-label">Biometrics</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.grades') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>📝</span>
                        <span class="nav-label">E Grade</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.load') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>📅</span>
                        <span class="nav-label">My Load</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.evaluation') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>✅</span>
                        <span class="nav-label">Teacher's Evaluation</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.calendar') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>📆</span>
                        <span class="nav-label">My Calendar</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.attendance.index') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>👤</span>
                        <span class="nav-label">Attendance Checker</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>🧾</span>
                        <span class="nav-label">Profile</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 ml-16 p-6">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-bold text-blue-700 mb-8">Teacher Dashboard</h2>

                <!-- Dashboard Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <span class="text-2xl">📅</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">My Schedule</h3>
                                <p class="text-gray-600">Today's classes</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('teacher.load') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                View Schedule →
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <span class="text-2xl">👤</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Attendance</h3>
                                <p class="text-gray-600">Check in/out</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('teacher.attendance.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                Check Attendance →
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <span class="text-2xl">📝</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Grades</h3>
                                <p class="text-gray-600">Student grades</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('teacher.grades') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                Manage Grades →
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <span class="text-2xl">📄</span>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Forms</h3>
                                <p class="text-gray-600">DepEd forms</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('teacher.forms') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                View Forms →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('teacher.attendance.index') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <span class="text-2xl mr-3">📍</span>
                            <div>
                                <h4 class="font-medium text-gray-900">Check Attendance</h4>
                                <p class="text-sm text-gray-600">Mark your attendance for today</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('teacher.calendar') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <span class="text-2xl mr-3">📆</span>
                            <div>
                                <h4 class="font-medium text-gray-900">View Calendar</h4>
                                <p class="text-sm text-gray-600">Check your schedule</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('profile.edit') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                            <span class="text-2xl mr-3">⚙️</span>
                            <div>
                                <h4 class="font-medium text-gray-900">Update Profile</h4>
                                <p class="text-sm text-gray-600">Manage your information</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

