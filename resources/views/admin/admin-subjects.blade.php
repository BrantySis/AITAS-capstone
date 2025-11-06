@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Subjects')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    {{-- ===================== HEADER & ACTIONS ===================== --}}
    <div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">Subject Catalog</h1>
            <p class="text-base text-gray-600">Manage all academic subjects and courses, grouped by department.</p>
        </div>

        <div class="space-x-3 flex flex-shrink-0">
            {{-- Add New Button (Primary Action) --}}
            <button type="button" data-modal-target="create-subject-modal"
                class="open-modal-btn **btn-primary** bg-blue-600 text-white px-5 py-2.5 rounded-xl shadow-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center text-sm font-semibold">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Subject
            </button>

            {{-- Import Button (Secondary Action) --}}
            <button type="button" data-modal-target="import-subjects-modal"
                class="open-modal-btn **btn-secondary** bg-green-500 text-white px-5 py-2.5 rounded-xl shadow-lg hover:bg-green-600 transition duration-150 ease-in-out flex items-center text-sm font-semibold">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import
            </button>
        </div>
    </div>

    ---

    {{-- ===================== FILTER & SEARCH ===================== --}}
    <form method="GET" action="{{ route('admin.subjects.index') }}" class="mb-8 p-4 bg-white rounded-xl shadow-md flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0">

        {{-- Department Dropdown --}}
        @if(isset($departments) && $departments->count() > 0)
            <div class="w-full sm:w-auto flex-shrink-0">
                <label for="department" class="block text-xs font-semibold text-gray-700 mb-1 uppercase">Filter by Department</label>
                <select name="department" id="department"
                    class="**input-select** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                    onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Search Bar --}}
        <div class="flex-grow">
            <label for="search" class="block text-xs font-semibold text-gray-700 mb-1 uppercase">Search Subject</label>
            <div class="relative">
                <input type="text" name="search" id="search" placeholder="Code, Name, or Description..."
                    value="{{ request('search') }}"
                    class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 pl-10 text-sm focus:ring-blue-500 focus:border-blue-500">
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                </svg>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="sm:flex-shrink-0">
            <button type="submit"
                class="w-full sm:w-auto bg-gray-500 text-white px-5 py-2.5 rounded-lg shadow-md hover:bg-gray-600 text-sm transition duration-150 font-semibold">
                Apply Filters
            </button>
        </div>
    </form>

    ---

    {{-- ===================== SUBJECTS GROUPED VIEW ===================== --}}
    @if(isset($subjects) && $subjects->count() > 0)
        <div class="space-y-10">
            @foreach($subjects as $department => $deptSubjects)
                <section>
                    <h2 class="text-2xl font-bold text-gray-800 mb-5 border-l-4 border-blue-600 pl-3">
                        {{ $department ?? 'Unassigned Subjects' }}
                        <span class="text-sm font-medium text-gray-500 ml-2">({{ $deptSubjects->count() }} total)</span>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($deptSubjects as $subject)
                            <div class="bg-white p-5 shadow-xl rounded-2xl border border-gray-100 flex flex-col justify-between hover:ring-2 hover:ring-blue-500 transition duration-200">
                                <div class="mb-3">
                                    <h3 class="text-xl font-bold text-gray-900 truncate">{{ $subject->subject_name }}</h3>
                                    <p class="text-sm font-mono text-blue-600 uppercase mt-0.5">Code: {{ $subject->subject_code }}</p>
                                </div>

                                <div class="text-sm text-gray-600 border-y border-gray-100 py-3 mb-4 space-y-1">
                                    <p><span class="font-semibold text-gray-700">Units:</span> {{ $subject->units }}</p>
                                    <p><span class="font-semibold text-gray-700">Course Year:</span> {{ $subject->course_year ?? '—' }}</p>
                                    <p><span class="font-semibold text-gray-700">Semester:</span> {{ $subject->semester ?? '—' }}</p>
                                    <p class="text-xs italic truncate mt-2"><span class="font-semibold text-gray-700">Description:</span> {{ Str::limit($subject->description ?? 'No description provided.', 80) }}</p>
                                </div>

                                <div class="flex justify-end items-center space-x-2 mt-auto pt-2">
                                    {{-- Edit Button: Clear, concise data attributes --}}
                                    <button class="edit-btn text-sm text-blue-600 hover:text-blue-800 font-medium px-3 py-1 rounded-lg hover:bg-blue-50 transition"
                                        data-id="{{ $subject->id }}"
                                        data-code="{{ $subject->subject_code }}"
                                        data-units="{{ $subject->units }}"
                                        data-name="{{ $subject->subject_name }}"
                                        data-desc="{{ $subject->description ?? '' }}"
                                        data-dept="{{ $subject->department ?? '' }}"
                                        data-course="{{ $subject->course_year ?? '' }}"
                                        data-semester="{{ $subject->semester ?? '' }}"
                                        data-school="{{ $subject->school_year ?? '' }}">
                                        ✏️ Edit
                                    </button>

                                    {{-- Delete Form: Using standard icon for better UX --}}
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-sm text-red-600 hover:text-red-800 font-medium px-3 py-1 rounded-lg hover:bg-red-50 transition"
                                            onclick="return confirm('WARNING: Are you sure you want to permanently delete {{ $subject->subject_name }} ({{ $subject->subject_code }})?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="p-12 text-center bg-white rounded-2xl shadow-xl border border-gray-100">
            <div class="text-gray-400 text-7xl mb-6">📚</div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">No Subjects Found</h3>
            <p class="text-gray-500 mb-6">Either clear your current filters, or start by adding your first subject.</p>
            <div class="space-x-4">
                <button type="button" data-modal-target="create-subject-modal" class="open-modal-btn bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow-md hover:bg-blue-700 transition font-semibold">Add Subject</button>
                <button type="button" data-modal-target="import-subjects-modal" class="open-modal-btn bg-green-500 text-white px-6 py-2.5 rounded-lg shadow-md hover:bg-green-600 transition font-semibold">Import from File</button>
            </div>
        </div>
    @endif
