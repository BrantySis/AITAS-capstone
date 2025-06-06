<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UC Admin Dashboard') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        .sidebar {
            transition: width 0.3s;
            box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.1);
        }

        .sidebar:hover {
            width: 240px;
        }

        .nav-label {
            display: none;
        }

        .sidebar:hover .nav-label {
            display: inline;
        }

        .sidebar:hover .nav-icon {
            justify-content: flex-start;
        }
    </style>
</head>

<body class="[background-color:#ECFAFF] min-h-screen">

    <!-- Top Bar -->
    <div class="bg-white shadow-md flex items-center justify-between px-6 py-4">
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
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Logout
            </button>
        </form>
    </div>

    <!-- Main content -->
    <main class="flex-1 p-10">
        {{ $slot }}
    </main>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Toastr configuration for top center position
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

</body>
</html>
