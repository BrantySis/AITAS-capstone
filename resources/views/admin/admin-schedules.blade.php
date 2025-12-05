@extends('layouts.mobile.mobile-app-admin')

@section('header_title', 'Schedule Management')

@section('content')
<style>
    .modal-closed { display: none !important; pointer-events: none !important; }
    .modal-open { display: flex !important; pointer-events: auto !important; }
    .modal-inner { transition: transform 280ms ease, opacity 280ms ease; transform: scale(0.96); opacity: 0; }
    .modal-inner.open { transform: scale(1); opacity: 1; }
</style>

<div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto relative z-10">

    {{-- ===================== HEADER + SEARCH + ACTIONS ===================== --}}
    <div class="mb-8 space-y-6">
        
        {{-- 1. TITLE SECTION --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mr-3 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h2 class="text-3xl font-extrabold text-gray-900">Class Schedules</h2>
            </div>
        </div>

        {{-- Insert this RIGHT AFTER the header title section and BEFORE the search & filters bar --}}

{{-- SCHEDULE COUNTS --}}
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            {{-- Total Schedules --}}
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div>
                    <p class="text-xs text-gray-600 font-medium">Total Schedules</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSchedulesCount) }}</p>
                </div>
            </div>

            {{-- Filtered Results (only show if filters are active) --}}
            @if(request('search') || request('department') || request('semester') || request('school_year') || request('filter'))
                <div class="flex items-center gap-2 pl-6 border-l-2 border-blue-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <div>
                        <p class="text-xs text-gray-600 font-medium">Filtered Results</p>
                        <p class="text-2xl font-bold text-indigo-700">{{ number_format($filteredSchedulesCount) }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Active Filters Badge --}}
        @if(request('search') || request('department') || request('semester') || request('school_year') || request('filter'))
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Filters Active
                </span>
            </div>
        @endif
    </div>

    {{-- Active Filter Tags --}}
    @if(request('search') || request('department') || request('semester') || request('school_year') || request('filter'))
        <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-blue-200">
            @if(request('search'))
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300">
                    Search: <strong class="ml-1">{{ request('search') }}</strong>
                </span>
            @endif
            @if(request('department'))
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300">
                    Department: <strong class="ml-1">{{ request('department') }}</strong>
                </span>
            @endif
            @if(request('semester'))
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300">
                    Semester: <strong class="ml-1">{{ request('semester') }}</strong>
                </span>
            @endif
            @if(request('school_year'))
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300">
                    School Year: <strong class="ml-1">{{ request('school_year') }}</strong>
                </span>
            @endif
            @if(request('filter'))
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white text-gray-700 border border-gray-300">
                    Status: <strong class="ml-1">{{ ucfirst(str_replace('-', ' ', request('filter'))) }}</strong>
                </span>
            @endif
        </div>
    @endif
