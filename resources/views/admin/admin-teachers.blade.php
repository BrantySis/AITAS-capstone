@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Faculty Management')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">

    {{-- ===================== 1. TITLE ===================== --}}
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m6-4a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Faculty Management
        </h1>
    </div>

    {{-- ===================== 2. FILTER, SEARCH, & ACTIONS ROW 🚀 ===================== --}}
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6 mb-8">
        
        {{-- LEFT COLUMN: SEARCH & FILTERS --}}
        <form method="GET" action="{{ route('admin.teachers.index') }}" 
            class="flex flex-col md:flex-row md:items-center gap-4 flex-grow w-full lg:w-auto">

            {{-- 2.1. Search Input (Enhanced Styling) --}}
            <div class="relative flex-grow md:max-w-xl"> 
                <div class="flex">
                    <input type="text" name="search" id="search" placeholder="Search by name, email, or faculty ID..."
                        value="{{ request('search') }}"
                        class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-l-xl px-4 py-2.5 pl-11 text-base focus:outline-none transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z" />
                    </svg>
                    <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-r-xl shadow-md hover:bg-blue-700 text-sm transition font-semibold flex-shrink-0">
                        Search
                    </button>
                </div>
            </div>

            {{-- 2.2. Reset Button --}}
            @if(request('search'))
                <a href="{{ route('admin.teachers.index') }}"
                    class="text-center bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl hover:bg-gray-200 text-sm transition font-semibold flex-shrink-0 md:ml-2">
                    Reset
                </a>
            @endif
        </form>

        {{-- RIGHT COLUMN: ACTION BUTTONS (Add/Import) --}}
        <div class="space-x-3 flex flex-shrink-0 pt-1">
            
            {{-- Import Users --}}
            <button type="button" data-modal-target="importModal"
                class="open-modal-btn bg-blue-100 text-blue-700 px-5 py-2.5 rounded-xl hover:bg-blue-200 transition duration-200 ease-in-out flex items-center text-sm font-semibold whitespace-nowrap">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import
            </button>
            
            {{-- Add Faculty --}}
            <button type="button" data-modal-target="addFacultyModal"
                class="open-modal-btn bg-blue-600 text-white px-5 py-2.5 rounded-xl shadow-lg hover:bg-blue-700 transition duration-200 ease-in-out flex items-center text-sm font-semibold whitespace-nowrap">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Faculty
            </button>
        </div>
    </div>
    {{---}}

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- ================= Faculty List (Cards) ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($users as $user)
            {{-- Faculty Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xl p-6 flex flex-col justify-between hover:ring-2 hover:ring-blue-500 transition duration-300">
                <div class="flex-grow mb-4">
                    <p class="text-xs font-bold text-blue-600 tracking-widest uppercase mb-1">
                        {{ $user->role->name ?? 'User' }}
                    </p>
                    <h3 class="text-2xl font-bold text-gray-900 leading-tight mb-2">{{ $user->name }}</h3>
                    
                    <div class="space-y-1 text-sm text-gray-700 pt-3 border-t border-gray-100">
                        <p class="font-medium truncate">
                            <span class="text-gray-500">ID:</span> 
                            <span class="font-semibold text-gray-900">{{ $user->faculty_number }}</span>
                        </p>
                        <p class="font-medium truncate">
                            <span class="text-gray-500">Email:</span> 
                            <span class="font-semibold text-gray-900">{{ $user->email }}</span>
                        </p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 flex justify-end gap-3">
                    {{-- Edit Button --}}
                    <button class="edit-user-btn text-sm text-blue-600 hover:text-blue-800 font-medium px-3 py-1.5 rounded-lg hover:bg-blue-50 transition"
                        data-id="{{ $user->id }}"
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-faculty-number="{{ $user->faculty_number }}"
                        data-modal-target="editUserModal">
                        Edit
                    </button>
                    
                    {{-- Delete Form --}}
                    <form action="{{ route('admin.teachers.destroy', $user) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to permanently delete {{ $user->name }}? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            {{-- No Faculty Found Message --}}
            <div class="col-span-full p-12 text-center bg-white rounded-2xl shadow-xl border border-gray-100">
                <div class="text-gray-400 text-7xl mb-6">🧑‍🏫</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">No Faculty Members Found</h3>
                <p class="text-gray-500 mb-6">There are no faculty members matching your search criteria. Try adding one.</p>
                <button type="button" data-modal-target="addFacultyModal" class="open-modal-btn bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow-md hover:bg-blue-700 transition font-semibold">
                    Add Faculty
                </button>
            </div>
        @endforelse
    </div>
</div>

{{-- ========================================================== --}}
{{-- 🚀 MODALS (Styled to match the new UI) 🚀 --}}
{{-- ========================================================== --}}

{{-- ADD FACULTY MODAL --}}
<div id="addFacultyModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity duration-300 modal-wrapper" aria-hidden="true">
    <div class="absolute top-0 left-0 w-full h-full grid place-items-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg w-full scale-95 opacity-0 duration-300" id="addFacultyModalContent">
            <div class="p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Add Faculty Member</h3>
                <p class="text-sm text-gray-500 mb-6">Create a new faculty account.</p>

                <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="add_role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="add_role" name="role" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="teacher">Teacher</option>
                            <option value="dean">Dean</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="Full Name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Faculty ID</label>
                        <input type="text" name="faculty_number" maxlength="8" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="e.g., F1234567">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required placeholder="Secure Password">
                    </div>
                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="addFacultyModal" class="px-5 py-2.5 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition font-semibold">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-md transition">Add Faculty</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- IMPORT MODAL --}}
<div id="importModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity duration-300 modal-wrapper" aria-hidden="true">
    <div class="absolute top-0 left-0 w-full h-full grid place-items-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg w-full scale-95 opacity-0 duration-300" id="importModalContent">
            <div class="p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Import Faculty Data</h3>
                <p class="text-sm text-gray-500 mb-6">Upload a file to bulk import faculty accounts.</p>

                <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload File</label>
                        <input type="file" name="file" class="w-full border-2 border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500 focus:border-blue-500 transition file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        <p class="text-xs text-gray-500 mt-2">Accepted formats: **.csv, .xlsx** (Columns needed: `name`, `email`, `faculty_number`, `password`)</p>
                    </div>
                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="importModal" class="px-5 py-2.5 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition font-semibold">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-md transition">Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editUserModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity duration-300 modal-wrapper" aria-hidden="true">
    <div class="absolute top-0 left-0 w-full h-full grid place-items-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg w-full scale-95 opacity-0 duration-300" id="editUserModalContent">
            <div class="p-6 sm:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Edit Faculty Details</h3>
                <p class="text-sm text-gray-500 mb-6">Update the faculty member's information.</p>

                <form id="editUserForm" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="edit_name" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="edit_email" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Faculty ID</label>
                        <input type="text" name="faculty_number" id="edit_faculty_number" maxlength="8" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
                        <input type="password" name="password" id="edit_password" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-5">
                        <button type="button" data-modal-close="editUserModal" class="px-5 py-2.5 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition font-semibold">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-md transition">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
            modal.classList.remove('hidden');
            modal.classList.add('flex'); // Use flex for centering
            modal.classList.remove('pointer-events-none');
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
                modal.classList.add('hidden');
                modal.classList.add('pointer-events-none');
                document.body.style.overflow = '';
            }, 300); 
        }
    };

    // --- 2. Event Listeners for Opening/Closing ---
    document.querySelectorAll('.open-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.modalTarget;
            openModal(target);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.modalClose));
    });

    // --- 3. Edit Button Logic (Data Transfer) ---
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = btn.dataset; 
            
            // Populate form fields in the edit modal using their IDs
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_faculty_number').value = data.facultyNumber;
            document.getElementById('edit_password').value = ''; // Always clear password field on open

            // Set the dynamic form action URL
            const form = document.getElementById('editUserForm');
            // Assuming base URL logic for resource route is correct
            form.action = `{{ url('admin/teachers') }}/${data.id}`;

            openModal('editUserModal');
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
                if (modal.classList.contains('flex')) {
                    closeModal(modal.id);
                }
            });
        }
    });

    // --- 5. Initial State Safety (for animation preparation) ---
    document.querySelectorAll('.modal-wrapper').forEach(modal => {
        const inner = getInnerModal(modal.id);
        if(inner) {
             // Reset animation state for initial load
            inner.classList.add('scale-95', 'opacity-0');
            inner.classList.remove('scale-100', 'opacity-100');
        }
        // Ensure initial hidden state
        modal.classList.add('hidden', 'pointer-events-none');
        modal.classList.remove('flex');
    });

});
</script>
@endsection