@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Faculty Management')

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto"> {{-- Added max-width and adjusted padding for desktop --}}

    {{-- ================= Header + Actions Bar ================= --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h2 class="text-3xl font-extrabold text-gray-900 flex items-center">
            {{-- Updated icon size, color, and stroke for better visibility --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 mr-3 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m6-4a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Faculty Management
        </h2>

        {{-- Action Buttons Group (Add Dean/Teacher and Import) --}}
        <div class="flex flex-wrap gap-3">
            {{-- Import Users Button - Moved up for better access and grouped with other actions --}}
            <button type="button" class="open-modal-btn bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition shadow-md text-sm font-semibold flex items-center"
                    data-modal-target="importModal">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import Users
            </button>

            {{-- Add Dean/Teacher Button - Use a single primary action (Add Faculty) --}}
            <button type="button" class="open-modal-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-md text-sm font-semibold flex items-center"
                    data-modal-target="addDeanModal">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Faculty
            </button>
        </div>
    </div>

    {{-- ================= Search and Filter Bar ================= --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.teachers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or faculty ID..."
                    class="border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-base w-full sm:flex-1 transition shadow-sm"
                    aria-label="Search faculty members"> {{-- Improved input styling and accessibility --}}

            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg text-base hover:bg-blue-600 transition font-semibold shadow-md sm:w-auto">
                {{-- Changed to a primary color search button --}}
                Search
            </button>
        </form>
    </div>

    {{-- Success/Error Messages - Better visual hierarchy for alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-4 rounded-md" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-4 rounded-md" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    ---
    
    ## 👤 Faculty List

    {{-- ================= Users Cards ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"> {{-- Increased grid gap and added 4-column layout for larger screens --}}
        @forelse($users as $user)
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-5 flex flex-col hover:shadow-xl transition duration-300 ease-in-out"> {{-- Enhanced card styling --}}
                <div class="flex-grow">
                    <h3 class="text-xl font-semibold mb-1 text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-blue-600 font-medium capitalize mb-2">Role: **{{ $user->role->name }}**</p> {{-- Emphasized Role --}}
                    <p class="text-sm text-gray-600 truncate">Email: {{ $user->email }}</p>
                    <p class="text-sm text-gray-600">Faculty ID: **{{ $user->faculty_number }}**</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end gap-3"> {{-- Action buttons aligned to the right --}}
                    {{-- Edit Button --}}
                    <button class="edit-user-btn text-blue-600 hover:text-blue-800 text-sm font-medium transition"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-faculty-number="{{ $user->faculty_number }}"
                            data-modal-target="editUserModal"> {{-- Added data-modal-target for consistency --}}
                        Edit
                    </button>
                    {{-- Delete Form --}}
                    <form action="{{ route('admin.teachers.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}? This action cannot be undone.')"> {{-- Improved confirmation message --}}
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-full text-center py-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-6 h-6 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" />
                </svg>
                No faculty members found matching your criteria.
            </p>
        @endforelse
    </div>
</div>

{{-- ================= Modals (No major changes needed, just minor style alignment) ================= --}}

{{-- Add Dean/Faculty Modal --}}
<div id="addDeanModal" class="modal-wrapper hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 transition-opacity duration-300 pointer-events-none">
    <div class="bg-white rounded-xl shadow-2xl w-11/12 max-w-lg p-6 relative transform transition-transform duration-300">
        <button data-modal-close="addDeanModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-800 transition rounded-full p-1">&times;</button>
        <h3 class="text-2xl font-bold mb-6 text-gray-800">Add New Faculty Member</h3>
        <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-4">
            @csrf
            {{-- Consider passing 'role' via a dropdown or dedicated routes if other roles are possible --}}
            <input type="hidden" name="role" value="dean"> 
            <div>
                <label for="add_name" class="block text-sm font-medium mb-1 text-gray-700">Name</label>
                <input type="text" id="add_name" name="name" value="{{ old('name') }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            <div>
                <label for="add_email" class="block text-sm font-medium mb-1 text-gray-700">Email</label>
                <input type="email" id="add_email" name="email" value="{{ old('email') }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            <div>
                <label for="add_faculty_number" class="block text-sm font-medium mb-1 text-gray-700">Faculty ID</label>
                <input type="text" id="add_faculty_number" name="faculty_number" value="{{ old('faculty_number') }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            <div>
                <label for="add_password" class="block text-sm font-medium mb-1 text-gray-700">Password</label>
                <input type="password" id="add_password" name="password" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" data-modal-close="addDeanModal" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition shadow-md">Add Faculty</button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="importModal" class="modal-wrapper hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 transition-opacity duration-300 pointer-events-none">
    <div class="bg-white rounded-xl shadow-2xl w-11/12 max-w-lg p-6 relative transform transition-transform duration-300">
        <button data-modal-close="importModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-800 transition rounded-full p-1">&times;</button>
        <h3 class="text-2xl font-bold mb-6 text-gray-800">Import Faculty Data</h3>
        <form action="{{ route('admin.teachers.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="import_file" class="block text-sm font-medium mb-1 text-gray-700">Upload CSV/Excel File</label>
                <input type="file" id="import_file" name="file" class="w-full border-2 border-gray-300 rounded-lg text-sm p-2 transition focus:border-blue-500" required>
            </div>
            <p class="text-xs text-gray-500 pt-2">Accepted formats: .csv, .xlsx. Ensure your file columns match the required structure (e.g., name, email, faculty\_number, password).</p>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" data-modal-close="importModal" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold transition shadow-md">Import Data</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit User Modal --}}
<div id="editUserModal" class="modal-wrapper hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 transition-opacity duration-300 pointer-events-none">
    <div class="bg-white rounded-xl shadow-2xl w-11/12 max-w-lg p-6 relative transform transition-transform duration-300">
        <button data-modal-close="editUserModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-800 transition rounded-full p-1">&times;</button>
        <h3 class="text-2xl font-bold mb-6 text-gray-800">Edit Faculty Member</h3>
        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_name" class="block text-sm font-medium mb-1 text-gray-700">Name</label>
                <input type="text" name="name" id="edit_name" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            <div>
                <label for="edit_email" class="block text-sm font-medium mb-1 text-gray-700">Email</label>
                <input type="email" name="email" id="edit_email" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            <div>
                <label for="edit_faculty_number" class="block text-sm font-medium mb-1 text-gray-700">Faculty ID</label>
                <input type="text" name="faculty_number" id="edit_faculty_number" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" required>
            </div>
            {{-- Optional: Add a password field for reset with a clear label/hint --}}
            <div>
                <label for="edit_password" class="block text-sm font-medium mb-1 text-gray-700">New Password (optional)</label>
                <input type="password" name="password" id="edit_password" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-lg px-4 py-2 text-sm transition" placeholder="Leave blank to keep current password">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" data-modal-close="editUserModal" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition shadow-md">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= Scripts (No changes needed here, logic is sound) ================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden', 'pointer-events-none');
            document.body.style.overflow = 'hidden';
        }
    };
    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden', 'pointer-events-none');
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('.open-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => openModal(btn.dataset.modalTarget));
    });
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.dataset.modalClose));
    });

    // Edit User Button Logic
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const userId = btn.dataset.id;
            document.getElementById('edit_name').value = btn.dataset.name;
            document.getElementById('edit_email').value = btn.dataset.email;
            document.getElementById('edit_faculty_number').value = btn.dataset.facultyNumber;
            // Clear password field on open
            const passwordField = document.getElementById('edit_password');
            if (passwordField) passwordField.value = '';
            
            document.getElementById('editUserForm').action = `{{ url('admin/teachers') }}/${userId}`; // Use full URL path for robustness
            openModal('editUserModal');
        });
    });

    document.querySelectorAll('.modal-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', e => {
            if (e.target === wrapper) closeModal(wrapper.id);
        });
    });
});
</script>
@endsection