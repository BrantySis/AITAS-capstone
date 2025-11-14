@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Admin Dashboard')

@section('content')

{{-- Base background set to bright white/light gray --}}
<div class="bg-gray-200 -m-6 p-4 min-h-screen transition-all">

    {{-- ===================== SYSTEM OVERVIEW METRICS ===================== --}}
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 tracking-tight flex items-center">
        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9.25 10.25M9.25 10.25V7.75M16.25 17L15.75 10.25M15.75 10.25V7.75M12.5 4.75L12 17.25M12 17.25H9.5M12 17.25H14.5M6 6H18C19.1046 6 20 6.89543 20 8V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V8C4 6.89543 4.89543 6 6 6Z"></path></svg>
        System Overview
    </h2>

    <div class="grid grid-cols-2 gap-4">

        {{-- Standardized Card Look for consistency and professionalism --}}

        <div class="p-4 rounded-xl bg-white shadow-lg border-b-4 border-blue-600 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Total Teachers</p>
            <h3 class="text-3xl font-extrabold text-blue-700 mt-1">{{ $totalTeachers }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-b-4 border-blue-500 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Total Deans</p>
            <h3 class="text-3xl font-extrabold text-blue-700 mt-1">{{ $totalDeans }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-b-4 border-indigo-500 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Active Subjects</p>
            <h3 class="text-3xl font-extrabold text-indigo-700 mt-1">{{ $activeSubjects }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-b-4 border-indigo-400 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Active Schedules</p>
            <h3 class="text-3xl font-extrabold text-indigo-700 mt-1">{{ $activeSchedules }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-b-4 border-blue-600 transition duration-300 hover:shadow-xl col-span-2">
            <p class="text-sm font-medium text-gray-500">Active Rooms</p>
            <h3 class="text-3xl font-extrabold text-blue-700 mt-1">{{ $activeRooms }}</h3>
        </div>
    </div>

    <hr class="my-8 border-gray-200">

    {{-- ===================== DAILY MONITORING (Color-Coded for status) ===================== --}}
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 tracking-tight flex items-center">
        <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Today's Snapshot
    </h2>

    <div class="grid grid-cols-2 gap-4">

        <div class="p-4 rounded-xl bg-white shadow-lg border-l-4 border-blue-600 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Today’s Classes</p>
            <h3 class="text-3xl font-extrabold text-gray-900 mt-1">{{ $todaysClasses }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-l-4 border-green-500 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Attendance Today</p>
            <h3 class="text-3xl font-extrabold text-green-600 mt-1">{{ $attendanceToday }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-l-4 border-yellow-500 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Late Today</p>
            <h3 class="text-3xl font-extrabold text-yellow-600 mt-1">{{ $lateToday }}</h3>
        </div>

        <div class="p-4 rounded-xl bg-white shadow-lg border-l-4 border-red-600 transition duration-300 hover:shadow-xl">
            <p class="text-sm font-medium text-gray-500">Absent Today</p>
            <h3 class="text-3xl font-extrabold text-red-600 mt-1">{{ $absentToday }}</h3>
        </div>
    </div>

    <hr class="my-8 border-gray-200">

    {{-- ===================== RESOURCE DISTRIBUTION CHART (Light background for contrast) ===================== --}}
    <div class="mt-8 rounded-2xl p-6 bg-white shadow-xl text-gray-900 w-full border border-gray-200">
        <h3 class="text-2xl font-semibold mb-6 border-b border-gray-200 pb-3 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 6-6M9 6h6m-6 0h.01M18 6h.01M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            Resource Distribution
        </h3>

        @php
            $total = max($totalTeachers + $activeRooms + $activeSubjects, 1);
            $teachersBar = ($totalTeachers / $total) * 100;
            $roomsBar    = ($activeRooms / $total) * 100;
            $subjectsBar = ($activeSubjects / $total) * 100;
        @endphp

        <div class="relative w-full">

            {{-- ================= LEFT Y-AXIS LABELS ================= --}}
            <div class="absolute left-0 top-0 bottom-0 flex flex-col justify-between text-sm text-gray-500 pr-4 z-10">
                <span class="transform -translate-y-1/2">100%</span>
                <span class="transform -translate-y-1/2">50%</span>
                <span class="transform -translate-y-1/2">0%</span>
            </div>

            {{-- ================= GRID LINES ================= --}}
            <div class="absolute left-12 right-0 top-0 bottom-0 pointer-events-none">
                <div class="absolute top-0 w-full border-t border-gray-300 border-dashed"></div>
                <div class="absolute top-1/2 w-full border-t border-gray-300 border-dashed"></div>
                <div class="absolute bottom-0 w-full border-t border-gray-400"></div>
            </div>

            {{-- ================= BAR CHART CONTAINERS ================= --}}
            <div class="pl-12 flex flex-col sm:flex-row justify-around items-end gap-6 mt-4 h-64">

                {{-- TEACHERS --}}
                <div class="flex-1 flex flex-col items-center min-w-[80px] h-full">
                    <div class="w-full h-full bg-gray-200 rounded-lg relative overflow-hidden flex justify-center border-b-2 border-blue-600">
                        {{-- Bar Fill (Darker blue for contrast) --}}
                        <div class="absolute bottom-0 w-full bg-blue-600/90 hover:bg-blue-500 transition-all duration-700"
                             style="height: {{ $teachersBar }}%;">
                        </div>
                        {{-- Value Label (Dark text for visibility on light/dark backgrounds) --}}
                        <span class="absolute top-1/2 transform -translate-y-1/2 text-2xl font-bold drop-shadow-md z-10 p-1 text-gray-900 bg-white/40 rounded">{{ $totalTeachers }}</span>
                    </div>
                    <span class="text-sm text-blue-600 mt-2 font-medium">Teachers</span>
                </div>

                {{-- ROOMS --}}
                <div class="flex-1 flex flex-col items-center min-w-[80px] h-full">
                    <div class="w-full h-full bg-gray-200 rounded-lg relative overflow-hidden flex justify-center border-b-2 border-indigo-600">
                        <div class="absolute bottom-0 w-full bg-indigo-600/90 hover:bg-indigo-500 transition-all duration-700"
                             style="height: {{ $roomsBar }}%;">
                        </div>
                        <span class="absolute top-1/2 transform -translate-y-1/2 text-2xl font-bold drop-shadow-md z-10 p-1 text-gray-900 bg-white/40 rounded">{{ $activeRooms }}</span>
                    </div>
                    <span class="text-sm text-indigo-600 mt-2 font-medium">Rooms</span>
                </div>

                {{-- SUBJECTS --}}
                <div class="flex-1 flex flex-col items-center min-w-[80px] h-full">
                    <div class="w-full h-full bg-gray-200 rounded-lg relative overflow-hidden flex justify-center border-b-2 border-teal-600">
                        <div class="absolute bottom-0 w-full bg-teal-600/90 hover:bg-teal-500 transition-all duration-700"
                             style="height: {{ $subjectsBar }}%;">
                        </div>
                        <span class="absolute top-1/2 transform -translate-y-1/2 text-2xl font-bold drop-shadow-md z-10 p-1 text-gray-900 bg-white/40 rounded">{{ $activeSubjects }}</span>
                    </div>
                    <span class="text-sm text-teal-600 mt-2 font-medium">Subjects</span>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection