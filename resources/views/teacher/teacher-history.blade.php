@extends('layouts.mobile.mobile-app')

@section('header_title', 'History')

@section('content')

<div class="flex items-center space-x-2 mb-6">
    {{-- 🔍 Search Bar --}}
    <form action="{{ route('teacher.history') }}" method="GET" class="relative flex-grow">
        <input 
            type="text" 
            name="search"
            value="{{ request('search') }}"
            placeholder="Search subject, room..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        >
        <img src="{{ asset('images/aitas-icons/Dashboard/search.png') }}" 
             class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2" 
             alt="Search Icon">                                                   
    </form>  ,, .

    {{-- ⚙️ Filter Button --}}
    <button id="openFilterModal" type="button" class="flex-shrink-0 p-2 text-gray-600 hover:text-blue-600">
        <img src="{{ asset('images/aitas-icons/Dashboard/filter.png') }}" class="w-6 h-6" alt="Filter Icon"> 
    </button>
</div>

{{-- 📦 Filter Modal --}}
<div id="filterModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50 flex items-center justify-center">
    <div class="bg-white w-11/12 max-w-md mx-auto rounded-2xl shadow-lg p-6 relative">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 text-center">Filter Attendance</h2>

        {{-- Close Button --}}
        <button id="closeFilterModal" class="absolute top-3 right-4 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

        <form method="GET" action="{{ route('teacher.history') }}" class="space-y-4">
            {{-- Preserve search query --}}
            <input type="hidden" name="search" value="{{ request('search') }}">

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    <option value="">All</option>
                    <option value="Attended" {{ request('status') == 'Attended' ? 'selected' : '' }}>Attended</option>
                    <option value="Late" {{ request('status') == 'Late' ? 'selected' : '' }}>Late</option>
                    <option value="Missed" {{ request('status') == 'Missed' ? 'selected' : '' }}>Missed</option>
                    <option value="Upcoming" {{ request('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="Attending" {{ request('status') == 'Attending' ? 'selected' : '' }}>Attending</option>
                </select>
            </div>

            {{-- Date Range Filter --}}
            <div class="flex space-x-3">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                </div>
            </div>

            {{-- Subject Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Subject</label>
                <select name="subject" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                    <option value="">All</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                            {{ $subject }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex justify-between pt-4">
                <a href="{{ route('teacher.history') }}" 
                   class="bg-gray-300 text-gray-800 text-sm px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                    Reset
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 🧠 JS to toggle modal --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('filterModal');
    const openBtn = document.getElementById('openFilterModal');
    const closeBtn = document.getElementById('closeFilterModal');

    openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.add('hidden'); // close when clicking outside
    });
});
</script>

@php
    use Carbon\Carbon;
    $today = Carbon::now('Asia/Manila')->toDateString();
    $yesterday = Carbon::yesterday('Asia/Manila')->toDateString();
@endphp

@if ($attendanceHistory->isEmpty())
    <p class="text-center text-gray-500 mt-10">No attendance history found.</p>
@else
    @foreach ($attendanceHistory as $date => $records)
        @php
            $formattedDate = Carbon::parse($date)->format('M d, Y');
            $label = match(true) {
                $date === $today => 'Today - ' . $formattedDate,
                $date === $yesterday => 'Yesterday - ' . $formattedDate,
                default => 'Earlier - ' . $formattedDate
            };
        @endphp

        <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ $label }}</h2>

        @foreach ($records as $attendance)
        @php
            $status = $attendance->status;
            $borderColor = match($status) {
                'Attended' => 'border-green-500',
                'Late' => 'border-yellow-500',
                'Missed' => 'border-red-500',
                default => 'border-gray-300'
            };

            $statusColor = match($status) {
                'Attended' => 'text-green-700 bg-green-200',
                'Late' => 'text-yellow-700 bg-yellow-200',
                'Missed' => 'text-red-700 bg-red-200',
                default => 'text-gray-700 bg-gray-200'
            };

            $subject = $attendance->schedule?->subject?->subject_name ?? 'N/A';
            $room = $attendance->schedule?->room?->room_code ?? 'N/A';
            $start = $attendance->schedule?->starts_at ? Carbon::parse($attendance->schedule->starts_at)->format('g:i A') : '-';
            $end = $attendance->schedule?->ends_at ? Carbon::parse($attendance->schedule->ends_at)->format('g:i A') : '-';
        @endphp

            <div class="bg-white p-4 mb-3 rounded-xl shadow-sm flex justify-between items-center border-l-4 {{ $borderColor }}">
                <div>
                    <p class="text-base font-semibold text-gray-900 truncate">{{ $subject }}</p>
                    <p class="text-xs text-gray-600">{{ $start }} - {{ $end }}</p>
                    <p class="text-xs text-gray-600">{{ $room }}</p>
                </div>
                <span class="text-xs font-medium {{ $statusColor }} px-3 py-1 rounded-full flex-shrink-0">
                    {{ $status }}
                </span>
            </div>
        @endforeach
    @endforeach
@endif

{{-- 📤 Export Button --}}
<div class="flex justify-end pt-4 pb-20">
    {{-- Keep filter params when exporting --}}
    <form method="GET" action="{{ route('teacher.attendance.export') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        <input type="hidden" name="subject" value="{{ request('subject') }}">
        <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-full shadow-lg transition duration-150 ease-in-out">
            Export
        </button>
    </form>
</div>


@endsection
