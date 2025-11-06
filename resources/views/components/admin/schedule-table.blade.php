@props(['schedules'])

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
                        <span class="block text-gray-700 font-semibold">
                            {{ \Carbon\Carbon::parse($schedule->starts_at)->format('D, M j') }}
                        </span>
                        <span class="text-xs text-blue-600 font-semibold">
                            {{ \Carbon\Carbon::parse($schedule->starts_at)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($schedule->ends_at)->format('h:i A') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <button type="button"
                            class="edit-schedule-btn text-blue-600 hover:text-blue-800 font-semibold text-sm mr-4"
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
                            <button type="submit"
                                class="text-red-600 hover:text-red-800 font-semibold text-sm"
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
