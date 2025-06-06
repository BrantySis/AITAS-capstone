<x-app-layout>
<h2 class="text-2xl font-bold mb-4">My Schedule for Today</h2>

@if($schedules->isEmpty())
    <p class="text-gray-500">You don't have any schedules today.</p>
@else
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="border px-4 py-2">Subject</th>
                <th class="border px-4 py-2">Time</th>
                <th class="border px-4 py-2">Room</th>
                <th class="border px-4 py-2">Day</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $schedule)
                <tr>
                    <td class="border px-4 py-2">{{ $schedule->subject }}</td>
                    <td class="border px-4 py-2">{{ $schedule->time_start }} - {{ $schedule->time_end }}</td>
                    <td class="border px-4 py-2">{{ $schedule->room->name ?? 'N/A' }}</td>
                    <td class="border px-4 py-2">{{ $schedule->day }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

</x-app-layout>