</div>
{{-- 2. SEARCH & FILTERS BAR --}}
<div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-100">
    
    {{-- Search Input --}}
    <form action="{{ route('admin.schedules.index') }}" method="GET" class="relative flex w-full md:w-80 mb-2 lg:mb-0">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search teacher, subject, room, school year..."
            class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-l-xl px-4 py-2.5 pl-10 text-sm focus:outline-none transition shadow-sm" />
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-3 top-3 text-gray-400"
            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 3a7.5 7.5 0 006.15 13.65z" />
        </svg>
        <button type="submit"
            class="bg-blue-600 text-white px-4 rounded-r-xl hover:bg-blue-700 transition font-semibold text-sm">
            Search
        </button>
    </form>

    {{-- Filters --}}
    <form action="{{ route('admin.schedules.index') }}" method="GET" class="flex flex-wrap gap-2 items-center">
        {{-- Preserve search --}}
        <input type="hidden" name="search" value="{{ request('search') }}">

        {{-- Department Filter --}}
        <select name="department" class="border border-gray-300 rounded-lg px-2 py-2.5 text-sm focus:border-blue-500 focus:outline-none bg-gray-50">
            <option value="">All Depts</option>
            @foreach($schedules->pluck('subject.department')->unique() as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>

        {{-- Semester Filter --}}
        <select name="semester" class="border border-gray-300 rounded-lg px-2 py-2.5 text-sm focus:border-blue-500 focus:outline-none bg-gray-50">
            <option value="">All Sems</option>
            <option value="1st" {{ request('semester') == '1st' ? 'selected' : '' }}>1st Semester</option>
            <option value="2nd" {{ request('semester') == '2nd' ? 'selected' : '' }}>2nd Semester</option>
            <option value="Summer" {{ request('semester') == 'Summer' ? 'selected' : '' }}>Summer</option>
        </select>

        
{{-- School Year Filter --}}
<select name="school_year" class="border border-gray-300 rounded-lg px-2 py-2.5 text-sm focus:border-blue-500 focus:outline-none bg-gray-50">
    <option value="">All Years</option>
    @foreach($schoolYears as $sy)
        <option value="{{ $sy }}" {{ request('school_year') == $sy ? 'selected' : '' }}>
            {{ $sy }}
        </option>
    @endforeach
</select>


        {{-- Filter Button --}}
        <button type="submit"
            class="bg-gray-700 text-white px-3 py-2.5 rounded-lg hover:bg-gray-800 transition font-semibold text-sm">
            Filter
        </button>

        {{-- Reset Filter --}}
        @if(request('semester') || request('department') || request('school_year') || request('search'))
            <a href="{{ route('admin.schedules.index') }}" class="text-red-600 hover:text-red-800 text-sm font-semibold px-2">
                Reset
            </a>
        @endif
    </form>
</div>


            {{-- Action Buttons --}}
            <div class="flex gap-3 w-full lg:w-auto justify-start lg:justify-end border-t lg:border-t-0 pt-3 lg:pt-0 mt-2 lg:mt-0">
                <button id="openImportModal" class="flex-1 lg:flex-none bg-white border border-blue-500 text-blue-600 hover:bg-blue-50 font-semibold px-4 py-2.5 rounded-xl transition shadow-sm flex items-center justify-center whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import
                </button>

                <button type="button" data-modal-target="addScheduleModal" class="flex-1 lg:flex-none bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl transition shadow-md flex items-center justify-center open-modal-btn whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== IMPORT FEEDBACK ===================== --}}


    {{-- Success Messages --}}
    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg border border-green-300" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="font-semibold">{{ session('success') }}</h3>
                </div>
            </div>
        </div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg border border-red-300" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold mb-2">❌ Validation Error</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Duplicates from Import --}}
    @if(session('duplicates') && count(session('duplicates')) > 0)
        <div class="p-4 mb-4 text-sm text-yellow-800 bg-yellow-100 rounded-lg border border-yellow-300" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold mb-2">⚠️ Duplicate Schedules Detected</h3>
                    <p class="mb-2">{{ count(session('duplicates')) }} schedule(s) were skipped:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach(session('duplicates') as $duplicate)
                            <li><strong>Row {{ $duplicate['row'] }}:</strong> {{ $duplicate['reason'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Failed Imports --}}
    @if(session('failed') && count(session('failed')) > 0)
        <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg border border-red-300" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold mb-2">❌ Import Errors</h3>
                    <p class="mb-2">{{ count(session('failed')) }} row(s) failed to import:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach(session('failed') as $fail)
                            <li><strong>Row {{ $fail['row'] }}:</strong> {{ $fail['reason'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Page-specific alerts from @push --}}
    @stack('page-alerts')

    {{-- ===================== IMPORT MODAL ===================== --}}
    <div id="importModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden justify-center items-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative">
            <div class="flex items-center justify-between border-b pb-3 mb-4">
                <h2 class="text-lg font-bold text-gray-800">Import Schedules</h2>
                <button id="closeImportModal" class="text-gray-500 hover:text-gray-700 text-xl">&times;</button>
            </div>
            <form action="{{ route('admin.schedules.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <p class="text-sm text-gray-600 mb-3">
                    Upload a CSV/XLSX file with columns like:
                    <code class="bg-gray-100 text-gray-800 px-1 py-0.5 rounded text-xs">
                        teacher_name, room_code, subject_code, day_of_week, starts_at, ends_at, semester, school_year
                    </code>
                </p>
                <input type="file" name="file" required 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelImport" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition">
                        Upload & Import
                    </button>
                </div>
                @error('file')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                @if(session('success'))
                    <p class="text-green-600 text-sm mt-2">{{ session('success') }}</p>
                @endif
                @if($errors->has('error'))
                    <p class="text-red-600 text-sm mt-2">{{ $errors->first('error') }}</p>
                @endif
            </form>
        </div>
    </div>

    {{-- ===================== DESKTOP TABLE ===================== --}}
    <div class="hidden md:block overflow-x-auto bg-white rounded-xl shadow-2xl border border-gray-100">
        <table class="min-w-full table-auto text-sm text-left border-collapse">
            <thead class="bg-blue-200 text-gray-600 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Teacher</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">EDP Code</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Subject</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Units</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Course Year</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Semester</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">School Year</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Room</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 text-center">Day</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Start Date</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">End Date</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200">Time</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-800 divide-y divide-gray-100">
                @forelse ($schedules ?? [] as $schedule)
                    <tr class="hover:bg-blue-50/50 transition duration-150">
    <td class="px-6 py-4 font-bold">{{ $schedule->teacher->name ?? 'N/A' }}</td>
    <td class="px-6 py-4 font-semibold text-blue-700">{{ $schedule->edp_code ?? '—' }}</td>
    <td class="px-6 py-4 font-medium">{{ $schedule->subject->subject_name ?? 'N/A' }}</td>
    <td class="px-6 py-4">{{ $schedule->subject->units ?? '—' }}</td>
    <td class="px-6 py-4">{{ $schedule->subject->course_year ?? '—' }}</td>
    <td class="px-6 py-4 font-semibold text-blue-700">
        {{ $schedule->semester ?? $schedule->subject->semester ?? '—' }}
    </td>
    <td class="px-6 py-4">{{ $schedule->school_year ?? '—' }}</td> {{-- FIXED --}}
    <td class="px-6 py-4">{{ $schedule->room->room_code ?? 'N/A' }}</td>
    <td class="px-6 py-4 font-semibold text-blue-700">{{ $schedule->day_short ?: '—' }}</td>
    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($schedule->start_date)->format('M j, Y') ?? '—' }}</td>
    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($schedule->end_date)->format('M j, Y') ?? '—' }}</td>
    <td class="px-6 py-4 text-center">
        <span class="text-xs text-blue-600 font-semibold">
            {{ \Carbon\Carbon::parse($schedule->starts_at)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->ends_at)->format('h:i A') }}
        </span>
    </td>
    <td class="px-6 py-4 text-center whitespace-nowrap">
        <button type="button" class="edit-schedule-btn text-blue-600 hover:text-blue-800 font-semibold text-sm mr-4"
            data-id="{{ $schedule->id }}"
            data-user-id="{{ $schedule->user_id }}"
            data-subject-id="{{ $schedule->subject_id }}"
            data-room-id="{{ $schedule->room_id }}"
            data-day="{{ is_array($schedule->day_of_week) ? implode(',', $schedule->day_of_week) : $schedule->day_of_week }}"
            data-starts-at="{{ \Carbon\Carbon::parse($schedule->starts_at)->format('H:i') }}"
            data-ends-at="{{ \Carbon\Carbon::parse($schedule->ends_at)->format('H:i') }}"
            data-semester="{{ $schedule->semester }}">
            Edit
        </button>

        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm"
                onclick="return confirm('Delete schedule for {{ $schedule->subject->subject_name ?? 'this subject' }}?')">
                Delete
            </button>
        </form>
    </td>
