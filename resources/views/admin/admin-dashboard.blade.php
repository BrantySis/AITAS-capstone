@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Admin Dashboard')

@section('content')

<div class="bg-[#EBF8FF] -m-6 p-4 min-h-screen">

    <div class="mb-4">
        
        <div class="relative mt-4">
            <input 
                type="text" 
                placeholder="Search" 
                class="w-full pl-10 pr-4 py-3 rounded-lg shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
    </div>

    <div class="bg-[#142D54] text-white rounded-2xl shadow-lg p-6">
        <h3 class="text-2xl font-bold mb-6">System Overview</h3>

        <div class="flex items-end h-48 space-x-4">
            
            {{-- Y-Axis Labels --}}
            <div class="flex flex-col justify-between h-full text-xs text-gray-400">
                <span>100%</span>
                <span>50%</span>
                <span>0%</span>
            </div>

            {{-- Bar 1: Teachers (Blue) --}}
            <div class="flex-1 h-full flex flex-col justify-end relative rounded-t-md overflow-hidden bg-[#1D4A80]">
                <div class="bg-[#008EFF] rounded-t-md" style="height: {{ $totalTeachers > 0 ? ($totalTeachers / ($totalTeachers + $totalRooms + $activeSubjects)) * 100 : 0 }}%;">
                    {{-- Using a static height for visual match to the previous image --}}
                    {{-- For the image's look, let's use a static 80% --}}
                    {{-- <div class="bg-[#00A9FF] rounded-t-md" style="height: 80%;"></div> --}}
                </div>
            </div>

            {{-- Bar 2: Rooms (Darker Blue with a colored bottom part) --}}
            <div class="flex-1 h-full flex flex-col justify-end relative rounded-t-md overflow-hidden bg-[#1D4A80]">
                {{-- This div acts as the main background of the column --}}
                {{-- The inner div is the colored segment at the bottom --}}
                <div class="absolute bottom-0 left-0 right-0 bg-[#2563EB] rounded-b-md" style="height: 15%;"></div>
                {{-- The height here is a static example to match your image --}}
            </div>

            {{-- Bar 3: Subjects (White/Light) --}}
            <div class="flex-1 h-full flex flex-col justify-end relative rounded-t-md overflow-hidden bg-[#1D4A80]">
                {{-- For the image's look, let's use a static 30% --}}
                <div class="bg-white rounded-t-md" style="height: 30%;"></div>
            </div>
        </div>

        {{-- Dashed Lines for Y-Axis --}}
        <div class="relative -mt-48 h-48 ml-[10%]">
            <div class="absolute w-full border-t border-dashed border-gray-600 top-0"></div>
            <div class="absolute w-full border-t border-dashed border-gray-600 top-1/2"></div>
            <div class="absolute w-full border-t border-gray-600 bottom-0"></div>
        </div>

        {{-- Legend --}}
        <div class="mt-6 space-y-3">
            {{-- 1. Total Teachers --}}
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-[#00A9FF] rounded-full mr-3"></span>
                    <span class="text-gray-300">Total Teachers</span>
                </div>
                <span class="font-semibold">{{ $totalTeachers ?? '...' }}</span>
            </div>
            
            {{-- 2. Total Active Rooms --}}
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-[#00477F] rounded-full mr-3"></span>
                    <span class="text-gray-300">Total Active Rooms</span>
                </div>
                <span class="font-semibold">{{ $totalRooms ?? '...' }}</span>
            </div>

            {{-- 3. Total Subjects --}}
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-white rounded-full mr-3"></span>
                    <span class="text-gray-300">Total Subjects</span>
                </div>
                <span class="font-semibold">{{ $activeSubjects ?? '...' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-[#142D54] text-white rounded-2xl shadow-lg p-6 mt-6">
        <h3 class="text-2xl font-bold">Suggest Drive</h3>
        <p class="text-gray-300 mt-2">This is a placeholder for future content.</p>
    </div>

</div>
@endsection