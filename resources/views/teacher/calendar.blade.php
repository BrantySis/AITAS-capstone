<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Calendar - UC Web Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <style>
        .sidebar {
            transition: width 0.3s;
            box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.1);
        }
        .sidebar:hover { width: 240px; }
        .sidebar:hover .nav-label { display: inline; }
        .nav-label { display: none; }
        .sidebar:hover .profile-name { display: inline-block; }
        .profile-name { display: none; }
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
        <!-- Sidebar (same as attendance page) -->
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
                    <a href="{{ route('teacher.load') }}" class="flex items-center space-x-3 nav-icon hover:bg-blue-100 hover:text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
                        <span>📅</span>
                        <span class="nav-label">My Load</span>
                    </a>
                </li>
                <li class="bg-blue-100 rounded">
                    <a href="{{ route('teacher.calendar') }}" class="flex items-center space-x-3 nav-icon text-blue-800 rounded px-3 py-2 transition-all cursor-pointer">
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
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('dashboard.teacher') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Dashboard
                    </a>
                </div>

                <h2 class="text-3xl font-bold text-blue-700 mb-8">My Calendar</h2>

                <!-- Calendar Container -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div id="calendar" class="h-[600px]"></div>
                </div>
            </div>
        </main>
    </div>

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
                events: @json($events),
                eventClick: function(info) {
                    const event = info.event;
                    const props = event.extendedProps;
                    const startTime = event.start.toLocaleString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const endTime = event.end.toLocaleString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    alert(`Subject: ${event.title}\nEDP Code: ${props.edp_code || 'N/A'}\nType: ${props.type || 'N/A'}\nUnits: ${props.units || 'N/A'}\nTime: ${startTime} - ${endTime}`);
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>