</tr>

                @empty
                    <tr>
                        <td colspan="13" class="text-center text-gray-500 py-10">No schedules found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===================== PAGINATION ===================== --}}
    @if (method_exists($schedules ?? null, 'links'))
        <div class="mt-8">
            {{ $schedules->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>

    {{-- ===================== MOBILE CARDS (Updated for readability) ===================== --}}
    <div class="grid md:hidden gap-5 px-4">
        @forelse ($schedules ?? [] as $schedule)
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-600">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-extrabold text-gray-900 text-lg leading-tight">{{ $schedule->subject->subject_name ?? 'N/A' }}</h3>
                    <x-admin.schedule-status :schedule="$schedule" :attendanceMap="$attendanceMap ?? []" />
                </div>
                
                <div class="space-y-1 text-sm text-gray-700 mb-4 border-t pt-3">
                    <p class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Teacher: <span class="ml-1 font-semibold text-gray-900">{{ $schedule->teacher->name ?? 'N/A' }}</span>
                    </p>
                    <p class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18a2 2 0 002-2V7a2 2 0 00-2-2H3a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Room: <span class="ml-1 font-semibold text-gray-900">{{ $schedule->room->room_code ?? 'N/A' }}</span>
                    </p>
                    <p class="flex items-center">
                        <span class="w-4 h-4 mr-2 text-center text-gray-500 font-bold text-xs">S</span>
                        Semester: <span class="ml-1 font-semibold text-gray-900">{{ $schedule->semester ?? $schedule->subject->semester ?? 'N/A' }}</span>
                    </p>
                    <p class="flex items-center text-blue-600 font-semibold pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v4H3V4zm0 6h18v10a1 1 0 01-1 1H4a1 1 0 01-1-1V10z" />
                    </svg>
                    {{ $schedule->day_short ?: '—' }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t pt-4">
                    <button type="button" class="edit-schedule-btn text-blue-600 hover:text-blue-800 font-semibold text-sm"
                        data-id="{{ $schedule->id }}"
                        data-user-id="{{ $schedule->teacher_id ?? $schedule->user_id }}"
                        data-subject-id="{{ $schedule->subject_id }}"
                        data-room-id="{{ $schedule->room_id }}"
                        data-day="{{ is_array($schedule->day_of_week) ? implode(',', $schedule->day_of_week) : $schedule->day_of_week }}"
                        data-starts-at="{{ \Carbon\Carbon::parse($schedule->starts_at)->format('H:i') }}"
                        data-ends-at="{{ \Carbon\Carbon::parse($schedule->ends_at)->format('H:i') }}"
                        data-semester="{{ $schedule->semester }}">
                        Edit
                    </button>
                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm"
                                onclick="return confirm('WARNING: This will permanently delete the schedule. Are you sure?')">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-6">No schedules found matching your filters.</p>
        @endforelse
    </div>


{{-- ===================== ADD SCHEDULE MODAL (fixed) ===================== --}}
<div id="addScheduleModal" class="modal-wrapper modal-closed fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity duration-300" aria-hidden="true">
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl modal-inner modal-content transform transition-all max-w-lg w-full scale-95 opacity-0 duration-300">
            <div class="p-6 md:p-8">
                <div class="flex justify-between items-start">
                    <h3 class="text-2xl font-bold text-gray-900">Add New Schedule</h3>
                    <button type="button" data-modal-close="addScheduleModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <form action="{{ route('admin.schedules.store') }}" method="POST" class="mt-6 space-y-6">
                    @csrf
                    {{-- Use same component as edit (is-edit-mode false) --}}
                    <x-admin.schedule-form :users="$teachers" :rooms="$rooms" :subjects="$subjects" />

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" data-modal-close="addScheduleModal"
                                class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition font-semibold">
                            Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== EDIT SCHEDULE MODAL (fixed) ===================== --}}
<div id="editScheduleModal" class="modal-wrapper modal-closed fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity duration-300" aria-hidden="true">
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl modal-inner modal-content transform transition-all max-w-lg w-full scale-95 opacity-0 duration-300">
            <div class="p-6 md:p-8">
                <div class="flex justify-between items-start">
                    <h3 class="text-2xl font-bold text-gray-900">Edit Schedule</h3>
                    <button type="button" data-modal-close="editScheduleModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <form id="editScheduleForm" method="POST" class="mt-6 space-y-6">
                    @csrf @method('PUT')
                    {{-- Pass edit mode true so component will render inputs with edit_ prefix --}}
                    <x-admin.schedule-form :users="$teachers" :rooms="$rooms" :subjects="$subjects" is-edit-mode="true" />

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" data-modal-close="editScheduleModal"
                                class="px-5 py-2 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition font-semibold">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition font-semibold">
                            Update Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL SCRIPT (robust + safe DOM checks) ===================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ======================= IMPORT MODAL ======================= */
    const importModal = document.getElementById('importModal');
    const openImportModal = document.getElementById('openImportModal');
    const closeImportModal = document.getElementById('closeImportModal');
    const cancelImport = document.getElementById('cancelImport');
    const importInner = importModal.querySelector('.bg-white');

    const openImport = () => {
        importModal.classList.remove('hidden');
        importModal.classList.add('flex');
        requestAnimationFrame(() => importInner.classList.add('scale-100', 'opacity-100'));
    };

    const closeImport = () => {
        importInner.classList.remove('scale-100', 'opacity-100');
        setTimeout(() => {
            importModal.classList.add('hidden');
            importModal.classList.remove('flex');
            document.getElementById('importForm').reset();
        }, 200);
    };

    if (openImportModal) openImportModal.addEventListener('click', openImport);
    if (closeImportModal) closeImportModal.addEventListener('click', closeImport);
    if (cancelImport) cancelImport.addEventListener('click', closeImport);
    importModal.addEventListener('click', e => { if (e.target === importModal) closeImport(); });


    /* ======================= GENERIC MODALS ======================= */

    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-closed');
        modal.classList.add('modal-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const inner = modal.querySelector('.modal-inner');
        if (inner) requestAnimationFrame(() => inner.classList.add('open'));
    };

    const closeModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;

        const inner = modal.querySelector('.modal-inner');
        if (inner) inner.classList.remove('open');

        setTimeout(() => {
            modal.classList.remove('modal-open');
            modal.classList.add('modal-closed');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }, 260);
    };

    document.querySelectorAll('.open-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-modal-target');
            if (target) openModal(target);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-modal-close');
            if (target) closeModal(target);
        });
    });

    document.querySelectorAll('.modal-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', e => {
            if (e.target === wrapper && wrapper.classList.contains('modal-open')) {
                closeModal(wrapper.id);
            }
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-wrapper.modal-open').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });


    /* ======================= EDIT BUTTON POPULATE ======================= */

    document.querySelectorAll('.edit-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Check if semester is on the button data, if not fallback
            const semester = btn.dataset.semester;

            const schedule = {
                id: btn.dataset.id,
                user_id: btn.dataset.userId ?? btn.dataset.teacherId ?? btn.dataset.user_id ?? btn.dataset.teacher_id,
                subject_id: btn.dataset.subjectId ?? btn.dataset.subject_id,
                room_id: btn.dataset.roomId ?? btn.dataset.room_id,
                day_of_week: btn.dataset.day ?? btn.dataset.day_of_week,
                starts_at: btn.dataset.startsAt ?? btn.dataset.starts_at,
                ends_at: btn.dataset.endsAt ?? btn.dataset.ends_at,
                semester: semester // Pass this to the form
            };

            const form = document.getElementById('editScheduleForm');
            if (form && schedule.id) form.action = `{{ url('admin/schedules') }}/${schedule.id}`;

            const mappings = {
                'edit_user_id': schedule.user_id,
                'edit_subject_id': schedule.subject_id,
                'edit_room_id': schedule.room_id,
                'edit_day_of_week': schedule.day_of_week,
                'edit_starts_at': schedule.starts_at,
                'edit_ends_at': schedule.ends_at,
                'edit_semester': schedule.semester // Make sure your component has this input
            };

            Object.entries(mappings).forEach(([id, val]) => {
                const el = document.getElementById(id);
                if (!el) return;
                try {
                    if (el.tagName.toLowerCase() === 'select') {
                        el.value = val ?? '';
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    } else el.value = val ?? '';
                } catch (err) {
                    console.warn('Could not set value for', id, err);
                }
            });

            openModal('editScheduleModal');
        });
    });

    // Ensure modals start closed
    document.querySelectorAll('.modal-wrapper').forEach(modal => modal.classList.add('modal-closed'));
});
</script>
@endsection