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
body { font-family: 'Inter', sans-serif; background-color: var(--light-bg); }

.calendar-day {
    min-height: 36px; min-width: 36px; line-height: 1.2; padding: 4px;
    cursor: pointer; transition: all 0.2s; font-size: 0.875rem;
    display: flex; align-items: center; justify-content: center;
    font-weight: 500; position: relative;
}
.calendar-day.current-day { color: white; font-weight: 700; background-color: var(--accent-blue); border-radius: 9999px; }
.calendar-day:hover:not(.current-day) { background-color: #E5E7EB; border-radius: 9999px; }
.calendar-day.has-schedule::after {
    content: ''; position: absolute; bottom: 4px; width: 5px; height: 5px;
    background-color: var(--search-blue); border-radius: 9999px;
}
.calendar-day.current-day.has-schedule::after { background-color: white; }

.schedule-item-list { display: flex; gap: 1rem; }
.time-column { width: 4rem; text-align: right; display: flex; flex-direction: column; justify-content: space-between; padding: 4px 0; }
.timeline-container { position: relative; padding-left: 0.5rem; border-left: 2px solid var(--accent-blue); }
.schedule-item-list:last-child .timeline-container { border-left: none; }
</style>

<div class="bg-white p-4 pt-6 rounded-xl shadow-lg border border-gray-100 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 id="currentMonthYear" class="text-lg font-extrabold text-gray-800 flex items-center">
            {{ \Carbon\Carbon::now()->format('F Y') }}
        </h2>
        <div class="flex space-x-0 text-gray-600">
            <button id="prevMonth" class="p-2 rounded-full hover:bg-gray-100 transition"><i data-lucide="chevron-left" class="h-6 w-6"></i></button>
            <button id="nextMonth" class="p-2 rounded-full hover:bg-gray-100 transition"><i data-lucide="chevron-right" class="h-6 w-6"></i></button>
        </div>
    </div>
    <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-center font-medium mb-4">
        @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
            <div class="{{ $day=='SUN' ? 'text-red-500' : 'text-gray-500' }} text-xs sm:text-sm pt-1 font-bold">{{ $day }}</div>
        @endforeach
    </div>
</div>

<div class="flex justify-center mb-6">
    <button id="currentClassesBtn" class="bg-blue-600 text-white font-bold py-3 px-6 rounded-full shadow-lg hover:bg-blue-700 transition focus:outline-none transform hover:scale-105">
        View Today's Schedules
    </button>
</div>

<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="text-white p-3 font-extrabold text-lg text-center" style="background-color: var(--search-blue);">
        <span id="scheduleHeaderTitle">Select a day to view schedules</span>
    </div>
    <div id="scheduleList" class="space-y-4 p-4 min-h-[100px]">
        <p class="text-gray-500 text-center py-4">Click a date or the button above.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if(typeof lucide!=='undefined') lucide.createIcons();

    const schedules = @json($schedules ?? []);
    const scheduleListEl = document.getElementById('scheduleList');
    const scheduleHeaderTitleEl = document.getElementById('scheduleHeaderTitle');

    const dayNames = ['SUN','MON','TUE','WED','THU','FRI','SAT'];
    let currentDate = new Date();

    function parseDays(s) {
        if(!s) return [];
        try { 
            let d = JSON.parse(s.day_of_week);
            if(Array.isArray(d)) return d;
        } catch(e) {
            // fallback: comma separated string
            return s.day_of_week.split(',').map(x=>x.trim());
        }
        return [];
    }

    function getSchedulesForDate(dateObj) {
        const dow = dayNames[dateObj.getDay()];
        return schedules.filter(s => parseDays(s).includes(dow));
    }

  function parseScheduleDays(s) {
    if (!s || !s.day_of_week) return [];

    const raw = s.day_of_week.toUpperCase().replace(/\s+/g, '');
    let val = raw;

    const days = [];

    // Detect Thursday first & remove it
    if (val.includes('TH')) {
        days.push('THU');
        val = val.replace(/TH/g, ''); // remove TH so T only means Tuesday
    }

    // Now remaining T = Tuesday
    if (val.includes('T')) days.push('TUE');

    if (val.includes('M'))   days.push('MON');
    if (val.includes('W'))   days.push('WED');
    if (val.includes('F'))   days.push('FRI');

    // Sunday vs Saturday logic
    if (val.includes('SU'))  days.push('SUN');
    else if (val.includes('S')) days.push('SAT');

    return days;
}

function getSchedulesForDate(dateObj) {
    const dow = dayNames[dateObj.getDay()];

    return schedules.filter(s => {

        const scheduleDays = parseScheduleDays(s);
        if (!scheduleDays.includes(dow)) return false;

        // Validate active date range
        const start = new Date(s.start_date);
        const end = new Date(s.end_date);

        return dateObj >= start && dateObj <= end;
    });
}

    function formatTime(date) { return new Date(date).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' }); }

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month+1, 0).getDate();

        document.getElementById('currentMonthYear').textContent = date.toLocaleDateString('en-US',{ month:'long', year:'numeric' });

        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';
        ['SUN','MON','TUE','WED','THU','FRI','SAT'].forEach(d => grid.innerHTML += `<div class="${d=='SUN'?'text-red-500':'text-gray-500'} text-xs sm:text-sm pt-1 font-bold">${d}</div>`);

        for(let i=0;i<firstDay;i++) grid.innerHTML += `<div class="calendar-day"></div>`;

        for(let d=1; d<=daysInMonth; d++){
            const dayDate = new Date(year, month, d);
            const daySchedules = getSchedulesForDate(dayDate);
            const isToday = d === new Date().getDate() && month===new Date().getMonth() && year===new Date().getFullYear();
            const hasSchedule = daySchedules.length>0;
            grid.innerHTML += `<div class="calendar-day ${isToday?'current-day':'font-semibold'} ${hasSchedule?'has-schedule':''}" data-day="${d}">${d}</div>`;
        }

        document.querySelectorAll('.calendar-day[data-day]').forEach(el=>{
            el.addEventListener('click', ()=>{
                document.querySelectorAll('.calendar-day').forEach(d=>d.classList.remove('current-day'));
                el.classList.add('current-day');
                const day = parseInt(el.getAttribute('data-day'));
                const selectedDate = new Date(year, month, day);
                const daySchedules = getSchedulesForDate(selectedDate);
                const weekday = selectedDate.toLocaleDateString('en-US',{ weekday:'long' });
                const monthName = selectedDate.toLocaleDateString('en-US',{ month:'long' });
                scheduleHeaderTitleEl.textContent = `${weekday}, ${monthName} ${day}`;
                displaySchedules(daySchedules);
            });
        });
    }

    function displaySchedules(list){
        if(list.length===0){ scheduleListEl.innerHTML='<p class="text-gray-500 text-center py-4">No schedules to display.</p>'; return; }
        scheduleListEl.innerHTML = list.map(s=>`
            <div class="schedule-item-list">
                <div class="time-column text-xs text-gray-600 font-semibold">
                    <p class="leading-tight">${formatTime(s.starts_at)}</p>
                    <p class="leading-tight">${formatTime(s.ends_at)}</p>
                </div>
                <div class="flex-1 timeline-container">
                    <p class="text-base font-bold text-gray-900 leading-snug">${s.edp_code} - ${s.subject.subject_name}</p>
                    <p class="text-sm text-gray-500 leading-snug">${s.room.room_code}</p>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('prevMonth').addEventListener('click',()=>{ currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(currentDate); });
    document.getElementById('nextMonth').addEventListener('click',()=>{ currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(currentDate); });
    document.getElementById('currentClassesBtn').addEventListener('click',()=>{
        const today = new Date();
        renderCalendar(today);
        document.querySelector(`.calendar-day[data-day="${today.getDate()}"]`)?.click();
    });

    renderCalendar(currentDate);
    document.getElementById('currentClassesBtn').click();
});
</script>

@endsection
