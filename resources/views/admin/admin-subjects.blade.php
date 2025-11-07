@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Subject Catalog')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
    {{-- ===================== HEADER & ACTIONS ===================== --}}
    <div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 flex items-center">
                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.5v11M17.5 12h-11M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path></svg>
                Subject Catalog
            </h1>
            <p class="text-base text-gray-600 mt-1">Manage all academic subjects and courses efficiently.</p>
        </div>

        <div class="space-x-3 flex flex-shrink-0">
            {{-- Add New Button (Primary Action) --}}
            <button type="button" data-modal-target="create-subject-modal"
                class="open-modal-btn bg-blue-600 text-white px-5 py-2.5 rounded-xl shadow-lg hover:bg-blue-700 transition duration-200 ease-in-out flex items-center text-sm font-semibold whitespace-nowrap">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Subject
            </button>

            {{-- Import Button (Secondary Action, less prominent blue) --}}
            <button type="button" data-modal-target="import-subjects-modal"
                class="open-modal-btn bg-blue-100 text-blue-700 px-5 py-2.5 rounded-xl hover:bg-blue-200 transition duration-200 ease-in-out flex items-center text-sm font-semibold whitespace-nowrap">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Import
            </button>
        </div>
    </div>

    ---

    {{-- ===================== FILTER & SEARCH (Enhanced Grouping) ===================== --}}
    <form method="GET" action="{{ route('admin.subjects.index') }}" class="mb-8 p-4 lg:p-6 bg-white rounded-2xl shadow-xl flex flex-col md:flex-row md:items-end gap-4">

        {{-- Department Dropdown (Kept separate for visual filtering) --}}
        @if(isset($departments) && $departments->count() > 0)
            <div class="flex-shrink-0 w-full md:w-56">
                <label for="department" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Department</label>
                <select name="department" id="department"
                    class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white appearance-none transition"
                    onchange="this.form.submit()">
                    <option value="" class="text-gray-500">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Search Bar & Button (Grouped) --}}
        <div class="flex-grow flex">
            <div class="relative flex-grow">
                <label for="search" class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Search Subject</label>
                <input type="text" name="search" id="search" placeholder="Code, Name, or Description..."
                    value="{{ request('search') }}"
                    class="w-full border-2 border-gray-300 rounded-l-xl px-4 py-2 pl-10 text-sm focus:ring-blue-500 focus:border-blue-500 transition focus:z-10 relative">
                <svg class="absolute left-3 top-[34px] h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z" /></svg>
            </div>

            {{-- Submit Button (Now attached to the search input) --}}
            <button type="submit"
                class="bg-blue-600 text-white px-5 py-2.5 rounded-r-xl shadow-md hover:bg-blue-700 text-sm transition font-semibold self-end">
                Go
            </button>
        </div>

        {{-- Reset Button (Only visible if filters are active) --}}
        @if(request('department') || request('search'))
            <a href="{{ route('admin.subjects.index') }}" 
                class="w-full md:w-auto text-center bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl hover:bg-gray-200 text-sm transition font-semibold self-end">
                Reset
            </a>
        @endif
    </form>

    ---

    {{-- ===================== SUBJECTS GROUPED VIEW (Enhanced Card Design) ===================== --}}
    @if(isset($subjects) && $subjects->count() > 0)
        <div class="space-y-12">
            @foreach($subjects as $department => $deptSubjects)
                <section>
                    <h2 class="text-2xl font-extrabold text-gray-800 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h14z"></path></svg>
                        {{ $department ?? 'Unassigned Subjects' }}
                        <span class="text-base font-medium text-gray-500 ml-3 bg-gray-100 px-3 py-0.5 rounded-full border border-gray-200">{{ $deptSubjects->count() }}</span>
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($deptSubjects as $subject)
                            <div class="bg-white p-6 shadow-xl rounded-2xl border border-gray-100 flex flex-col justify-between hover:ring-2 hover:ring-blue-500 transition duration-200">
                                
                                {{-- Subject Header --}}
                                <div class="mb-4">
                                    <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">Code: {{ $subject->subject_code }}</p>
                                    <h3 class="text-2xl font-bold text-gray-900 leading-snug mt-1 truncate">{{ $subject->subject_name }}</h3>
                                </div>

                                {{-- Details Grid --}}
                                <div class="grid grid-cols-2 gap-3 text-sm text-gray-700 border-y border-gray-100 py-4 mb-4">
                                    <p class="font-medium"><span class="text-gray-500">Units:</span> <span class="font-bold text-gray-900">{{ $subject->units }}</span></p>
                                    <p class="font-medium"><span class="text-gray-500">Year:</span> <span class="font-bold text-gray-900">{{ $subject->course_year ?? '—' }}</span></p>
                                    <p class="font-medium"><span class="text-gray-500">Semester:</span> <span class="font-bold text-gray-900">{{ $subject->semester ?? '—' }}</span></p>
                                    <p class="font-medium"><span class="text-gray-500">S.Y.:</span> <span class="font-bold text-gray-900">{{ $subject->school_year ?? '—' }}</span></p>
                                </div>

                                {{-- Description --}}
                                <p class="text-xs italic text-gray-500 line-clamp-2 mb-4">
                                    <span class="font-semibold text-gray-700">Description:</span> {{ Str::limit($subject->description ?? 'No description provided for this subject.', 80) }}
                                </p>

                                {{-- Actions --}}
                                <div class="flex justify-end items-center space-x-2 mt-auto pt-3 border-t border-gray-100">
                                    {{-- Edit Button: Clear, concise data attributes --}}
                                    <button class="edit-btn text-sm text-blue-600 hover:text-blue-800 font-medium px-4 py-1.5 rounded-lg hover:bg-blue-50 transition"
                                        data-id="{{ $subject->id }}"
                                        data-code="{{ $subject->subject_code }}"
                                        data-units="{{ $subject->units }}"
                                        data-name="{{ $subject->subject_name }}"
                                        data-desc="{{ $subject->description ?? '' }}"
                                        data-dept="{{ $subject->department ?? '' }}"
                                        data-course="{{ $subject->course_year ?? '' }}"
                                        data-semester="{{ $subject->semester ?? '' }}"
                                        data-school="{{ $subject->school_year ?? '' }}">
                                        <span class="mr-1">✏️</span> Edit
                                    </button>

                                    {{-- Delete Form: Using standard icon for better UX --}}
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-sm text-red-600 hover:text-red-800 font-medium px-4 py-1.5 rounded-lg hover:bg-red-50 transition"
                                            onclick="return confirm('Are you sure you want to permanently delete {{ $subject->subject_name }} ({{ $subject->subject_code }})?')">
                                            <span class="mr-1">🗑️</span> Delete
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
                <button type="button" data-modal-target="import-subjects-modal" class="open-modal-btn bg-blue-100 text-blue-700 px-6 py-2.5 rounded-lg hover:bg-blue-200 transition font-semibold">Import from File</button>
            </div>
        </div>
    @endif
