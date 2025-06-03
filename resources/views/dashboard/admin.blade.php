<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - UC Web Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- Layout -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar w-16 hover:w-60 [background-color:#ECFAFF] h-screen shadow-md overflow-hidden">
            <ul class="space-y-2 mt-6 text-gray-700">
                <li class="nav-icon flex items-center space-x-3 hover:bg-blue-100 rounded-md px-4 py-2 cursor-pointer">
                    <span>👥</span>
                    <a href="{{ route('admin.teachers.index') }}" class="block py-2 px-4 text-gray-800 hover:bg-blue-100">
    Manage Teachers
</a>
<li class="nav-icon flex items-center space-x-3 hover:bg-blue-100 rounded-md px-4 py-2 cursor-pointer">
                    <span>📈</span>
                    <span class="nav-label">manage subjects</span>
                </li>

                </li>
                <li class="nav-icon flex items-center space-x-3 hover:bg-blue-100 rounded-md px-4 py-2 cursor-pointer">
                    <span>📈</span>
                    <span class="nav-label">Reports</span>
                </li>
                  <li class="nav-icon flex items-center space-x-3 hover:bg-blue-100 rounded-md px-4 py-2 cursor-pointer">
                    <span>⚙️</span>
                    <span class="nav-label">Change Geolocation Settings</span>
                </li>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="flex-1 p-10">
            <div class="bg-white rounded-lg shadow p-8 h-[500px] text-center">
                <h2 class="text-xl font-semibold text-gray-700">Reports panel to be added soon</h2>
            </div>
        </main>
    </div>

</body>
</html>
