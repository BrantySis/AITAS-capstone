@props(['schedule', 'attendanceMap' => []])

@php
    use Carbon\Carbon;

    $now = Carbon::now();
    $start = Carbon::parse($schedule->starts_at);
    $end = Carbon::parse($schedule->ends_at);

    if ($now->lt($start)) {
        $status = 'Upcoming';
        $color = 'bg-blue-100 text-blue-700 border-blue-400';
    } elseif ($now->between($start, $end)) {
        $status = 'Ongoing';
        $color = 'bg-green-100 text-green-700 border-green-400';
    } else {
        $status = 'Missed';
        $color = 'bg-red-100 text-red-700 border-red-400';
    }
@endphp

<span class="px-3 py-1 text-xs font-semibold border rounded-full {{ $color }}">
    {{ $status }}
</span>
