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
    --red-weekend: #EF4444; /* Tailwind red-500 */
}
body { font-family: 'Inter', sans-serif; background-color: var(--light-bg); }

.calendar-day {
    min-height: 40px; min-width: 40px; line-height: 1.2; padding: 4px;
    cursor: pointer; transition: all 0.2s; font-size: 0.875rem;
    display: flex; align-items: center; justify-content: center;
    font-weight: 500; position: relative;
}
/* Style for the current system day */
.calendar-day.is-today { font-weight: 700; color: var(--accent-blue); border: 2px solid var(--accent-blue); border-radius: 9999px; }

/* Style for the *selected* day */
.calendar-day.selected-day { 
    color: white; 
    font-weight: 700; 
    background-color: var(--accent-blue); 
    border-radius: 9999px; 
    border: none !important; /* Override is-today border if it's the selected day */
}

/* Hover effect */
.calendar-day:hover:not(.selected-day) { background-color: #E5E7EB; border-radius: 9999px; }

/* Schedule dot indicator */
.calendar-day.has-schedule::after {
    content: ''; position: absolute; bottom: 4px; width: 5px; height: 5px;
    background-color: var(--search-blue); border-radius: 9999px;
}
.calendar-day.selected-day.has-schedule::after { background-color: white; }

.schedule-item-list { 
    display: flex; 
    gap: 1rem; 
    padding: 10px 0; 
    border-bottom: 1px dashed #E5E7EB; /* Subtle divider for list items */
}
.schedule-item-list:last-child {
    border-bottom: none;
}
.time-column { 
    width: 4rem; 
    text-align: right; 
    display: flex; 
    flex-direction: column; 
    justify-content: space-between; 
    padding: 4px 0; 
    flex-shrink: 0;
}
.timeline-container { 
    position: relative; 
    padding-left: 0.5rem; 
    border-left: 2px solid var(--accent-blue); 
}
</style>

<div class="bg-white p-4 pt-6 rounded-xl shadow-lg border border-gray-100 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 id="currentMonthYear" class="text-xl font-extrabold text-gray-800 flex items-center">
            {{ \Carbon\Carbon::now()->format('F Y') }}
        </h2>
        <div class="flex space-x-0 text-gray-600">
            <button id="prevMonth" class="p-2 rounded-full hover:bg-gray-100 transition"><i data-lucide="chevron-left" class="h-6 w-6"></i></button>
            <button id="nextMonth" class="p-2 rounded-full hover:bg-gray-100 transition"><i data-lucide="chevron-right" class="h-6 w-6"></i></button>
        </div>
    </div>
    <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-center font-medium mb-4">
        @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
            <div class="{{ $day=='SUN' || $day=='SAT' ? 'text-red-500' : 'text-gray-500' }} text-xs sm:text-sm pt-1 font-bold">{{ $day }}</div>
        @endforeach
    </div>
</div>

<div class="flex justify-center mb-6">
    <button id="currentClassesBtn" class="bg-blue-600 text-white font-bold py-3 px-6 rounded-full shadow-lg hover:bg-blue-700 transition focus:outline-none transform hover:scale-105">
        <i data-lucide="calendar-check" class="w-5 h-5 inline-block mr-2"></i> View Today's Schedules
    </button>
</div>

<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="text-white p-3 font-extrabold text-lg text-center" style="background-color: var(--search-blue);">
        <span id="scheduleHeaderTitle">Select a day to view schedules</span>
    </div>
    <div id="scheduleList" class="space-y-0 p-4 min-h-[100px]">
        <p class="text-gray-500 text-center py-4">Click a date or the button above.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if(typeof lucide!=='undefined') lucide.createIcons();

    // Ensure $schedules is a JSON string/array.
    const schedules = @json($schedules ?? []);
    const scheduleListEl = document.getElementById('scheduleList');
    const scheduleHeaderTitleEl = document.getElementById('scheduleHeaderTitle');

    // Full day names (used for comparing with the database data)
    const fullDayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    let currentDate = new Date();

    /**
     * Parses the schedule's day_of_week property.
     * Expects a JSON array of full day names (e.g., ["Monday", "Friday"]).
     * @param {object} s - The schedule object.
     * @returns {string[]} An array of full day names.
     */
    function parseDays(s) {
        if(!s || !s.day_of_week) return [];
        let d = s.day_of_week;

        if(Array.isArray(d)) return d.map(day => day.trim());

        try { 
            d = JSON.parse(d);
            if(Array.isArray(d)) return d.map(day => day.trim());
        } catch(e) {
            return String(s.day_of_week).split(',').map(x=>x.trim());
        }
        return [];
    }

    /**
     * Filters schedules for a specific date, checking day of week and date range.
     * @param {Date} dateObj - The date to check.
     * @returns {object[]} Filtered array of schedules.
     */
    function getSchedulesForDate(dateObj) {
        const fullDow = fullDayNames[dateObj.getDay()];
        const dateOnly = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());

        return schedules.filter(s => {
            const scheduleDays = parseDays(s);
            if (!scheduleDays.includes(fullDow)) return false;

            const start = new Date(s.start_date);
            const end = new Date(s.end_date);
            const startDateOnly = new Date(start.getFullYear(), start.getMonth(), start.getDate());
            const endDateOnly = new Date(end.getFullYear(), end.getMonth(), end.getDate());

            return dateOnly >= startDateOnly && dateOnly <= endDateOnly;
        });
    }

    /**
     * Fixes "Invalid Date" issue by ensuring a date component is present 
     * before formatting the time string.
     * @param {string} dateTimeStr - The time string (e.g., "10:00:00") or full datetime string.
     * @returns {string} The formatted time (e.g., "10:00 AM") or 'Invalid Date'.
     */
    function formatTime(dateTimeStr) { 
        let dateObj;
        
        if (dateTimeStr && (dateTimeStr.includes('T') || dateTimeStr.includes('-'))) {
             dateObj = new Date(dateTimeStr);
        } else if (dateTimeStr) {
            // Prepend a date component to ensure a valid Date object is created if only time is provided.
            // Current year/month is used for correct locale/timezone handling, but 1970/01/01 is safer 
            // if the system date is used in the background for date comparison elsewhere.
            // Using a static date (1970/01/01) ensures the time is the only changing variable.
            dateObj = new Date(`1970/01/01 ${dateTimeStr}`);
        } else {
            return 'N/A'; // Handle null or empty input
        }

        // Check if parsing failed
        if (isNaN(dateObj.getTime())) {
            return 'Invalid Date';
        }

        // Use 'h:mm a' format for aesthetic time display
        return dateObj.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit', hour12: true }); 
    }

    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month+1, 0).getDate();
        const today = new Date();
        const isCurrentMonth = month === today.getMonth() && year === today.getFullYear();
        let selectedDayElement = document.querySelector('.calendar-day.selected-day');
        let selectedDay = selectedDayElement ? parseInt(selectedDayElement.getAttribute('data-day')) : null;

        document.getElementById('currentMonthYear').textContent = date.toLocaleDateString('en-US',{ month:'long', year:'numeric' });

        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';
        ['SUN','MON','TUE','WED','THU','FRI','SAT'].forEach(d => grid.innerHTML += `<div class="${d=='SUN' || d=='SAT' ? 'text-red-500' : 'text-gray-500'} text-xs sm:text-sm pt-1 font-bold">${d}</div>`);

        for(let i=0;i<firstDay;i++) grid.innerHTML += `<div class="calendar-day"></div>`;

        for(let d=1; d<=daysInMonth; d++){
            const dayDate = new Date(year, month, d);
            const daySchedules = getSchedulesForDate(dayDate);
            const isToday = isCurrentMonth && d === today.getDate();
            const hasSchedule = daySchedules.length>0;
            
            // Determine initial classes
            let classes = 'font-semibold';
            if(isToday) classes += ' is-today';
            // If a day was previously selected and is in the current month, retain selection visual
            if(selectedDay !== null && d === selectedDay && !isToday) classes += ' selected-day';
            
            if(hasSchedule) classes += ' has-schedule';
            
            grid.innerHTML += `<div class="calendar-day ${classes}" data-day="${d}">${d}</div>`;
        }
        
        // Re-select today if we rendered the current month and it was the selected day
        if (isCurrentMonth) {
            const todayEl = document.querySelector(`.calendar-day[data-day="${today.getDate()}"]`);
            if (todayEl) todayEl.classList.add('selected-day');
        }


        document.querySelectorAll('.calendar-day[data-day]').forEach(el=>{
            el.addEventListener('click', ()=>{
                // 1. Remove selection from all days
                document.querySelectorAll('.calendar-day.selected-day').forEach(d => d.classList.remove('selected-day'));

                // 2. Add selection to the clicked element
                el.classList.add('selected-day'); 

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
        // Sort by starts_at time before display
        list.sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at));

        if(list.length===0){ scheduleListEl.innerHTML='<p class="text-gray-500 text-center py-4">No schedules to display.</p>'; return; }
        scheduleListEl.innerHTML = list.map(s=>`
            <div class="schedule-item-list">
                <div class="time-column text-xs text-gray-600 font-semibold">
                    <p class="leading-tight">${formatTime(s.starts_at)}</p>
                    <p class="leading-tight text-gray-400">${formatTime(s.ends_at)}</p>
                </div>
                <div class="flex-1 timeline-container">
                    <p class="text-base font-bold text-gray-900 leading-snug">${s.edp_code} - ${s.subject.subject_name}</p>
                    <p class="text-sm text-gray-500 leading-snug">${s.room.room_code}</p>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('prevMonth').addEventListener('click',()=>{ 
        currentDate.setMonth(currentDate.getMonth()-1); 
        renderCalendar(currentDate); 
    });
    document.getElementById('nextMonth').addEventListener('click',()=>{ 
        currentDate.setMonth(currentDate.getMonth()+1); 
        renderCalendar(currentDate); 
    });
    document.getElementById('currentClassesBtn').addEventListener('click',()=>{
        const today = new Date();
        // Reset currentDate to today's month/year before rendering
        currentDate = new Date(today.getFullYear(), today.getMonth(), 1); 
        renderCalendar(currentDate);
        
        // Use setTimeout to ensure the DOM is updated before clicking
        setTimeout(() => {
            // Find the day element and click it to select it and load schedules
            const todayEl = document.querySelector(`.calendar-day[data-day="${today.getDate()}"]`);
            if (todayEl) {
                todayEl.click();
            } else {
                // Fallback to initial message if for some reason today's element is missing
                scheduleHeaderTitleEl.textContent = `Select a day to view schedules`;
                displaySchedules([]);
            }
        }, 0);
    });

    renderCalendar(currentDate);
    // Automatically select today's schedule on load
    document.getElementById('currentClassesBtn').click();
});
</script>

@endsection