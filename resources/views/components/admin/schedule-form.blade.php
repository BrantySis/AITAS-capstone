@props([
    'schedule' => null,
    'users' => [],
    'rooms' => [],
    'subjects' => [],
    'isEditMode' => false,
])

@php
    $prefix = $isEditMode ? 'edit_' : '';
@endphp

{{-- Show validation errors OUTSIDE modal (at top of page) --}}
@if ($errors->any())
    @push('page-alerts')
        <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg border border-red-300" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold mb-2">❌ {{ $isEditMode ? 'Failed to Update Schedule' : 'Failed to Create Schedule' }}</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endpush

    {{-- Automatically open modal if errors exist --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modalId = '{{ $isEditMode ? "editScheduleModal" : "addScheduleModal" }}';
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('modal-closed');
                    modal.classList.add('modal-open');
                    modal.setAttribute('aria-hidden', 'false');
                    const inner = modal.querySelector('.modal-inner');
                    if (inner) requestAnimationFrame(() => inner.classList.add('open'));
                    document.body.style.overflow = 'hidden';
                }
                
                // Scroll to top to see error
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    @endpush
@endif

{{-- Show errors inside modal as well --}}
@if ($errors->any())
    <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300">
        <p class="font-semibold mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    {{-- Teacher --}}
    <div>
        <label for="{{ $prefix }}user_id" class="block text-sm font-medium text-gray-700 mb-1">
            Teacher <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}user_id" name="user_id" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('user_id') border-red-500 @enderror">
            <option value="">Select Teacher</option>
            @foreach ($users->where('role_id', 2) as $teacher)
                <option value="{{ $teacher->id }}"
                    {{ old('user_id', $schedule->user_id ?? '') == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
        @error('user_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Subject --}}
    <div>
        <label for="{{ $prefix }}subject_id" class="block text-sm font-medium text-gray-700 mb-1">
            Subject <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}subject_id" name="subject_id" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('subject_id') border-red-500 @enderror">
            <option value="">Select Subject</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}"
                    {{ old('subject_id', $schedule->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                    {{ $subject->subject_code }} - {{ $subject->subject_name }}
                </option>
            @endforeach
        </select>
        @error('subject_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Units --}}
    <div>
        <label for="{{ $prefix }}units" class="block text-sm font-medium text-gray-700">
            Units <span class="text-red-500">*</span>
        </label>
        <input type="number" id="{{ $prefix }}units" name="units" required
            min="0" step="0.5"
            placeholder="Enter number of units"
            value="{{ old('units', $schedule->units ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('units') border-red-500 @enderror">
        @error('units')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Room --}}
    <div>
        <label for="{{ $prefix }}room_id" class="block text-sm font-medium text-gray-700 mb-1">
            Room <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}room_id" name="room_id" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('room_id') border-red-500 @enderror">
            <option value="">Select Room</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}"
                    {{ old('room_id', $schedule->room_id ?? '') == $room->id ? 'selected' : '' }}>
                    {{ $room->room_code }} {{ $room->building_name ? '(' . $room->building_name . ')' : '' }}
                </option>
            @endforeach
        </select>
        @error('room_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- EDP Code --}}
    <div>
        <label for="{{ $prefix }}edp_code" class="block text-sm font-medium text-gray-700 mb-1">
            EDP Code <span class="text-red-500">*</span>
        </label>
        <input type="text" id="{{ $prefix }}edp_code" name="edp_code" required
            placeholder="Enter EDP Code"
            value="{{ old('edp_code', $schedule->edp_code ?? '') }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('edp_code') border-red-500 @enderror">
        @error('edp_code')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Type --}}
    <div>
        <label for="{{ $prefix }}type" class="block text-sm font-medium text-gray-700 mb-1">
            Type <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}type" name="type" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
            <option value="">Select Type</option>
            <option value="lecture" {{ old('type', $schedule->type ?? '') == 'lecture' ? 'selected' : '' }}>Lecture</option>
            <option value="lab" {{ old('type', $schedule->type ?? '') == 'lab' ? 'selected' : '' }}>Laboratory</option>
        </select>
        @error('type')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Day of Week --}}
    <div>
        <label for="{{ $prefix }}day_of_week" class="block text-sm font-medium text-gray-700 mb-1">
            Day of Week <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}day_of_week" name="day_of_week" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('day_of_week') border-red-500 @enderror">
            <option value="">Select Day Pattern</option>
            @foreach([
                'MONDAY,WEDNESDAY,FRIDAY' => 'Monday, Wednesday, Friday (MWF)',
                'TUESDAY,THURSDAY' => 'Tuesday, Thursday (TTH)',
                'SATURDAY' => 'Saturday (Sat)',
                'SUNDAY' => 'Sunday (Sun)'
            ] as $value => $label)
                <option value="{{ $value }}" 
                    {{ old('day_of_week', $schedule->day_of_week ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('day_of_week')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Start/End Time --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="{{ $prefix }}starts_at" class="block text-sm font-medium text-gray-700 mb-1">
                Start Time <span class="text-red-500">*</span>
            </label>
            <input type="time" id="{{ $prefix }}starts_at" name="starts_at" required
                value="{{ old('starts_at', optional(optional($schedule)->starts_at)->format('H:i')) }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('starts_at') border-red-500 @enderror">
            @error('starts_at')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $prefix }}ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                End Time <span class="text-red-500">*</span>
            </label>
            <input type="time" id="{{ $prefix }}ends_at" name="ends_at" required
                value="{{ old('ends_at', optional(optional($schedule)->ends_at)->format('H:i')) }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('ends_at') border-red-500 @enderror">
            @error('ends_at')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Start/End Date --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="{{ $prefix }}start_date" class="block text-sm font-medium text-gray-700 mb-1">
                Start Date <span class="text-red-500">*</span>
            </label>
            <input type="date" id="{{ $prefix }}start_date" name="start_date" required
                value="{{ old('start_date', $schedule->start_date ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror">
            @error('start_date')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="{{ $prefix }}end_date" class="block text-sm font-medium text-gray-700 mb-1">
                End Date <span class="text-red-500">*</span>
            </label>
            <input type="date" id="{{ $prefix }}end_date" name="end_date" required
                value="{{ old('end_date', $schedule->end_date ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
            @error('end_date')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Semester --}}
    <div>
        <label for="{{ $prefix }}semester" class="block text-sm font-medium text-gray-700 mb-1">
            Semester <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}semester" name="semester" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('semester') border-red-500 @enderror">
            <option value="">Select Semester</option>
            @foreach(['1st'=>'1st Semester','2nd'=>'2nd Semester','Summer'=>'Summer'] as $key => $label)
                <option value="{{ $key }}" {{ old('semester', $schedule->semester ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('semester')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- School Year --}}
    <div>
        <label for="{{ $prefix }}school_year" class="block text-sm font-medium text-gray-700 mb-1">
            School Year <span class="text-red-500">*</span>
        </label>
        <input type="text" id="{{ $prefix }}school_year" name="school_year" required
            placeholder="e.g. 2025-2026"
            value="{{ old('school_year', $schedule->school_year ?? '') }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('school_year') border-red-500 @enderror">
        @error('school_year')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>