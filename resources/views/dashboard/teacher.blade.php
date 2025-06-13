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

    <!-- Top Bar -->
    <div class="bg-white shadow-md flex items-center justify-between px-6 py-4">
        <div class="flex items-center space-x-4">
            <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="h-10" />
            <h1 class="text-lg font-bold text-blue-800">UNIVERSITY OF CEBU WEB PORTAL</h1>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Logout
            </button>
        </form>
    </div>

    <!-- Layout -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-16 hover:w-60 [background-color:#ECFAFF] h-screen shadow-md overflow-hidden">
          <!-- Profile Section -->
                    <div class="flex flex-col items-center px-4 py-4 space-y-2">
                        <img src="{{ asset('images/profile.png') }}" alt="Profile" class="w-10 h-10 rounded-full border-2 border-blue-300" />
                        <div class="text-center">
                            <span class="profile-name block text-sm font-semibold text-blue-800">
                                {{ Auth::user()->name }}
                            </span>
                            <span class="profile-name block text-xs text-gray-600">
                                {{ Auth::user()->role->name ?? 'No role' }}
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
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <span>🏠</span>
                    <span class="nav-label">Dashboard</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <span>🔔</span>
                    <span class="nav-label">Notifications</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <span>📄</span>
                    <span class="nav-label">DepEd Forms</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <span>📊</span>
                    <span class="nav-label">Biometrics</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <span>📝</span>
                    <span class="nav-label">E Grade</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <a href="{{ route('teacher.load') }}" class="flex items-center space-x-2">
                        <span>📅</span>
                        <span class="nav-label">My Load</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <span>✅</span>
                    <span class="nav-label">Teacher's Evaluation</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                    <a href="{{ route('teacher.calendar') }}" class="flex items-center space-x-2">
                        <span>📆</span>
                        <span class="nav-label">My Calendar</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                <a href="{{ route('teacher.attendance.index') }}" class="flex items-center space-x-2">    
                <span>👤</span>
                    <span class="nav-label">Attendance Checker</span>
                </li>
                <li class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2">  
                        <span>🧾</span>
                        <span class="nav-label">Profile</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Content Area -->
        <main class="flex-1 p-6">
            <h2 class="text-2xl font-bold text-blue-700 mb-4">Attendance Checker</h2>

            <div class="border rounded-lg bg-white p-10 h-[500px] flex items-center justify-center text-gray-400 text-lg">
            <div id="calendar" class="bg-white border rounded-lg p-4 shadow-md h-[400px] w-full"></div>

                        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
                        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
                        <script>
                              document.addEventListener('DOMContentLoaded', function () {
                            const calendarEl = document.getElementById('calendar');
                            const calendar = new FullCalendar.Calendar(calendarEl, {
                                initialView: 'dayGridMonth',
                                height: 600,
                                headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                },
                                events: [
                                    {
                                        title: 'Test Class',
                                        start: '2025-06-14T10:00:00',
                                        end: '2025-06-14T12:00:00'
                                    }
                                    ]
                            });
                            calendar.render();
                        });
                           
                        </script>
                </div>
        </main>
    </div>

</body>
</html>
