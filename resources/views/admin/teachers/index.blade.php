{{-- resources/views/admin/teachers/index.blade.php --}}
<x-app-layout>
    <div class="max-w-7xl mx-auto py-10 px-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Teachers</h2>
            <a href="{{ route('admin.teachers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Teacher</a>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $teacher->name }}</td>
                        <td class="px-4 py-2">{{ $teacher->email }}</td>
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
