@props([
    'schedule' => null,
    'users' => [],
    'rooms' => [],
    'subjects' => [],
    'isEditMode' => false, // ✅ Prevent undefined variable error
])

@if ($errors->any())
    <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $prefix = $isEditMode ? 'edit_' : '';
@endphp

<div class="space-y-4">

    {{-- Teacher (filtered: role_id = 2) --}}
    <div>
        <label for="{{ $prefix }}user_id" class="block text-sm font-medium text-gray-700 mb-1">
            Teacher <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}user_id" name="user_id" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Teacher</option>
            @foreach ($users->where('role_id', 2) as $teacher)
                <option value="{{ $teacher->id }}"
                    {{ old('user_id', $schedule->user_id ?? '') == $teacher->id ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Subject --}}
    <div>
        <label for="{{ $prefix }}subject_id" class="block text-sm font-medium text-gray-700 mb-1">
            Subject <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}subject_id" name="subject_id" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Subject</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}"
                    {{ old('subject_id', $schedule->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                    {{ $subject->subject_code }} - {{ $subject->subject_name }}
                </option>
            @endforeach
        </select>
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
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

    {{-- Room --}}
    <div>
        <label for="{{ $prefix }}room_id" class="block text-sm font-medium text-gray-700 mb-1">
            Room <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}room_id" name="room_id" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Room</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}"
                    {{ old('room_id', $schedule->room_id ?? '') == $room->id ? 'selected' : '' }}>
                    {{ $room->room_code }} {{ $room->building_name ? '(' . $room->building_name . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- EDP Code --}}
    <div>
        <label for="{{ $prefix }}edp_code" class="block text-sm font-medium text-gray-700 mb-1">
            EDP Code <span class="text-red-500">*</span>
        </label>
        <input type="text" id="{{ $prefix }}edp_code" name="edp_code" required
            placeholder="Enter EDP Code"
            value="{{ old('edp_code', $schedule->edp_code ?? '') }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

    {{-- Type --}}
    <div>
        <label for="{{ $prefix }}type" class="block text-sm font-medium text-gray-700 mb-1">
            Type <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}type" name="type" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Type</option>
            <option value="lecture" {{ old('type', $schedule->type ?? '') == 'lecture' ? 'selected' : '' }}>Lecture</option>
            <option value="lab" {{ old('type', $schedule->type ?? '') == 'lab' ? 'selected' : '' }}>Laboratory</option>
        </select>
    </div>

    {{-- Day of Week --}}
    <div>
        <label for="{{ $prefix }}day_of_week" class="block text-sm font-medium text-gray-700 mb-1">
            Day of Week <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}day_of_week" name="day_of_week" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Day Pattern</option>
            <option value="MWF" {{ old('day_of_week', $schedule->day_of_week ?? '') == 'MWF' ? 'selected' : '' }}>Monday, Wednesday, Friday (MWF)</option>
            <option value="TTH" {{ old('day_of_week', $schedule->day_of_week ?? '') == 'TTH' ? 'selected' : '' }}>Tuesday, Thursday (TTH)</option>
            <option value="S" {{ old('day_of_week', $schedule->day_of_week ?? '') == 'S' ? 'selected' : '' }}>Saturday (S)</option>
        </select>
    </div>

    {{-- Time --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="{{ $prefix }}starts_at" class="block text-sm font-medium text-gray-700 mb-1">
                Start Time <span class="text-red-500">*</span>
            </label>
            <input type="time" id="{{ $prefix }}starts_at" name="starts_at" required
                value="{{ old('starts_at', optional(optional($schedule)->starts_at)->format('H:i')) }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="{{ $prefix }}ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                End Time <span class="text-red-500">*</span>
            </label>
            <input type="time" id="{{ $prefix }}ends_at" name="ends_at" required
                value="{{ old('ends_at', optional(optional($schedule)->ends_at)->format('H:i')) }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- Date Range --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="{{ $prefix }}start_date" class="block text-sm font-medium text-gray-700 mb-1">
                Start Date <span class="text-red-500">*</span>
            </label>
            <input type="date" id="{{ $prefix }}start_date" name="start_date" required
                value="{{ old('start_date', $schedule->start_date ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label for="{{ $prefix }}end_date" class="block text-sm font-medium text-gray-700 mb-1">
                End Date <span class="text-red-500">*</span>
            </label>
            <input type="date" id="{{ $prefix }}end_date" name="end_date" required
                value="{{ old('end_date', $schedule->end_date ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- Semester --}}
    <div>
        <label for="{{ $prefix }}semester" class="block text-sm font-medium text-gray-700 mb-1">
            Semester <span class="text-red-500">*</span>
        </label>
        <select id="{{ $prefix }}semester" name="semester" required
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Semester</option>
            <option value="1st" {{ old('semester', $schedule->semester ?? '') == '1st' ? 'selected' : '' }}>1st Semester</option>
            <option value="2nd" {{ old('semester', $schedule->semester ?? '') == '2nd' ? 'selected' : '' }}>2nd Semester</option>
            <option value="Summer" {{ old('semester', $schedule->semester ?? '') == 'Summer' ? 'selected' : '' }}>Summer</option>
        </select>
    </div>

    {{-- School Year --}}
    <div>
        <label for="{{ $prefix }}school_year" class="block text-sm font-medium text-gray-700 mb-1">
            School Year <span class="text-red-500">*</span>
        </label>
        <input type="text" id="{{ $prefix }}school_year" name="school_year" required
            placeholder="e.g. 2025-2026"
            value="{{ old('school_year', $schedule->school_year ?? '') }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

</div>
