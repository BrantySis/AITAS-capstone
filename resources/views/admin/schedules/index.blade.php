<!-- schedules index -->
<x-app-layout>
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Schedules</h2>
        <a href="{{ route('admin.schedules.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Schedule</a>
    </div>

    <table class="w-full border text-sm text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Teacher</th>
                <th class="p-2">Subject</th>
                <th class="p-2">Room</th>
                <th class="p-2">Date&Day</th>
                <th class="p-2">Time</th>
                <th class="p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($schedules as $schedule)
                <tr class="border-t">
                    <!-- Use teacher relationship here -->
                    <td class="p-2">{{ $schedule->teacher->name ?? 'N/A' }}</td>
                    <td class="p-2">{{ $schedule->subject }}</td>
                    <td class="p-2">{{ $schedule->room->room_code ?? 'N/A' }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($schedule->starts_at)->format('D, M j') }}</td>
<td class="p-2">
    {{ \Carbon\Carbon::parse($schedule->starts_at)->format('h:i A') }} -
    {{ \Carbon\Carbon::parse($schedule->ends_at)->format('h:i A') }}
</td>
                    <td class="p-2">
                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline ml-2" onclick="return confirm('Delete this schedule?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-app-layout>
