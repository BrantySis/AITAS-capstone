<x-app-layout title="Import Subjects">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Import Subjects</h1>
            <p class="text-gray-600">Upload an Excel or CSV file to import multiple subjects</p>
        </div>

        <!-- Sample Template Download -->
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <h3 class="text-sm font-medium text-green-800 mb-2">📥 Download Sample Template</h3>
            <p class="text-sm text-green-700 mb-3">Download a sample Excel file to see the correct format:</p>
            <a href="{{ route('admin.subjects.downloadTemplate') }}" 
               class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                📄 Download Template
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('admin.subjects.import.process') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                    <input type="file" name="file" id="file" accept=".xlsx,.csv,.xls"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('file')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-2">Accepted formats: .xlsx, .csv, .xls (Max: 2MB)</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">📋 File Format Requirements:</h3>
                    <div class="text-sm text-blue-700">
                        <p class="mb-2">Your Excel/CSV file must have these column headers (first row):</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <ul class="space-y-1">
                                    <li><strong>subject_code</strong> - Unique code (e.g., "CS101")</li>
                                    <li><strong>subject_name</strong> - Full subject name</li>
                                </ul>
                            </div>
                            <div>
                                <ul class="space-y-1">
                                    <li><strong>units</strong> - Number of units (1-10)</li>
                                    <li><strong>description</strong> - Optional description</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Data Preview -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-6">
                    <h3 class="text-sm font-medium text-gray-800 mb-2">📊 Sample Data Format:</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-2 py-1 text-left">subject_code</th>
                                    <th class="px-2 py-1 text-left">subject_name</th>
                                    <th class="px-2 py-1 text-left">units</th>
                                    <th class="px-2 py-1 text-left">description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t">
                                    <td class="px-2 py-1">CS101</td>
                                    <td class="px-2 py-1">Introduction to Programming</td>
                                    <td class="px-2 py-1">3</td>
                                    <td class="px-2 py-1">Basic programming concepts</td>
                                </tr>
                                <tr class="border-t">
                                    <td class="px-2 py-1">MATH201</td>
                                    <td class="px-2 py-1">Calculus I</td>
                                    <td class="px-2 py-1">4</td>
                                    <td class="px-2 py-1">Differential calculus</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.subjects.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Import Subjects
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>



