<x-app-layout>
    <!-- Back Button -->
    <div class="max-w-7xl -ml-1 mx-auto mb-6">
        <a href="{{ route('dashboard.admin') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>

    <!-- Page Header and Filter Tabs -->
    <div class="mb-4">
        <div class="flex justify-between items-center mb-2">
            <h2 class="text-2xl font-bold">Schedules</h2>
            <a href="{{ route('admin.schedules.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Schedule</a>
        </div>
        
        <div class="flex gap-4 border-b pb-2 mb-4 text-sm">
            <a href="{{ route('admin.schedules.index') }}"
               class="{{ request('filter') == null ? 'text-blue-700 font-semibold border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600' }}">
               All
            </a>
            <a href="{{ route('admin.schedules.index', ['filter' => 'in-progress']) }}"
               class="{{ request('filter') == 'in-progress' ? 'text-blue-700 font-semibold border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600' }}">
               In Progress
            </a>
        </div>
    </div>

    <!-- Schedule Table -->
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full table-auto text-sm text-left border border-gray-200">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2">Teacher</th>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">Room</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Time</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-800">
                @foreach ($schedules as $schedule)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $schedule->teacher->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $schedule->subject }}</td>
                        <td class="px-4 py-2">{{ $schedule->room->room_code ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($schedule->starts_at)->format('D, M j') }}</td>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($schedule->starts_at)->format('h:i A') }} -
                            {{ \Carbon\Carbon::parse($schedule->ends_at)->format('h:i A') }}
                        </td>
                        <td class="px-4 py-2">
                            @if(in_array($schedule->id, $attendanceMap))
                                <span class="text-green-600 font-semibold">Attended</span>
                            @elseif(\Carbon\Carbon::parse($schedule->ends_at)->isPast())
                                <span class="text-red-600 font-semibold">Missed</span>
                            @else
                                <span class="text-yellow-600 font-medium">Upcoming</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2" onclick="return confirm('Delete this schedule?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach

                @if ($schedules->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-6">No schedules found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</x-app-layout>