</div>

{{-- ========================================================== --}}
{{-- 🚀 CREATE SUBJECT MODAL (Self-Contained) 🚀 --}}
{{-- ========================================================== --}}
<div id="create-subject-modal" class="modal-wrapper hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-70 pointer-events-none transition duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-xl sm:w-full">
            <div class="bg-white p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Create New Subject</h3>
                <p class="text-sm text-gray-500 mb-6">Ensure all fields are accurate and complete.</p>

                <form method="POST" action="{{ route('admin.subjects.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
                            <input type="text" name="subject_code" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g., CS-101">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Units</label>
                            <input type="number" name="units" min="1" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g., 3">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                        <input type="text" name="subject_name" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required placeholder="e.g., Introduction to Programming">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mt-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course Year</label>
                            <select name="course_year" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select name="semester" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Semester</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                            <select name="school_year" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select S.Y.</option>
                                @php $currentYear = date('Y'); @endphp
                                @for($i = $currentYear; $i <= $currentYear + 3; $i++)
                                    <option value="{{ $i }}-{{ $i + 1 }}">{{ $i }}-{{ $i + 1 }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Briefly describe the subject's focus."></textarea>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="create-subject-modal" class="**btn-cancel** px-4 py-2 text-gray-600 hover:text-gray-800 font-medium rounded-lg hover:bg-gray-100 transition">Cancel</button>
                        <button type="submit" class="**btn-primary** bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition">Create Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================== --}}
{{-- ✏️ EDIT SUBJECT MODAL (Self-Contained) ✏️ --}}
{{-- ========================================================== --}}
<div id="edit-subject-modal" class="modal-wrapper hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-70 pointer-events-none transition duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-xl sm:w-full">
            <div class="bg-white p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Edit Subject Details</h3>
                <p class="text-sm text-gray-500 mb-6">Update the subject information below.</p>

                <form method="POST" id="edit-subject-form">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
                            <input type="text" name="subject_code" id="edit_subject_code" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Units</label>
                            <input type="number" name="units" id="edit_units" min="1" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mt-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course Year</label>
                            <select name="course_year" id="edit_course_year" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select name="semester" id="edit_semester" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select Semester</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                            <select name="school_year" id="edit_school_year" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select S.Y.</option>
                                @php $currentYear = date('Y'); @endphp
                                @for($i = $currentYear; $i <= $currentYear + 3; $i++)
                                    <option value="{{ $i }}-{{ $i + 1 }}">{{ $i }}-{{ $i + 1 }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" name="department" id="edit_department" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Computer Science">
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="edit_description" rows="3" class="**input-text** w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="edit-subject-modal" class="**btn-cancel** px-4 py-2 text-gray-600 hover:text-gray-800 font-medium rounded-lg hover:bg-gray-100 transition">Cancel</button>
                        <button type="submit" class="**btn-primary** bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition">Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- NOTE: The Import Modal (import-subjects-modal) needs to be defined for the button to function. For brevity, it is omitted here but the button remains. --}}

---

{{-- ========================================================== --}}
{{-- 💻 JAVASCRIPT FOR MODAL FUNCTIONALITY 💻 --}}
{{-- ========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Modal Control Functions
    const openModal = id => {
        const modal = document.getElementById(id);
        if (modal) {
            // Remove utility classes that hide/disable the modal
            modal.classList.remove('hidden', 'pointer-events-none');
            // Prevent scrolling on the main page
            document.body.style.overflow = 'hidden';
        }
    };

    const closeModal = id => {
        const modal = document.getElementById(id);
        if (modal) {
            // Add utility classes to hide/disable the modal
            modal.classList.add('hidden', 'pointer-events-none');
            // Re-enable scrolling on the main page
            document.body.style.overflow = '';
        }
    };

    // 2. Event Listeners for Opening/Closing
    document.querySelectorAll('.open-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn.dataset.modalTarget));
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.modalClose));
    });

    // 3. Edit Button Logic (Data Transfer)
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = btn.dataset; // Get all data-* attributes as an object

            // Populate form fields in the edit modal using their IDs
            document.getElementById('edit_subject_code').value = data.code;
            document.getElementById('edit_units').value = data.units;
            document.getElementById('edit_subject_name').value = data.name;
            document.getElementById('edit_description').value = data.desc;
            document.getElementById('edit_department').value = data.dept;
            document.getElementById('edit_course_year').value = data.course;
            document.getElementById('edit_semester').value = data.semester;
            document.getElementById('edit_school_year').value = data.school;

            // Set the dynamic form action URL
            const form = document.getElementById('edit-subject-form');
            form.action = `/admin/subjects/${data.id}`;

            openModal('edit-subject-modal');
        });
    });

    // 4. Close modal on background click
    document.querySelectorAll('.modal-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', e => {
            if (e.target === wrapper) closeModal(wrapper.id);
        });
    });
});
</script>
@endsection