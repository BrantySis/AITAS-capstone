@extends('layouts.mobile.mobile-app-dean')

@section('header_title', 'Teachers')

@section('content')
<div class="p-4 space-y-4">

    @forelse ($teachers as $teacher)
        @php
            $attendance = $teacher->latestAttendance;
        @endphp

        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow">

            <p class="font-bold text-lg">{{ $teacher->name }}</p>

            {{-- Status --}}
            <p class="text-green-600 font-semibold">Attending</p>

            {{-- Time In --}}
            <p class="text-gray-600 dark:text-gray-300">
                <strong>Time In:</strong> {{ $attendance->time_in ?? '---' }}
            </p>

            {{-- Location --}}
            <p class="text-gray-600 dark:text-gray-300">
                <strong>Location:</strong> 
                {{ $attendance->latitude }}, {{ $attendance->longitude }}
            </p>

            {{-- Last Updated --}}
            <p class="text-sm text-gray-500 mt-1">
                Last updated: {{ $attendance->created_at->diffForHumans() }}
            </p>

        </div>

    @empty
        <p class="text-center text-gray-500">No teachers are currently attending.</p>
    @endforelse

</div>
@endsection
