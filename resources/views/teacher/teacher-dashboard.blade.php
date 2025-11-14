@extends('layouts.mobile.mobile-app')

@section('header_title', 'Dashboard')

@section('content')

{{-- Assume Tailwind CSS is loaded in layouts.mobile.mobile-app --}}

<div class="p-4 space-y-6">

{{-- Top Row: Stats - Optimized for Mobile Impact --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    
    {{-- Completed Attendance Card (Primary Action/Stat) --}}
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-xl rounded-xl p-4 text-white hover:shadow-2xl transition duration-300">
        <h3 class="text-sm font-medium opacity-80">Completed Attendance</h3>
        <p class="text-3xl font-bold mt-2">{{ $completedAttendance }}/{{ $classesToday }}</p>
        <p class="text-xs mt-1 opacity-70">Classes marked today</p>
    </div>
    
    {{-- Late Today Card --}}
    <div class="bg-white shadow-lg rounded-xl p-4 text-center hover:shadow-xl transition duration-300">
        <h3 class="text-sm font-medium text-gray-500">Late Today</h3>
        <p class="text-3xl font-bold mt-2 text-yellow-600">{{ $lateToday }}</p>
        <p class="text-xs mt-1 text-gray-400">Total reported late</p>
    </div>
    
    {{-- Missed Classes Card --}}
    <div class="bg-white shadow-lg rounded-xl p-4 text-center hover:shadow-xl transition duration-300">
        <h3 class="text-sm font-medium text-gray-500">Missed Classes</h3>
        <p class="text-3xl font-bold mt-2 text-red-600">{{ $missedClasses }}</p>
        <p class="text-xs mt-1 text-gray-400">Unmarked or pending</p>
    </div>
</div>

{{-- Donut Chart: Present / Late / Absent --}}
<div class="bg-white shadow-lg rounded-xl p-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Today's Attendance Breakdown</h3>
    {{-- Fixed height (h-64) ensures chart stability on mobile --}}
    <canvas id="donutChart" class="w-full h-64"></canvas>
</div>

{{-- Monthly Attendance Chart (Bar Chart) --}}
<div class="bg-white shadow-lg rounded-xl p-4">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Attendance This Month</h3>
    {{-- Fixed height (h-64) ensures chart stability on mobile --}}
    <canvas id="monthlyChart" class="w-full h-64"></canvas>
</div>


</div>

{{-- Chart.js and Initialization Scripts --}}
{{-- Note: If Chart.js is already included in your main mobile-app layout, you can remove this script tag. --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // --- Donut chart setup ---
    const donutCtx = document.getElementById(&#39;donutChart&#39;).getContext(&#39;2d&#39;);
    new Chart(donutCtx, {
        type: &#39;doughnut&#39;,
        data: {
            labels: [&#39;Present&#39;, &#39;Late&#39;, &#39;Absent&#39;],
            datasets: [{
                // Assuming $present, $lateToday, and $absent are available PHP variables
                data: [{{ $present }}, {{ $lateToday }}, {{ $absent }}],
                backgroundColor: [&#39;#10b981&#39;, &#39;#f59e0b&#39;, &#39;#ef4444&#39;], // green, amber, red
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Crucial for mobile height management
            plugins: {
                legend: {
                    position: &#39;bottom&#39;,
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });

    // --- Monthly attendance chart setup ---
    const monthlyCtx = document.getElementById(&#39;monthlyChart&#39;).getContext(&#39;2d&#39;);
    new Chart(monthlyCtx, {
        type: &#39;bar&#39;,
        data: {
            // Assuming $attendanceThisMonth is an associative array: [&#39;Label&#39; =&gt; Count, ...]
            labels: {!! json_encode(array_keys($attendanceThisMonth)) !!},
            datasets: [{
                label: &#39;Attendance Count&#39;,
                data: {!! json_encode(array_values($attendanceThisMonth)) !!},
                backgroundColor: &#39;#3b82f6&#39;, // Primary Blue
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Crucial for mobile height management
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    stepSize: 1,
                    // Ensure y-axis labels are readable
                    ticks: {
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value;
                            }
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});


</script>

@endsection