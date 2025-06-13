<x-app-layout>
    <!-- Back Button -->
    <div class="max-w-7xl -ml-1 mx-auto mb-6">
        <a href="{{ route('dashboard.teacher') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
    </div>

    <div class="max-w-6xl mx-auto p-6">
        <!-- Title & Filters -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-2xl font-bold text-blue-700 text-center md:text-left">My Schedule</h2>

            <div class="flex gap-4 text-sm">
                <a href="{{ route('teacher.load') }}"
                   class="{{ request('filter') === null ? 'text-blue-700 font-semibold border-b-2 border-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                    All
                </a>
                <a href="{{ route('teacher.load', ['filter' => 'in-progress']) }}"
                   class="{{ request('filter') === 'in-progress' ? 'text-blue-700 font-semibold border-b-2 border-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                    In Progress
                </a>
                <a href="{{ route('teacher.load', ['filter' => 'upcoming']) }}"
                   class="{{ request('filter') === 'upcoming' ? 'text-blue-700 font-semibold border-b-2 border-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                    Upcoming
                </a>
                <a href="{{ route('teacher.load', ['filter' => 'past']) }}"
                   class="{{ request('filter') === 'past' ? 'text-blue-700 font-semibold border-b-2 border-blue-700' : 'text-gray-600 hover:text-blue-600' }}">
                    Past
                </a>
            </div>
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
                            <th class="px-6 py-3 text-left border-b">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($schedules as $schedule)
                            @php
                                $now = now();
                                $start = \Carbon\Carbon::parse($schedule->starts_at);
                                $end = \Carbon\Carbon::parse($schedule->ends_at);

                                if ($now->between($start, $end)) {
                                    $status = 'In Progress';
                                    $color = 'bg-yellow-100 text-yellow-800';
                                } elseif ($now->lt($start)) {
                                    $status = 'Upcoming';
                                    $color = 'bg-blue-100 text-blue-800';
                                } elseif ($now->gt($end)) {
                                    $status = 'Past';
                                    $color = 'bg-gray-200 text-gray-700';
                                }
                            @endphp

                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">{{ $schedule->edp_code }}</td>
                                <td class="px-6 py-4">{{ $schedule->subject }}</td>
                                <td class="px-6 py-4">{{ $schedule->units }}</td>
                                <td class="px-6 py-4 capitalize">{{ $schedule->type }}</td>
                                <td class="px-6 py-4">
                                    {{ $start->format('l') }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $start->format('g:i A') }} - {{ $end->format('g:i A') }}
                                </td>
                                <td class="px-6 py-4">{{ $schedule->room->room_code ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                @if(in_array($schedule->id, $attendedScheduleIds))
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Attended
                                    </span>
                                @elseif($now->gt($end))
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Missed
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                                        {{ $status }}
                                    </span>
                                @endif
                            </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center mt-8">You don't have any schedules assigned for this filter.</p>
        @endif
    </div>
</x-app-layout>
