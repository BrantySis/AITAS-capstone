<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                <!-- Left arrow icon -->
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </a>
            <h2 class="text-2xl font-bold text-blue-700 text-center flex-grow">My Schedule</h2>
        </div>

        @if($schedules->count())
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md text-sm">
                    <thead class="bg-blue-100 text-blue-800 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left border-b">EDP Code</th>
                            <th class="px-6 py-3 text-left border-b">Subject</th>
                            <th class="px-6 py-3 text-left border-b">Units</th>
                            <th class="px-6 py-3 text-left border-b">Type</th>
                            <th class="px-6 py-3 text-left border-b">Day</th>
                            <th class="px-6 py-3 text-left border-b">Time</th>
                            <th class="px-6 py-3 text-left border-b">Room</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($schedules as $schedule)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $schedule->edp_code }}</td>
                                <td class="px-6 py-4">{{ $schedule->subject }}</td>
                                <td class="px-6 py-4">{{ $schedule->units }}</td>
                                <td class="px-6 py-4 capitalize">{{ $schedule->type }}</td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($schedule->starts_at)->format('l') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ \Carbon\Carbon::parse($schedule->starts_at)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->ends_at)->format('g:i A') }}
                                </td>
                                <td class="px-6 py-4">{{ $schedule->room->room_code ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center">You don't have any schedules assigned yet.</p>
        @endif
    </div>
</x-app-layout>
