@props([
    'schedule' => null,
    'users' => [],
    'rooms' => [],
    'subjects' => [],
    'isEditMode' => false, {{-- ✅ Fix: Prevent undefined variable error --}}
])

{{-- ✅ Display validation and conflict errors --}}
@if ($errors->any())
    <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-300">
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
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Teacher <span class="text-red-500">*</span>
        </label>
        <select name="user_id" required
            id="{{ $isEditMode ? 'edit_user_id' : 'user_id' }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Teacher</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}"
                    {{ old('user_id', $schedule->user_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Subject --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Subject <span class="text-red-500">*</span>
        </label>
        <select name="subject_id" required
            id="{{ $isEditMode ? 'edit_subject_id' : 'subject_id' }}"
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
        <label for="{{ $isEditMode ? 'edit_units' : 'units' }}" class="block text-sm font-medium text-gray-700">
            Units <span class="text-red-500">*</span>
        </label>
        <input 
            type="number" 
            name="units" 
            id="{{ $isEditMode ? 'edit_units' : 'units' }}" 
            value="{{ old('units', $schedule->units ?? '') }}"
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            placeholder="Enter number of units"
            min="0"
            step="0.5"
            required
        >
    </div>

    {{-- Room --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Room <span class="text-red-500">*</span>
        </label>
        <select name="room_id" required
            id="{{ $isEditMode ? 'edit_room_id' : 'room_id' }}"
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
        <label class="block text-sm font-medium text-gray-700 mb-1">
            EDP Code <span class="text-red-500">*</span>
        </label>
        <input type="text" name="edp_code" required
            id="{{ $isEditMode ? 'edit_edp_code' : 'edp_code' }}"
            placeholder="Enter EDP Code"
            value="{{ old('edp_code', $schedule->edp_code ?? '') }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

    {{-- Type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Type <span class="text-red-500">*</span>
        </label>
        <select name="type" required
            id="{{ $isEditMode ? 'edit_type' : 'type' }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Type</option>
            <option value="lecture" {{ old('type', $schedule->type ?? '') == 'lecture' ? 'selected' : '' }}>Lecture</option>
            <option value="lab" {{ old('type', $schedule->type ?? '') == 'lab' ? 'selected' : '' }}>Laboratory</option>
        </select>
    </div>

    {{-- Days --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Days <span class="text-red-500">*</span>
        </label>
        <div class="flex flex-wrap gap-3">
            @php
                $oldDays = old('days', $schedule->days ?? '');
                $selectedDays = is_array($oldDays) ? $oldDays : str_split($oldDays);
                $dayOptions = ['M' => 'Mon', 'T' => 'Tue', 'W' => 'Wed', 'H' => 'Thu', 'F' => 'Fri', 'S' => 'Sat'];
            @endphp
            @foreach ($dayOptions as $code => $day)
                <label class="flex items-center space-x-1">
                    <input type="checkbox" name="days[]" value="{{ $code }}"
                        {{ in_array($code, $selectedDays) ? 'checked' : '' }}
                        class="text-blue-600 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">{{ $day }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Time --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Start Time <span class="text-red-500">*</span>
            </label>
            <input type="time" name="starts_at" required
                id="{{ $isEditMode ? 'edit_starts_at' : 'starts_at' }}"
                value="{{ old('starts_at', $schedule->starts_at ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                End Time <span class="text-red-500">*</span>
            </label>
            <input type="time" name="ends_at" required
                id="{{ $isEditMode ? 'edit_ends_at' : 'ends_at' }}"
                value="{{ old('ends_at', $schedule->ends_at ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- Dates --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Start Date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="start_date" required
                id="{{ $isEditMode ? 'edit_start_date' : 'start_date' }}"
                value="{{ old('start_date', $schedule->start_date ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                End Date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="end_date" required
                id="{{ $isEditMode ? 'edit_end_date' : 'end_date' }}"
                value="{{ old('end_date', $schedule->end_date ?? '') }}"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- Semester --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Semester <span class="text-red-500">*</span>
        </label>
        <select name="semester" required
            id="{{ $isEditMode ? 'edit_semester' : 'semester' }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Semester</option>
            <option value="1st" {{ old('semester', $schedule->semester ?? '') == '1st' ? 'selected' : '' }}>1st Semester</option>
            <option value="2nd" {{ old('semester', $schedule->semester ?? '') == '2nd' ? 'selected' : '' }}>2nd Semester</option>
            <option value="Summer" {{ old('semester', $schedule->semester ?? '') == 'Summer' ? 'selected' : '' }}>Summer</option>
        </select>
    </div>

    {{-- School Year --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            School Year <span class="text-red-500">*</span>
        </label>
        <input type="text" name="school_year" required
            id="{{ $isEditMode ? 'edit_school_year' : 'school_year' }}"
            placeholder="e.g. 2024-2025"
            value="{{ old('school_year', $schedule->school_year ?? '') }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>
</div>
