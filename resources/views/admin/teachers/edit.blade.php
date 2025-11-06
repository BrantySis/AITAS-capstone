{{-- resources/views/admin/teachers/edit.blade.php --}}
<x-app-layout>
     <!-- Back Button -->
     <div class="max-w-7xl -ml-1 mx-auto mb-6">
        <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>
    
    <div class="max-w-2xl mx-auto py-10 px-6">
        <h2 class="text-2xl font-semibold mb-4">Edit Teacher</h2>
        <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" value="{{ $teacher->name }}" class="w-full border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ $teacher->email }}" class="w-full border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
            <label for="faculty_number">Faculty Number</label>
            <input type="text" name="faculty_number" id="faculty_number" class="w-full border-gray-300 rounded mt-1" maxlength="8" value="{{ old('faculty_number', $teacher->faculty_number ?? '') }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">New Password (optional)</label>
                <input type="password" name="password" class="w-full border-gray-300 rounded mt-1">
            </div>
            <div class="flex justify-end">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</x-app-layout>
