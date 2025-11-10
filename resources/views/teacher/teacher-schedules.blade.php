@extends('layouts.mobile.mobile-app')

@section('header_title', 'Schedules')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<style>
:root {
    --primary-blue: #1E3A8A;
    --light-bg: #F3F4F6;
    --search-blue: #004A8F;
    --accent-blue: #3B82F6;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--light-bg);
}

.calendar-day {
    min-height: 36px;
    min-width: 36px;
    line-height: 1.2;
    padding: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    position: relative;
}

.calendar-day.current-day {
    color: white;
    font-weight: 700;
    background-color: var(--accent-blue);
    border-radius: 9999px;
}

.calendar-day:hover:not(.current-day) {
    background-color: #E5E7EB;
    border-radius: 9999px;
}

.calendar-day.has-schedule::after {
    content: '';
    position: absolute;
    bottom: 4px;
    width: 5px;
    height: 5px;
    background-color: var(--search-blue);
    border-radius: 9999px;
}

.calendar-day.current-day.has-schedule::after {
    background-color: white; 
}

.schedule-item-list {
    display: flex;
    gap: 1rem;
}

.time-column {
    width: 4rem;
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding-top: 4px;
    padding-bottom: 4px;
}

.timeline-container {
    position: relative;
    padding-left: 0.5rem;
    border-left: 2px solid var(--accent-blue);
}

.schedule-item-list:last-child .timeline-container {
    border-left: none; 
}
</style>

<!-- Calendar Card -->
<div class="bg-white p-4 pt-6 rounded-xl shadow-lg border border-gray-100 mb-6">

    <!-- Month Navigation -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-extrabold text-gray-800 flex items-center">
            {{ \Carbon\Carbon::now()->format('F Y') }}
            <i data-lucide="chevron-right" class="h-4 w-4 ml-1 text-gray-500"></i>
        </h2>
        <div class="flex space-x-0 text-gray-600">
            <button id="prevMonth" aria-label="Previous Month" class="p-2 rounded-full hover:bg-gray-100 transition focus:outline-none">
                <i data-lucide="chevron-left" class="h-6 w-6"></i>
            </button>
            <button id="nextMonth" aria-label="Next Month" class="p-2 rounded-full hover:bg-gray-100 transition focus:outline-none">
                <i data-lucide="chevron-right" class="h-6 w-6"></i>
            </button>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-1 text-center font-medium mb-4">
        @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
            <div class="{{ $day=='SUN' ? 'text-red-500' : 'text-gray-500' }} text-xs sm:text-sm pt-1 font-bold">{{ $day }}</div>
        @endforeach

       @php
        use Carbon\Carbon;
        $now = Carbon::now();
        $daysInMonth = $now->daysInMonth;
        $firstDayOfMonth = Carbon::create($now->year, $now->month, 1)->dayOfWeek;
        $dayNames = ['SUN','MON','TUE','WED','THU','FRI','SAT'];

        // Prepare array mapping each day in month to schedules
        $schedulesByDayOfMonth = [];
        foreach(range(1, $daysInMonth) as $day) {
            $date = Carbon::create($now->year, $now->month, $day);
            $dow = $dayNames[$date->dayOfWeek];

            foreach($schedules as $s) {
                $days = json_decode($s->day_of_week ?? '[]', true);
                if(is_array($days) && in_array($dow, $days)) {
                    $schedulesByDayOfMonth[$day][] = $s;
                }
            }
        }
        @endphp

       <!-- Empty leading cells -->
            @for($i=0; $i < $firstDayOfMonth; $i++)
                <div class="calendar-day"></div>
            @endfor

            <!-- Calendar Days -->
            @for($day=1; $day <= $daysInMonth; $day++)
                @php
                    $hasSchedule = isset($schedulesByDayOfMonth[$day]);
                    $isToday = $day == $now->day;
                @endphp
                <div class="calendar-day text-gray-800 {{ $isToday ? 'current-day' : 'font-semibold' }} {{ $hasSchedule ? 'has-schedule' : '' }}" 
                    data-day="{{ $day }}">
                    {{ $day }}
                </div>
            @endfor
    </div>
