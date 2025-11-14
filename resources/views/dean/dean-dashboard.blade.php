@extends('layouts.mobile.mobile-app-dean')

@section('header_title', 'Dashboard')

@section('content')

<div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto">

    {{-- Dashboard Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Teachers</h3>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $totalTeachers ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Active Schedules</h3>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $activeSchedules ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Other Info</h3>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">—</p>
        </div>
    </div>

    {{-- Placeholder for additional content --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
        Welcome to the Dean Dashboard. Content will appear here.
    </div>

</div>

@endsection
