<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Top Bar -->
    <div class="bg-white shadow-md flex items-center justify-between px-6 py-4 fixed w-full top-0 z-50">
        <div class="flex items-center space-x-4">
            <img src="{{ asset('images/UClogo.png') }}" alt="UC Logo" class="h-10" />
            <div>
                <h1 class="text-lg font-bold text-blue-800">University of Cebu</h1>
                <p class="text-sm text-gray-600">Lapu-Lapu and Mandaue</p>
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

    <!-- Sidebar -->
    <div class="fixed left-0 top-16 h-full w-64 bg-blue-800 text-white pt-6 z-40">
        <nav class="space-y-2 px-4">
            <a href="{{ route('dashboard.admin') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.subjects.index') }}" class="flex items-center space-x-3 py-2 px-3 rounded hover:bg-blue-700 transition">
                <span>📚</span>
                <span>Subjects</span>
            </a>
            <!-- Add more navigation items as needed -->
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 pt-16 min-h-screen">
        <main class="p-6">
            @yield('content')
        </main>
    </div>

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
        @if(session('info'))
            toastr.info("{{ session('info') }}");
        @endif
        @if(session('warning'))
            toastr.warning("{{ session('warning') }}");
        @endif
    </script>

    @stack('scripts')
</body>
</html>