</div>

<!-- Current Classes Button -->
<div class="flex justify-center mb-6">
    <button id="currentClassesBtn" class="bg-blue-600 text-white font-bold py-3 px-6 rounded-full shadow-lg hover:bg-blue-700 transition focus:outline-none transform hover:scale-105" style="background-color: var(--accent-blue);">
        View Today's Schedules
    </button>
</div>

<!-- Schedule List Card -->
<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">

    <!-- Schedule List Header -->
    <div class="text-white p-3 font-extrabold text-lg text-center" style="background-color: var(--search-blue);">
        <span id="scheduleHeaderTitle">Select a day to view schedules</span>
    </div>

    <!-- Schedule Items Container -->
    <div id="scheduleList" class="space-y-4 p-4 min-h-[100px]">
        <p class="text-gray-500 text-center py-4">Click a date or the button above.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();

    const schedules = @json($schedules ?? []);
    const currentClasses = @json($currentClasses ?? []);
    const scheduleListEl = document.getElementById('scheduleList');
    const scheduleHeaderTitleEl = document.getElementById('scheduleHeaderTitle');

    const dayNames = ['SUN','MON','TUE','WED','THU','FRI','SAT'];
    const today = new Date();

    const allSchedules = schedules.map(s => ({
        ...s,
        day_of_week: s.day_of_week || [],
        startsAt: new Date(s.starts_at),
        endsAt: new Date(s.ends_at)
    }));

    function formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function displaySchedules(list, title = '') {
        scheduleHeaderTitleEl.textContent = title || 'Select a day to view schedules';

        if(list.length === 0) {
            scheduleListEl.innerHTML = '<p class="text-gray-500 text-center py-4">No schedules to display.</p>';
            return;
        }

        scheduleListEl.innerHTML = list.map(s => `
            <div class="schedule-item-list">
                <div class="time-column text-xs text-gray-600 font-semibold">
                    <p class="leading-tight">${formatTime(s.startsAt)}</p>
                    <p class="leading-tight">${formatTime(s.endsAt)}</p>
                </div>
                <div class="flex-1 timeline-container">
                    <p class="text-base font-bold text-gray-900 leading-snug">${s.edp_code} - ${s.subject.subject_name}</p>
                    <p class="text-sm text-gray-500 leading-snug">${s.room.room_code}</p>
                </div>
            </div>
        `).join('');
    }

    function getSchedulesForDay(day) {
        const date = new Date(today.getFullYear(), today.getMonth(), day);
        const dow = dayNames[date.getDay()];
        return allSchedules.filter(s => s.day_of_week.includes(dow));
    }

    document.querySelectorAll('.calendar-day').forEach(dayEl => {
        const day = parseInt(dayEl.getAttribute('data-day'));
        if(!day) return;

        dayEl.addEventListener('click', () => {
            document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('current-day'));
            dayEl.classList.add('current-day');

            const daySchedules = getSchedulesForDay(day);
            const monthName = today.toLocaleDateString('en-US', { month: 'long' });
            const dateObj = new Date(today.getFullYear(), today.getMonth(), day);
            const weekday = dateObj.toLocaleDateString('en-US', { weekday: 'long' });

            displaySchedules(daySchedules, `${weekday}, ${monthName} ${day}`);
        });
    });

    document.getElementById('currentClassesBtn').addEventListener('click', () => {
        const todayDay = today.getDate();
        const todayEl = document.querySelector(`.calendar-day[data-day="${todayDay}"]`);
        if(todayEl) {
            todayEl.click();
        } else {
            const currentMapped = currentClasses.map(s => ({
                ...s,
                startsAt: new Date(s.starts_at),
                endsAt: new Date(s.ends_at)
            }));
            displaySchedules(currentMapped, `Today, ${today.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })}`);
        }
    });

    // Auto-click current day on page load
    document.getElementById('currentClassesBtn').click();
});
</script>

@endsection
