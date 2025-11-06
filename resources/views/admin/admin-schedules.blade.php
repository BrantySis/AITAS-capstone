@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Schedule Management')

@section('content')
<div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto relative z-10">

    {{-- ===================== HEADER & ADD BUTTON ===================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
        <h2 class="text-3xl font-extrabold text-gray-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 mr-3 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Class Schedules
        </h2>

        <button type="button" data-modal-target="addScheduleModal" class="open-modal-btn bg-blue-600 text-white font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-700 transition shadow-lg text-base flex items-center justify-center sm:w-auto w-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add New Schedule
        </button>
    </div>

    {{-- ===================== SEARCH & FILTER ===================== --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <form action="{{ route('admin.schedules.index') }}" method="GET" class="relative w-full lg:w-96 flex">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by teacher, subject, or room..."
                class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-l-xl px-4 py-2.5 pl-10 text-base focus:outline-none transition shadow-sm" />
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-3 top-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z" />
            </svg>
            <button type="submit" class="bg-blue-500 text-white px-4 rounded-r-xl hover:bg-blue-600 transition font-semibold">
                Go
            </button>
        </form>

        <div class="flex gap-2 overflow-x-auto text-sm pb-1">
            @php
                $filters = ['all' => 'All', 'upcoming' => 'Upcoming', 'in-progress' => 'Ongoing', 'missed' => 'Missed'];
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.schedules.index', array_merge(request()->except('page', 'search'), ['filter' => $key !== 'all' ? $key : null])) }}"
                   class="whitespace-nowrap px-4 py-2 rounded-full border-2 transition font-medium text-sm
                        {{ request('filter') == $key || ($key == 'all' && !request('filter')) 
                            ? 'bg-blue-600 text-white border-blue-600 shadow-md' 
                            : 'border-gray-300 text-gray-700 hover:bg-gray-100 hover:border-gray-400' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ===================== DESKTOP TABLE ===================== --}}
    <div class="hidden md:block overflow-x-auto bg-white rounded-xl shadow-2xl border border-gray-100">
        <table class="min-w-full table-auto text-sm text-left border-collapse">
            <thead class="bg-gray-50 text-gray-600 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Teacher</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Subject</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Units</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Course Year</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Semester</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">School Year</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Room</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 text-center">Time & Date</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-800 divide-y divide-gray-100">
                @forelse ($schedules ?? [] as $schedule)
                    <tr class="hover:bg-blue-50/50 transition duration-150">
                        <td class="px-6 py-4 font-medium">{{ $schedule->teacher->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 font-medium">{{ $schedule->subject->subject_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $schedule->subject->units ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $schedule->subject->course_year ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $schedule->subject->semester ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $schedule->subject->school_year ?? '—' }}</td>
                        <td class="px-6 py-4">{{ $schedule->room->room_code ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="block text-gray-700 font-semibold">{{ \Carbon\Carbon::parse($schedule->starts_at)->format('D, M j') }}</span>
                            <span class="text-xs text-blue-600 font-semibold">
                                {{ \Carbon\Carbon::parse($schedule->starts_at)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->ends_at)->format('h:i A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <button type="button" class="edit-schedule-btn text-blue-600 hover:text-blue-800 font-semibold text-sm mr-4"
                                data-id="{{ $schedule->id }}"
                                data-teacher-id="{{ $schedule->teacher_id }}"
                                data-subject-id="{{ $schedule->subject_id }}"
                                data-room-id="{{ $schedule->room_id }}"
                                data-day="{{ $schedule->day_of_week }}"
                                data-starts-at="{{ \Carbon\Carbon::parse($schedule->starts_at)->format('H:i') }}"
                                data-ends-at="{{ \Carbon\Carbon::parse($schedule->ends_at)->format('H:i') }}">
                                Edit
                            </button>

                            <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm"
                                    onclick="return confirm('Delete schedule for {{ $schedule->subject->subject_name ?? 'this subject' }}?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-gray-500 py-10">No schedules found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===================== PAGINATION ===================== --}}
    @if (method_exists($schedules ?? null, 'links'))
        <div class="mt-8">
            {{ $schedules->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>

    {{-- ===================== MOBILE CARDS (Updated for readability) ===================== --}}
    <div class="grid md:hidden gap-5">
        @forelse ($schedules ?? [] as $schedule)
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-600"> {{-- Card emphasis with left border --}}
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-extrabold text-gray-900 text-lg leading-tight">{{ $schedule->subject->subject_name ?? 'N/A' }}</h3>
                    <x-admin.schedule-status :schedule="$schedule" :attendanceMap="$attendanceMap ?? []" />
                </div>
                
                <div class="space-y-1 text-sm text-gray-700 mb-4 border-t pt-3">
                    <p class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Teacher: <span class="ml-1 font-semibold text-gray-900">{{ $schedule->teacher->name ?? 'N/A' }}</span>
                    </p>
                    <p class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18a2 2 0 002-2V7a2 2 0 00-2-2H3a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Room: <span class="ml-1 font-semibold text-gray-900">{{ $schedule->room->room_code ?? 'N/A' }}</span>
                    </p>
                    <p class="flex items-center text-blue-600 font-semibold pt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ \Carbon\Carbon::parse($schedule->starts_at)->format('D, M j | h:i A') }} - {{ \Carbon\Carbon::parse($schedule->ends_at)->format('h:i A') }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t pt-4">
                    <button type="button" class="edit-schedule-btn text-blue-600 hover:text-blue-800 font-semibold text-sm"
                        data-id="{{ $schedule->id }}"
                        data-teacher-id="{{ $schedule->teacher_id }}"
                        data-subject-id="{{ $schedule->subject_id }}"
                        data-room-id="{{ $schedule->room_id }}"
                        data-day="{{ $schedule->day_of_week }}"
                        data-starts-at="{{ \Carbon\Carbon::parse($schedule->starts_at)->format('H:i') }}"
                        data-ends-at="{{ \Carbon\Carbon::parse($schedule->ends_at)->format('H:i') }}">
                        Edit
                    </button>
                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm"
                                onclick="return confirm('WARNING: This will permanently delete the schedule. Are you sure?')">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-6">No schedules found matching your filters.</p>
        @endforelse
    </div>

    @if (method_exists($schedules ?? null, 'links'))
        <div class="mt-8">
            {{ $schedules->appends(request()->except('page'))->links() }} {{-- Added appends for consistent pagination --}}
        </div>
    @endif
</div>

{{-- ===================== ADD SCHEDULE MODAL (Styling alignment) ===================== --}}
<div id="addScheduleModal" class="modal-wrapper hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm pointer-events-none transition-opacity duration-300" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl transform transition-all max-w-lg w-full scale-95 opacity-0 duration-300">
            <div class="p-6 md:p-8">
                <div class="flex justify-between items-start">
                    <h3 class="text-2xl font-bold text-gray-900">Add New Schedule</h3>
                    <button type="button" data-modal-close="addScheduleModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <form action="{{ route('admin.schedules.store') }}" method="POST" class="mt-6 space-y-6">
                    @csrf
                    <x-admin.schedule-form :users="$teachers" :rooms="$rooms" :subjects="$subjects" /> 
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" data-modal-close="addScheduleModal"
                                class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition font-semibold">
                            Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== EDIT SCHEDULE MODAL (Styling alignment + input ID logic) ===================== --}}
<div id="editScheduleModal" class="modal-wrapper hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm pointer-events-none transition-opacity duration-300" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl transform transition-all max-w-lg w-full scale-95 opacity-0 duration-300">
            <div class="p-6 md:p-8">
                <div class="flex justify-between items-start">
                    <h3 class="text-2xl font-bold text-gray-900">Edit Schedule</h3>
                    <button type="button" data-modal-close="editScheduleModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <form id="editScheduleForm" method="POST" class="mt-6 space-y-6">
                    @csrf @method('PUT')
                    {{-- The component must use IDs like 'edit_teacher_id' when is-edit-mode="true" is passed --}}
                    <x-admin.schedule-form :users="$teachers" :rooms="$rooms" :subjects="$subjects" is-edit-mode="true" />
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" data-modal-close="editScheduleModal"
                                class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition font-semibold">
                            Update Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL SCRIPT (Final Polish) ===================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // Function to handle modal opening with basic transition support (requires CSS transition classes on modal)
    const openModal = (id) => {
        const modal = document.getElementById(id);
        if(modal) {
            modal.classList.remove('hidden', 'pointer-events-none'); 
            modal.querySelector('.shadow-2xl').classList.remove('scale-95', 'opacity-0');
            modal.querySelector('.shadow-2xl').classList.add('scale-100', 'opacity-100');
            document.body.style.overflow = 'hidden';
            modal.setAttribute('aria-hidden', 'false');
        }
    };

    // Function to handle modal closing
    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if(modal) {
            // Apply reverse transition classes before hiding
            modal.querySelector('.shadow-2xl').classList.remove('scale-100', 'opacity-100');
            modal.querySelector('.shadow-2xl').classList.add('scale-95', 'opacity-0');
            
            // Wait for transition to complete before hiding fully
            setTimeout(() => {
                modal.classList.add('hidden', 'pointer-events-none'); 
                document.body.style.overflow = '';
                modal.setAttribute('aria-hidden', 'true');
            }, 300); // 300ms matches the transition duration
        }
    };

    // Attach listeners for opening/closing modals
    document.querySelectorAll('.open-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-modal-target');
            if(target) openModal(target);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-modal-close');
            if(target) closeModal(target);
        });
    });
    
    // Edit Schedule Button Logic
    document.querySelectorAll('.edit-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const schedule = {
                id: btn.dataset.id,
                teacher_id: btn.dataset.teacherId,
                subject_id: btn.dataset.subjectId,
                room_id: btn.dataset.roomId,
                day_of_week: btn.dataset.day,
                starts_at: btn.dataset.startsAt,
                ends_at: btn.dataset.endsAt
            };

            const form = document.getElementById('editScheduleForm');
            // Ensure the form action is correctly set for PUT request
            form.action = `{{ url('admin/schedules') }}/${schedule.id}`; 
            
            // Populate the form fields using the 'edit_' prefix, assuming the component uses it when is-edit-mode="true"
            document.getElementById('edit_teacher_id').value = schedule.teacher_id;
            document.getElementById('edit_subject_id').value = schedule.subject_id;
            document.getElementById('edit_room_id').value = schedule.room_id;
            document.getElementById('edit_day_of_week').value = schedule.day_of_week;
            document.getElementById('edit_starts_at').value = schedule.starts_at;
            document.getElementById('edit_ends_at').value = schedule.ends_at;

            openModal('editScheduleModal');
        });
    });

    // Close modal when clicking outside
    document.querySelectorAll('.modal-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', e => {
            // Only close if the click target is the wrapper itself (not a child element)
            if(e.target === wrapper) closeModal(wrapper.id);
        });
    });
});
</script>
@endsection