<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Attendance Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 15px;
            margin-bottom: 20px;
            padding-top: 10px;
        }

        .header img {
            width: 80px;
            margin-bottom: 8px;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 8px;
            color: #1a1a1a;
        }

        .sub-heading {
            margin-top: 5px;
            font-size: 14px;
            color: #555;
            font-weight: 600;
        }

        .department-badge {
            background: #0066cc;
            color: white;
            padding: 5px 16px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 8px;
            border: 2px solid #0052a3;
        }

        .details {
            margin-top: 20px;
            margin-bottom: 15px;
            background: #f0f0f0;
            padding: 12px;
            border-left: 4px solid #0066cc;
        }

        .details table {
            width: 100%;
            font-size: 11px;
        }

        .details td {
            padding: 3px 0;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.report-table th {
            background: #4a5568;
            color: white;
            border: 1px solid #2d3748;
            padding: 10px 6px;
            font-size: 11px;
            text-align: center;
            font-weight: 600;
        }

        table.report-table td {
            border: 1px solid #cbd5e0;
            padding: 8px 6px;
            font-size: 10px;
            text-align: center;
        }

        table.report-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        .status-attended { color: #22543d; font-weight: 600; }
        .status-late { color: #c05621; font-weight: 600; }
        .status-missed { color: #c53030; font-weight: 600; }
        .status-undertime { color: #744210; font-weight: 600; }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/UClogo.png');
    $logoData = base64_encode(file_get_contents($logoPath));

    // Determine period covered based on attendance
    if ($attendances->isNotEmpty()) {
        $from = $attendances->min(fn($a) => $a->created_at);
        $to = $attendances->max(fn($a) => $a->created_at);
    } else {
        $from = $filters['start_date'] ?? now();
        $to = $filters['end_date'] ?? now();
    }
@endphp

<div class="header">
    <img src="data:image/png;base64,{{ $logoData }}" alt="UCLM Logo">
    <div class="report-title">UNIVERSITY OF CEBU LAPU-LAPU & MANDAUE</div>
    <div class="sub-heading">TEACHER ATTENDANCE REPORT</div>
    <div class="department-badge">{{ strtoupper($teacher->department ?? 'N/A') }} DEPARTMENT</div>
</div>

<!-- DATE RANGE & DEPARTMENT -->
<div class="details">
    <table>
        <tr>
            <td><span class="label">Period Covered:</span> {{ \Carbon\Carbon::parse($from)->format('F d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td><span class="label">Department:</span> {{ strtoupper($teacher->department ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td><span class="label">Generated On:</span> {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y h:i A') }}</td>
        </tr>
    </table>
</div>

<!-- ATTENDANCE TABLE -->
<table class="report-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Subject</th>
            <th>Room</th>
            <th>Status</th>
            <th>Time-in</th>
            <th>Time-out</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($attendances as $a)
            @php
                $schedule = $a->schedule;
                $attendanceDate = \Carbon\Carbon::parse($a->created_at, 'Asia/Manila');
                $timeIn = $a->time_in ? \Carbon\Carbon::parse($a->time_in, 'Asia/Manila')->format('g:i A') : '--';
                $timeOut = $a->time_out ? \Carbon\Carbon::parse($a->time_out, 'Asia/Manila')->format('g:i A') : '--';
                $statusClass = 'status-' . strtolower($a->status ?? 'unknown');
                $subject = $schedule->subject->subject_name ?? 'N/A';
                $room = $schedule->room->room_code ?? 'N/A';
            @endphp
            <tr>
                <td>{{ $attendanceDate->format('M d, Y') }}</td>
                <td>{{ $subject }}</td>
                <td>{{ $room }}</td>
                <td class="{{ $statusClass }}">{{ strtoupper($a->status ?? '--') }}</td>
                <td>{{ $timeIn }}</td>
                <td>{{ $timeOut }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:10px;">No attendance records found for this date range.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