</div>

{{-- ========================================================== --}}
{{-- 🚀 MODALS (Retaining logic, enhanced styling) 🚀 --}}
{{-- ========================================================== --}}

{{-- CREATE SUBJECT MODAL --}}
<div id="create-subject-modal" class="modal-wrapper hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm pointer-events-none transition-opacity duration-300" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg w-full scale-95 opacity-0 duration-300">
            <div class="p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Create New Subject</h3>
                <p class="text-sm text-gray-500 mb-6">Ensure all fields are accurate and complete.</p>

                <form method="POST" action="{{ route('admin.subjects.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Field structure simplified for clarity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
                            <input type="text" name="subject_code" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="e.g., CS-101">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Units</label>
                            <input type="number" name="units" min="1" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="e.g., 3">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                        <input type="text" name="subject_name" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="e.g., Introduction to Programming">
                    </div>
                    
                    {{-- Department Field Added for consistency in new creation --}}
                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department (Optional)</label>
                        <input type="text" name="department" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" placeholder="e.g., Computer Science">
                    </div>

                    <div class="grid grid-cols-3 gap-5 mt-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course Year</label>
                            <select name="course_year" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                                <option value="" disabled selected>Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select name="semester" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                                <option value="" disabled selected>Select Sem</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                            <select name="school_year" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                                <option value="" disabled selected>Select S.Y.</option>
                                @php $currentYear = date('Y'); @endphp
                                @for($i = $currentYear; $i <= $currentYear + 3; $i++)
                                    <option value="{{ $i }}-{{ $i + 1 }}">{{ $i }}-{{ $i + 1 }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Briefly describe the subject's focus."></textarea>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="create-subject-modal" class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition">Create Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT SUBJECT MODAL --}}
<div id="edit-subject-modal" class="modal-wrapper hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm pointer-events-none transition-opacity duration-300" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg w-full scale-95 opacity-0 duration-300">
            <div class="p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Edit Subject Details</h3>
                <p class="text-sm text-gray-500 mb-6">Update the subject information below.</p>

                <form method="POST" id="edit-subject-form">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
                            <input type="text" name="subject_code" id="edit_subject_code" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Units</label>
                            <input type="number" name="units" id="edit_units" min="1" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                        <input type="text" name="subject_name" id="edit_subject_name" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                    </div>

                    <div class="grid grid-cols-3 gap-5 mt-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Course Year</label>
                            <select name="course_year" id="edit_course_year" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select name="semester" id="edit_semester" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                                <option value="">Select Sem</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                            <select name="school_year" id="edit_school_year" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
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
                        <input type="text" name="department" id="edit_department" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition" placeholder="e.g., Computer Science">
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="edit_description" rows="3" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="edit-subject-modal" class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition">Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

---

{{-- ========================================================== --}}
{{-- 💻 JAVASCRIPT FOR MODAL FUNCTIONALITY (FIXED & ANIMATED) 💻 --}}
{{-- ========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Modal Control Functions with Animation ---
    const getInnerModal = (id) => document.getElementById(id)?.querySelector('.shadow-2xl');

    const openModal = id => {
        const modal = document.getElementById(id);
        const inner = getInnerModal(id);

        if (modal && inner) {
            // Step 1: Make container visible
            modal.classList.remove('hidden', 'pointer-events-none');
            modal.classList.add('flex'); // Use flex for centering
            document.body.style.overflow = 'hidden';

            // Step 2: Animate inner content
            requestAnimationFrame(() => {
                inner.classList.add('scale-100', 'opacity-100');
                inner.classList.remove('scale-95', 'opacity-0');
            });
        }
    };

    const closeModal = id => {
        const modal = document.getElementById(id);
        const inner = getInnerModal(id);

        if (modal && inner) {
            // Step 1: Animate inner content out
            inner.classList.remove('scale-100', 'opacity-100');
            inner.classList.add('scale-95', 'opacity-0');
            
            // Step 2: Hide container after transition duration (300ms)
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden', 'pointer-events-none');
                document.body.style.overflow = '';
            }, 300); 
        }
    };

    // --- 2. Event Listeners for Opening/Closing ---
    document.querySelectorAll('.open-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.modalTarget;
            // Ensure no data remains from previous session when opening ADD modal
            if(target === 'create-subject-modal') document.getElementById('create-subject-modal').querySelector('form').reset();
            openModal(target);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.modalClose));
    });

    // --- 3. Edit Button Logic (Data Transfer) ---
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

    // --- 4. Close modal on background click and ESC key ---
    document.querySelectorAll('.modal-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', e => {
            if (e.target === wrapper) closeModal(wrapper.id);
        });
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-wrapper:not(.hidden)').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // --- 5. Initial State Safety ---
    // Ensure all modals start in a fully closed/hidden state
    document.querySelectorAll('.modal-wrapper').forEach(modal => {
        modal.classList.add('hidden', 'pointer-events-none');
        modal.classList.remove('flex');
    });

});
</script>
@endsection