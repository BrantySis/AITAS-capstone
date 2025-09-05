<x-app-layout>
    <div class="max-w-7xl pl-6 mx-auto mt-6">
        <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Teachers
        </a>
    </div>

    <div class="max-w-2xl mx-auto py-10 px-6">
        <h2 class="text-2xl font-semibold mb-4">Import Teachers</h2>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-blue-800 mb-2">Excel/CSV Format Required:</h3>
            <p class="text-sm text-blue-700">Your file should have these columns:</p>
            <ul class="text-sm text-blue-700 mt-2 list-disc list-inside">
                <li><strong>name</strong> - Teacher's full name</li>
                <li><strong>email</strong> - Teacher's email address</li>
                <li><strong>faculty_number</strong> - Faculty number (max 10 chars)</li>
                <li><strong>password</strong> - Password (optional, defaults to 'password123')</li>
            </ul>
        </div>

        <form action="{{ route('admin.teachers.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Select Excel/CSV File</label>
                <input type="file" name="file" accept=".xlsx,.csv" class="w-full border-gray-300 rounded mt-1" required>
            </div>
            <div class="flex justify-end">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Import Teachers</button>
            </div>
        </form>
    </div>
</x-app-layout>