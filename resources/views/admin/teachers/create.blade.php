{{-- resources/views/admin/teachers/create.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-10 px-6">
        <h2 class="text-2xl font-semibold mb-4">Add New Teacher</h2>
        <form action="{{ route('admin.teachers.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" class="w-full border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" class="w-full border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
            <label for="faculty_number">Faculty Number</label>
            <input type="text" name="faculty_number" id="faculty_number" maxlength="7" value="{{ old('faculty_number', $teacher->faculty_number ?? '') }}" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" class="w-full border-gray-300 rounded mt-1" required>
            </div>
            <div class="flex justify-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create</button>
            </div>
        </form>
    </div>
</x-app-layout>
