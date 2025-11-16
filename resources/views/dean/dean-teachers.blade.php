@extends('layouts.mobile.mobile-app-dean')

@section('header_title', 'Teachers')

@section('content')
<div class="p-4 space-y-4">

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('dean.teachers') }}" class="mb-4">
        <input 
            type="text" 
            name="search" 
            value="{{ request('search') }}" 
            placeholder="Search teacher by name..." 
            class="w-full p-3 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-400 focus:outline-none"
        >
    </form>

    @forelse ($teachers as $teacher)
        @php
            $attendance = $teacher->latestAttendance;
            $schedule = optional($attendance)->schedule;
            $room = optional($schedule)->room;
            $subject = optional($schedule)->subject;
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 flex flex-col space-y-2 border border-gray-200 dark:border-gray-700 transition hover:shadow-xl">
            
            {{-- Teacher Name & Status --}}
            <div class="flex items-center justify-between">
                <p class="font-bold text-lg text-gray-900 dark:text-white">{{ $teacher->name }}</p>
                <span class="text-green-600 font-semibold flex items-center space-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Attending</span>
                </span>
            </div>

            {{-- Time In --}}
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a8 8 0 100 16 8 8 0 000-16z" />
                </svg>
                <p><strong>Time In:</strong> {{ $attendance->time_in ?? '---' }}</p>
            </div>

            {{-- Time Out --}}
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l-3 3" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a8 8 0 100 16 8 8 0 000-16z" />
                </svg>
                <p><strong>Time Out:</strong> {{ $attendance->time_out ?? '---' }}</p>
            </div>

            {{-- Room --}}
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M3 15h18M3 19h18" />
                </svg>
                <p><strong>Room:</strong> {{ $room->name ?? '---' }}</p>
            </div>

            {{-- Subject --}}
            <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.84 6.682L12 20l-7-2.74a12.083 12.083 0 01.84-6.682L12 14z" />
                </svg>
                <p><strong>Subject:</strong> {{ $subject->name ?? '---' }}</p>
            </div>

            {{-- Last Updated --}}
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Last updated: {{ optional($attendance->created_at)->diffForHumans() ?? '---' }}
            </p>

        </div>

    @empty
        <p class="text-center text-gray-500 dark:text-gray-400 mt-10">No teachers are currently attending.</p>
    @endforelse

</div>
@endsection
