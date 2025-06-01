<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-6">
        <h1 class="text-3xl font-bold text-yellow-700 mb-6">Teacher Dashboard</h1>

        <div class="bg-yellow-100 p-4 rounded-lg shadow">
            <p class="text-gray-800">Welcome, {{ auth()->user()->name }}. You can check your attendance here.</p>
        </div>
    </div>
</x-app-layout>
