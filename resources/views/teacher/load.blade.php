<x-app-layout>

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">My Schedule</h2>

    @if($schedules->count())
        <table class="min-w-full bg-white rounded shadow">
            <thead>
                <tr class="bg-blue-100 text-sm text-blue-800 uppercase">
                    <th class="px-4 py-2">EDP Code</th>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">Units</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Day</th>
                    <th class="px-4 py-2">Time</th>
                    <th class="px-4 py-2">Room</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                    <tr class="border-t text-gray-700">
                        <td class="px-4 py-2">{{ $schedule->edp_code }}</td>
                        <td class="px-4 py-2">{{ $schedule->subject }}</td>
                        <td class="px-4 py-2">{{ $schedule->units }}</td>
                        <td class="px-4 py-2 capitalize">{{ $schedule->type }}</td>
                        <td class="px-4 py-2">{{ $schedule->day }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($schedule->time_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->time_end)->format('g:i A') }}</td>
                        <td class="px-4 py-2">{{ $schedule->room->room_code ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-500">You don't have any schedules assigned yet.</p>
    @endif
</div>
</x-app-layout>
