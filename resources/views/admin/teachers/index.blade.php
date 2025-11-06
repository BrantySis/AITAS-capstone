{{-- resources/views/admin/teachers/index.blade.php --}}
<x-app-layout>

            <!-- Back Button Outside the Frame -->
    <div class="max-w-7xl pl-6 mx-auto mt-6">
        <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>

    <div class="max-w-7xl mx-auto py-10 px-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Teachers</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.teachers.import') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">📁 Import Teachers</a>
                <a href="{{ route('admin.teachers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Teacher</a>
            </div>
        </div>    

        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Faculty Number</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $teacher->name }}</td>
                        <td class="px-4 py-2">{{ $teacher->email }}</td>
                        <td class="px-4 py-2">{{ $teacher->faculty_number }}</td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

