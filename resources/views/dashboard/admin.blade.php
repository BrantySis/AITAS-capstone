<x-app-layout title="Admin Dashboard">
    <!-- Dashboard Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
        <p class="text-gray-600">Welcome to the University of Cebu Admin Portal</p>
    </div>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <span class="text-2xl">👥</span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Teachers</h3>
                    <p class="text-gray-600">Manage faculty</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.teachers.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <span class="text-2xl">📚</span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Subjects</h3>
                    <p class="text-gray-600">Course management</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.subjects.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <span class="text-2xl">📈</span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Schedules</h3>
                    <p class="text-gray-600">Class scheduling</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.schedules.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <span class="text-2xl">🏢</span>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Rooms</h3>
                    <p class="text-gray-600">Room management</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.rooms.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>
        </div>
    </div>

    <!-- Reports Section -->
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Reports & Analytics</h2>
        <p class="text-gray-600">Reports panel to be added soon</p>
    </div>
</x-app-layout>