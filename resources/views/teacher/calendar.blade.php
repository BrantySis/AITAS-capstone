<x-app-layout>
    <div class="max-w-6xl mx-auto py-6">
        <h2 class="text-2xl font-bold text-blue-700 mb-4">My Teaching Schedule (Calendar View)</h2>

        <div class="bg-white p-6 rounded shadow">
             <div id="calendar" class="bg-white border rounded-lg p-4 shadow-md h-[400px] w-full"></div>
        </div>
    </div>

    {{-- FullCalendar Scripts --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 600,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: @json($events),
            });

            calendar.render();
        });
    </script>
</x-app-layout>
