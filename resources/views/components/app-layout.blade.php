<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
</head>

<body class="bg-gray-50">
    @if(!isset($showNavigation) || $showNavigation)
        <!-- Top Navigation Bar -->
        <nav class="bg-white shadow-sm border-b border-gray-200 fixed w-full top-0 z-50">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="h-10 w-10" />
                        <div>
                            <h1 class="text-lg font-bold text-blue-800">University of Cebu</h1>
                            <p class="text-sm text-blue-600">Lapu-Lapu and Mandaue</p>
                            <p class="text-xs text-gray-500">ADMIN DASHBOARD</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="fixed left-0 top-0 pt-20 h-full w-64 bg-blue-800 text-white z-40">
            <nav class="px-4 py-6">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('dashboard.admin') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                            <span>📊</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.teachers.index') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                            <span>👥</span>
                            <span>Teachers</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.subjects.index') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                            <span>📚</span>
                            <span>Subjects</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.schedules.index') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                            <span>📈</span>
                            <span>Schedules</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.rooms.index') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                            <span>🏢</span>
                            <span>Rooms</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="ml-64 pt-20 min-h-screen">
            <div class="p-6">
                {{ $slot }}
            </div>
        </main>
    @else
        <!-- Layout without navigation -->
        {{ $slot }}
    @endif

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-center",
            "timeOut": "3000",
        }

        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    </script>
</body>
</html>

