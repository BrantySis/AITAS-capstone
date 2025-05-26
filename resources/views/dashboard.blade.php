<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-6">
        <div class="bg-white shadow-lg rounded-2xl p-6">
            <h1 class="text-3xl font-semibold text-gray-800 mb-6">
                Welcome, {{ auth()->user()->name }}!
            </h1>

            @if (auth()->user()->isAdmin())
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-blue-700">Admin Dashboard</h2>
                    <a href="{{ route('register.teacher') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        + Register New Teacher
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 p-4 rounded-xl shadow-sm">
                        <h3 class="text-lg font-semibold text-blue-800">Manage Attendance</h3>
                        <p class="text-gray-700 mt-1">Review and manage teacher attendance records.</p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-xl shadow-sm">
                        <h3 class="text-lg font-semibold text-green-800">Reports</h3>
                        <p class="text-gray-700 mt-1">View summary reports and logs.</p>
                    </div>
                </div>
            @elseif (auth()->user()->isTeacher())
                <div class="bg-yellow-50 p-4 rounded-xl shadow-sm">
                    <h2 class="text-xl font-bold text-yellow-700">Teacher Dashboard</h2>
                    <p class="text-gray-700 mt-2">Check in and view attendance logs.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
