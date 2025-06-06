@props(['schedule' => null, 'users', 'rooms'])

<div>
    <label class="block">Teacher</label>
    <select name="user_id" class="w-full border p-2 rounded" required>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $schedule->user_id ?? '') == $user->id)>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
    @error('user_id')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">Room</label>
    <select name="room_id" class="w-full border p-2 rounded" required>
        @foreach ($rooms as $room)
            <option value="{{ $room->id }}" @selected(old('room_id', $schedule->room_id ?? '') == $room->id)>
                {{ $room->room_code }}
            </option>
        @endforeach
    </select>
    @error('room_id')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">EDP Code</label>
    <input type="text" name="edp_code" class="w-full border p-2 rounded" value="{{ old('edp_code', $schedule->edp_code ?? '') }}" required>
    @error('edp_code')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">Subject</label>
    <input type="text" name="subject" class="w-full border p-2 rounded" value="{{ old('subject', $schedule->subject ?? '') }}" required>
    @error('subject')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">Units</label>
    <input type="number" name="units" class="w-full border p-2 rounded" value="{{ old('units', $schedule->units ?? '') }}" min="1" required>
    @error('units')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">Type</label>
    <select name="type" class="w-full border p-2 rounded" required>
        <option value="lecture" @selected(old('type', $schedule->type ?? '') == 'lecture')>Lecture</option>
        <option value="lab" @selected(old('type', $schedule->type ?? '') == 'lab')>Lab</option>
    </select>
    @error('type')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">Start Date & Time</label>
    <input type="datetime-local" name="starts_at" class="w-full border p-2 rounded"
        value="{{ old('starts_at', isset($schedule) ? \Carbon\Carbon::parse($schedule->starts_at)->format('Y-m-d\TH:i') : '') }}"
        required>
    @error('starts_at')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block">End Date & Time</label>
    <input type="datetime-local" name="ends_at" class="w-full border p-2 rounded"
        value="{{ old('ends_at', isset($schedule) ? \Carbon\Carbon::parse($schedule->ends_at)->format('Y-m-d\TH:i') : '') }}"
        required>
    @error('ends_at')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
